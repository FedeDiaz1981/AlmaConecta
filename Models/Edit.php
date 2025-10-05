<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Edit extends Model
{
    use HasFactory;

    protected $fillable = ['profile_id','payload','status','reviewed_by','reviewed_at','reason'];

    protected $casts = [
        'payload' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function profile() {
        return $this->belongsTo(Profile::class);
    }

    public function reviewer() {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
