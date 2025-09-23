<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Helpers\AddTimeHelper;
use Illuminate\Http\Request;
use App\Helpers\ConectionDB;
use App\Helpers\CurrentApp;
use App\Helpers\MPage;
use App\Aplication;
use App\DataBase;
use App\Client;
use DateTime;
use Artisan;
use App\Http\Controllers\Controllers_local\ReportsController;
use App\Http\Controllers\Controllers_local\RequestsController;
use App\Http\Controllers\Controllers_local\ProductsController;
use App\Http\Controllers\Controllers_local\CategoriesController;
use App\Http\Controllers\Controllers_local\SellsController;

//Main
use App\Modules;
use App\SubModules;

//Settings
use App\Settings_modules;
use App\Settings_submodules;

use Carbon\Carbon;

//
use Illuminate\Support\Facades\Log;

class ReportAplicationController extends Controller {

  public function getApp(Request $request) {
    $app = Aplication::where('id', $request->input('id'))->with('database')->get();
    return response()->json($app);
  }

  public function getApps() {
    $currentApp = CurrentApp::App();
    $apps = Aplication::where('client',$currentApp->client)->with('database')->get();
    return response()->json($apps);
  }

  public function getAppCounters(Request $request, ReportsController $reportsController)  {
    $_request = $request->all();
    $apps = Aplication::where('id', $_request['id'])->with('database')->get();
    
    $appCounters = [];

    foreach ($apps as $app) {
        $connection = new ConectionDB($app); // Suponiendo que $app contiene la información de la base de datos
        $connection->ChangeDBToApp($app, $reconect = true); // Reconectar a la base de datos
        $counters = $reportsController->getCounters($request); // Llamar al método getCounters del controlador ReportsController pasando la request como parámetro
        
        $appCounters[$app->name] = $counters; // Guardar los datos de contadores en un array asociativo con el nombre de la aplicación
    }

    return response()->json($appCounters);
  }

  public function getAppRequests(Request $request, RequestsController $RequestsController)  {
    $_request = $request->all();
    $apps = Aplication::where('id', $_request['id'])->with('database')->get();
    
    $appRequests = [];

    foreach ($apps as $app) {
        $connection = new ConectionDB($app); // Suponiendo que $app contiene la información de la base de datos
        $connection->ChangeDBToApp($app, $reconect = true); // Reconectar a la base de datos
        $requests = $RequestsController->index($request); // Llamar al método getCounters del controlador ReportsController pasando la request como parámetro
        
        $appRequests[$app->name] = $requests; // Guardar los datos de contadores en un array asociativo con el nombre de la aplicación
    }

    return response()->json($appRequests);
  }

  public function getAppTopSells(Request $request, ReportsController $reportsController)  {
    $_request = $request->all();
    $apps = Aplication::where('id', $_request['id'])->with('database')->get();
    
    $appTopSells = [];

    foreach ($apps as $app) {
        $connection = new ConectionDB($app); // Suponiendo que $app contiene la información de la base de datos
        $connection->ChangeDBToApp($app, $reconect = true); // Reconectar a la base de datos

        $topSells = $reportsController->getTopSells($request); // Llamar al método getTopSells del controlador ReportsController pasando la aplicación como parámetro
        
        $appTopSells[$app->name] = $topSells; // Guardar los datos de contadores en un array asociativo con el nombre de la aplicación
    }

    return response()->json($appTopSells);
  }

  public function getAppSellsByHour(Request $request, ReportsController $reportsController)  {
    $id = $request->input('id');
    $apps = Aplication::where('id', $id)->with('database')->get();
    
    $appSellsByHour = [];

    foreach ($apps as $app) {
        $connection = new ConectionDB($app); // Suponiendo que $app contiene la información de la base de datos
        $connection->ChangeDBToApp($app, $reconect = true); // Reconectar a la base de datos

        $sells = $reportsController->getSellsByHour($request); // Llamar al método getCounters del controlador ReportsController pasando la aplicación como parámetro
        
        $appSellsByHour[$app->name] = $sells; // Guardar los datos de contadores en un array asociativo con el nombre de la aplicación
    }
    
    return response()->json($appSellsByHour);
  }

  public function getAppSells(Request $request, ReportsController $reportsController)  {
    $id = $request->input('id');
    $apps = Aplication::where('id', $id)->with('database')->get();
    
    $appTopSells = [];

    foreach ($apps as $app) {
        $connection = new ConectionDB($app); // Suponiendo que $app contiene la información de la base de datos
        $connection->ChangeDBToApp($app, $reconect = true); // Reconectar a la base de datos

        $topSells = $reportsController->getSells($request); // Llamar al método getCounters del controlador ReportsController pasando la aplicación como parámetro
        
        $appTopSells[$app->name] = $topSells; // Guardar los datos de contadores en un array asociativo con el nombre de la aplicación
    }

    return response()->json($appTopSells);
  } 

  public function getAppById(Request $request, $id){
    $app = Aplication::find($id);
    $config = $app->getApp($request->has('slim'));
    return response()->json($config);
  }


  // REPOSTERIA
  public function getAppRequestsReposteria(Request $request, RequestsController $RequestsController)  {
    $_request = $request->all();
    $apps = Aplication::where('id', $_request['id'])->with('database')->get();
    
    $appRequests = [];

    foreach ($apps as $app) {
        $connection = new ConectionDB($app); // Suponiendo que $app contiene la información de la base de datos
        $connection->ChangeDBToApp($app, $reconect = true); // Reconectar a la base de datos
        $requests = $RequestsController->indexReposteria($request); // Llamar al método getCounters del controlador ReportsController pasando la request como parámetro
        
        $appRequests[$app->name] = $requests; // Guardar los datos de contadores en un array asociativo con el nombre de la aplicación
    }

    return response()->json($appRequests);
  }

  //Productos
  public function getAppProducts(Request $request, ProductsController $ProductsController)  {
    $_request = $request->all();
    $apps = Aplication::where('id', $_request['id'])->with('database')->get();
    
    $appProducts = [];

    foreach ($apps as $app) {
        $connection = new ConectionDB($app); // Suponiendo que $app contiene la información de la base de datos
        $connection->ChangeDBToApp($app, $reconect = true); // Reconectar a la base de datos
        $products = $ProductsController->getProductsExtended($request); // Llamar al método getCounters del controlador ReportsController pasando la request como parámetro
        
        $appProducts[$app->name] = $products; // Guardar los datos de contadores en un array asociativo con el nombre de la aplicación
    }

    return response()->json($appProducts);
  }

  //Categorias
  public function getAppCategories(Request $request, CategoriesController $CategoriesController)  {
    $_request = $request->all();
    $apps = Aplication::where('id', $_request['id'])->with('database')->get();
    
    $appCategories = [];

    foreach ($apps as $app) {
        $connection = new ConectionDB($app); // Suponiendo que $app contiene la información de la base de datos
        $connection->ChangeDBToApp($app, $reconect = true); // Reconectar a la base de datos
        $categories = $CategoriesController->getCategories($request); // Llamar al método getCounters del controlador ReportsController pasando la request como parámetro
        
        $appCategories[$app->name] = $categories; // Guardar los datos de contadores en un array asociativo con el nombre de la aplicación
    }

    return response()->json($appCategories);
  }

  //Ventas
  public function getAppVentas(Request $request, SellsController $SellsController)  {
      $_request = $request->all();
      $apps = Aplication::where('id', $_request['id'])->with('database')->get();
      
      $appSells = [];
  
      foreach ($apps as $app) {
          $connection = new ConectionDB($app); // Suponiendo que $app contiene la información de la base de datos
          $connection->ChangeDBToApp($app, $reconect = true); // Reconectar a la base de datos
          $sells = $SellsController->getSellsExtended($request); // Llamar al método getCounters del controlador ReportsController pasando la request como parámetro
          
          $appSells[$app->name] = $sells; // Guardar los datos de contadores en un array asociativo con el nombre de la aplicación
      }
  
      return response()->json($appSells);
    }

    /**
     * Obtener salsas desde la base de datos EasyERP
     */
    public function getSalsasEasyERP(Request $request)
    {
        try {
            // Lista de salsas específicas que queremos consultar
            $salsasObjetivo = [
                'ALFREDO',
                'BOLOÑESA', 
                'CAMARON',
                'CHAMPIÑON',
                'PESTO',
                'CREMA POLLO MOSTAZA'
            ];

            // Conectar a la base de datos easyerp
            $productos = DB::connection('easyerp')
                ->table('products')
                ->select('id', 'name', 'active', 'trash')
                ->where(function($query) use ($salsasObjetivo) {
                    foreach ($salsasObjetivo as $salsa) {
                        $query->orWhere('name', 'LIKE', '%' . $salsa . '%');
                    }
                })
                ->get();

            return response()->json([
                'success' => true,
                'data' => $productos,
                'message' => 'Salsas obtenidas correctamente',
                'total' => $productos->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al consultar salsas: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Actualizar estado de un producto en EasyERP (active o trash)
     */
    public function actualizarProductoEasyERP(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer',
                'campo' => 'required|string|in:active,trash',
                'valor' => 'required|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $id = $request->input('id');
            $campo = $request->input('campo');
            $valor = $request->input('valor') ? 1 : 0;

            // Actualizar en la base de datos easyerp
            $updated = DB::connection('easyerp')
                ->table('products')
                ->where('id', $id)
                ->update([$campo => $valor]);

            if ($updated) {
                return response()->json([
                    'success' => true,
                    'message' => "Campo {$campo} actualizado correctamente",
                    'data' => [
                        'id' => $id,
                        'campo' => $campo,
                        'valor' => $valor
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo actualizar el producto'
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar producto: ' . $e->getMessage()
            ], 500);
        }
    }
}