<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
    $app_url = env('APP_URL');
    URL::forceRootUrl($app_url);
    $schema = explode(':', $app_url)[0];
    URL::forceScheme($schema);

Route::get('/admin/{any}', 'SpaController@index')->where('any', '.*');

Route::get('/login', 'SpaController@login')->where('any', '.*')->name('view-login');

Route::post('/login', 'Auth\LoginController@login')->name('login');
Route::get('/logout', 'Auth\LoginController@logout')->name('logout');

Route::get('/images/default', function (){
  $path = public_path('images/person.jpg');
  if (!File::exists($path)) { return response()->json('Archivo no encontrado',404); }

  $file = File::get($path);
  $type = File::mimeType($path);

  $response = Response::make($file, 200);
  $response->header("Content-Type", $type);

  return $response;
});
Route::get('/images/productDefault', function (){
  $path = public_path('images/product.png');
  if (!File::exists($path)) { return response()->json('Archivo no encontrado',404); }

  $file = File::get($path);
  $type = File::mimeType($path);

  $response = Response::make($file, 200);
  $response->header("Content-Type", $type);

  return $response;
});

Route::get('/images/uploads/products/{file}', function ($file){
  $path = storage_path('app/uploads/products/'.$file);
  if (!File::exists($path)) { return response()->json('Archivo no encontrado',404); }

  $file = File::get($path);
  $type = File::mimeType($path);

  $response = Response::make($file, 200);
  $response->header("Content-Type", $type);

  return $response;
});

Route::get('/images/uploads/avatars/{file}', function ($file){
  $path = storage_path('app/uploads/avatars/'.$file);
  if (!File::exists($path)) { return response()->json('Archivo no encontrado',404); }

  $file = File::get($path);
  $type = File::mimeType($path);

  $response = Response::make($file, 200);
  $response->header("Content-Type", $type);

  return $response;
});

Route::get('/images/uploads/images/{file}', function ($file){
  $path = storage_path('app/uploads/images/'.$file);
  if (!File::exists($path)) { return response()->json('Archivo no encontrado',404); }

  $file = File::get($path);
  $type = File::mimeType($path);

  $response = Response::make($file, 200);
  $response->header("Content-Type", $type);

  return $response;
});

Route::get('/', function() {
    if (Auth::user()) {
        return redirect('admin/inicio');
    }else{
        return redirect('login');
    }
});
