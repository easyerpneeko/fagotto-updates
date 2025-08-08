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
}
