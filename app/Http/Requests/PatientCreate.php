<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PatientCreate extends FormRequest
{

    public function rules()
    {
        return [
            'patient_id'        => 'required|max:255|unique:patients',
            'patient_name'      => 'required|max:255',
            'postal_code'       => 'required',
            'address'           => 'required|max:255',
            'phone_number'      => 'required|max:255|min:5',
            'resident_number'   => 'required|max:255|min:5',
        ];
    }
    protected function failedValidation(Validator $validator) {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
