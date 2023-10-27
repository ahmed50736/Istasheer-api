<?php

namespace App\Services;

use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class VoucherService
{
    public function createOrUpdateVoucher(array $data): object
    {
        $setData = $this->setDataforUpdateOrCreate($data);
        if (isset($data['id'])) {

            $voucher = $this->updateVoucher($setData);
        } else {
            $voucher = $this->createVoucher($setData);
        }
        return $voucher;
    }

    public function updateVoucher(array $updateData): object
    {

        $voucher = Voucher::where('id', $updateData['id'])->first();
        $voucher->update($updateData);
        return $voucher->refresh();
    }

    public function createVoucher(array $createData): object
    {
        return Voucher::create($createData);
    }

    public function setDataforUpdateOrCreate(array $data): array
    {
        $user = auth()->user();
        $voucherSetData = [];
        $voucherSetData['name'] = $data['name'];
        $voucherSetData['voucher_number'] = $data['code'];
        $voucherSetData['amount'] = $data['amount'];
        $voucherSetData['type'] = $data['type'];

        if (!isset($data['id'])) {
            $voucherSetData['createTime'] = Carbon::now()->format('Y-m-d');
            $voucherSetData['created_by'] = $user->id;
        } else {
            $voucherSetData['updated_by'] = $user->id;
            $voucherSetData['id'] = $data['id'];
        }

        $voucherSetData['endTime'] = $data['expire_date'];

        return $voucherSetData;
    }
}
