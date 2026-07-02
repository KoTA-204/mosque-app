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
    public function index(Request $request)
    {
        // mode statistik saja
        if ($request->boolean('stats_only')) {
            return response()->json(['stats' => $this->buildStats()]);
        }

        $perPage = (int) $request->get('per_page', 10);
        $asets   = Aset::filter($request->all())
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $stats = $this->buildStats();

        // kirim potongan tabel untuk request ajax
        if ($request->ajax()) {
            return response()->json([
                'html' => view('pages.aset.table', compact('asets'))->render(),
            ]);
        }

        return view('pages.aset.index', compact('asets', 'stats'));
    }

    // form create
    public function create()
    {
        if (request()->ajax()) {
            return response()->json([
                'html' => view('pages.aset.create')->render(),
            ]);
        }
        return redirect()->route('dashboard.aset.index');
    }

    // simpan aset baru
    public function store(StoreAsetRequest $request)
    {
        $this->asetService->create(
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
    public function show(Aset $aset)
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
    public function edit(Aset $aset)
    {
        if (request()->ajax()) {
            return response()->json([
                'html' => view('pages.aset.edit', compact('aset'))->render(),
            ]);
        }
        return redirect()->route('dashboard.aset.index');
    }

    // update aset
    public function update(UpdateAsetRequest $request, Aset $aset)
    {
        $this->asetService->update(
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
    public function toggleStatus(Aset $aset)
    {
        $newStatus = $this->asetService->toggleStatus($aset);

        if (request()->ajax()) {
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
    public function destroy(Aset $aset)
    {
        try {
            $this->asetService->delete($aset);
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
    private function buildStats(): array
    {
        return [
            'total'       => Aset::count(),
            'aktif'       => Aset::where('status_aset', 'AKTIF')->count(),
            'tidak_aktif' => Aset::where('status_aset', 'TIDAK AKTIF')->count(),
        ];
    }
}