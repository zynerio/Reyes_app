<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        if (! $user || $user->role !== 'admin') {
            return response()->json(['message' => 'Prohibido'], 403);
        }
        return $next($request);
    }
}

