<?php

namespace App\Http\Controllers\Autentikasi;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function tampilkanFormLogin(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard.index');
        }

        return view('pages.autentikasi.login');
    }

    public function prosesLogin(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid. Contoh: nama@email.com',
            'password.required' => 'Password wajib diisi.',
        ]);

        if (!Auth::attempt($credentials)) {
            return back()->withErrors([
                'email' => 'Email atau password salah.',
            ]);
        }

        // ── PERBAIKAN: tolak login jika akun dinonaktifkan ───────────────────
        // Kredensial sudah benar (Auth::attempt sukses & sesi sudah dibuat),
        // tapi kita masih harus memastikan akunnya berstatus 'active'.
        // Kalau tidak aktif: bongkar lagi sesi yang baru dibuat, lalu tolak.
        // Nilai 'active' menyesuaikan kolom users.status pada seeder.
        if (Auth::user()->status !== 'active') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Akun Anda dinonaktifkan. Silakan hubungi administrator.',
            ]);
        }
        // ─────────────────────────────────────────────────────────────────────

        $request->session()->regenerate();
        return redirect()->intended(route('dashboard.index'));
    }

    public function prosesLogout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('auth.login');
    }
}