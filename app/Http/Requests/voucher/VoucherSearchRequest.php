<?php

namespace App\Http\Requests\voucher;

use App\Http\Requests\BaseRequest;

class VoucherSearchRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'search' => 'nullable|string'
        ];
    }
}
