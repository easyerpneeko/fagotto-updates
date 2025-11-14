<?php

namespace App\Http\Controllers\Controllers_local\ClientOrders;

use App\Http\Repositories\ClientOrderRepository;
use App\Http\Controllers\Controller;
use App\Helpers\CurrentApp;

use App\Http\Requests\ClientOrders\FindRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class ClientOrdersController extends Controller
{

  protected $repository;

  public function __construct(ClientOrderRepository $repository) {
    $this->repository = $repository;
  }

  public function find(FindRequest $request){
    try {
      $order = $this->repository->find($request->input('order_id'));
    } catch (\Exception $e) {
      Log::info('La exception de find en ClientOrdersController');
      Log::info($e);
      return response()->json("Error desconocido al encontrar orden de cliente.", 500);
    }
    return response()->json($order, 200);
  }

}
