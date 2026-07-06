<?php

namespace App\Services\Akuntansi;

use App\Models\Akun;
use App\Models\Aset;
use App\Models\DetailJurnal;
use App\Models\Jurnal;
use App\Models\Periode;
use Illuminate\Support\Facades\DB;

class JurnalPenyesuaianService extends JurnalService
{
    const TIPE_LABELS = [
        'PENYUSUTAN_ASET'          => 'Penyusutan Aset',
        'BEBAN_BELUM_DIBAYAR'      => 'Beban Masih Harus Dibayar',
        'PENDAPATAN_BELUM_DICATAT' => 'Pendapatan Belum Dicatat',
        'BEBAN_DIBAYAR_DIMUKA'     => 'Beban Dibayar Dimuka',
        'ZAKAT_INFAQ'              => 'Penyesuaian Dana Zakat/Infaq',
        'PELEPASAN_ASET'           => 'Pelepasan Aset',
    ];

    const TIPE_DESCRIPTIONS = [
        'PENYUSUTAN_ASET'          => 'Mencatat penurunan nilai aset tetap.',
        'BEBAN_BELUM_DIBAYAR'      => 'Beban yang sudah terjadi tetapi belum dibayar.',
        'PENDAPATAN_BELUM_DICATAT' => 'Pendapatan yang sudah terjadi tetapi belum dicatat.',
        'BEBAN_DIBAYAR_DIMUKA'     => 'Biaya yang dibayar di muka untuk periode berikutnya.',
        'ZAKAT_INFAQ'              => 'Mencatat dana yang belum disalurkan atau dialokasikan.',
        'PELEPASAN_ASET'           => 'Mengeluarkan aset dari pembukuan (dijual/dibuang/dihibahkan) dan mengakui laba/rugi pelepasan.',
    ];

    /**
     * Akun yang dikecualikan dari semua tipe penyesuaian.
     * Kas & Bank tidak pernah disesuaikan lewat jurnal penyesuaian.
     */
    // Kas tidak pernah disesuaikan lewat jurnal penyesuaian (CoA laporan).
    const EXCLUDED_AKUN_KODE = ['1-101', '1-102', '1-103'];

    // ── Query ──────────────────────────────────────────────────────────────

    public function getList(
        ?string $search    = '',
        ?string $periodeId = '',
        ?string $tipe      = '',
        ?string $status    = '',
        int     $perPage   = 10
    ) {
        return Jurnal::with(['periode', 'detailJurnal.akun', 'aset'])
            ->penyesuaian()
            ->when($periodeId, fn($q) => $q->where('periode_id', $periodeId))
            ->when($tipe,      fn($q) => $q->where('tipe_penyesuaian', $tipe))
            ->when($status,    fn($q) => $q->where('status', strtoupper($status)))
            ->when($search,    fn($q) =>
                $q->where('keterangan', 'like', "%{$search}%")
            )
            ->orderBy('tanggal', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getById(Jurnal $jurnal): Jurnal
    {
        return $jurnal->load('periode', 'detailJurnal.akun', 'aset');
    }

    public function getAkunList(string $tipe = ''): array
    {
        return Akun::with('kategoriAkun')
            ->whereNotNull('parent_id')
            ->whereNotIn('kode_akun', self::EXCLUDED_AKUN_KODE)
            ->orderBy('kode_akun')
            ->get()
            ->groupBy(fn($akun) => $akun->kategoriAkun->nama_kategori ?? 'Lainnya')
            ->map(fn($group, $kategori) => [
                'kategori' => $kategori,
                'akun'     => $group->map(fn($a) => [
                    'id'           => $a->id,
                    'kode_akun'    => $a->kode_akun,
                    'nama_akun'    => $a->nama_akun,
                    'saldo_normal' => $a->saldo_normal,
                    'label'        => $a->kode_akun . ' — ' . $a->nama_akun,
                ])->values(),
            ])
            ->values()
            ->toArray();
    }

    public function getAsetAktif()
    {
        return Aset::aktif()
            ->whereNotNull('umur_manfaat')
            ->where('umur_manfaat', '>', 0)
            ->orderBy('nama_aset')
            ->get()
            ->map(fn($aset) => [
                'id'                   => $aset->id,
                'nama_aset'            => $aset->nama_aset,
                'nilai_tercatat'       => (float) $aset->nilai_tercatat,
                'akumulasi_penyusutan' => (float) ($aset->akumulasi_penyusutan ?? 0),
                'nilai_buku'           => (float) $aset->nilai_buku,
                'umur_manfaat'         => $aset->umur_manfaat,
                'penyusutan_per_bulan' => $this->hitungPenyusutan($aset),
            ]);
    }

    /**
     * Daftar aset yang MASIH tercatat di pembukuan (belum pernah dilepas),
     * baik berstatus AKTIF maupun TIDAK AKTIF. Dipakai pada tipe PELEPASAN_ASET.
     */
    public function getAsetUntukPelepasan()
    {
        return Aset::query()
            ->where('status_aset', 'TIDAK AKTIF')
            ->where('alasan_nonaktif', Aset::ALASAN_AKAN_DILEPAS)
            ->belumDilepas()
            ->orderBy('nama_aset')
            ->get()
            ->map(fn($aset) => [
                'id'                   => $aset->id,
                'kode_aset'            => $aset->kode_aset,
                'nama_aset'            => $aset->nama_aset,
                'status_aset'          => $aset->status_aset,
                'nilai_tercatat'       => (float) $aset->nilai_tercatat,
                'akumulasi_penyusutan' => (float) ($aset->akumulasi_penyusutan ?? 0),
                'nilai_buku'           => max((float) $aset->nilai_tercatat - (float) ($aset->akumulasi_penyusutan ?? 0), 0),
            ]);
    }

    // ── Kalkulasi ──────────────────────────────────────────────────────────

    /**
     * Penyusutan garis lurus per bulan.
     * = nilai_tercatat / (umur_manfaat * 12)
     */
    public function hitungPenyusutan(Aset $aset): float
    {
        if (!$aset->umur_manfaat || $aset->umur_manfaat <= 0) return 0;

        return round($aset->nilai_tercatat / ($aset->umur_manfaat * 12), 2);
    }

    // ── Store ──────────────────────────────────────────────────────────────

    public function store(array $data, string $status = 'DRAFT'): Jurnal
    {
        return DB::transaction(function () use ($data, $status) {
            $periode = Periode::findOrFail($data['periode_id']);

            $jurnal = Jurnal::create([
                'periode_id'       => $periode->id,
                'transaksi_id'     => null,
                'jurnal_ref_id'    => null,
                'jenis_jurnal'     => 'PENYESUAIAN',
                'tipe_penyesuaian' => $data['tipe_penyesuaian'],
                'tanggal'          => $data['tanggal'],
                'keterangan'       => $data['keterangan'] ?? null,
                'status'           => $status,
            ]);

            // Gunakan helper dari base class
            $this->storeDetail($jurnal, $data['detail']);

            if ($data['tipe_penyesuaian'] === 'PENYUSUTAN_ASET') {
                $this->attachAset($jurnal, $data, $status);
            } elseif ($data['tipe_penyesuaian'] === 'PELEPASAN_ASET') {
                $this->attachAsetPelepasan($jurnal, $data, $status);
            }

            return $jurnal->load('detailJurnal.akun', 'aset');
        });
    }

    /**
     * Attach aset ke jurnal dan update akumulasi jika POSTED.
     *
     * Format dari form:
     *   detail[0][aset_rows][0][aset_id] = 1
     *   detail[0][aset_rows][0][nominal] = "46.875"
     */
    private function attachAset(Jurnal $jurnal, array $data, string $status): void
    {
        $debitRow = collect($data['detail'] ?? [])->firstWhere('tipe', 'DEBIT');
        $asetRows = $debitRow['aset_rows'] ?? [];

        foreach ($asetRows as $row) {
            $asetId  = $row['aset_id'] ?? null;
            $nominal = $this->parseNominal($row['nominal'] ?? 0); 

            if (!$asetId || $nominal <= 0) continue;

            $jurnal->aset()->attach($asetId, ['nominal' => $nominal]);

            if ($status === 'POSTED') {
                $this->updateAkumulasiAset($asetId, $nominal);
            }
        }
    }

    /**
     * Update akumulasi penyusutan dan nilai buku aset.
     */
    private function updateAkumulasiAset(int $asetId, float $nominal): void
    {
        $aset = Aset::find($asetId);
        if (!$aset) return;

        $aset->akumulasi_penyusutan = ($aset->akumulasi_penyusutan ?? 0) + $nominal;
        $aset->nilai_buku           = $aset->nilai_tercatat - $aset->akumulasi_penyusutan;
        $aset->save();
    }

    // ── Hook: post ─────────────────────────────────────────────────────────

    /**
     * Saat diposting, update akumulasi penyusutan untuk tiap aset terlampir.
     */
    protected function onPosted(Jurnal $jurnal): void
    {
        $jurnal->loadMissing('aset');

        // Penyusutan: perhitungan LAMA tidak diubah.
        if ($jurnal->tipe_penyesuaian === 'PENYUSUTAN_ASET') {
            foreach ($jurnal->aset as $aset) {
                $this->updateAkumulasiAset($aset->id, (float) $aset->pivot->nominal);
            }
            return;
        }

        // Pelepasan: hentikan pengakuan aset saat diposting.
        if ($jurnal->tipe_penyesuaian === 'PELEPASAN_ASET') {
            foreach ($jurnal->aset as $aset) {
                $this->lepasAset($aset, $jurnal->tanggal);
            }
        }
    }

    // ── Hook: delete ───────────────────────────────────────────────────────

    /**
     * Detach relasi aset sebelum jurnal dihapus.
     */
    protected function beforeDelete(Jurnal $jurnal): void
    {
        $jurnal->aset()->detach();
    }

    // ── Pelepasan aset ─────────────────────────────────────────

    /**
     * Lampirkan aset yang dilepas ke jurnal. Nilai buku saat pelepasan
     * disimpan pada pivot 'nominal' untuk keperluan penelusuran/audit.
     * Field form: aset_dilepas[] berisi id aset.
     */
    private function attachAsetPelepasan(Jurnal $jurnal, array $data, string $status): void
    {
        $asetIds = collect($data['aset_dilepas'] ?? [])
            ->filter()
            ->unique()
            ->values();

        foreach ($asetIds as $asetId) {
            $aset = Aset::find($asetId);
            if (!$aset) continue;

            $nilaiBuku = max((float) $aset->nilai_tercatat - (float) ($aset->akumulasi_penyusutan ?? 0), 0);
            $jurnal->aset()->attach($aset->id, ['nominal' => $nilaiBuku]);

            if ($status === 'POSTED') {
                $this->lepasAset($aset, $jurnal->tanggal);
            }
        }
    }

    /**
     * Hentikan pengakuan (derecognition) aset saat jurnal pelepasan diposting.
     *
     * PERUBAHAN PERHITUNGAN — KHUSUS PELEPASAN (baru):
     *   akumulasi_penyusutan := nilai_tercatat   (akumulasi penuh)
     *   nilai_buku           := 0                (aset keluar dari pembukuan)
     *   status_aset          := 'TIDAK AKTIF' dengan alasan 'AKAN_DILEPAS'
     * Tidak ada status baru 'DILEPAS'. Penanda sudah-dilepas = adanya jurnal
     * PELEPASAN_ASET berstatus POSTED yang terkait (Aset::sudahDilepas()).
     */
    private function lepasAset(Aset $aset, $tanggal = null): void
    {
        $aset->akumulasi_penyusutan = $aset->nilai_tercatat;
        $aset->nilai_buku           = 0;
        $aset->status_aset          = 'TIDAK AKTIF';
        $aset->alasan_nonaktif      = Aset::ALASAN_AKAN_DILEPAS;
        if (empty($aset->tanggal_nonaktif)) {
            $aset->tanggal_nonaktif = $tanggal ?? now()->toDateString();
        }
        $aset->save();
    }
}