<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes para Uber Eats (Actualizado)
|--------------------------------------------------------------------------
*/

// Rutas sin middleware para OAuth y webhooks externos
Route::group(['prefix' => 'uber-eats'], function () {
    // OAuth routes (sin middleware para acceso externo)
    Route::get('auth', 'UberEatsController@redirectToUber');
    Route::get('auth/callback', 'UberEatsController@handleCallback');
    
    // Webhook route (sin middleware para acceso de Uber)
    Route::post('webhook', 'UberEatsController@handleWebhook');
    
    // Test connection (sin middleware para verificación inicial)
    Route::get('test-connection', 'UberEatsController@testConnection');
});

// Rutas con middleware para uso interno de la aplicación
Route::group(['prefix' => 'uber-eats', 'middleware' => 'AppSecurity'], function () {
    // Orders management
    Route::get('orders', 'UberEatsController@getOrders');
    Route::get('orders/{orderId}', 'UberEatsController@getOrder');
    Route::post('orders/{orderId}/accept', 'UberEatsController@acceptOrder');
    Route::post('orders/{orderId}/deny', 'UberEatsController@denyOrder');
    Route::post('orders/{orderId}/ready', 'UberEatsController@markOrderReady');
    Route::post('orders/{orderId}/ready-time', 'UberEatsController@updateOrderReadyTime');
    Route::post('orders/{orderId}/cancel', 'UberEatsController@cancelOrder'); // Cambiado de denyOrder a cancelOrder para órdenes activas
    
    // Menu management
    Route::get('menu', 'UberEatsController@getMenu');
    Route::put('menu', 'UberEatsController@updateMenu');
    
    // Store status
    Route::get('store/status', 'UberEatsController@getStoreStatus');
    Route::post('store/status', 'UberEatsController@updateStoreStatus');
});
