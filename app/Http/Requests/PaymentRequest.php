<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseRequest;

class PaymentRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'case_id' => 'required|exists:law_cases,id',
            'amount' => 'required|numeric|min:1',
            'extra_service_id' => 'sometimes|nullable|exists:extra_services,id'
        ];
    }
}
