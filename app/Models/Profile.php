<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'display_name',
        'slug',
        'about',
        'mode_presential',
        'mode_remote',
        'country',
        'state',
        'city',
        'address',
        'lat',
        'lng',
        'template_key',
        'status',
        'approved_at',
        'whatsapp',
        'contact_email',
        'photo_path',
        'video_url',
    ];

    protected $casts = [
        'mode_presential' => 'boolean',
        'mode_remote'     => 'boolean',
        'approved_at'     => 'datetime',
        'lat'             => 'float',
        'lng'             => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Servicios antiguos (lo dejamos por compatibilidad, aunque el select está oculto)
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'profile_service');
    }

    // NUEVO: muchas especialidades a través de profile_specialty
    public function specialties(): BelongsToMany
    {
        return $this->belongsToMany(Specialty::class, 'profile_specialty')
                    ->withTimestamps();
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class)->orderBy('position');
    }
}
