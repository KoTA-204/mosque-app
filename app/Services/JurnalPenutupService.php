<?php

namespace App\Services;

use App\Models\Akun;
use App\Models\DetailJurnal;
use App\Models\Jurnal;
use App\Models\Periode;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class JurnalPenutupService
{
    // ── Kode akun khusus penutupan ─────────────────────────────────────────
    // Sesuaikan dengan data di tabel akun kamu
    const KODE_IKHTISAR_LR       = '3.2.01';
    const KODE_SALDO_DANA_MASJID = '3.1.01';
    const KODE_SALDO_DANA_KUMULATIF = '3.1.02';
    const KODE_SURPLUS_PERIODE   = '3.3.01';

    // Kode kategori akun
    const KODE_KATEGORI_PENDAPATAN = '4';
    const KODE_KATEGORI_BEBAN      = '5';

    // Label tipe penutupan
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

    public function getPeriodeAktif(): ?Periode
    {
        return Periode::aktif()->where('tipe', 'bulanan')->latest('tanggal_awal')->first();
    }

    public function getPeriodeList()
    {
        return Periode::orderBy('tanggal_awal', 'desc')->get();
    }

    public function getById(Jurnal $jurnal): Jurnal
    {
        return $jurnal->load('periode', 'detailJurnal.akun');
    }

    // ── Ringkasan saldo periode ────────────────────────────────────────────

    /**
     * Ambil ringkasan pendapatan & beban dari buku besar periode ini.
     * Hanya dari jurnal POSTED (umum + penyesuaian), bukan penutup.
     */
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
                    $key = $akun->id;
                    if (!$pendapatan->has($key)) {
                        $pendapatan->put($key, [
                            'akun'       => $akun,
                            'saldo'      => 0,
                        ]);
                    }
                    $item = $pendapatan->get($key);
                    // Pendapatan: saldo normal KREDIT
                    $item['saldo'] += $detail->tipe === 'KREDIT'
                        ? (float) $detail->nominal
                        : -(float) $detail->nominal;
                    $pendapatan->put($key, $item);
                }

                if ($kodeKat === self::KODE_KATEGORI_BEBAN) {
                    $key = $akun->id;
                    if (!$beban->has($key)) {
                        $beban->put($key, [
                            'akun'  => $akun,
                            'saldo' => 0,
                        ]);
                    }
                    $item = $beban->get($key);
                    // Beban: saldo normal DEBIT
                    $item['saldo'] += $detail->tipe === 'DEBIT'
                        ? (float) $detail->nominal
                        : -(float) $detail->nominal;
                    $beban->put($key, $item);
                }
            }
        }

        $totalPendapatan = $pendapatan->sum('saldo');
        $totalBeban      = $beban->sum('saldo');
        $surplus         = $totalPendapatan - $totalBeban;

        // Cek jurnal penyesuaian sudah diposting semua?
        $adaDraftPenyesuaian = Jurnal::where('periode_id', $periode->id)
            ->where('jenis_jurnal', 'PENYESUAIAN')
            ->where('status', 'DRAFT')
            ->exists();

        return [
            'pendapatan'          => $pendapatan->values(),
            'beban'               => $beban->values(),
            'total_pendapatan'    => $totalPendapatan,
            'total_beban'         => $totalBeban,
            'surplus'             => $surplus,
            'ada_draft_penyesuaian' => $adaDraftPenyesuaian,
        ];
    }

    // ── Status tahap penutupan ─────────────────────────────────────────────

    /**
     * Cek tahap mana yang sudah selesai (ada jurnal PENUTUP + POSTED-nya).
     */
    public function getStatusTahap(Periode $periode): array
    {
        $tipes = ['TUTUP_PENDAPATAN', 'TUTUP_BEBAN', 'IKHTISAR_LR', 'TUTUP_SALDO_DANA'];
        $status = [];

        foreach ($tipes as $tipe) {
            $jurnal = Jurnal::where('periode_id', $periode->id)
                ->where('jenis_jurnal', 'PENUTUP')
                ->where('tipe_penyesuaian', $tipe)
                ->latest()
                ->first();

            $status[$tipe] = [
                'jurnal'   => $jurnal,
                'selesai'  => $jurnal && $jurnal->status === 'POSTED',
                'ada'      => (bool) $jurnal,
            ];
        }

        return $status;
    }

    public function getTahapSelesai(Periode $periode): int
    {
        $status = $this->getStatusTahap($periode);
        $tipes  = ['TUTUP_PENDAPATAN', 'TUTUP_BEBAN', 'IKHTISAR_LR', 'TUTUP_SALDO_DANA'];
        $selesai = 0;
        foreach ($tipes as $tipe) {
            if ($status[$tipe]['selesai']) $selesai++;
        }
        return $selesai;
    }

    // ── Generate entri per tahap ───────────────────────────────────────────

    /**
     * Tahap 1: Tutup Pendapatan
     * Debit semua akun pendapatan → Kredit Ikhtisar L/R
     */
    public function generateTutupPendapatan(array $ringkasan): array
    {
        $ikhtisarAkun = Akun::where('kode_akun', self::KODE_IKHTISAR_LR)->first();
        $detail = [];

        foreach ($ringkasan['pendapatan'] as $item) {
            if ($item['saldo'] <= 0) continue;
            $detail[] = [
                'akun_id' => $item['akun']->id,
                'akun'    => $item['akun']->nama_akun,
                'tipe'    => 'DEBIT',
                'nominal' => $item['saldo'],
            ];
        }

        if ($ikhtisarAkun && $ringkasan['total_pendapatan'] > 0) {
            $detail[] = [
                'akun_id' => $ikhtisarAkun->id,
                'akun'    => $ikhtisarAkun->nama_akun,
                'tipe'    => 'KREDIT',
                'nominal' => $ringkasan['total_pendapatan'],
            ];
        }

        return $detail;
    }

    /**
     * Tahap 2: Tutup Beban
     * Kredit semua akun beban → Debit Ikhtisar L/R
     */
    public function generateTutupBeban(array $ringkasan): array
    {
        $ikhtisarAkun = Akun::where('kode_akun', self::KODE_IKHTISAR_LR)->first();
        $detail = [];

        if ($ikhtisarAkun && $ringkasan['total_beban'] > 0) {
            $detail[] = [
                'akun_id' => $ikhtisarAkun->id,
                'akun'    => $ikhtisarAkun->nama_akun,
                'tipe'    => 'DEBIT',
                'nominal' => $ringkasan['total_beban'],
            ];
        }

        foreach ($ringkasan['beban'] as $item) {
            if ($item['saldo'] <= 0) continue;
            $detail[] = [
                'akun_id' => $item['akun']->id,
                'akun'    => $item['akun']->nama_akun,
                'tipe'    => 'KREDIT',
                'nominal' => $item['saldo'],
            ];
        }

        return $detail;
    }

    /**
     * Tahap 3: Ikhtisar L/R → Saldo Dana Masjid
     * Debit Ikhtisar L/R → Kredit Saldo Dana Masjid (jika surplus)
     */
    public function generateIkhtisarLR(array $ringkasan): array
    {
        $ikhtisarAkun   = Akun::where('kode_akun', self::KODE_IKHTISAR_LR)->first();
        $saldoDanaAkun  = Akun::where('kode_akun', self::KODE_SALDO_DANA_MASJID)->first();
        $surplus        = $ringkasan['surplus'];
        $detail         = [];

        if (!$ikhtisarAkun || !$saldoDanaAkun || $surplus == 0) return $detail;

        if ($surplus > 0) {
            // Surplus: Debit Ikhtisar → Kredit Saldo Dana
            $detail[] = [
                'akun_id' => $ikhtisarAkun->id,
                'akun'    => $ikhtisarAkun->nama_akun,
                'tipe'    => 'DEBIT',
                'nominal' => abs($surplus),
            ];
            $detail[] = [
                'akun_id' => $saldoDanaAkun->id,
                'akun'    => $saldoDanaAkun->nama_akun,
                'tipe'    => 'KREDIT',
                'nominal' => abs($surplus),
            ];
        } else {
            // Defisit: Debit Saldo Dana → Kredit Ikhtisar
            $detail[] = [
                'akun_id' => $saldoDanaAkun->id,
                'akun'    => $saldoDanaAkun->nama_akun,
                'tipe'    => 'DEBIT',
                'nominal' => abs($surplus),
            ];
            $detail[] = [
                'akun_id' => $ikhtisarAkun->id,
                'akun'    => $ikhtisarAkun->nama_akun,
                'tipe'    => 'KREDIT',
                'nominal' => abs($surplus),
            ];
        }

        return $detail;
    }

    /**
     * Tahap 4: Tutup ke Saldo Dana Kumulatif
     * Debit Surplus Periode → Kredit Saldo Dana Kumulatif
     */
    public function generateTutupSaldoDana(Periode $periode, array $ringkasan): array
    {
        $surplusAkun    = Akun::where('kode_akun', self::KODE_SURPLUS_PERIODE)->first();
        $kumulatifAkun  = Akun::where('kode_akun', self::KODE_SALDO_DANA_KUMULATIF)->first();
        $surplus        = $ringkasan['surplus'];
        $detail         = [];

        if (!$surplusAkun || !$kumulatifAkun || $surplus == 0) return $detail;

        $namaSurplus = 'Surplus ' . $periode->nama_periode;

        $detail[] = [
            'akun_id' => $surplusAkun->id,
            'akun'    => $namaSurplus,
            'tipe'    => 'DEBIT',
            'nominal' => abs($surplus),
        ];
        $detail[] = [
            'akun_id' => $kumulatifAkun->id,
            'akun'    => $kumulatifAkun->nama_akun,
            'tipe'    => 'KREDIT',
            'nominal' => abs($surplus),
        ];

        return $detail;
    }

    // ── Store per tahap ────────────────────────────────────────────────────

    public function storeTahap(
        Periode $periode,
        string  $tipePenyesuaian,
        array   $detail,
        string  $tanggal,
        string  $status = 'DRAFT'
    ): Jurnal {
        return DB::transaction(function () use ($periode, $tipePenyesuaian, $detail, $tanggal, $status) {
            // Hapus jurnal tahap ini jika sudah ada dan masih DRAFT
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

            foreach ($detail as $d) {
                if (empty($d['akun_id']) || ($d['nominal'] ?? 0) <= 0) continue;

                DetailJurnal::create([
                    'jurnal_id' => $jurnal->id,
                    'akun_id'   => $d['akun_id'],
                    'tipe'      => $d['tipe'],
                    'nominal'   => $d['nominal'],
                ]);
            }

            return $jurnal->load('detailJurnal.akun');
        });
    }

    // ── Store semua tahap sekaligus (step 3 review) ────────────────────────

    public function storeAllTahap(
        Periode $periode,
        array   $semua,   // ['TUTUP_PENDAPATAN' => [...detail], ...]
        string  $tanggal,
        string  $status = 'DRAFT'
    ): array {
        $hasil = [];
        foreach ($semua as $tipe => $detail) {
            $hasil[$tipe] = $this->storeTahap($periode, $tipe, $detail, $tanggal, $status);
        }
        return $hasil;
    }

    // ── Post ───────────────────────────────────────────────────────────────

    public function postSemua(Periode $periode): bool|string
    {
        $jurnals = Jurnal::where('periode_id', $periode->id)
            ->where('jenis_jurnal', 'PENUTUP')
            ->where('status', 'DRAFT')
            ->get();

        if ($jurnals->isEmpty()) {
            return 'Tidak ada jurnal penutup draft yang bisa diposting';
        }

        DB::transaction(function () use ($jurnals) {
            $jurnals->each(fn($j) => $j->update(['status' => 'POSTED']));
        });

        return true;
    }

    public function post(Jurnal $jurnal): bool|string
    {
        if ($jurnal->status === 'POSTED') {
            return 'Jurnal sudah diposting';
        }

        $jurnal->load('detailJurnal');

        $totalDebit  = $jurnal->detailJurnal->where('tipe', 'DEBIT')->sum('nominal');
        $totalKredit = $jurnal->detailJurnal->where('tipe', 'KREDIT')->sum('nominal');

        if (round($totalDebit, 2) !== round($totalKredit, 2)) {
            return 'Total debit dan kredit harus sama sebelum diposting';
        }

        DB::transaction(fn() => $jurnal->update(['status' => 'POSTED']));

        return true;
    }

    // ── Delete ─────────────────────────────────────────────────────────────

    public function delete(Jurnal $jurnal): bool|string
    {
        if ($jurnal->status === 'POSTED') {
            return 'Jurnal yang sudah diposting tidak bisa dihapus';
        }

        DB::transaction(function () use ($jurnal) {
            $jurnal->detailJurnal()->delete();
            $jurnal->delete();
        });

        return true;
    }
}