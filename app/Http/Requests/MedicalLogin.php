<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class MedicalLogin extends FormRequest
{

    public function rules()
    {
        return [
            'email'             => 'required|email|exists:users',
            'password'          => 'required|min:3',
        ];
    }
    protected function failedValidation(Validator $validator) {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
