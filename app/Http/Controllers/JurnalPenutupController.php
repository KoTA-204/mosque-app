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

    // ─────────────────────────────────────────────────────────────
    // Index
    // ─────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $search    = $request->get('search', '');
        $periodeId = $request->get('periode_id', '');
        $status    = $request->get('status', '');
        $perPage   = (int) $request->get('per_page', 10);

        $jurnal       = $this->service->getList($search, $periodeId, $status, $perPage);
        $periodeList  = $this->service->getPeriodeList();
        $periodeAktif = $this->service->getPeriodeAktif();

        $statusTahap  = $periodeAktif
            ? $this->service->getStatusTahap($periodeAktif)
            : [];

        $tahapSelesai = $periodeAktif
            ? $this->service->getTahapSelesai($periodeAktif)
            : 0;

        return view('pages.jurnal-penutup.index', compact(
            'jurnal',
            'periodeList',
            'periodeAktif',
            'search',
            'periodeId',
            'status',
            'perPage',
            'statusTahap',
            'tahapSelesai'
        ));
    }

    // ─────────────────────────────────────────────────────────────
    // Create
    // ─────────────────────────────────────────────────────────────

    public function create()
    {
        $periodeAktif = $this->service->getPeriodeAktif();
        $periodeList  = $this->service->getPeriodeList();

        $ringkasan     = null;
        $statusTahap   = null;
        $existingDraft = null;

        if ($periodeAktif) {

            if ($this->service->isPeriodeClosed($periodeAktif)) {
                return redirect()
                    ->route('dashboard.jurnal-penutup.index')
                    ->with('error', 'Periode aktif sudah ditutup.');
            }

            $ringkasan = $this->service->getRingkasanPeriode($periodeAktif);

            $statusTahap = $this->service->getStatusTahap($periodeAktif);

            $existingDraft = $this->service->getExistingDraft($periodeAktif);
        }

        return view('pages.jurnal-penutup.create', compact(
            'periodeAktif',
            'periodeList',
            'ringkasan',
            'statusTahap',
            'existingDraft'
        ));
    }

    // ─────────────────────────────────────────────────────────────
    // Store
    // ─────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'periode_id' => 'required|exists:periode,id',
            'tanggal'    => 'required|date',
            'aksi'       => 'required|in:draft,posting',
        ]);

        $periode = Periode::findOrFail($request->periode_id);

        if ($this->service->isPeriodeClosed($periode)) {
            return redirect()->back()->with('error', 'Periode sudah ditutup.');
        }

        // Guard Opsi B — cek sebelum generate entri
        if ($err = $this->service->guardPeriodeSiapTutup($periode)) {
            return redirect()->back()->with('error', $err);
        }

        $aksi = $request->aksi;

        try {
            $ringkasan = $this->service->getRingkasanPeriode($periode);

            $semua = [
                'TUTUP_PENDAPATAN' => $this->service->generateTutupPendapatan($ringkasan),
                'TUTUP_BEBAN'      => $this->service->generateTutupBeban($ringkasan),
            ];

            // ── Posting langsung ──────────────────────────────
            if ($aksi === 'posting') {
                $result = $this->service->storeAndPost($periode, $semua, $request->tanggal);

                if ($result !== true) {
                    return redirect()->back()->with('error', $result);
                }

                return redirect()
                    ->route('dashboard.jurnal-penutup.index')
                    ->with('success', 'Jurnal penutup berhasil diposting dan periode berhasil ditutup.');
            }

            // ── Simpan draft ──────────────────────────────────
            $this->service->storeAllTahap($periode, $semua, $request->tanggal, 'DRAFT');

            return redirect()
                ->route('dashboard.jurnal-penutup.index')
                ->with('success', 'Jurnal penutup berhasil disimpan sebagai draft.');

        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Posting Existing Draft
    // ─────────────────────────────────────────────────────────────

    public function postDraft(Request $request)
    {
        $request->validate([
            'periode_id' => 'required|exists:periode,id',
        ]);

        $periode = Periode::findOrFail($request->periode_id);

        if ($this->service->isPeriodeClosed($periode)) {
            return redirect()->back()->with('error', 'Periode sudah ditutup.');
        }

        $result = $this->service->postExistingDraft($periode);

        if ($result !== true) {
            return redirect()->back()->with('error', $result);
        }

        return redirect()
            ->route('dashboard.jurnal-penutup.index')
            ->with('success', 'Jurnal penutup berhasil diposting dan periode berhasil ditutup.');
    }

    // ─────────────────────────────────────────────────────────────
    // Show
    // ─────────────────────────────────────────────────────────────

    public function show(Jurnal $jurnal)
    {
        $jurnal = $this->service->getById($jurnal);

        $nomorJurnal = 'JPT-'
            . ($jurnal->periode->tanggal_awal->format('Y') ?? '0000')
            . '-'
            . ($jurnal->periode->tanggal_awal->format('m') ?? '00')
            . '-'
            . str_pad($jurnal->id, 3, '0', STR_PAD_LEFT);

        return response()->json([
            'jurnal' => [
                'id'              => $jurnal->id,
                'nomor_jurnal'    => $nomorJurnal,
                'tanggal'         => $jurnal->tanggal?->format('j M Y'),
                'keterangan'      => $jurnal->keterangan,
                'status'          => $jurnal->status,
                'tipe_penutupan'  => $jurnal->tipe_penutupan,
                'label_penutupan' => JurnalPenutupService::TIPE_LABELS[$jurnal->tipe_penutupan]
                    ?? $jurnal->tipe_penutupan,
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
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // Delete
    // ─────────────────────────────────────────────────────────────

    public function destroy(Jurnal $jurnal)
    {
        if (!$jurnal->periode?->status) {
            return redirect()->back()
                ->with('error', 'Jurnal pada periode yang sudah ditutup tidak dapat dihapus.');
        }

        $result = $this->service->delete($jurnal);

        if ($result !== true) {
            return redirect()->back()->with('error', $result);
        }

        return redirect()
            ->route('dashboard.jurnal-penutup.index')
            ->with('success', 'Jurnal penutup berhasil dihapus.');
    }
}