<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransaksiRequest;
use App\Models\Kegiatan;
use App\Models\Transaksi;
use App\Services\KegiatanService;
use Illuminate\Http\Request;

class KegiatanController extends Controller
{
    public function __construct(
        protected KegiatanService $kegiatanService
    ) {}

    // ── List Kegiatan ──────────────────────────────────────────

    public function index(Request $request)
    {
        $search   = $request->get('search', '');
        $status   = $request->get('status', '');
        $kegiatan = $this->kegiatanService->getKegiatanList($search, $status);
        $summary  = $this->kegiatanService->getSummary();

        return view('pages.kegiatan.index', compact('kegiatan', 'summary', 'search', 'status'));
    }

    // ── List Transaksi per Kegiatan ────────────────────────────

    public function show(Kegiatan $kegiatan, Request $request)
    {
        // Panitia hanya bisa lihat kegiatan miliknya
        if (auth()->user()->hasRole('panitia-khusus') &&
            $kegiatan->panitia_id !== auth()->id()) {
            abort(403);
        }

        $search    = $request->get('search', '');
        $transaksi = $this->kegiatanService->getTransaksiByKegiatan($kegiatan, $search);
        $porsi     = $this->kegiatanService->getPorsiAnggaran($kegiatan);

        return view('pages.kegiatan.show', compact('kegiatan', 'transaksi', 'porsi', 'search'));
    }

    // ── Form Catat Transaksi ───────────────────────────────────

    public function createTransaksi(Kegiatan $kegiatan)
    {
        // Cek kegiatan masih berjalan
        if ($kegiatan->status !== Kegiatan::STATUS_BERJALAN) {
            return redirect()->route('dashboard.kegiatan.show', $kegiatan)
                ->with('error', 'Kegiatan tidak sedang berjalan');
        }

        // Panitia hanya bisa input ke kegiatan miliknya
        if (auth()->user()->hasRole('panitia-khusus') &&
            $kegiatan->panitia_id !== auth()->id()) {
            abort(403);
        }

        $dompetList   = $this->kegiatanService->getDompetList();
        $kategoriList = $this->kegiatanService->getKategoriList();
        $kodeTransaksi= $this->kegiatanService->generateKodeTransaksi();

        return view('pages.kegiatan.create-transaksi', compact(
            'kegiatan', 'dompetList', 'kategoriList', 'kodeTransaksi'
        ));
    }

    // ── Simpan Transaksi ───────────────────────────────────────

    public function storeTransaksi(StoreTransaksiRequest $request, Kegiatan $kegiatan)
    {
        if ($kegiatan->status !== Kegiatan::STATUS_BERJALAN) {
            return redirect()->back()->with('error', 'Kegiatan tidak sedang berjalan');
        }

        if (auth()->user()->hasRole('panitia-khusus') &&
            $kegiatan->panitia_id !== auth()->id()) {
            abort(403);
        }

        $this->kegiatanService->storeTransaksi($kegiatan, $request->validated());

        return redirect()->route('dashboard.kegiatan.show', $kegiatan)
            ->with('success', 'Transaksi berhasil dicatat');
    }

    // ── Detail Transaksi ───────────────────────────────────────

    public function showTransaksi(Kegiatan $kegiatan, Transaksi $transaksi)
    {
        if ($transaksi->kegiatan_id !== $kegiatan->id) {
            abort(404);
        }

        $transaksi = $this->kegiatanService->getTransaksiById($transaksi);

        return view('pages.kegiatan.show-transaksi', compact('kegiatan', 'transaksi'));
    }

    // ── Hapus Transaksi ────────────────────────────────────────

    public function destroyTransaksi(Kegiatan $kegiatan, Transaksi $transaksi)
    {
        if ($transaksi->kegiatan_id !== $kegiatan->id) {
            abort(404);
        }

        $result = $this->kegiatanService->deleteTransaksi($transaksi);

        if ($result !== true) {
            return redirect()->back()->with('error', $result);
        }

        return redirect()->route('dashboard.kegiatan.show', $kegiatan)
            ->with('success', 'Transaksi berhasil dihapus');
    }
}