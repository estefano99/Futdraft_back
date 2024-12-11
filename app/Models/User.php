<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Traits\PersonaTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, PersonaTrait, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'apellido',
        'password',
        'email',
        'nro_celular',
        'dni',
        'estado',
        'tipo_usuario'
    ];

    //Atributo temporal para el password usarlo en el observer
    public $temporalPassword;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function grupos()
    {
        return $this->belongsToMany(Grupo::class, 'grupo_usuario', 'user_id', 'grupo_id');
    }

    public function acciones()
    {
        return $this->belongsToMany(Accion::class, 'usuario_accion', 'usu_id', 'acc_id');
    }

    public function scopeActivos($query) {
        return $query->where('estado', 1);
    }
    public function scopeInactivos($query) {
        return $query->where('estado', 0);
    }
}
