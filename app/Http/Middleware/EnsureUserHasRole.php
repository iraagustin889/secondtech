<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforcement server-side untuk halaman berdasarkan role.
 * Menu yang disembunyikan bukan authorization; setiap route
 * tetap ditolak 403 bila role tidak cocok.
 */
final class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->hasRole($role)) {
            abort(403);
        }

        return $next($request);
    }
}