<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Vérifier que l'utilisateur est connecté
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        
        // Vérifier que l'utilisateur a un rôle valide
        if (!$user->role) {
            abort(403, 'Accès refusé : rôle non défini');
        }

        // Vérifier que le rôle de l'utilisateur est dans la liste des rôles autorisés
        if (!in_array($user->role, $roles)) {
            abort(403, 'Accès refusé : permissions insuffisantes');
        }

        return $next($request);
    }
}
