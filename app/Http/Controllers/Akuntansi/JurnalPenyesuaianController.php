<?php

namespace App\Http\Controllers\Akuntansi;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkPostRequest;
use App\Http\Requests\StoreJurnalPenyesuaianRequest;
use App\Models\Jurnal;
use App\Services\Akuntansi\JurnalPenyesuaianService;
use Illuminate\Http\Request;

class JurnalPenyesuaianController extends Controller
{
    public function __construct(protected JurnalPenyesuaianService $service) {}

    public function tampilkanJurnalPenyesuaian(Request $request)
    {
        $filter = [
            'search'    => $request->get('search', ''),
            'periode_id' => $request->get('periode_id', ''),
            'tipe'      => $request->get('tipe', ''),
            'status'    => $request->get('status', ''),
            'per_page'  => (int) $request->get('per_page', 10),
        ];

        $jurnal       = $this->service->daftar($filter);
        $periodeList  = $this->service->getPeriodeList();
        $periodeAktif = $this->service->getPeriodeAktif();
        $tipeLabels   = JurnalPenyesuaianService::TIPE_LABELS;

        $search    = $filter['search'];
        $periodeId = $filter['periode_id'];
        $tipe      = $filter['tipe'];
        $status    = $filter['status'];
        $perPage   = $filter['per_page'];

        return view('pages.akuntansi.jurnal-penyesuaian.index', compact(
            'jurnal', 'periodeList', 'periodeAktif',
            'tipeLabels', 'search', 'periodeId', 'tipe', 'status', 'perPage'
        ));
    }

    public function tambahJurnalPenyesuaian()
    {
        $periodeAktif = $this->service->getPeriodeAktif();
        $periodeList  = $this->service->getPeriodeList();
        $tipeLabels   = JurnalPenyesuaianService::TIPE_LABELS;
        $tipeDescs    = JurnalPenyesuaianService::TIPE_DESCRIPTIONS;

        $akunList = $this->service->getAkunList('MANUAL');
        $asetList = $this->service->getAsetAktif();
        $asetPelepasanList = $this->service->getAsetUntukPelepasan();

        $akunPerTipe = [];
        foreach (array_keys($tipeLabels) as $tipe) {
            $akunPerTipe[$tipe] = $this->service->getAkunList($tipe);
        }

        return view('pages.akuntansi.jurnal-penyesuaian.create', compact(
            'periodeAktif', 'periodeList', 'akunList', 'akunPerTipe',
            'asetList', 'asetPelepasanList', 'tipeLabels', 'tipeDescs'
        ));
    }

    public function simpanJurnalPenyesuaian(StoreJurnalPenyesuaianRequest $request)
    {
        $status = $request->submit_type === 'posting' ? 'POSTED' : 'DRAFT';

        if ($status === 'POSTED' && !$this->service->isDetailSeimbang($request->detail)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['balance' => 'Total debit dan kredit harus sama sebelum diposting.']);
        }

        try {
            $this->service->catatPenyesuaian($request->validated(), $status);
        } catch (\RuntimeException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        $msg = $status === 'POSTED'
            ? 'Jurnal berhasil diposting ke buku besar.'
            : 'Jurnal berhasil disimpan sebagai draft.';

        return redirect()->route('dashboard.jurnal-penyesuaian.index')->with('success', $msg);
    }

    public function tampilkanDetailJurnalPenyesuaian(Jurnal $jurnal)
    {
        if (! request()->ajax()) {
            return redirect()->route('dashboard.jurnal-penyesuaian.index', ['buka' => $jurnal->id]);
        }

        $jurnal = $this->service->getById($jurnal);

        return response()->json([
            'jurnal' => $jurnal,
            'labels' => JurnalPenyesuaianService::TIPE_LABELS,
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

        return redirect()->route('dashboard.jurnal-penyesuaian.index')
            ->with($result['failed'] > 0 ? 'error' : 'success', $result['message']);
    }

    public function hapusJurnalPenyesuaian(Jurnal $jurnal)
    {
        $result = $this->service->hapusJurnal($jurnal);

        if ($result !== true) {
            return redirect()->back()->with('error', $result);
        }

        return redirect()->route('dashboard.jurnal-penyesuaian.index')
            ->with('success', 'Jurnal berhasil dihapus.');
    }
}
