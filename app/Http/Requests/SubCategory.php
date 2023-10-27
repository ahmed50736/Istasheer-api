<?php

namespace App\Http\Requests;

use App\Http\Traits\SanitizeInput;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SubCategory extends BaseRequest
{


    public function rules()
    {
        return [
            'id' => 'sometimes|required|exists:case_sub_categories,id',
            'category_id' => 'required|exists:case_categories,id',
            'sub_category_title_english' => 'required|string|min:3',
            'sub_category_title_arabic' => 'required|string|min:2',
            'price' => 'required|numeric|min:1',
            'case_type' => 'required|in:individual,corporate'
        ];
    }
}
