<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DenyAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isAdmin()) {
            abort(403, 'Admin hanya dapat memantau, tidak dapat bertransaksi.');
        }

        return $next($request);
    }
}