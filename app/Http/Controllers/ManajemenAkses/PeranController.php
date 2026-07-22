<?php

namespace App\Http\Controllers\ManajemenAkses;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Peran;
use App\Models\Menu;
use App\Models\HakAkses;
use App\Http\Requests\StorePeranRequest;
use App\Http\Requests\UpdatePeranRequest;
use App\Services\ManajemenAkses\PeranService;

class PeranController extends Controller
{
    public function __construct(
        protected PeranService $peranService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function tampilkanDaftarPeran(Request $request)
    {
        $search = $request->get('search', '');
        $peran  = $this->peranService->getDataPeran($search);

        return view('pages.manajemen-akses.peran.index', compact('peran', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function tampilkanFormTambahPeran()
    {
        $menus       = $this->getMenuBesertaHakAkses();
        $hak_akses = HakAkses::where('aktif', true)
            ->get()
            ->groupBy('modul');

        $actions = ['view', 'create', 'update', 'delete'];

        return view('pages.manajemen-akses.peran.create', compact(
            'menus',
            'hak_akses',
            'actions'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function simpanPeranBaru(StorePeranRequest $request)
    {
        try {
            $this->peranService->buatPeran($request->validated());

            return redirect()->route('dashboard.peran.index')
                ->with('success', 'Peran berhasil dibuat');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal membuat peran. Silakan coba lagi.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function tampilkanDetailPeran(Peran $peran)
    {
        $peran = $this->peranService->getDetailPeran($peran);

        return view('pages.manajemen-akses.peran.show', compact('peran'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function tampilkanFormEditPeran(Peran $peran)
    {
        $peran        = $this->peranService->getDetailPeran($peran);
        $menus       = $this->getMenuBesertaHakAkses();
        $hak_akses = HakAkses::where('aktif', true)
            ->get()
            ->groupBy('modul');

        $actions = ['view', 'create', 'update', 'delete'];

        $assignedIds = $peran->hak_akses
            ->pluck('id')
            ->toArray();

        return view('pages.manajemen-akses.peran.edit', compact(
            'peran',
            'menus',
            'hak_akses',
            'actions',
            'assignedIds'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function perbaruiPeran(UpdatePeranRequest $request, Peran $peran)
    {
        try {
            $this->peranService->perbaruiPeran($peran, $request->validated());

            return redirect()->route('dashboard.peran.index')
                ->with('success', 'Peran berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui peran. Silakan coba lagi.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function hapusPeran(Peran $peran)
    {
        try {
            $result = $this->peranService->hapusPeran($peran);

            if ($result !== true) {
                return redirect()->back()->with('error', $result);
            }

            return redirect()->route('dashboard.peran.index')
                ->with('success', 'Peran berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus peran. Silakan coba lagi.');
        }
    }

    private function getMenuBesertaHakAkses()
    {
        return Menu::whereNull('parent_id')
            ->where('is_active', true)
            ->with(['children' => function ($query) {
                $query->where('is_active', true)
                    ->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get();
    }
}