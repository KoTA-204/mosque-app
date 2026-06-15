<?php

namespace App\Http\Controllers;

use App\Models\Akun;
use App\Models\DetailJurnal;
use App\Models\Periode;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class LaporanKeuanganController extends Controller
{
    // =========================================================================
    //  CORE HELPERS
    //  Semua logika keuangan bertumpu pada dua fungsi ini.
    //  Tidak ada nama akun atau kode akun spesifik di luar fungsi-fungsi ini.
    // =========================================================================

    /**
     * Hitung saldo semua akun dengan prefix kode_akun tertentu pada SATU periode.
     * Digunakan untuk akun flow (pendapatan & beban) yang di-reset tiap periode.
     *
     * @param  string  $prefix       e.g. '4-1', '4-2', '5-1', '5-'
     * @param  int     $periodeId
     * @param  string  $saldoNormal  'KREDIT' untuk pendapatan, 'DEBIT' untuk beban
     */
    private function saldoByPrefix(string $prefix, int $periodeId, string $saldoNormal = 'KREDIT'): float
    {
        $ids = Akun::where('kode_akun', 'like', $prefix . '%')->pluck('id');
        if ($ids->isEmpty()) return 0;

        return $this->hitungSaldo(
            DetailJurnal::whereIn('akun_id', $ids)
                ->whereHas('jurnal', fn($q) => $q
                    ->where('periode_id', $periodeId)
                    ->where('status', 'POSTED')
                    ->whereNull('tipe_penutupan')
                ),
            $saldoNormal
        );
    }

    /**
     * Hitung saldo KUMULATIF (semua periode s.d. periode ini) untuk akun dengan prefix.
     * Digunakan untuk akun posisi keuangan (aset, liabilitas, aset neto).
     *
     * @param  string  $prefix
     * @param  array   $periodeIds   Semua periode_id s.d. periode ini
     * @param  string  $saldoNormal
     */
    private function saldoKumulatifByPrefix(string $prefix, array $periodeIds, string $saldoNormal = 'DEBIT'): float
    {
        $ids = Akun::where('kode_akun', 'like', $prefix . '%')->pluck('id');
        if ($ids->isEmpty()) return 0;

        return $this->hitungSaldo(
            DetailJurnal::whereIn('akun_id', $ids)
                ->whereHas('jurnal', fn($q) => $q
                    ->whereIn('periode_id', $periodeIds)
                    ->where('status', 'POSTED')
                ),
            $saldoNormal
        );
    }

    private function saldoPosisiByPrefix(
        string $prefix,
        int $periodeId,
        string $saldoNormal = 'DEBIT'
    ): float
    {
        $ids = Akun::where('kode_akun', 'like', $prefix . '%')->pluck('id');

        if ($ids->isEmpty()) return 0;

        return $this->hitungSaldo(
            DetailJurnal::whereIn('akun_id', $ids)
                ->whereHas('jurnal', fn($q) => $q
                    ->where('periode_id', $periodeId)
                    ->where('status', 'POSTED')
                    ->where('jenis_jurnal', 'PEMBUKA')
                ),
            $saldoNormal
        );
    }

    /**
     * Hitung saldo KUMULATIF untuk satu kode_akun tepat (exact match).
     */
    private function saldoKumulatifByKode(string $kode, array $periodeIds, string $saldoNormal = 'DEBIT'): float
    {
        $akun = Akun::where('kode_akun', $kode)->first();
        if (!$akun) return 0;

        return $this->hitungSaldo(
            DetailJurnal::where('akun_id', $akun->id)
                ->whereHas('jurnal', fn($q) => $q
                    ->whereIn('periode_id', $periodeIds)
                    ->where('status', 'POSTED')
                ),
            $saldoNormal
        );
    }

    /**
     * Eksekusi query dan hitung saldo berdasarkan saldo normal.
     */
    private function hitungSaldo($query, string $saldoNormal): float
    {
        $row = $query->selectRaw("
            SUM(CASE WHEN tipe = 'DEBIT'  THEN nominal ELSE 0 END) AS total_debit,
            SUM(CASE WHEN tipe = 'KREDIT' THEN nominal ELSE 0 END) AS total_kredit
        ")->first();

        $d = (float)($row->total_debit  ?? 0);
        $k = (float)($row->total_kredit ?? 0);

        return $saldoNormal === 'DEBIT' ? ($d - $k) : ($k - $d);
    }

    /**
     * Ambil rincian saldo per akun leaf (bukan header/parent) untuk satu prefix,
     * pada satu periode. Digunakan untuk baris detail di semua laporan.
     *
     * Mengembalikan Collection of stdClass {kode_akun, nama_akun, saldo}.
     * Akun dengan saldo 0 difilter keluar.
     */
    private function getRincianAkun(string $prefix, int $periodeId, string $saldoNormal): Collection
    {
        // Leaf = punya parent_id (bukan header grup)
        $akuns = Akun::where('kode_akun', 'like', $prefix . '%')
            ->whereNotNull('parent_id')
            ->orderBy('kode_akun')
            ->get();

        return $akuns->map(function (Akun $akun) use ($periodeId, $saldoNormal) {
            $saldo = $this->hitungSaldo(
                DetailJurnal::where('akun_id', $akun->id)
                    ->whereHas('jurnal', fn($q) => $q
                        ->where('periode_id', $periodeId)
                        ->where('status', 'POSTED')
                        ->whereNull('tipe_penutupan')
                    ),
                $saldoNormal
            );

            return (object)[
                'kode_akun' => $akun->kode_akun,
                'nama_akun' => $akun->nama_akun,
                'saldo'     => $saldo,
            ];
        })->values();
    }

    /**
     * Ambil rincian saldo KUMULATIF per akun leaf untuk posisi keuangan.
     * Sama seperti getRincianAkun tapi menggunakan array periodeIds.
     */
    private function getRincianAkunKumulatif(string $prefix, array $periodeIds, string $saldoNormal): Collection
    {
        $akuns = Akun::where('kode_akun', 'like', $prefix . '%')
            ->whereNotNull('parent_id')
            ->orderBy('kode_akun')
            ->get();

        return $akuns->map(function (Akun $akun) use ($periodeIds, $saldoNormal) {
            $saldo = $this->hitungSaldo(
                DetailJurnal::where('akun_id', $akun->id)
                    ->whereHas('jurnal', fn($q) => $q
                        ->whereIn('periode_id', $periodeIds)
                        ->where('status', 'POSTED')
                    ),
                $saldoNormal
            );

            return (object)[
                'kode_akun' => $akun->kode_akun,
                'nama_akun' => $akun->nama_akun,
                'saldo'     => $saldo,
            ];
        })->values();
    }

    /**
     * Ambil semua periode_id dengan tanggal_akhir ≤ tanggal_akhir periode ini.
     */
    private function getPeriodeIdsUpTo(int $periodeId): array
    {
        $periode = Periode::find($periodeId);
        if (!$periode) return [$periodeId];

        return Periode::where('tanggal_akhir', '<=', $periode->tanggal_akhir)
            ->pluck('id')
            ->toArray();
    }

    /**
     * Resolve periode aktif dan periode sebelumnya dari request.
     * Return: [periodeList, periode, periodePrev, selectedId]
     */
    private function resolvePeriode(Request $request): array
    {
        $periodeList  = Periode::orderByDesc('tanggal_akhir')->get();
        $periodeAktif = Periode::where('status', true)->first();

        $selectedId = $request->get('periode_id', $periodeAktif?->id);
        $periode    = $selectedId ? Periode::find($selectedId) : $periodeAktif;

        $periodePrev = null;
        if ($periode) {
            $periodePrev = Periode::where('tipe', $periode->tipe)
                ->where('tanggal_akhir', '<', $periode->tanggal_awal)
                ->orderByDesc('tanggal_akhir')
                ->first();
        }

        return [$periodeList, $periode, $periodePrev, $selectedId];
    }

    // =========================================================================
    //  1. LAPORAN PENGHASILAN KOMPREHENSIF
    //
    //  Klasifikasi dari struktur CoA:
    //    4-1xxx → Tanpa Pembatasan  (prefix '4-1')
    //    4-2xxx → Dengan Pembatasan (prefix '4-2')
    //    5-xxxx → Beban, semua Tanpa Pembatasan
    //
    //  Format A ISAK 35 (kolom tunggal, komparatif):
    //    TANPA PEMBATASAN
    //      Pendapatan (rincian dinamis dari CoA)
    //      Beban      (rincian dinamis dari CoA, dalam kurung)
    //      Surplus Tanpa Pembatasan
    //    DENGAN PEMBATASAN
    //      Pendapatan (rincian dinamis dari CoA)
    //      Surplus Dengan Pembatasan
    //    PENGHASILAN KOMPREHENSIF LAIN
    //    TOTAL PENGHASILAN KOMPREHENSIF
    // =========================================================================

    public function penghasilanKomprehensif(Request $request)
    {
        [$periodeList, $periode, $periodePrev, $selectedId] = $this->resolvePeriode($request);

        $data     = $this->buildPenghasilanKomprehensif($periode?->id);
        $dataPrev = $periodePrev ? $this->buildPenghasilanKomprehensif($periodePrev->id) : null;

        return view('pages.laporan.penghasilan-komprehensif', compact(
            'periodeList', 'periode', 'periodePrev', 'data', 'dataPrev'
        ))->with('selectedPeriodeId', $selectedId);
    }

    private function buildPenghasilanKomprehensif(?int $periodeId): array
    {
        if (!$periodeId) {
            return [
                // Tanpa Pembatasan
                'pendapatanTanpaPembatasan' => 0,
                'rincianTanpaPembatasan'   => collect(),
                // Beban (semua grup, dinamis dari CoA)
                'grupBeban'                => collect(),
                'jumlahBeban'              => 0,
                'surplusTanpaPembatasan'   => 0,
                // Dengan Pembatasan
                'pendapatanDenganPembatasan' => 0,
                'rincianDenganPembatasan'    => collect(),
                'surplusDenganPembatasan'    => 0,
                // Total
                'surplusDefisit'        => 0,
                'penghasilanKomprLain'  => 0,
                'totalKomprehensif'     => 0,
            ];
        }

        // ── TANPA PEMBATASAN (4-1xxx) ──────────────────────────────────────
        $pendapatanTanpaPembatasan = $this->saldoByPrefix('4-1', $periodeId, 'KREDIT');
        $rincianTanpaPembatasan    = $this->getRincianAkun('4-1', $periodeId, 'KREDIT');

        // ── BEBAN — dinamis: baca semua grup akun di bawah kategori '5' ───
        // Ambil semua akun header (parent_id = null) di kategori beban (kode 5-xxxx)
        // Setiap header = satu grup beban (Operasional, Kegiatan, Penyusutan, dll.)
        $headerBeban = Akun::where('kode_akun', 'like', '5-%')
            ->whereNull('parent_id')
            ->orderBy('kode_akun')
            ->get();

        $grupBeban   = collect();
        $jumlahBeban = 0;

        foreach ($headerBeban as $header) {
            $kode = rtrim($header->kode_akun, '0');
            $rincian = $this->getRincianAkun($kode, $periodeId, 'DEBIT');
            $total   = $rincian->sum('saldo');

            $grupBeban->push((object)[
                'kode_akun'  => $header->kode_akun,
                'nama_akun'  => $header->nama_akun,
                'rincian'    => $rincian,
                'total'      => $total,
            ]);

            $jumlahBeban += $total;
        }

         // Surplus/defisit = pendapatan - beban

        $surplusTanpaPembatasan = $pendapatanTanpaPembatasan - $jumlahBeban;

        // ── DENGAN PEMBATASAN (4-2xxx) ─────────────────────────────────────
        // Sesuai ISAK 35: beban tidak dibebankan ke dana terikat
        $pendapatanDenganPembatasan = $this->saldoByPrefix('4-2', $periodeId, 'KREDIT');
        $rincianDenganPembatasan    = $this->getRincianAkun('4-2', $periodeId, 'KREDIT');
        $surplusDenganPembatasan    = $pendapatanDenganPembatasan;

        // ── TOTAL ──────────────────────────────────────────────────────────
        $surplusDefisit       = $surplusTanpaPembatasan + $surplusDenganPembatasan;
        $penghasilanKomprLain = 0; 
        $totalKomprehensif    = $surplusDefisit + $penghasilanKomprLain;

        return compact(
            'pendapatanTanpaPembatasan', 'rincianTanpaPembatasan',
            'grupBeban', 'jumlahBeban', 'surplusTanpaPembatasan',
            'pendapatanDenganPembatasan', 'rincianDenganPembatasan', 'surplusDenganPembatasan',
            'surplusDefisit', 'penghasilanKomprLain', 'totalKomprehensif'
        );
    }

    // =========================================================================
    //  2. LAPORAN POSISI KEUANGAN
    //
    //  Aset Neto dua kelas sesuai CoA:
    //    3-1xxx → Tanpa Pembatasan
    //    3-2xxx → Dengan Pembatasan
    //
    //  Akun aset & liabilitas dibaca DINAMIS dari CoA berdasarkan
    //  header grup (parent_id = null). Tidak ada nama akun hardcode.
    // =========================================================================

    public function posisiKeuangan(Request $request)
    {
        [$periodeList, $periode, $periodePrev, $selectedId] = $this->resolvePeriode($request);

        $data     = $this->buildPosisiKeuangan($periode?->id);
        $dataPrev = $periodePrev ? $this->buildPosisiKeuangan($periodePrev->id) : null;

        return view('pages.laporan.posisi-keuangan', compact(
            'periodeList', 'periode', 'periodePrev', 'data', 'dataPrev'
        ))->with('selectedPeriodeId', $selectedId);
    }

    private function buildPosisiKeuangan(?int $periodeId): array
    {
        if (!$periodeId) {
            return [
                'grupAsetLancar'           => collect(),
                'jumlahAsetLancar'         => 0,
                'grupAsetTetap'            => collect(),
                'jumlahAsetTetap'          => 0,
                'jumlahAset'               => 0,
                'grupLiabilitas'           => collect(),
                'jumlahLiabilitas'         => 0,
                'asetNetoTanpaPembatasan'  => 0,
                'rincianAsetNetoTanpa'     => collect(),
                'asetNetoDenganPembatasan' => 0,
                'rincianAsetNetoDengan'    => collect(),
                'jumlahAsetNeto'           => 0,
            ];
        }

        $pids = $this->getPeriodeIdsUpTo($periodeId);

        // ── ASET LANCAR (1-1xxx) — dinamis per sub-akun ───────────────────
        // Ambil semua leaf di bawah 1-1000, kelompokkan secara flat
        $rincianAsetLancar = $this->getRincianAkunKumulatif('1-1', $pids, 'DEBIT');

        // Khusus akumulasi penyusutan (saldo_normal KREDIT) sudah tidak ada
        // di aset lancar, jadi tidak perlu perlakuan khusus di sini.
        $jumlahAsetLancar = $rincianAsetLancar->sum('saldo');

        // ── ASET TETAP (1-2xxx) — dinamis, tangani akumulasi penyusutan ───
        // Baca semua leaf 1-2xxx. Akun akumulasi penyusutan (KREDIT) otomatis
        // ditangani oleh getRincianAkunKumulatif dengan saldo_normal dari DB.
        $akunAsetTetap = Akun::where('kode_akun', 'like', '1-2%')
            ->whereNotNull('parent_id')
            ->orderBy('kode_akun')
            ->get();

        $rincianAsetTetap = $akunAsetTetap->map(function (Akun $akun) use ($pids) {
            // Baca saldo_normal dari database — DEBIT untuk aset, KREDIT untuk akumulasi
            $saldo = $this->hitungSaldo(
                DetailJurnal::where('akun_id', $akun->id)
                    ->whereHas('jurnal', fn($q) => $q
                        ->whereIn('periode_id', $pids)
                        ->where('status', 'POSTED')
                    ),
                $akun->saldo_normal // ambil dari field di tabel akun
            );

            // Akumulasi penyusutan = pengurang → tampilkan negatif
            $isAkumulasi = $akun->saldo_normal === 'KREDIT';

            return (object)[
                'kode_akun'   => $akun->kode_akun,
                'nama_akun'   => $akun->nama_akun,
                'saldo'       => $isAkumulasi ? -$saldo : $saldo, // negatif = pengurang
                'is_akumulasi'=> $isAkumulasi,
            ];
        })->values();

        $jumlahAsetTetap = $rincianAsetTetap->sum('saldo');
        $jumlahAset      = $jumlahAsetLancar + $jumlahAsetTetap;

        // ── LIABILITAS (2-xxxx) — dinamis per grup header ─────────────────
        $headerLiab = Akun::where('kode_akun', 'like', '2-%')
            ->whereNull('parent_id')
            ->orderBy('kode_akun')
            ->get();

        $grupLiabilitas = collect();
        $jumlahLiabilitas = 0;

        foreach ($headerLiab as $header) {
            $kode = rtrim($header->kode_akun, '0');
            $rincian = $this->getRincianAkunKumulatif($kode, $pids, 'KREDIT');
            $total   = $rincian->sum('saldo');

            if ($total == 0) continue;

            $grupLiabilitas->push((object)[
                'kode_akun' => $header->kode_akun,
                'nama_akun' => $header->nama_akun,
                'rincian'   => $rincian,
                'total'     => $total,
            ]);

            $jumlahLiabilitas += $total;
        }

        // ── ASET NETO ─────────────────────────────────────────────────────
        // Prefix '3-1' = Tanpa Pembatasan, '3-2' = Dengan Pembatasan
        $asetNetoTanpaPembatasan  = $this->saldoKumulatifByPrefix('3-1', $pids, 'KREDIT');
        $rincianAsetNetoTanpa     = $this->getRincianAkunKumulatif('3-1', $pids, 'KREDIT');

        $asetNetoDenganPembatasan = $this->saldoKumulatifByPrefix('3-2', $pids, 'KREDIT');
        $rincianAsetNetoDengan    = $this->getRincianAkunKumulatif('3-2', $pids, 'KREDIT');

        $jumlahAsetNeto = $asetNetoTanpaPembatasan + $asetNetoDenganPembatasan;

        return compact(
            'rincianAsetLancar', 'jumlahAsetLancar',
            'rincianAsetTetap',  'jumlahAsetTetap', 'jumlahAset',
            'grupLiabilitas',    'jumlahLiabilitas',
            'asetNetoTanpaPembatasan', 'asetNetoDenganPembatasan',
            'jumlahAsetNeto'
        );
    }

    // =========================================================================
    //  3. LAPORAN PERUBAHAN ASET NETO
    //
    //  Format ISAK 35: dua kolom (Tanpa Pembatasan | Dengan Pembatasan | Jumlah)
    //    Saldo Awal
    //    + Surplus tahun berjalan
    //    ± Aset neto dibebaskan dari pembatasan (reklasifikasi)
    //    + Penghasilan Komprehensif Lain
    //    = Saldo Akhir
    // =========================================================================

    public function perubahanAsetNeto(Request $request)
    {
        [$periodeList, $periode, $periodePrev, $selectedId] = $this->resolvePeriode($request);

        $data = $this->buildPerubahanAsetNeto($periode, $periodePrev);
        $dataPrev = $periodePrev ? $this->buildPerubahanAsetNeto($periodePrev, null) : null;

        return view('pages.laporan.perubahan-aset-neto', compact(
            'periodeList', 'periode', 'periodePrev', 'data', 'dataPrev'
        ))->with('selectedPeriodeId', $selectedId);
    }

    private function buildPerubahanAsetNeto(?Periode $periode, ?Periode $periodePrev): array
    {
        $empty = [
            'saldoAwalTanpa'   => 0, 'saldoAwalDengan'   => 0, 'totalSaldoAwal'   => 0,
            'surplusTanpa'     => 0, 'surplusDengan'     => 0,
            'rincianTanpa'     => collect(), 'rincianDengan' => collect(),
            'dibebaskan'       => 0,
            'pkl'              => 0,
            'saldoAkhirTanpa'  => 0, 'saldoAkhirDengan'  => 0, 'totalSaldoAkhir'  => 0,
        ];

        if (!$periode) return $empty;

        // ── Saldo Awal = Posisi Keuangan akhir periode sebelumnya ──────────
        $saldoAwalTanpa  = 0;
        $saldoAwalDengan = 0;

        if ($periodePrev) {
            $prevPos         = $this->buildPosisiKeuangan($periodePrev->id);
            $saldoAwalTanpa  = $prevPos['asetNetoTanpaPembatasan'];
            $saldoAwalDengan = $prevPos['asetNetoDenganPembatasan'];
        } else {
            // Periode pertama → ambil dari jurnal pembuka di periode ini saja
            $saldoAwalTanpa  = $this->saldoPosisiByPrefix('3-1', $periode->id, 'KREDIT');
            $saldoAwalDengan = $this->saldoPosisiByPrefix('3-2', $periode->id, 'KREDIT');
        }

        // ── Surplus dari laporan penghasilan komprehensif ──────────────────
        $peng = $this->buildPenghasilanKomprehensif($periode->id);

        $surplusTanpa  = $peng['surplusTanpaPembatasan'];
        $surplusDengan = $peng['surplusDenganPembatasan'];

        // ── Saldo Akhir ────────────────────────────────────────────────────
        $saldoAkhirTanpa  = $saldoAwalTanpa  + $surplusTanpa;
        $saldoAkhirDengan = $saldoAwalDengan + $surplusDengan;
        $totalSaldoAwal   = $saldoAwalTanpa  + $saldoAwalDengan;
        $totalSaldoAkhir  = $saldoAkhirTanpa + $saldoAkhirDengan;

        return compact(
            'saldoAwalTanpa',  'saldoAwalDengan',  'totalSaldoAwal',
            'surplusTanpa',    'surplusDengan',
            'saldoAkhirTanpa', 'saldoAkhirDengan', 'totalSaldoAkhir'
        );
    }

    // =========================================================================
    //  4. LAPORAN ARUS KAS
    //
    //  Format ISAK 35: tiga kolom (Aktivitas Operasi | Aktivitas Investasi | Aktivitas Pendanaan)
    // ==>> Untuk laporan arus kas, kita tidak bisa hanya mengandalkan struktur CoA karena
    //      aktivitasnya tidak selalu berhubungan langsung dengan prefix kode_akun tertentu.
    //      Oleh karena itu, kita akan menggunakan pendekatan berbasis tagging:
    //      1. Tambahkan field 'tag' di tabel akun untuk menandaii aktivitas (operasi, investasi, pendanaan).
    //      2. Saat menghitung arus kas, filter akun berdasarkan tag ini, bukan hanya kode_akun.
    //      3. Ini memungkinkan fleksibilitas penuh dalam klasifikasi akun untuk laporan arus kas, tanpa mengubah struktur CoA yang sudah ada.
    // =========================================================================

    public function arusKas(Request $request)
    {
        [$periodeList, $periode, $periodePrev, $selectedId] = $this->resolvePeriode($request);

        $data     = $this->buildArusKas($periode);
        $dataPrev = $periodePrev ? $this->buildArusKas($periodePrev) : null;

        return view('pages.laporan.arus-kas', compact(
            'periodeList', 'periode', 'periodePrev', 'data', 'dataPrev'
        ))->with('selectedPeriodeId', $selectedId);
    }

    private function buildArusKas(?Periode $periode): array
    {
        $empty = [
            'penerimaanOperasional' => collect(),
            'pengeluaranOperasional' => collect(),
            'kasNetoOperasional'    => 0,
            'pengeluaranInvestasi'  => collect(),
            'penerimaanInvestasi'   => collect(),
            'kasNetoInvestasi'      => 0,
            'penerimaanPendanaan'   => collect(),
            'penyaluranPendanaan'   => collect(),
            'kasNetoPendanaan'      => 0,
            'kenaikanNeto'          => 0,
            'kasAwal'               => 0,
            'kasAkhir'              => 0,
        ];

        if (!$periode) return $empty;

        $pid  = $periode->id;
        $pids = $this->getPeriodeIdsUpTo($pid);

        // Periode sebelumnya untuk hitung delta aset tetap
        $periodePrevObj = Periode::where('tipe', $periode->tipe)
            ->where('tanggal_akhir', '<', $periode->tanggal_awal)
            ->orderByDesc('tanggal_akhir')
            ->first();

        // ── OPERASIONAL: PENERIMAAN ─────────────────────────────────────────
        // Sumber: semua akun 4-1 (pendapatan tanpa pembatasan), periode ini
        $penerimaanOperasional = $this->getRincianAkun('4-1', $pid, 'KREDIT')
            ->filter(fn($r) => $r->saldo != 0)->values();

        $totalPenerimaanOp = $penerimaanOperasional->sum('saldo');

        // ── OPERASIONAL: PENGELUARAN ────────────────────────────────────────
        // Sumber: semua akun 5- KECUALI yang mengandung "penyusutan"/"depresiasi"
        // (non-kas tidak masuk arus kas metode langsung)
        $semuaBeban = collect();
        $headerBeban = Akun::where('kode_akun', 'like', '5-%')
            ->whereNull('parent_id')
            ->orderBy('kode_akun')
            ->get();

        foreach ($headerBeban as $header) {
            $kode    = rtrim($header->kode_akun, '0');
            $rincian = $this->getRincianAkun($kode, $pid, 'DEBIT')
                ->filter(fn($r) => $r->saldo != 0)
                ->filter(fn($r) => !preg_match('/penyusutan|depresiasi/i', $r->nama_akun))
                ->values();
            foreach ($rincian as $r) {
                $semuaBeban->push($r);
            }
        }

        // Pisahkan: beban operasional vs beban yang terkait dana terikat
        // (beban zakat/santunan → masuk Pendanaan, bukan Operasional)
        $pengeluaranOperasional = $semuaBeban
            ->filter(fn($r) => !preg_match('/zakat|santunan|wakaf/i', $r->nama_akun))
            ->values();

        $penyaluranPendanaan = $semuaBeban
            ->filter(fn($r) => preg_match('/zakat|santunan|wakaf/i', $r->nama_akun))
            ->values();

        $totalPengeluaranOp  = $pengeluaranOperasional->sum('saldo');
        $kasNetoOperasional  = $totalPenerimaanOp - $totalPengeluaranOp;

        // ── INVESTASI ───────────────────────────────────────────────────────
        // Pengeluaran: delta aset tetap (1-2, saldo_normal DEBIT) vs periode lalu
        $asetTetapSekarang = Akun::where('kode_akun', 'like', '1-2%')
            ->whereNotNull('parent_id')
            ->where('saldo_normal', 'DEBIT')
            ->get()
            ->map(fn($akun) => $this->hitungSaldo(
                DetailJurnal::where('akun_id', $akun->id)
                    ->whereHas('jurnal', fn($q) => $q
                        ->whereIn('periode_id', $pids)
                        ->where('status', 'POSTED')
                    ),
                'DEBIT'
            ))->sum();

        $asetTetapSebelumnya = 0;
        if ($periodePrevObj) {
            $prevPids = $this->getPeriodeIdsUpTo($periodePrevObj->id);
            $asetTetapSebelumnya = Akun::where('kode_akun', 'like', '1-2%')
                ->whereNotNull('parent_id')
                ->where('saldo_normal', 'DEBIT')
                ->get()
                ->map(fn($akun) => $this->hitungSaldo(
                    DetailJurnal::where('akun_id', $akun->id)
                        ->whereHas('jurnal', fn($q) => $q
                            ->whereIn('periode_id', $prevPids)
                            ->where('status', 'POSTED')
                        ),
                    'DEBIT'
                ))->sum();
        }

        $deltaAsetTetap = $asetTetapSekarang - $asetTetapSebelumnya;

        // Pengeluaran investasi = pembelian (delta positif = beli)
        $pengeluaranInvestasi = collect();
        if ($deltaAsetTetap > 0) {
            $pengeluaranInvestasi->push((object)[
                'nama_akun' => 'Pembelian peralatan & inventaris',
                'saldo'     => $deltaAsetTetap,
            ]);
        }

        // Penerimaan investasi = akun pendapatan yang mengandung "penjualan aset"
        $penerimaanInvestasi = $this->getRincianAkun('4-1', $pid, 'KREDIT')
            ->filter(fn($r) => preg_match('/penjualan aset|divestasi/i', $r->nama_akun))
            ->values();

        $kasNetoInvestasi = $penerimaanInvestasi->sum('saldo') - $pengeluaranInvestasi->sum('saldo');

        // ── PENDANAAN ───────────────────────────────────────────────────────
        // Penerimaan: semua 4-2 (terikat): zakat, wakaf, infak pembangunan
        $penerimaanPendanaan = $this->getRincianAkun('4-2', $pid, 'KREDIT')
            ->filter(fn($r) => $r->saldo != 0)->values();

        $kasNetoPendanaan = $penerimaanPendanaan->sum('saldo') - $penyaluranPendanaan->sum('saldo');

        // ── REKONSILIASI ────────────────────────────────────────────────────
        $kenaikanNeto = $kasNetoOperasional + $kasNetoInvestasi + $kasNetoPendanaan;

        // Kas Awal: saldo akun 1-1 (bukan piutang) s.d. periode lalu
        $kasAwal = 0;
        if ($periodePrevObj) {
            $prevPids2 = $this->getPeriodeIdsUpTo($periodePrevObj->id);
            $kasAwal = $this->getRincianAkunKumulatif('1-1', $prevPids2, 'DEBIT')
                ->filter(fn($r) => !preg_match('/piutang/i', $r->nama_akun))
                ->sum('saldo');
        }

        $kasAkhir = $kasAwal + $kenaikanNeto;

        return compact(
            'penerimaanOperasional', 'pengeluaranOperasional', 'kasNetoOperasional',
            'penerimaanInvestasi', 'pengeluaranInvestasi', 'kasNetoInvestasi',
            'penerimaanPendanaan', 'penyaluranPendanaan', 'kasNetoPendanaan',
            'kenaikanNeto', 'kasAwal', 'kasAkhir'
        );
    }

    // =========================================================================
    //  5. CATATAN ATAS LAPORAN KEUANGAN (CALK)
    // =========================================================================

    public function calk(Request $request)
    {
        [$periodeList, $periode, $periodePrev, $selectedId] = $this->resolvePeriode($request);

        $data = $this->buildCalk($periode);

        return view('pages.laporan.calk', compact(
            'periodeList', 'periode', 'periodePrev', 'data'
        ))->with('selectedPeriodeId', $selectedId);
    }

    private function buildCalk(?Periode $periode): array
    {
        if (!$periode) {
            return [
                'kasSetaraKas'      => collect(),
                'totalKas'          => 0,
                'piutang'           => collect(),
                'totalPiutang'      => 0,
                'asetTetap'         => collect(),
                'totalHargaPerolehan'   => 0,
                'totalAkumulasi'        => 0,
                'totalNilaiBuku'        => 0,
                'liabilitas'        => collect(),
                'totalLiabilitas'   => 0,
                'pendapatanTanpa'   => collect(),
                'totalPendapatanTanpa' => 0,
                'beban'             => collect(),
                'totalBeban'        => 0,
                'asetNeto'          => [],
                'arusKas'           => [],
            ];
        }

        $pids    = $this->getPeriodeIdsUpTo($periode->id);
        $pid     = $periode->id;

        // ── 1. Kas dan Setara Kas (1-1xxx, DEBIT kumulatif) ───────────────────
        $kasSetaraKas = $this->getRincianAkunKumulatif('1-1', $pids, 'DEBIT')
            ->filter(fn($r) => $r->saldo != 0)->values();
        $totalKas = $kasSetaraKas->sum('saldo');

        // ── 2. Piutang (1-1xxx subset: nama mengandung "piutang") ─────────────
        // Pisahkan: kas = bukan piutang, piutang = yang mengandung kata piutang
        $piutang = $kasSetaraKas->filter(fn($r) => stripos($r->nama_akun, 'piutang') !== false)->values();
        $kasSetaraKas = $kasSetaraKas->filter(fn($r) => stripos($r->nama_akun, 'piutang') === false)->values();
        $totalKas = $kasSetaraKas->sum('saldo');
        $totalPiutang = $piutang->sum('saldo');

        // ── 3. Aset Tetap (1-2xxx) ─────────────────────────────────────────────
        $akunAsetTetap = Akun::where('kode_akun', 'like', '1-2%')
            ->whereNotNull('parent_id')
            ->orderBy('kode_akun')
            ->get();

        $asetTetap = $akunAsetTetap->map(function (Akun $akun) use ($pids) {
            $saldo = $this->hitungSaldo(
                DetailJurnal::where('akun_id', $akun->id)
                    ->whereHas('jurnal', fn($q) => $q
                        ->whereIn('periode_id', $pids)
                        ->where('status', 'POSTED')
                    ),
                $akun->saldo_normal
            );
            $isAkumulasi = $akun->saldo_normal === 'KREDIT';
            return (object)[
                'nama_akun'    => $akun->nama_akun,
                'is_akumulasi' => $isAkumulasi,
                'harga_perolehan' => $isAkumulasi ? 0 : $saldo,
                'akumulasi'       => $isAkumulasi ? $saldo : 0,
                'nilai_buku'      => $isAkumulasi ? -$saldo : $saldo,
            ];
        })->values();

        $totalHargaPerolehan = $asetTetap->sum('harga_perolehan');
        $totalAkumulasi      = $asetTetap->sum('akumulasi');
        $totalNilaiBuku      = $asetTetap->sum('nilai_buku');

        // ── 4. Liabilitas (2-xxxx) ─────────────────────────────────────────────
        $liabilitas = collect();
        $headerLiab = Akun::where('kode_akun', 'like', '2-%')
            ->whereNull('parent_id')
            ->orderBy('kode_akun')
            ->get();
        foreach ($headerLiab as $header) {
            $kode    = rtrim($header->kode_akun, '0');
            $rincian = $this->getRincianAkunKumulatif($kode, $pids, 'KREDIT')
                ->filter(fn($r) => $r->saldo != 0)->values();
            foreach ($rincian as $r) {
                $liabilitas->push($r);
            }
        }
        $totalLiabilitas = $liabilitas->sum('saldo');

        // ── 5. Pendapatan (4-1xxx = tanpa pembatasan, periode ini) ────────────
        $pendapatanTanpa      = $this->getRincianAkun('4-1', $pid, 'KREDIT')
            ->filter(fn($r) => $r->saldo != 0)->values();
        $totalPendapatanTanpa = $pendapatanTanpa->sum('saldo');

        // ── 6. Beban (5-xxxx) ──────────────────────────────────────────────────
        $beban = collect();
        $headerBeban = Akun::where('kode_akun', 'like', '5-%')
            ->whereNull('parent_id')
            ->orderBy('kode_akun')
            ->get();
        foreach ($headerBeban as $header) {
            $kode    = rtrim($header->kode_akun, '0');
            $rincian = $this->getRincianAkun($kode, $pid, 'DEBIT')
                ->filter(fn($r) => $r->saldo != 0)->values();
            foreach ($rincian as $r) {
                $beban->push($r);
            }
        }
        $totalBeban = $beban->sum('saldo');

        // ── 7. Aset Neto (dari buildPerubahanAsetNeto) ─────────────────────────
        $periodeObj  = $periode;
        $periodePrevObj = Periode::where('tipe', $periode->tipe)
            ->where('tanggal_akhir', '<', $periode->tanggal_awal)
            ->orderByDesc('tanggal_akhir')
            ->first();
        $pan = $this->buildPerubahanAsetNeto($periodeObj, $periodePrevObj);

        $asetNeto = [
            'saldoAwalTanpa'   => $pan['saldoAwalTanpa'],
            'saldoAwalDengan'  => $pan['saldoAwalDengan'],
            'totalSaldoAwal'   => $pan['totalSaldoAwal'],
            'surplusTanpa'     => $pan['surplusTanpa'],
            'surplusDengan'    => $pan['surplusDengan'],
            'saldoAkhirTanpa'  => $pan['saldoAkhirTanpa'],
            'saldoAkhirDengan' => $pan['saldoAkhirDengan'],
            'totalSaldoAkhir'  => $pan['totalSaldoAkhir'],
        ];

        // ── 8. Arus Kas (estimasi dari perubahan kas) ──────────────────────────
        // Kas operasional = surplus periode + non-kas (penyusutan)
        $peng = $this->buildPenghasilanKomprehensif($pid);
        $surplusDefisit = $peng['surplusDefisit'];

        // Penyusutan = beban akun mengandung 'penyusutan'
        $penyusutan = $beban->filter(fn($r) => stripos($r->nama_akun, 'penyusutan') !== false)->sum('saldo');

        // Perubahan kas & setara kas
        $kasAkhir = $totalKas;
        $kasAwal  = 0;
        if ($periodePrevObj) {
            $prevPids = $this->getPeriodeIdsUpTo($periodePrevObj->id);
            $prevKasRincian = $this->getRincianAkunKumulatif('1-1', $prevPids, 'DEBIT')
                ->filter(fn($r) => stripos($r->nama_akun, 'piutang') === false);
            $kasAwal = $prevKasRincian->sum('saldo');
        }

        $kasNeto = $kasAkhir - $kasAwal;
        // Investasi = perubahan aset tetap (harga perolehan)
        $prevHargaPerolehan = 0;
        if ($periodePrevObj) {
            $prevPids2   = $this->getPeriodeIdsUpTo($periodePrevObj->id);
            $prevAsetTetap = Akun::where('kode_akun', 'like', '1-2%')
                ->whereNotNull('parent_id')
                ->where('saldo_normal', 'DEBIT')
                ->orderBy('kode_akun')
                ->get()
                ->map(function (Akun $akun) use ($prevPids2) {
                    return $this->hitungSaldo(
                        DetailJurnal::where('akun_id', $akun->id)
                            ->whereHas('jurnal', fn($q) => $q
                                ->whereIn('periode_id', $prevPids2)
                                ->where('status', 'POSTED')
                            ),
                        'DEBIT'
                    );
                })->sum();
            $prevHargaPerolehan = $prevAsetTetap;
        }
        $kasInvestasi   = -($totalHargaPerolehan - $prevHargaPerolehan);
        $kasOperasional = $kasNeto - $kasInvestasi;

        $arusKas = [
            'operasional' => $kasOperasional,
            'investasi'   => $kasInvestasi,
            'pendanaan'   => 0,
            'kenaikan'    => $kasNeto,
            'kasAwal'     => $kasAwal,
            'kasAkhir'    => $kasAkhir,
        ];

        return compact(
            'kasSetaraKas', 'totalKas',
            'piutang', 'totalPiutang',
            'asetTetap', 'totalHargaPerolehan', 'totalAkumulasi', 'totalNilaiBuku',
            'liabilitas', 'totalLiabilitas',
            'pendapatanTanpa', 'totalPendapatanTanpa',
            'beban', 'totalBeban',
            'asetNeto', 'arusKas'
        );
    }
}
