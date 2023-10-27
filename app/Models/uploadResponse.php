<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class uploadResponse extends Model
{
    use HasFactory, UUID, SoftDeletes;

    public $timestamps = false;

    protected $fillable = [
        'status',
        'submissionTime',
        'case_id',
        'note',
        'attorney_id'
    ];

    public function responseFiles()
    {
        $user = auth()->user();
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return $this->hasMany(caseresponse::class, 'case_id', 'id')->withTrashed();
        } else {
            return $this->hasMany(caseresponse::class, 'case_id', 'id');
        }
    }
}
