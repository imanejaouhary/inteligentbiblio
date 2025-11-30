<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // S'assurer que les réponses API ont uniquement Content-Type: application/json
        // (sauf pour les téléchargements de fichiers)
        if ($request->is('api/*') && !$response->headers->has('Content-Disposition')) {
            $response->headers->set('Content-Type', 'application/json', true);
            
            // Supprimer les headers indésirables pour les API
            $response->headers->remove('X-XSRF-TOKEN');
            $response->headers->remove('XSRF-TOKEN');
        }

        return $response;
    }
}






