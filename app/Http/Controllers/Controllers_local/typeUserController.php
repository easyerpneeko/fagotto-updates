<?php

namespace App\Http\Controllers\Controllers_local;

use Illuminate\Http\Request;
use App\models_local\TypeUser;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

use Auth;
use App\Aplication;
use App\Helpers\ConectionDB;

class typeUserController extends Controller
{
  protected function newTypeUser(Request $request){
      $validator = Validator::make($request->all(), [
        'name' => 'required|string|min:2|max:24',
        'keyname' => 'required|string|min:2|max:24|unique:mysql_local.type_users,keyname',
      ]);
      if($validator->fails()) return response()->json($validator->errors()->toJson(), 400);
      $_request = $request->all();

      if ($_request['permission']) {
        if(!$this->json_validator($_request['permission'])){
         return response()->json("Permisos no es un JSON", 400);
        }
      } else {
        $_request['permission'] = [];
      }//null || []
      if ($_request['permission'] === 'null') $_request['permission'] = null;

      $typeUser = TypeUser::newTypeUser($_request);
      if(!$typeUser) return response()->json("Database Error", 500);

      return response()->json('Rol registrado exitosamente', 200);
  }

  protected function editTypeUser(Request $request , $id){
      $validator = Validator::make($request->all(), [
        'name' => 'string|min:2|max:24',
        'keyname' => 'string|min:2|max:24',
      ]);
      if($validator->fails()) return response()->json($validator->errors()->toJson(), 400);
      if(Auth::user()->role == $id)return response()->json("No puedes editar tu propio rol", 400);
      $_request = $request->all();
      if (TypeUser::find($id) && TypeUser::find($id)->keyname != $_request['keyname']) {
        $keyname = TypeUser::where('keyname',$_request['keyname'])->first();
        if ($keyname) {
          return response()->json("El keyname ya se encuentra en uso", 400);
        }
      }

      if ($_request['permission'] === 'null') $_request['permission'] = null;

      if ($_request['permission']) {
        if(!$this->json_validator($_request['permission'])){
         return response()->json("Permisos no es un JSON", 400);
        }
      } else {
        $_request['permission'] = [];
      }//null || []

      return TypeUser::editTypeUser($_request, $id);
  }

  protected function getRoles(){
    $pquery = TypeUser::where('trash', 0)->get();
    if (!$pquery) return response()->json('Error del servidor',500);
    return response()->json($pquery);
  }

  protected function trashRole($id){
    $role = TypeUser::find($id);
    if(!$role) return response()->json('Rol no encontrado',404);
    if(Auth::user()->role == $id)return response()->json("No puedes borrar tu propio rol", 400);
    if($role->trash == 1) return response()->json('El rol ya se encuentra eliminado',400);

    $role->trash = 1;
    if(!$role->save()) return response()->json('Error del servidor',500);

    return response()->json(['Rol eliminado exitosamente'],200);
  }

  function json_validator($data = NULL) {
    if (!empty($data)) {
      @json_decode($data);
      return (json_last_error() === JSON_ERROR_NONE);
    }
    return false;
  }

  protected function getRolesById($id){
    $app = Aplication::find($id);
    if (!$app) return response()->json('Aplicacion no encontrada',404);

    ConectionDB::ChangeDBToApp($app);
    return $this->getRoles();
  }

  protected function registerTypeUserById(Request $request, $id){
    $app = Aplication::find($id);
    if (!$app) return response()->json('Aplicacion no encontrada',404);

    ConectionDB::ChangeDBToApp($app);
    return $this->newTypeUser($request);
  }

  protected function editUserById(Request $request, $idApp, $id){
    $app = Aplication::find($idApp);
    if (!$app) return response()->json('Aplicacion no encontrada',404);

    ConectionDB::ChangeDBToApp($app);
    return $this->editTypeUser($request, $id);
  }

  protected function trashRoleById($idRole, $id){
    $app = Aplication::find($id);
    if (!$app) return response()->json('Aplicacion no encontrada',404);

    ConectionDB::ChangeDBToApp($app);
    return $this->trashRole($idRole);
  }
}
