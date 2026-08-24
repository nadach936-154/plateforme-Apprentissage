<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (! $request->user() || $request->user()->role !== $role) {
            return response()->json([
                'message' => 'Accès non autorisé pour votre rôle.'
            ], 403);
        }

        return $next($request);
    }
}