<?php

namespace App\Http\Controllers;

use App\Models\Dompet;
use App\Models\Kegiatan;
use App\Models\Transaksi;
use App\Models\Periode; 
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

        // ── SALDO AWAL ───────────────────────────────────────────────
        $saldoAwal = $periodeSebelumnya
            ? $this->dashboard->totalKasSetaraKas($pidsPrev)        // = saldo akhir periode lalu
            : $this->dashboard->totalKasSetaraKasPembuka($periodeAktif->id); // jurnal pembuka

        // ── SALDO AKHIR ──────────────────────────────────────────────
        $saldoAkhir = $this->dashboard->totalKasSetaraKas($pids);

        // ── PEMASUKAN & PENGELUARAN periode aktif ───────────────────
        $pemasukanBulanIni   = $this->dashboard->totalPendapatan($periodeAktif->id);
        $pengeluaranBulanIni = $this->dashboard->totalBeban($periodeAktif->id);

        // ── Pemasukan/Pengeluaran periode sebelumnya ───
        $pemasukanBulanLalu   = $periodeSebelumnya ? $this->dashboard->totalPendapatan($periodeSebelumnya->id) : 0;
        $pengeluaranBulanLalu = $periodeSebelumnya ? $this->dashboard->totalBeban($periodeSebelumnya->id) : 0;

        $saldoAwalBulanLalu = $periodeSebelumnya
            ? (function () use ($periodeSebelumnya) {
                $prevPrev = Periode::where('tipe', $periodeSebelumnya->tipe)
                    ->where('tanggal_akhir', '<', $periodeSebelumnya->tanggal_awal)
                    ->orderByDesc('tanggal_akhir')->first();
                return $prevPrev
                    ? $this->dashboard->totalKasSetaraKas($this->dashboard->getPeriodeIdsUpTo($prevPrev->id))
                    : $this->dashboard->totalKasSetaraKasPembuka($periodeSebelumnya->id);
            })()
            : 0;

        // ── Saldo per dompet (dinamis, CASH & BANK) ──────────────────────────
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

        // ── Distribusi pengeluaran per kategori (bulan ini) ──────────────────
        $distribusiPengeluaran = $approved()
            ->where('jenis_transaksi', 'PENGELUARAN')
            ->whereNotNull('kategori_transaksi_id')
            ->join('kategori_transaksi', 'transaksi.kategori_transaksi_id', '=', 'kategori_transaksi.id')
            ->groupBy('kategori_transaksi.nama_kategori')
            ->select('kategori_transaksi.nama_kategori', DB::raw('SUM(transaksi.jumlah) as total'))
            ->orderByDesc('total')
            ->get()
            ->map(fn($row) => tap($row, fn($r) => $r->total = (float) $r->total));

        // ── Grafik pemasukan vs pengeluaran (8 bulan terakhir) ───────────────
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

        // ── Laporan ringkasan ─────────────────────────────────────────────────
        $totalAset       = $saldoAkhir; // total saldo seluruh dompet akhir periode
        $totalLiabilitas = 0;
        $totalAsetNeto   = $totalAset - $totalLiabilitas;

        $totalPenghasilan = $pemasukanBulanIni;
        $totalBeban       = $pengeluaranBulanIni;
        $surplus          = $totalPenghasilan - $totalBeban;

        $pemasukanTidakTerikat = (float) $approved()
            ->where('jenis_transaksi', 'PEMASUKAN')
            ->whereNull('kegiatan_id')
            ->whereYear('tanggal_transaksi', $tahunIni)
            ->whereMonth('tanggal_transaksi', $bulanIni)
            ->sum('jumlah');

        $pemasukanTerikatTemporer = (float) $approved()
            ->where('jenis_transaksi', 'PEMASUKAN')
            ->whereNotNull('kegiatan_id')
            ->whereYear('tanggal_transaksi', $tahunIni)
            ->whereMonth('tanggal_transaksi', $bulanIni)
            ->sum('jumlah');

        // Sesuai ISAK 35: beban dibebankan ke dana tidak terikat
        $tidakTerikat    = $pemasukanTidakTerikat - $pengeluaranBulanIni;
        $terikatTemporer = $pemasukanTerikatTemporer;

        // ── Laporan Arus Kas (ringkas, untuk kartu dashboard) ────────────────
        $arusOperasional = $tidakTerikat;
        $arusInvestasi   = 0;
        $arusPendanaan   = $terikatTemporer;
        $kenaikanNetoKas = $arusOperasional + $arusInvestasi + $arusPendanaan;

        // ── Transaksi terbaru ─────────────────────────────────────────────────
        $transaksiTerbaru = Transaksi::with('kategoriTransaksi')
            ->where(function ($q) {
                $q->whereNull('status_approval')
                ->orWhere('status_approval', 'APPROVED');
            })
            ->latest('tanggal_transaksi')
            ->take(6)
            ->get();

        // ── Kegiatan aktif ───────────────────────────────────────────────────
        $kegiatanAktif = Kegiatan::aktif()
            ->withSum(['transaksi as total_terkumpul' => fn($q) =>
                $q->where('jenis_transaksi', 'PEMASUKAN')
                ->where(function ($q2) {
                    $q2->whereNull('status_approval')
                        ->orWhere('status_approval', 'APPROVED');
                })
            ], 'jumlah')
            ->take(3)
            ->get();

        return view('pages.dashboard.index', compact(
            'periodeAktif', 'now',
            'saldoAwal', 'saldoAkhir',
            'pemasukanBulanIni', 'pengeluaranBulanIni',
            'pemasukanBulanLalu', 'pengeluaranBulanLalu',
            'saldoAwalBulanLalu',
            'saldoDompet',
            'distribusiPengeluaran', 'grafikData',
            'totalAset', 'totalLiabilitas', 'totalAsetNeto',
            'totalPenghasilan', 'totalBeban', 'surplus',
            'tidakTerikat', 'terikatTemporer',
            'arusOperasional', 'arusInvestasi', 'arusPendanaan', 'kenaikanNetoKas',
            'transaksiTerbaru', 'kegiatanAktif',
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
        $now      = Carbon::now();
        $tahunIni = $now->year;
        $bulanIni = $now->month;

        $approved = fn() => Transaksi::where(function ($q) {
            $q->whereNull('status_approval')
            ->orWhere('status_approval', 'APPROVED');
        });

        // ── Kartu ringkasan ────────────────────────────────────────────────
        $totalSaldoAwalDompet = Dompet::sum('saldo_awal');

        $pemasukan = (float) $approved()
            ->where('jenis_transaksi', 'PEMASUKAN')
            ->sum('jumlah');

        $pengeluaran = (float) $approved()
            ->where('jenis_transaksi', 'PENGELUARAN')
            ->sum('jumlah');

        $saldoKas   = $totalSaldoAwalDompet + $pemasukan - $pengeluaran;
        $saldoAkhir = $saldoKas;

        // ── Sumber dana (donut chart) ──────────────────────────────────────
        $sumberDana = $approved()
            ->where('jenis_transaksi', 'PEMASUKAN')
            ->join('kategori_transaksi', 'transaksi.kategori_transaksi_id', '=', 'kategori_transaksi.id')
            ->groupBy('kategori_transaksi.nama_kategori')
            ->select('kategori_transaksi.nama_kategori', DB::raw('SUM(transaksi.jumlah) as total'))
            ->orderByDesc('total')
            ->take(5)
            ->get()
            ->map(function ($row) {
                $row->total = (float) $row->total;
                return $row;
            });

        // ── Penggunaan dana per kategori (bar chart) ───────────────────────
        $penggunaanDana = $approved()
            ->where('jenis_transaksi', 'PENGELUARAN')
            ->join('kategori_transaksi', 'transaksi.kategori_transaksi_id', '=', 'kategori_transaksi.id')
            ->groupBy('kategori_transaksi.nama_kategori')
            ->select('kategori_transaksi.nama_kategori', DB::raw('SUM(transaksi.jumlah) as total'))
            ->orderByDesc('total')
            ->get()
            ->map(function ($row) {
                $row->total = (float) $row->total;
                return $row;
            });

        // ── Perkembangan dana (line chart, 30 hari terakhir) ───────────────
        $perkembangan = collect(range(29, 0))->map(function ($i) use ($approved) {
            $tgl   = Carbon::today()->subDays($i);
            $total = (float) $approved()
                ->where('jenis_transaksi', 'PEMASUKAN')
                ->whereDate('tanggal_transaksi', $tgl)
                ->sum('jumlah');
            return ['tanggal' => $tgl->format('d/m'), 'total' => $total];
        });

        // Total donasi (rupiah) = total pemasukan keseluruhan
        $totalDonasi = $pemasukan;

        // Rata-rata dihitung dari 30 hari terakhir (lebih representatif
        // daripada dibagi "hari ke-N dalam tahun ini")
        $totalPemasukan30Hari = $perkembangan->sum('total');
        $avgHari   = $totalPemasukan30Hari / 30;
        $avgMinggu = $avgHari * 7;
        $avgBulan  = $totalPemasukan30Hari; // total 30 hari ≈ rata-rata per bulan

        // Perbandingan 30 hari ini vs 30 hari sebelumnya
        $pemasukan30HariSebelumnya = (float) $approved()
            ->where('jenis_transaksi', 'PEMASUKAN')
            ->whereBetween('tanggal_transaksi', [
                Carbon::today()->subDays(59),
                Carbon::today()->subDays(30),
            ])
            ->sum('jumlah');

        $persenPerkembangan = $pemasukan30HariSebelumnya > 0
            ? round((($totalPemasukan30Hari - $pemasukan30HariSebelumnya) / $pemasukan30HariSebelumnya) * 100, 1)
            : ($totalPemasukan30Hari > 0 ? 100 : 0);

        // ── Kegiatan berjalan ──────────────────────────────────────────────
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

        // ── Transaksi terbaru ──────────────────────────────────────────────
        $transaksiTerbaru = Transaksi::with('kategoriTransaksi')
            ->where(function ($q) {
                $q->whereNull('status_approval')
                ->orWhere('status_approval', 'APPROVED');
            })
            ->latest('tanggal_transaksi')
            ->take(6)
            ->get();

        $tanggalFilter = now()->format('Y-m-d');

        return view('pages.dashboard.publik', compact(
            'now', 'saldoKas', 'pemasukan', 'pengeluaran', 'saldoAkhir',
            'sumberDana', 'penggunaanDana', 'perkembangan',
            'totalDonasi', 'avgHari', 'avgMinggu', 'avgBulan', 'persenPerkembangan',
            'kegiatanBerjalan', 'transaksiTerbaru', 'tanggalFilter',
        ));
    }
}