<?php

namespace App\Http\Requests;

use App\Http\Traits\SanitizeInput;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserRegistration extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|min:2|max:100',
            'username' => 'required|string|min:5|unique:users,username|regex:/^[A-Za-z0-9]+$/',
            'email' => 'sometimes|nullable|unique:users,email',
            'password' => 'required|string|min:6',
            'confirm_password' => 'required|same:password',
            'phone_no' => 'required|string|size:8|unique:users,phone_no|regex:/^[569]\d{7}$/',
            'notes' => 'sometimes|nullable|string|max:255',
            'other_info' => 'sometimes|nullable|string|max:255'
        ];
    }
}
