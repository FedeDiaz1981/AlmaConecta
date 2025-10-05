<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Specialty extends Model
{
    protected $fillable = ['name','slug','active'];

    public function profiles(): HasMany
    {
        return $this->hasMany(Profile::class);
    }
}
