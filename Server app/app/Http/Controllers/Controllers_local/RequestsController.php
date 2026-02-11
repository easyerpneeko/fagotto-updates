<?php

namespace App\Http\Controllers\Controllers_local;

use Illuminate\Http\Request;
//Models
use App\models_local\Requests;
use App\models_local\RequestsReposteria;
use App\models_local\Payment;
use App\models_local\PaymentReposteria;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use App\Product;
use App\ProductChange;

use App\models_local\Client;
use App\models_local\Order;
use App\models_local\Folio;
use App\models_local\Ingredient;
// Helpers
use App\Helpers\MPage;
use App\Helpers\CurrentApp;
use Config;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Helpers\ConectionDB;
use App\Helpers\PaymentMethodHelper;
use App\Aplication;
use Auth;
use Dompdf\Dompdf;
use Carbon\Carbon;


// Controllers
use App\Http\Controllers\SIIController;

class RequestsController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'contact_name' => 'required|string|max:70',
            'contact_phone' => 'required|string|max:20',
            'paymode' => 'required',
            'payment_method' => 'nullable|string|in:contado,credito,efectivo,debito,transferencia,cheque,banco,amipass,multicaja,edenred,convenio_empresa,sodexo,rappi,junaeb,uber,pedidos_ya,pluxee,banco_chile_20,fluxi',
            'invoice_type' => 'nullable|string|in:ticket,boleta',
            'voucher' => 'nullable|file',
            'status' => 'required',
            'products' => 'required|string',
            'comment' => 'required|string',
            'price' => '',
            'subtotal' => '',
            'iva' => '',
            'emergency' => '',
            'despacho' => '',
            'app_id' => 'required|integer',
        ]);

        $products = json_decode($validatedData['products'], true);
        if (is_array($products)) {
            foreach ($products as $productData) {
                $productId = $productData['id'];
                $quantity = $productData['quantity'];
                $vasosPedido = $productData['vasos'];

                $product = Product::find($productId);
                if ($product) {
                    $stockToSubtract = $quantity;

                    if ($product->category == 2) {
                        $stockToSubtract = $vasosPedido / $product->vasos;
                    }

                    // Verificar min_stock
                    if ($product->stock - $stockToSubtract < $product->min_stock) {
                        return response()->json(['message' => 'No se puede realizar el pedido. No hay stock de :' . $product->name], 400);
                    }
                } else {
                    return response()->json(['message' => 'Producto no encontrado: ' . $productId], 404);
                }
            }
        } else {
            return response()->json(['message' => 'Error al procesar los productos.'], 400);
        }

        $newRequest = new Requests();
        $newRequest->contact_name = $validatedData['contact_name'];
        $newRequest->contact_phone = $validatedData['contact_phone'];
        $newRequest->paymode = $validatedData['paymode'];
        $newRequest->payment_method = $validatedData['payment_method'] ?? 'contado';
        $newRequest->invoice_type = $validatedData['invoice_type'] ?? 'ticket';
        $newRequest->price = $validatedData['price'];
        $newRequest->subtotal = $validatedData['subtotal'];
        $newRequest->iva = $validatedData['iva'];
        $newRequest->emergency = isset($validatedData['emergency']) ? $validatedData['emergency'] : 0;
        $newRequest->despacho = isset($validatedData['despacho']) ? $validatedData['despacho'] : 0;

        if ($request->hasFile('voucher')) {
            $file = $request->file('voucher');
            $filename = 'voucher_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads'), $filename);
            $newRequest->voucher = $filename;
        }

        $newRequest->status = 'nuevo';
        $newRequest->status_payment = 'impagado';
        $newRequest->products = $validatedData['products'];
        $newRequest->comment = $validatedData['comment'];
        $newRequest->app_id = $validatedData['app_id'];
        $newRequest->save();

        $request_id = $newRequest->id;

        $newPayment = new Payment();
        $newPayment->amount = $validatedData['price'];
        $newPayment->description = 'Monto a cancelar para el pedido # ' . $request_id;
        $newPayment->currency = 'CLP';
        $newPayment->contact = null;
        $newPayment->extra_data = null;
        $newPayment->transfers = null;
        $newPayment->request_id = $request_id;
        $newPayment->save();

        // Restar stock después de crear el pedido
        if (is_array($products)) {
            foreach ($products as $productData) {
                $productId = $productData['id'];
                $quantity = $productData['quantity'];
                $vasosPedido = $productData['vasos'];

                $product = Product::find($productId);
                if ($product) {
                    $stockToSubtract = $quantity;

                    if ($product->category == 2) {
                        $stockToSubtract = $vasosPedido / $product->vasos;
                    }

                    // Permitir stock negativo excepto para categoría 2
                    if ($product->category != 2) {
                        $product->stock -= $stockToSubtract;
                    } else {
                        $product->stock = max(0, $product->stock - $stockToSubtract);
                    }
                    $product->save();
                }
            }
        }

        return response()->json(['message' => 'Pedido creado con éxito'], 201);
    }

    public function storePedidoFinal(Request $request)
    {
        // 🕐 Validar horario permitido para pedidos (7:00 AM - 1:00 PM)
        $horaActual = Carbon::now('America/Santiago');
        $horaInicio = Carbon::createFromTime(7, 0, 0, 'America/Santiago');
        $horaFin = Carbon::createFromTime(13, 0, 0, 'America/Santiago');
        
        if (!$horaActual->between($horaInicio, $horaFin)) {
            $horaActualFormateada = $horaActual->format('H:i');
            return response()->json([
                'success' => false,
                'message' => "⏰ Los pedidos solo están habilitados entre las 7:00 AM y las 1:00 PM.\n\nHora actual: {$horaActualFormateada}\n\nPor favor, intenta nuevamente dentro del horario permitido."
            ], 403);
        }

        // 📅 Validar día de la semana permitido según tipo de negocio
        $appId = $request->input('app_id');
        
        // IDs de locales propios (Martes, Jueves, Viernes)
        $idsPropios = [58, 59, 78, 86, 97, 107, 111, 116]; // Agustinas, Plaza De Armas, Encomenderos, Ahumada, Rosario norte, Bulnes, Mall Imperio, Las Condes
        
        // IDs de franquicias (Lunes, Miércoles, Viernes)
        $idsFranquicias = [114, 95, 77, 108, 117, 113, 96, 102, 98]; // Amunategui, Bombero Ossa, Merced, Puente Alto, Rancagua, Vergara, Suecia, Turbus, Manuel Montt
        
        $esPropio = in_array($appId, $idsPropios);
        $esFranquicia = in_array($appId, $idsFranquicias);
        
        $diaActual = $horaActual->dayOfWeek; // 0=domingo, 1=lunes, etc.
        
        // Determinar días permitidos según tipo
        $diasPermitidos = null;
        $tipoNegocio = '';
        
        if ($esPropio) {
            $diasPermitidos = [2, 4, 5]; // Martes, Jueves, Viernes
            $tipoNegocio = 'propio';
        } elseif ($esFranquicia) {
            $diasPermitidos = [1, 3, 5]; // Lunes, Miércoles, Viernes
            $tipoNegocio = 'franquicia';
        }
        
        // Validar día solo si el negocio está en alguna de las listas
        if ($diasPermitidos !== null) {
            if (!in_array($diaActual, $diasPermitidos)) {
                $nombresDias = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
                $nombreDiasPermitidos = implode(', ', array_map(function($d) use ($nombresDias) {
                    return $nombresDias[$d];
                }, $diasPermitidos));
                
                $nombreDiaActual = $nombresDias[$diaActual];
                
                return response()->json([
                    'success' => false,
                    'message' => "📅 Tu local ({$tipoNegocio}) solo puede hacer pedidos los días: {$nombreDiasPermitidos}.\n\nHoy es {$nombreDiaActual}.\n\nPor favor, intenta nuevamente en un día permitido."
                ], 403);
            }
        }

        $validatedData = $request->validate([
            'contact_name' => 'required|string|max:70',
            'contact_phone' => 'required|string|max:20',
            'paymode' => 'required',
            'payment_method' => 'nullable|string|in:contado,credito,efectivo,debito,transferencia,cheque,banco,amipass,multicaja,edenred,convenio_empresa,sodexo,rappi,junaeb,uber,pedidos_ya,pluxee,banco_chile_20,fluxi',
            'invoice_type' => 'nullable|string|in:ticket,boleta',
            'status' => 'required',
            'products' => 'required|string',
            'comment' => 'required|string',
            'price' => '',
            'subtotal' => '',
            'iva' => '',
            'emergency' => '',
            'despacho' => '',
            'app_id' => 'required|integer',
        ]);

        // Validar stock de productos antes de crear el pedido
        $productos = json_decode($validatedData['products'], true);
        $productosConStock = [];
        
        foreach ($productos as $producto) {
            // Buscar el producto en la tabla de precios centralizados
            $productoDB = DB::connection('easyerp_master')
                ->table('pedidofinal_precios')
                ->where('id', $producto['id'])
                ->first();
            
            if (!$productoDB) {
                return response()->json([
                    'success' => false,
                    'message' => "Producto '{$producto['name']}' no encontrado en precios centralizados"
                ], 404);
            }
            
            // Solo validar stock si el producto tiene control de stock (stock NOT NULL)
            if ($productoDB->stock !== null) {
                $cantidadSolicitada = floatval($producto['quantity']);
                $stockDisponible = floatval($productoDB->stock);
                
                if ($stockDisponible <= 0) {
                    return response()->json([
                        'success' => false,
                        'message' => "⚠️ '{$productoDB->producto}' sin stock disponible"
                    ], 400);
                }
                
                if ($cantidadSolicitada > $stockDisponible) {
                    return response()->json([
                        'success' => false,
                        'message' => "⚠️ '{$productoDB->producto}': solo quedan {$stockDisponible} {$productoDB->unidad_medida}, solicitaste {$cantidadSolicitada}"
                    ], 400);
                }
                
                // Guardar para descontar después
                $productosConStock[] = [
                    'id' => $productoDB->id,
                    'cantidad' => $cantidadSolicitada
                ];
            }
        }

        // Crear el pedido usando transacción para atomicidad
        DB::beginTransaction();
        try {
            $newRequest = new Requests();
            $newRequest->contact_name = $validatedData['contact_name'];
            $newRequest->contact_phone = $validatedData['contact_phone'];
            $newRequest->paymode = $validatedData['paymode'];
            $newRequest->price = $validatedData['price'] ?? 0;
            $newRequest->subtotal = $validatedData['subtotal'] ?? 0;
            $newRequest->iva = $validatedData['iva'] ?? 0;
            $newRequest->emergency = $validatedData['emergency'] ?? 0;
            $newRequest->despacho = $validatedData['despacho'] ?? 0;
            $newRequest->products = $validatedData['products'];
            $newRequest->comment = $validatedData['comment'];
            $newRequest->status = $validatedData['status'];
            $newRequest->status_payment = 'impagado';
            $newRequest->app_id = $validatedData['app_id'];
            $newRequest->save();
            
            // Descontar stock de los productos Y guardar detalle para historial
            foreach ($productosConStock as $prod) {
                DB::connection('easyerp_master')
                    ->table('pedidofinal_precios')
                    ->where('id', $prod['id'])
                    ->decrement('stock', $prod['cantidad']);
            }
            
            // 📊 NUEVO: Guardar detalle de cada producto para reportes e historial
            foreach ($productos as $producto) {
                // Obtener info actualizada del producto
                $productoDB = DB::connection('easyerp_master')
                    ->table('pedidofinal_precios')
                    ->where('id', $producto['id'])
                    ->first();
                
                if ($productoDB) {
                    $cantidad = floatval($producto['quantity']);
                    $precioUnitario = floatval($productoDB->precio_por_unidad);
                    
                    DB::connection('easyerp_master')
                        ->table('pedidofinal_detalle')
                        ->insert([
                            'request_id' => $newRequest->id,
                            'app_id' => $validatedData['app_id'],
                            'producto_id' => $productoDB->id,
                            'producto_nombre' => $productoDB->producto,
                            'categoria' => $productoDB->categoria ?? 'general',
                            'cantidad' => $cantidad,
                            'unidad_medida' => $productoDB->unidad_medida,
                            'precio_unitario' => $precioUnitario,
                            'subtotal' => $cantidad * $precioUnitario,
                            'contact_name' => $validatedData['contact_name'],
                            'status' => $validatedData['status'],
                            'fecha_pedido' => now(),
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                }
            }
            
            // Crear registro de Payment para Linkify
            $newPayment = new Payment();
            $newPayment->amount = $validatedData['price'] ?? 0;
            $newPayment->description = 'Monto a cancelar para el pedido # ' . $newRequest->id;
            $newPayment->currency = 'CLP';
            $newPayment->contact = null;
            $newPayment->extra_data = null;
            $newPayment->transfers = null;
            $newPayment->request_id = $newRequest->id;
            $newPayment->save();
            
            // Cargar la relación payment en el request
            $newRequest->load('payment');
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Pedido Final creado con éxito',
                'data' => $newRequest  // Retornar el pedido completo con payment
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear pedido: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'contact_name' => 'required|string|max:70',
            'contact_phone' => 'required|string|max:20',
            'paymode' => [
                'required',
                // Rule::in(['Credito', 'Transferencia', 'Efectivo']),
            ],
            'voucher' => 'nullable|file', // Actualizado para aceptar archivos
            'status' => [
                'required',
                // Rule::in(['enviado', 'aprobado', 'rechazado']),
            ],
            'products' => 'required|string',
            'comment' => 'required|string',
            // 'transaccion' => 'nullable|string',
            
            'app_id' => 'required|integer|unsigned',
        ]);
    
        $requestData = Requests::findOrFail($id);
        $requestData->contact_name = $validatedData['contact_name'];
        $requestData->contact_phone = $validatedData['contact_phone'];
        $requestData->paymode = $validatedData['paymode'];
        // $requestData->paymode = $validatedData['transaccion'];
        // Procesar el archivo adjunto si existe
        if ($request->hasFile('voucher')) {
            $file = $request->file('voucher');
            $filename = 'voucher_'.time();
            $file->move(public_path('uploads'), $filename);
            $requestData->voucher = $filename;
        }
    
        $requestData->status = $validatedData['status'];
        $requestData->products = $validatedData['products'];
        $requestData->comment = $validatedData['comment'];
        $requestData->app_id = $validatedData['app_id'];
        $requestData->save();
    
        return response()->json(['message' => 'Request updated successfully'], 200);
    }

    public function index(Request $request)
    {
        $_request = $request->all();
        $pquery =  Requests::with('payment')->orderByDesc('id');

        if (isset($_request['startDate']) && isset($_request['endDate'])){
            $pquery->where('created_at', '>=', $_request['startDate'])
            ->where('created_at', '<=', $_request['endDate']);
        }

        $filters = ['status', 'paymode'];
        $orders = ['id','name'];

        // Procesamiento individual de items
        $Paginated = MPage::paginate($pquery, $request,15,'','requests',$orders,$filters, false, null);
        
        return response()->json($Paginated);
    }

    public function getApproved(Request $request)
    {
        $today = Carbon::today(); // Obtiene la fecha de hoy

        // $database = Config::get('database.connections.mysql_local.database');
        $pquery = Requests::with('payment')
            ->where('status', 'aprobado')
            ->where('voucher', '=', null)
            ->whereDate('created_at', $today) // Agrega la condición para filtrar por fecha de hoy
            ->orderByDesc('id')
            ->limit(1)
            ->get();

        // $request = Requests::findOrFail($id);
        $orders = ['id','name'];
        $filters = ['status', 'paymode'];
        
        //Procesamiento individual de items
        // $Paginated = MPage::paginate($pquery, $request,12,'','requests',$orders,$filters, false, null);

        // foreach ($Paginated['items'] as $key) {
        //     if ($key->prices) {
        //         $key->prices = json_decode($key->prices);
        //     }
        // }
        
        $data = $pquery;
        return response()->json($data);
    }


    public function remove($id)
    {
        $request = Requests::findOrFail($id);
        $request->delete();

        return response()->json(['message' => 'Request deleted successfully'], 200);
    }

    public function decline($app_id,$id)
    {   
        $app = Aplication::where('id', $app_id)->with('database')->first();
        // dd($app );
        $connection = new ConectionDB($app); // Suponiendo que $app contiene la información de la base de datos
        $connection->ChangeDBToApp($app, $reconect = true); // Reconectar a la base de datos

        $request = Requests::findOrFail($id);

        if(!$request) return response()->json('Pedido no encontrado',404);
    
        if($request->status == 'rechazado') return response()->json('El pedido ya se encuentra rechazado',400);
    
        $request->status = 'rechazado';
        if(!$request->save()) return response()->json('Error del servidor',500);
        // $this->transactionHistory(null,$request, 'rechazado');
        return response()->json('Pedido rechazado exitosamente',200);
    }

    public function approve($app_id, $id)
    {
        $app = Aplication::where('id', $app_id)->with('database')->first();
        if (!$app) {
            return response()->json('Aplicación no encontrada', 404);
        }

        $connection = new ConectionDB($app);
        $connection->ChangeDBToApp($app, $reconect = true);

        $pedido = Requests::findOrFail($id); // Usamos o modelo Requests diretamente

        if (!$pedido) { // Esta verificação é redundante se usar findOrFail, mas mantida por segurança.
            return response()->json('Pedido no encontrado', 404);
        }

        if ($pedido->status == 'aprobado') {
            return response()->json('El pedido ya se encuentra aprobado', 200);
        }

        // Mudar o status do pedido para 'aprobado'
        $pedido->status = 'aprobado';
        if (!$pedido->save()) {
            return response()->json('Error del servidor al aprobar el pedido', 500);
        }

        // Processar os productos do pedido para sumar o stock dos ingredientes
        $productsInRequest = json_decode($pedido->products, true);

        if (is_array($productsInRequest) && !empty($productsInRequest)) {
            foreach ($productsInRequest as $item) {
                // Asegurarse de que 'id', 'name', 'category' e 'quantity' existen en el item del producto del pedido
                if (isset($item['id']) && isset($item['name']) && isset($item['category']) && isset($item['quantity'])) {
                    $productNameInRequest = $item['name'];
                    $productCategoryInRequest = (int) $item['category']; // Categoría del producto en el pedido
                    $productQuantityInRequest = (float) $item['quantity']; // Cantidad del producto en el pedido (en KILOGRAMOS)

                    // Si el producto del pedido es una "salsa" (categoría 2)
                    if ($productCategoryInRequest === 2) {
                        // Encontrar el ingrediente correspondiente a esta salsa por su nombre
                        // Asumimos que el nombre del producto en el pedido es idéntico al nombre del ingrediente.
                        $ingredient = Ingredient::where('name', $productNameInRequest)->first();

                        // Si el ingrediente existe y su categoría también es 2 (salsa)
                        if ($ingredient && $ingredient->category_id === 2) {
                            $ingredient->stock_quantity = (float) $ingredient->stock_quantity + $productQuantityInRequest;

                            if (!$ingredient->save()) {
                                return response()->json([
                                    'message' => 'Error al actualizar el stock del ingrediente: ' . $ingredient->name,
                                    'ingredient_id' => $ingredient->id
                                ], 500);
                            }
                        }
                    }
                    // Si el producto no es una "salsa" (categoría 2), o si es un producto compuesto
                    // que usa ingredientes, la lógica para descontar/sumar sus ingredientes
                    // sería diferente y no está cubierta por esta sección.
                }
            }
        }

        // $this->transactionHistory(null,$request, 'rechazado'); // Si esta función es necesaria, asegúrate de que esté definida neste controlador o sea accesible.
        return response()->json('Pedido aprobado exitosamente y stock de ingredientes actualizado.', 200);
    }
    public function hide($app_id,$id)
    {
        $app = Aplication::where('id', $app_id)->with('database')->first();
        // dd($app );
        $connection = new ConectionDB($app); // Suponiendo que $app contiene la información de la base de datos
        $connection->ChangeDBToApp($app, $reconect = true); // Reconectar a la base de datos

        $request = Requests::findOrFail($id);

        if(!$request) return response()->json('Pedido no encontrado',404);
    
        if($request->voucher == 'oculto') return response()->json('El pedido ya se encuentra oculto',400);
    
        $request->voucher = 'oculto';
        if(!$request->save()) return response()->json('Error del servidor',500);
        // $this->transactionHistory(null,$request, 'rechazado');
        return response()->json('Pedido oculto exitosamente',200);
    }

    public function watch($app_id,$id)
    {
        $app = Aplication::where('id', $app_id)->with('database')->first();
        // dd($app );
        $connection = new ConectionDB($app); // Suponiendo que $app contiene la información de la base de datos
        $connection->ChangeDBToApp($app, $reconect = true); // Reconectar a la base de datos

        $request = Requests::findOrFail($id);

        if(!$request) return response()->json('Pedido no encontrado',404);
    
        if($request->status == 'espera' || $request->status == 'aprobado') return response()->json('El pedido ya no es nuevo',202);
    
        $request->status = 'espera';
        if(!$request->save()) return response()->json('Error del servidor',500);
        // $this->transactionHistory(null,$request, 'rechazado');
        return response()->json('Pedido ya no es nuevo exitosamente',200);
    }

    public function view($app_id, $id)
    {
        $app = Aplication::where('id', $app_id)->with('database')->first();
        // dd($app );
        $connection = new ConectionDB($app); // Suponiendo que $app contiene la información de la base de datos
        $connection->ChangeDBToApp($app, $reconect = true); // Reconectar a la base de datos

        $request = Requests::findOrFail($id);

        if(!$request) return response()->json('Pedido no encontrado',404);
    
        
        return response()->json($request,200);
    }
    
    public function voucher(Request $request, $id)
    {

        $pedido = Requests::findOrFail($id);

        if(!$pedido) return response()->json('Pedido no encontrado',404);
    
        if($pedido->review !== null){
            if ($request->hasFile('voucher')) {
                $file = $request->file('voucher');
                $filename = 'voucher_'.time().'.'.$file->getClientOriginalExtension();
                $file->move(public_path('uploads'), $filename);
                $pedido->voucher = $filename;
            }else{
                return response()->json('Voucher no cargado',400);
            }
        }
          
        if(!$pedido->save()) return response()->json('Error del servidor',500);
        
        return response()->json('Pedido actualizado exitosamente',200);
    }

    public function review(Request $request, $id)
    {

        $pedido = Requests::findOrFail($id);

        if(!$pedido) return response()->json('Pedido no encontrado',404);
    
        $pedido->review = $request['review'];
          
        if(!$pedido->save()) return response()->json('Error del servidor',500);
        
        return response()->json('Pedido actualizado exitosamente',200);
    }

    public function getCantPedidosNew(){

        $pedidosNew = Requests::where('status','nuevo')->get();
        
        return count($pedidosNew);
    }

    public function facturarPedido(Request $request)
    {
        $_request = $request->all();
        $_request['type_sell'] = 'factura';
        //Nos conectamos a la app del pedido y buscamos el pedido
        $app_id  = $_request['app_id'];
        $pedido_id  = $_request['pedido_id'];
        //Buscamos el pedido EN LA SUCURSAL
        $app = Aplication::where('id', $app_id)->with('database')->first();
        $pedido = DB::table($app->database->name.'.requests')->where('id', $pedido_id)->first();

        if(!$pedido){
            return response()->json("Pedido no encontrado", 404);
        }
        //Usuario logueado
        $user = Auth::user()->id;
        if (!$user) {
            return response()->json("Usuario no encontrado", 404);
        }
        
        $_request['user'] = $user;

        // Verificando que el cliente exista
        $client = Client::where('rut', $_request['rut'])->first();

        if (!$client) {
            $client = Client::createClient($_request);
        } else {
            $client = Client::editClient($_request, $client->id);
        }
        
        $query = array('id' => $pedido->id, 'response_folio' => false, 'dataEnviada99' => $_request);

        if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii')) {
            if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.factura')) {
                if (isset($_request['type_sell']) && $_request['type_sell'] == 'factura') {
                    $asingFolio = SIIController::processFacturaPedido($request, $pedido, $client, $app);
                }
                if (isset($asingFolio)) {
                    if (!$asingFolio['success']) {
                        $query['response_folio'] = $asingFolio['content'];
                        $query['sii_error'] = $asingFolio['content'];
                        if (isset($_request['ticket'])) {
                            return [$query, $asingFolio['code']];
                        } else {
                            return response()->json($query, $asingFolio['code']);
                        }
                    }
                    $query['response_folio'] = $asingFolio['content'];
                }
            } else {
                if (!isset($_request['type_sell'])) {
                    $query['response_folio'] = 'boleta_local';
                }
                if (isset($_request['other_type'])) {
                    $query['response_folio_other'] = $_request['other_type'];
                }
            }
        
            if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.boleta_local') && !isset($_request['type_sell'])) {
                // impresion de boleta con formato SII (regulacion)
                $query['response_folio'] = $this->printPedidoPDF($request, $pedido, true, true);
            }
        
            if (isset($_request['type_sell'])) {
                if ($_request['type_sell'] == "other" || $_request['type_sell'] == "rappi" || $_request['type_sell'] == "junaeb" || $_request['type_sell'] == "uber" || $_request['type_sell'] == "transferencia" || $_request['type_sell'] == "credito") {
                    $query['response_folio'] = $this->printPedidoPDF($request, $pedido, true, true);
                }
            }
        } // CurrentApp::ConfStr('modulos.ventas.submodulos.sii')
        
        if (isset($_request['ticket'])) {
            return $query;
        } else {
            return response()->json($query, 200);
        }
    }

    public function printPedidoPDF(Request $request, $pedido, $boleta = false, $self = false)
    {
      $database = Config::get('database.connections.mysql_local.database');
  
      $app = CurrentApp::App();
      $pedido['envs'] = json_decode($app->environment_vars);
        
      // todos los productos con información completa
      $products = json_decode($pedido->products);
      
      // 🔍 DEBUG: Ver qué productos tenemos
      error_log('🚀 PRODUCTOS RECIBIDOS EN PDF: ' . json_encode($products));
      
      // Enriquecer productos con información adicional si es necesario
      foreach ($products as &$product) {
        // Asegurar que tenga precio si no lo tiene
        if (!isset($product->price) || $product->price == 0) {
          // Buscar el precio del producto en la base de datos
          $dbProduct = DB::table($database . '.products')->where('name', $product->name)->first();
          if ($dbProduct) {
            $product->price = $dbProduct->price;
          }
        }
        
        // Asegurar que tenga cantidad
        if (!isset($product->quantity)) {
          $product->quantity = 1;
        }
      }

      $size = array(0, 0, 227, 600);

        $pdf = \PDF::loadView('facturaPedido', compact('pedido','products'))->setPaper($size);
        $name = 'recibo_' . uniqid() . '.pdf';

      Storage::put('public/facturas_pedidos/' . $name, $pdf->output());

      $b64Doc = chunk_split(base64_encode($pdf->output()));

      if ($boleta && $self) {
        return $b64Doc;
      }
      return response()->json($b64Doc, 200);
    }

    // --------------------------------------------------REPOSTERIA-----------------------------------------------------

    public function indexReposteria(Request $request)
    {
        $_request = $request->all();
        $pquery =  RequestsReposteria::with('payment')->orderByDesc('id');

        if (isset($_request['startDate']) && isset($_request['endDate'])){
            $pquery->where('created_at', '>=', $_request['startDate'])
            ->where('created_at', '<=', $_request['endDate']);
        }

        $filters = ['status', 'paymode'];
        $orders = ['id','name'];

        // Procesamiento individual de items
        $Paginated = MPage::paginate($pquery, $request,15,'','requests_reposteria',$orders,$filters, false, null);
        
        return response()->json($Paginated);
    }

    public function storeReposteria(Request $request)
    {
        $validatedData = $request->validate([
            'contact_name' => 'required|string|max:70',
            'contact_phone' => 'required|string|max:20',
            'paymode' => [
                'required',
                // Rule::in(['credito', 'transfererencia', 'efectivo']),
            ],
            'voucher' => 'nullable|file', // aceptar archivos
            'status' => [
                'required',
                // Rule::in(['enviado', 'aprobado', 'rechazado']),
            ],
            'products' => 'required|string',
            'comment' => 'required|string',
            // 'transaccion' => 'required|string',
            'price' => '',
            'subtotal' => '',
            'iva' => '',
            'emergency' => '',
            'despacho' => '',
            'app_id' => 'required|integer',
        ]);


        $newRequest = new RequestsReposteria();   
        $newRequest->contact_name = $validatedData['contact_name'];
        $newRequest->contact_phone = $validatedData['contact_phone'];
        $newRequest->paymode = $validatedData['paymode'];
        // $newRequest->transaccion = $validatedData['transaccion'];
        $newRequest->price = $validatedData['price'];
        $newRequest->subtotal = $validatedData['subtotal'];
        $newRequest->iva = $validatedData['iva'];
        
        if(isset($validatedData['emergency'])){
            $newRequest->emergency = $validatedData['emergency'];
        }else{
            $newRequest->emergency = 0;
        }
        if(isset($validatedData['despacho'])){
            $newRequest->despacho = $validatedData['despacho'];
        }else{
            $newRequest->despacho = 0;
        }
        // Procesar el archivo adjunto si existe
        if ($request->hasFile('voucher')) {
            $file = $request->file('voucher');
            $filename = 'voucher_'.time().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads'), $filename);
            $newRequest->voucher = $filename;
        }
        //Por defecto 'impagado'
        // $newRequest->status = $validatedData['status'];
        $newRequest->status = 'nuevo';
        $newRequest->status_payment = 'impagado';
        $newRequest->products = $validatedData['products'];
        $newRequest->comment = $validatedData['comment'];
        $newRequest->app_id = $validatedData['app_id'];
        $newRequest->save();


        //CREANDO EL COBRO CORRESPONDIENTE AL PEDIDO
        //Id del request recien creado
        $request_id = $newRequest->id;

        $newPayment = new PaymentReposteria();   
        $newPayment->amount = $validatedData['price'];
        $newPayment->description = 'Monto a cancelar para el pedido # '.$request_id;
        $newPayment->currency = 'CLP';
        $newPayment->contact = null;
        $newPayment->extra_data = null;
        $newPayment->transfers = null;
        $newPayment->request_id = $request_id;      

        $newPayment->save();

        return response()->json(['message' => 'Request created successfully'], 201);
    }
    
    public function reviewReposteria(Request $request, $id)
    {

        $pedido = RequestsReposteria::findOrFail($id);

        if(!$pedido) return response()->json('Pedido no encontrado',404);
    
        $pedido->review = $request['review'];
          
        if(!$pedido->save()) return response()->json('Error del servidor',500);
        
        return response()->json('Pedido actualizado exitosamente',200);
    }

    public function getCantPedidosNewReposteria(){

        $pedidosNew = RequestsReposteria::where('status','nuevo')->get();
        
        return count($pedidosNew);
    }

    public function approveReposteria($app_id,$id)
    {
        $app = Aplication::where('id', $app_id)->with('database')->first();
        // dd($app );
        $connection = new ConectionDB($app); // Suponiendo que $app contiene la información de la base de datos
        $connection->ChangeDBToApp($app, $reconect = true); // Reconectar a la base de datos

        $request = RequestsReposteria::findOrFail($id);

        if(!$request) return response()->json('Pedido no encontrado',404);
    
        if($request->status == 'aprobado') return response()->json('El pedido ya se encuentra aprobado',400);
    
        $request->status = 'aprobado';
        if(!$request->save()) return response()->json('Error del servidor',500);
        // $this->transactionHistory(null,$request, 'rechazado');
        return response()->json('Pedido aprobado exitosamente',200);
    }

    public function declineReposteria($app_id,$id)
    {   
        $app = Aplication::where('id', $app_id)->with('database')->first();
        // dd($app );
        $connection = new ConectionDB($app); // Suponiendo que $app contiene la información de la base de datos
        $connection->ChangeDBToApp($app, $reconect = true); // Reconectar a la base de datos

        $request = RequestsReposteria::findOrFail($id);

        if(!$request) return response()->json('Pedido no encontrado',404);
    
        if($request->status == 'rechazado') return response()->json('El pedido ya se encuentra rechazado',400);
    
        $request->status = 'rechazado';
        if(!$request->save()) return response()->json('Error del servidor',500);
        // $this->transactionHistory(null,$request, 'rechazado');
        return response()->json('Pedido rechazado exitosamente',200);
    }

    public function watchReposteria($app_id,$id)
    {
        $app = Aplication::where('id', $app_id)->with('database')->first();
        // dd($app );
        $connection = new ConectionDB($app); // Suponiendo que $app contiene la información de la base de datos
        $connection->ChangeDBToApp($app, $reconect = true); // Reconectar a la base de datos

        $request = RequestsReposteria::findOrFail($id);

        if(!$request) return response()->json('Pedido no encontrado',404);
    
        if($request->status == 'espera' || $request->status == 'aprobado') return response()->json('El pedido ya no es nuevo',202);
    
        $request->status = 'espera';
        if(!$request->save()) return response()->json('Error del servidor',500);
        // $this->transactionHistory(null,$request, 'rechazado');
        return response()->json('Pedido ya no es nuevo exitosamente',200);
    }

    public function viewReposteria($app_id, $id)
    {
        $app = Aplication::where('id', $app_id)->with('database')->first();
        // dd($app );
        $connection = new ConectionDB($app); // Suponiendo que $app contiene la información de la base de datos
        $connection->ChangeDBToApp($app, $reconect = true); // Reconectar a la base de datos

        $request = RequestsReposteria::findOrFail($id);

        if(!$request) return response()->json('Pedido no encontrado',404);
    
        
        return response()->json($request,200);
    }

    public function facturarPedidoReposteria(Request $request)
    {
        $_request = $request->all();
        $_request['type_sell'] = 'factura';
        //Nos conectamos a la app del pedido y buscamos el pedido
        $app_id  = $_request['app_id'];
        $pedido_id  = $_request['pedido_id'];
        //Buscamos el pedido EN LA SUCURSAL
        $app = Aplication::where('id', $app_id)->with('database')->first();
        $pedido = DB::table($app->database->name.'.requests_reposteria')->where('id', $pedido_id)->first();

        if(!$pedido){
            return response()->json("Pedido no encontrado", 404);
        }
        //Usuario logueado
        $user = Auth::user()->id;
        if (!$user) {
            return response()->json("Usuario no encontrado", 404);
        }
        
        $_request['user'] = $user;

        // Verificando que el cliente exista
        $client = Client::where('rut', $_request['rut'])->first();

        if (!$client) {
            $client = Client::createClient($_request);
        } else {
            $client = Client::editClient($_request, $client->id);
        }
        
        $query = array('id' => $pedido->id, 'response_folio' => false, 'dataEnviada88' => $_request);

        if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii')) {
            if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.factura')) {
                if (isset($_request['type_sell']) && $_request['type_sell'] == 'factura') {
                    $asingFolio = SIIController::processFacturaPedidorReposteria($request, $pedido, $client, $app);
                }
                if (isset($asingFolio)) {
                    if (!$asingFolio['success']) {
                        $query['response_folio'] = $asingFolio['content'];
                        if (isset($_request['ticket'])) {
                            return [$query, $asingFolio['code']];
                        } else {
                            return response()->json($query, $asingFolio['code']);
                        }
                    }
                    $query['response_folio'] = $asingFolio['content'];
                }
            } else {
                if (!isset($_request['type_sell'])) {
                    $query['response_folio'] = 'boleta_local';
                }
                if (isset($_request['other_type'])) {
                    $query['response_folio_other'] = $_request['other_type'];
                }
            }
        
            if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.boleta_local') && !isset($_request['type_sell'])) {
                // impresion de boleta con formato SII (regulacion)
                $query['response_folio'] = $this->printPedidoReposteriaPDF($request, $pedido, true, true);
            }
        
            if (isset($_request['type_sell'])) {
                if ($_request['type_sell'] == "other" || $_request['type_sell'] == "rappi" || $_request['type_sell'] == "junaeb" || $_request['type_sell'] == "uber" || $_request['type_sell'] == "transferencia" || $_request['type_sell'] == "credito") {
                    $query['response_folio'] = $this->printPedidoReposteriaPDF($request, $pedido, true, true);
                }
            }
        } // CurrentApp::ConfStr('modulos.ventas.submodulos.sii')
        
        if (isset($_request['ticket'])) {
            return $query;
        } else {
            return response()->json($query, 200);
        }
    }

    public function printPedidoReposteriaPDF(Request $request, $pedido, $boleta = false, $self = false)
    {
      $database = Config::get('database.connections.mysql_local.database');
  
      $app = CurrentApp::App();
      $pedido['envs'] = json_decode($app->environment_vars);
        
      // todos los productos
       $products = json_decode($pedido->products);

      $size = array(0, 0, 227, 600);

        $pdf = \PDF::loadView('facturaPedido', compact('pedido','products'))->setPaper($size);
        $name = 'recibo_' . uniqid() . '.pdf';

      Storage::put('public/facturas_pedidos/' . $name, $pdf->output());

      $b64Doc = chunk_split(base64_encode($pdf->output()));

      if ($boleta && $self) {
        return $b64Doc;
      }
      return response()->json($b64Doc, 200);
    }

    public function cancelarFactura(Request $request, $pedido_id)
    {
        $_request = $request->all();
        $_request['type_sell'] = 'nota_de_credito'; // ← CLAVE: marca como nota de crédito
        $_request['pedido_id'] = $pedido_id;
        
        // Obtener app_id del pedido (buscar en todas las apps si no se especifica)
        if (!isset($_request['app_id'])) {
            // Buscar el pedido en la base de datos principal para obtener app_id
            $pedido_main = DB::table('requests')->where('id', $pedido_id)->first();
            if (!$pedido_main) {
                return response()->json(['success' => false, 'message' => 'Pedido no encontrado'], 404);
            }
            $_request['app_id'] = $pedido_main->app_id;
        }
        
        $app_id = $_request['app_id'];
        
        // Buscamos el pedido EN LA SUCURSAL (mismo patrón que facturarPedido)
        $app = Aplication::where('id', $app_id)->with('database')->first();
        
        if (!$app) {
            return response()->json(['success' => false, 'message' => 'Aplicación no encontrada'], 404);
        }
        
        $pedido = DB::table($app->database->name.'.requests')->where('id', $pedido_id)->first();

        if (!$pedido) {
            return response()->json(['success' => false, 'message' => 'Pedido no encontrado'], 404);
        }
        
        // Usuario logueado (mismo patrón que facturarPedido)
        $user = Auth::user()->id;
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Usuario no encontrado'], 404);
        }
        
        $_request['user'] = $user;
        
        // Para nota de crédito, no necesitamos crear/editar cliente
        // El cliente ya existe de la factura original
        
        $query = array(
            'success' => false,
            'id' => $pedido->id, 
            'response_folio' => false, 
            'message' => '',
            'dataEnviada' => $_request
        );

        // Verificar configuración SII (mismo patrón que facturarPedido)
        if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii')) {
            if (CurrentApp::ConfStr('modulos.ventas.submodulos.sii.ajustes.factura')) {
                // Procesar nota de crédito usando el SIIController (adaptado del patrón de facturación)
                $asingFolio = SIIController::processNotaCreditoPedido($_request, $pedido, null, $app);
                
                if (isset($asingFolio)) {
                    if (!$asingFolio['success']) {
                        $query['response_folio'] = $asingFolio['content'];
                        $query['message'] = $asingFolio['message'] ?? 'Error al generar la nota de crédito';
                        return response()->json($query, $asingFolio['code'] ?? 400);
                    }
                    $query['success'] = true;
                    $query['response_folio'] = $asingFolio['content'];
                    $query['message'] = 'Nota de crédito generada exitosamente';
                }
            } else {
                $query['message'] = 'El módulo de facturación SII no está configurado';
                return response()->json($query, 400);
            }
        } else {
            $query['message'] = 'El módulo SII no está habilitado';
            return response()->json($query, 400);
        }

        return response()->json($query, 200);
    }

    /**
     * Obtener productos de pedidofinal_precios para control de stock
     */
    public function getPedidoFinalStock()
    {
        try {
            \Log::info('getPedidoFinalStock: Iniciando consulta');
            
            $productos = DB::table('easyerp.pedidofinal_precios')
                ->select(
                    'id', 
                    'producto as name', 
                    'stock', 
                    'unidad_medida', 
                    'unidad_venta',
                    'precio_por_unidad as precio', 
                    'categoria', 
                    'min_stock'
                )
                ->whereNotNull('stock') // Solo productos con control de stock
                ->orderBy('producto', 'asc')
                ->get()
                ->map(function($producto) {
                    // Calcular kilos totales si es bolsa de 2kg
                    $producto->kilos_totales = null;
                    
                    // Si la unidad de venta es 2 (kg por bolsa) y tiene stock
                    if ($producto->unidad_venta && $producto->stock) {
                        $producto->kilos_totales = $producto->stock * $producto->unidad_venta;
                    }
                    
                    return $producto;
                });

            \Log::info('getPedidoFinalStock: Productos encontrados', ['count' => $productos->count()]);
            
            return response()->json($productos, 200);
        } catch (\Exception $e) {
            \Log::error('getPedidoFinalStock: Error', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener productos: ' . $e->getMessage(),
                'error' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Actualizar stock de un producto en pedidofinal_precios
     */
    public function updatePedidoFinalStock(Request $request, $id)
    {
        $validatedData = $request->validate([
            'stock' => 'required|numeric',
            'tipo' => 'nullable|string|in:agregar,establecer',
        ]);

        try {
            $producto = DB::table('easyerp.pedidofinal_precios')
                ->where('id', $id)
                ->first();

            if (!$producto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ], 404);
            }

            $stockAnterior = $producto->stock ?? 0;
            $tipo = $validatedData['tipo'] ?? 'agregar';
            
            if ($tipo === 'establecer') {
                // Modo establecer: el valor es el stock final
                $nuevoStock = $validatedData['stock'];
                $stockAgregado = $nuevoStock - $stockAnterior;
            } else {
                // Modo agregar: el valor se suma al stock actual
                $stockAgregado = $validatedData['stock'];
                $nuevoStock = $stockAnterior + $stockAgregado;
            }

            if ($nuevoStock < 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'El stock no puede ser negativo'
                ], 400);
            }

            // Actualizar stock
            DB::table('easyerp.pedidofinal_precios')
                ->where('id', $id)
                ->update(['stock' => $nuevoStock]);

            // Registrar el cambio en historial (opcional si la tabla no existe)
            try {
                DB::table('easyerp.pedidofinal_stock_history')
                    ->insert([
                        'producto_id' => $id,
                        'producto_nombre' => $producto->producto,
                        'stock_anterior' => $stockAnterior,
                        'stock_agregado' => $stockAgregado,
                        'stock_nuevo' => $nuevoStock,
                        'usuario' => Auth::check() ? Auth::user()->name : 'Sistema',
                        'fecha' => now(),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
            } catch (\Exception $e) {
                // Si la tabla de historial no existe, continuar sin registrar
                \Log::warning('No se pudo registrar en historial: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Stock actualizado correctamente',
                'stock_anterior' => $stockAnterior,
                'stock_nuevo' => $nuevoStock
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar stock: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar precio de un producto en pedidofinal_precios
     */
    public function updatePedidoFinalPrecio(Request $request, $id)
    {
        $validatedData = $request->validate([
            'precio_por_unidad' => 'required|numeric|min:0',
        ]);

        try {
            $producto = DB::table('easyerp.pedidofinal_precios')
                ->where('id', $id)
                ->first();

            if (!$producto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ], 404);
            }

            $precioAnterior = $producto->precio_por_unidad ?? 0;
            $precioNuevo = $validatedData['precio_por_unidad'];

            // Actualizar precio
            DB::table('easyerp.pedidofinal_precios')
                ->where('id', $id)
                ->update(['precio_por_unidad' => $precioNuevo]);

            // Registrar el cambio en historial
            try {
                DB::table('easyerp.pedidofinal_precio_history')
                    ->insert([
                        'producto_id' => $id,
                        'producto_nombre' => $producto->producto,
                        'precio_anterior' => $precioAnterior,
                        'precio_nuevo' => $precioNuevo,
                        'usuario' => Auth::check() ? Auth::user()->name : 'Sistema',
                        'fecha' => now(),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
            } catch (\Exception $e) {
                \Log::warning('No se pudo registrar en historial de precios: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Precio actualizado correctamente',
                'precio_anterior' => $precioAnterior,
                'precio_nuevo' => $precioNuevo
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar precio: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar nombre de un producto en pedidofinal_precios
     */
    public function updatePedidoFinalNombre(Request $request, $id)
    {
        $validatedData = $request->validate([
            'producto' => 'required|string|max:255',
        ]);

        try {
            $producto = DB::table('easyerp.pedidofinal_precios')
                ->where('id', $id)
                ->first();

            if (!$producto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ], 404);
            }

            $nombreAnterior = $producto->producto ?? '';
            $nombreNuevo = $validatedData['producto'];

            // Actualizar nombre
            DB::table('easyerp.pedidofinal_precios')
                ->where('id', $id)
                ->update(['producto' => $nombreNuevo]);

            // Registrar el cambio en historial
            try {
                DB::table('easyerp.pedidofinal_nombre_history')
                    ->insert([
                        'producto_id' => $id,
                        'nombre_anterior' => $nombreAnterior,
                        'nombre_nuevo' => $nombreNuevo,
                        'usuario' => Auth::check() ? Auth::user()->name : 'Sistema',
                        'fecha' => now(),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
            } catch (\Exception $e) {
                \Log::warning('No se pudo registrar en historial de nombres: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Nombre actualizado correctamente',
                'nombre_anterior' => $nombreAnterior,
                'nombre_nuevo' => $nombreNuevo
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar nombre: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener historial de cambios de stock
     */
    public function getPedidoFinalStockHistory()
    {
        try {
            $historial = DB::table('easyerp.pedidofinal_stock_history')
                ->orderBy('fecha', 'desc')
                ->limit(100)
                ->get();

            return response()->json($historial, 200);
        } catch (\Exception $e) {
            // Si la tabla no existe, devolver array vacío
            \Log::warning('Error al obtener historial de stock: ' . $e->getMessage());
            return response()->json([], 200);
        }
    }

    public function getPedidoFinalPrecioHistory()
    {
        try {
            $historial = DB::table('easyerp.pedidofinal_precio_history')
                ->orderBy('fecha', 'desc')
                ->limit(100)
                ->get();

            return response()->json($historial, 200);
        } catch (\Exception $e) {
            \Log::warning('Error al obtener historial de precios: ' . $e->getMessage());
            return response()->json([], 200);
        }
    }

    public function getPedidoFinalNombreHistory()
    {
        try {
            $historial = DB::table('easyerp.pedidofinal_nombre_history')
                ->orderBy('fecha', 'desc')
                ->limit(100)
                ->get();

            return response()->json($historial, 200);
        } catch (\Exception $e) {
            \Log::warning('Error al obtener historial de nombres: ' . $e->getMessage());
            return response()->json([], 200);
        }
    }

    /**
     * 📊 REPORTE: Historial de compras/pedidos con filtros
     * GET /api/local/pedidofinal/historial
     * 
     * Query params:
     * - fecha_desde: 2026-01-01
     * - fecha_hasta: 2026-01-20
     * - app_id: (opcional) filtrar por local específico
     * - categoria: (opcional) filtrar por categoría
     * - producto_id: (opcional) filtrar por producto específico
     */
    public function getHistorialPedidos(Request $request)
    {
        try {
            \Log::info('getHistorialPedidos: Iniciando consulta', $request->all());
            
            $query = DB::connection('easyerp_master')
                ->table('pedidofinal_detalle');
            
            // Filtro por fechas (requerido)
            if ($request->has('fecha_desde') && $request->has('fecha_hasta')) {
                $fechaDesde = $request->input('fecha_desde') . ' 00:00:00';
                $fechaHasta = $request->input('fecha_hasta') . ' 23:59:59';
                $query->whereBetween('fecha_pedido', [$fechaDesde, $fechaHasta]);
            }
            
            // Filtro por app_id (local)
            if ($request->has('app_id') && $request->input('app_id') != 'todos') {
                $query->where('app_id', $request->input('app_id'));
            }
            
            // Filtro por categoría
            if ($request->has('categoria') && $request->input('categoria') != 'todas') {
                $query->where('categoria', $request->input('categoria'));
            }
            
            // Filtro por producto específico
            if ($request->has('producto_id')) {
                $query->where('producto_id', $request->input('producto_id'));
            }
            
            $pedidos = $query
                ->select([
                    'id',
                    'request_id',
                    'app_id',
                    'producto_id',
                    'producto_nombre',
                    'categoria',
                    'cantidad',
                    'unidad_medida',
                    'precio_unitario',
                    'subtotal',
                    'contact_name',
                    'status',
                    'fecha_pedido'
                ])
                ->orderBy('fecha_pedido', 'desc')
                ->get();
            
            \Log::info('getHistorialPedidos: Registros encontrados', ['count' => $pedidos->count()]);
            
            return response()->json([
                'success' => true,
                'data' => $pedidos,
                'total' => $pedidos->count()
            ], 200);
            
        } catch (\Exception $e) {
            \Log::error('getHistorialPedidos: Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener historial: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 📊 REPORTE: Resumen de compras agrupado por producto
     * GET /api/local/pedidofinal/reporte-productos
     * 
     * Query params:
     * - fecha_desde: 2026-01-01
     * - fecha_hasta: 2026-01-20
     * - app_id: (opcional)
     */
    public function getReporteProductos(Request $request)
    {
        try {
            $query = DB::connection('easyerp_master')
                ->table('pedidofinal_detalle');
            
            // Filtros
            if ($request->has('fecha_desde') && $request->has('fecha_hasta')) {
                $fechaDesde = $request->input('fecha_desde') . ' 00:00:00';
                $fechaHasta = $request->input('fecha_hasta') . ' 23:59:59';
                $query->whereBetween('fecha_pedido', [$fechaDesde, $fechaHasta]);
            }
            
            if ($request->has('app_id') && $request->input('app_id') != 'todos') {
                $query->where('app_id', $request->input('app_id'));
            }
            
            // Agrupar por producto
            $reporte = $query
                ->select([
                    'producto_id',
                    'producto_nombre',
                    'categoria',
                    'unidad_medida',
                    DB::raw('SUM(cantidad) as total_cantidad'),
                    DB::raw('AVG(precio_unitario) as precio_promedio'),
                    DB::raw('SUM(subtotal) as total_gastado'),
                    DB::raw('COUNT(DISTINCT request_id) as num_pedidos'),
                    DB::raw('MIN(fecha_pedido) as primera_compra'),
                    DB::raw('MAX(fecha_pedido) as ultima_compra')
                ])
                ->groupBy('producto_id', 'producto_nombre', 'categoria', 'unidad_medida')
                ->orderBy('total_gastado', 'desc')
                ->get();
            
            // Calcular totales generales
            $totales = [
                'total_productos' => $reporte->count(),
                'total_general' => $reporte->sum('total_gastado'),
                'total_pedidos' => $reporte->sum('num_pedidos')
            ];
            
            return response()->json([
                'success' => true,
                'data' => $reporte,
                'totales' => $totales
            ], 200);
            
        } catch (\Exception $e) {
            \Log::error('getReporteProductos: Error', ['message' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al generar reporte: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 📊 REPORTE: Compras por día (timeline)
     * GET /api/local/pedidofinal/reporte-timeline
     */
    public function getReporteTimeline(Request $request)
    {
        try {
            $query = DB::connection('easyerp_master')
                ->table('pedidofinal_detalle');
            
            if ($request->has('fecha_desde') && $request->has('fecha_hasta')) {
                $fechaDesde = $request->input('fecha_desde') . ' 00:00:00';
                $fechaHasta = $request->input('fecha_hasta') . ' 23:59:59';
                $query->whereBetween('fecha_pedido', [$fechaDesde, $fechaHasta]);
            }
            
            if ($request->has('app_id') && $request->input('app_id') != 'todos') {
                $query->where('app_id', $request->input('app_id'));
            }
            
            $timeline = $query
                ->select([
                    DB::raw('DATE(fecha_pedido) as fecha'),
                    DB::raw('COUNT(DISTINCT request_id) as pedidos'),
                    DB::raw('SUM(subtotal) as total'),
                    DB::raw('COUNT(*) as items')
                ])
                ->groupBy(DB::raw('DATE(fecha_pedido)'))
                ->orderBy('fecha', 'asc')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $timeline
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener resumen de stock por negocio
     */
    public function getStockNegocioResumen(Request $request)
    {
        try {
            // SEGURIDAD: Cada negocio solo puede ver su propio stock
            $currentAppId = CurrentApp::App()->id;
            
            $idNegocio = $request->input('id_negocio');
            $appId = $request->input('app_id', $currentAppId); // Usar app actual si no se especifica
            
            // FORZAR: Siempre filtrar por el negocio actual
            $appId = $currentAppId;
            
            // Obtener el registro más reciente de cada producto
            $subQuery = DB::table('pedidofinal_stock_por_negocio as sub')
                ->select('id_producto', DB::raw('MAX(fecha_registro) as max_fecha'))
                ->where('app_id', $appId) // FILTRO OBLIGATORIO
                ->groupBy('id_producto');
            
            if ($idNegocio) {
                $subQuery->where('id_negocio', $idNegocio);
            }
            
            $query = DB::table('pedidofinal_stock_por_negocio as main')
                ->select(
                    'main.id_producto', 
                    'main.producto_nombre',
                    'main.cantidad_reportada', 
                    'main.unidad_medida',
                    'main.id_negocio', 
                    'main.app_id', 
                    'main.nombre_negocio',
                    'main.usuario',
                    'main.observacion',
                    'main.fecha_registro'
                )
                ->joinSub($subQuery, 'latest', function ($join) {
                    $join->on('main.id_producto', '=', 'latest.id_producto')
                         ->on('main.fecha_registro', '=', 'latest.max_fecha');
                })
                ->where('main.app_id', $appId); // FILTRO OBLIGATORIO POR SEGURIDAD
            
            if ($idNegocio) {
                $query->where('main.id_negocio', $idNegocio);
            }
            
            $stock = $query->get();
            
            return response()->json([
                'success' => true,
                'data' => $stock
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener historial completo de stock por negocio
     */
    public function getStockNegocioHistorial(Request $request)
    {
        try {
            // SEGURIDAD: Cada negocio solo puede ver su propio stock
            $currentAppId = CurrentApp::App()->id;
            
            $idNegocio = $request->input('id_negocio');
            $appId = $request->input('app_id', $currentAppId); // Usar app actual si no se especifica
            
            // FORZAR: Siempre filtrar por el negocio actual
            $appId = $currentAppId;
            
            $query = DB::table('pedidofinal_stock_por_negocio')
                ->select(
                    'id',
                    'id_producto', 
                    'producto_nombre',
                    'cantidad_reportada', 
                    'unidad_medida',
                    'id_negocio', 
                    'app_id', 
                    'nombre_negocio',
                    'usuario',
                    'observacion',
                    'fecha_registro'
                )
                ->where('app_id', $appId) // FILTRO OBLIGATORIO POR SEGURIDAD
                ->orderBy('fecha_registro', 'desc');
            
            if ($idNegocio) {
                $query->where('id_negocio', $idNegocio);
            }
            
            $historial = $query->get();
            
            return response()->json([
                'success' => true,
                'data' => $historial
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registrar stock de un negocio (similar a cómo ArqueoCaja guarda datos)
     */
    public function storeStockNegocio(Request $request)
    {
        try {
            // Obtener todos los datos del request
            $data = $request->all();
            
            \Log::info('✅ storeStockNegocio DATOS RECIBIDOS', [
                'data' => $data,
                'tiene_id_negocio' => isset($data['id_negocio']),
                'tiene_app_id' => isset($data['app_id']),
                'tiene_nombre_negocio' => isset($data['nombre_negocio'])
            ]);
            
            // ✅ VALIDAR
            $validator = \Validator::make($data, [
                'id_producto' => 'required|integer',
                'producto_nombre' => 'required|string|max:100',
                'cantidad_reportada' => 'required|numeric|min:0',
                'unidad_medida' => 'nullable|string|max:50',
                'id_negocio' => 'nullable|integer',
                'app_id' => 'nullable',
                'nombre_negocio' => 'nullable|string|max:255',
                'usuario' => 'nullable|string|max:100',
                'observacion' => 'nullable|string'
            ]);
            
            if ($validator->fails()) {
                \Log::error('❌ Validación fallida:', [
                    'errors' => $validator->errors(),
                    'data_received' => $data
                ]);
                return response()->json([
                    'error' => 'Error al registrar stock',
                    'message' => 'The given data was invalid.',
                    'errors' => $validator->errors(),
                    'data_sent' => $data
                ], 422);
            }
            
            $validated = $validator->validated();
            
            \Log::info('✅ Validación exitosa', ['validated' => $validated]);

            // Verificar si ya existe un registro para este producto y negocio (tomar el más reciente)
            $existing = DB::connection('easyerp')->table('pedidofinal_stock_por_negocio')
                ->where('id_producto', $validated['id_producto'])
                ->where(function($q) use ($validated) {
                    if (isset($validated['id_negocio'])) {
                        $q->where('id_negocio', $validated['id_negocio']);
                    }
                    if (isset($validated['app_id'])) {
                        $q->where('app_id', $validated['app_id']);
                    }
                })
                ->orderBy('fecha_registro', 'desc')
                ->first();

            if ($existing) {
                // Actualizar existente
                DB::connection('easyerp')->table('pedidofinal_stock_por_negocio')
                    ->where('id', $existing->id)
                    ->update([
                        'cantidad_reportada' => $validated['cantidad_reportada'],
                        'nombre_negocio' => $validated['nombre_negocio'] ?? $existing->nombre_negocio,
                        'usuario' => $validated['usuario'] ?? $existing->usuario,
                        'observacion' => $validated['observacion'] ?? $existing->observacion,
                        'fecha_registro' => now()
                    ]);

                $id = $existing->id;
            } else {
                // Crear nuevo
                $id = DB::connection('easyerp')->table('pedidofinal_stock_por_negocio')->insertGetId([
                    'id_producto' => $validated['id_producto'],
                    'producto_nombre' => $validated['producto_nombre'],
                    'cantidad_reportada' => $validated['cantidad_reportada'],
                    'unidad_medida' => $validated['unidad_medida'] ?? 'kg',
                    'id_negocio' => $validated['id_negocio'] ?? null,
                    'app_id' => $validated['app_id'] ?? null,
                    'nombre_negocio' => $validated['nombre_negocio'] ?? null,
                    'usuario' => $validated['usuario'] ?? null,
                    'observacion' => $validated['observacion'] ?? null,
                    'fecha_registro' => now()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Stock registrado correctamente',
                'id' => $id
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Error al registrar stock',
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error en storeStockNegocio: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al registrar stock',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener productos base (para stock-excel.vue)
     */
    public function getStock(Request $request)
    {
        try {
            $productos = DB::connection('easyerp')->table('pedidofinal_precios')
                ->select('id', 'producto', 'stock', 'precio_por_unidad', 'unidad_medida')
                ->orderBy('producto', 'asc')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $productos
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
