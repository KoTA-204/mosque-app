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

    public function home()
    {
        $slug = optional(auth()->user()->roles)->slug;

        $map = [
            'administrator'            => 'dashboard.users.index',
            'ketua-dkm'                => 'dashboard.index',
            'bendahara-1'              => 'dashboard.index',
            'bendahara-2'              => 'dashboard.index',
            'pengurus-harian-masjid'   => 'dashboard.kencleng.index',
            'panitia-kegiatan-khusus'  => 'dashboard.transaksi-kegiatan.index',
            'sekretaris'               => 'dashboard.aset.index',
        ];

        return redirect()->route($map[$slug] ?? 'dashboard.index');
    }

    // ── Dashboard Internal ─────────────────────────────────────────────────
    public function index(Request $request)
    {
        [$periodeAktif, $periodeSebelumnya] = $this->dashboard->resolvePeriodeAktif();

        return view('pages.dashboard.index', $this->hitungDataDashboard($periodeAktif, $periodeSebelumnya));
    }

    public function laporanKeuangan(Request $request)
    {
        return $this->publik();
    }

    // ── Dashboard Publik ───────────────────────────────────────────────────
    public function publik()
    {
        [$periodeAktif, $periodeSebelumnya] = $this->dashboard->resolvePeriodeAktif();

        return view('pages.dashboard.index', $this->hitungDataDashboard($periodeAktif, $periodeSebelumnya));
    }

    private function hitungDataDashboard(?Periode $periodeAktif, ?Periode $periodeSebelumnya): array
    {
        $approved = fn() => Transaksi::where(function ($q) {
            $q->whereNull('status_approval')
            ->orWhere('status_approval', 'APPROVED');
        });

        $now = Carbon::now();

        $pids = $periodeAktif ? $this->dashboard->getPeriodeIdsUpTo($periodeAktif->id) : [];

        // ── SALDO AWAL & AKHIR ──────────────────────────────────────────────
        $saldoAwal = $periodeAktif
            ? $this->dashboard->saldoAwalAsetNeto($periodeAktif->id, $periodeSebelumnya?->id)
            : 0;

        $pemasukanBulanIni   = $periodeAktif ? $this->dashboard->totalPendapatan($periodeAktif->id) : 0;
        $pengeluaranBulanIni = $periodeAktif ? $this->dashboard->totalBeban($periodeAktif->id) : 0;

        $surplus    = $pemasukanBulanIni - $pengeluaranBulanIni;
        $saldoAkhir = $saldoAwal + $surplus;

        $pemasukanBulanLalu   = $periodeSebelumnya ? $this->dashboard->totalPendapatan($periodeSebelumnya->id) : 0;
        $pengeluaranBulanLalu = $periodeSebelumnya ? $this->dashboard->totalBeban($periodeSebelumnya->id) : 0;

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

        // ── Saldo per dompet: TIDAK bergantung periode, tetap dihitung ────────
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

        // ── Distribusi & grafik: kosong kalau belum ada periode ───────────────
        $distribusiPemasukan   = collect();
        $distribusiPengeluaran = collect();
        $grafikData            = collect();

        if ($periodeAktif) {
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
        }

        // ── Laporan ringkasan ─────────────────────────────────────────────────
        $totalAsetLancar = $periodeAktif ? $this->dashboard->totalAsetLancar($pids) : 0;
        $totalAsetTetap  = $periodeAktif ? $this->dashboard->totalAsetTetap($pids) : 0;
        $totalAset       = $totalAsetLancar + $totalAsetTetap;
        $totalLiabilitas = 0;
        $totalAsetNeto   = $saldoAkhir;

        $totalPenghasilan = $pemasukanBulanIni;
        $totalBeban       = $pengeluaranBulanIni;

        $pendapatanTanpaPembatasan  = $periodeAktif ? $this->dashboard->totalPendapatanTanpaPembatasan($periodeAktif->id) : 0;
        $pendapatanDenganPembatasan = $periodeAktif ? $this->dashboard->totalPendapatanDenganPembatasan($periodeAktif->id) : 0;

        $tidakTerikat    = $pendapatanTanpaPembatasan - $pengeluaranBulanIni;
        $terikatTemporer = $pendapatanDenganPembatasan;

        $arusOperasional = $tidakTerikat;
        $arusInvestasi   = 0;
        $arusPendanaan   = $terikatTemporer;
        $kenaikanNetoKas = $arusOperasional + $arusInvestasi + $arusPendanaan;

        // ── Transaksi terbaru & kegiatan berjalan: TIDAK bergantung periode ────
        $transaksiTerbaru = Transaksi::with('kategoriTransaksi')
            ->where(function ($q) {
                $q->whereNull('status_approval')
                ->orWhere('status_approval', 'APPROVED');
            })
            ->latest('tanggal_transaksi')
            ->take(6)
            ->get();

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

        return compact(
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
        );
    }

    // ── Export Transaksi Publik ────────────────────
    private function transaksiPeriodeAktif(): array
    {
        [$periodeAktif] = $this->dashboard->resolvePeriodeAktif();

        $transaksi = Transaksi::with('kategoriTransaksi')
            ->where(function ($q) {
                $q->whereNull('status_approval')
                  ->orWhere('status_approval', 'APPROVED');
            })
            ->when($periodeAktif, fn($q) => $q->whereBetween('tanggal_transaksi', [
                $periodeAktif->tanggal_awal->startOfDay(),
                $periodeAktif->tanggal_akhir->endOfDay(),
            ]))
            ->orderByDesc('tanggal_transaksi')
            ->get();

        return [$transaksi, $periodeAktif];
    }

    public function exportTransaksiExcel()
    {
        [$transaksi, $periodeAktif] = $this->transaksiPeriodeAktif();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Transaksi');

        $sheet->setCellValue('A1', 'Masjid Luqmanul Hakim - Laporan Transaksi');
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A2', 'Periode: ' . ($periodeAktif?->nama_periode ?? '-'));
        $sheet->mergeCells('A2:F2');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $headerRow = 4;
        $headers = ['No', 'Tanggal', 'Jenis', 'Keterangan', 'Kategori', 'Jumlah (Rp)'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . $headerRow, $h);
            $col++;
        }
        $sheet->getStyle('A' . $headerRow . ':F' . $headerRow)->getFont()->setBold(true);

        $row = $headerRow + 1;
        $no = 1;
        foreach ($transaksi as $t) {
            $nominal = ($t->jenis_transaksi === 'PEMASUKAN' ? 1 : -1) * (float) $t->jumlah;
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, optional($t->tanggal_transaksi)->format('d/m/Y'));
            $sheet->setCellValue('C' . $row, ucfirst(strtolower($t->jenis_transaksi)));
            $sheet->setCellValue('D' . $row, $t->deskripsi ?? '-');
            $sheet->setCellValue('E' . $row, $t->kategoriTransaksi?->nama_kategori ?? '-');
            $sheet->setCellValue('F' . $row, $nominal);
            $row++;
        }

        foreach (range('A', 'F') as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        $namaPeriode = $periodeAktif?->nama_periode ?? now()->format('Y-m');
        $filename = 'transaksi-' . str_replace(' ', '-', strtolower($namaPeriode)) . '.xlsx';

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function exportTransaksiPdf()
    {
        [$transaksi, $periodeAktif] = $this->transaksiPeriodeAktif();
        $now = Carbon::now();

        return view('pages.dashboard.transaksi-pdf', compact('transaksi', 'periodeAktif', 'now'));
    }
}
