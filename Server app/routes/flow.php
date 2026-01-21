<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Flow Payment Gateway Routes
|--------------------------------------------------------------------------
|
| Rutas separadas para Flow sin pasar por middleware api
| Solo usa FlowCors para evitar conflictos
|
*/

Route::group(['middleware' => ['FlowCors']], function () {
    Route::post('/api/local/flow/create-payment', 'Controllers_local\FlowController@createPayment');
    Route::post('/api/local/flow/confirm', 'Controllers_local\FlowController@confirmPayment');
    Route::get('/api/local/flow/return', 'Controllers_local\FlowController@returnPayment');
});
