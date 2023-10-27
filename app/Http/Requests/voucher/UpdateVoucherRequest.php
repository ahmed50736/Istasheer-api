<?php

namespace App\Http\Requests\voucher;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class UpdateVoucherRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        return [
            'id' => 'required|exists:vouchers,id',
            'name' => 'sometimes|nullable|string',
            'code' => 'required|unique:vouchers,voucher_number,' . $this->input('id'),
            'amount' => 'required|numeric',
            'type' => 'required|in:single,multiple',
            'expire_date' => 'required|date_format:Y-m-d|after:today'
        ];
    }
}
