<?php

namespace App\Http\Middleware;

use Closure;

class ApiSecurity
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
        $codigo = $request->header('Api-Key');

        if (!$codigo) return response()->json('Llave de API no encontrada en header.',401);
        if ($codigo !== '2af345002d3b2bafe7e22c42cef2efc6') return response()->json('Llave de API es invalida.',401);

        return $next($request);
    }
}
