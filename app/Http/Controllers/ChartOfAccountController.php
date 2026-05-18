<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Akun;
use App\Models\KategoriAkun;
use Illuminate\Http\Request;

class ChartOfAccountController extends Controller
{
    // ──────────────────────────────────────────────
    // INDEX — Halaman utama Chart of Account
    // ──────────────────────────────────────────────
    public function index(Request $request)
    {
        // Statistik card
        $totalKategori    = KategoriAkun::count();
        $totalSubKategori = Akun::whereNotNull('parent_id')->whereNull('kategori_akun_id')->count();
        // Sub kategori = akun level-2 (punya parent, parent-nya adalah root)
        $totalSubKategori = Akun::whereNotNull('parent_id')
            ->whereHas('parent', fn($q) => $q->whereNull('parent_id'))
            ->count();
        $totalAkun        = Akun::whereNotNull('parent_id')
            ->whereHas('parent', fn($q) => $q->whereNotNull('parent_id'))
            ->count();

        // Filter kategori yang ditampilkan
        $kategoriQuery = KategoriAkun::with([
            'akunKeuangan' => fn($q) => $q->whereNull('parent_id')
                ->with(['children' => fn($q2) => $q2->with('children')]),
        ])->orderBy('kode_kategori');

        // Filter per kategori (opsional)
        if ($request->filled('kategori')) {
            $kategoriQuery->where('id', $request->kategori);
        }

        $allKategori = KategoriAkun::orderBy('kode_kategori')->get();

        // Pagination per kategori yang ditampilkan
        $perPage   = (int) $request->get('per_page', 1);
        $kategori  = $kategoriQuery->paginate($perPage)->withQueryString();

        return view('dashboard.chart-of-account.index', compact(
            'totalKategori',
            'totalSubKategori',
            'totalAkun',
            'kategori',
            'allKategori',
            'perPage',
        ));
    }

    // ──────────────────────────────────────────────
    // KATEGORI — Create & Store
    // ──────────────────────────────────────────────
    public function createKategori()
    {
        return view('dashboard.chart-of-account.create-kategori');
    }

    public function storeKategori(Request $request)
    {
        $request->validate([
            'kode_kategori' => 'required|string|max:10|unique:kategori_akun,kode_kategori',
            'nama_kategori' => 'required|string|max:100',
        ], [
            'kode_kategori.required' => 'Kode kategori wajib diisi.',
            'kode_kategori.unique'   => 'Kode kategori sudah digunakan.',
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
        ]);

        KategoriAkun::create($request->only('kode_kategori', 'nama_kategori'));

        return redirect()
            ->route('dashboard.coa.index')
            ->with('success', 'Kategori akun berhasil ditambahkan.');
    }

    public function editKategori(KategoriAkun $kategori)
    {
        return view('dashboard.chart-of-account.edit-kategori', compact('kategori'));
    }

    public function updateKategori(Request $request, KategoriAkun $kategori)
    {
        $request->validate([
            'kode_kategori' => 'required|string|max:10|unique:kategori_akun,kode_kategori,' . $kategori->id,
            'nama_kategori' => 'required|string|max:100',
        ]);

        $kategori->update($request->only('kode_kategori', 'nama_kategori'));

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

    // ──────────────────────────────────────────────
    // SUB KATEGORI — Create & Store
    // Sub kategori = Akun level-1 (parent_id = null, punya kategori_akun_id)
    // ──────────────────────────────────────────────
    public function createSubKategori()
    {
        $kategoriList = KategoriAkun::orderBy('kode_kategori')->get();
        return view('dashboard.chart-of-account.create-sub-kategori', compact('kategoriList'));
    }

    public function storeSubKategori(Request $request)
    {
        $request->validate([
            'kategori_akun_id' => 'required|exists:kategori_akun,id',
            'kode_akun'        => 'required|string|max:20|unique:akun,kode_akun',
            'nama_akun'        => 'required|string|max:150',
            'deskripsi'        => 'nullable|string',
        ], [
            'kategori_akun_id.required' => 'Kategori wajib dipilih.',
            'kode_akun.required'        => 'Kode sub kategori wajib diisi.',
            'kode_akun.unique'          => 'Kode sudah digunakan.',
            'nama_akun.required'        => 'Nama sub kategori wajib diisi.',
        ]);

        Akun::create([
            'kategori_akun_id' => $request->kategori_akun_id,
            'parent_id'        => null,
            'kode_akun'        => $request->kode_akun,
            'nama_akun'        => $request->nama_akun,
            'saldo_normal'     => $request->saldo_normal ?? 'debit',
        ]);

        return redirect()
            ->route('dashboard.coa.index')
            ->with('success', 'Sub kategori akun berhasil ditambahkan.');
    }

    public function editSubKategori(Akun $subKategori)
    {
        $kategoriList = KategoriAkun::orderBy('kode_kategori')->get();
        return view('dashboard.chart-of-account.edit-sub-kategori', compact('subKategori', 'kategoriList'));
    }

    public function updateSubKategori(Request $request, Akun $subKategori)
    {
        $request->validate([
            'kategori_akun_id' => 'required|exists:kategori_akun,id',
            'kode_akun'        => 'required|string|max:20|unique:akun,kode_akun,' . $subKategori->id,
            'nama_akun'        => 'required|string|max:150',
        ]);

        $subKategori->update($request->only('kategori_akun_id', 'kode_akun', 'nama_akun', 'saldo_normal'));

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

    // ──────────────────────────────────────────────
    // AKUN — Create & Store
    // Akun = level-2 (parent_id menunjuk ke sub kategori)
    // ──────────────────────────────────────────────
    public function createAkun()
    {
        $subKategoriList = Akun::whereNull('parent_id')
            ->with('kategoriAkun')
            ->orderBy('kode_akun')
            ->get();

        return view('dashboard.chart-of-account.create-akun', compact('subKategoriList'));
    }

    public function storeAkun(Request $request)
    {
        $request->validate([
            'parent_id'    => 'required|exists:akun,id',
            'kode_akun'    => 'required|string|max:20|unique:akun,kode_akun',
            'nama_akun'    => 'required|string|max:150',
            'saldo_normal' => 'required|in:debit,kredit',
            'deskripsi'    => 'nullable|string',
        ], [
            'parent_id.required'    => 'Sub kategori wajib dipilih.',
            'kode_akun.required'    => 'Nomor akun wajib diisi.',
            'kode_akun.unique'      => 'Nomor akun sudah digunakan.',
            'nama_akun.required'    => 'Nama akun wajib diisi.',
            'saldo_normal.required' => 'Saldo normal wajib dipilih.',
        ]);

        // Ambil kategori_akun_id dari parent (sub kategori)
        $subKategori = Akun::findOrFail($request->parent_id);

        Akun::create([
            'kategori_akun_id' => $subKategori->kategori_akun_id,
            'parent_id'        => $request->parent_id,
            'kode_akun'        => $request->kode_akun,
            'nama_akun'        => $request->nama_akun,
            'saldo_normal'     => $request->saldo_normal,
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

        return view('dashboard.chart-of-account.edit-akun', compact('akun', 'subKategoriList'));
    }

    public function updateAkun(Request $request, Akun $akun)
    {
        $request->validate([
            'parent_id'    => 'required|exists:akun,id',
            'kode_akun'    => 'required|string|max:20|unique:akun,kode_akun,' . $akun->id,
            'nama_akun'    => 'required|string|max:150',
            'saldo_normal' => 'required|in:debit,kredit',
        ]);

        $subKategori = Akun::findOrFail($request->parent_id);

        $akun->update([
            'kategori_akun_id' => $subKategori->kategori_akun_id,
            'parent_id'        => $request->parent_id,
            'kode_akun'        => $request->kode_akun,
            'nama_akun'        => $request->nama_akun,
            'saldo_normal'     => $request->saldo_normal,
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