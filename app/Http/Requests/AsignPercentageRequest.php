<?php

namespace App\Http\Requests;

use App\Http\Traits\SanitizeInput;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AsignPercentageRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => 'sometimes|required|exists:asign_attorney_percentages,id',
            'attorney_id' => 'required|exists:users,id,user_type,2',
            'subcategory_id' => 'required|exists:case_sub_categories,id',
            'admin_percentage' => 'required|numeric|min:1'
        ];
    }
}
