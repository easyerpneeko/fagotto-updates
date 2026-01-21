<?php

namespace App\Http\Middleware;

use Closure;

class FlowCors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        \Log::info('🔴 FlowCors ejecutándose para: ' . $request->getMethod() . ' ' . $request->path());
        
        // Manejar peticiones OPTIONS (preflight) primero
        if ($request->getMethod() === "OPTIONS") {
            \Log::info('🔴 Respondiendo OPTIONS con headers CORS');
            $response = response('', 200);
        } else {
            // Procesar la petición normal
            $response = $next($request);
        }
        
        // ELIMINAR headers CORS existentes para evitar duplicados
        $response->headers->remove('Access-Control-Allow-Origin');
        $response->headers->remove('Access-Control-Allow-Methods');
        $response->headers->remove('Access-Control-Allow-Headers');
        $response->headers->remove('Access-Control-Allow-Credentials');
        $response->headers->remove('Access-Control-Max-Age');
        
        // Forzar headers CORS correctos
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'POST, GET, OPTIONS, PUT, DELETE');
        $response->headers->set('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, X-Token-Auth, Authorization, Application, Accept');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Max-Age', '86400');
            
        \Log::info('🔴 Headers CORS configurados correctamente');
        
        return $response;
    }
}
