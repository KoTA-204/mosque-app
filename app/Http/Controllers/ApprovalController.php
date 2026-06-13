<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Services\ApprovalService;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function __construct(
        protected ApprovalService $approvalService
    ) {}

    // ── Approval (Bendahara) ───────────────────────────────────
    public function approvalIndex(Request $request)
    {
        $search  = $request->get('search', '') ?? '';
        $sumber  = $request->get('sumber', '') ?? '';
        $dari    = $request->get('dari', '') ?? '';
        $sampai  = $request->get('sampai', '') ?? '';
        $urut    = $request->get('urut', 'asc') ?? 'asc';
        $perPage = (int) ($request->get('per_page', 10) ?? 10);

        $transaksi = $this->approvalService->getTransaksiPending(
            $search, $sumber, $dari, $sampai, $urut, $perPage
        );

        return view('pages.approval.index', compact(
            'transaksi', 'search', 'sumber', 'dari', 'sampai', 'urut', 'perPage'
        ));
    }

    public function approvalShow(Transaksi $transaksi)
    {
        $transaksi = $this->approvalService->getTransaksiById($transaksi);

        if ($transaksi->kencleng !== null) {
            return view('pages.approval.show-kencleng', compact('transaksi'));
        }

        return view('pages.approval.show', compact('transaksi'));
    }

    public function approve(Transaksi $transaksi)
    {
        $result = $this->approvalService->approve($transaksi);

        if ($result !== true) {
            return redirect()->back()->with('error', $result);
        }

        // Tutup kegiatan otomatis jika semua transaksi sudah APPROVED & tgl lewat
        if ($transaksi->kegiatan) {
            $transaksi->kegiatan->tutupJikaSelesai();
        }

        return redirect()->route('dashboard.approval.index')
            ->with('success', 'Transaksi berhasil disetujui');
    }

    public function reject(Request $request, Transaksi $transaksi)
    {
        $request->validate([
            'catatan' => 'nullable|string|max:500',
        ]);

        $result = $this->approvalService->reject($transaksi, $request->catatan ?? '');

        if ($result !== true) {
            return redirect()->back()->with('error', $result);
        }

        // Buka kembali kegiatan jika sebelumnya sudah ditutup
        if ($transaksi->kegiatan) {
            $transaksi->kegiatan->bukaKembali();
        }

        return redirect()->route('dashboard.approval.index')
            ->with('success', 'Transaksi berhasil ditolak');
    }

    public function revision(Request $request, Transaksi $transaksi)
    {
        $request->validate([
            'catatan' => 'required|string|max:500',
        ]);

        $result = $this->approvalService->revision($transaksi, $request->catatan);

        if ($result !== true) {
            return redirect()->back()->with('error', $result);
        }

        // Buka kembali kegiatan jika sebelumnya sudah ditutup
        if ($transaksi->kegiatan) {
            $transaksi->kegiatan->bukaKembali();
        }

        return redirect()->route('dashboard.approval.index')
            ->with('success', 'Transaksi dikembalikan untuk revisi');
    }

    // ── Bulk Approval (Bendahara) ──────────────────────────────
    public function bulkApprove(Request $request)
    {
        $request->validate(['ids' => 'required|string']);

        $ids = array_filter(array_map('intval', explode(',', $request->ids)));

        if (empty($ids)) {
            return redirect()->back()->with('error', 'Tidak ada transaksi yang dipilih');
        }

        // Ambil transaksi sebelum di-approve untuk bisa akses kegiatan-nya
        $transaksiList = \App\Models\Transaksi::whereIn('id', $ids)
            ->with('kegiatan')
            ->get();

        $result = $this->approvalService->bulkApprove($ids);

        // Cek semua kegiatan yang terlibat, hindari duplikat
        $transaksiList->pluck('kegiatan')
            ->filter()
            ->unique('id')
            ->each(fn($kegiatan) => $kegiatan->tutupJikaSelesai());

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

        $result = $this->approvalService->bulkReject($catatanMap);

        $msg = "{$result['rejected']} transaksi berhasil ditolak";
        if ($result['skipped'] > 0) {
            $msg .= ", {$result['skipped']} dilewati (bukan PENDING)";
        }

        return redirect()->route('dashboard.approval.index')->with('success', $msg);
    }
}