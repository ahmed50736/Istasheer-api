<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\hearings;

class HearingAttorneys extends Model
{
    use HasFactory, UUID, SoftDeletes;

    protected $table = 'hearing_attorneys';

    protected $fillable = ['hearing_id', 'attorney_id', 'created_at', 'updated_at'];

    public function hearing()
    {
        return $this->belongsTo(hearings::class, 'hearing_id', 'id')->select('hearings.id');
    }

    public function attorney()
    {
        return $this->belongsTo(User::class, 'attorney_id', 'id')->select('users.name', 'users.id');
    }
}
