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

        // modalidad
        'mode_presential',
        'mode_remote',

        // ubicación
        'country',
        'state',
        'city',
        'address',
        'location_label',   // texto exacto elegido por el provider
        'lat',
        'lng',

        // presentación / otros
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

    // Servicios antiguos (compatibilidad)
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'profile_service');
    }

    // Muchas especialidades a través de profile_specialty
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
