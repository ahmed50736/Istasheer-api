<?php

namespace App\Http\Requests;

use App\Http\Traits\SanitizeInput;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BaseRequest extends FormRequest
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

    public function validated()
    {
        $validatedData = parent::validated();

        // Sanitize all input data before returning the validated data
        return $this->sanitizeArray($validatedData);
    }

    protected function sanitizeArray(array $data)
    {
        // Recursively sanitize all values in the array
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->sanitizeArray($value);
            } else {
                $data[$key] = $this->sanitizeInput($value);
            }
        }

        return $data;
    }
}
