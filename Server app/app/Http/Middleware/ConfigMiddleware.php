<?php

namespace App\Http\Middleware;

use Closure;
use App\Helpers\CurrentApp;
use App\Aplication;


class ConfigMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $strConfig)
    {
        $config = CurrentApp::Config(true);
        if (!$config) return CurrentApp::Config();

        if (CurrentApp::ConfStr($strConfig,$config)) {
          return $next($request);
        }

        return response()->json('No se encuentra instalado este modulo.',501);
    }
}
