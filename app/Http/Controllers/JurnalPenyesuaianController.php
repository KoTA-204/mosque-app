<?php

namespace App\Http\Controllers;

use App\Models\Jurnal;
use App\Services\JurnalPenyesuaianService;
use Illuminate\Http\Request;

class JurnalPenyesuaianController extends Controller
{
    public function __construct(
        protected JurnalPenyesuaianService $service
    ) {}

    public function index(Request $request)
    {
        $search    = $request->get('search', '');
        $periodeId = $request->get('periode_id', '');
        $tipe      = $request->get('tipe', '');
        $status    = $request->get('status', '');
        $perPage   = (int) $request->get('per_page', 10);

        $jurnal       = $this->service->getList($search, $periodeId, $tipe, $status, $perPage);
        $periodeList  = $this->service->getPeriodeList();
        $periodeAktif = $this->service->getPeriodeAktif();
        $tipeLabels   = JurnalPenyesuaianService::TIPE_LABELS;

        return view('pages.jurnal-penyesuaian.index', compact(
            'jurnal', 'periodeList', 'periodeAktif',
            'tipeLabels', 'search', 'periodeId', 'tipe', 'status', 'perPage'
        ));
    }

    public function create()
    {
        $periodeAktif = $this->service->getPeriodeAktif();
        $periodeList  = $this->service->getPeriodeList();
        $tipeLabels   = JurnalPenyesuaianService::TIPE_LABELS;
        $tipeDescs    = JurnalPenyesuaianService::TIPE_DESCRIPTIONS;

        $akunList = $this->service->getAkunList('MANUAL');
        $asetList = $this->service->getAsetAktif();

        $akunPerTipe = [];
        foreach (array_keys($tipeLabels) as $tipe) {
            $akunPerTipe[$tipe] = $this->service->getAkunList($tipe);
        }

        return view('pages.jurnal-penyesuaian.create', compact(
            'periodeAktif', 'periodeList', 'akunList', 'akunPerTipe',
            'asetList', 'tipeLabels', 'tipeDescs'
        ));
    }

    public function store(Request $request)
    {
        $tipeKeys = implode(',', array_keys(JurnalPenyesuaianService::TIPE_LABELS));

        $rules = [
            'periode_id'       => 'required|exists:periode,id',
            'tanggal'          => 'required|date',
            'tipe_penyesuaian' => 'required|in:' . $tipeKeys,
            'keterangan'       => 'required|string|max:500',
            'detail'           => 'required|array|min:2',
            'detail.*.akun_id' => 'required|exists:akun,id',
            'detail.*.tipe'    => 'required|in:DEBIT,KREDIT',
            'detail.*.nominal' => 'required|string',
            'submit_type'      => 'required|in:draft,posting',
        ];

        if ($request->tipe_penyesuaian === 'PENYUSUTAN_ASET') {
            $rules['detail.0.aset_rows']           = 'required|array|min:1';
            $rules['detail.0.aset_rows.*.aset_id'] = 'required|exists:aset,id';
            $rules['detail.0.aset_rows.*.nominal']  = 'required|string';
        }

        $request->validate($rules);

        $status = $request->submit_type === 'posting' ? 'POSTED' : 'DRAFT';

        // Validasi balance sebelum posting
        if ($status === 'POSTED') {
            $totalDebit  = 0;
            $totalKredit = 0;
            foreach ($request->detail as $d) {
                $nominal = (float) str_replace(['.', ','], ['', '.'], $d['nominal'] ?? 0);
                if ($d['tipe'] === 'DEBIT')  $totalDebit  += $nominal;
                if ($d['tipe'] === 'KREDIT') $totalKredit += $nominal;
            }
            if (round($totalDebit, 2) !== round($totalKredit, 2)) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['balance' => 'Total debit dan kredit harus sama sebelum diposting.']);
            }
        }

        $this->service->store($request->all(), $status);

        $msg = $status === 'POSTED'
            ? 'Jurnal berhasil diposting ke buku besar.'
            : 'Jurnal berhasil disimpan sebagai draft.';

        return redirect()->route('dashboard.jurnal-penyesuaian.index')
            ->with('success', $msg);
    }

    public function show(Jurnal $jurnal)
    {
        $jurnal = $this->service->getById($jurnal);
        return response()->json([
            'jurnal' => $jurnal,
            'labels' => JurnalPenyesuaianService::TIPE_LABELS,
        ]);
    }

    /**
     * Bulk post — mengubah status beberapa jurnal DRAFT menjadi POSTED sekaligus.
     */
    public function bulkPost(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:jurnal,id',
        ]);

        $berhasil = 0;
        $gagal    = 0;
        $pesanGagal = [];

        foreach ($request->ids as $id) {
            $jurnal = Jurnal::find($id);

            if (!$jurnal) {
                $gagal++;
                continue;
            }

            $result = $this->service->post($jurnal);

            if ($result === true) {
                $berhasil++;
            } else {
                $gagal++;
                $pesanGagal[] = $result; // pesan error dari service
            }
        }

        if ($berhasil === 0) {
            // Semua gagal
            $detail = !empty($pesanGagal) ? ' (' . implode(', ', array_unique($pesanGagal)) . ')' : '';
            return redirect()->route('dashboard.jurnal-penyesuaian.index')
                ->with('error', "Tidak ada jurnal yang berhasil diposting.{$detail}");
        }

        $msg = "Berhasil memposting {$berhasil} jurnal.";
        if ($gagal > 0) {
            $msg .= " {$gagal} jurnal gagal (debit/kredit tidak balance atau sudah diposting).";
        }

        return redirect()->route('dashboard.jurnal-penyesuaian.index')
            ->with($gagal > 0 ? 'error' : 'success', $msg);
    }

    public function destroy(Jurnal $jurnal)
    {
        $result = $this->service->delete($jurnal);

        if ($result !== true) {
            return redirect()->back()->with('error', $result);
        }

        return redirect()->route('dashboard.jurnal-penyesuaian.index')
            ->with('success', 'Jurnal berhasil dihapus.');
    }
}