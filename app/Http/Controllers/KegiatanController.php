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

    // ── Approval (Bendahara) ───────────────────────────────────

    public function approvalIndex(Request $request)
    {
        $search  = $request->get('search', '');
        $sumber  = $request->get('sumber', '');   // '' | 'kegiatan' | 'kencleng'
        $dari    = $request->get('dari', '');
        $sampai  = $request->get('sampai', '');
 
        $transaksi = $this->kegiatanService->getTransaksiPending($search, $sumber, $dari, $sampai);
 
        return view('pages.approval.index', compact('transaksi', 'search', 'sumber', 'dari', 'sampai'));
    }

    public function approvalShow(Transaksi $transaksi)
    {
        $transaksi = $this->kegiatanService->getTransaksiById($transaksi);
 
        // Kencleng → view khusus kencleng
        if ($transaksi->kencleng !== null) {
            return view('pages.approval.show-kencleng', compact('transaksi'));
        }
 
        // Kegiatan → view biasa
        return view('pages.approval.show', compact('transaksi'));
    }

    public function approve(Transaksi $transaksi)
    {
        $result = $this->kegiatanService->approve($transaksi);

        if ($result !== true) {
            return redirect()->back()->with('error', $result);
        }

        return redirect()->route('dashboard.approval.index')
            ->with('success', 'Transaksi berhasil disetujui');
    }

    public function reject(Request $request, Transaksi $transaksi)
    {
        $request->validate([
            'catatan' => 'nullable|string|max:500',
        ]);

        $result = $this->kegiatanService->reject($transaksi, $request->catatan ?? '');

        if ($result !== true) {
            return redirect()->back()->with('error', $result);
        }

        return redirect()->route('dashboard.approval.index')
            ->with('success', 'Transaksi berhasil ditolak');
    }

    public function revision(Request $request, Transaksi $transaksi)
    {
        $request->validate([
            'catatan' => 'required|string|max:500',
        ]);

        $result = $this->kegiatanService->revision($transaksi, $request->catatan);

        if ($result !== true) {
            return redirect()->back()->with('error', $result);
        }

        return redirect()->route('dashboard.approval.index')
            ->with('success', 'Transaksi dikembalikan untuk revisi');
    }

    // ── Edit Transaksi setelah REVISION (Panitia) ──────────────

    public function editTransaksi(Kegiatan $kegiatan, Transaksi $transaksi)
    {
        if ($transaksi->status_approval !== 'REVISION') {
            return redirect()->back()->with('error', 'Transaksi tidak dalam status revisi');
        }

        if ($transaksi->user_id !== auth()->id()) {
            abort(403);
        }

        $transaksi    = $this->kegiatanService->getTransaksiById($transaksi);
        $dompetList   = $this->kegiatanService->getDompetList();
        $kategoriList = $this->kegiatanService->getKategoriList();

        return view('pages.kegiatan.edit-transaksi', compact('kegiatan', 'transaksi', 'dompetList', 'kategoriList'));
    }

    public function updateTransaksi(Request $request, Kegiatan $kegiatan, Transaksi $transaksi)
    {
        if ($transaksi->status_approval !== 'REVISION') {
            return redirect()->back()->with('error', 'Transaksi tidak dalam status revisi');
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

        $this->kegiatanService->updateTransaksi($transaksi, $request->all());

        return redirect()->route('dashboard.kegiatan.show', $kegiatan)
            ->with('success', 'Transaksi berhasil diperbaiki dan dikirim ulang');
    }

    // ── Bulk Approval (Bendahara) ──────────────────────────────

    public function bulkApprove(Request $request)
    {
        $request->validate(['ids' => 'required|string']);
 
        $ids = array_filter(array_map('intval', explode(',', $request->ids)));
 
        if (empty($ids)) {
            return redirect()->back()->with('error', 'Tidak ada transaksi yang dipilih');
        }
 
        $result = $this->kegiatanService->bulkApprove($ids);
 
        $msg = "{$result['approved']} transaksi berhasil disetujui";
        if ($result['skipped'] > 0) {
            $msg .= ", {$result['skipped']} dilewati (bukan PENDING)";
        }
 
        return redirect()->route('dashboard.approval.index')->with('success', $msg);
    }
 
    public function bulkReject(Request $request)
    {
        $request->validate([
            'ids'       => 'required|array|min:1',
            'ids.*'     => 'integer|exists:transaksi,id',
            'catatan'   => 'nullable|array',
            'catatan.*' => 'nullable|string|max:500',
        ]);
 
        $catatanMap = [];
        foreach ($request->ids as $id) {
            $catatanMap[(int) $id] = $request->catatan[$id] ?? null;
        }
 
        $result = $this->kegiatanService->bulkReject($catatanMap);
 
        $msg = "{$result['rejected']} transaksi berhasil ditolak";
        if ($result['skipped'] > 0) {
            $msg .= ", {$result['skipped']} dilewati (bukan PENDING)";
        }
 
        return redirect()->route('dashboard.approval.index')->with('success', $msg);
    }
}