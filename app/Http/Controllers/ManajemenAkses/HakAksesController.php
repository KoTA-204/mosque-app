<?php

namespace App\Http\Controllers\ManajemenAkses;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HakAkses;
use App\Http\Requests\StoreHakAksesRequest;
use App\Http\Requests\UpdateHakAksesRequest;
use App\Services\ManajemenAkses\HakAksesService;
use Illuminate\Support\Facades\Log;

class HakAksesController extends Controller
{
    public function __construct(
        protected HakAksesService $hakAksesService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function tampilkanDaftarHakAkses(Request $request)
    {
        $search  = $request->get('search', '');
        $module  = $request->get('module', '');
        $action  = $request->get('action', '');
        $perPage = (int) $request->get('per_page', 10);

        $hak_akses = $this->hakAksesService->getDataHakAkses($search, $module, $action, $perPage);
        $modules     = $this->hakAksesService->getDaftarModul();

        return view('pages.manajemen-akses.hak-akses.index', compact('hak_akses', 'search', 'module', 'action', 'perPage', 'modules'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function tampilkanFormTambahHakAkses()
    {
        return view('pages.manajemen-akses.hak-akses.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function simpanHakAksesBaru(StoreHakAksesRequest $request)
    {
        try {
            $this->hakAksesService->buatHakAkses($request->validated());

            return redirect()->route('dashboard.hak-akses.index')
                ->with('success', 'HakAkses berhasil dibuat.');
        } catch (\Throwable $e) {
            Log::error('HakAksesController@store: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat membuat hak_akses. Silakan coba lagi.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function tampilkanDetailHakAkses(HakAkses $hak_akses)
    {
        try {
            $hak_akses = $this->hakAksesService->getDetailHakAkses($hak_akses);

            return view('pages.manajemen-akses.hak-akses.show', compact('hak_akses'));
        } catch (\Throwable $e) {
            Log::error('HakAksesController@show: ' . $e->getMessage());

            return redirect()->route('dashboard.hak-akses.index')
                ->with('error', 'HakAkses tidak ditemukan.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function tampilkanFormEditHakAkses(HakAkses $hak_akses)
    {
        return view('pages.manajemen-akses.hak-akses.edit', compact('hak_akses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function perbaruiHakAkses(UpdateHakAksesRequest $request, HakAkses $hak_akses)
    {
        try {
            $this->hakAksesService->perbaruiHakAkses($hak_akses, $request->validated());

            return redirect()->route('dashboard.hak-akses.index')
                ->with('success', 'HakAkses berhasil diperbarui.');
        } catch (\Throwable $e) {
            Log::error('HakAksesController@update: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui hak_akses. Silakan coba lagi.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function hapusHakAkses(HakAkses $hak_akses)
    {
        try {
            $result = $this->hakAksesService->hapusHakAkses($hak_akses);

            if ($result !== true) {
                return redirect()->back()->with('error', $result);
            }

            return redirect()->route('dashboard.hak-akses.index')
                ->with('success', 'HakAkses berhasil dihapus.');
        } catch (\Throwable $e) {
            Log::error('HakAksesController@destroy: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menghapus hak_akses. Silakan coba lagi.');
        }
    }
}