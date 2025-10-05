<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = [
        'user_id',
        'display_name',
        'slug',
        'service_id', // <---
        'about',
        'video_url',
        'template_key',
        'mode_remote',
        'mode_presential',
        'country',
        'state',
        'city',
        'address',
        'lat',
        'lng',
        'photo_path',
        'status',
        'whatsapp',
        'contact_email',
    ];

    protected $casts = [
        'mode_remote' => 'boolean',
        'mode_presential' => 'boolean',
        'lat' => 'float',
        'lng' => 'float',
    ];

    // Relaciones
    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // N:M con services. Usa la tabla pivote `profile_service` (convención Laravel).
    public function service()
    {
        return $this->belongsTo(Service::class);
    }


    // 1:N con media (ajustá el modelo si tu clase se llama distinto)
    public function media()
    {
        return $this->hasMany(ProfileMedia::class);
    }
}
