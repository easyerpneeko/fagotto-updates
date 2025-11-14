<?php

namespace App\Http\Controllers\Controllers_local;

// Laravel
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
// Helpers
use App\Helpers\MPage;
use App\Helpers\CurrentApp;
// Models
use App\models_local\Waiter;
use App\models_local\Board;
// Illuminate
use Illuminate\Support\Facades\Log;
use App\Events\Cafeteria\BoardCreated;
use App\Events\Cafeteria\BoardUpdated;
use App\Events\Cafeteria\BoardRemoved;

class BoardsController extends Controller
{
  protected function store(Request $request){
    $validRules = [
      'name' => 'required|string|unique:mysql_local.boards|min:1|max:24',
    ];
    if (CurrentApp::ConfStr('modulos.cafeteria.ajustes.change_board_color')) {
      $validRules['color'] = 'string|max:16';
    }
    
    $validator = Validator::make($request->all(), $validRules);
    if($validator->fails()) return response()->json($validator->errors()->toJson(), 400);
    $_request = $request->all();

    // Creando columna
    $board = Board::create($_request);
    // Verificando respuesta
    if(!$board) return response()->json("Error del servidor",500);
    // Ok
    broadcast(new BoardCreated($board))->toOthers();
    return response()->json(['Mesa creada exitosamente'],200);
  }

  protected function update(Request $request, $id){
      $validRules = [
        'name' => 'string|min:1|max:24',
      ];

      if (CurrentApp::ConfStr('modulos.cafeteria.ajustes.change_board_color')) {
        $validRules['color'] = 'string|max:16';
      }

      
      $validator = Validator::make($request->all(), $validRules);
      if($validator->fails()) return response()->json($validator->errors(), 400);
      // Validando nombre unico
      $_request = $request->all();
      Log::info('REQUEST BOARD COLOR');
      Log::info($_request);

      $unique = Board::where('name', $_request['name'])->where('id', "<>", $id)->first();
      if($unique) return response()->json("El nombre de mesa esta en uso", 409);

      // Editando mesero
      $board = Board::find($id);
      $board->name = $_request['name'];
      if (CurrentApp::ConfStr('modulos.cafeteria.ajustes.change_board_color')) {
        $board->color = $_request['color'];
      }
      if(!$board->save()) return response()->json('Error del servidor',500);
      // Ok
      broadcast(new BoardUpdated($board))->toOthers();
      return response()->json(['Mesa editada exitosamente'],200);
  }

  protected function index(Request $request){
    // Obtener meseros
    $pquery = Board::where('trash', 0);
    //Ordenamientos
    $orders = ['id','name'];
    //Filtrados
    $filters = ['name', 'state'];
    //Procesamiento individual de items
    $Paginated = MPage::paginate($pquery, $request,10,'','boards',$orders,$filters, false, null);
    // Obtener estado de mesa
    $Paginated['items'] = Board::getStatesBoards($Paginated['items']);
    // Ok
    return response()->json($Paginated);
  }

  protected function remove($id){
    $board = Board::find($id);
    if(!$board) return response()->json('Mesa no encontrada',404);

    if($board->trash == 1) return response()->json('La mesa ya se encuentra eliminada',400);
    // Editando mesero
    $board->trash = 1;
    $board->name = sha1(uniqid());
    if(!$board->save()) return response()->json('Error del servidor',500);

    broadcast(new BoardRemoved($board))->toOthers();
    return response()->json(['Mesa eliminada exitosamente'],200);
  }

  protected function getAllBoards(Request $request){
    // Obtener mesas
    $pquery = Board::where('trash', 0)->get();
    return response()->json($pquery);
  }
}
