<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Voucher extends Model
{
    use HasFactory, UUID, SoftDeletes;

    public $timestamps = false;

    protected $table = 'vouchers';

    protected $fillable = [
        'name',
        'voucher_number',
        'amount',
        'type',
        'createTime',
        'endTime',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * get voucher list with pagination
     * @param array $searchParam
     * @return object
     */
    public static function getVoucherListWithPagination(array $searchParam): object
    {
        $voucherList = self::select(DB::raw('
            id as id,
            name as name,
            amount as voucher_amount,
            createTime as created_at,
            endTime as expire_date,
            deleted_at as deleted_at
        '));

        if (isset($searchParam['search']) && $searchParam['search'] != null) { //searching by name
            $voucherList->where('name', 'like', '%' . $searchParam['search'] . '%');
        }

        $voucherList = $voucherList->groupBy('id')->paginate(15);

        return $voucherList;
    }
}
