<?php

namespace App\Http\Requests;

use App\Http\Traits\SanitizeInput;
use App\Rules\UserRuleUsername;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginValidation extends BaseRequest
{
    use SanitizeInput;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'username' => ['required', new UserRuleUsername],
            'device_uid' => 'sometimes|nullable',
            'device_os' => 'sometimes|nullable',
            'fcm_token' => 'sometimes|nullable',
            'password' => 'required|string|min:6',
        ];
    }
}
