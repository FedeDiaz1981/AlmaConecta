<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','display_name','slug','about',
        'mode_presential','mode_remote',
        'country','state','city','address','lat','lng',
        'template_key','status','approved_at'
    ];

    protected $casts = [
        'mode_presential' => 'boolean',
        'mode_remote' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class)->orderBy('position');
    }
}
