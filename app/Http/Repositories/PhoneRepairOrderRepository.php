<?php namespace App\Http\Repositories;

// Illuminate
use Illuminate\Support\Facades\Log;
// Modelos
use App\models_local\ClientOrder\PhoneRepairOrder;
use App\models_local\ClientOrder\ClientOrder;
use App\models_local\Client;
// Excepciones
use App\Exceptions\ClientException;
// Repositorios padre
use App\Http\Repositories\ClientOrderRepository;
// Helpers
use App\Helpers\MPage;
use Illuminate\Http\Request;

class PhoneRepairOrderRepository extends ClientOrderRepository
{

  protected $model;

  public function __construct(PhoneRepairOrder $model, ClientOrder $parent_model, Client $client_dependency) {
    parent::__construct($parent_model, $client_dependency);
    $this->model = $model;
  }

  // Crear Parent
  public function create($attributes = [], $repositoryName = null) {
    $order = parent::create($attributes, $repositoryName);
    return $this->find($order->id);
  }

  protected function createOrderClient($attributes = []) {
    $client = $this->createOrUpdateClient($attributes['client']);
    // Creando la orden (PADRE)
    $order = $this->order_model->create([
      'client_id' => $client->id,
    ]);
    return $order;
  }

  // Crear
  public function createChild($attributes = []) {
    /*Log::info($attributes['device_condition']);
    Log::info(gettype($attributes['device_condition']));*/
    return $this->model->create($attributes);
  }

  // Find
  public function find($id = null) {
    $phoneOrder = $this->model->find($id);
    return $phoneOrder->populate();
  }

  // FindByOrder
  public function findByOrder($client_order_id = null) {
    $clientOrder = ClientOrder::where('id', $client_order_id)
                  ->where('orderable_type', PhoneRepairOrder::class)
                  ->whereNotNull('orderable_id')
                  ->first();
    if (!$clientOrder) return null;
    $phoneOrder = $this->find($clientOrder->orderable_id);
    return $phoneOrder;
  }

}

/*
'order' => [
'client' => [
'rut' => '10101',
'email' => 'test',
],
],
'attribute_1',
*/

/*
client:
email
phone
rut
name
lastname
*/
