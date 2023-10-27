<?php

namespace App\Http\Requests;

use App\Http\Traits\SanitizeInput;
use Illuminate\Foundation\Http\FormRequest;

class NotificationRequest extends BaseRequest
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
            'user_id' => 'required|exists:users,id',
            'message' => 'required|string|max:255',
            'title' => 'sometimes|required|max:55'
        ];
    }
}
