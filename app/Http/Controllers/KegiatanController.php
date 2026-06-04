<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Models\User;
use Illuminate\Http\Request;

class KegiatanController extends Controller
{
    public function index(Request $request)
    {
        $query = Kegiatan::with('panitia');

        if ($request->has('search')) {
            $query->where('nama_kegiatan', 'like', "%{$request->search}%");
        }
        if ($request->has('jenis') && $request->jenis != '') {
            $query->where('jenis_kegiatan', $request->jenis);
        }
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $kegiatan = $query->latest()->paginate(10);
        $panitias = User::whereHas('roles', function($q) {
            $q->where('role_name', 'Panitia Khusus');
        })->get();

        return view('pages.kegiatan.index', compact('kegiatan', 'panitias'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kegiatan'  => 'required|string|max:255',
            'jenis_kegiatan' => 'required|in:QURBAN,ZAKAT,KAJIAN,SOSIAL,LAINNYA',
            'tanggal_mulai'  => 'required|date',
            'tanggal_selesai'=> 'nullable|date|after_or_equal:tanggal_mulai',
            'anggaran'       => 'required|numeric|min:0',
            'status'         => 'required|in:DRAFT,BERJALAN,SELESAI,DIBATALKAN',
            'panitia_id'     => 'required|exists:users,id'
        ]);

        Kegiatan::create($request->all());

        return redirect()->route('dashboard.kegiatan.index')->with('success', 'Kegiatan berhasil ditambahkan');
    }

    public function update(Request $request, Kegiatan $kegiatan)
    {
        $request->validate([
            'nama_kegiatan'  => 'required|string|max:255',
            'jenis_kegiatan' => 'required|in:QURBAN,ZAKAT,KAJIAN,SOSIAL,LAINNYA',
            'tanggal_mulai'  => 'required|date',
            'tanggal_selesai'=> 'nullable|date|after_or_equal:tanggal_mulai',
            'anggaran'       => 'required|numeric|min:0',
            'status'         => 'required|in:DRAFT,BERJALAN,SELESAI,DIBATALKAN',
            'panitia_id'     => 'required|exists:users,id'
        ]);

        $kegiatan->update($request->all());

        return redirect()->route('dashboard.kegiatan.index')->with('success', 'Kegiatan berhasil diupdate');
    }

    public function destroy(Kegiatan $kegiatan)
    {
        $kegiatan->delete();
        return redirect()->route('dashboard.kegiatan.index')->with('success', 'Kegiatan berhasil dihapus');
    }
}