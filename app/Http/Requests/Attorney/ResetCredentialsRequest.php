<?php

namespace App\Http\Requests\Attorney;

use App\Http\Requests\BaseRequest;
use App\Rules\IgonreCurrentUser;
use App\Rules\UserTypeCheck;
use Illuminate\Foundation\Http\FormRequest;

class ResetCredentialsRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => ['required', 'uuid', 'exists:users,id', new UserTypeCheck(2)],
            'username' => ['required', 'string', 'min:5', new IgonreCurrentUser(request('id')), 'regex:/^[A-Za-z0-9]+$/'],
            'password' => 'required|string|min:6',
            'confirm_password' => 'same:password'
        ];
    }
}
