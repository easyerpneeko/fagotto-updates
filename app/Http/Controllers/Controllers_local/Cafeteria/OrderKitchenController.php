<?php

namespace App\Http\Controllers\Controllers_local\Cafeteria;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Dompdf\Dompdf;

use App\Helpers\CurrentApp;
use App\models_local\Waiter;
use App\models_local\Board;
use App\Http\Controllers\Controllers_local\SellsController;
use App\Helpers\HistorialCafeteria\HistoryCoffeHelper;

// Helpers
use Carbon\Carbon;
// Models
use App\models_local\Cafeteria\OrderKitchen;

/*
CurrentApp::ConfStr('modulos.cafeteria.submodulos.modo_cocina')
*/
// ["pending","process","complete","cancelled", "closed"]

class OrderKitchenController extends Controller
{

  public static function getPendingOrdersFunc()
  {
    $ordersQuery = OrderKitchen::where(function ($query) {
      $query->where(
        'completed_at',
        '>',
        Carbon::now()->subMinutes(10)->toDateTimeString()
      )->orWhereNull('completed_at');
    })
      ->where(function ($query) {
        $query->where('kitchen_state', '<>', 'cancelled')
          ->where('kitchen_state', '<>', 'closed');
      });

    if (CurrentApp::ConfStr('modulos.cafeteria.submodulo.modo_cocina.ajustes.coc_nombre_mesa')) {
      $ordersQuery->with('order.board');
    }

    if (CurrentApp::ConfStr('modulos.cafeteria.submodulo.modo_cocina.ajustes.coc_nombre_garzon')) {
      $ordersQuery->with('order.waiterAssigned');
    }

    if (CurrentApp::ConfStr('modulos.cafeteria.submodulo.modo_cocina.ajustes.lifo_ordenament')) {
      $ordersQuery->orderBy('created_at', 'desc');
    }

    //if (CurrentApp::ConfStr('modulos.cafeteria.submodulo.modo_cocina.ajustes.kit_o_descripcion')) {
    $ordersQuery->with('order');
    //}

    $orders = $ordersQuery->get();
    return $orders;
  }

  public static function getClosedOrdersFunc()
  {

    // $today = now()->format('Y-m-d'); // Obtiene la fecha actual en formato 'Y-m-d'
    $ordersQuery = OrderKitchen::where('kitchen_state', '<>', 'pending')
      ->whereNotNull('completed_at')
      ->where('completed_at', '>=', now()->subMinutes(1)->subSeconds(30))
      ->orderBy('completed_at', 'asc')
      ->limit(15);
    if (CurrentApp::ConfStr('modulos.cafeteria.submodulo.modo_cocina.ajustes.coc_nombre_mesa')) {
      $ordersQuery->with('order.board');
    }

    if (CurrentApp::ConfStr('modulos.cafeteria.submodulo.modo_cocina.ajustes.coc_nombre_garzon')) {
      $ordersQuery->with('order.waiterAssigned');
    }

    if (CurrentApp::ConfStr('modulos.cafeteria.submodulo.modo_cocina.ajustes.lifo_ordenament')) {
      $ordersQuery->orderBy('created_at', 'asc');
    }

    //if (CurrentApp::ConfStr('modulos.cafeteria.submodulo.modo_cocina.ajustes.kit_o_descripcion')) {
    $ordersQuery->with('order');
    //}

    $orders = $ordersQuery->get();
    return $orders;
  }

  public function getPendingOrders()
  {
    $orders = Self::getPendingOrdersFunc();
    return response()->json($orders, 200);
  }

  public function getClosedOrders()
  {
    $orders = Self::getClosedOrdersFunc();
    return response()->json($orders, 200);
  }

  public function markAsState(Request $request, $id)
  {

    try {
      $orderKitchen = OrderKitchen::findOrFail($id);
    } catch (\Exception $e) {
      return response()->json("No se ha encontrado OrderKitchen.", 404);
    }

    $newKitchenState = $request->input('kitchenState');

    switch ($newKitchenState) {
      case 'complete':
        $orderKitchen->markAsComplete();
        break;
      case 'process':
        $orderKitchen->markAsProcess();
        break;
      case 'pending':
        $orderKitchen->markAsPending();
        break;
    }

    $orders = Self::getPendingOrdersFunc();

    return response()->json(['current' => $orderKitchen, 'orders' => $orders], 200);
  }
}
