<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use App\Rules\AdminUsernameRule;

class AdminLoginRequest extends BaseRequest
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'username' => ['required', new AdminUsernameRule],
            'device_uid' => 'sometimes|nullable',
            'device_os' => 'sometimes|nullable',
            'fcm_token' => 'sometimes|nullable',
            'password' => 'required|string|min:6',
        ];
    }
}
