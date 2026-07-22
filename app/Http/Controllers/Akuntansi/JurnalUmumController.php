<?php

namespace App\Http\Controllers\Akuntansi;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkPostRequest;
use App\Models\Jurnal;
use App\Services\Akuntansi\JurnalUmumService;
use Illuminate\Http\Request;

class JurnalUmumController extends Controller
{
    public function __construct(private JurnalUmumService $service) {}

    public function tampilkanJurnalUmum(Request $request)
    {
        $filter = [
            'search'   => $request->input('search', ''),
            'bulan'    => $request->input('bulan', now()->format('Y-m')),
            'status'   => $request->input('status', ''),
            'per_page' => $request->input('per_page', 10),
        ];

        $jurnals = $this->service->daftar($filter);
        ['totalDebit' => $totalDebit, 'totalKredit' => $totalKredit] = $this->service->getRingkasan($filter);
        $periodes = $this->service->getPeriodeList();
        $stats    = $this->service->getStatistik($filter);

        if ($request->ajax()) {
            return response()->json([
                'html'  => view('pages.akuntansi.jurnal-umum.table', compact('jurnals'))->render(),
                'stats' => $stats,
            ]);
        }

        $search = $filter['search'];
        $bulan  = $filter['bulan'];
        $status = $filter['status'];

        return view('pages.akuntansi.jurnal-umum.index', compact(
            'jurnals', 'totalDebit', 'totalKredit', 'periodes', 'bulan', 'search', 'status', 'stats'
        ));
    }

    public function tampilkanDetailJurnalUmum(Jurnal $jurnalUmum)
    {
        $jurnalUmum->load('periode', 'detailJurnal.akun');
        return view('pages.akuntansi.jurnal-umum.show', ['jurnal' => $jurnalUmum]);
    }

    public function post(Jurnal $jurnalUmum)
    {
        $result = $this->service->postingKeBukuBesar($jurnalUmum);

        return $result === true
            ? back()->with('success', 'Jurnal berhasil diposting.')
            : back()->with('error', $result);
    }

    public function bulkPost(BulkPostRequest $request)
    {
        $result = $this->service->postingMassalKeBukuBesar($request->validated()['ids']);
        return $this->bulkResponse($request, $result['success'], $result['message']);
    }

    public function hapusJurnalUmum(Jurnal $jurnalUmum)
    {
        $result = $this->service->hapusJurnal($jurnalUmum);

        return $result === true
            ? redirect()->route('dashboard.jurnal-umum.index')->with('success', 'Jurnal umum berhasil dihapus.')
            : back()->with('error', $result);
    }

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
