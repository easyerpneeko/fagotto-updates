<?php

namespace App\Http\Controllers\Controllers_local;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Config;
// Models
use App\models_local\Expense;
// Helpers
use App\Helpers\MPage;
use App\Helpers\ConectionDB;

class ExpensesController extends Controller
{
  public function store(Request $request){
    $_request = $request->all();
    // Validator
    $validator = Validator::make($_request, [
      'name' => 'required',
      'balance' => 'required',
    ]);
    if($validator->fails()) return response()->json($validator->errors(), 400);

    $expense = Expense::create($_request);
    if(!$expense) return response()->json("Error del servidor",500);

    return response()->json($expense,200);
  }

  public function remove($id){
    $expense = Expense::find($id);
    if(!$expense) return response()->json("Este gasto no existe",404);

    if(!$expense->delete()) return response()->json("Error del servidor",500);

    return response()->json("Gasto eliminado exitosamente",200);
  }

  public function index(Request $request){
    $date = Carbon::now();
    $database2 = Config::get('database.connections.mysql_local.database');
    $expenses = DB::table($database2.'.expenses')->where('created_at', '>=', $date->format('Y-m-d 00:00:00'))
                                                 ->where('created_at', '<=', $date->format('Y-m-d 23:59:59'));
    //Ordenamientos
    $orders = ['id','name'];
    //Filtrados
    $filters = ['name', 'balance'];
    //Procesamiento individual de items
    $Paginated = MPage::paginate($expenses, $request, 10, '','expenses',$orders,$filters, false, null);

    return response()->json($Paginated,200);
  }
}
