<?php

namespace App\Http\Requests;

use App\Http\Traits\SanitizeInput;
use Illuminate\Foundation\Http\FormRequest;

class DisibleUserRequest extends BaseRequest
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
            'from' => 'required|date_format:Y-m-d H:i:s|today_or_future',
            'to' => 'required|date_format:Y-m-d H:i:s',
            'user_id' => 'required|uuid|exists:users,id'
        ];
    }
}
