<?php

namespace App\Http\Controllers;

use App\Models\Periode;
use App\Services\Laporan\ArusKasService;
use App\Services\Laporan\CalkService;
use App\Services\Laporan\PenghasilanKomprehensifService;
use App\Services\Laporan\PerubahanAsetNetoService;
use App\Services\Laporan\PosisiKeuanganService;
use Illuminate\Http\Request;

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
}