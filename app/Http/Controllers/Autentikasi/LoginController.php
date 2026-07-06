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
            return redirect()->route($this->redirectRouteUntukRole(Auth::user()));
        }

        return view('pages.autentikasi.login');
    }

    public function prosesLogin(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
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

        // Tolak login jika akun dinonaktifkan
        if (Auth::user()->status !== 'active') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Akun Anda dinonaktifkan. Silakan hubungi administrator.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route($this->redirectRouteUntukRole(Auth::user())));
    }

    public function prosesLogout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('auth.login');
    }

    /**
     * Tentukan halaman pertama yang ditampilkan setelah login
     * berdasarkan role pengguna.
     *
     * Mapping slug role → route tujuan:
     *   admin / super-admin         → Manajemen Pengguna
     *   bendahara-1 / bendahara-2   → Dashboard Operasional
     *   ketua-dkm                   → Dashboard Operasional
     *   phm                         → Pencatatan Kencleng
     *   panitia-khusus              → Pencatatan Transaksi Kegiatan
     *     (akan diupdate ke panitia-kegiatan-khusus setelah bulk seeder)
     *   sekretaris                  → Aset
     *   default                     → Dashboard Operasional
     */
    private function redirectRouteUntukRole($user): string
    {
        $slug = optional($user->roles)->slug;

        $map = [
            // Admin — akses penuh, langsung ke manajemen pengguna
            'super-admin'              => 'dashboard.users.index',
            'admin'                    => 'dashboard.users.index',

            // Bendahara & Ketua DKM — landing di dashboard utama
            'bendahara-1'              => 'dashboard.index',
            'bendahara-2'              => 'dashboard.index',
            'ketua-dkm'               => 'dashboard.index',

            // PHM — tugas utama: kencleng
            'phm'                      => 'dashboard.kencleng.index',

            // Panitia — langsung ke transaksi kegiatan yang ditugaskan
            // Catatan: slug akan berubah jadi panitia-kegiatan-khusus setelah bulk seeder
            'panitia-khusus'           => 'dashboard.transaksi-kegiatan.index',
            'panitia-kegiatan-khusus'  => 'dashboard.transaksi-kegiatan.index',

            // Sekretaris — langsung ke aset
            'sekretaris'               => 'dashboard.aset.index',
        ];

        return $map[$slug] ?? 'dashboard.index';
    }
}
