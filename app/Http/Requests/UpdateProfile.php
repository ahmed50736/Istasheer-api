<?php

namespace App\Http\Requests;

use App\Rules\MatchOldPassword;
use App\Http\Traits\SanitizeInput;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class UpdateProfile extends BaseRequest
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $id = Auth::id();
        return [
            'name' => 'required|string|min:3',
            'gender' => 'sometimes|nullable|in:male,female,other',
            'dob' => 'sometimes|nullable|date_format:Y-m-d',
            'phone_no' => 'required|string|size:8|unique:users,phone_no,' . $id . ',id|regex:/^[569]\d{7}$/'
        ];
    }
}
