<?php

namespace App\Http\Controllers\Controllers_local;

use Illuminate\Http\Request;
use Auth;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Validator;
use App\models_local\UserApp;
use App\Http\Controllers\Controller;
use Session;

class LoginLocal extends Controller
{
  use AuthenticatesUsers;

  public function __construct(){
    // $this->middleware('guest')->except('logout');
  }
  /*
    api/local/login
    params:
      username
      password
    header:
      app-key

  */
  public function login(Request $request){
    $user = null;
    $validator = Validator::make($request->all(), [
      'username' => 'required|string',
      'password' => 'required|string|min:4|max:48',
    ],
    [
      'username.required' => 'El usuario es obligatorio.',
      'password.required' => 'La contraseña es obligatoria.'
    ]);
    if($validator->fails()) return response()->json($validator->errors(), 400);
    $_request = $request->all();

    $username = $_request['username'];
    $password = $_request['password'];

    //Verificando si el usuario introdujo el correo o su nombre de usuario
    if(filter_var($username, FILTER_VALIDATE_EMAIL)) {
      $credentials = ['email' => $username, 'password' => $password];
    } else {
      $credentials = ['username' => $username, 'password' => $password];
    }

    if(!Auth::attempt($credentials)) return response()->json('Credenciales invalidas', 400);

    //En caso de logueo exitoso tomamos al usuario en la variable &user
    if ( Auth::check() )
      $user = Auth::user();

    if($user->trash == 1) return response()->json('Usuario no encontrado', 404);
    
    //Generando json web token
    try {
      $token = JWTAuth::attempt($credentials);
    } catch (JWTException $e) {

      return response()->json('No se pudo crear el token', 500);
    }

    session(['JWT' => $token]);
    Auth::login(UserApp::find($user['id']));

    return response()->json(['user'=>$user,'token'=>$token]);
  }

  public function logout(){
    Auth::logout();
    return response()->json('Seccion finalizada', 200);
  }
}
