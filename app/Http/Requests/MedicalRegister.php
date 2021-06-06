<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class MedicalRegister extends FormRequest
{
    public function rules()
    {
        return [
            'email'             => 'required|email|unique:users',
            'password'          => 'required|min:3|confirmed',
            'phone_number'      => 'required',
            'name'              => 'required',
            'unique_number'     => 'required|unique:users',
        ];
    }
    protected function failedValidation(Validator $validator) {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }

}
