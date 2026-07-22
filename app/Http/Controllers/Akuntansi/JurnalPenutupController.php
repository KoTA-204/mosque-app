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

    public function tambahJurnalPenutup(Request $request)
    {
        $periodeAktif = $this->service->getPeriodeAktif();
        // Hanya periode yang masih terbuka yang boleh ditutup.
        $periodeList  = $this->service->getPeriodeOpenList();

        // Periode yang akan ditutup: ikuti pilihan dropdown (periode_id) bila valid
        // & masih terbuka; jika tidak, gunakan periode aktif sebagai default.
        $periodeDipilih = $periodeAktif;
        if ($request->filled('periode_id')) {
            $dipilih = Periode::find($request->periode_id);
            if ($dipilih && ! $this->periode->isPeriodeClosed($dipilih)) {
                $periodeDipilih = $dipilih;
            }
        }

        $ringkasan     = null;
        $statusTahap   = null;
        $existingDraft = null;

        if ($periodeDipilih) {
            if ($this->periode->isPeriodeClosed($periodeDipilih)) {
                return redirect()->route('dashboard.jurnal-penutup.index')
                    ->with('error', 'Periode yang dipilih sudah ditutup.');
            }
            $ringkasan     = $this->service->getRingkasanPeriode($periodeDipilih);
            $statusTahap   = $this->service->getStatusTahap($periodeDipilih);
            $existingDraft = $this->service->getExistingDraft($periodeDipilih);
        }

        // Periode hanya boleh diposting/ditutup setelah benar-benar berakhir.
        $periodeSudahBerakhir = $periodeDipilih
            ? now()->startOfDay()->gte($periodeDipilih->tanggal_akhir->copy()->startOfDay())
            : false;

        return view('pages.akuntansi.jurnal-penutup.create', compact(
            'periodeAktif', 'periodeDipilih', 'periodeList', 'ringkasan', 'statusTahap', 'existingDraft', 'periodeSudahBerakhir'
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
                $periodeBaru = Periode::where('status', true)
                    ->orderBy('tanggal_awal', 'desc')
                    ->first();
                $pesan = "Jurnal penutup berhasil diposting dan periode {$periode->nama_periode} berhasil ditutup.";
                if ($periodeBaru) {
                    $pesan .= " Periode {$periodeBaru->nama_periode} kini aktif dan siap mencatat transaksi.";
                }
                return redirect()->route('dashboard.jurnal-penutup.index')
                    ->with('success', $pesan);
            }

            $this->service->catatSemuaTahapPenutupan($periode, $semua, $request->tanggal, 'DRAFT');
            return redirect()->route('dashboard.jurnal-penutup.index')
                ->with('success', 'Jurnal penutup berhasil disimpan sebagai draft.');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()
                ->with('error', 'Gagal memproses penutupan: ' . $e->getMessage());
        }
    }

    public function postDraft(PostDraftPenutupRequest $request)
    {
        $periode = Periode::findOrFail($request->periode_id);

        if ($this->periode->isPeriodeClosed($periode)) {
            return redirect()->back()->with('error', 'Periode sudah ditutup.');
        }

        try {
            $result = $this->service->postingDraftPenutupan($periode);
            if ($result !== true) {
                return redirect()->back()->with('error', $result);
            }
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()
                ->with('error', 'Gagal memposting draft penutup: ' . $e->getMessage());
        }

        $periodeBaru = Periode::where('status', true)
            ->orderBy('tanggal_awal', 'desc')
            ->first();
        $pesan = "Jurnal penutup berhasil diposting dan periode {$periode->nama_periode} berhasil ditutup.";
        if ($periodeBaru) {
            $pesan .= " Periode {$periodeBaru->nama_periode} kini aktif dan siap mencatat transaksi.";
        }
        return redirect()->route('dashboard.jurnal-penutup.index')
            ->with('success', $pesan);
    }

    public function bukaPeriodeBerikutnya()
    {
        try {
            $baru = $this->periode->bukaPeriodeBerikutnya();
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->route('dashboard.jurnal-penutup.index')
            ->with('success', "Periode {$baru->nama_periode} berhasil dibuka dan siap mencatat transaksi.");
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
