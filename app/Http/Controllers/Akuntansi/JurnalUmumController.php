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

        if ($request->ajax()) {
            return response()->json([
                'html' => view('pages.akuntansi.jurnal-umum.table', compact('jurnals'))->render(),
            ]);
        }

        $search = $filter['search'];
        $bulan  = $filter['bulan'];
        $status = $filter['status'];

        return view('pages.akuntansi.jurnal-umum.index', compact(
            'jurnals', 'totalDebit', 'totalKredit', 'periodes', 'bulan', 'search', 'status'
        ));
    }

    public function tampilkanDetailJurnalUmum(Jurnal $jurnalUmum)
    {
        // Endpoint ini melayani drawer via AJAX (JSON). Jika diakses langsung
        // dari browser (mis. tautan "Buka & posting"), arahkan ke halaman index
        // agar tidak menampilkan JSON mentah; drawer terbuka otomatis via ?buka.
        if (! request()->ajax()) {
            return redirect()->route('dashboard.jurnal-umum.index', ['buka' => $jurnalUmum->id]);
        }

        $jurnalUmum->load('periode', 'transaksi', 'detailJurnal.akun');

        return response()->json([
            'jurnal' => [
                'id'           => $jurnalUmum->id,
                'nomor_jurnal' => $jurnalUmum->kode_jurnal,
                'tanggal'      => $jurnalUmum->tanggal?->format('j M Y'),
                'keterangan'   => $jurnalUmum->keterangan ?: $jurnalUmum->transaksi?->deskripsi,
                'status'       => $jurnalUmum->status,
                'periode'      => [
                    'nama_periode' => $jurnalUmum->periode->nama_periode ?? '—',
                ],
                'detail_jurnal' => $jurnalUmum->detailJurnal->map(fn($d) => [
                    'tipe'    => $d->tipe,
                    'nominal' => (float) $d->nominal,
                    'akun'    => [
                        'kode_akun' => $d->akun->kode_akun ?? '',
                        'nama_akun' => $d->akun->nama_akun ?? '—',
                    ],
                ]),
            ],
        ]);
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
