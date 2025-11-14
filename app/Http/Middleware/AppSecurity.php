<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\Log;

use Closure;
use App\Aplication;
use App\Helpers\ConectionDB;
use Carbon\Carbon;

class AppSecurity
{
    public function handle($request, Closure $next)
    {
        if ($codigo = $request->header('x-socket-id')) {
          Log::info('IsPusherSecurity');
          Log::info($request->header('x-socket-id'));
          Log::info($request->header('X-Socket-ID'));
        }
        //var_dump('esto dara error');exit();
        //Obtener el codigo de serial de la aplicacion
        $codigo = $request->header('App-Key');

        if (!$codigo) return response()->json('Codigo de aplicacion no encontrado en header.',401);

        session(['app-serial' => $codigo]);

        $app = Aplication::where('serial', $codigo)->first();
        if(!$app) return response()->json('Serial no encontrado.',404);

        session(['app-current' => $app]);
        session(['app-config' => $app->getApp()]);

        $today = Carbon::now()->toDateTimeString();

        if(strtotime($app->expiration) < strtotime($today)){
          return response()->json('La aplicacion ha expirado, consulte con su proveedor', 498);
        }

        if ($app->active == false) {
          return response()->json('El servicio para esta aplicacion fue cortado, consulte con su proveedor', 423);
        }

        $conexion = new ConectionDB($app);
        // $conexion->set_database();
        $conexion->set_database($app->database);

        auth()->setDefaultDriver('local');
        
        return $next($request);
    }
}
