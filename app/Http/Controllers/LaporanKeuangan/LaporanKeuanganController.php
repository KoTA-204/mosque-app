<?php

namespace App\Http\Controllers\LaporanKeuangan;

use App\Http\Controllers\Controller;
use App\Models\Periode;
use App\Services\LaporanKeuangan\CalkService;
use App\Services\LaporanKeuangan\ArusKasService;
use App\Services\LaporanKeuangan\LaporanKeuanganInterface;
use App\Services\LaporanKeuangan\PenghasilanKomprehensifService;
use App\Services\LaporanKeuangan\PerubahanAsetNetoService;
use App\Services\LaporanKeuangan\PosisiKeuanganService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanKeuanganController extends Controller
{
    public function tampilkanLaporan(Request $request, string $jenis)
    {
        $laporan = $this->pilihLaporanByJenis($jenis);

        [$periodeList, $periode, $periodeSebelumnya, $selectedId] = $this->tentukanPeriodeAktif($request);

        $data     = $laporan->susunLaporan($periode, $periodeSebelumnya);
        $dataPrev = $periodeSebelumnya
            ? $laporan->susunLaporan($periodeSebelumnya, $periodeSebelumnya->periodeSebelumnya())
            : null;

        $periodePrev = $periodeSebelumnya;

        return view($laporan->namaViewHalaman(), compact(
            'periodeList', 'periode', 'periodePrev', 'data', 'dataPrev'
        ))->with('selectedPeriodeId', $selectedId);
    }

    public function unduhLaporanPdf(Request $request, string $jenis)
    {
        require_once app_path('Helpers/pdf_helpers.php');

        $laporan = $this->pilihLaporanByJenis($jenis);

        [$periodeList, $periode, $periodeSebelumnya, $selectedId] = $this->tentukanPeriodeAktif($request);

        $data     = $laporan->susunLaporan($periode, $periodeSebelumnya);
        $dataPrev = $periodeSebelumnya
            ? $laporan->susunLaporan($periodeSebelumnya, $periodeSebelumnya->periodeSebelumnya())
            : null;
        $periodePrev = $periodeSebelumnya;
        $judul       = $laporan->judulLaporan();

        $bulan    = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $now      = now();
        $tgl      = $now->day . $bulan[$now->month - 1] . $now->year;
        $namaFile = $tgl . '_' . $judul . '_MosQue.pdf';

        $pdf = Pdf::loadView($laporan->namaViewPdf(), [
            'periode'     => $periode,
            'periodePrev' => $periodePrev,
            'data'        => $data,
            'dataPrev'    => $dataPrev,
            'judul'       => $judul,
            'namaFile'    => $namaFile,
        ])->setPaper('a4', 'portrait');

        return $pdf->download($namaFile);
    }

    private function pilihLaporanByJenis(string $jenis): LaporanKeuanganInterface
    {
        return match ($jenis) {
            'posisi-keuangan'          => app(PosisiKeuanganService::class),
            'penghasilan-komprehensif' => app(PenghasilanKomprehensifService::class),
            'perubahan-aset-neto'      => app(PerubahanAsetNetoService::class),
            'arus-kas'                 => app(ArusKasService::class),
            'calk'                     => app(CalkService::class),
            default                    => abort(404),
        };
    }

    private function tentukanPeriodeAktif(Request $request): array
    {
        $periodeList  = Periode::orderByDesc('tanggal_akhir')->get();
        $periodeAktif = Periode::berjalan();
        $selectedId   = $request->get('periode_id', $periodeAktif?->id);
        $periode      = $selectedId ? Periode::find($selectedId) : $periodeAktif;

        $periodeSebelumnya = $periode?->periodeSebelumnya();

        return [$periodeList, $periode, $periodeSebelumnya, $selectedId];
    }
}
