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
    /**
     * Tampilkan halaman laporan sesuai jenis.
     *
     * $jenis diinjeksikan lewat ->defaults('jenis', ...) pada definisi route,
     * sehingga nama & path route lama tetap dipertahankan (kompatibel dengan
     * pemanggilan route() di Blade), namun cukup satu method di sini
     * menggantikan 5 method yang sebelumnya nyaris identik.
     */
    public function tampilkanLaporan(Request $request, string $jenis)
    {
        $laporan = $this->pilihLaporanByJenis($jenis);

        [$periodeList, $periode, $periodeSebelumnya, $selectedId] = $this->tentukanPeriodeAktif($request);

        $data     = $laporan->susunLaporan($periode, $periodeSebelumnya);
        $dataPrev = $periodeSebelumnya
            ? $laporan->susunLaporan($periodeSebelumnya, $periodeSebelumnya->periodeSebelumnya())
            : null;

        // Nama variabel view dipertahankan (periodePrev) agar Blade lama tetap jalan.
        $periodePrev = $periodeSebelumnya;

        return view($laporan->namaViewHalaman(), compact(
            'periodeList', 'periode', 'periodePrev', 'data', 'dataPrev'
        ))->with('selectedPeriodeId', $selectedId);
    }

    /** Unduh laporan sebagai PDF (nama file otomatis dari server). */
    public function unduhLaporanPdf(Request $request, string $jenis)
    {
        // Helper format angka untuk template PDF. Didefinisikan di file TANPA
        // namespace (app/Helpers/pdf_helpers.php) agar fungsi terdaftar di
        // namespace global, sehingga bisa dipanggil dari view Blade ter-compile.
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

    /**
     * Pilih objek laporan berdasarkan jenis.
     * Menggantikan switch besar; penambahan laporan baru cukup 1 baris di sini.
     */
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

    /** Tentukan periode aktif & periode sebelumnya dari request. */
    private function tentukanPeriodeAktif(Request $request): array
    {
        $periodeList  = Periode::orderByDesc('tanggal_akhir')->get();
        $periodeAktif = Periode::where('status', true)->first();
        $selectedId   = $request->get('periode_id', $periodeAktif?->id);
        $periode      = $selectedId ? Periode::find($selectedId) : $periodeAktif;

        $periodeSebelumnya = $periode?->periodeSebelumnya();

        return [$periodeList, $periode, $periodeSebelumnya, $selectedId];
    }
}
