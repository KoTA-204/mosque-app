<?php

namespace App\Http\Controllers\Akuntansi;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostDraftPenutupRequest;
use App\Http\Requests\StoreJurnalPenutupRequest;
use App\Models\Jurnal;
use App\Models\Periode;
use App\Services\Akuntansi\JurnalPenutupService;
use App\Services\Akuntansi\PeriodeService;
use Illuminate\Http\Request;

class JurnalPenutupController extends Controller
{
    public function __construct(
        protected JurnalPenutupService $service,
        protected PeriodeService $periode
    ) {}

    public function tampilkanJurnalPenutup(Request $request)
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

        $periode = $periodeAktif;

        if ($filter['periode_id']) {
            $periode = Periode::find($filter['periode_id']);
        }

        $statusTahap = $periode ? $this->service->getStatusTahap($periode) : [];
        $tahapSelesai = $periode ? $this->service->getTahapSelesai($periode) : 0;

        $search    = $filter['search'];
        $periodeId = $filter['periode_id'];
        $status    = $filter['status'];
        $perPage   = $filter['per_page'];

        return view('pages.akuntansi.jurnal-penutup.index', compact(
            'jurnal', 'periodeList', 'periodeAktif',
            'search', 'periodeId', 'status', 'perPage',
            'statusTahap', 'tahapSelesai'
        ));
    }

    public function tambahJurnalPenutup()
    {
        $periodeAktif = $this->service->getPeriodeAktif();
        $periodeList  = $this->service->getPeriodeList();
        $ringkasan     = null;
        $statusTahap   = null;
        $existingDraft = null;

        if ($periodeAktif) {
            if ($this->periode->isPeriodeClosed($periodeAktif)) {
                return redirect()->route('dashboard.jurnal-penutup.index')
                    ->with('error', 'Periode aktif sudah ditutup.');
            }
            $ringkasan     = $this->service->getRingkasanPeriode($periodeAktif);
            $statusTahap   = $this->service->getStatusTahap($periodeAktif);
            $existingDraft = $this->service->getExistingDraft($periodeAktif);
        }

        return view('pages.akuntansi.jurnal-penutup.create', compact(
            'periodeAktif', 'periodeList', 'ringkasan', 'statusTahap', 'existingDraft'
        ));
    }

    public function simpanJurnalPenutup(StoreJurnalPenutupRequest $request)
    {
        $periode = Periode::findOrFail($request->periode_id);

        if ($this->periode->isPeriodeClosed($periode)) {
            return redirect()->back()->with('error', 'Periode sudah ditutup.');
        }

        try {
            $ringkasan = $this->service->getRingkasanPeriode($periode);

            $semua = array_filter([
                'TUTUP_PENDAPATAN'     => $this->service->susunJurnalTutupPendapatan($ringkasan),
                'TUTUP_BEBAN'          => $this->service->susunJurnalTutupBeban($ringkasan),
                'PELEPASAN_PEMBATASAN' => $this->service->susunJurnalPelepasanPembatasan($ringkasan),
            ], fn($detail) => !empty($detail));

            if ($request->aksi === 'posting') {
                $result = $this->service->catatDanPostingPenutupan($periode, $semua, $request->tanggal);
                if ($result !== true) {
                    return redirect()->back()->with('error', $result);
                }
                return redirect()->route('dashboard.jurnal-penutup.index')
                    ->with('success', 'Jurnal penutup berhasil diposting dan periode berhasil ditutup.');
            }

            $this->service->catatSemuaTahapPenutupan($periode, $semua, $request->tanggal, 'DRAFT');
            return redirect()->route('dashboard.jurnal-penutup.index')
                ->with('success', 'Jurnal penutup berhasil disimpan sebagai draft.');
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function postDraft(PostDraftPenutupRequest $request)
    {
        $periode = Periode::findOrFail($request->periode_id);

        if ($this->periode->isPeriodeClosed($periode)) {
            return redirect()->back()->with('error', 'Periode sudah ditutup.');
        }

        $result = $this->service->postingDraftPenutupan($periode);
        if ($result !== true) {
            return redirect()->back()->with('error', $result);
        }

        return redirect()->route('dashboard.jurnal-penutup.index')
            ->with('success', 'Jurnal penutup berhasil diposting dan periode berhasil ditutup.');
    }

    public function tampilkanDetailJurnalPenutup(Jurnal $jurnal)
    {
        $jurnal = $this->service->getById($jurnal);

        return response()->json([
            'jurnal' => [
                'id'              => $jurnal->id,
                'nomor_jurnal'    => $jurnal->kode_jurnal,
                'tanggal'         => $jurnal->tanggal?->format('j M Y'),
                'keterangan'      => $jurnal->keterangan,
                'status'          => $jurnal->status,
                'tipe_penutupan'  => $jurnal->tipe_penutupan,
                'label_penutupan' => JurnalPenutupService::TIPE_LABELS[$jurnal->tipe_penutupan]
                    ?? $jurnal->tipe_penutupan,
                'periode' => [
                    'nama_periode' => $jurnal->periode->nama_periode ?? '-',
                ],
                'detail_jurnal' => $jurnal->detailJurnal->map(fn($d) => [
                    'tipe'    => $d->tipe,
                    'nominal' => (float) $d->nominal,
                    'akun'    => [
                        'kode_akun' => $d->akun->kode_akun ?? '',
                        'nama_akun' => $d->akun->nama_akun ?? '-',
                    ],
                ]),
            ],
        ]);
    }

    public function hapusJurnalPenutup(Jurnal $jurnal)
    {
        if (!$jurnal->periode?->status) {
            return redirect()->back()
                ->with('error', 'Jurnal pada periode yang sudah ditutup tidak dapat dihapus.');
        }

        $result = $this->service->hapusJurnal($jurnal);
        if ($result !== true) {
            return redirect()->back()->with('error', $result);
        }

        return redirect()->route('dashboard.jurnal-penutup.index')
            ->with('success', 'Jurnal penutup berhasil dihapus.');
    }
}
