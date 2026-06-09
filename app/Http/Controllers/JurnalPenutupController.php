<?php

namespace App\Http\Controllers;

use App\Models\Jurnal;
use App\Models\Periode;
use App\Services\JurnalPenutupService;
use Illuminate\Http\Request;

class JurnalPenutupController extends Controller
{
    public function __construct(
        protected JurnalPenutupService $service
    ) {}

    public function index(Request $request)
    {
        $search    = $request->get('search', '');
        $periodeId = $request->get('periode_id', '');
        $status    = $request->get('status', '');
        $perPage   = (int) $request->get('per_page', 10);

        $jurnal       = $this->service->getList($search, $periodeId, $status, $perPage);
        $periodeList  = $this->service->getPeriodeList();
        $periodeAktif = $this->service->getPeriodeAktif();

        $statusTahap  = $periodeAktif ? $this->service->getStatusTahap($periodeAktif) : [];
        $tahapSelesai = $periodeAktif ? $this->service->getTahapSelesai($periodeAktif) : 0;

        return view('pages.jurnal-penutup.index', compact(
            'jurnal', 'periodeList', 'periodeAktif',
            'search', 'periodeId', 'status', 'perPage',
            'statusTahap', 'tahapSelesai'
        ));
    }

    public function create()
    {
        $periodeAktif = $this->service->getPeriodeAktif();
        $periodeList  = $this->service->getPeriodeList();

        $ringkasan   = null;
        $statusTahap = null;

        if ($periodeAktif) {
            $ringkasan   = $this->service->getRingkasanPeriode($periodeAktif);
            $statusTahap = $this->service->getStatusTahap($periodeAktif);
        }

        return view('pages.jurnal-penutup.create', compact(
            'periodeAktif', 'periodeList', 'ringkasan', 'statusTahap'
        ));
    }

    public function store(Request $request)
    {
        dd($request->all());
        $request->validate([
            'periode_id'  => 'required|exists:periode,id',
            'tanggal'     => 'required|date',
            'submit_type' => 'required|in:draft,posting',
        ]);

        $periode = Periode::findOrFail($request->periode_id);
        $status  = $request->submit_type === 'posting' ? 'POSTED' : 'DRAFT';

        $ringkasan = $this->service->getRingkasanPeriode($periode);

        $semua = [
            'TUTUP_PENDAPATAN' => $this->service->generateTutupPendapatan($ringkasan),
            'TUTUP_BEBAN'      => $this->service->generateTutupBeban($ringkasan),
        ];

        $this->service->storeAllTahapIfNotExists($periode, $semua, $request->tanggal, $status);

        $msg = $status === 'POSTED'
            ? 'Semua jurnal penutup berhasil diposting ke buku besar.'
            : 'Semua jurnal penutup berhasil disimpan sebagai draft.';

        return redirect()->route('dashboard.jurnal-penutup.index')
            ->with('success', $msg);
    }

    public function konfirmasiTahap(Request $request)
    {
        $request->validate([
            'periode_id'     => 'required|exists:periode,id',
            'tipe_penutupan' => 'required|in:TUTUP_PENDAPATAN,TUTUP_BEBAN',
            'tanggal'        => 'required|date',
        ]);

        $periode   = Periode::findOrFail($request->periode_id);
        $ringkasan = $this->service->getRingkasanPeriode($periode);
        $tipe      = $request->tipe_penutupan;

        $detail = match ($tipe) {
            'TUTUP_PENDAPATAN' => $this->service->generateTutupPendapatan($ringkasan),
            'TUTUP_BEBAN'      => $this->service->generateTutupBeban($ringkasan),
        };

        $jurnal = $this->service->storeTahap($periode, $tipe, $detail, $request->tanggal, 'DRAFT');

        return response()->json([
            'success' => true,
            'jurnal'  => [
                'id'     => $jurnal->id,
                'tipe'   => $tipe,
                'detail' => $detail,
            ],
        ]);
    }

    public function show(Jurnal $jurnal)
    {
        $jurnal = $this->service->getById($jurnal);

        $nomorJurnal = 'JPT-'
            . ($jurnal->periode->tanggal_awal->format('Y') ?? '0000') . '-'
            . ($jurnal->periode->tanggal_awal->format('m') ?? '00') . '-'
            . str_pad($jurnal->id, 3, '0', STR_PAD_LEFT);

        return response()->json([
            'jurnal' => [
                'id'              => $jurnal->id,
                'nomor_jurnal'    => $nomorJurnal,
                'tanggal'         => $jurnal->tanggal?->format('j M Y'),
                'keterangan'      => $jurnal->keterangan,
                'status'          => $jurnal->status,
                'tipe_penutupan'  => $jurnal->tipe_penutupan,
                'label_penutupan' => JurnalPenutupService::TIPE_LABELS[$jurnal->tipe_penutupan] ?? $jurnal->tipe_penutupan,
                'periode'         => [
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
            ],
        ]);
    }

    public function postSemua(Request $request)
    {
        $request->validate(['periode_id' => 'required|exists:periode,id']);
        $periode = Periode::findOrFail($request->periode_id);
        $result  = $this->service->postSemua($periode);

        if ($result !== true) {
            return redirect()->back()->with('error', $result);
        }

        return redirect()->route('dashboard.jurnal-penutup.index')
            ->with('success', 'Semua jurnal penutup berhasil diposting.');
    }

    public function destroy(Jurnal $jurnal)
    {
        $result = $this->service->delete($jurnal);

        if ($result !== true) {
            return redirect()->back()->with('error', $result);
        }

        return redirect()->route('dashboard.jurnal-penutup.index')
            ->with('success', 'Jurnal penutup berhasil dihapus.');
    }
}