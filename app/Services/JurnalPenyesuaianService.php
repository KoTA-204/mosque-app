<?php

namespace App\Services;

use App\Models\Akun;
use App\Models\Aset;
use App\Models\DetailJurnal;
use App\Models\Jurnal;
use App\Models\Periode;
use Illuminate\Support\Facades\DB;

class JurnalPenyesuaianService
{
    const TIPE_LABELS = [
        'PENYUSUTAN_ASET'          => 'Penyusutan Aset',
        'BEBAN_BELUM_DIBAYAR'      => 'Beban Masih Harus Dibayar',
        'PENDAPATAN_BELUM_DICATAT' => 'Pendapatan Belum Dicatat',
        'BEBAN_DIBAYAR_DIMUKA'     => 'Beban Dibayar Dimuka',
        'ZAKAT_INFAQ'              => 'Penyesuaian Dana Zakat/Infaq',
    ];

    const TIPE_DESCRIPTIONS = [
        'PENYUSUTAN_ASET'          => 'Mencatat penurunan nilai aset tetap.',
        'BEBAN_BELUM_DIBAYAR'      => 'Beban yang sudah terjadi tetapi belum dibayar.',
        'PENDAPATAN_BELUM_DICATAT' => 'Pendapatan yang sudah terjadi tetapi belum dicatat.',
        'BEBAN_DIBAYAR_DIMUKA'     => 'Biaya yang dibayar di muka untuk periode berikutnya.',
        'ZAKAT_INFAQ'              => 'Mencatat dana yang belum disalurkan atau dialokasikan.',
    ];

    /**
     * Akun yang dikecualikan dari semua tipe penyesuaian (kecuali MANUAL).
     * Kas & Bank tidak pernah disesuaikan lewat jurnal penyesuaian.
     */
    const EXCLUDED_AKUN_KODE = ['1-1100', '1-1200', '1-1300', '1-1400'];

    // ── Query ──────────────────────────────────────────────────────────────

    public function getList(
        ?string $search    = '',
        ?string $periodeId = '',
        ?string $tipe      = '',
        ?string $status    = '',
        int    $perPage   = 10
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

    public function getPeriodeAktif(): ?Periode
    {
        return Periode::aktif()->where('tipe', 'bulanan')->latest('tanggal_awal')->first();
    }

    public function getPeriodeList()
    {
        return Periode::orderBy('tanggal_awal', 'desc')->get();
    }

    /**
     * Akun child yang relevan untuk penyesuaian.
     *
     * Untuk tipe MANUAL    → semua akun child (parent_id != null).
     * Untuk tipe lainnya   → akun child KECUALI Kas & Bank.
     * Dikelompokkan per kategori untuk UX dropdown yang lebih baik.
     */
    public function getAkunList(string $tipe = ''): array
    {
        $query = Akun::with('kategoriAkun')
            ->whereNotNull('parent_id')
            ->orderBy('kode_akun');

        $query->whereNotIn('kode_akun', self::EXCLUDED_AKUN_KODE);

        return $query->get()
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

            // Simpan detail jurnal (baris debit/kredit)
            foreach ($data['detail'] as $detail) {
                $nominalRaw = $detail['nominal'] ?? 0;
                $nominal    = is_string($nominalRaw)
                    ? (float) str_replace(['.', ','], ['', '.'], $nominalRaw)
                    : (float) $nominalRaw;

                if (empty($detail['akun_id']) || $nominal <= 0) continue;

                DetailJurnal::create([
                    'jurnal_id' => $jurnal->id,
                    'akun_id'   => $detail['akun_id'],
                    'tipe'      => $detail['tipe'],
                    'nominal'   => $nominal,
                ]);
            }

            // Attach ke jurnal_aset kalau tipe penyusutan
            if ($data['tipe_penyesuaian'] === 'PENYUSUTAN_ASET') {
                $this->attachAset($jurnal, $data, $status);
            }

            return $jurnal->load('detailJurnal.akun', 'aset');
        });
    }

    /**
     * Attach aset ke jurnal dan update akumulasi jika POSTED.
     *
     * FIX: aset_rows sekarang nested di dalam detail[0] (baris DEBIT),
     * bukan lagi di top-level $data['aset_rows'].
     *
     * Format baru dari form:
     *   detail[0][aset_rows][0][aset_id] = 1
     *   detail[0][aset_rows][0][nominal] = "46.875"
     */
    private function attachAset(Jurnal $jurnal, array $data, string $status): void
    {
        // FIX: ambil aset_rows dari baris DEBIT di dalam detail, bukan top-level
        $debitRow = collect($data['detail'] ?? [])
            ->firstWhere('tipe', 'DEBIT');

        $asetRows = $debitRow['aset_rows'] ?? [];

        foreach ($asetRows as $row) {
            $asetId  = $row['aset_id'] ?? null;
            $nominal = is_string($row['nominal'] ?? '')
                ? (float) str_replace(['.', ','], ['', '.'], $row['nominal'] ?? 0)
                : (float) ($row['nominal'] ?? 0);

            if (!$asetId || $nominal <= 0) continue;

            $jurnal->aset()->attach($asetId, ['nominal' => $nominal]);

            // Update akumulasi & nilai buku hanya jika POSTED
            if ($status === 'POSTED') {
                $aset = Aset::find($asetId);
                if ($aset) {
                    $aset->akumulasi_penyusutan = ($aset->akumulasi_penyusutan ?? 0) + $nominal;
                    $aset->nilai_buku           = $aset->nilai_tercatat - $aset->akumulasi_penyusutan;
                    $aset->save();
                }
            }
        }
    }

    // ── Post ───────────────────────────────────────────────────────────────

    public function post(Jurnal $jurnal): bool|string
    {
        if ($jurnal->status === 'POSTED') {
            return 'Jurnal sudah diposting';
        }

        $jurnal->load('detailJurnal', 'aset');

        if ($jurnal->detailJurnal->isEmpty()) {
            return 'Jurnal harus memiliki minimal satu entri';
        }

        $totalDebit  = $jurnal->detailJurnal->where('tipe', 'DEBIT')->sum('nominal');
        $totalKredit = $jurnal->detailJurnal->where('tipe', 'KREDIT')->sum('nominal');

        if (round($totalDebit, 2) !== round($totalKredit, 2)) {
            return 'Total debit dan kredit harus sama sebelum diposting';
        }

        DB::transaction(function () use ($jurnal) {
            $jurnal->update(['status' => 'POSTED']);

            if ($jurnal->tipe_penyesuaian === 'PENYUSUTAN_ASET') {
                foreach ($jurnal->aset as $aset) {
                    $nominal = (float) $aset->pivot->nominal;
                    $aset->akumulasi_penyusutan = ($aset->akumulasi_penyusutan ?? 0) + $nominal;
                    $aset->nilai_buku           = $aset->nilai_tercatat - $aset->akumulasi_penyusutan;
                    $aset->save();
                }
            }
        });

        return true;
    }

    // ── Delete ─────────────────────────────────────────────────────────────

    public function delete(Jurnal $jurnal): bool|string
    {
        if ($jurnal->status === 'POSTED') {
            return 'Jurnal yang sudah diposting tidak bisa dihapus';
        }

        DB::transaction(function () use ($jurnal) {
            $jurnal->aset()->detach();
            $jurnal->detailJurnal()->delete();
            $jurnal->delete();
        });

        return true;
    }
}