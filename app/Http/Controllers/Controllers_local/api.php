<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
use App\Helpers\CurrentApp;
use App\Aplication;

Route::group(['middleware' => ['ApiSecurity']], function () {
  Route::post('/masive/consult-dte', 'SIIController@masiveConsultDTE');
  Route::group(['middleware' => ['AppSecurity']], function () {
    Route::get('/report', 'ApiReportsController@getReport');
    Route::get('/report/counters', 'ApiReportsController@getCounters');
  });
});


Route::get('/getAppByIdAlt/{id}', 'AplicationController@getAppById');
/*Route::get('/codeTest', function() {
  session(['app-current' => Aplication::find(1)]);
  session(['app-config' => CurrentApp::App()->getApp()]);



  var_dump(CurrentApp::ConfStr('modulos.ventas.submodulos.factura.ajustes.permitir_factura_electronica'));exit();
});*/
//Middleware para el login del master ---------------Rutas de la aplicacion global---------------//
Route::group(['middleware' => ['JwtMiddleware']], function () {

  Route::get('/getAppById/{id}', 'AplicationController@getAppById');
  Route::get('/aplications/all', 'AplicationController@getAllAplications');

  // Feeds del admin
  Route::post('/feed', 'FeedController@store');
  Route::put('/feed/{id}', 'FeedController@update');
  Route::delete('/feed/{id}', 'FeedController@remove');
  Route::get('/feeds', 'FeedController@index');


  // Folios
  Route::post('/folios/{id}', 'SIIController@readXML');
  Route::get('/folios/{id}', 'SIIController@getFolios');

  Route::get('/me', 'UserController@meUser');

  Route::get('/modules/of/app/{id}', 'ModulesController@getModulesAplication');

  //Te da los submodulos a cambio del ID de la relacion modulo-aplicacion
  Route::get('/submodules/of/rel/{id}', 'SubmodulesController@getSubmodulesOfModuleApp');
  //Te da los submodulos a cambio del ID del modulo y del ID de la aplicacion
  Route::get('/submodules/of/module/app/{moduleid}/{appid}', 'ModulesController@getSubmodulesOfApp');

  //Modifica el ajuste de un modulo una aplicacion en base a su ID de ajuste de relacion
  Route::put('/module/setting/{id}', 'SettingsController@putSettingOfModule');
  //Modifica el ajuste de de un submodulo una aplicacion en base a su ID de ajuste de relacion
  Route::put('/submodule/setting/{id}', 'SubSettingsController@putSettingOfSubModule');

  Route::post('/app/{id}/addModule', 'AplicationController@addModule');
  Route::post('/app/{id}/addSubModule', 'AplicationController@addSubModule');

  //Ejecutar migraciones del modulo-app
  Route::post('/module/migrate/{relid}', 'ModulesController@executeMigration');

  //Ejecutar migraciones del submodulo-app
  Route::post('/submodule/migrate/{relid}', 'SubmodulesController@executeMigration');

  //Actualizar el modulo-app
  Route::post('/module/update/{relid}', 'ModulesController@updateModule');

  //Actualizar el submodulo-app
  Route::post('/submodule/update/{relid}', 'SubmodulesController@updateModule');

  //Modificar las .env de una aplicacion
  Route::put('/app/{id}/envs', 'AplicationController@modifyEnvs');

  Route::get('/list/submodule', 'SubmodulesController@getSubModulesList');
  Route::get('/list/module', 'ModulesController@getModulesList');

  //Desinstalar modulo
  Route::delete('/modules/of/rel/{relid}', 'ModulesController@uninstallModule');

  //Desinstalar submodulo
  Route::delete('/submodules/of/rel/rel/{relid}', 'SubmodulesController@uninstallSubModule');

  // traer aplicaciones y clientes
  Route::get('/clients', 'ClientController@getClients');
  Route::get('/aplications', 'AplicationController@getAplications');

  Route::get('/dashboard', 'AplicationController@getDashboard');

  Route::get('/clients', 'ClientController@getClients');

  // Rutas de los clientes
  Route::get('/client/{id}', 'ClientController@getClientAplications');
  Route::get('/rut/{rut}', 'ClientController@searchUser');

  // Rutas de las aplicaciones
  Route::post('/aplication', 'AplicationController@newAplication');
  Route::put('/aplication/{id}', 'AplicationController@addTime');

  // Rutas de a mensualidad
  Route::put('/cutService/{id}', 'AplicationController@cutService');
  Route::put('/activeService/{id}', 'AplicationController@activeService');

  // Rutas de modulos
  Route::get('/modules', 'ModulesController@getModules');
  Route::get('/module/{id}', 'ModulesController@getModulesAplication');

  // Rutas de submodulos
  Route::get('/submodule/{id}', 'SubmodulesController@getSubmodules');

  // manejo de usuarios local global
  Route::post('/global/userCreate/{id}', 'Controllers_local\UserLocal@registerById');
  Route::put('/global/useredit/{idUser}/{id}', 'Controllers_local\UserLocal@editById');
  Route::get('/global/users/{id}', 'Controllers_local\UserLocal@getUserById');
  Route::delete('/global/user/{idUser}/delete/{id}', 'Controllers_local\UserLocal@trashUserById');

  // manejo de tipos de usuarios local global
  Route::post('/global/typeUserCreate/{id}', 'Controllers_local\typeUserController@registerTypeUserById');
  Route::put('/global/typeUser/{idApp}/{id}', 'Controllers_local\typeUserController@editUserById');
  Route::get('/global/role/{id}', 'Controllers_local\typeUserController@getRolesById');
  Route::delete('/global/role/{idRole}/delete/{id}', 'Controllers_local\typeUserController@trashRoleById');

});




//Middleware para el serial de seguridad ---------------Rutas de la aplicacion local---------------//

Route::group(['middleware' => ['AppSecurity']], function () {

  Route::get('/getApp', 'AplicationController@getApp');

  Route::get('/getEnvs', 'AplicationController@getEnvs');

  Route::post('/local/login', 'Controllers_local\LoginLocal@login');


  Route::group(['middleware' => ['JwtMiddleware']], function () {

      Route::get('/local/feeds', 'FeedController@getMyFeeds');

      Route::group(['middleware' => ['HavePermission:monto_inicial']], function () {
        Route::get('/init/money', 'AplicationController@getInitMoney');
        Route::post('/init/money', 'AplicationController@setInitMoney');
      });

      // Obtenerme a mi mismo (yo)
      Route::get('/local/me', 'Controllers_local\UserLocal@getUserByMe');

      // Manejo de usuarios
      Route::group(['middleware' => ['HavePermission:usuarios_gestion']], function () {
        Route::post('/local/register', 'Controllers_local\UserLocal@register');
        Route::put('/local/user/{id}', 'Controllers_local\UserLocal@editUser');
        Route::put('/local/user/{id}/delete', 'Controllers_local\UserLocal@trashUser');
      });
      // Obtener usuarios
      Route::group(['middleware' => ['HavePermission:usuarios_obtener']], function () {
        Route::get('/local/users', 'Controllers_local\UserLocal@getUsers');
        Route::get('/local/all/users', 'Controllers_local\UserLocal@getAllUsers');
      });
      // Tipos de usuario CRUD [El permiso mas peligroso de todos]
      Route::group(['middleware' => ['HavePermission:tipos_usuarios_gestion']], function () {
        Route::get('/local/typeUser', 'Controllers_local\typeUserController@getRoles');
        Route::post('/local/typeUser', 'Controllers_local\typeUserController@newTypeUser');
        Route::put('/local/typeUser/{id}', 'Controllers_local\typeUserController@editTypeUser');
        Route::put('/local/typeUser/{id}/delete', 'Controllers_local\typeUserController@trashRole');
      });

      //Rutas de productos
      Route::group(['middleware' => ['Config:modulos.productos']], function () {

        // Crud de productos
        Route::group(['middleware' => ['HavePermission:productos_obtener']], function () {
          Route::get('/local/products', 'Controllers_local\ProductsController@getProducts');
          Route::get('/local/products/sell', 'Controllers_local\ProductsController@getProductsOfSell');
        });
        Route::group(['middleware' => ['HavePermission:crear_productos_nueva_venta']], function () {
          Route::post('/local/product/new/sell', 'Controllers_local\ProductsController@newProduct');
        });
        Route::group(['middleware' => ['HavePermission:productos_gestion']], function () {

          Route::post('/local/product', 'Controllers_local\ProductsController@newProduct');
          Route::post('/local/product/import', 'Controllers_local\ProductsController@import');
          Route::group(['middleware' => ['Config:modulos.productos.ajustes.donwload_inventory']], function () {
            Route::get('/local/product/export', 'Controllers_local\ProductsController@export');
          });
          Route::put('/local/product/{id}', 'Controllers_local\ProductsController@editProduct');
          Route::put('/local/product/{id}/delete', 'Controllers_local\ProductsController@trashProduct');


          Route::group(['middleware' => ['Config:modulos.productos.submodulos.categorias']], function () {
            Route::post('/local/category', 'Controllers_local\CategoriesController@newCategory');
            Route::put('/local/category/{id}', 'Controllers_local\CategoriesController@editCategory');
            Route::get('/local/categories', 'Controllers_local\CategoriesController@getCategories');
            Route::put('/local/category/{id}/delete', 'Controllers_local\CategoriesController@trashCategory');
            Route::get('/local/category/products/{id}', 'Controllers_local\CategoriesController@getProducts');
          });

        });


        // Modulo de ventas ===========================================
        Route::group(['middleware' => ['Config:modulos.ventas']], function () {

          Route::group(['middleware' => ['HavePermission:eliminar_venta']], function (){
            Route::delete('/local/sell/{id}', 'Controllers_local\SellsController@removeSell');
          });

          Route::group(['middleware' => ['HavePermission:getionar_tickets']], function () {
            Route::group(['middleware' => ['Config:modulos.ventas.submodulos.ticket']], function () {
              // Ticket
              Route::post('/local/ticket/app','Controllers_local\OrderController@newOrderApp');
              Route::post('/local/ticket', 'Controllers_local\OrderController@newOrder');
              Route::put('/local/ticket/{id}', 'Controllers_local\OrderController@editOrder');
              Route::get('/local/order/{code}', 'Controllers_local\OrderController@getOrder');
            });
          });
          Route::group(['middleware' => ['HavePermission:gestionar_ventas']], function () {

            Route::get('/local/sells', 'Controllers_local\SellsController@getSells');
            Route::get('/local/sell/{id}', 'Controllers_local\SellsController@getSell');
            Route::get('/local/sell/{id}/pdf', 'Controllers_local\SellsController@printPDF');

            Route::group(['middleware' => ['Config:modulos.ventas.submodulos.sii']], function () {
              // Consulta DTE
              Route::get('/local/sell/dte/{sellId}', 'SIIController@consultDTE');
              // Factura
              Route::post('/local/sell/factura/{sellId}', 'SIIController@processFactura');
              // Boleta
              Route::post('/local/sell/boleta/{sellId}', 'SIIController@processBoleta');
              // Nota de credito
              Route::put('/local/sell/nota_de_credito/factura/{sellId}', 'SIIController@notaDeCreditoFactura');
              Route::put('/local/sell/nota_de_credito/boleta/{sellId}', 'SIIController@notaDeCreditoBoleta');
            });


            Route::group(['middleware' => ['HavePermission:crear_venta']], function () {
              Route::post('/local/sell', 'Controllers_local\SellsController@newSell');
              Route::post('/local/guia', 'Controllers_local\GuiaController@nuevaGuia');

              Route::post('/local/fastSell', 'Controllers_local\SellsController@fastSell');
              Route::group(['middleware' => ['Config:modulos.ventas.submodulos.clientes']], function () {
                Route::get('/local/client/{rut}', 'Controllers_local\SellsController@getClient');
                Route::get('/local/retrieve-client/{rut}', 'Controllers_local\ClientsController@retrieveClient');
                Route::put('/local/directly-client/{id}', 'Controllers_local\ClientsController@editClient');
                Route::post('/local/client/{sell}', 'Controllers_local\SellsController@editClient');
              });
            });

          });

          Route::group(['middleware' => ['Config:modulos.ventas.submodulos.reporte']], function () {
            // rutas de ventas
            //Route::get('/local/sell/{id}', 'Controllers_local\SellsController@getSell');
            //Route::get('/local/sell/{id}/pdf', 'Controllers_local\SellsController@printPDF');

            // rutas de reportes
            Route::get('/local/report/sells', 'Controllers_local\ReportsController@getSells');
            Route::get('/local/report/waiter', 'Controllers_local\ReportsController@getOneWaiter');
            Route::get('/local/report/counters', 'Controllers_local\ReportsController@getCounters');
            Route::post('/local/reports/pdf', 'Controllers_local\ReportsController@printReport');
          });

          // Submodulo de gastos del dia
          Route::group(['middleware' => ['Config:modulos.ventas.submodulos.expenses_day']], function () {
            Route::group(['middleware' => ['HavePermission:gestionar_gastos']], function () {
              Route::post('/local/expense', 'Controllers_local\ExpensesController@store');
              Route::delete('/local/expense/{id}', 'Controllers_local\ExpensesController@remove');
            });
            Route::get('/local/expenses', 'Controllers_local\ExpensesController@index');
          });
        });

        // Modulo de cafeteria ===========================================
        Route::group(['middleware' => ['Config:modulos.cafeteria']], function () {
          // Cafeteria
          Route::get('/local/cafeteria', 'Controllers_local\CafeteriaController@index');
          // Mesas ocupadas
          Route::get('/local/boards/actives', 'Controllers_local\CafeteriaController@getBoardsActives');
          // Ordenes activas (Principalmente para el modo Garzon)
          Route::get('/local/orders/actives', 'Controllers_local\CafeteriaController@getActiveOrders');
          // Mesa
          Route::get('/local/board/{id}', 'Controllers_local\CafeteriaController@getBoard');
          // Todos los meseros
          Route::get('/local/all/waiters', 'Controllers_local\CafeteriaController@getAllWaiters');
          // Cambiar mesero
          Route::put('/local/change/waiter/{id}', 'Controllers_local\CafeteriaController@changeWaiter');
          // Cambiar mesa
          Route::put('/local/change/board/{id}', 'Controllers_local\CafeteriaController@changeBoard');
          // Eliminar producto
          Route::put('/local/remove/product/{id}', 'Controllers_local\OrderController@removeProduct');
          // other
          Route::get('/local/print/order/{id}', 'Controllers_local\OrderController@printOrderTotal');

          // Waiters
          Route::group(['middleware' => ['HavePermission:gestionar_meseros']], function (){
            Route::post('/local/waiter', 'Controllers_local\WaitersController@store');
            Route::put('/local/waiter/{id}', 'Controllers_local\WaitersController@update');
            Route::delete('/local/waiter/{id}', 'Controllers_local\WaitersController@remove');
          });
          Route::group(['middleware' => ['HavePermission:obtener_meseros']], function (){
            Route::get('/local/waiters', 'Controllers_local\WaitersController@index');
          });
          Route::group(['middleware' => ['Config:modulos.cafeteria.submodulos.additions_waiter']], function () {
            Route::post('/local/addtion/waiter', 'Controllers_local\WaitersController@newAddtion');
          });

          // Boards
          Route::group(['middleware' => ['HavePermission:gestionar_mesas']], function (){
            Route::post('/local/board', 'Controllers_local\BoardsController@store');
            Route::put('/local/board/{id}', 'Controllers_local\BoardsController@update');
            Route::delete('/local/board/{id}', 'Controllers_local\BoardsController@remove');
          });
          Route::group(['middleware' => ['HavePermission:obtener_mesas']], function (){
            Route::get('/local/boards', 'Controllers_local\BoardsController@index');
            Route::get('/local/boards/all', 'Controllers_local\BoardsController@getAllBoards');
          });

          Route::group(['middleware' => ['Config:modulos.cafeteria.submodulos.modo_cocina']], function () {
            Route::get('/local/cafeteria/modo_cocina/orders_kitchens', 'Controllers_local\Cafeteria\OrderKitchenController@getPendingOrders');
            Route::post('/local/cafeteria/modo_cocina/orders_kitchens/{id}', 'Controllers_local\Cafeteria\OrderKitchenController@markAsState');
          });
        });

        // Modulo de ordenes de clientes ===========================================
        Route::group(['middleware' => ['Config:modulos.client_orders']], function () {
          // [Generalidades]
          Route::group(['middleware' => ['HavePermission:retrieve_client_orders']], function () {
            Route::get('/local/client-orders/core/{id}', 'Controllers_local\ClientOrders\ClientOrdersController@find');
            //Route::get('/local/client-orders/paginate', 'Controllers_local\ClientOrders\ClientOrdersController@paginate');
          });

          // [Submodulo] Reparacion de dispositivos moviles
          Route::group(['middleware' => ['Config:modulos.client_orders.submodulos.co_mobile_device']], function () {
            Route::post('/local/client-orders/mobile-devices', 'Controllers_local\ClientOrders\PhoneRepairOrderController@create')->middleware(['HavePermission:create_client_order']);
            //Route::update('/local/mobile-devices', 'Controllers_local\ClientOrders\PhoneRepairOrderController@update')->middleware(['HavePermission:update_client_order']);
            Route::get('/local/client-orders/mobile-devices/fetch-device/{imei}',
              'Controllers_local\ClientOrders\PhoneRepairOrderController@fetchDeviceByImei'
            );
            Route::group(['middleware' => ['HavePermission:retrieve_client_orders']], function () {
              Route::get('/local/client-orders/mobile-devices/paginate', 'Controllers_local\ClientOrders\PhoneRepairOrderController@paginate');
              Route::get('/local/client-orders/mobile-devices/print/{id}', 'Controllers_local\ClientOrders\PhoneRepairOrderController@print');
              Route::get('/local/client-orders/mobile-devices/{id}', 'Controllers_local\ClientOrders\PhoneRepairOrderController@find');
            });
          });

          

        });
      });

  });

});


Route::group(['middleware' => ['AppSecurity','JwtMiddleware']], function () {
  Route::post('broadcasting/auth',function (Request $request){ 
    $pusher = new Pusher\Pusher(env('PUSHER_APP_KEY'),env('PUSHER_APP_SECRET'), env('PUSHER_APP_ID')); return $pusher->socket_auth($request->request->get('channel_name'),$request->request->get('socket_id')); 
  });
});

