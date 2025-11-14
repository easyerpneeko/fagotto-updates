<?php namespace App\Http\Repositories;

// Illuminate
use Illuminate\Support\Facades\Log;
// Modelos
use App\models_local\ClientOrder\ClientOrder;
use App\models_local\Client;

// Modelos hijos
use App\models_local\ClientOrder\PhoneRepairOrder;

// Repositorios hijos
use App\Http\Repositories\PhoneRepairOrderRepository;

// Excepciones
use App\Exceptions\ClientException;

/*
modulos.ventas.submodulos.clientes
modulos.ventas.submodulos.clientes.ajustes.cliente_obligatorio
*/

class ClientOrderRepository
{
  protected $order_model;
  protected $client_dependency;
  public function __construct(ClientOrder $order_model, Client $client_dependency) {
    $this->order_model = $order_model;
    $this->client_dependency = $client_dependency;
  }

  // Esta clase debe ser remplazada por la clase hijo que extienda a esta
  public function createChild($attributes = []) {
    $noneChild = new stdClass();
    $noneChild->id = null;
    return $noneChild;
  }

  public function create($attributes = [], $repositoryName = null) {

    // Valores
    $modelName = null;
    if ($repositoryName === null) $repositoryName = get_class($this);
    switch ($repositoryName) {
      case PhoneRepairOrderRepository::class:
        $modelName = PhoneRepairOrder::class;
      break;
      case ClientOrderRepository::class:
        $modelName = null;
      break;
    }

    // Creando orden PADRE
    $order = $this->createOrderClient($attributes['order']); /*order = ['client' => [ ... ]]*/

    // Creando la orden especifica del HIJO
    unset($attributes['order']);
    $child_order = $this->createChild($attributes);

    // Actualizando el modelo padre de ordenes
    $order->update([
      'orderable_type' => $modelName,
      'orderable_id' => $child_order->id,
    ]);

    // Retornando el hijo
    return $child_order;
  }

  protected function createOrderClient($attributes = []) {
    $client = $this->createOrUpdateClient($attributes['client']);
    // Creando la orden (PADRE)
    $order = $this->order_model->create([
      'client_id' => $client->id,
    ]);
    return $order;
  }

  protected function createOrUpdateClient($attributes = []) {
    $hasClient = Client::where('rut', $attributes['rut'])->first();
    $client = null;
    if ($hasClient) {
      $client = $this->updateClient($hasClient->id, $attributes);
      if (!$client)
        throw new ClientException("Error al actualizar cliente", 1);
    }else{
      $client = $this->createClient($attributes);
      if (!$client)
        throw new ClientException("Error al crear cliente", 1);
    }
    return $client;
  }

  private function createClient($attributes = []) {
      return Client::createClient($attributes);
  }

  private function updateClient($id, $attributes = []) {
    try {
      return Client::editClient($attributes, $id, true);
    } catch (\Exception $e) {
      Log::info('La exception de updateClient en ClientOrderRepository');
      Log::info($e);
      return null;
    }
  }

  // Get Order By ID
  public function find($id) {
    $order = $this->order_model->findOrFail($id);
    $order->orderable();
    return $order;
  }

}
