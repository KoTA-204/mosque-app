<?php

namespace App\Http\Controllers;

use App\Models\Dompet;
use App\Models\Kegiatan;
use App\Models\Transaksi;
use App\Models\Periode; 
use App\Models\DetailJurnal;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboard) {}

    // ── Dashboard Internal ─────────────────────────────────────────────────
    public function index(Request $request)
    {
        [$periodeAktif, $periodeSebelumnya] = $this->dashboard->resolvePeriodeAktif();

        if (!$periodeAktif) {
            // handle jika belum ada periode
        }

        $approved = fn() => Transaksi::where(function ($q) {
            $q->whereNull('status_approval')
            ->orWhere('status_approval', 'APPROVED');
        });

        $now      = Carbon::now();
        $tahunIni = $now->year;
        $bulanIni = $now->month;

        $pids     = $this->dashboard->getPeriodeIdsUpTo($periodeAktif->id);
        $pidsPrev = $periodeSebelumnya
            ? $this->dashboard->getPeriodeIdsUpTo($periodeSebelumnya->id)
            : [];

        // ── SALDO AWAL = Aset Neto periode sebelumnya (atau jurnal pembuka jika pertama) ──
        $saldoAwal = $this->dashboard->saldoAwalAsetNeto(
            $periodeAktif->id,
            $periodeSebelumnya?->id
        );

        // ── PEMASUKAN = akun pendapatan 4-1xxx + 4-2xxx periode aktif ────────────
        $pemasukanBulanIni = $this->dashboard->totalPendapatan($periodeAktif->id);

        // ── PENGELUARAN = akun beban 5-xxxx periode aktif ────────────────────────
        $pengeluaranBulanIni = $this->dashboard->totalBeban($periodeAktif->id);

        // ── SALDO AKHIR = Saldo Awal + Pemasukan - Pengeluaran ───────────────────
        $surplus    = $pemasukanBulanIni - $pengeluaranBulanIni;
        $saldoAkhir = $saldoAwal + $surplus;

        // ── Periode sebelumnya untuk perbandingan ─────────────────────────────────
        $pemasukanBulanLalu   = $periodeSebelumnya ? $this->dashboard->totalPendapatan($periodeSebelumnya->id) : 0;
        $pengeluaranBulanLalu = $periodeSebelumnya ? $this->dashboard->totalBeban($periodeSebelumnya->id) : 0;

        // Saldo awal bulan lalu = aset neto periode sebelum-sebelumnya
        $saldoAwalBulanLalu = 0;
        if ($periodeSebelumnya) {
            $prevPrev = Periode::where('tipe', $periodeSebelumnya->tipe)
                ->where('tanggal_akhir', '<', $periodeSebelumnya->tanggal_awal)
                ->orderByDesc('tanggal_akhir')
                ->first();

            $saldoAwalBulanLalu = $this->dashboard->saldoAwalAsetNeto(
                $periodeSebelumnya->id,
                $prevPrev?->id
            );
        }

        // ── Saldo per dompet (tetap dari transaksi, bukan akuntansi) ─────────────
        $dompetList = Dompet::all();

        $mutasiPerDompet = $approved()
            ->whereIn('dompet_id', $dompetList->pluck('id'))
            ->select('dompet_id', DB::raw("SUM(CASE WHEN jenis_transaksi = 'PEMASUKAN' THEN jumlah ELSE -jumlah END) as mutasi"))
            ->groupBy('dompet_id')
            ->pluck('mutasi', 'dompet_id');

        $saldoDompet = $dompetList->map(function (Dompet $dompet) use ($mutasiPerDompet) {
            return (object) [
                'id'             => $dompet->id,
                'nama_dompet'    => $dompet->nama_dompet,
                'jenis_dompet'   => $dompet->jenis_dompet,
                'nama_bank'      => $dompet->nama_bank,
                'nomor_rekening' => $dompet->nomor_rekening,
                'saldo'          => (float) $dompet->saldo_awal + (float) ($mutasiPerDompet[$dompet->id] ?? 0),
            ];
        });

        // ── Distribusi Pemasukan per Akun Pendapatan ──────────────────────────────
        $distribusiPemasukan = DetailJurnal::whereHas('jurnal', fn($q) => $q
                ->where('periode_id', $periodeAktif->id)
                ->where('status', 'POSTED')
                ->whereNull('tipe_penutupan')
            )
            ->whereHas('akun', fn($q) => $q
                ->where(fn($q2) => $q2
                    ->where('kode_akun', 'like', '4-1%')
                    ->orWhere('kode_akun', 'like', '4-2%')
                )
                ->whereNotNull('parent_id')
            )
            ->where('tipe', 'KREDIT')
            ->join('akun', 'detail_jurnal.akun_id', '=', 'akun.id')
            ->groupBy('akun.id', 'akun.nama_akun')
            ->select('akun.nama_akun', DB::raw('SUM(detail_jurnal.nominal) as total'))
            ->orderByDesc('total')
            ->get()
            ->map(fn($r) => (object)['nama_akun' => $r->nama_akun, 'total' => (float)$r->total]);

        // ── Distribusi Pengeluaran per Akun Beban ────────────────────────────────
        $distribusiPengeluaran = DetailJurnal::whereHas('jurnal', fn($q) => $q
                ->where('periode_id', $periodeAktif->id)
                ->where('status', 'POSTED')
                ->whereNull('tipe_penutupan')
            )
            ->whereHas('akun', fn($q) => $q
                ->where('kode_akun', 'like', '5-%')
                ->whereNotNull('parent_id')
            )
            ->where('tipe', 'DEBIT')
            ->join('akun', 'detail_jurnal.akun_id', '=', 'akun.id')
            ->groupBy('akun.id', 'akun.nama_akun')
            ->select('akun.nama_akun', DB::raw('SUM(detail_jurnal.nominal) as total'))
            ->orderByDesc('total')
            ->get()
            ->map(fn($r) => (object)['nama_akun' => $r->nama_akun, 'total' => (float)$r->total]);

        // ── Grafik 8 periode terakhir ─────────────────────────────────────────────
        $periodeList8 = Periode::where('tipe', $periodeAktif->tipe)
            ->where('tanggal_akhir', '<=', $periodeAktif->tanggal_akhir)
            ->orderByDesc('tanggal_akhir')
            ->take(8)
            ->get()
            ->sortBy('tanggal_awal')
            ->values();

        $grafikData = $periodeList8->map(fn(Periode $p) => [
            'label'       => Carbon::parse($p->tanggal_awal)->translatedFormat('M Y'),
            'pemasukan'   => $this->dashboard->totalPendapatan($p->id),
            'pengeluaran' => $this->dashboard->totalBeban($p->id),
        ])->values();

        // ── Laporan ringkasan ─────────────────────────────────────────────────────
        $totalAsetLancar = $this->dashboard->totalAsetLancar($pids);
        $totalAsetTetap  = $this->dashboard->totalAsetTetap($pids);
        $totalAset       = $totalAsetLancar + $totalAsetTetap;
        $totalLiabilitas = 0; // tambahkan method jika sudah ada akun liabilitas
        // Aset Neto kumulatif = saldo akhir semua periode sampai aktif
        $totalAsetNeto   = $saldoAkhir;

        $totalPenghasilan = $pemasukanBulanIni;
        $totalBeban       = $pengeluaranBulanIni;

        // ── Perubahan Aset Neto (dari jurnal akuntansi, konsisten dengan LaporanKeuanganController) ──
        $pendapatanTanpaPembatasan  = $this->dashboard->totalPendapatanTanpaPembatasan($periodeAktif->id);
        $pendapatanDenganPembatasan = $this->dashboard->totalPendapatanDenganPembatasan($periodeAktif->id);

        // Sesuai ISAK 335: beban hanya dibebankan ke dana tanpa pembatasan
        $surplusTanpaPembatasan  = $pendapatanTanpaPembatasan - $pengeluaranBulanIni;
        $surplusDenganPembatasan = $pendapatanDenganPembatasan; // tidak dikurangi beban

        $tidakTerikat    = $surplusTanpaPembatasan;
        $terikatTemporer = $surplusDenganPembatasan;

        // ── Arus Kas (estimasi ringkas untuk dashboard) ───────────────────────────────
        // Operasional = surplus dana tanpa pembatasan (pendapatan bebas - beban)
        // Pendanaan   = surplus dana terikat (infak kegiatan, wakaf, dll)
        // Investasi   = 0 (belum ada penghitungan aset tetap di dashboard ringkas)
        $arusOperasional = $tidakTerikat;
        $arusInvestasi   = 0;
        $arusPendanaan   = $terikatTemporer;
        $kenaikanNetoKas = $arusOperasional + $arusInvestasi + $arusPendanaan;

        // ── Transaksi terbaru ─────────────────────────────────────────────────────
        $transaksiTerbaru = Transaksi::with('kategoriTransaksi')
            ->where(function ($q) {
                $q->whereNull('status_approval')
                ->orWhere('status_approval', 'APPROVED');
            })
            ->latest('tanggal_transaksi')
            ->take(6)
            ->get();

        // ── Kegiatan berjalan ─────────────────────────────────────────────────
        $kegiatanBerjalan = Kegiatan::aktif()
            ->withSum(['transaksi as terkumpul' => fn($q) =>
                $q->where('jenis_transaksi', 'PEMASUKAN')
                ->where(function ($q2) {
                    $q2->whereNull('status_approval')
                        ->orWhere('status_approval', 'APPROVED');
                })
            ], 'jumlah')
            ->take(4)
            ->get();

        return view('pages.dashboard.index', compact(
            'periodeAktif', 'now',
            'saldoAwal', 'saldoAkhir',
            'pemasukanBulanIni', 'pengeluaranBulanIni',
            'pemasukanBulanLalu', 'pengeluaranBulanLalu',
            'saldoAwalBulanLalu',
            'saldoDompet',
            'distribusiPengeluaran', 'distribusiPemasukan', 
            'grafikData',
            'totalAset', 'totalLiabilitas', 'totalAsetNeto',
            'totalPenghasilan', 'totalBeban', 'surplus',
            'tidakTerikat', 'terikatTemporer',
            'arusOperasional', 'arusInvestasi', 'arusPendanaan', 'kenaikanNetoKas',
            'transaksiTerbaru', 'kegiatanBerjalan',
        ));
    }

    // ── Dashboard Laporan Keuangan Publik ───────────────────────────
    public function laporanKeuangan(Request $request)
    {
        return $this->publik();
    }

    // ── Dashboard Publik ───────────────────────────────────────────────────
    public function publik()
    {
        $now = Carbon::now();

        $approved = fn() => Transaksi::where(function ($q) {
            $q->whereNull('status_approval')
            ->orWhere('status_approval', 'APPROVED');
        });

        [$periodeAktif, $periodeSebelumnya] = $this->dashboard->resolvePeriodeAktif();

        $dompetList = Dompet::all();
        $dompetIds  = $dompetList->pluck('id');

        // ── Saldo Awal = saldo dompet pada AWAL periode aktif (tanggal_awal) ──
        // = saldo_awal dompet + mutasi sebelum tanggal_awal periode aktif
        $saldoAwal = $periodeAktif
            ? $dompetList->sum(function ($d) use ($approved, $periodeAktif) {
                $mutasiSebelum = (float) $approved()
                    ->where('dompet_id', $d->id)
                    ->where('tanggal_transaksi', '<', $periodeAktif->tanggal_awal->startOfDay())
                    ->selectRaw("SUM(CASE WHEN jenis_transaksi = 'PEMASUKAN' THEN jumlah ELSE -jumlah END) as mutasi")
                    ->value('mutasi') ?? 0;
                return (float) $d->saldo_awal + $mutasiSebelum;
            })
            : (float) $dompetList->sum('saldo_awal');

        // ── Pemasukan & Pengeluaran = transaksi dompet dalam rentang periode aktif, hingga hari ini ──
        $batasAwal  = $periodeAktif ? $periodeAktif->tanggal_awal->startOfDay() : Carbon::today()->startOfDay();
        $batasAkhir = $periodeAktif
            ? min($periodeAktif->tanggal_akhir->endOfDay(), Carbon::now())
            : Carbon::now();

        $pemasukan = (float) $approved()
            ->whereIn('dompet_id', $dompetIds)
            ->where('jenis_transaksi', 'PEMASUKAN')
            ->whereBetween('tanggal_transaksi', [$batasAwal, $batasAkhir])
            ->sum('jumlah');

        $pengeluaran = (float) $approved()
            ->whereIn('dompet_id', $dompetIds)
            ->where('jenis_transaksi', 'PENGELUARAN')
            ->whereBetween('tanggal_transaksi', [$batasAwal, $batasAkhir])
            ->sum('jumlah');

        // ── Saldo Akhir = Saldo Awal + Pemasukan - Pengeluaran (periode aktif, s.d. hari ini) ──
        $saldoAkhir = $saldoAwal + $pemasukan - $pengeluaran;

        // ── Sumber Pemasukan per Dompet ────────────────────────────────────────
        $sumberDana = $dompetList->map(function ($d) use ($approved, $batasAwal, $batasAkhir) {
            $total = (float) $approved()
                ->where('dompet_id', $d->id)
                ->where('jenis_transaksi', 'PEMASUKAN')
                ->whereBetween('tanggal_transaksi', [$batasAwal, $batasAkhir])
                ->sum('jumlah');
            return (object) ['nama_kategori' => $d->nama_dompet, 'total' => $total];
        })->filter(fn($r) => $r->total > 0)->sortByDesc('total')->values();

        // ── Distribusi Pengeluaran per Kegiatan (fallback: Operasional Umum) ───
        $penggunaanDana = $approved()
            ->whereIn('dompet_id', $dompetIds)
            ->where('jenis_transaksi', 'PENGELUARAN')
            ->whereBetween('tanggal_transaksi', [$batasAwal, $batasAkhir])
            ->leftJoin('kegiatan', 'transaksi.kegiatan_id', '=', 'kegiatan.id')
            ->selectRaw("COALESCE(kegiatan.nama_kegiatan, 'Operasional Umum') as nama_kategori, SUM(transaksi.jumlah) as total")
            ->groupBy('nama_kategori')
            ->orderByDesc('total')
            ->get()
            ->map(function ($row) {
                $row->total = (float) $row->total;
                return $row;
            });

        // ── Perkembangan dana (line chart, 30 hari terakhir) ─────────────────
        $perkembangan = collect(range(29, 0))->map(function ($i) use ($approved, $dompetIds) {
            $tgl   = Carbon::today()->subDays($i);
            $total = (float) $approved()
                ->whereIn('dompet_id', $dompetIds)
                ->where('jenis_transaksi', 'PEMASUKAN')
                ->whereDate('tanggal_transaksi', $tgl)
                ->sum('jumlah');
            return ['tanggal' => $tgl->format('d/m'), 'total' => $total];
        });

        $totalDonasi          = $pemasukan;
        $totalPemasukan30Hari = $perkembangan->sum('total');
        $avgHari              = $totalPemasukan30Hari / 30;
        $avgMinggu            = $avgHari * 7;
        $avgBulan             = $totalPemasukan30Hari;

        $pemasukan30HariSebelumnya = (float) $approved()
            ->whereIn('dompet_id', $dompetIds)
            ->where('jenis_transaksi', 'PEMASUKAN')
            ->whereBetween('tanggal_transaksi', [
                Carbon::today()->subDays(59),
                Carbon::today()->subDays(30),
            ])
            ->sum('jumlah');

        $persenPerkembangan = $pemasukan30HariSebelumnya > 0
            ? round((($totalPemasukan30Hari - $pemasukan30HariSebelumnya) / $pemasukan30HariSebelumnya) * 100, 1)
            : ($totalPemasukan30Hari > 0 ? 100 : 0);

        // ── Kegiatan berjalan ─────────────────────────────────────────────────
        $kegiatanBerjalan = Kegiatan::aktif()
            ->withSum(['transaksi as terkumpul' => fn($q) =>
                $q->where('jenis_transaksi', 'PEMASUKAN')
                ->where(function ($q2) {
                    $q2->whereNull('status_approval')
                        ->orWhere('status_approval', 'APPROVED');
                })
            ], 'jumlah')
            ->take(4)
            ->get();

        // ── Transaksi terbaru ─────────────────────────────────────────────────
        $transaksiTerbaru = Transaksi::with('kategoriTransaksi')
            ->where(function ($q) {
                $q->whereNull('status_approval')
                ->orWhere('status_approval', 'APPROVED');
            })
            ->when($periodeAktif, fn($q) => $q->whereBetween('tanggal_transaksi', [
                $periodeAktif->tanggal_awal->startOfDay(),
                $periodeAktif->tanggal_akhir->endOfDay(),
            ]))
            ->latest('tanggal_transaksi')
            ->get();

        $tanggalFilter = now()->format('Y-m-d');

        return view('pages.dashboard.publik', compact(
            'now', 'periodeAktif', 'saldoAwal', 'pemasukan', 'pengeluaran', 'saldoAkhir',
            'sumberDana', 'penggunaanDana', 'perkembangan',
            'totalDonasi', 'avgHari', 'avgMinggu', 'avgBulan', 'persenPerkembangan',
            'kegiatanBerjalan', 'transaksiTerbaru', 'tanggalFilter',
        ));
    }
}