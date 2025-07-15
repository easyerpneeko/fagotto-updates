<?php

namespace App\Http\Controllers\Controllers_local;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
// Helpers
use App\Helpers\MPage;
// Models
use App\models_local\Waiter;
use App\models_local\Board;
use App\models_local\Order;

// EVENTS
use App\Events\Cafeteria\OrderUpdated;

class CafeteriaController extends Controller
{
  protected function index(Request $request) {
    // Obtener todas las mesas de la cafetería
    $allBoards = Board::where('trash', 0)->get();
    if (!$allBoards) {
        return response()->json("Error del servidor", 500);
    }

    // Obtener el estado de todas las mesas
    $allBoards = Board::getStatesBoards($allBoards);

    // Obtener camareros en las mesas + las ordenes
    $allBoards = Waiter::getWaitersOfBoards($allBoards);

    // Devolver respuesta exitosa con las mesas y camareros
    return response()->json($allBoards, 200);
}

  protected function getBoardsActives(Request $request){
    // Obtener mesas y meseros
    $boards = Board::where('trash', 0)->get();
    if (!$boards) return response()->json('Error del servidor',500);
    // Obtener estado de las mesas ocupadas
    $boards = Board::getBoardsWaiting($boards);
    // Obtener meseros en mesas
    $boards = Waiter::getWaitersOfBoards($boards);
    // Ok
    
    return response()->json($boards, 200);
  }

  protected function getActiveOrders(Request $request) { //Principalmente para el modo Garzon
    $orders = Order::where('state', 'en espera')->get();
    foreach ($orders as $key => $order) {
      if ($order->board_id)
        $orders[$key]->board  = Board::find($order->board_id);
      if ($order->waiter_id)
        $orders[$key]->waiter = Waiter::find($order->waiter_id);
    }
    if (!$orders) return response()->json('Error del servidor',500);
    return response()->json($orders, 200);
  }

  protected function getBoard(Request $request, $id){
    // Obtener mesas y meseros
    $board = Board::find($id);
    if (!$board) return response()->json('Error del servidor',500);
    // Obtener estado de la mesa
    $board = Board::getBoardWaiting($board);
    // Obtener mesero en mesa
    $board = Waiter::getWaiterOfBoard($board);
    //obtener orders de la mesa
    $board->orders = Order::getOrdersOfBoard($board);
    // Ok
    //dd($board);
    return response()->json($board, 200);
  }

  protected function getAllWaiters(Request $request){
    $waiters = Waiter::where('trash', 0)->get();
    if (!$waiters) return response()->json('Error del servidor',500);

    return response()->json($waiters, 200);
  }

  protected function changeWaiter(Request $request, $id){
    $validator = Validator::make($request->all(), [
      'waiter_id' => 'required',
    ],
    ["waiter_id.required" => "El mesero es requerido"]);
    if($validator->fails()) return response()->json($validator->errors()->toJson(), 400);
    $_request = $request->all();

    $order = Order::find($id);
    if (!$order) return response()->json('Orden no encotrada',404);

    $order->waiter_id = $_request['waiter_id'];
    if(!$order->save())return response()->json('Error del servidor',500);
    try {
      broadcast(new OrderUpdated($order))->toOthers();
    } catch (\Exception $e) {
      //
    }
    return response()->json("Mesero editado exitosamente", 200);
  }

  protected function changeBoard(Request $request, $id){
    $validator = Validator::make($request->all(), [
      'board_id' => 'required',
    ],
    ["board_id.required" => "La mesa es requerida"]);
    if($validator->fails()) return response()->json($validator->errors()->toJson(), 400);
    $_request = $request->all();

    $boardOrder = Order::where('board_id', $_request['board_id'])->where('state', 'en espera')->first();
    if($boardOrder) return response()->json('La mesa se encuentra ocupada', 400);

    $order = Order::find($id);
    if (!$order) return response()->json('Orden no encotrada',404);

    $order->board_id = $_request['board_id'];
    if(!$order->save())return response()->json('Error del servidor',500);
    try {
      broadcast(new OrderUpdated($order))->toOthers();
    } catch (\Exception $e) {
      //
    }
    return response()->json("Mesa cambiada exitosamente", 200);
  }
}
