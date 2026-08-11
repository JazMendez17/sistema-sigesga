<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

// Modelo de Usuario
class Usuario extends Authenticatable
{
    use HasApiTokens, Notifiable, HasFactory, SoftDeletes;

    protected $table = 'usuarios';

    protected $fillable = [
        'empresa_id',
        'empleado_id',
        'name',
        'email',
        'telefono',
        'password',
        'debe_cambiar_password',
        'foto',
        'password_reset_token',
        'password_reset_expires_at',
        'rol',
        'intentos_fallidos',
        'cuenta_bloqueada',
        'codigo_desbloqueo',
        'codigo_desbloqueo_expira',
        'bloqueada_en',
        'desbloqueada_por',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'password_reset_expires_at' => 'datetime',
            'bloqueada_en' => 'datetime',
            'codigo_desbloqueo_expira' => 'datetime',
            'cuenta_bloqueada' => 'boolean',
        ];
    }

    // Relación con empresa
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    // Relación con empleado
    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    // Relación con usuario que desbloqueó
    public function desbloqueadoPor()
    {
        return $this->belongsTo(Usuario::class, 'desbloqueado_por');
    }

    // Relación con cotizaciones
    public function cotizaciones()
    {
        return $this->hasMany(Cotizacione::class, 'usuario_creador_id');
    }

    // Relación con autorizaciones de cancelación como solicitante
    public function autorizacionesCancelacionSolicitante()
    {
        return $this->hasMany(AutorizacionesCancelacione::class, 'usuario_solicitante_id');
    }

    // Relación con autorizaciones de cancelación como resolutor
    public function autorizacionesCancelacionResolutor()
    {
        return $this->hasMany(AutorizacionesCancelacione::class, 'usuario_resolutor_id');
    }

    // Relación con notificaciones
    public function notificaciones()
    {
        return $this->hasMany(Notificacione::class, 'usuario_id');
    }

    // Relación con clientes
    public function clientes()
    {
        return $this->hasMany(Cliente::class, 'usuario_id');
    }

    public function hasRole($role): bool
    {
        return $this->rol === $role;
    }

    public function isAdmin(): bool
    {
        return $this->rol === 'admin';
    }

    public function isCotizador(): bool
    {
        return $this->rol === 'cotizador';
    }

    public function isOperador(): bool
    {
        return $this->rol === 'operador';
    }

    public function isCliente(): bool
    {
        return $this->rol === 'cliente';
    }
}
