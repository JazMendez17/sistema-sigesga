<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

// Modelo de Integración de Empresa
class EmpresaIntegracione extends Model
{
    use HasFactory;

    protected $table = 'empresa_integraciones';

    protected $fillable = [
        'empresa_id',
        'proveedor',
        'api_key',
        'webhook_secret',
        'configuracion_json',
        'activo',
    ];

    protected $hidden = [
        'api_key',
        'webhook_secret',
    ];

    protected function casts(): array
    {
        return [
            'configuracion_json' => 'array',
            'activo' => 'boolean',
        ];
    }

    /** Cifra `api_key` al guardar y descifra con tolerancia a valores heredados. */
    protected function apiKey(): Attribute
    {
        return Attribute::make(
            get: function (?string $value): ?string {
                if ($value === null || $value === '') {
                    return null;
                }
                try {
                    return Crypt::decryptString($value);
                } catch (DecryptException) {
                    return $value;
                }
            },
            set: fn (?string $value): ?string => ($value === null || $value === '')
                ? null
                : Crypt::encryptString($value),
        );
    }

    protected function webhookSecret(): Attribute
    {
        return Attribute::make(
            get: function (?string $value): ?string {
                if ($value === null || $value === '') {
                    return null;
                }

                try {
                    return Crypt::decryptString($value);
                } catch (DecryptException) {
                    return $value;
                }
            },
            set: fn (?string $value): ?string => ($value === null || $value === '')
                ? null
                : Crypt::encryptString($value),
        );
    }

    // Relación con empresa
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
