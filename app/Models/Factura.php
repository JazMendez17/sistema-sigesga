<?php

namespace App\Models;

use App\Models\Concerns\Ocultable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Modelo de Factura
class Factura extends Model
{
        use Ocultable;
use HasFactory;

    protected $fillable = [
        'empresa_id',
        'cliente_id',
        'servicio_id',
        'uuid_fiscal',
        'folio_factura',
        'correo_envio_factura',
        'subtotal',
        'iva',
        'total',
        'xml_url',
        'pdf_url',
        'estatus',
    ];

    // RelaciÃ³n con empresa
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    // RelaciÃ³n con cliente
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    // RelaciÃ³n con servicio
    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class);
    }
}
