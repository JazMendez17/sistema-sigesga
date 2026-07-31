<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Empresa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'empresas';

    protected $fillable = [
        'nombre',
        'siglas',
        'slogan',
        'logo',
        'imagen_fondo',
        'texto_derechos',
        'color_primario',
        'color_secundario',
        'color_fondo',
        'color_texto',
        'tipografia',
        'modo_oscuro',
        'telefono_contacto',
        'email_contacto',
    ];

    protected function casts(): array
    {
        return [
            'modo_oscuro' => 'boolean',
        ];
    }

    public function empresaNosotros()
    {
        return $this->hasOne(EmpresaNosotros::class, 'empresa_id');
    }

    public function empresaValores()
    {
        return $this->hasMany(EmpresaValore::class, 'empresa_id');
    }

    public function empresaAccesosRapidos()
    {
        return $this->hasMany(EmpresaAccesosRapido::class, 'empresa_id');
    }

    public function empresaServicios()
    {
        return $this->hasMany(EmpresaServicio::class, 'empresa_id');
    }

    public function empresaIntegraciones()
    {
        return $this->hasMany(EmpresaIntegracione::class, 'empresa_id');
    }

    public function oficinas()
    {
        return $this->hasMany(Oficina::class, 'empresa_id');
    }

    public function empleados()
    {
        return $this->hasMany(Empleado::class, 'empresa_id');
    }

    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'empresa_id');
    }

    public function operadores()
    {
        return $this->hasMany(Operadore::class, 'empresa_id');
    }

    public function unidades()
    {
        return $this->hasMany(Unidade::class, 'empresa_id');
    }

    public function catalogoServicios()
    {
        return $this->hasMany(CatalogoServicio::class, 'empresa_id');
    }

    public function aseguradoras()
    {
        return $this->hasMany(Aseguradora::class, 'empresa_id');
    }

    public function clientes()
    {
        return $this->hasMany(Cliente::class, 'empresa_id');
    }

    public function convenios()
    {
        return $this->hasMany(Convenio::class, 'empresa_id');
    }

    public function tarifasEmpresa()
    {
        return $this->hasMany(TarifasEmpresa::class, 'empresa_id');
    }

    public function cotizaciones()
    {
        return $this->hasMany(Cotizacione::class, 'empresa_id');
    }

    public function servicios()
    {
        return $this->hasMany(Servicio::class, 'empresa_id');
    }

    public function facturas()
    {
        return $this->hasMany(Factura::class, 'empresa_id');
    }

    public function notificaciones()
    {
        return $this->hasMany(Notificacione::class, 'empresa_id');
    }

    public function empresaModuloColores()
    {
        return $this->hasMany(EmpresaModuloColore::class, 'empresa_id');
    }

    public function unidadMantenimientos()
    {
        return $this->hasManyThrough(UnidadMantenimiento::class, Unidade::class, 'empresa_id', 'unidad_id');
    }
}
