<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActionAttorneyAssign extends Model
{
    use HasFactory, UUID, SoftDeletes;

    protected $table = 'action_attorney_assigns';

    protected $fillable = [
        'action_id',
        'attorney_id',
        'created_at',
        'deleted_at',
        'updated_at'
    ];

    public function action()
    {
        return $this->belongsTo(caseAction::class, 'action_id', 'id');
    }

    public function attorney()
    {
        return $this->belongsTo(User::class, 'attorney_id', 'id')->select('users.name', 'users.id');
    }
}
