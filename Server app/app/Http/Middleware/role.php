<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use App\models_local\TypeUser;

class role
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
      $user = Auth::user();
      if(!$user) return response()->json("Usuario no encontrado", 404);
      $roles=array_slice(func_get_args(), 2);
      $hasRole=false;

      /*Buscamos el rol del usuario*/
      $roleId = $user->role;
      $roleIns = TypeUser::find($roleId);
      $roleKeyName = $roleIns->keyname;
      $roleName = $roleIns->name;

      /*Comparamos que el ID o el nombre llave o el nombre coincida*/
      foreach($roles as $role){
          if (!is_nan((float)$role)) if ($roleId === (integer) $role) $hasRole = true;
          else if ($roleName == $role || $roleKeyName == $role) $hasRole = true;
      }

      if(!$hasRole) return response()->json('No autorizado', 401);

      return $next($request);

    }
}
