<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Specialty extends Model
{
    // La tabla en la BD se llama "specialities"
    protected $table = 'specialities';

    protected $fillable = [
        'name',
        'slug',
        'active',
    ];

    public $timestamps = false; // ponelo en true si la tabla tiene created_at/updated_at

    public function profiles(): BelongsToMany
    {
        return $this->belongsToMany(Profile::class)->withTimestamps();
    }
}
