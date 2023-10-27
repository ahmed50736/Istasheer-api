<?php

namespace App\Http\Requests;

use App\Http\Traits\SanitizeInput;
use Illuminate\Foundation\Http\FormRequest;

class CreateActionRequest extends BaseRequest
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
            'case_id' => 'required|uuid|exists:law_cases,id',
            'actionType' => 'required|string',
            'importance' => 'string',
            'startDate' => 'required|date_format:Y-m-d',
            'endDate' => 'required|date_format:Y-m-d',
            'attorney_ids' => 'sometimes|nullable|array',
            'attorney_ids.*' => 'sometimes|nullable|exists:users,id',
            'inform' => 'required|integer|in:0,1'
        ];
    }
}
