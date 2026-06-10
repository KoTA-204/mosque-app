<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\KategoriTransaksi;
use Illuminate\Http\Request;

class KategoriTransaksiController extends Controller
{
    public function index(Request $request)
    {
        $perPage  = $request->input('per_page', 10);
        $search   = $request->input('search');

        $kategori = KategoriTransaksi::withCount('transaksi')
            ->when($search, fn($q) =>
                $q->whereRaw('LOWER(nama_kategori) LIKE ?', ['%' . strtolower($search) . '%'])
                ->orWhereRaw('LOWER(deskripsi) LIKE ?', ['%' . strtolower($search) . '%'])
            )
            ->when($request->input('status'), fn($q, $status) =>
                $q->where('status', $status)
            )
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return view('pages.kategori-transaksi.index', compact('kategori', 'perPage'));
    }

    public function create()
    {
        return view('pages.kategori-transaksi.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kategori'   => 'required|string|max:100|unique:kategori_transaksi,nama_kategori',
            'status'          => 'required|in:aktif,tidak_aktif',
            'deskripsi'       => 'nullable|string|max:500',
        ], [
            'nama_kategori.required'   => 'Nama kategori wajib diisi.',
            'nama_kategori.unique'     => 'Nama kategori sudah digunakan.',
            'status.required'          => 'Status wajib dipilih.',
        ]);

        KategoriTransaksi::create($request->only(
            'nama_kategori', 'status', 'deskripsi'
        ));

        return redirect()
            ->route('dashboard.kategori-transaksi.index')
            ->with('success', 'Kategori transaksi berhasil ditambahkan.');
    }

    public function edit(KategoriTransaksi $kategoriTransaksi)
    {
        return view('pages.kategori-transaksi.edit', compact('kategoriTransaksi'));
    }

    public function update(Request $request, KategoriTransaksi $kategoriTransaksi)
    {
        $request->validate([
            'nama_kategori'   => 'required|string|max:100|unique:kategori_transaksi,nama_kategori,' . $kategoriTransaksi->id,
            'status'          => 'required|in:aktif,tidak_aktif',
            'deskripsi'       => 'nullable|string|max:500',
        ], [
            'nama_kategori.required'   => 'Nama kategori wajib diisi.',
            'nama_kategori.unique'     => 'Nama kategori sudah digunakan.',
            'status.required'          => 'Status wajib dipilih.',
        ]);

        $kategoriTransaksi->update([
            'nama_kategori'   => $request->nama_kategori,
            'status'          => strtolower(trim($request->status)), 
            'deskripsi'       => $request->deskripsi,
        ]);

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