<?php

namespace App\Http\Controllers\Controllers_local;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Helpers\ConectionDB;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Aplication;
use App\models_local\Payment;
use App\models_local\PaymentReposteria;
use App\models_local\Requests;

class PaymentsController extends Controller
{
    function checkHash()
    {
        $verification_hash = $_SERVER["HTTP_X_LINKIFY_CONFIRMATION"];
        $secret = "K85NAo1F7ZAjiJOAbmKyDTDkV3tzYmOJTWRDR0OBX7CMPZh8eYu2p6oWEPtq1YDT";

        switch ($_SERVER["REQUEST_METHOD"]) {
            case "GET":
                $content = html_entity_decode($_GET["encoded_data"]);
                break;
            case "POST":
                $content = file_get_contents('php://input');
                break;
        }

        $local_verification_hash = hash_hmac("sha256", $content, $secret);
        return $verification_hash === $local_verification_hash;
    }

    public function getPayment()
    {
        
        if ($this->checkHash()) {
            // Obtén el valor del parámetro 'encoded_data' de la URL
            $encodedData = $_GET['encoded_data'];

            // Decodificar el JSON
            $data = json_decode($encodedData, true);
            
            if(str_contains($data['id'], 'i')){
                //Separamos los dos ID el de la app y el payment
                list($app_id, $payment_id) = explode('i', $data['id']);
                
                //Conectamos a la app correspondiente
                $app = Aplication::where('id', $app_id)->with('database')->first();
                // dd($app );
                $connection = new ConectionDB($app); // Suponiendo que $app contiene la información de la base de datos
                $connection->set_database($app->database);
    
                $cobro = Payment::findOrFail($payment_id);
    
                return response()->json($cobro);
            }else if(str_contains($data['id'], 'p')){
                //Separamos los dos ID el de la app y el payment
                list($app_id, $payment_id) = explode('p', $data['id']);
                
                //Conectamos a la app correspondiente
                $app = Aplication::where('id', $app_id)->with('database')->first();
                // dd($app );
                $connection = new ConectionDB($app); // Suponiendo que $app contiene la información de la base de datos
                $connection->set_database($app->database);
    
                $cobro = PaymentReposteria::findOrFail($payment_id);
    
                return response()->json($cobro);
            }
            // Separamos los dos ID el de la app y el payment
            // list($app_id, $payment_id) = explode('i', $data['id']);
            
            //Conectamos a la app correspondiente
            // $app = Aplication::where('id', $app_id)->with('database')->first();
            // dd($app );
            // $connection = new ConectionDB($app); // Suponiendo que $app contiene la información de la base de datos
            // $connection->set_database($app->database);

            // $cobro = Payment::findOrFail($payment_id);

            // return response()->json($cobro);

        ////MOCK
        // return response()->json([
        //     'amount' => 1000,
        //     'description' =>
        //     'Cobro servicio X',
        //     'currency' => 'CLP',
        //     'contact' => null,
        //     'extra_data' => null,
        // ]);

        }else{
            return response()->json("Solicitud incorrecta");
        }
    }

    public function notifyPago(Request $request)
    {

        $datos = $request->all();
        // dd($datos);
        if ($this->checkHash()) {
            if($datos['action'] == 'notification'){
                //Separamos los ID
                list($app_id, $payment_id) = explode('i', $datos['id']);
    
                //Conectamos a la app correspondiente
                $app = Aplication::where('id', $app_id)->with('database')->first();
                $connection = new ConectionDB($app); // Suponiendo que $app contiene la información de la base de datos
                $connection->ChangeDBToApp($app, $reconect = true); 
    
                
                $cobro = Payment::findOrFail($payment_id);
                $pedido = Requests::findOrFail($cobro->request_id);
    
                //Guardamos la transferencias
                $cobro->transfers = $datos['transfers'];
                $cobro->save();
                
    
                //Si el monto del pago es igual al cobro LINKIFY devuelve 'exact'
                if($datos['completeness'] == 'exact'){
                    $pedido->status_payment='pagado';
                }
    
                $pedido->save();
                
                //// Contar las tranferencias en caso que sean mas de 1 pago
                // $transfers = $datos['transfers'];
                // $total_amount = 0;
                // //Comparamos los dos montos
                // if($pedido->price == $total_amount){
                //     $pedido->status = 'pagado';
                // }
    
                return response()->json([
                    'status' => 'accepted',
                    'message' => 'Pago verificado correctamente',
                    // 'redirect' => 'http://154.38.171.1/',
                    'restart' => false
                ]);
                
            }
        }
       
        // Definir la ruta y el nombre del archivo
        // $rutaArchivo = storage_path('app/public/datos.txt');

        // Guardar los datos en un archivo de texto
        // $contenido = json_encode($datos);
        // file_put_contents($rutaArchivo, $contenido);

        //Mock
        // return response()->json([
        //     'status' => 'accepted',
        //     'message' => 'Pago verificado correctamente',
        //     'redirect' => 'http://154.38.171.1/',
        //     'restart' => false
        // ]);
    }

    // public function newPayment(Request $request){
        
    //     $validatedData = $request->validate([
    //         'amount' => 'required',
    //         'description' => 'required|string',
    //         'currency' => 'required|string', // aceptar archivos
    //         'contact' => 'nullable',
    //         'extra_data' => 'nullable',
    //         'request_id' => 'required|integer',
    //     ]);

    //     $newRequest = new Payment();   
    //     $newRequest->amount = $validatedData['amount'];
    //     $newRequest->description = $validatedData['description'];
    //     $newRequest->currency = $validatedData['currency'];
    //     $newRequest->contact = null;
    //     $newRequest->extra_data = null;
    //     $newRequest->request_id = $validatedData['request_id'];

    //     $newRequest->save();

    //     return response()->json(['message' => 'Payment created successfully'], 201);
    // }
}
