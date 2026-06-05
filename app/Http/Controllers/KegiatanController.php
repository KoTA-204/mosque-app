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

        if ($request->filled('search')) {
            $query->where('nama_kegiatan', 'ilike', '%' . $request->search . '%');
        }
        if ($request->filled('jenis')) {
            $query->where('jenis_kegiatan', $request->jenis);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage  = in_array($request->per_page, [10, 25, 50]) ? $request->per_page : 10;
        $kegiatan = $query->latest()->paginate($perPage)->withQueryString();
        $panitias = User::whereHas('roles', fn($q) =>
            $q->where('role_name', 'Panitia Khusus')
        )->get();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('pages.kegiatan.table', compact('kegiatan', 'panitias'))->render(),
            ]);
        }

        return view('pages.kegiatan.index', compact('kegiatan', 'panitias'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kegiatan'   => 'required|string|max:255',
            'jenis_kegiatan'  => 'required|in:QURBAN,ZAKAT,KAJIAN,SOSIAL,LAINNYA',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'anggaran'        => 'required|numeric|min:0',
            'panitia_id'      => 'required|exists:users,id',
        ]);

        $validated['status'] = Kegiatan::STATUS_AKTIF;

        Kegiatan::create($validated);

        return redirect()->route('dashboard.kegiatan.index')
            ->with('success', 'Kegiatan berhasil ditambahkan');
    }

    public function update(Request $request, Kegiatan $kegiatan)
    {
        $validated = $request->validate([
            'nama_kegiatan'   => 'required|string|max:255',
            'jenis_kegiatan'  => 'required|in:QURBAN,ZAKAT,KAJIAN,SOSIAL,LAINNYA',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'anggaran'        => 'required|numeric|min:0',
            'panitia_id'      => 'required|exists:users,id',
        ]);

        $kegiatan->update($validated);

        return redirect()->route('dashboard.kegiatan.index')
            ->with('success', 'Kegiatan berhasil diupdate');
    }

    public function destroy(Kegiatan $kegiatan)
    {
        $kegiatan->delete();
        return redirect()->route('dashboard.kegiatan.index')
            ->with('success', 'Kegiatan berhasil dihapus');
    }

    public function create()
    {
        $panitias = User::whereHas('roles', fn($q) =>
            $q->where('role_name', 'Panitia Khusus')
        )->get();
        return view('pages.kegiatan.create', compact('panitias'));
    }

    public function edit(Kegiatan $kegiatan)
    {
        $panitias = User::whereHas('roles', fn($q) =>
            $q->where('role_name', 'Panitia Khusus')
        )->get();
        return view('pages.kegiatan.edit', compact('kegiatan', 'panitias'));
    }

    public function show(Kegiatan $kegiatan)
    {
        $kegiatan->load('panitia');
        return view('pages.kegiatan.show', compact('kegiatan'));
    }
}