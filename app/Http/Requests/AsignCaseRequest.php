<?php

namespace App\Http\Requests;

use App\Http\Traits\SanitizeInput;
use App\Rules\UserTypeCheck;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AsignCaseRequest extends BaseRequest
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
            'case_id' => 'required|exists:law_cases,id',
            'attorney_id' => ['required', 'exists:users,id', new UserTypeCheck(2)],
            'deadline' => 'required|date_format:Y-m-d|after:today',
            'notify_user' => ['sometimes', 'required', 'boolean', 'default' => 0],
            'due_date' => 'required|date_format:Y-m-d|after:today'
        ];
    }
}
