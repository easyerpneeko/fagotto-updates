<?php

namespace App\Http\Middleware;

use Closure;
use App\Helpers\CurrentApp;
use App\Aplication;
use Auth;
use JWTAuth;

class HavePermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $strPermission)
    {
        $config = CurrentApp::Config(true);
        if (!$config) return CurrentApp::Config();

        $user = Auth::user();
        //var_dump($user);exit();

        if (CurrentApp::havePermission($strPermission,$config,$user)) {
          return $next($request);
        }

        return response()->json('No se cuentan con los permisos necesarios para hacer esto.',403);
    }
}
