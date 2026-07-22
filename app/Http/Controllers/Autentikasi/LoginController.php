<?php

namespace App\Http\Controllers\Autentikasi;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Batas maksimal percobaan login gagal sebelum akun di-throttle sementara.
     */
    private const MAX_LOGIN_ATTEMPTS = 5;

    /**
     * Lama waktu (dalam detik) akun di-throttle setelah mencapai batas gagal.
     * 3600 detik = 1 jam.
     */
    private const LOGIN_DECAY_SECONDS = 3600;

    public function tampilkanFormLogin(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route($this->redirectRouteUntukPeran(Auth::user()));
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

        $throttleKey = $this->throttleKey($request);

        // Jika sudah melebihi batas percobaan, tolak sebelum cek kredensial
        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_LOGIN_ATTEMPTS)) {
            $detikTersisa = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'email' => $this->pesanTerkunci($detikTersisa),
            ])->onlyInput('email');
        }

        if (!Auth::attempt($credentials)) {
            RateLimiter::hit($throttleKey, self::LOGIN_DECAY_SECONDS);

            $sisaPercobaan = $this->sisaPercobaan($throttleKey);

            // Jika hit ini yang membuat batas tercapai, langsung tampilkan pesan terkunci
            if ($sisaPercobaan <= 0) {
                $detikTersisa = RateLimiter::availableIn($throttleKey);

                return back()->withErrors([
                    'email' => $this->pesanTerkunci($detikTersisa),
                ])->onlyInput('email');
            }

            return back()->withErrors([
                'email' => "Email atau password salah. Sisa percobaan: {$sisaPercobaan} kali lagi.",
            ])->onlyInput('email');
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

        // Login berhasil → reset penghitung percobaan gagal
        RateLimiter::clear($throttleKey);

        $request->session()->regenerate();

        return redirect()->intended(route($this->redirectRouteUntukPeran(Auth::user())));
    }

    /**
     * Bangun key unik untuk rate limiter berdasarkan email (case-insensitive)
     */
    private function throttleKey(Request $request): string
    {
        return Str::lower((string) $request->input('email')) . '|' . $request->ip();
    }

    /**
     * Hitung sisa percobaan login yang masih tersedia untuk key tertentu.
     */
    private function sisaPercobaan(string $throttleKey): int
    {
        return max(0, self::MAX_LOGIN_ATTEMPTS - RateLimiter::attempts($throttleKey));
    }

    /**
     * Format pesan lockout dalam Bahasa Indonesia, dengan estimasi waktu
     * dalam menit jika lebih dari 60 detik, atau detik jika kurang.
     */
    private function pesanTerkunci(int $detikTersisa): string
    {
        if ($detikTersisa > 60) {
            $menit = (int) ceil($detikTersisa / 60);

            return "Terlalu banyak percobaan login gagal. Silakan coba lagi dalam {$menit} menit.";
        }

        return "Terlalu banyak percobaan login gagal. Silakan coba lagi dalam {$detikTersisa} detik.";
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
     * berdasarkan peran pengguna.
     *
     * Mapping slug peran → route tujuan:
     *   admin                       → Manajemen Pengguna
     *   bendahara-1 / bendahara-2   → Dashboard Operasional
     *   ketua-dkm                   → Dashboard Operasional
     *   pengurus-harian-masjid       → Pencatatan Kencleng
     *   panitia-kegiatan-khusus     → Pencatatan Transaksi Kegiatan
     *   sekretaris                  → Aset
     *   default                     → Dashboard Operasional
     */
    private function redirectRouteUntukPeran($pengguna): string
    {
        $slug = optional($pengguna->peran)->slug;

        $map = [
            // Admin — akses penuh, langsung ke manajemen pengguna
            'administrator'            => 'dashboard.pengguna.index',

            // Bendahara & Ketua DKM — landing di dashboard utama
            'bendahara-1'              => 'dashboard.index',
            'bendahara-2'              => 'dashboard.index',
            'ketua-dkm'               => 'dashboard.index',

            // Pengurus Harian Masjid — tugas utama: kencleng
            'pengurus-harian-masjid'   => 'dashboard.kencleng.index',

            // Panitia — langsung ke transaksi kegiatan yang ditugaskan
            'panitia-kegiatan-khusus'  => 'dashboard.transaksi-kegiatan.index',

            // Sekretaris — langsung ke aset
            'sekretaris'               => 'dashboard.aset.index',
        ];

        return $map[$slug] ?? 'dashboard.index';
    }
}