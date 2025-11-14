<?php namespace App\Http\Requests\ClientOrders\PhoneRepair;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use App\Helpers\JsonUtils;
use App\Helpers\CurrentApp;

class CreateRequest extends FormRequest
{

    protected function prepareForValidation() {
      if ($this->has('order')) {
        if (JsonUtils::isJson($this->get('order'))) {
          $this->merge([
            'order' => json_decode($this->get('order'), 1)
          ]);
        }
      }
    }

    public function authorize()
    {
        return true;
    }

     public function rules()
     {
        $validaciones = [
          "order"                => "required",
          "order.client"         => "required",
          "order.client.rut"     => "required|string|max:12|min:7",
          "order.client.name"    => "required|string|max:32",
          "order.client.lastname"=> "nullable|string|max:32",
          "order.client.email"   => "required|string|max:256",
          "contact_email"        => "required|string|max:256",
          "device_model"         => "string|nullable|max:32",
          "device_condition"     => "json|nullable",
          "device_failure"       => "string|nullable|max:256",
          "device_imei"          => "string|nullable|max:15",
          "device_password"      => "string|nullable|max:24",
          "observations"         => "string|nullable|max:512",
          "technician_id"        => "nullable|numeric|exists:App\models_local\UserApp,id",
          "budget"               => "numeric|nullable",
        ];
        if (CurrentApp::ConfStr('modulos.ventas.submodulos.clientes.ajustes.cliente_telefono')){
          $validaciones['order.client.phone'] =  'max:16|nullable';
          $validaciones['contact_phone'] =  'max:16|nullable';
        }
        return $validaciones;
     }

     public function messages()
     {
       return [
         "order"                => [
           'required' => "Los datos de la orden padre son requeridos",
           'json'     => 'Formato de :attribute invalido',
         ],
         "order.client"         => [
           'required' => "Los datos del cliente son requeridos",
           'json'     => 'Formato de :attribute invalido',
         ],
         "order.client.rut"     => [
           'required' => "El rut del cliente es requerido",
           'string'   => 'Formato de rut invalido',
           'max'      => 'El numero maximo de caracteres para el rut es de :max',
           'max'      => 'El numero minimo de caracteres para el rut es de :min',
         ],
         "order.client.email"   => [
           'required' => "El :attribute del cliente es requerido",
           'string'   => 'Formato de :attribute del cliente es invalido',
           'max'      => 'El numero maximo de caracteres para el :attribute es de :max',
         ],
         "order.client.name"   => [
           'required' => "El nombre del cliente es requerido",
           'string'   => 'Formato de :attribute del cliente es invalido',
           'max'      => 'El numero maximo de caracteres para el nombre del cliente es de :max',
         ],
         "order.client.lastname"   => [
           'string'   => 'Formato de :attribute del cliente es invalido',
           'max'      => 'El numero maximo de caracteres para el apellido del cliente es de :max',
         ],
         "device_model"         => [
           'string'   => 'Formato de :attribute es invalido',
           'max'      => 'El numero maximo de caracteres para el modelo es de :max',
         ],
         "device_condition"     => [
           'json'   => 'Formato de :attribute es invalido',
         ],
         "device_failure"       => [
           'string'   => 'Formato de :attribute es invalido',
           'max'      => 'El numero maximo de caracteres para la falla es de :max',
         ],
         "device_imei"          => [
           'string'   => 'Formato de :attribute es invalido',
           'max'      => 'El numero maximo de caracteres para el imei es de :max',
         ],
         "device_password"      => [
           'string'   => 'Formato de :attribute es invalido',
           'max'      => 'El numero maximo de caracteres para el contraseña del dispositivo es de :max',
         ],
         "observations"         => [
           'string'   => 'Formato de :attribute es invalido',
           'max'      => 'El numero maximo de caracteres para el observaciones es de :max',
         ],
         "technician_id"        => [
           'numeric'   => 'Formato de :attribute es invalido',
           'exists'    => 'El usuario del tecnico seleccionado no se ha encontrado.',
         ],
         "budget"      => [
           'numeric'   => 'Formato de :attribute es invalido',
         ],
       ];
    }

    protected function failedValidation(Validator $validator) {
       throw new HttpResponseException(response()->json($validator->errors(), 422));
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
