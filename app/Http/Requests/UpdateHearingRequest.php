<?php

namespace App\Http\Requests;

use App\Http\Traits\SanitizeInput;
use Illuminate\Foundation\Http\FormRequest;

class UpdateHearingRequest extends BaseRequest
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
            'id' => 'required|exists:hearings,id',
            'case_id' => 'required|uuid|exists:law_cases,id',
            'session_type' => 'required|string',
            'date' => 'date_format:Y-m-d',
            'time' => 'date_format:H:i:s',
            'decission' => 'required|string',
            'note' => 'string',
            'inform' => 'sometimes|integer|in:0,1',
            'attorney_ids' => 'sometimes|nullable|array',
            'attorney_ids.*' => 'sometimes|nullable|exists:users,id'
        ];
    }
}
