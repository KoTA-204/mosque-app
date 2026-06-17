<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

// ── PERBAIKAN: middleware baru untuk memutus sesi user nonaktif ──────────────
// Pasang pada grup route yang butuh proteksi (lihat routes/web.php di bawah).
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->status !== 'active') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('auth.login')
                ->withErrors(['email' => 'Akun Anda dinonaktifkan. Silakan hubungi administrator.']);
        }

        return $next($request);
    }
}