// ============ NUEVAS RUTAS DE UBER EATS API ============
// Endpoints públicos (sin middleware) para OAuth y webhooks
Route::group(['prefix' => 'uber-eats'], function () {
  // OAuth callback - debe ser accesible públicamente por Uber
  Route::get('/auth/callback', 'UberEatsController@handleOAuthCallback');
  // Webhook para recibir notificaciones en tiempo real - público para Uber
  Route::post('/webhook', 'UberEatsController@handleWebhook');
});

// Rutas protegidas de Uber Eats API
Route::group(['prefix' => 'uber-eats', 'middleware' => ['AppSecurity']], function () {
  // Test de conexión
  Route::get('/test-connection', 'UberEatsController@testConnection');
  
  // Gestión de pedidos
  Route::get('/orders', 'UberEatsController@getOrders');
  Route::get('/orders/{orderId}', 'UberEatsController@getOrder');
  
  // Acciones de pedidos
  Route::post('/orders/{orderId}/accept', 'UberEatsController@acceptOrder');
  Route::post('/orders/{orderId}/deny', 'UberEatsController@denyOrder');
  Route::post('/orders/{orderId}/cancel', 'UberEatsController@cancelOrder');
  Route::post('/orders/{orderId}/ready', 'UberEatsController@markOrderReady');
  Route::post('/orders/{orderId}/ready-time', 'UberEatsController@updateOrderReadyTime');
  
  // Gestión de menú
  Route::get('/menu', 'UberEatsController@getMenu');
  Route::post('/menu', 'UberEatsController@updateMenu');
});
