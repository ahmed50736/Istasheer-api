<?php

namespace App\Http\Requests;

use App\Http\Traits\SanitizeInput;
use Illuminate\Foundation\Http\FormRequest;

class UpdateActionRequest extends BaseRequest
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
            'id' => 'required|exists:case_actions,id',
            'actionType' => 'required|string',
            'importance' => 'string',
            'startDate' => 'required|date_format:Y-m-d',
            'endDate' => 'required|date_format:Y-m-d',
            'decision' => 'sometimes|nullable|string',
            'note' => 'sometimes|nullable|string',
            'attorney_ids' => 'sometimes|nullable|array',
            'attorney_ids.*' => 'sometimes|nullable|exists:users,id',
        ];
    }
}
