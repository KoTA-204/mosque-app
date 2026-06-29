<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Notifications\AkunDibuatNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $stats = [
            'total'       => User::count(),
            'aktif'       => User::where('status', 'active')->count(),
            'tidak_aktif' => User::where('status', 'inactive')->count(),
        ];

        if ($request->get('stats_only')) {
            return response()->json(['stats' => $stats]);
        }

        $query = User::with('roles');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%");
            });
        }
        if ($request->filled('role')) {
            $query->whereHas('roles', fn($q) => $q->where('role_name', $request->role));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = in_array($request->per_page, [10, 25, 50, 100]) ? $request->per_page : 10;
        $users   = $query->paginate($perPage)->withQueryString();
        $roles   = Role::all();

        if ($request->ajax()) {
            return response()->json([
                'html'  => view('pages.users.table', compact('users', 'roles'))->render(),
                'stats' => $stats,
            ]);
        }

        return view('pages.users.index', compact('users', 'roles', 'stats'));
    }

    public function create(Request $request)
    {
        $roles = Role::all();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('pages.users.create', compact('roles'))->render(),
            ]);
        }

        return view('pages.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email|unique:users,email',
            'role_id' => 'required|exists:roles,id',
            'status'  => 'required|in:active,inactive',
        ], [
            'name.required'    => 'Nama wajib diisi.',
            'email.required'   => 'Email wajib diisi.',
            'email.email'      => 'Format email tidak valid.',
            'email.unique'     => 'Email sudah digunakan.',
            'role_id.required' => 'Role wajib dipilih.',
            'role_id.exists'   => 'Role yang dipilih tidak valid.',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make(Str::random(32)),
            'role_id'  => $validated['role_id'],
            'status'   => $validated['status'],
        ]);

        $emailSent = false;
        try {
            $user->notify(new AkunDibuatNotification($user->name));
            $emailSent = true;
        } catch (\Throwable $e) {
            // Email gagal tapi user tetap tersimpan
            \Log::error('Gagal kirim email aktivasi user #' . $user->id . ': ' . $e->getMessage());
        }

        $message = $emailSent
            ? 'User berhasil ditambahkan. Link pengaturan password telah dikirim ke ' . $user->email . '.'
            : 'User berhasil ditambahkan, namun email gagal terkirim. Pastikan konfigurasi mail sudah benar.';

        if ($request->ajax()) {
            return response()->json([
                'success'    => true,
                'message'    => $message,
                'email_sent' => $emailSent,
            ]);
        }

        return redirect()->route('dashboard.users.index')->with('success', $message);
    }

    public function edit(Request $request, User $user)
    {
        $user->load('roles');
        $roles = Role::all();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('pages.users.edit', compact('user', 'roles'))->render(),
            ]);
        }

        return view('pages.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email|unique:users,email,' . $user->id,
            'role_id' => 'required|exists:roles,id',
            'status'  => 'required|in:active,inactive',
        ], [
            'name.required'    => 'Nama wajib diisi.',
            'email.required'   => 'Email wajib diisi.',
            'email.email'      => 'Format email tidak valid.',
            'email.unique'     => 'Email sudah digunakan.',
            'role_id.required' => 'Role wajib dipilih.',
            'role_id.exists'   => 'Role yang dipilih tidak valid.',
        ]);

        $user->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'User berhasil diupdate.',
            ]);
        }

        return redirect()->route('dashboard.users.index')
            ->with('success', 'User berhasil diupdate.');
    }

    public function confirmDelete(Request $request, User $user)
    {
        if ($request->ajax()) {
            return response()->json([
                'html' => view('pages.users.delete', compact('user'))->render(),
            ]);
        }

        return redirect()->route('dashboard.users.index');
    }

    public function destroy(Request $request, User $user)
    {
        // Tolak hapus jika user sudah mencatat transaksi.
        // Sebutkan jumlahnya & sarankan nonaktifkan agar jejak audit tetap utuh.
        $jumlahTransaksi = \App\Models\Transaksi::where('user_id', $user->id)->count();
        if ($jumlahTransaksi > 0) {
            $msg = 'User tidak dapat dihapus karena sudah memiliki ' . $jumlahTransaksi
                . ' riwayat transaksi. Untuk menjaga jejak audit, nonaktifkan akun ini '
                . '(buka Edit user lalu ubah status menjadi Nonaktif) sebagai gantinya.';
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return redirect()->route('dashboard.users.index')->with('error', $msg);
        }

        // Cegah penghapusan diam-diam: kolom kegiatan.panitia_id memakai
        // cascadeOnDelete, sehingga menghapus user yang menjadi panitia akan
        // ikut menghapus kegiatannya (kehilangan data). Blokir & beri pesan jelas
        // beserta daftar kegiatan terkait agar admin tahu apa yang harus diganti.
        $kegiatanPanitia = \App\Models\Kegiatan::where('panitia_id', $user->id)
            ->pluck('nama_kegiatan');
        if ($kegiatanPanitia->isNotEmpty()) {
            $msg = 'User tidak dapat dihapus karena masih terdaftar sebagai panitia pada '
                . $kegiatanPanitia->count() . ' kegiatan: ' . $kegiatanPanitia->implode(', ')
                . '. Ganti panitia kegiatan tersebut terlebih dahulu, atau nonaktifkan akun ini.';
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return redirect()->route('dashboard.users.index')->with('error', $msg);
        }

        try {
            $user->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::warning('Gagal menghapus user karena relasi terkait', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            $msg = 'User tidak dapat dihapus karena masih tertaut dengan data lain (mis. menjadi panitia kegiatan).';
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return redirect()->route('dashboard.users.index')->with('error', $msg);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'User berhasil dihapus.',
            ]);
        }

        return redirect()->route('dashboard.users.index')
            ->with('success', 'User berhasil dihapus.');
    }
}