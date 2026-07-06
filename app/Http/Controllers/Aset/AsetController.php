<?php

namespace App\Http\Controllers\Aset;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAsetRequest;
use App\Http\Requests\UpdateAsetRequest;
use App\Models\Aset;
use App\Services\Aset\AsetService;
use Illuminate\Http\Request;

class AsetController extends Controller
{
    // inject service
    public function __construct(private readonly AsetService $asetService)
    {
    }

    // daftar aset + statistik + filter
    public function tampilkanDaftarAset(Request $request)
    {
        // mode statistik saja
        if ($request->boolean('stats_only')) {
            return response()->json(['stats' => $this->hitungStatistikAset()]);
        }

        $perPage = (int) $request->get('per_page', 10);
        $asets   = Aset::saring($request->all())
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $stats = $this->hitungStatistikAset();

        // kirim potongan tabel untuk request ajax
        if ($request->ajax()) {
            return response()->json([
                'html' => view('pages.aset.table', compact('asets'))->render(),
            ]);
        }

        return view('pages.aset.index', compact('asets', 'stats'));
    }

    // form create
    public function tampilkanFormTambahAset()
    {
        if (request()->ajax()) {
            return response()->json([
                'html' => view('pages.aset.create')->render(),
            ]);
        }
        return redirect()->route('dashboard.aset.index');
    }

    // simpan aset baru
    public function simpanAsetBaru(StoreAsetRequest $request)
    {
        $this->asetService->simpanAset(
            $request->validated(),
            $request->file('dokumen_pendukung'),
        );

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Aset berhasil ditambahkan.']);
        }

        return redirect()->route('dashboard.aset.index')
            ->with('success', 'Aset berhasil ditambahkan.');
    }

    // detail aset
    public function tampilkanDetailAset(Aset $aset)
    {
        $aset->load('jurnalPenyesuaian.periode');

        if (request()->ajax()) {
            return response()->json([
                'html' => view('pages.aset.show', compact('aset'))->render(),
            ]);
        }
        return redirect()->route('dashboard.aset.index');
    }

    // form edit
    public function tampilkanFormUbahAset(Aset $aset)
    {
        $keuanganTerkunci = $aset->jurnalPenyesuaian()->exists();

        if (request()->ajax()) {
            return response()->json([
                'html' => view('pages.aset.edit', compact('aset', 'keuanganTerkunci'))->render(),
            ]);
        }
        return redirect()->route('dashboard.aset.index');
    }

    // update aset
    public function perbaruiAset(UpdateAsetRequest $request, Aset $aset)
    {
        $this->asetService->perbaruiAset(
            $aset,
            $request->validated(),
            $request->file('dokumen_pendukung'),
            $request->boolean('disusutkan'),
        );

        if ($request->ajax()) {
            $message = 'Aset berhasil diperbarui.';

            return response()->json([
                'success' => true,
                'message' => $message,
                'alert'   => (string) view('components.jurnal.alert', [
                    'type'    => 'success',
                    'message' => $message,
                ]),
            ]);
        }

        return redirect()->route('dashboard.aset.index')
            ->with('success', 'Aset berhasil diperbarui.');
    }

    // aktif / nonaktifkan aset
    public function ubahStatusAset(Request $request, Aset $aset)
    {
        try {
            $newStatus = $this->asetService->ubahStatusAset(
                $aset,
                $request->input('alasan_nonaktif'),
                $request->input('catatan_nonaktif'),
                $request->input('jenis_pelepasan'),
            );
        } catch (\InvalidArgumentException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'status'  => $newStatus,
                'message' => "Aset berhasil diubah ke {$newStatus}.",
            ]);
        }

        return redirect()->route('dashboard.aset.index')
            ->with('success', "Aset berhasil diubah ke {$newStatus}.");
    }

    // hapus aset
    public function hapusAset(Aset $aset)
    {
        try {
            $this->asetService->hapusAset($aset);
        } catch (\InvalidArgumentException $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Aset berhasil dihapus.']);
        }

        return redirect()->route('dashboard.aset.index')
            ->with('success', 'Aset berhasil dihapus.');
    }

    // hitung statistik kartu
    private function hitungStatistikAset(): array
    {
        return [
            'total'       => Aset::count(),
            'aktif'       => Aset::where('status_aset', 'AKTIF')->count(),
            'tidak_aktif' => Aset::where('status_aset', 'TIDAK AKTIF')->count(),
        ];
    }
}
