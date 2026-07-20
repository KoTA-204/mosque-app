<?php

namespace App\Http\Controllers\Operasional;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Services\Operasional\PersetujuanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PersetujuanController extends Controller
{
    public function __construct(
        protected PersetujuanService $persetujuanService
    ) {}

    // ── Index & Show ──────────────────────────────────────────────────────
    public function tampilkanDaftarPersetujuan(Request $request)
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

        $stats     = $this->persetujuanService->hitungStatistikPersetujuan();
        $transaksi = $this->persetujuanService->getTransaksiBerdasarkanStatus(
            $tab, $search, $sumber, $dari, $sampai, $urut, $perPage
        );

        return view('pages.operasional.persetujuan.index', compact(
            'transaksi', 'stats', 'tab', 'search', 'sumber', 'dari', 'sampai', 'urut', 'perPage'
        ));
    }

    public function tampilkanDetailPersetujuan(Transaksi $transaksi)
    {
        $transaksi = $this->persetujuanService->getDetailTransaksi($transaksi);

        return $transaksi->kencleng !== null
            ? view('pages.operasional.persetujuan.show-kencleng', compact('transaksi'))
            : view('pages.operasional.persetujuan.show', compact('transaksi'));
    }

    // ── Single Actions ────────────────────────────────────────────────────
    public function setujuiTransaksi(Transaksi $transaksi): RedirectResponse
    {
        $result = $this->persetujuanService->setujuiTransaksi($transaksi);

        return $result !== true
            ? redirect()->back()->with('error', $result)
            : redirect()->route('dashboard.persetujuan.index')->with('success', 'Transaksi berhasil disetujui');
    }

    public function tolakTransaksi(Request $request, Transaksi $transaksi): RedirectResponse
    {
        $request->validate(['catatan' => 'nullable|string|max:500']);

        $result = $this->persetujuanService->tolakTransaksi($transaksi, $request->catatan ?? '');

        return $result !== true
            ? redirect()->back()->with('error', $result)
            : redirect()->route('dashboard.persetujuan.index')->with('success', 'Transaksi berhasil ditolak');
    }

    public function revisiTransaksi(Request $request, Transaksi $transaksi): RedirectResponse
    {
        $request->validate(['catatan' => 'required|string|max:500']);

        $result = $this->persetujuanService->revisiTransaksi($transaksi, $request->catatan);

        return $result !== true
            ? redirect()->back()->with('error', $result)
            : redirect()->route('dashboard.persetujuan.index')->with('success', 'Transaksi dikembalikan untuk revisi');
    }

    // ── Bulk Actions ──────────────────────────────────────────────────────
    private function prosesAksiMassal(Request $request, string $action): RedirectResponse
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

            $result = $this->persetujuanService->setujuiTransaksiMassal($ids);
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
                ? $this->persetujuanService->tolakTransaksiMassal($catatanMap)
                : $this->persetujuanService->revisiTransaksiMassal($catatanMap);
        }

        $msg = "{$result['done']} transaksi berhasil {$labels[$action]}";
        if ($result['skipped'] > 0) {
            $msg .= ", {$result['skipped']} dilewati (bukan PENDING)";
        }

        return redirect()->route('dashboard.persetujuan.index')->with('success', $msg);
    }

    public function setujuiTransaksiMassal(Request $request): RedirectResponse
    {
        return $this->prosesAksiMassal($request, 'approve');
    }

    public function tolakTransaksiMassal(Request $request): RedirectResponse
    {
        return $this->prosesAksiMassal($request, 'reject');
    }

    public function revisiTransaksiMassal(Request $request): RedirectResponse
    {
        return $this->prosesAksiMassal($request, 'revisi');
    }
}