<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    protected $fillable = ['profile_id','type','url','position'];

    public function profile() {
        return $this->belongsTo(Profile::class);
    }
}
