<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeleteFileRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'file_ids' => 'required|array',
            'file_ids.*' => [
                'required',
                Rule::exists('medias', 'id')->where(function ($query) {
                    $query->whereNull('deleted_at');
                }),
            ]
        ];
    }
}
