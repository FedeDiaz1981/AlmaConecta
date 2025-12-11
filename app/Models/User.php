<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'account_status',
        'approved_at',
        'rejected_at',
        'suspended_at',
        'activated_at',
        'reject_reason',
        'suspend_reason',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'approved_at'       => 'datetime',
        'rejected_at'       => 'datetime',
        'suspended_at'      => 'datetime',
        'activated_at'      => 'datetime',
    ];

    /**
     * Perfil profesional asociado al usuario.
     * Tabla: profiles, FK: user_id
     */
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }
}
