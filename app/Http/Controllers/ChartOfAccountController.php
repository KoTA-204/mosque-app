<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKategoriRequest;
use App\Http\Requests\UpdateKategoriRequest;
use App\Http\Requests\StoreSubKategoriRequest;
use App\Http\Requests\UpdateSubKategoriRequest;
use App\Http\Requests\StoreAkunRequest;
use App\Http\Requests\UpdateAkunRequest;
use App\Models\Akun;
use App\Models\KategoriAkun;
use Illuminate\Http\Request;

class ChartOfAccountController extends Controller
{
    public function index(Request $request)
    {
        $totalKategori    = KategoriAkun::count();
        $totalSubKategori = Akun::whereNull('parent_id')->count();
        $totalAkun        = Akun::whereNotNull('parent_id')
            ->whereHas('parent', fn($q) => $q->whereNull('parent_id'))
            ->count();

        $kategoriQuery = KategoriAkun::with([
            'akunKeuangan' => fn($q) => $q->whereNull('parent_id')
                ->with(['children' => fn($q2) => $q2->with('children')]),
        ])->orderBy('kode_kategori');

        $allKategori = KategoriAkun::orderBy('kode_kategori')->get();

        if ($request->filled('kategori')) {
            $kategoriQuery->where('id', $request->kategori);
            $perPage    = 1;
            $kategori   = $kategoriQuery->paginate($perPage)->withQueryString();
            $isFiltered = true;
        } else {
            $perPage    = null;
            $kategori   = $kategoriQuery->get();
            $isFiltered = false;
        }

        $subKategoriList = Akun::whereNull('parent_id')
            ->with('kategoriAkun')
            ->orderBy('kode_akun')
            ->get();

        $akunList = Akun::whereNotNull('parent_id')
            ->whereHas('parent', fn($q) => $q->whereNull('parent_id'))
            ->with(['parent', 'kategoriAkun'])
            ->orderBy('kode_akun')
            ->get();

        return view('pages.coa.index', compact(
            'totalKategori',
            'totalSubKategori',
            'totalAkun',
            'kategori',
            'allKategori',
            'perPage',
            'isFiltered',
            'subKategoriList',
            'akunList',
        ));
    }

    // Kategori

    public function createKategori()
    {
        return view('pages.coa.create-kategori');
    }

    public function storeKategori(StoreKategoriRequest $request)
    {
        KategoriAkun::create($request->validated());

        return redirect()
            ->route('dashboard.coa.index')
            ->with('success', 'Kategori akun berhasil ditambahkan.');
    }

    public function editKategori(KategoriAkun $kategori)
    {
        return view('pages.coa.edit-kategori', compact('kategori'));
    }

    public function updateKategori(UpdateKategoriRequest $request, KategoriAkun $kategori)
    {
        $kategori->update($request->validated());

        return redirect()
            ->route('dashboard.coa.index')
            ->with('success', 'Kategori akun berhasil diperbarui.');
    }

    public function destroyKategori(KategoriAkun $kategori)
    {
        if ($kategori->akunKeuangan()->exists()) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena masih memiliki akun.');
        }

        $kategori->delete();

        return back()->with('success', 'Kategori akun berhasil dihapus.');
    }

    // Sub Kategori

    public function createSubKategori()
    {
        $kategoriList = KategoriAkun::orderBy('kode_kategori')->get();

        return view('pages.coa.create-subkategori', compact('kategoriList'));
    }

    public function storeSubKategori(StoreSubKategoriRequest $request)
    {
        Akun::create([
            ...$request->validated(),
            'parent_id'    => null,
            'saldo_normal' => null,
        ]);

        return redirect()
            ->route('dashboard.coa.index')
            ->with('success', 'Sub kategori akun berhasil ditambahkan.');
    }

    public function editSubKategori(Akun $subKategori)
    {
        $kategoriList = KategoriAkun::orderBy('kode_kategori')->get();

        return view('pages.coa.edit-subkategori', compact('subKategori', 'kategoriList'));
    }

    public function updateSubKategori(UpdateSubKategoriRequest $request, Akun $subKategori)
    {
        $subKategori->update([
            ...$request->validated(),
            'saldo_normal' => null,
        ]);

        return redirect()
            ->route('dashboard.coa.index')
            ->with('success', 'Sub kategori akun berhasil diperbarui.');
    }

    public function destroySubKategori(Akun $subKategori)
    {
        if ($subKategori->children()->exists()) {
            return back()->with('error', 'Sub kategori tidak dapat dihapus karena masih memiliki akun.');
        }

        $subKategori->delete();

        return back()->with('success', 'Sub kategori berhasil dihapus.');
    }

    // Akun

    public function createAkun()
    {
        $subKategoriList = Akun::whereNull('parent_id')
            ->with('kategoriAkun')
            ->orderBy('kode_akun')
            ->get();

        return view('pages.coa.create-akun', compact('subKategoriList'));
    }

    public function storeAkun(StoreAkunRequest $request)
    {
        $subKategori = Akun::findOrFail($request->parent_id);

        Akun::create([
            ...$request->validated(),
            'kategori_akun_id' => $subKategori->kategori_akun_id,
        ]);

        return redirect()
            ->route('dashboard.coa.index')
            ->with('success', 'Akun berhasil ditambahkan.');
    }

    public function editAkun(Akun $akun)
    {
        $subKategoriList = Akun::whereNull('parent_id')
            ->with('kategoriAkun')
            ->orderBy('kode_akun')
            ->get();

        return view('pages.coa.edit-akun', compact('akun', 'subKategoriList'));
    }

    public function updateAkun(UpdateAkunRequest $request, Akun $akun)
    {
        $subKategori = Akun::findOrFail($request->parent_id);

        $akun->update([
            ...$request->validated(),
            'kategori_akun_id' => $subKategori->kategori_akun_id,
        ]);

        return redirect()
            ->route('dashboard.coa.index')
            ->with('success', 'Akun berhasil diperbarui.');
    }

    public function destroyAkun(Akun $akun)
    {
        $akun->delete();

        return back()->with('success', 'Akun berhasil dihapus.');
    }
}