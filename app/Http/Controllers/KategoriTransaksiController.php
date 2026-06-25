<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\KategoriTransaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KategoriTransaksiController extends Controller
{
    public function index(Request $request)
    {
        $perPage  = $request->input('per_page', 10);
        $search   = $request->input('search');

        $kategori = KategoriTransaksi::withCount('transaksi')
            ->when($search, fn($q) =>
                $q->where(fn($sub) =>
                    $sub->whereRaw('LOWER(nama_kategori) LIKE ?', ['%' . strtolower($search) . '%'])
                        ->orWhereRaw('LOWER(deskripsi) LIKE ?', ['%' . strtolower($search) . '%'])
                )
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
        $validator = Validator::make($request->all(), [
            'nama_kategori'   => 'required|string|max:100|unique:kategori_transaksi,nama_kategori',
            'status'          => 'required|in:aktif,tidak_aktif',
            'deskripsi'       => 'nullable|string|max:500',
        ], [
            'nama_kategori.required'   => 'Nama kategori wajib diisi.',
            'nama_kategori.unique'     => 'Nama kategori sudah digunakan.',
            'status.required'          => 'Status wajib dipilih.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withErrors($validator, 'createKategori')
                ->withInput();
        }

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
        $validator = Validator::make($request->all(), [
            'nama_kategori'   => 'required|string|max:100|unique:kategori_transaksi,nama_kategori,' . $kategoriTransaksi->id,
            'status'          => 'required|in:aktif,tidak_aktif',
            'deskripsi'       => 'nullable|string|max:500',
        ], [
            'nama_kategori.required'   => 'Nama kategori wajib diisi.',
            'nama_kategori.unique'     => 'Nama kategori sudah digunakan.',
            'status.required'          => 'Status wajib dipilih.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withErrors($validator, 'editKategori')
                ->withInput()
                ->with('edit_error_id', $kategoriTransaksi->id);
        }

        $kategoriTransaksi->update([
            'nama_kategori'   => $request->nama_kategori,
            'status'          => $request->status,
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

        try {
            $kategoriTransaksi->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::warning('Gagal menghapus kategori transaksi karena relasi terkait', [
                'id'    => $kategoriTransaksi->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Kategori tidak dapat dihapus karena masih tertaut dengan data lain.');
        }

        return back()->with('success', 'Kategori transaksi berhasil dihapus.');
    }
}