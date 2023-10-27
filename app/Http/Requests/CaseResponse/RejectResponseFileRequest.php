<?php

namespace App\Http\Requests\CaseResponse;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class RejectResponseFileRequest extends BaseRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'file_id' => 'required|uuid|exists:medias,id',
            'reason' => 'required|string|max:255',
            'notify_client' => 'required|boolean'
        ];
    }
}
