<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Specialty extends Model
{
    protected $fillable = ['name', 'slug', 'active', 'is_featured','featured_image_path'];
    protected $casts = [
        'active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function profiles(): HasMany
    {
        return $this->hasMany(Profile::class);
    }
}
