<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RemoveAttorneyFromCase extends BaseRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'case_id' => 'required|exists:asigne_cases,case_id,deleted_at,NULL',
            'attorney_id' => 'required|exists:asigne_cases,attorney_id'
        ];
    }
}
