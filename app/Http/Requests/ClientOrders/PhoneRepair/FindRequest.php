<?php namespace App\Http\Requests\ClientOrders\PhoneRepair;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class FindRequest extends FormRequest
{

    protected function prepareForValidation() {
      if (!$this->get('phone_order_id') && $this->route('id'))
      $this->merge(['phone_order_id' => $this->route('id')]);
    }

    public function authorize()
    {
        return true;
    }

    public function rules()
    {

        $inputs = [
          'phone_order_id'  =>  [
            'required','exists:App\models_local\ClientOrder\PhoneRepairOrder,id'
          ]
        ];

        return $inputs;

    }

    protected function failedValidation(Validator $validator) {

       throw new HttpResponseException(response()->json($validator->errors(), 422));

    }

}
