<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
  public function meUser(){
    $user = Auth::user();
    if (!$user) return response()->json('No encontro el usuario', 404);

    $me = DB::table('users')->where('id', $user->id )->first();
    if (!$me) return response()->json('Error de servidor', 500);

    unset($me->password);

    return response()->json($me);
  }
}
