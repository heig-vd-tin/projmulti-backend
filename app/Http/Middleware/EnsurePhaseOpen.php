<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bloque l'accès selon la phase choisie par l'admin :
 *  - projects    : les profs saisissent leurs projets, les étudiants n'ont pas accès ;
 *  - preferences : les étudiants choisissent, les profs consultent sans modifier ;
 *  - closed      : les profs consultent, les étudiants n'ont pas accès.
 * L'admin a toujours accès. Les écritures des profs sont contrôlées par ProjectPolicy.
 */
class EnsurePhaseOpen
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && $user->isStudent() && Setting::phase() !== Setting::PHASE_PREFERENCES) {
            return response()->json([
                'code' => 'phase_closed',
                'message' => "La plateforme n'est pas ouverte aux étudiants pour le moment.",
            ], 403);
        }
        return $next($request);
    }
}
