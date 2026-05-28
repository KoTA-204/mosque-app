<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKategoriTransaksiRequest;
use App\Http\Requests\UpdateKategoriTransaksiRequest;
use App\Models\KategoriTransaksi;
use Illuminate\Http\Request;

class KategoriTransaksiController extends Controller
{
    public function index(Request $request)
    {
        $query = KategoriTransaksi::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_kategori', 'ilike', '%' . $request->search . '%')
                  ->orWhere('deskripsi', 'ilike', '%' . $request->search . '%');
            });
        }

        if ($request->filled('jenis')) {
            $query->where('jenis_transaksi', $request->jenis);
        }

        $perPage  = (int) $request->get('per_page', 10);
        $kategori = $query->orderBy('nama_kategori')->paginate($perPage)->withQueryString();

        return view('pages.kategori-transaksi.index', compact('kategori', 'perPage'));
    }

    public function create()
    {
        return view('pages.kategori-transaksi.create');
    }

    public function store(StoreKategoriTransaksiRequest $request)
    {
        KategoriTransaksi::create($request->validated());

        return redirect()
            ->route('dashboard.kategori-transaksi.index')
            ->with('success', 'Kategori transaksi berhasil ditambahkan.');
    }

    public function edit(KategoriTransaksi $kategoriTransaksi)
    {
        return view('pages.kategori-transaksi.edit', compact('kategoriTransaksi'));
    }

    public function update(UpdateKategoriTransaksiRequest $request, KategoriTransaksi $kategoriTransaksi)
    {
        $kategoriTransaksi->update($request->validated());

        return redirect()
            ->route('dashboard.kategori-transaksi.index')
            ->with('success', 'Kategori transaksi berhasil diperbarui.');
    }

    public function destroy(KategoriTransaksi $kategoriTransaksi)
    {
        if ($kategoriTransaksi->transaksi()->exists()) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena sudah digunakan oleh transaksi.');
        }

        $kategoriTransaksi->delete();

        return back()->with('success', 'Kategori transaksi berhasil dihapus.');
    }
}