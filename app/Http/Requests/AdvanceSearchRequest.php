<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdvanceSearchRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'client_name' => 'sometimes|nullable|string',
            'case_no' => 'sometimes|nullable|string',
            'automated_no' => 'sometimes|nullable|string',
            'order_no' => 'sometimes|nullable|string',
            'opposition' => 'sometimes|nullable|string'
        ];
    }
}
