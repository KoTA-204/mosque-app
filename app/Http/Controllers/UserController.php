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
        // Tolak hapus jika user sudah mencatat transaksi
        if ($user->hasTransaksi()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak dapat dihapus karena sudah memiliki riwayat transaksi.',
                ], 422);
            }

            return redirect()->route('dashboard.users.index')
                ->with('error', 'User tidak dapat dihapus karena sudah memiliki riwayat transaksi.');
        }

        $user->delete();

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