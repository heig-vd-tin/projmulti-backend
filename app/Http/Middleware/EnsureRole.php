<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restreint une route à certains rôles : ->middleware('role:admin').
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        if (!$user || !in_array($user->role, $roles, true)) {
            return response()->json([
                'code' => 'forbidden',
                'message' => "Vous n'avez pas les droits pour cette action.",
            ], 403);
        }
        return $next($request);
    }
}
