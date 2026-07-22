<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CekHakAkses
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $hak_akses): Response
    {
        $pengguna = auth()->user();

        if (!$pengguna) {
            return redirect()->route('auth.login');
        }

        if (!$pengguna->hasHakAkses($hak_akses)) {
            if ($request->isMethod('get')) {
                abort(403, 'Anda tidak memiliki akses ke halaman ini');
            }

            $redirect = redirect()->back()
                ->withErrors(['hak_akses' => 'Anda tidak memiliki izin untuk melakukan aksi ini.'])
                ->withInput();

            if ($request->isMethod('put') || $request->isMethod('patch') || $request->isMethod('delete')) {
                $redirect->with('error', 'Anda tidak memiliki izin untuk melakukan aksi ini.');
            }

            return $redirect;
        }

        return $next($request);
    }
}