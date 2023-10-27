<?php

namespace App\Http\Requests;

use App\Rules\AttorneyUsernameRule;
use Illuminate\Foundation\Http\FormRequest;

class AttorneyLoginRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'username' => ['required', new AttorneyUsernameRule],
            'device_uid' => 'sometimes|nullable',
            'device_os' => 'sometimes|nullable',
            'fcm_token' => 'sometimes|nullable',
            'password' => 'required|string|min:6',
        ];
    }
}
