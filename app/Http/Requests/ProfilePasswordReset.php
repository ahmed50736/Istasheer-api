<?php

namespace App\Http\Requests;

use App\Http\Traits\SanitizeInput;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProfilePasswordReset extends FormRequest
{
    use SanitizeInput;

    public function expectsJson()
    {
        return true;
    }

    public function wantsJson()
    {
        return true;
    }
    protected function failedValidation(Validator $validator)
    {

        throw new HttpResponseException(response()->json([
            'status' => false,
            'status_code' => 422,
            'message' => $validator->errors()->first(),
            'data' => []
        ], 422));
    }
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'current_password' => 'required|string|min:6',
            'new_password' => 'sometimes|nullable|required_with:current_password|string|min:6',
            'confirm_password' => 'sometimes|nullable|required_with:current_password|same:new_password',
        ];
    }
}
