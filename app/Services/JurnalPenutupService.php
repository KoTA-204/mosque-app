<?php

namespace App\Services;

use App\Models\Akun;
use App\Models\Jurnal;
use App\Models\Periode;
use Illuminate\Support\Facades\DB;

class JurnalPenutupService extends JurnalService
{
    // ── Kode akun khusus penutupan ─────────────────────────────────────────
    const KODE_IKHTISAR_LR          = '3.2.01';
    const KODE_SALDO_DANA_MASJID    = '3.1.01';
    const KODE_SALDO_DANA_KUMULATIF = '3.1.02';
    const KODE_SURPLUS_PERIODE      = '3.3.01';

    const KODE_KATEGORI_PENDAPATAN  = '4';
    const KODE_KATEGORI_BEBAN       = '5';

    const TIPE_LABELS = [
        'TUTUP_PENDAPATAN' => 'Tutup Pendapatan',
        'TUTUP_BEBAN'      => 'Tutup Beban',
        'IKHTISAR_LR'      => 'Ikhtisar Laba/Rugi',
        'TUTUP_SALDO_DANA' => 'Tutup ke Saldo Dana',
    ];

    // ── Query ──────────────────────────────────────────────────────────────

    public function getList(
        ?string $search    = '',
        ?string $periodeId = '',
        ?string $status    = '',
        int     $perPage   = 10
    ) {
        return Jurnal::with(['periode', 'detailJurnal.akun'])
            ->penutup()
            ->when($periodeId, fn($q) => $q->where('periode_id', $periodeId))
            ->when($status,    fn($q) => $q->where('status', strtoupper($status)))
            ->when($search,    fn($q) => $q->where('keterangan', 'like', "%{$search}%"))
            ->orderBy('tanggal', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getById(Jurnal $jurnal): Jurnal
    {
        return $jurnal->load('periode', 'detailJurnal.akun');
    }

    // ── Ringkasan saldo periode ────────────────────────────────────────────

    public function getRingkasanPeriode(Periode $periode): array
    {
        $jurnals = Jurnal::with('detailJurnal.akun.kategoriAkun')
            ->where('periode_id', $periode->id)
            ->whereIn('jenis_jurnal', ['UMUM', 'PENYESUAIAN'])
            ->where('status', 'POSTED')
            ->get();

        $pendapatan = collect();
        $beban      = collect();

        foreach ($jurnals as $jurnal) {
            foreach ($jurnal->detailJurnal as $detail) {
                $akun     = $detail->akun;
                $kategori = $akun?->kategoriAkun;
                if (!$kategori) continue;

                $kodeKat = $kategori->kode_kategori;

                if ($kodeKat === self::KODE_KATEGORI_PENDAPATAN) {
                    $this->akumulasiSaldo($pendapatan, $akun, $detail, 'KREDIT');
                }

                if ($kodeKat === self::KODE_KATEGORI_BEBAN) {
                    $this->akumulasiSaldo($beban, $akun, $detail, 'DEBIT');
                }
            }
        }

        $totalPendapatan = $pendapatan->sum('saldo');
        $totalBeban      = $beban->sum('saldo');

        $adaDraftPenyesuaian = Jurnal::where('periode_id', $periode->id)
            ->where('jenis_jurnal', 'PENYESUAIAN')
            ->where('status', 'DRAFT')
            ->exists();

        return [
            'pendapatan'            => $pendapatan->values(),
            'beban'                 => $beban->values(),
            'total_pendapatan'      => $totalPendapatan,
            'total_beban'           => $totalBeban,
            'surplus'               => $totalPendapatan - $totalBeban,
            'ada_draft_penyesuaian' => $adaDraftPenyesuaian,
        ];
    }

    /**
     * Akumulasi saldo akun ke dalam collection berdasarkan saldo normal.
     *
     * @param  \Illuminate\Support\Collection  $collection
     * @param  \App\Models\Akun  $akun
     * @param  \App\Models\DetailJurnal  $detail
     * @param  string  $saldoNormal  'DEBIT' atau 'KREDIT'
     */
    private function akumulasiSaldo($collection, $akun, $detail, string $saldoNormal): void
    {
        $key = $akun->id;

        if (!$collection->has($key)) {
            $collection->put($key, ['akun' => $akun, 'saldo' => 0]);
        }

        $item          = $collection->get($key);
        $item['saldo'] += $detail->tipe === $saldoNormal
            ? (float) $detail->nominal
            : -(float) $detail->nominal;

        $collection->put($key, $item);
    }

    // ── Status tahap penutupan ─────────────────────────────────────────────

    public function getStatusTahap(Periode $periode): array
    {
        $tipes  = ['TUTUP_PENDAPATAN', 'TUTUP_BEBAN', 'IKHTISAR_LR', 'TUTUP_SALDO_DANA'];
        $status = [];

        foreach ($tipes as $tipe) {
            $jurnal = Jurnal::where('periode_id', $periode->id)
                ->where('jenis_jurnal', 'PENUTUP')
                ->where('tipe_penyesuaian', $tipe)
                ->latest()
                ->first();

            $status[$tipe] = [
                'jurnal'  => $jurnal,
                'selesai' => $jurnal && $jurnal->status === 'POSTED',
                'ada'     => (bool) $jurnal,
            ];
        }

        return $status;
    }

    public function getTahapSelesai(Periode $periode): int
    {
        $status  = $this->getStatusTahap($periode);
        $tipes   = ['TUTUP_PENDAPATAN', 'TUTUP_BEBAN', 'IKHTISAR_LR', 'TUTUP_SALDO_DANA'];
        $selesai = 0;

        foreach ($tipes as $tipe) {
            if ($status[$tipe]['selesai']) $selesai++;
        }

        return $selesai;
    }

    // ── Generate entri per tahap ───────────────────────────────────────────

    public function generateTutupPendapatan(array $ringkasan): array
    {
        $ikhtisarAkun = Akun::where('kode_akun', self::KODE_IKHTISAR_LR)->first();
        $detail       = [];

        foreach ($ringkasan['pendapatan'] as $item) {
            if ($item['saldo'] <= 0) continue;
            $detail[] = $this->buatEntri($item['akun']->id, $item['akun']->nama_akun, 'DEBIT', $item['saldo']);
        }

        if ($ikhtisarAkun && $ringkasan['total_pendapatan'] > 0) {
            $detail[] = $this->buatEntri($ikhtisarAkun->id, $ikhtisarAkun->nama_akun, 'KREDIT', $ringkasan['total_pendapatan']);
        }

        return $detail;
    }

    public function generateTutupBeban(array $ringkasan): array
    {
        $ikhtisarAkun = Akun::where('kode_akun', self::KODE_IKHTISAR_LR)->first();
        $detail       = [];

        if ($ikhtisarAkun && $ringkasan['total_beban'] > 0) {
            $detail[] = $this->buatEntri($ikhtisarAkun->id, $ikhtisarAkun->nama_akun, 'DEBIT', $ringkasan['total_beban']);
        }

        foreach ($ringkasan['beban'] as $item) {
            if ($item['saldo'] <= 0) continue;
            $detail[] = $this->buatEntri($item['akun']->id, $item['akun']->nama_akun, 'KREDIT', $item['saldo']);
        }

        return $detail;
    }

    public function generateIkhtisarLR(array $ringkasan): array
    {
        $ikhtisarAkun  = Akun::where('kode_akun', self::KODE_IKHTISAR_LR)->first();
        $saldoDanaAkun = Akun::where('kode_akun', self::KODE_SALDO_DANA_MASJID)->first();
        $surplus       = $ringkasan['surplus'];

        if (!$ikhtisarAkun || !$saldoDanaAkun || $surplus == 0) return [];

        // Surplus → Debit Ikhtisar, Kredit Saldo Dana
        // Defisit → Debit Saldo Dana, Kredit Ikhtisar
        [$debitAkun, $kreditAkun] = $surplus > 0
            ? [$ikhtisarAkun, $saldoDanaAkun]
            : [$saldoDanaAkun, $ikhtisarAkun];

        return [
            $this->buatEntri($debitAkun->id,  $debitAkun->nama_akun,  'DEBIT',  abs($surplus)),
            $this->buatEntri($kreditAkun->id, $kreditAkun->nama_akun, 'KREDIT', abs($surplus)),
        ];
    }

    public function generateTutupSaldoDana(Periode $periode, array $ringkasan): array
    {
        $surplusAkun   = Akun::where('kode_akun', self::KODE_SURPLUS_PERIODE)->first();
        $kumulatifAkun = Akun::where('kode_akun', self::KODE_SALDO_DANA_KUMULATIF)->first();
        $surplus       = $ringkasan['surplus'];

        if (!$surplusAkun || !$kumulatifAkun || $surplus == 0) return [];

        return [
            $this->buatEntri($surplusAkun->id,   'Surplus ' . $periode->nama_periode, 'DEBIT',  abs($surplus)),
            $this->buatEntri($kumulatifAkun->id,  $kumulatifAkun->nama_akun,          'KREDIT', abs($surplus)),
        ];
    }

    /**
     * Helper pembuat array satu baris detail jurnal.
     */
    private function buatEntri(int $akunId, string $namaAkun, string $tipe, float $nominal): array
    {
        return [
            'akun_id' => $akunId,
            'akun'    => $namaAkun,
            'tipe'    => $tipe,
            'nominal' => $nominal,
        ];
    }

    // ── Store per tahap ────────────────────────────────────────────────────

    public function storeTahap(
        Periode $periode,
        string  $tipePenyesuaian,
        array   $detail,
        string  $tanggal,
        string  $status = 'DRAFT'
    ): Jurnal {
        return \DB::transaction(function () use ($periode, $tipePenyesuaian, $detail, $tanggal, $status) {
            // Hapus draft lama untuk tahap yang sama
            Jurnal::where('periode_id', $periode->id)
                ->where('jenis_jurnal', 'PENUTUP')
                ->where('tipe_penyesuaian', $tipePenyesuaian)
                ->where('status', 'DRAFT')
                ->each(function ($j) {
                    $j->detailJurnal()->delete();
                    $j->delete();
                });

            $jurnal = Jurnal::create([
                'periode_id'       => $periode->id,
                'transaksi_id'     => null,
                'jurnal_ref_id'    => null,
                'jenis_jurnal'     => 'PENUTUP',
                'tipe_penyesuaian' => $tipePenyesuaian,
                'tanggal'          => $tanggal,
                'keterangan'       => self::TIPE_LABELS[$tipePenyesuaian] . ' — ' . $periode->nama_periode,
                'status'           => $status,
            ]);

            // Gunakan helper dari base class
            $this->storeDetail($jurnal, $detail);

            return $jurnal->load('detailJurnal.akun');
        });
    }

    public function storeAllTahap(
        Periode $periode,
        array   $semua,
        string  $tanggal,
        string  $status = 'DRAFT'
    ): array {
        $hasil = [];
        foreach ($semua as $tipe => $detail) {
            $hasil[$tipe] = $this->storeTahap($periode, $tipe, $detail, $tanggal, $status);
        }
        return $hasil;
    }

    // ── Post semua draft sekaligus ─────────────────────────────────────────

    public function postSemua(Periode $periode): bool|string
    {
        $jurnals = Jurnal::where('periode_id', $periode->id)
            ->where('jenis_jurnal', 'PENUTUP')
            ->where('status', 'DRAFT')
            ->get();

        if ($jurnals->isEmpty()) {
            return 'Tidak ada jurnal penutup draft yang bisa diposting';
        }

        \DB::transaction(fn() => $jurnals->each(fn($j) => $j->update(['status' => 'POSTED'])));

        return true;
    }
}