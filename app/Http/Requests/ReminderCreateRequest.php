<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReminderCreateRequest extends BaseRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'case_id' => 'required|uuid|exists:law_cases,id',
            'attorney_id' => 'required|uuid|exists:users,id',
            'note' => 'required|string|max:255',
        ];
    }
}
