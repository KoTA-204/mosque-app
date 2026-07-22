<?php

namespace App\Http\Controllers\DataInduk;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKategoriRequest;
use App\Http\Requests\UpdateKategoriRequest;
use App\Http\Requests\StoreSubKategoriRequest;
use App\Http\Requests\UpdateSubKategoriRequest;
use App\Http\Requests\StoreAkunRequest;
use App\Http\Requests\UpdateAkunRequest;
use App\Models\Akun;
use App\Models\DetailJurnal;
use App\Models\KategoriAkun;
use Illuminate\Http\Request;

class ChartOfAccountController extends Controller
{
    public function tampilkanDaftarCoa(Request $request)
    {
        $totalKategori    = KategoriAkun::count();
        $totalSubKategori = Akun::whereNull('parent_id')->count();
        $totalAkun        = Akun::whereNotNull('parent_id')
            ->whereHas('parent', fn($q) => $q->whereNull('parent_id'))
            ->count();

        $allKategori = KategoriAkun::orderBy('kode_kategori')->get();

        $subKategoriList = Akun::whereNull('parent_id')
            ->when($request->kategori, function ($q) use ($request) {
                $q->where('kategori_akun_id', $request->kategori);
            })
            ->orderBy('kode_akun')
            ->get();

        $kategoriQuery = KategoriAkun::with([
            'akunKeuangan' => function ($q) use ($request) {
                $q->whereNull('parent_id')
                ->when($request->sub_kategori, function ($query) use ($request) {
                    $query->where('id', $request->sub_kategori);
                })
                ->orderBy('kode_akun')
                ->with(['children' => function ($q) {
                    $q->orderBy('kode_akun');
                }]);
            }
        ])->orderBy('kode_kategori');

        // Filter kategori
        if ($request->filled('kategori')) {
            $kategoriQuery->where('id', $request->kategori);
        }

        // Filter sub kategori
        if ($request->filled('sub_kategori')) {
            $kategoriQuery->whereHas('akunKeuangan', function ($q) use ($request) {
                $q->where('id', $request->sub_kategori);
            });
        }

        // Search
        if ($request->filled('search')) {
            $search = strtolower($request->search);

            $kategoriQuery->where(function ($q) use ($search) {

                $q->whereRaw('LOWER(nama_kategori) LIKE ?', ["%{$search}%"])

                ->orWhereHas('akunKeuangan', function ($sub) use ($search) {

                    $sub->whereRaw('LOWER(nama_akun) LIKE ?', ["%{$search}%"])

                        ->orWhereHas('children', function ($akun) use ($search) {

                            $akun->whereRaw('LOWER(nama_akun) LIKE ?', ["%{$search}%"]);

                        });

                });

            });
        }

        $kategori = $kategoriQuery
            ->orderBy('kode_kategori')
            ->paginate(10)
            ->withQueryString();

        $akunIds = $kategori->getCollection()
            ->flatMap(fn ($kat) => $kat->akunKeuangan)
            ->flatMap(fn ($sub) => collect([$sub->id])->merge($sub->children->pluck('id')))
            ->all();

        $akunIdsTerpakai = DetailJurnal::whereIn('akun_id', $akunIds)
            ->distinct()
            ->pluck('akun_id')
            ->all();

        $nextKodeSubKategori = $allKategori->mapWithKeys(
            fn ($kat) => [$kat->id => $this->generateKodeSubKategori($kat->id)]
        );

        $nextKodeAkun = $subKategoriList->mapWithKeys(
            fn ($sub) => [$sub->id => $this->generateKodeAkun($sub)]
        );

        return view('pages.data-induk.coa.index', compact(
            'totalKategori',
            'totalSubKategori',
            'totalAkun',
            'kategori',
            'subKategoriList',
            'allKategori',
            'akunIdsTerpakai',
            'nextKodeSubKategori',
            'nextKodeAkun'
        ));
    }

    // Kategori

    public function tampilkanFormTambahKategoriAkun()
    {
        return view('pages.data-induk.coa.create-kategori');
    }

    public function simpanKategoriAkunBaru(StoreKategoriRequest $request)
    {
        KategoriAkun::create($request->validated());

        return redirect()
            ->route('dashboard.coa.index')
            ->with('success', 'Kategori akun berhasil ditambahkan.');
    }

    public function tampilkanFormUbahKategoriAkun(KategoriAkun $kategori)
    {
        return view('pages.data-induk.coa.edit-kategori', compact('kategori'));
    }

    public function perbaruiKategoriAkun(UpdateKategoriRequest $request, KategoriAkun $kategori)
    {
        if ($kategori->akunKeuangan()->exists()) {
            return back()->with("error",
                "Kategori tidak dapat diubah karena sudah memiliki akun turunan.");
        }
        $kategori->update($request->validated());
        return redirect()->route("dashboard.coa.index")
            ->with("success", "Kategori akun berhasil diperbarui.");
    }

    public function hapusKategoriAkun(KategoriAkun $kategori)
    {
        if (!auth()->user()->hasHakAkses('DELETE_COA')) {
            return back()->with('error', 'Anda tidak memiliki akses untuk menghapus kategori akun ini.');
        }

        try {

            if ($kategori->akunKeuangan()->exists()) {
                return back()->with(
                    'error',
                    'Kategori tidak dapat dihapus karena masih memiliki sub kategori.'
                );
            }

            $kategori->delete();

            return back()->with(
                'success',
                'Kategori berhasil dihapus.'
            );

        } catch (\Exception $e) {

            return back()->with(
                'error',
                'Kategori tidak dapat dihapus karena masih digunakan.'
            );
        }
    }

    // Sub Kategori

    public function tampilkanFormTambahSubKategori()
    {
        return redirect()->route('dashboard.coa.index');
    }

    public function simpanSubKategoriBaru(StoreSubKategoriRequest $request)
    {
        $data = $request->validated();
        $data['kode_akun'] = $this->generateKodeSubKategori($data['kategori_akun_id']);

        Akun::create([
            ...$data,
            'parent_id' => null,
        ]);

        return redirect()
            ->route('dashboard.coa.index')
            ->with('success', 'Sub kategori akun berhasil ditambahkan.');
    }

    public function tampilkanFormUbahSubKategori(Akun $subKategori)
    {
        $kategoriList = KategoriAkun::orderBy('kode_kategori')->get();

        $subKategoriList = Akun::whereNull('parent_id')
            ->orderBy('kode_akun')
            ->get();

        $allKategori = KategoriAkun::orderBy('kode_kategori')->get();

        return view('pages.data-induk.coa.edit-subkategori', compact(
            'subKategori',
            'kategoriList',
            'subKategoriList',
            'allKategori'
        ));
    }

    public function perbaruiSubKategori(UpdateSubKategoriRequest $request, Akun $subKategori)
    {
        if ($subKategori->children()->exists()) {
            return back()->with("error",
                "Sub kategori tidak dapat diubah karena sudah memiliki akun turunan.");
        }
        $subKategori->update([...$request->validated()]);
        return redirect()->route("dashboard.coa.index")
            ->with("success", "Sub kategori akun berhasil diperbarui.");
    }

    public function hapusSubKategori(Akun $subKategori)
    {
        if (!auth()->user()->hasHakAkses('DELETE_COA')) {
            return back()->with('error', 'Anda tidak memiliki akses untuk menghapus sub kategori ini.');
        }

        try {

            if ($subKategori->children()->exists()) {
                return back()->with(
                    'error',
                    'Sub kategori tidak dapat dihapus karena masih memiliki akun.'
                );
            }

            // Tolak hapus 
            if (DetailJurnal::where('akun_id', $subKategori->id)->exists()) {
                return back()->with(
                    'error',
                    'Sub kategori tidak dapat dihapus karena sudah digunakan pada transaksi.'
                );
            }

            $subKategori->delete();

            return back()->with(
                'success',
                'Sub kategori berhasil dihapus.'
            );

        } catch (\Exception $e) {

            return back()->with(
                'error',
                'Sub kategori tidak dapat dihapus karena masih digunakan.'
            );
        }
    }   

    // Akun

    public function tampilkanFormTambahAkun()
    {
        $subKategoriList = Akun::whereNull('parent_id')
            ->with('kategoriAkun')
            ->orderBy('kode_akun')
            ->get();

        return view('pages.data-induk.coa.create-akun', compact('subKategoriList'));
    }

    public function simpanAkunBaru(StoreAkunRequest $request)
    {
        $data = $request->validated();
        $subKategori = Akun::findOrFail($data['parent_id']);

        $data['kode_akun'] = $this->generateKodeAkun($subKategori);

        Akun::create([
            ...$data,
            'kategori_akun_id' => $subKategori->kategori_akun_id,
        ]);

        return redirect()
            ->route('dashboard.coa.index')
            ->with('success', 'Akun berhasil ditambahkan.');
    }

    public function tampilkanFormUbahAkun(Akun $akun)
    {
        $subKategoriList = Akun::whereNull('parent_id')
            ->with('kategoriAkun')
            ->orderBy('kode_akun')
            ->get();

        $akunList = Akun::whereNotNull('parent_id')
            ->orderBy('kode_akun')
            ->get();

        return view('pages.data-induk.coa.edit-akun', compact(
            'akun',
            'subKategoriList',
            'akunList'
        ));
    }

    public function perbaruiAkun(UpdateAkunRequest $request, Akun $akun)
    {
        if (DetailJurnal::where("akun_id", $akun->id)->exists()) {
            // Sudah dipakai transaksi: hanya status yang boleh berubah
            $akun->update(["status" => $request->validated()["status"]]);
        } else {
            $subKategori = Akun::findOrFail($request->parent_id);
            $akun->update([...$request->validated(),
                "kategori_akun_id" => $subKategori->kategori_akun_id]);
        }
        return redirect()->route("dashboard.coa.index")
            ->with("success", "Akun berhasil diperbarui.");
    }

    public function hapusAkun(Akun $akun)
    {
        if (!auth()->user()->hasHakAkses('DELETE_COA')) {
            return back()->with('error', 'Anda tidak memiliki akses untuk menghapus akun ini.');
        }

        try {
            if (DetailJurnal::where('akun_id', $akun->id)->exists()) {
                return back()->with(
                    'error',
                    'Akun tidak dapat dihapus karena sudah digunakan pada transaksi.'
                );
            }

            if ($akun->children()->exists()) {
                return back()->with(
                    'error',
                    'Akun tidak dapat dihapus karena masih memiliki sub akun.'
                );
            }

            $akun->delete();

            return back()->with('success', 'Akun berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Akun tidak dapat dihapus karena masih digunakan.');
        }
    }

    // Helper: generate kode otomatis 

    private function generateKodeSubKategori(int $kategoriId): string
    {
        $kategori = KategoriAkun::findOrFail($kategoriId);

        $last = Akun::whereNull('parent_id')
            ->where('kategori_akun_id', $kategoriId)
            ->get()
            ->map(fn ($akun) => (int) substr($akun->kode_akun, strpos($akun->kode_akun, '-') + 1))
            ->max();

        $next = $last ? $last + 1000 : 1000;

        return $kategori->kode_kategori . '-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    private function generateKodeAkun(Akun $subKategori): string
    {
        $last = Akun::where('parent_id', $subKategori->id)
            ->get()
            ->map(fn ($akun) => (int) substr($akun->kode_akun, strpos($akun->kode_akun, '-') + 1))
            ->max();

        $prefix = substr($subKategori->kode_akun, 0, strpos($subKategori->kode_akun, '-'));
        $base   = (int) substr($subKategori->kode_akun, strpos($subKategori->kode_akun, '-') + 1);

        $next = $last ? $last + 1 : $base + 1;

        return $prefix . '-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}