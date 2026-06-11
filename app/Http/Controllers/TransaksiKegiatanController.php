<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransaksiRequest;
use App\Http\Requests\UpdateTransaksiRequest;
use App\Models\Kegiatan;
use App\Models\Transaksi;
use App\Services\TransaksiKegiatanService;
use Illuminate\Http\Request;

class TransaksiKegiatanController extends Controller
{
    public function __construct(
        protected TransaksiKegiatanService $transaksiKegiatanService
    ) {}

    // ── List Kegiatan ─────────────────────────────────────────
    public function index(Request $request)
    {
        $search   = $request->get('search', '');
        $status   = $request->get('status', '');
        $kegiatan = $this->transaksiKegiatanService->getKegiatanList($search, $status);
        $summary  = $this->transaksiKegiatanService->getSummary();

        return view('pages.transaksi-kegiatan.index', compact('kegiatan', 'summary', 'search', 'status'));
    }

    // ── List Transaksi per Kegiatan ────────────────────────────
    public function show(Kegiatan $kegiatan, Request $request)
    {
        $this->authorizeKegiatan($kegiatan);

        $search        = $request->get('search', '');
        $transaksi     = $this->transaksiKegiatanService->getTransaksiByKegiatan($kegiatan, $search);
        $porsi         = $this->transaksiKegiatanService->getPorsiAnggaran($kegiatan);
        $dompetList    = $this->transaksiKegiatanService->getDompetList();
        $kategoriList  = $this->transaksiKegiatanService->getKategoriList();
        $kodeTransaksi = $this->transaksiKegiatanService->generateKodeTransaksi();

        return view('pages.transaksi-kegiatan.show', compact(
            'kegiatan', 'transaksi', 'porsi', 'search',
            'dompetList', 'kategoriList', 'kodeTransaksi'
        ));
    }

    // ── Simpan Transaksi ───────────────────────────────────────
    public function storeTransaksi(StoreTransaksiRequest $request, Kegiatan $kegiatan)
    {
        $this->authorizeKegiatan($kegiatan);

        if (! $kegiatan->isAktif()) {
            return back()->with('error', 'Kegiatan tidak sedang aktif');
        }

        $this->transaksiKegiatanService->storeTransaksi($kegiatan, $request->validated());

        return redirect()
            ->route('dashboard.transaksi-kegiatan.show', $kegiatan)
            ->with('success', 'Transaksi berhasil dicatat');
    }

    // ── Detail Transaksi ───────────────────────────────────────
    public function showTransaksi(Kegiatan $kegiatan, Transaksi $transaksi)
    {
        $this->ensureMilikKegiatan($kegiatan, $transaksi);

        $transaksi = $this->transaksiKegiatanService->getTransaksiById($transaksi);

        return view('pages.transaksi-kegiatan.show-transaksi', compact('kegiatan', 'transaksi'));
    }

    // ── Update Transaksi (hanya PENDING / REVISION) ───────────
    public function updateTransaksi(UpdateTransaksiRequest $request, Kegiatan $kegiatan, Transaksi $transaksi)
    {
        $this->ensureMilikKegiatan($kegiatan, $transaksi);

        if (! $transaksi->bisaDiedit()) {
            return back()->with('error', 'Transaksi tidak dapat diedit karena sudah diproses');
        }
        if ($transaksi->user_id !== auth()->id()) {
            abort(403);
        }

        $this->transaksiKegiatanService->updateTransaksi($transaksi, $request->validated());

        return redirect()
            ->route('dashboard.transaksi-kegiatan.show', $kegiatan)
            ->with('success', 'Transaksi berhasil diperbarui');
    }

    // ── Hapus Transaksi ────────────────────────────────────────
    public function destroyTransaksi(Kegiatan $kegiatan, Transaksi $transaksi)
    {
        $this->ensureMilikKegiatan($kegiatan, $transaksi);

        $result = $this->transaksiKegiatanService->deleteTransaksi($transaksi);

        if ($result !== true) {
            return back()->with('error', $result);
        }

        return redirect()
            ->route('dashboard.transaksi-kegiatan.show', $kegiatan)
            ->with('success', 'Transaksi berhasil dihapus');
    }

    // ── Helpers ────────────────────────────────────────────────
    private function authorizeKegiatan(Kegiatan $kegiatan): void
    {
        if (auth()->user()->hasRole('panitia-khusus') && $kegiatan->panitia_id !== auth()->id()) {
            abort(403);
        }
    }

    private function ensureMilikKegiatan(Kegiatan $kegiatan, Transaksi $transaksi): void
    {
        if ($transaksi->kegiatan_id !== $kegiatan->id) {
            abort(404);
        }
        $this->authorizeKegiatan($kegiatan);
    }
}
