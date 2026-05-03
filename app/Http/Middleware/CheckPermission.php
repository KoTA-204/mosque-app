<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('auth.login');
        }

        if (!$this->hasPermission($user, $permission)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini');
        }

        return $next($request);
    }

    private function hasPermission($user, string $permissionCode): bool
    {
        return $user->roles()
            ->whereHas('permissions', function ($query) use ($permissionCode) {
                $query->where('permission_code', $permissionCode)
                      ->where('is_active', true);
            })
            ->exists();
    }
}
