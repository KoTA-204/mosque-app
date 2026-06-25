<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Models\User;
use Illuminate\Http\Request;

class KegiatanController extends Controller
{
    private function getPanitias()
    {
        return User::whereHas('roles', fn($q) =>
            $q->where('role_name', 'Panitia Khusus')
        )->get();
    }

    public function index(Request $request)
    {
        $stats = [
            'total'   => Kegiatan::count(),
            'aktif'   => Kegiatan::where('status', 'AKTIF')->count(),
            'ditutup' => Kegiatan::where('status', 'DITUTUP')->count(),
        ];

        if ($request->get('stats_only')) {
            return response()->json(['stats' => $stats]);
        }

        $query = Kegiatan::with('panitia');

        if ($request->filled('search')) {
            $query->whereRaw('LOWER(nama_kegiatan) LIKE ?', ['%'.strtolower($request->search).'%']);
        }

        if ($request->filled('jenis')) {
            $query->where('jenis_kegiatan', $request->jenis);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage  = in_array($request->per_page, [10, 25, 50]) ? $request->per_page : 10;
        $kegiatan = $query->latest()->paginate($perPage)->withQueryString();
        $panitias = $this->getPanitias();

        if ($request->ajax()) {
            return response()->json([
                'html'  => view('pages.kegiatan.table', compact('kegiatan', 'panitias'))->render(),
                'stats' => $stats,
            ]);
        }

        return view('pages.kegiatan.index', compact('kegiatan', 'panitias', 'stats'));
    }

    public function create(Request $request)
    {
        $panitias = $this->getPanitias();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('pages.kegiatan.create', compact('panitias'))->render(),
            ]);
        }

        return view('pages.kegiatan.create', compact('panitias'));
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

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Kegiatan berhasil ditambahkan.',
            ]);
        }

        return redirect()->route('dashboard.kegiatan.index')
            ->with('success', 'Kegiatan berhasil ditambahkan.');
    }

    public function show(Request $request, Kegiatan $kegiatan)
    {
        $kegiatan->load('panitia');

        $kegiatan->transaksi_count = $kegiatan->transaksi()->count();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('pages.kegiatan.show', compact('kegiatan'))->render(),
            ]);
        }

        return view('pages.kegiatan.show', compact('kegiatan'));
    }

    public function edit(Request $request, Kegiatan $kegiatan)
    {
        $panitias = $this->getPanitias();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('pages.kegiatan.edit', compact('kegiatan', 'panitias'))->render(),
            ]);
        }

        return view('pages.kegiatan.edit', compact('kegiatan', 'panitias'));
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

        // Cek ulang setelah update (misal tanggal diubah jadi sudah lewat)
        $kegiatan->tutupJikaSelesai();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Kegiatan berhasil diupdate.',
            ]);
        }

        return redirect()->route('dashboard.kegiatan.index')
            ->with('success', 'Kegiatan berhasil diupdate.');
    }

    public function confirmDelete(Request $request, Kegiatan $kegiatan)
    {
        $transaksiCount = $kegiatan->transaksi()->count();
        $hasTransaksi   = $transaksiCount > 0;

        if ($request->ajax()) {
            return response()->json([
                'html' => view('pages.kegiatan.delete', compact(
                    'kegiatan', 'hasTransaksi', 'transaksiCount'
                ))->render(),
            ]);
        }

        return redirect()->route('dashboard.kegiatan.index');
    }

    public function destroy(Request $request, Kegiatan $kegiatan)
    {
        if ($kegiatan->transaksi()->count() > 0) {
            $msg = 'Kegiatan tidak dapat dihapus karena memiliki transaksi.';

            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return redirect()->route('dashboard.kegiatan.index')->with('error', $msg);
        }

        try {
            $kegiatan->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::warning('Gagal menghapus kegiatan karena relasi terkait', [
                'id'    => $kegiatan->id,
                'error' => $e->getMessage(),
            ]);

            $msg = 'Kegiatan tidak dapat dihapus karena masih tertaut dengan data lain.';
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return redirect()->route('dashboard.kegiatan.index')->with('error', $msg);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Kegiatan berhasil dihapus.',
            ]);
        }

        return redirect()->route('dashboard.kegiatan.index')
            ->with('success', 'Kegiatan berhasil dihapus.');
    }
}