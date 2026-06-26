<?php

namespace App\Http\Controllers;

use App\Models\Periode;
use App\Services\Laporan\ArusKasService;
use App\Services\Laporan\CalkService;
use App\Services\Laporan\PenghasilanKomprehensifService;
use App\Services\Laporan\PerubahanAsetNetoService;
use App\Services\Laporan\PosisiKeuanganService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanKeuanganController extends Controller
{
    public function __construct(
        private PenghasilanKomprehensifService $penghasilan,
        private PosisiKeuanganService $posisi,
        private PerubahanAsetNetoService $perubahan,
        private ArusKasService $arusKas,
        private CalkService $calk,
    ) {}

    // untuk resolve periode aktif & periode sebelumnya dari request
    private function resolvePeriode(Request $request): array
    {
        $periodeList  = Periode::orderByDesc('tanggal_akhir')->get();
        $periodeAktif = Periode::where('status', true)->first();
        $selectedId   = $request->get('periode_id', $periodeAktif?->id);
        $periode      = $selectedId ? Periode::find($selectedId) : $periodeAktif;

        $periodePrev = null;
        if ($periode) {
            $periodePrev = Periode::where('tipe', $periode->tipe)
                ->where('tanggal_akhir', '<', $periode->tanggal_awal)
                ->orderByDesc('tanggal_akhir')
                ->first();
        }
        return [$periodeList, $periode, $periodePrev, $selectedId];
    }

    public function penghasilanKomprehensif(Request $request)
    {
        [$periodeList, $periode, $periodePrev, $selectedId] = $this->resolvePeriode($request);
        $data     = $this->penghasilan->build($periode?->id);
        $dataPrev = $periodePrev ? $this->penghasilan->build($periodePrev->id) : null;
        return view('pages.laporan.penghasilan-komprehensif', compact(
            'periodeList', 'periode', 'periodePrev', 'data', 'dataPrev'
        ))->with('selectedPeriodeId', $selectedId);
    }

    public function posisiKeuangan(Request $request)
    {
        [$periodeList, $periode, $periodePrev, $selectedId] = $this->resolvePeriode($request);
        $data     = $this->posisi->build($periode?->id);
        $dataPrev = $periodePrev ? $this->posisi->build($periodePrev->id) : null;
        return view('pages.laporan.posisi-keuangan', compact(
            'periodeList', 'periode', 'periodePrev', 'data', 'dataPrev'
        ))->with('selectedPeriodeId', $selectedId);
    }

    public function perubahanAsetNeto(Request $request)
    {
        [$periodeList, $periode, $periodePrev, $selectedId] = $this->resolvePeriode($request);
        $data     = $this->perubahan->build($periode, $periodePrev);
        $dataPrev = $periodePrev ? $this->perubahan->build($periodePrev, null) : null;
        return view('pages.laporan.perubahan-aset-neto', compact(
            'periodeList', 'periode', 'periodePrev', 'data', 'dataPrev'
        ))->with('selectedPeriodeId', $selectedId);
    }

    public function arusKas(Request $request)
    {
        [$periodeList, $periode, $periodePrev, $selectedId] = $this->resolvePeriode($request);
        $data     = $this->arusKas->build($periode);
        $dataPrev = $periodePrev ? $this->arusKas->build($periodePrev) : null;
        return view('pages.laporan.arus-kas', compact(
            'periodeList', 'periode', 'periodePrev', 'data', 'dataPrev'
        ))->with('selectedPeriodeId', $selectedId);
    }

    public function calk(Request $request)
    {
        [$periodeList, $periode, $periodePrev, $selectedId] = $this->resolvePeriode($request);
        $data = $this->calk->build($periode);
        return view('pages.laporan.calk', compact(
            'periodeList', 'periode', 'periodePrev', 'data'
        ))->with('selectedPeriodeId', $selectedId);
    }

    // Unduh laporan sebagai PDF (nama file otomatis dari server)
    public function downloadPdf(Request $request, string $jenis)
    {
        // Helper format angka untuk template PDF. Didefinisikan di file TANPA
        // namespace (app/Helpers/pdf_helpers.php) agar fungsi terdaftar di
        // namespace global, sehingga bisa dipanggil dari view Blade ter-compile
        // yang berjalan di namespace global.
        require_once app_path('Helpers/pdf_helpers.php');

        [$periodeList, $periode, $periodePrev, $selectedId] = $this->resolvePeriode($request);

        switch ($jenis) {
            case 'posisi-keuangan':
                $data     = $this->posisi->build($periode?->id);
                $dataPrev = $periodePrev ? $this->posisi->build($periodePrev->id) : null;
                $judul    = 'Laporan Posisi Keuangan';
                break;
            case 'penghasilan-komprehensif':
                $data     = $this->penghasilan->build($periode?->id);
                $dataPrev = $periodePrev ? $this->penghasilan->build($periodePrev->id) : null;
                $judul    = 'Laporan Penghasilan Komprehensif';
                break;
            case 'perubahan-aset-neto':
                $data     = $this->perubahan->build($periode, $periodePrev);
                $dataPrev = $periodePrev ? $this->perubahan->build($periodePrev, null) : null;
                $judul    = 'Laporan Perubahan Aset Neto';
                break;
            case 'arus-kas':
                $data     = $this->arusKas->build($periode);
                $dataPrev = $periodePrev ? $this->arusKas->build($periodePrev) : null;
                $judul    = 'Laporan Arus Kas';
                break;
            case 'calk':
                $data     = $this->calk->build($periode);
                $dataPrev = null;
                $judul    = 'Laporan CALK';
                break;
            default:
                abort(404);
        }

        $bulan    = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $now      = now();
        $tgl      = $now->day . $bulan[$now->month - 1] . $now->year;
        $namaFile = $tgl . '_' . $judul . '_MosQue.pdf';

        $pdf = Pdf::loadView('pages.laporan.pdf.' . $jenis, [
            'periode'     => $periode,
            'periodePrev' => $periodePrev,
            'data'        => $data,
            'dataPrev'    => $dataPrev,
            'judul'       => $judul,
            'namaFile'    => $namaFile,
        ])->setPaper('a4', 'portrait');

        return $pdf->download($namaFile);
    }
}