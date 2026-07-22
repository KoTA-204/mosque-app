<?php

namespace App\Http\Controllers\Akuntansi;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkPostRequest;
use App\Http\Requests\StoreJurnalKoreksiRequest;
use App\Models\Jurnal;
use App\Services\Akuntansi\JurnalKoreksiService;
use Illuminate\Http\Request;

class JurnalKoreksiController extends Controller
{
    public function __construct(protected JurnalKoreksiService $service) {}

    public function tampilkanJurnalKoreksi(Request $request)
    {
        $filter = [
            'search'     => $request->get('search', ''),
            'periode_id' => $request->get('periode_id', ''),
            'status'     => $request->get('status', ''),
            'per_page'   => (int) $request->get('per_page', 10),
        ];

        $jurnal       = $this->service->daftar($filter);
        $periodeList  = $this->service->getPeriodeList();
        $periodeAktif = $this->service->getPeriodeAktif();

        $search    = $filter['search'];
        $periodeId = $filter['periode_id'];
        $status    = $filter['status'];
        $perPage   = $filter['per_page'];

        return view('pages.akuntansi.jurnal-koreksi.index', compact(
            'jurnal', 'periodeList', 'periodeAktif',
            'search', 'periodeId', 'status', 'perPage'
        ));
    }

    public function tambahJurnalKoreksi()
    {
        $periodeAktif = $this->service->getPeriodeAktif();
        $periodeList  = $this->service->getPeriodeList();
        $jurnalData   = $this->service->getJurnalData();
        $akunList     = $this->service->getAkunList();

        return view('pages.akuntansi.jurnal-koreksi.create', compact(
            'periodeAktif', 'periodeList', 'akunList', 'jurnalData'
        ));
    }

    public function simpanJurnalKoreksi(StoreJurnalKoreksiRequest $request)
    {
        $status = $request->submit_type === 'posting' ? 'POSTED' : 'DRAFT';

        if ($status === 'POSTED' && !$this->service->isDetailSeimbang($request->detail)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['balance' => 'Total debit dan kredit harus sama sebelum diposting.']);
        }

        $this->service->catatKoreksi($request->validated(), $status);

        $msg = $status === 'POSTED'
            ? 'Jurnal koreksi berhasil diposting ke buku besar.'
            : 'Jurnal koreksi berhasil disimpan sebagai draft.';

        return redirect()->route('dashboard.jurnal-koreksi.index')->with('success', $msg);
    }

    public function tampilkanDetailJurnalKoreksi(Jurnal $jurnal)
    {
        if (! request()->ajax()) {
            return redirect()->route('dashboard.jurnal-koreksi.index', ['buka' => $jurnal->id]);
        }

        $jurnal = $this->service->getById($jurnal);

        return response()->json([
            'jurnal' => [
                'id'            => $jurnal->id,
                'nomor_jurnal'  => $jurnal->kode_jurnal,
                'tanggal'       => $jurnal->tanggal?->format('j M Y'),
                'keterangan'    => $jurnal->keterangan,
                'status'        => $jurnal->status,
                'jenis_jurnal'  => $jurnal->jenis_jurnal,
                'jurnal_ref_id' => $jurnal->jurnal_ref_id,
                'jurnal_ref'    => $jurnal->jurnalRef ? [
                    'id'         => $jurnal->jurnalRef->id,
                    'keterangan' => $jurnal->jurnalRef->keterangan,
                    'tanggal'    => $jurnal->jurnalRef->tanggal?->format('j M Y'),
                ] : null,
                'periode' => [
                    'nama_periode' => $jurnal->periode->nama_periode ?? '—',
                ],
                'detail_jurnal' => $jurnal->detailJurnal->map(fn($d) => [
                    'tipe'    => $d->tipe,
                    'nominal' => (float) $d->nominal,
                    'akun'    => [
                        'kode_akun' => $d->akun->kode_akun ?? '',
                        'nama_akun' => $d->akun->nama_akun ?? '—',
                    ],
                ]),
                'aset' => $jurnal->aset->map(fn($a) => [
                    'nama_aset' => $a->nama_aset,
                    'pivot'     => ['nominal' => (float) ($a->pivot->nominal ?? 0)],
                ]),
            ],
        ]);
    }

    public function post(Jurnal $jurnal)
    {
        $result = $this->service->postingKeBukuBesar($jurnal);

        return $result === true
            ? back()->with('success', 'Jurnal berhasil diposting.')
            : back()->with('error', $result);
    }

    public function bulkPost(BulkPostRequest $request)
    {
        $result = $this->service->postingMassalKeBukuBesar($request->validated()['ids']);

        return redirect()->route('dashboard.jurnal-koreksi.index')
            ->with($result['failed'] > 0 ? 'error' : 'success', $result['message']);
    }

    public function hapusJurnalKoreksi(Jurnal $jurnal)
    {
        $result = $this->service->hapusJurnal($jurnal);

        if ($result !== true) {
            return redirect()->back()->with('error', $result);
        }

        return redirect()->route('dashboard.jurnal-koreksi.index')
            ->with('success', 'Jurnal berhasil dihapus.');
    }
}
