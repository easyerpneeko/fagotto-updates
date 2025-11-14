<?php

namespace App\models_local;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\ConectionDB;

class TypeUser extends Model
{

  protected $connection = 'mysql_local';
  protected $table = 'type_users';

  protected $fillable = [
      'name',
      'keyname',
      'permission',
  ];

  public static function getTypeUsersOfApp($app,$slim = false) {
      ConectionDB::ChangeDBToApp($app);
      if (!$slim) {
        $typeusers = TypeUser::all();
      }else{
        $typeusers = TypeUser::select('keyname','permission','id')->get();
      }
      foreach ($typeusers as $item) $item['permission'] = json_decode($item['permission'],1);
      return $typeusers;
  }

  public static function newTypeUser($request){
    $keysAllow = [
      'name',
      'keyname',
      'permission',
    ];
    $itemToSave = [];

    foreach ($keysAllow as $key){
      if (isset($request[$key])) $itemToSave[$key] = $request[$key];
      else $itemToSave[$key] = null;
    }
    return TypeUser::create($itemToSave);
  }

  public static function editTypeUser($request, $id){
    $typeUserActual = TypeUser::find($id);
    if(!$typeUserActual) return response()->json('Rol no encontrado', 404);

    if($request["name"]){
      $typeUserActual->name = $request["name"];
    }

    if($request['permission']){
      $typeUserActual->permission = $request["permission"];
    }else{
      $typeUserActual->permission = null;
    }

    if($request["keyname"]){
      $typeUserActual->keyname = $request["keyname"];
    }

    $typeUserActual->save();
    return response()->json('Rol editado exitosamente', 200);
  }

  public static function createRole($name,$keyname,$permission = null) {
    $role = new Self;
    $role->name = $name;
    $role->keyname = $keyname;
    $role->permission = $permission;
    if ($role->save()) return $role;
    return false;
  }

}
