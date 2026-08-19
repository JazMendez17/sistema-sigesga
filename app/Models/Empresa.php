<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// Modelo de Empresa
class Empresa extends Model
{
    use HasFactory;

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

    // RelaciÃ³n con nosotros de empresa
    public function empresaNosotros()
    {
        return $this->hasOne(EmpresaNosotros::class, 'empresa_id');
    }

    // RelaciÃ³n con valores de empresa
    public function empresaValores()
    {
        return $this->hasMany(EmpresaValore::class, 'empresa_id');
    }

    // RelaciÃ³n con accesos rÃ¡pidos de empresa
    public function empresaAccesosRapidos()
    {
        return $this->hasMany(EmpresaAccesosRapido::class, 'empresa_id');
    }

    // RelaciÃ³n con servicios de empresa
    public function empresaServicios()
    {
        return $this->hasMany(EmpresaServicio::class, 'empresa_id');
    }

    // RelaciÃ³n con integraciones de empresa
    public function empresaIntegraciones()
    {
        return $this->hasMany(EmpresaIntegracione::class, 'empresa_id');
    }

    // RelaciÃ³n con oficinas
    public function oficinas()
    {
        return $this->hasMany(Oficina::class, 'empresa_id');
    }

    // RelaciÃ³n con empleados
    public function empleados()
    {
        return $this->hasMany(Empleado::class, 'empresa_id');
    }

    // RelaciÃ³n con usuarios
    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'empresa_id');
    }

    // RelaciÃ³n con operadores
    public function operadores()
    {
        return $this->hasMany(Operadore::class, 'empresa_id');
    }

    // RelaciÃ³n con unidades
    public function unidades()
    {
        return $this->hasMany(Unidade::class, 'empresa_id');
    }

    // RelaciÃ³n con catÃ¡logo de servicios
    public function catalogoServicios()
    {
        return $this->hasMany(CatalogoServicio::class, 'empresa_id');
    }

    // RelaciÃ³n con aseguradoras
    public function aseguradoras()
    {
        return $this->hasMany(Aseguradora::class, 'empresa_id');
    }

    // RelaciÃ³n con clientes
    public function clientes()
    {
        return $this->hasMany(Cliente::class, 'empresa_id');
    }

    // RelaciÃ³n con convenios
    public function convenios()
    {
        return $this->hasMany(Convenio::class, 'empresa_id');
    }

    // RelaciÃ³n con tarifas de empresa
    public function tarifasEmpresa()
    {
        return $this->hasMany(TarifasEmpresa::class, 'empresa_id');
    }

    // RelaciÃ³n con cotizaciones
    public function cotizaciones()
    {
        return $this->hasMany(Cotizacione::class, 'empresa_id');
    }

    // RelaciÃ³n con servicios
    public function servicios()
    {
        return $this->hasMany(Servicio::class, 'empresa_id');
    }

    // RelaciÃ³n con facturas
    public function facturas()
    {
        return $this->hasMany(Factura::class, 'empresa_id');
    }

    // RelaciÃ³n con notificaciones
    public function notificaciones()
    {
        return $this->hasMany(Notificacione::class, 'empresa_id');
    }

    // RelaciÃ³n con colores de mÃ³dulos
    public function empresaModuloColores()
    {
        return $this->hasMany(EmpresaModuloColore::class, 'empresa_id');
    }

    // RelaciÃ³n con mantenimientos de unidades
    public function unidadMantenimientos()
    {
        return $this->hasManyThrough(UnidadMantenimiento::class, Unidade::class, 'empresa_id', 'unidad_id');
    }
}
