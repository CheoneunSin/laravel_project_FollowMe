<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PatientRegister extends FormRequest
{
    public function rules()
    {
        return [
            'patient_name'      => 'required|max:255',
            'phone_number'      => 'required|max:255|min:5',
            'login_id'          => 'required|max:255|min:5|unique:patients',
            'password'          => 'required|max:20|min:3|confirmed',
            'resident_number'   => 'required|max:255|min:5',
        ];
    }
    protected function failedValidation(Validator $validator) {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
