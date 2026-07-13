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

    const EXCLUDED_AKUN_KODE = ['1-1001', '1-1002', '1-1003'];

    public function __construct(private AkunQueryService $akunQuery) {}

    // ── Query ────────────────────────────

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

    public function getById(Jurnal $jurnal): array
    {
        $jurnal->load('periode', 'detailJurnal.akun', 'aset');

        return [
            'id'               => $jurnal->id,
            'nomor_jurnal'     => $jurnal->kode_jurnal,
            'tanggal'          => $jurnal->tanggal->format('d M Y'),
            'keterangan'       => $jurnal->keterangan ?? '—',
            'tipe_penyesuaian' => $jurnal->tipe_penyesuaian,
            'status'           => $jurnal->status,
            'detail_jurnal' => $jurnal->detailJurnal->map(fn($d) => [
                'akun'    => [
                    'kode_akun' => $d->akun->kode_akun,
                    'nama_akun' => $d->akun->nama_akun,
                ],
                'tipe'    => $d->tipe,
                'nominal' => (float) $d->nominal,
            ])->values(),
            'aset' => $jurnal->aset->map(fn($a) => [
                'nama_aset' => $a->nama_aset,
                'pivot'     => ['nominal' => (float) $a->pivot->nominal],
            ])->values(),
        ];
    }

    public function getAkunList(string $tipe = ''): array
    {
        return $this->akunQuery->getGroupedAkun(self::EXCLUDED_AKUN_KODE);
    }

    public function getAsetAktif()
    {
        $periodeAktif   = $this->getPeriodeAktif();
        $periodeAktifId = $periodeAktif?->id;

        return Aset::aktif()
            ->whereNotNull('umur_manfaat')
            ->where('umur_manfaat', '>', 0)
            ->belumDilepas()
            ->when($periodeAktifId, fn ($q) => $q
                ->whereDoesntHave('jurnalPenyesuaian', fn ($jq) => $jq
                    ->where('tipe_penyesuaian', 'PENYUSUTAN_ASET')
                    ->whereIn('status', ['POSTED', 'DRAFT'])
                    ->where('periode_id', $periodeAktifId)
                )
            )
            ->orderBy('nama_aset')
            ->get()
            ->map(fn ($aset) => [
                'id'                   => $aset->id,
                'nama_aset'            => $aset->nama_aset,
                'nilai_tercatat'       => (float) $aset->nilai_tercatat,
                'akumulasi_penyusutan' => (float) ($aset->akumulasi_penyusutan ?? 0),
                'nilai_buku'           => (float) $aset->nilai_buku,
                'umur_manfaat'         => $aset->umur_manfaat,
                'penyusutan_per_bulan' => $this->hitungPenyusutan($aset),
            ]);
    }

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
    public function hitungPenyusutan(Aset $aset): float
    {
        if (!$aset->umur_manfaat || $aset->umur_manfaat <= 0) return 0;

        return round($aset->nilai_tercatat / ($aset->umur_manfaat * 12), 2);
    }

    // ── Aksi ────────────────────────────────────────────
    public function catatPenyesuaian(array $data, string $status = 'DRAFT'): Jurnal
    {
        return DB::transaction(function () use ($data, $status) {
            $periode = Periode::findOrFail($data['periode_id']);

            if (!$periode->status) {
                throw new \RuntimeException(
                    "Periode {$periode->nama_periode} sudah ditutup. "
                    . 'Jurnal penyesuaian tidak dapat dicatat pada periode yang telah ditutup.'
                );
            }

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

            if ($status === 'POSTED') {
                $hasil = $this->postingKeBukuBesar($jurnal);
                if ($hasil !== true) {
                    throw new \RuntimeException($hasil);
                } 
            }

            return $jurnal->load('detailJurnal.akun', 'aset');
        });
    }

    private function lampirkanAsetPenyusutan(Jurnal $jurnal, array $data): void
    {
        $debitRow = collect($data['detail'] ?? [])->firstWhere('tipe', 'DEBIT');
        $asetRows = $debitRow['aset_rows'] ?? [];

        foreach ($asetRows as $row) {
            $asetId  = $row['aset_id'] ?? null;
            $nominal = $this->parseNominal($row['nominal'] ?? 0);

            if (!$asetId || $nominal <= 0) continue;

            $jurnal->lampirkanAset((int) $asetId, $nominal);
        }
    }

    private function perbaruiAkumulasiAset(int $asetId, float $nominal): void
    {
        $aset = Aset::find($asetId);
        if (!$aset) return;

        $aset->akumulasi_penyusutan = ($aset->akumulasi_penyusutan ?? 0) + $nominal;
        $aset->nilai_buku           = $aset->nilai_tercatat - $aset->akumulasi_penyusutan;
        $aset->save();
    }

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

            $jurnal->lampirkanAset((int) $aset->id, $nilaiBuku);
        }
    }

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

    protected function setelahPosting(Jurnal $jurnal): void
    {
        if (!in_array($jurnal->tipe_penyesuaian, ['PENYUSUTAN_ASET', 'PELEPASAN_ASET'], true)) {
            return;
        }

        $jurnal->loadMissing('aset');

        if ($jurnal->tipe_penyesuaian === 'PENYUSUTAN_ASET') {
            foreach ($jurnal->aset as $aset) {
                $this->perbaruiAkumulasiAset($aset->id, (float) $aset->pivot->nominal);
            }
            return;
        }

        foreach ($jurnal->aset as $aset) {
            $this->lepasAset($aset, $jurnal->tanggal);
        }
    }

    protected function sebelumPenghapusan(Jurnal $jurnal): void
    {
        $jurnal->aset()->detach();
    }
}
