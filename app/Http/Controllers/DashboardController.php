<?php

namespace App\Http\Controllers;

use App\Models\Dompet;
use App\Models\Kegiatan;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // ── Dashboard Internal ─────────────────────────────────────────────────
    public function index(Request $request)
    {
        $now       = Carbon::now();
        $bulanIni  = $now->month;
        $tahunIni  = $now->year;
        $bulanLalu = $now->copy()->subMonth();

        // ── Helper scope: transaksi approved ──────────────────────────────
        $approved = fn() => Transaksi::where(function ($q) {
            $q->whereNull('status_approval')
              ->orWhere('status_approval', 'APPROVED');
        });

        // ── Kartu ringkasan ────────────────────────────────────────────────
        $totalSaldoAwalDompet = Dompet::sum('saldo_awal');

        // Pemasukan & pengeluaran SEBELUM bulan ini (untuk saldo awal)
        $mutasiSebelumBulanIni = fn($jenis) => $approved()
            ->where('jenis_transaksi', $jenis)
            ->where(function ($q) use ($tahunIni, $bulanIni) {
                $q->whereYear('tanggal_transaksi', '<', $tahunIni)
                  ->orWhere(function ($q2) use ($tahunIni, $bulanIni) {
                      $q2->whereYear('tanggal_transaksi', $tahunIni)
                         ->whereMonth('tanggal_transaksi', '<', $bulanIni);
                  });
            })->sum('jumlah');

        $saldoAwal = $totalSaldoAwalDompet
            + $mutasiSebelumBulanIni('PEMASUKAN')
            - $mutasiSebelumBulanIni('PENGELUARAN');

        // Pemasukan & pengeluaran bulan ini
        $pemasukanBulanIni = $approved()
            ->where('jenis_transaksi', 'PEMASUKAN')
            ->whereYear('tanggal_transaksi', $tahunIni)
            ->whereMonth('tanggal_transaksi', $bulanIni)
            ->sum('jumlah');

        $pengeluaranBulanIni = $approved()
            ->where('jenis_transaksi', 'PENGELUARAN')
            ->whereYear('tanggal_transaksi', $tahunIni)
            ->whereMonth('tanggal_transaksi', $bulanIni)
            ->sum('jumlah');

        // Pemasukan & pengeluaran bulan lalu
        $pemasukanBulanLalu = $approved()
            ->where('jenis_transaksi', 'PEMASUKAN')
            ->whereYear('tanggal_transaksi', $bulanLalu->year)
            ->whereMonth('tanggal_transaksi', $bulanLalu->month)
            ->sum('jumlah');

        $pengeluaranBulanLalu = $approved()
            ->where('jenis_transaksi', 'PENGELUARAN')
            ->whereYear('tanggal_transaksi', $bulanLalu->year)
            ->whereMonth('tanggal_transaksi', $bulanLalu->month)
            ->sum('jumlah');

        $saldoAkhir = $saldoAwal + $pemasukanBulanIni - $pengeluaranBulanIni;

        // Saldo awal bulan lalu
        $mutasiSebelumBulanLalu = fn($jenis) => $approved()
            ->where('jenis_transaksi', $jenis)
            ->where(function ($q) use ($bulanLalu) {
                $q->whereYear('tanggal_transaksi', '<', $bulanLalu->year)
                  ->orWhere(function ($q2) use ($bulanLalu) {
                      $q2->whereYear('tanggal_transaksi', $bulanLalu->year)
                         ->whereMonth('tanggal_transaksi', '<', $bulanLalu->month);
                  });
            })->sum('jumlah');

        $saldoAwalBulanLalu = $totalSaldoAwalDompet
            + $mutasiSebelumBulanLalu('PEMASUKAN')
            - $mutasiSebelumBulanLalu('PENGELUARAN');

        // ── Rekening kas (dari dompet + mutasi transaksi per dompet) ───────
        $dompetList = Dompet::all();

        $saldoPerDompet = fn($jenisDompet) => $dompetList
            ->where('jenis_dompet', $jenisDompet)
            ->sum('saldo_awal')
            + $approved()
                ->whereIn('dompet_id', $dompetList->where('jenis_dompet', $jenisDompet)->pluck('id'))
                ->sum(DB::raw("CASE WHEN jenis_transaksi = 'PEMASUKAN' THEN jumlah ELSE -jumlah END"));

        $kasTunai      = $saldoPerDompet('kas_tunai');
        $rekeningInfak = $saldoPerDompet('rekening_infak');
        $rekeningZakat = $saldoPerDompet('rekening_zakat');

        // ── Distribusi pengeluaran per kategori ────────────────────────────
        $distribusiPengeluaran = $approved()
            ->where('jenis_transaksi', 'PENGELUARAN')
            ->whereYear('tanggal_transaksi', $tahunIni)
            ->whereMonth('tanggal_transaksi', $bulanIni)
            ->join('kategori_transaksi', 'transaksi.kategori_transaksi_id', '=', 'kategori_transaksi.id')
            ->groupBy('kategori_transaksi.nama_kategori')
            ->select('kategori_transaksi.nama_kategori', DB::raw('SUM(transaksi.jumlah) as total'))
            ->orderByDesc('total')
            ->get();

        // ── Grafik pemasukan vs pengeluaran (8 bulan terakhir) ─────────────
        $bulan8     = collect(range(7, 0))->map(fn($i) => $now->copy()->subMonths($i));
        $grafikData = $bulan8->map(function ($bln) use ($approved) {
            return [
                'label'       => $bln->translatedFormat('M'),
                'pemasukan'   => (clone $approved())
                    ->where('jenis_transaksi', 'PEMASUKAN')
                    ->whereYear('tanggal_transaksi', $bln->year)
                    ->whereMonth('tanggal_transaksi', $bln->month)
                    ->sum('jumlah'),
                'pengeluaran' => (clone $approved())
                    ->where('jenis_transaksi', 'PENGELUARAN')
                    ->whereYear('tanggal_transaksi', $bln->year)
                    ->whereMonth('tanggal_transaksi', $bln->month)
                    ->sum('jumlah'),
            ];
        });

        // ── Laporan ringkasan ──────────────────────────────────────────────
        $totalAset       = $approved()->where('jenis_transaksi', 'PEMASUKAN')->sum('jumlah');
        $totalLiabilitas = 0;
        $totalAsetNeto   = $totalAset - $totalLiabilitas;

        $totalPenghasilan = $pemasukanBulanIni;
        $totalBeban       = $pengeluaranBulanIni;
        $surplus          = $totalPenghasilan - $totalBeban;

        $tidakTerikat = $approved()
            ->where('jenis_transaksi', 'PEMASUKAN')
            ->whereNull('kegiatan_id')
            ->whereYear('tanggal_transaksi', $tahunIni)
            ->whereMonth('tanggal_transaksi', $bulanIni)
            ->sum('jumlah');

        $terikatTemporer = $approved()
            ->where('jenis_transaksi', 'PEMASUKAN')
            ->whereNotNull('kegiatan_id')
            ->whereYear('tanggal_transaksi', $tahunIni)
            ->whereMonth('tanggal_transaksi', $bulanIni)
            ->sum('jumlah');

        // ── Transaksi terbaru ──────────────────────────────────────────────
        $transaksiTerbaru = Transaksi::with('kategoriTransaksi')
            ->where(function ($q) {
                $q->whereNull('status_approval')
                  ->orWhere('status_approval', 'APPROVED');
            })
            ->latest('tanggal_transaksi')
            ->take(6)
            ->get();

        // ── Kegiatan aktif ─────────────────────────────────────────────────
        $kegiatanAktif = Kegiatan::where('status', 'aktif')
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
            'saldoAwal', 'saldoAkhir', 'now',
            'pemasukanBulanIni', 'pengeluaranBulanIni',
            'pemasukanBulanLalu', 'pengeluaranBulanLalu',
            'saldoAwalBulanLalu',
            'kasTunai', 'rekeningInfak', 'rekeningZakat',
            'distribusiPengeluaran', 'grafikData',
            'totalAset', 'totalLiabilitas', 'totalAsetNeto',
            'totalPenghasilan', 'totalBeban', 'surplus',
            'tidakTerikat', 'terikatTemporer',
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

        $pemasukan = $approved()
            ->where('jenis_transaksi', 'PEMASUKAN')
            ->sum('jumlah');

        $pengeluaran = $approved()
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
            ->get();

        // ── Penggunaan dana per kategori (bar chart) ───────────────────────
        $penggunaanDana = $approved()
            ->where('jenis_transaksi', 'PENGELUARAN')
            ->join('kategori_transaksi', 'transaksi.kategori_transaksi_id', '=', 'kategori_transaksi.id')
            ->groupBy('kategori_transaksi.nama_kategori')
            ->select('kategori_transaksi.nama_kategori', DB::raw('SUM(transaksi.jumlah) as total'))
            ->orderByDesc('total')
            ->get();

        // ── Perkembangan dana (line chart, 30 hari terakhir) ───────────────
        $perkembangan = collect(range(29, 0))->map(function ($i) use ($approved) {
            $tgl   = Carbon::today()->subDays($i);
            $total = $approved()
                ->where('jenis_transaksi', 'PEMASUKAN')
                ->whereDate('tanggal_transaksi', $tgl)
                ->sum('jumlah');
            return ['tanggal' => $tgl->format('d/m'), 'total' => $total];
        });

        $totalDonasi = $approved()->where('jenis_transaksi', 'PEMASUKAN')->count();
        $avgHari     = $pemasukan / max(Carbon::now()->dayOfYear, 1);
        $avgMinggu   = $avgHari * 7;
        $avgBulan    = $avgHari * 30;

        // ── Kegiatan berjalan ──────────────────────────────────────────────
        $kegiatanBerjalan = Kegiatan::where('status', 'aktif')
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
            'totalDonasi', 'avgHari', 'avgMinggu', 'avgBulan',
            'kegiatanBerjalan', 'transaksiTerbaru', 'tanggalFilter',
        ));
    }
}