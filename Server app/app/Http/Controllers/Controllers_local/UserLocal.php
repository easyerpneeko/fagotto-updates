<?php

namespace App\Http\Controllers\Controllers_local;

use App\models_local\UserApp;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use App\Helpers\MPage;
use App\Aplication;
use App\Helpers\ConectionDB;
use Illuminate\Support\Facades\DB;
use Config;
use Auth;

class UserLocal extends Controller
{
  // Funciones para la app local
  protected function register(Request $request){
      $validator = Validator::make($request->all(), [
        'username' => 'required|string|unique:mysql_local.users|min:2|max:24',
        'fullname' => 'required|string|min:3|max:32',
        // 'email' => 'required|string|unique:mysql_local.users|min:4|max:180',
        'password' => 'required|string|min:4|max:48',
        'confirmPass' => 'required|string|min:4|max:48',
        'role' => 'required|exists:mysql_local.type_users,id',
        'avatar' => 'nullable|image'
      ]);
      if($validator->fails()) return response()->json($validator->errors()->toJson(), 400);
      $_request = $request->all();

      if($_request['password'] != $_request['confirmPass'])
        return response()->json('Las contraceñas no coinciden',400);

      $_request['password'] = Hash::make($_request['password']);

      $user = UserApp::NewUser($_request);
      if(!$user) return response()->json("Database Error",500);

      return response()->json(['Usuario registrado exitosamente'],200);
  }

  protected function editUser(Request $request, $id){
      $validator = Validator::make($request->all(), [
        'username' => 'string|min:2|max:24',
        'fullname' => 'string|min:3|max:32',
        'email' => 'string|min:4|max:180',
        'role' => 'required|exists:mysql_local.type_users,id'
      ]);

      if($validator->fails()) return response()->json($validator->errors(), 400);
      $_request = $request->all();
      $user = UserApp::where('username','=',$_request['username'])->where('id','<>',$id)->first();
      if ($user) response()->json('El nombre de usuario esta en uso',409);


      if (isset($_request['avatar']) && !is_string($_request['avatar'])) {
        $userImage = UserApp::find($id);
        if ($_request['avatar'] != $userImage->avatar){
          $validator = Validator::make($request->all(), [
            'avatar' => 'image'
          ]);
          if($validator->fails()) return response()->json($validator->errors(), 400);
        }
      }

      if(isset($_request['password'])){
        if ($_request['password'] && $_request['password'] !== '') {
          if($_request['password'] != $_request['confirmPass'])
          return response()->json('Las contraseñas no coinsiden',400);

          $_request['password'] = Hash::make($_request['password']);
        }
      }

      return $user = UserApp::EditUser($_request, $id);
  }

  protected function getUsers(Request $request){
    $database2 = Config::get('database.connections.mysql_local.database');
    $pquery = DB::table($database2.'.users as users')->where('users.trash', 0)
              ->leftJoin($database2.'.type_users as type_users', 'type_users.id', '=', 'users.role')
              ->select('users.*','type_users.name as RoleName','type_users.keyname as RoleKey','users.id as userID');

    if (!$pquery) return response()->json('Error del servidor',500);


    //Ordenamientos
    $orders = ['id','name','email'];
    //Filtrados
    $filters = ['name','email','fullname'];

    if ($request->input('rutOfUser')) {
      $name = $request->input('rutOfUser');
      $pquery->whereRaw("(users.fullname like '%$name%' OR users.email like '%$name%' OR users.username like '%$name%')");
    }

    //Procesamiento individual de items
    $Paginated = MPage::paginate($pquery, $request,10,'','users',$orders,$filters, false, null);

    return response()->json($Paginated);
  }
  protected function getAllUsers(Request $request){
    $database2 = Config::get('database.connections.mysql_local.database');
    $pquery = DB::table($database2.'.users as users')->where('users.trash', 0)
              ->leftJoin($database2.'.type_users as type_users', 'type_users.id', '=', 'users.role')
              ->select('users.*','type_users.name as RoleName','type_users.keyname as RoleKey','users.id as userID')->get();

    if (!$pquery) return response()->json('Error del servidor',500);

    return response()->json($pquery);
  }


  protected function trashUser($id){
    $user = UserApp::find($id);
    if(!$user) return response()->json('Usuario no encontrado',404);

    if($user->trash == 1) return response()->json('El usuario ya se encuentra eliminado',400);

    $user->trash = 1;
    if(!$user->save()) return response()->json('Error del servidor',500);

    return response()->json(['Usuario eliminado exitosamente'],200);
  }

  // Funciones para la app global
  protected function registerById(Request $request,$id){
      $app = Aplication::find($id);
      if (!$app) return response()->json('Aplicacion no encontrada',404);

      ConectionDB::ChangeDBToApp($app);
      return $this->register($request);
  }

  protected function editById(Request $request,$idUser,$id){
      $app = Aplication::find($id);
      if (!$app) return response()->json('Aplicacion no encontrada',404);

      ConectionDB::ChangeDBToApp($app);
      return $this->editUser($request, $idUser);
  }

  protected function getUserById(Request $request,$id){
    $app = Aplication::find($id);
    if (!$app) return response()->json('Aplicacion no encontrada',404);

    ConectionDB::ChangeDBToApp($app);
    return $this->getUsers($request);
  }

  protected function getUserByMe(Request $request){
    $user = Auth::user();
    if (!$user) return response()->json('Usuario no autenticado',401);
    return response()->json($user);
  }

  protected function trashUserById($idUser, $id){
    $app = Aplication::find($id);
    if (!$app) return response()->json('Aplicacion no encontrada',404);

    ConectionDB::ChangeDBToApp($app);
    return $this->trashUser($idUser);
  }
}
