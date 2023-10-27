<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class otp_management extends Model
{
    use HasFactory,UUID,SoftDeletes;

    public $timestamps =false;

    protected $fillable = [
        'otp',
        'uid',
        'otp_type',
        'phone_or_email',
        'create_time'
    ];

    protected $casts = [
        'create_time' => 'datetime',
    ];

    public function getDeleteTimeAttribute() {
        return Carbon::parse($this->create_time)->addMinutes(5);
    }

}
