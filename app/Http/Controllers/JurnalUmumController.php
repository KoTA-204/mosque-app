<?php

namespace App\Http\Controllers;

use App\Models\Jurnal;
use App\Services\JurnalUmumService;
use Illuminate\Http\Request;

class JurnalUmumController extends Controller
{
    public function __construct(private JurnalUmumService $jurnal) {}

    public function index(Request $request)
    {
        $filter = [
            'search'   => $request->input('search', ''),
            'bulan'    => $request->input('bulan', now()->format('Y-m')),
            'status'   => $request->input('status', ''),
            'per_page' => $request->input('per_page', 10),
        ];

        $jurnals = $this->jurnal->daftar($filter);
        ['totalDebit' => $totalDebit, 'totalKredit' => $totalKredit] = $this->jurnal->summary($filter);
        $periodes = $this->jurnal->getPeriodeList();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('pages.jurnal-umum.table', compact('jurnals'))->render(),
            ]);
        }

        $search = $filter['search'];
        $bulan  = $filter['bulan'];
        $status = $filter['status'];
        return view('pages.jurnal-umum.index', compact(
            'jurnals', 'totalDebit', 'totalKredit', 'periodes', 'bulan', 'search', 'status'
        ));
    }

    public function show(Jurnal $jurnalUmum)
    {
        $jurnalUmum->load('periode', 'detailJurnal.akun');
        return view('pages.jurnal-umum.show', ['jurnal' => $jurnalUmum]);
    }

    public function post(Jurnal $jurnalUmum)
    {
        $result = $this->jurnal->post($jurnalUmum); // dari abstract
        return $result === true
            ? back()->with('success', 'Jurnal berhasil diposting.')
            : back()->with('error', $result);
    }

    public function bulkPost(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return $this->bulkResponse($request, false, 'Tidak ada jurnal yang dipilih.');
        }
        $result = $this->jurnal->bulkPosting($ids);
        return $this->bulkResponse($request, $result['success'], $result['message']);
    }

    public function destroy(Jurnal $jurnalUmum)
    {
        $result = $this->jurnal->delete($jurnalUmum); // dari abstract
        return $result === true
            ? redirect()->route('dashboard.jurnal-umum.index')->with('success', 'Jurnal umum berhasil dihapus.')
            : back()->with('error', $result);
    }

    // untuk response bulk (ajax JSON / redirect biasa)
    private function bulkResponse(Request $request, bool $success, string $message)
    {
        if ($request->ajax()) {
            return response()->json([
                'success' => $success,
                'message' => $message,
                'alert'   => (string) view('components.jurnal.alert', [
                    'type'    => $success ? 'success' : 'error',
                    'message' => $message,
                ]),
            ]);
        }
        return back()->with($success ? 'success' : 'error', $message);
    }
}