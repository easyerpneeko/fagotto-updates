<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Auth;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\User;
use Session;


class LoginController extends Controller
{

  use AuthenticatesUsers;

  public function __construct(){
   // $this->middleware('guest')->except('logout');
  }

  public function login(Request $request){
    $user = null;
    $validator = Validator::make($request->all(), [
      'username' => 'required|string',
      'password' => 'required|string|min:4|max:48',
    ],
    [
      'username.required' => 'El usuario de contraseña es obligatorio.',
      'password.required' => 'El campo de contraseña es obligatorio.'
    ]);

    if($validator->fails()) {

      if(!$request->ajax()) {
        return redirect('/login')->withErrors($validator->errors());
      }
      return response()->json($validator->errors(), 400);
    }

    $_request = $request->all();

    $username = $_request['username'];
    $password = $_request['password'];

    //Verificando si el usuario introdujo el correo o su nombre de usuario
    if(filter_var($username, FILTER_VALIDATE_EMAIL)) {
      $credentials = ['email' => $username, 'password' => $password];
    } else {
      $credentials = ['username' => $username, 'password' => $password];
    }
    if(!Auth::attempt($credentials)) {

      if(!$request->ajax()) {
        return redirect('/login')->with('error','Credenciales invalidas');
      }
      return response()->json('Credenciales invalidas', 400);

    }
    //En caso de logueo exitoso tomamos al usuario en la variable &user
    if ( Auth::check() )
      $user = Auth::user();

    //Generando json web token
    try {
      $token = JWTAuth::attempt($credentials);
    } catch (JWTException $e) {

      if(!$request->ajax()) {
        return redirect('/login')->with('error','No se pudo crear el token');
      }
      return response()->json('No se pudo crear el token', 500);

    }

    session(['JWT' => $token]);
    Auth::login(User::find($user['id']));

    if(!$request->ajax()) {
      return redirect('/admin/inicio');
    }

    return response()->json(['user'=>$user,'token'=>$token]);

  }

  public function logout(){
    Auth::logout();
    return redirect('/login');
  }
  
}
