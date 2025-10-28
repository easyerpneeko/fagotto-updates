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

  public function exportAppRequestsExcel(Request $request, RequestsController $RequestsController)  {
    try {
        // Debug logging
        \Log::info('📊 Exportando pedidos - Parámetros recibidos:', $request->all());
        
        $_request = $request->all();
        $apps = Aplication::where('id', $_request['id'])->with('database')->get();
        
        if ($apps->isEmpty()) {
            \Log::error('❌ Sucursal no encontrada con ID: ' . $_request['id']);
            return response()->json(['error' => 'Sucursal no encontrada'], 404);
        }
        
        $allPedidos = [];
        $sucursalName = '';

        foreach ($apps as $app) {
            \Log::info('🔄 Procesando sucursal: ' . $app->name);
            
            $connection = new ConectionDB($app);
            $connection->ChangeDBToApp($app, $reconect = true);
            
            // Obtener pedidos directamente sin paginación
            $query = \App\models_local\Requests::orderByDesc('id');
            
            // Aplicar filtros de fecha si existen
            if (isset($_request['startDate']) && isset($_request['endDate'])) {
                $query->where('created_at', '>=', $_request['startDate'])
                      ->where('created_at', '<=', $_request['endDate']);
                \Log::info('📅 Aplicando filtro de fechas: ' . $_request['startDate'] . ' - ' . $_request['endDate']);
            }
            
            $pedidos = $query->get();
            \Log::info('📦 Pedidos encontrados: ' . $pedidos->count());
            
            // Convertir a array simple
            foreach ($pedidos as $pedido) {
                $pedidoArray = [
                    'id' => $pedido->id,
                    'created_at' => $pedido->created_at,
                    'contact_name' => $pedido->contact_name,
                    'contact_phone' => $pedido->contact_phone,
                    'status' => $pedido->status,
                    'emergency' => $pedido->emergency,
                    'despacho' => $pedido->despacho,
                    'payment_method' => $pedido->payment_method ?? $pedido->paymode,
                    'invoice_type' => $pedido->invoice_type ?? 'TICKET',
                    'subtotal' => $pedido->subtotal,
                    'iva' => $pedido->iva,
                    'price' => $pedido->price,
                    'comment' => $pedido->comment,
                    'products' => $pedido->products
                ];
                $allPedidos[] = $pedidoArray;
            }
            
            $sucursalName = $app->name;
        }

        // Validar que hay pedidos
        if (empty($allPedidos)) {
            \Log::warning('⚠️ No se encontraron pedidos para exportar');
            return response()->json(['error' => 'No se encontraron pedidos para exportar'], 404);
        }

        \Log::info('✅ Total pedidos a exportar: ' . count($allPedidos));

        // Formatear fechas para el nombre del archivo
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $fechaStr = '';
        
        if ($startDate && $endDate) {
            $fechaInicio = date('d-m-Y', strtotime($startDate));
            $fechaFin = date('d-m-Y', strtotime($endDate));
            $fechaStr = "_desde_{$fechaInicio}_hasta_{$fechaFin}";
        } elseif ($startDate) {
            $fechaInicio = date('d-m-Y', strtotime($startDate));
            $fechaStr = "_desde_{$fechaInicio}";
        } else {
            $fechaStr = "_" . date('d-m-Y');
        }

        // Limpiar nombre de sucursal para el archivo
        $sucursalClean = preg_replace('/[^A-Za-z0-9_-]/', '_', $sucursalName);
        $fileName = "Pedidos_{$sucursalClean}{$fechaStr}.xls";
        \Log::info('📁 Generando archivo: ' . $fileName);

        // Crear Excel simple
        $content = $this->createSimpleExcel($allPedidos, $sucursalName);
        
        return response($content)
            ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"; filename*=UTF-8\'\'' . rawurlencode($fileName))
            ->header('Cache-Control', 'no-cache, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
        
    } catch (\Exception $e) {
        \Log::error('❌ ERROR CRÍTICO exportando pedidos: ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString());
        return response()->json(['error' => 'Error interno: ' . $e->getMessage()], 500);
    }
  }

  private function createSimpleExcel($pedidos, $sucursalName) {
    // Procesar y agregar todos los productos para el resumen
    $resumenProductos = [];
    
    foreach ($pedidos as $pedido) {
        $productos = json_decode($pedido['products'] ?? '[]', true) ?? [];
        
        foreach ($productos as $producto) {
            $nombre = $producto['name'] ?? 'Sin nombre';
            $cantidad = floatval($producto['quantity'] ?? 0);
            
            if (isset($resumenProductos[$nombre])) {
                $resumenProductos[$nombre] += $cantidad;
            } else {
                $resumenProductos[$nombre] = $cantidad;
            }
        }
    }
    
    // Ordenar productos por cantidad descendente
    arsort($resumenProductos);
    
    // Excel con múltiples tablas
    $html = '<html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            h2 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
            h3 { color: #27ae60; margin-top: 30px; }
            table { border-collapse: collapse; width: 100%; margin-bottom: 30px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; vertical-align: top; }
            th { background-color: #3498db; color: white; font-weight: bold; font-size: 12px; }
            tr:nth-child(even) { background-color: #f8f9fa; }
            .productos-detalle { max-width: 300px; word-wrap: break-word; }
            .numero { text-align: right; }
            .centro { text-align: center; }
            .resumen-th { background-color: #27ae60 !important; }
            .total-row { background-color: #e8f5e8 !important; font-weight: bold; }
        </style>
    </head>
    <body>
        <h2>📋 Reporte Detallado de Pedidos - ' . htmlspecialchars($sucursalName) . '</h2>
        
        <!-- TABLA PRINCIPAL DE PEDIDOS -->
        <table>
            <thead>
                <tr>
                    <th>ID Pedido</th>
                    <th>Fecha y Hora</th>
                    <th>Cliente</th>
                    <th>Teléfono</th>
                    <th>Estado</th>
                    <th>Emergencia</th>
                    <th>Despacho</th>
                    <th>Método Pago</th>
                    <th>Tipo Factura</th>
                    <th>Productos Detallados</th>
                    <th>Subtotal</th>
                    <th>IVA</th>
                    <th>Total Final</th>
                    <th>Comentarios</th>
                </tr>
            </thead>
            <tbody>';

    $totalGeneral = 0;
    $totalSubtotal = 0;
    $totalIva = 0;
    $cantidadPedidos = 0;

    foreach ($pedidos as $pedido) {
        $cantidadPedidos++;
        
        // Procesar productos de forma más detallada
        $productos = json_decode($pedido['products'] ?? '[]', true) ?? [];
        $productosDetalle = '';
        
        if (!empty($productos)) {
            $productosArray = [];
            foreach ($productos as $index => $producto) {
                $nombre = $producto['name'] ?? 'Sin nombre';
                $cantidad = $producto['quantity'] ?? 0;
                $precio = isset($producto['price']) ? number_format($producto['price'], 0, ',', '.') : 'N/A';
                
                $productosArray[] = ($index + 1) . ". " . $nombre . "\n   → Cantidad: " . $cantidad . "\n   → Precio: $" . $precio;
            }
            $productosDetalle = implode("\n\n", $productosArray);
        } else {
            $productosDetalle = 'Sin productos';
        }

        // Calcular totales
        $subtotal = floatval($pedido['subtotal'] ?? 0);
        $iva = floatval($pedido['iva'] ?? 0);
        $total = floatval($pedido['price'] ?? 0);
        
        $totalSubtotal += $subtotal;
        $totalIva += $iva;
        $totalGeneral += $total;

        $html .= '<tr>
            <td class="centro">' . htmlspecialchars($pedido['id'] ?? '') . '</td>
            <td>' . (isset($pedido['created_at']) ? date('d/m/Y H:i:s', strtotime($pedido['created_at'])) : '') . '</td>
            <td>' . htmlspecialchars($pedido['contact_name'] ?? 'Sin nombre') . '</td>
            <td class="centro">' . htmlspecialchars($pedido['contact_phone'] ?? 'Sin teléfono') . '</td>
            <td class="centro">' . ucfirst($pedido['status'] ?? 'Pendiente') . '</td>
            <td class="centro">' . (($pedido['emergency'] ?? 0) ? '🚨 SÍ' : 'NO') . '</td>
            <td class="centro">' . (($pedido['despacho'] ?? 0) ? '🚚 SÍ' : 'NO') . '</td>
            <td class="centro">' . strtoupper($pedido['payment_method'] ?? 'NO ESPECIFICADO') . '</td>
            <td class="centro">' . strtoupper($pedido['invoice_type'] ?? 'TICKET') . '</td>
            <td class="productos-detalle">' . nl2br(htmlspecialchars($productosDetalle)) . '</td>
            <td class="numero">$' . number_format($subtotal, 0, ',', '.') . '</td>
            <td class="numero">$' . number_format($iva, 0, ',', '.') . '</td>
            <td class="numero">$' . number_format($total, 0, ',', '.') . '</td>
            <td>' . htmlspecialchars($pedido['comment'] ?? 'Sin comentarios') . '</td>
        </tr>';
    }

    // Fila de totales
    $html .= '<tr class="total-row">
        <td colspan="10" class="centro"><strong>TOTALES GENERALES (' . $cantidadPedidos . ' pedidos)</strong></td>
        <td class="numero"><strong>$' . number_format($totalSubtotal, 0, ',', '.') . '</strong></td>
        <td class="numero"><strong>$' . number_format($totalIva, 0, ',', '.') . '</strong></td>
        <td class="numero"><strong>$' . number_format($totalGeneral, 0, ',', '.') . '</strong></td>
        <td></td>
    </tr>';

    $html .= '</tbody>
        </table>
        
        <!-- RESUMEN DE PRODUCTOS -->
        <h3>📊 Resumen General de Productos Vendidos</h3>
        <table>
            <thead>
                <tr>
                    <th class="resumen-th">#</th>
                    <th class="resumen-th">Producto</th>
                    <th class="resumen-th">Cantidad Total</th>
                    <th class="resumen-th">Porcentaje</th>
                </tr>
            </thead>
            <tbody>';

    $totalCantidadProductos = array_sum($resumenProductos);
    $contador = 1;

    foreach ($resumenProductos as $producto => $cantidad) {
        $porcentaje = ($totalCantidadProductos > 0) ? round(($cantidad / $totalCantidadProductos) * 100, 2) : 0;
        
        $html .= '<tr>
            <td class="centro">' . $contador . '</td>
            <td>' . htmlspecialchars($producto) . '</td>
            <td class="numero">' . number_format($cantidad, 2, ',', '.') . '</td>
            <td class="numero">' . $porcentaje . '%</td>
        </tr>';
        
        $contador++;
    }

    $html .= '<tr class="total-row">
        <td colspan="2" class="centro"><strong>TOTAL PRODUCTOS</strong></td>
        <td class="numero"><strong>' . number_format($totalCantidadProductos, 2, ',', '.') . '</strong></td>
        <td class="numero"><strong>100%</strong></td>
    </tr>';

    $html .= '</tbody>
        </table>
        
        <!-- ESTADÍSTICAS ADICIONALES -->
        <h3>📈 Estadísticas del Período</h3>
        <table>
            <tbody>
                <tr>
                    <td><strong>Total de Pedidos:</strong></td>
                    <td>' . $cantidadPedidos . '</td>
                </tr>
                <tr>
                    <td><strong>Promedio por Pedido:</strong></td>
                    <td>$' . ($cantidadPedidos > 0 ? number_format($totalGeneral / $cantidadPedidos, 0, ',', '.') : '0') . '</td>
                </tr>
                <tr>
                    <td><strong>Productos Únicos:</strong></td>
                    <td>' . count($resumenProductos) . '</td>
                </tr>
                <tr>
                    <td><strong>Cantidad Total Items:</strong></td>
                    <td>' . number_format($totalCantidadProductos, 2, ',', '.') . '</td>
                </tr>
            </tbody>
        </table>
        
    </body>
    </html>';

    return $html;
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

  public function getAppSellsByDayAndHour(Request $request, ReportsController $reportsController)  {
    $id = $request->input('id');
    $apps = Aplication::where('id', $id)->with('database')->get();
    
    $appSellsByDayAndHour = [];

    foreach ($apps as $app) {
        $connection = new ConectionDB($app); // Suponiendo que $app contiene la información de la base de datos
        $connection->ChangeDBToApp($app, $reconect = true); // Reconectar a la base de datos

        $heatmapData = $reportsController->getSellsByDayAndHour($request); // Llamar al método del controlador ReportsController
        
        $appSellsByDayAndHour[$app->name] = $heatmapData; // Guardar los datos de heatmap en un array asociativo con el nombre de la aplicación
    }
    
    return response()->json($appSellsByDayAndHour);
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

    //Top Productos Más Vendidos
    public function getAppTopProducts(Request $request, ReportsController $ReportsController)  {
      $_request = $request->all();
      $apps = Aplication::where('id', $_request['id'])->with('database')->get();
      
      $appTopProducts = [];

      foreach ($apps as $app) {
          $connection = new ConectionDB($app);
          $connection->ChangeDBToApp($app, $reconect = true);
          
          // Obtener productos más vendidos usando la lógica ya existente
          $topProducts = $ReportsController->getTopSells($request);
          
          $appTopProducts[$app->name] = $topProducts;
      }

      return response()->json($appTopProducts);
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