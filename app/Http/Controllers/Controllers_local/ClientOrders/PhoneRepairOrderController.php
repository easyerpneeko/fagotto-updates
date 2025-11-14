<?php

namespace App\Http\Controllers\Controllers_local\ClientOrders;

use App\Http\Repositories\PhoneRepairOrderRepository;
use App\Http\Controllers\Controller;
use App\Helpers\CurrentApp;

use App\Http\Requests\ClientOrders\PhoneRepair\CreateRequest;
use App\Http\Requests\ClientOrders\PhoneRepair\FindRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Http\Controllers\Controllers_local\ClientOrders\PhoneRepairOrder\Paginator;
use App\Classes\ClientOrders\PhoneRepairOrder\Printer;
use App\Classes\ClientOrders\PhoneRepairOrder\EmailDispatcher;

//
use App\Classes\ClientOrders\PhoneRepairOrder\ImeiService;

class PhoneRepairOrderController extends Controller
{

  protected $repository;
  protected $printer;
  protected $email;

  public function __construct(PhoneRepairOrderRepository $repository, Printer $printer, EmailDispatcher $email) {
    $this->repository = $repository;
    $this->printer = $printer;
    $this->email = $email;
  }

  public function create(CreateRequest $request){
    try {
      $data = $request->all();
      $order = $this->repository->create($data);
    } catch (\Exception $e) {
      $this->handleError('La exception de create/create', $e);
      return response()->json("Error desconocido al crear una orden de cliente.", 500);
    }

    try {
      $order->printeable = $this->printer->generate64($order);
    } catch (\Exception $e) {
      $this->handleError('La exception de create/print', $e);
      return response()->json("Error desconocido al generar PDF de impresion para orden.", 500);
    }

    try {
      $this->email->dispatchCreatedOrder($order);
    } catch (\Exception $e) {
      $this->handleError('La exception de create/dispatch-email', $e);
      return response()->json("Error desconocido al enviar email para orden.", 500);
    }

    return response()->json($order, 200);
  }

  public function print(FindRequest $request){
    try {
      $order = $this->repository->find($request->input('phone_order_id'));
    } catch (\Exception $e) {
      $this->handleError('La exception de print/find', $e);
      return response()->json("Error desconocido al buscar una orden de cliente.", 500);
    }

    try {
      $order->printeable = $this->printer->generate64($order);
    } catch (\Exception $e) {
      $this->handleError('La exception de print/print', $e);
      return response()->json("Error desconocido al generar PDF de impresion para orden.", 500);
    }

    return response()->json($order->printeable, 200);
  }

  public function find(FindRequest $request){
    try {
      $order = $this->repository->find($request->input('phone_order_id'));
    } catch (\Exception $e) {
      $this->handleError('La exception de find', $e);
      return response()->json("Error desconocido al buscar una orden de cliente.", 500);
    }

    return response()->json($order, 200);
  }

  public function paginate(Request $request){
    try {
      $paginator = new Paginator($request);
      $paginate = $paginator->paginate();
    } catch (\Exception $e) {
      $this->handleError('La exception de paginate', $e);
      return response()->json("Error desconocido al paginar las ordenes de cliente.", 500);
    }

    return response()->json($paginate, 200);
  }

  public function fetchDeviceByImei($imei) {
    try {
      $response = ImeiService::examine($imei);
    } catch (\Exception $e) {
      $this->handleError('La exception de fetchDeviceByImei', $e);
      return response()->json("Error desconocido al buscar la imei.", 500);
    }
    return response()->json($response, 200);
  }

  /////

  private function handleError($description, $e) {
    Log::info($description.' en PhoneRepairOrderController');
    Log::info($e);
  }

}
