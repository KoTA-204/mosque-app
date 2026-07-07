<?php

namespace App\Services\Akuntansi;

use App\Models\Aset;
use App\Models\Jurnal;
use App\Models\Periode;
use Illuminate\Pagination\LengthAwarePaginator;
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

    public function __construct(private AkunQueryService $akunQuery) {}

    // ── Query (getter — dipertahankan) ────────────────────────────

    public function daftar(array $filter): LengthAwarePaginator
    {
        return Jurnal::with(['periode', 'detailJurnal.akun', 'aset'])
            ->penyesuaian()
            ->when($filter['periode_id'] ?? null, fn($q) => $q->where('periode_id', $filter['periode_id']))
            ->when($filter['tipe'] ?? null,      fn($q) => $q->where('tipe_penyesuaian', $filter['tipe']))
            ->when($filter['status'] ?? null,    fn($q) => $q->where('status', strtoupper($filter['status'])))
            ->when($filter['search'] ?? null,    fn($q) =>
                $q->where('keterangan', 'like', "%{$filter['search']}%")
            )
            ->orderBy('tanggal', 'desc')
            ->paginate($filter['per_page'] ?? 10)
            ->withQueryString();
    }

    public function getById(Jurnal $jurnal): Jurnal
    {
        return $jurnal->load('periode', 'detailJurnal.akun', 'aset');
    }

    /**
     * Delegasi ke AkunQueryService (hapus duplikasi).
     * Parameter $tipe dipertahankan demi kompatibilitas pemanggilan lama.
     */
    public function getAkunList(string $tipe = ''): array
    {
        return $this->akunQuery->getGroupedAkun(self::EXCLUDED_AKUN_KODE);
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
     * Daftar aset yang sudah ditandai untuk dilepas (TIDAK AKTIF + alasan
     * AKAN_DILEPAS) dan belum pernah benar-benar dilepas. Dipakai tipe PELEPASAN_ASET.
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

    // ── Kalkulasi ─────────────────────────────────────────

    /**
     * Penyusutan garis lurus per bulan.
     * = nilai_tercatat / (umur_manfaat * 12)
     */
    public function hitungPenyusutan(Aset $aset): float
    {
        if (!$aset->umur_manfaat || $aset->umur_manfaat <= 0) return 0;

        return round($aset->nilai_tercatat / ($aset->umur_manfaat * 12), 2);
    }

    // ── Aksi ────────────────────────────────────────────

    /**
     * Mencatat jurnal penyesuaian.
     *
     * Alur satu jalur (menghindari double-update akumulasi):
     * 1. selalu dibuat sebagai DRAFT + catat detail + tautkan aset (tanpa update akumulasi),
     * 2. bila diminta POSTED, posting lewat Template Method postingKeBukuBesar()
     *    sehingga hook setelahPosting() yang meng-update akumulasi hanya berjalan SEKALI.
     */
    public function catatPenyesuaian(array $data, string $status = 'DRAFT'): Jurnal
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
                'status'           => 'DRAFT',
            ]);

            $this->catatDetailJurnal($jurnal, $data['detail']);

            if ($data['tipe_penyesuaian'] === 'PENYUSUTAN_ASET') {
                $this->lampirkanAsetPenyusutan($jurnal, $data);
            } elseif ($data['tipe_penyesuaian'] === 'PELEPASAN_ASET') {
                $this->lampirkanAsetPelepasan($jurnal, $data);
            }

            // Posting lewat satu jalur resmi → setelahPosting() menjalankan efek aset 1x.
            if ($status === 'POSTED') {
                $hasil = $this->postingKeBukuBesar($jurnal);
                if ($hasil !== true) {
                    throw new \RuntimeException($hasil);
                }
            }

            return $jurnal->load('detailJurnal.akun', 'aset');
        });
    }

    /**
     * Menautkan aset ke jurnal (TANPA menyentuh akumulasi).
     * Pembaruan akumulasi ditangani terpusat di hook setelahPosting().
     *
     * Format dari form:
     *   detail[0][aset_rows][0][aset_id] = 1
     *   detail[0][aset_rows][0][nominal] = "46.875"
     */
    private function lampirkanAsetPenyusutan(Jurnal $jurnal, array $data): void
    {
        $debitRow = collect($data['detail'] ?? [])->firstWhere('tipe', 'DEBIT');
        $asetRows = $debitRow['aset_rows'] ?? [];

        foreach ($asetRows as $row) {
            $asetId  = $row['aset_id'] ?? null;
            $nominal = $this->parseNominal($row['nominal'] ?? 0);

            if (!$asetId || $nominal <= 0) continue;

            // Jurnal sebagai Creator dari tautan aset (Creator).
            $jurnal->lampirkanAset((int) $asetId, $nominal);
        }
    }

    /** Memperbarui akumulasi penyusutan dan nilai buku aset. */
    private function perbaruiAkumulasiAset(int $asetId, float $nominal): void
    {
        $aset = Aset::find($asetId);
        if (!$aset) return;

        $aset->akumulasi_penyusutan = ($aset->akumulasi_penyusutan ?? 0) + $nominal;
        $aset->nilai_buku           = $aset->nilai_tercatat - $aset->akumulasi_penyusutan;
        $aset->save();
    }

    /**
     * Menautkan aset yang akan dilepas ke jurnal (TANPA menghentikan pengakuan).
     * Nilai buku saat pelepasan disimpan di pivot 'nominal' untuk keperluan audit.
     * Derecognition sesungguhnya ditangani terpusat di hook setelahPosting()
     * lewat lepasAset(), sehingga efeknya hanya terjadi SEKALI saat posting.
     *
     * Field form: aset_dilepas[] berisi id aset.
     */
    private function lampirkanAsetPelepasan(Jurnal $jurnal, array $data): void
    {
        $asetIds = collect($data['aset_dilepas'] ?? [])
            ->filter()
            ->unique()
            ->values();

        foreach ($asetIds as $asetId) {
            $aset = Aset::find($asetId);
            if (!$aset) continue;

            $nilaiBuku = max((float) $aset->nilai_tercatat - (float) ($aset->akumulasi_penyusutan ?? 0), 0);

            // Jurnal sebagai Creator dari tautan aset (Creator).
            $jurnal->lampirkanAset((int) $aset->id, $nilaiBuku);
        }
    }

    /**
     * Menghentikan pengakuan (derecognition) aset saat jurnal pelepasan diposting.
     *   akumulasi_penyusutan := nilai_tercatat  (akumulasi penuh)
     *   nilai_buku           := 0               (aset keluar dari pembukuan)
     *   status_aset          := 'TIDAK AKTIF' dengan alasan AKAN_DILEPAS
     * Penanda sudah-dilepas = adanya jurnal PELEPASAN_ASET berstatus POSTED terkait.
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

    // ── Hook: setelah posting (SATU-SATUNYA tempat efek aset dijalankan) ─────────

    protected function setelahPosting(Jurnal $jurnal): void
    {
        if (!in_array($jurnal->tipe_penyesuaian, ['PENYUSUTAN_ASET', 'PELEPASAN_ASET'], true)) {
            return;
        }

        $jurnal->loadMissing('aset');

        // Penyusutan: menambah akumulasi penyusutan (nilai buku turun bertahap).
        if ($jurnal->tipe_penyesuaian === 'PENYUSUTAN_ASET') {
            foreach ($jurnal->aset as $aset) {
                $this->perbaruiAkumulasiAset($aset->id, (float) $aset->pivot->nominal);
            }
            return;
        }

        // Pelepasan: derecognition — aset dikeluarkan dari pembukuan.
        foreach ($jurnal->aset as $aset) {
            $this->lepasAset($aset, $jurnal->tanggal);
        }
    }

    // ── Hook: sebelum hapus ────────────────────────────────────

    protected function sebelumPenghapusan(Jurnal $jurnal): void
    {
        $jurnal->aset()->detach();
    }
}
