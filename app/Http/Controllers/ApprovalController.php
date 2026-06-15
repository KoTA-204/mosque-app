<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Services\ApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function __construct(
        protected ApprovalService $approvalService
    ) {}

    // ── Index & Show ──────────────────────────────────────────────────────
    public function approvalIndex(Request $request)
    {
        $search  = $request->get('search', '') ?? '';
        $sumber  = $request->get('sumber', '') ?? '';
        $dari    = $request->get('dari', '') ?? '';
        $sampai  = $request->get('sampai', '') ?? '';
        $urut    = $request->get('urut', 'asc') ?? 'asc';
        $perPage = (int) ($request->get('per_page', 10) ?? 10);

        $tab     = strtoupper($request->get('tab', 'PENDING') ?? 'PENDING');
        if (! in_array($tab, ['PENDING', 'APPROVED', 'REJECTED', 'REVISION'], true)) {
            $tab = 'PENDING';
        }

        $stats     = $this->approvalService->getStats();
        $transaksi = $this->approvalService->getTransaksiByStatus(
            $tab, $search, $sumber, $dari, $sampai, $urut, $perPage
        );

        return view('pages.approval.index', compact(
            'transaksi', 'stats', 'tab', 'search', 'sumber', 'dari', 'sampai', 'urut', 'perPage'
        ));
    }

    public function approvalShow(Transaksi $transaksi)
    {
        $transaksi = $this->approvalService->getTransaksiById($transaksi);

        return $transaksi->kencleng !== null
            ? view('pages.approval.show-kencleng', compact('transaksi'))
            : view('pages.approval.show', compact('transaksi'));
    }

    // ── Single Actions ────────────────────────────────────────────────────
    public function approve(Transaksi $transaksi): RedirectResponse
    {
        $result = $this->approvalService->approve($transaksi);

        return $result !== true
            ? redirect()->back()->with('error', $result)
            : redirect()->route('dashboard.approval.index')->with('success', 'Transaksi berhasil disetujui');
    }

    public function reject(Request $request, Transaksi $transaksi): RedirectResponse
    {
        $request->validate(['catatan' => 'nullable|string|max:500']);

        $result = $this->approvalService->reject($transaksi, $request->catatan ?? '');

        return $result !== true
            ? redirect()->back()->with('error', $result)
            : redirect()->route('dashboard.approval.index')->with('success', 'Transaksi berhasil ditolak');
    }

    public function revision(Request $request, Transaksi $transaksi): RedirectResponse
    {
        $request->validate(['catatan' => 'required|string|max:500']);

        $result = $this->approvalService->revision($transaksi, $request->catatan);

        return $result !== true
            ? redirect()->back()->with('error', $result)
            : redirect()->route('dashboard.approval.index')->with('success', 'Transaksi dikembalikan untuk revisi');
    }

    // ── Bulk Actions ──────────────────────────────────────────────────────
    private function handleBulk(Request $request, string $action): RedirectResponse
    {
        $labels = [
            'approve' => 'disetujui',
            'reject'  => 'ditolak',
            'revisi'  => 'direvisi',
        ];

        if ($action === 'approve') {
            $request->validate(['ids' => 'required|string']);
            $ids = array_filter(array_map('intval', explode(',', $request->ids)));

            if (empty($ids)) {
                return redirect()->back()->with('error', 'Tidak ada transaksi yang dipilih');
            }

            $result = $this->approvalService->bulkApprove($ids);
        } else {
            $request->validate([
                'ids'       => 'required|array|min:1',
                'ids.*'     => 'integer|exists:transaksi,id',
                'catatan.*' => 'nullable|string|max:500',
            ]);

            $catatanMap = collect($request->ids)
                ->mapWithKeys(fn($id) => [(int) $id => $request->catatan[$id] ?? null])
                ->all();

            $result = $action === 'reject'
                ? $this->approvalService->bulkReject($catatanMap)
                : $this->approvalService->bulkRevisi($catatanMap);
        }

        $msg = "{$result['done']} transaksi berhasil {$labels[$action]}";
        if ($result['skipped'] > 0) {
            $msg .= ", {$result['skipped']} dilewati (bukan PENDING)";
        }

        return redirect()->route('dashboard.approval.index')->with('success', $msg);
    }

    public function bulkApprove(Request $request): RedirectResponse
    {
        return $this->handleBulk($request, 'approve');
    }

    public function bulkReject(Request $request): RedirectResponse
    {
        return $this->handleBulk($request, 'reject');
    }

    public function bulkRevisi(Request $request): RedirectResponse
    {
        return $this->handleBulk($request, 'revisi');
    }
}