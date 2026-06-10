<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransaksiRequest;
use App\Models\Kegiatan;
use App\Models\Transaksi;
use App\Services\TransaksiKegiatanService;
use Illuminate\Http\Request;

class TransaksiKegiatanController extends Controller
{
    public function __construct(
        protected TransaksiKegiatanService $transaksiKegiatanService
    ) {}

    // ── List Kegiatan ──────────────────────────────────────────

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
        // Panitia hanya bisa lihat kegiatan miliknya
        if (auth()->user()->hasRole('panitia-khusus') &&
            $kegiatan->panitia_id !== auth()->id()) {
            abort(403);
        }

        $search    = $request->get('search', '');
        $transaksi = $this->transaksiKegiatanService->getTransaksiByKegiatan($kegiatan, $search);
        $porsi     = $this->transaksiKegiatanService->getPorsiAnggaran($kegiatan);
        $dompetList    = $this->transaksiKegiatanService->getDompetList();
        $kategoriList  = $this->transaksiKegiatanService->getKategoriList();
        $kodeTransaksi = $this->transaksiKegiatanService->generateKodeTransaksi();

        return view('pages.transaksi-kegiatan.show', compact('kegiatan', 'transaksi', 'porsi', 'search', 'dompetList', 'kategoriList', 'kodeTransaksi'));
    }

    // ── Form Catat Transaksi ───────────────────────────────────

    public function createTransaksi(Kegiatan $kegiatan)
    {
        // Cek kegiatan masih berjalan
        if ($kegiatan->status !== Kegiatan::STATUS_AKTIF) {
            return redirect()->route('dashboard.transaksi-kegiatan.show', $kegiatan)
                ->with('error', 'Kegiatan tidak sedang aktif');
        }

        if (auth()->user()->hasRole('panitia-khusus') &&
            $kegiatan->panitia_id !== auth()->id()) {
            abort(403);
        }

        return view('pages.transaksi-kegiatan.create-transaksi', compact(
            'kegiatan', 'dompetList', 'kategoriList', 'kodeTransaksi'
        ));
    }

    // ── Simpan Transaksi ───────────────────────────────────────

    public function storeTransaksi(StoreTransaksiRequest $request, Kegiatan $kegiatan)
    {
        if ($kegiatan->status !== Kegiatan::STATUS_AKTIF) {
            return redirect()->back()->with('error', 'Kegiatan tidak sedang aktif');
        }

        if (auth()->user()->hasRole('panitia-khusus') &&
            $kegiatan->panitia_id !== auth()->id()) {
            abort(403);
        }

        $this->transaksiKegiatanService->storeTransaksi($kegiatan, $request->validated());

        return redirect()->route('dashboard.transaksi-kegiatan.show', $kegiatan)
            ->with('success', 'Transaksi berhasil dicatat');
    }

    // ── Detail Transaksi ───────────────────────────────────────

    public function showTransaksi(Kegiatan $kegiatan, Transaksi $transaksi)
    {
        if ($transaksi->kegiatan_id !== $kegiatan->id) {
            abort(404);
        }

        $transaksi = $this->transaksiKegiatanService->getTransaksiById($transaksi);

        return view('pages.transaksi-kegiatan.show-transaksi', compact('kegiatan', 'transaksi'));
    }

    // ── Hapus Transaksi ────────────────────────────────────────

    public function destroyTransaksi(Kegiatan $kegiatan, Transaksi $transaksi)
    {
        if ($transaksi->kegiatan_id !== $kegiatan->id) {
            abort(404);
        }

        $result = $this->transaksiKegiatanService->deleteTransaksi($transaksi);

        if ($result !== true) {
            return redirect()->back()->with('error', $result);
        }

        return redirect()->route('dashboard.transaksi-kegiatan.show', $kegiatan)
            ->with('success', 'Transaksi berhasil dihapus');
    }

    // ── Edit Transaksi setelah REVISION (Panitia) ──────────────

    public function editTransaksi(Kegiatan $kegiatan, Transaksi $transaksi)
    {
        if (!in_array($transaksi->status_approval, ['REVISION', 'PENDING'])) {
            return redirect()->back()->with(
                'error',
                'Transaksi tidak dapat diedit'
            );
        }

        if ($transaksi->user_id !== auth()->id()) {
            abort(403);
        }

        $transaksi    = $this->transaksiKegiatanService->getTransaksiById($transaksi);
        $dompetList   = $this->transaksiKegiatanService->getDompetList();
        $kategoriList = $this->transaksiKegiatanService->getKategoriList();

        return view(
            'pages.transaksi-kegiatan.edit-transaksi',
            compact(
                'kegiatan',
                'transaksi',
                'dompetList',
                'kategoriList'
            )
        );
    }

    public function updateTransaksi(
        Request $request,
        Kegiatan $kegiatan,
        Transaksi $transaksi
    ) {
        if (!in_array($transaksi->status_approval, ['REVISION', 'PENDING'])) {
            return redirect()->back()->with(
                'error',
                'Transaksi tidak dapat diedit'
            );
        }

        if ($transaksi->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'tanggal_transaksi'     => 'required|date',
            'jumlah'                => 'required|numeric|min:1',
            'dompet_id'             => 'required|exists:dompet,id',
            'kategori_transaksi_id' => 'required|exists:kategori_transaksi,id',
            'deskripsi'             => 'nullable|string|max:500',
            'bukti_transaksi'       => 'nullable|array',
            'bukti_transaksi.*'     => 'file|mimes:jpg,jpeg,png,pdf|max:5120',
            'hapus_bukti'           => 'nullable|array',
            'hapus_bukti.*'         => 'integer|exists:bukti_transaksi,id',
        ]);

        $this->transaksiKegiatanService->updateTransaksi(
            $transaksi,
            $request->all()
        );

        return redirect()
            ->route('dashboard.transaksi-kegiatan.show', $kegiatan)
            ->with(
                'success',
                'Transaksi berhasil diperbarui'
            );
    }
}