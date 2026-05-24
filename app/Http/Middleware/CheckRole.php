<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        $rol = strtolower($request->user()?->rol ?? '');

        // 'administrador' es alias de 'admin'
        if ($rol === 'administrador') {
            $rol = 'admin';
        }

        $allowed = array_map('strtolower', $roles);

        if (!in_array($rol, $allowed)) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        return $next($request);
    }
}
