<?php

namespace App\Http\Controllers;

use App\Models\Jurnal;
use App\Models\Akun;
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

    public function create()
    {
        $akuns    = Akun::with('kategoriAkun')->whereNotNull('parent_id')->orderBy('kode_akun')->get();
        $periodes = $this->jurnal->getPeriodeList();
        return view('pages.jurnal-umum.create', compact('akuns', 'periodes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'periode_id'         => 'required|exists:periode,id',
            'tanggal'            => 'required|date',
            'keterangan'         => 'nullable|string|max:500',
            'detail'             => 'required|array|min:2',
            'detail.*.akun_id'   => 'required|exists:akun,id',
            'detail.*.tipe'      => 'required|in:DEBIT,KREDIT',
            'detail.*.nominal'   => 'required',
        ]);

        $this->jurnal->simpan($request->all());

        return redirect()->route('dashboard.jurnal-umum.index')
            ->with('success', 'Jurnal umum berhasil disimpan.');
    }

    public function show(Jurnal $jurnalUmum)
    {
        $jurnalUmum->load('periode', 'detailJurnal.akun');
        return view('pages.jurnal-umum.show', ['jurnal' => $jurnalUmum]);
    }

    public function edit(Jurnal $jurnalUmum)
    {
        if ($jurnalUmum->status === 'POSTED') {
            return redirect()->route('dashboard.jurnal-umum.index')
                ->with('error', 'Jurnal yang sudah diposting tidak dapat diedit.');
        }
        $jurnalUmum->load('detailJurnal.akun');
        $akuns    = Akun::with('kategoriAkun')->whereNotNull('parent_id')->orderBy('kode_akun')->get();
        $periodes = $this->jurnal->getPeriodeList();
        return view('pages.jurnal-umum.edit', [
            'jurnal'   => $jurnalUmum,
            'akuns'    => $akuns,
            'periodes' => $periodes,
        ]);
    }

    public function update(Request $request, Jurnal $jurnalUmum)
    {
        if ($jurnalUmum->status === 'POSTED') {
            return back()->with('error', 'Jurnal yang sudah diposting tidak dapat diubah.');
        }

        $request->validate([
            'periode_id'       => 'required|exists:periode,id',
            'tanggal'          => 'required|date',
            'keterangan'       => 'nullable|string|max:500',
            'detail'           => 'required|array|min:2',
            'detail.*.akun_id' => 'required|exists:akun,id',
            'detail.*.tipe'    => 'required|in:DEBIT,KREDIT',
            'detail.*.nominal' => 'required',
        ]);

        $this->jurnal->perbarui($jurnalUmum, $request->all());

        return redirect()->route('dashboard.jurnal-umum.index')
            ->with('success', 'Jurnal umum berhasil diperbarui.');
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