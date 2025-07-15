<?php

namespace App\models_local;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Auth;

class UserApp extends Authenticatable implements JWTSubject
{
    use Notifiable;
    protected $connection = 'mysql_local';
    protected $table = 'users';

    protected $fillable = [
        'fullname',
        'username',
        // 'email',
        'password',
        'role',
        'active',
        'avatar',
        'trash'
    ];
    protected $hidden = ['password'];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }

    public static function NewUser($request){
      $keysAllow = [
        'fullname',
        'username',
        'email',
        'password',
        'role',
        'avatar'
      ];
      $itemToSave = [];
      if (isset($request['avatar'])) {
        $uploadedFile = $request['avatar'];
        $filename = time().$uploadedFile->getClientOriginalName();

        $result = Storage::disk('local')->putFileAs(
          'uploads/avatars',
          $uploadedFile,
          $filename
        );
        // asignando la ruta de la imagen que se guardara en la base de datos
        $request['avatar'] = $result;
      }else {
        $request['avatar'] = 'default';
      }
      foreach ($keysAllow as $key){
        if (isset($request[$key])) $itemToSave[$key] = $request[$key];
        else $itemToSave[$key] = null;
      }

      return UserApp::create($itemToSave);
    }

    public static function EditUser($request, $id){
      $User = UserApp::find($id);
      if(!$User) return response()->json('Usuario no encontrado',404);

      $keysAllow = [
        'fullname',
        'username',
        'email',
        'password',
        'role',
        'avatar'
      ];
      if (isset($request['avatar']) && !is_string($request['avatar'])) {
        $uploadedFile = $request['avatar'];
        $filename = time().$uploadedFile->getClientOriginalName();

        $result = Storage::disk('local')->putFileAs(
          'uploads/avatars',
          $uploadedFile,
          $filename
        );
        // asignando la ruta de la imagen que se guardara en la base de datos
        $request['avatar'] = $result;
      }
      foreach ($keysAllow as $key)
          if (isset($request[$key]))
              $User->{$key} = $request[$key];

      if(!$User->save()) return response()->json('Database error',500);

      return response()->json('Usuario editado exitosamente',200);
    }

    public static function createUser($fullname,$username,$email,$pass,$role) {
      $user = new Self;
      $user->fullname = $fullname;
      $user->username = $username;
      $user->email = ($email === null) ? 'admin@example.com' : $email;
      $user->password = Hash::make($pass);
      $user->active = 1;
      $user->trash = 0;
      $user->role = 1;
      $user->avatar = 'default';
      if ($user->save()) return $user;
      return false;
    }

    public function devolution()
    {
      return $this->hasMany(Devolution::class);
    }
}
