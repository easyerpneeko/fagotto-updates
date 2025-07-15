<?php

namespace App\Http\Requests\pedidos;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class StorePedidos extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
     public function rules()
     {
         return [
           "name"     => "string",
           "price"      => "required"
         ];
     }

     public function messages()
     {
       return [
          'name.required' => 'El nombre es requerido',
          'price.required' => 'El precio es requerido'
       ];
     }


    protected function failedValidation(Validator $validator) {
       throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
