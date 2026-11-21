<?php

namespace App\Services;

use App\Models\Servicio;
use InvalidArgumentException;

class FacturaCalculator
{
    /** @return array{subtotal: float, iva: float, total: float, km: float, km_incluidos: float, km_excedentes: float, costo_km: float, banderazo: float, cargo_zona_especial: float, descuento: float, casetas: float} */
    public function calcular(Servicio $servicio): array
    {
        $cotizacion = $servicio->cotizacion;
        if (!$cotizacion) {
            throw new InvalidArgumentException('El servicio no tiene una cotización asociada.');
        }

        // Prioridad: km cobrados reales explícitos -> diferencia de odómetro -> distancia estimada de cotización
        $kmDiferenciaOdometro = (
            $servicio->kms_salida !== null
            && $servicio->kms_termino_servicio !== null
            && $servicio->kms_termino_servicio >= $servicio->kms_salida
        ) ? ($servicio->kms_termino_servicio - $servicio->kms_salida) : null;

        $km = (float) ($servicio->kms_cobrados_reales
            ?? $kmDiferenciaOdometro
            ?? $cotizacion->distancia_km
            ?? 0);

        $kmIncluidos = (float) ($cotizacion->km_incluidos ?? 0);
        $kmExcedentes = max(0, $km - $kmIncluidos);
        $banderazo = (float) ($cotizacion->costo_banderazo ?? 0);
        $costoKm = (float) ($cotizacion->costo_km ?? 0);

        $cargoServicio = (float) ($servicio->cargo_zona_especial ?? 0);
        $cargoZona = $cargoServicio > 0
            ? $cargoServicio
            : (float) ($cotizacion->cargo_zona_especial ?? 0);

        $bruto = $banderazo + ($kmExcedentes * $costoKm) + $cargoZona;
        $porcentajeDescuento = max(0, min(100, (float) ($cotizacion->descuento_pct ?? 0)));
        $subtotal = round(max($banderazo, $bruto * (1 - $porcentajeDescuento / 100)), 2);
        $casetas = (bool) ($cotizacion->incluye_peajes ?? false)
            ? 0.0
            : (float) ($cotizacion->costo_aprox_casetas ?? 0);
        $iva = round($subtotal * 0.16, 2);

        return [
            'subtotal' => $subtotal,
            'iva' => $iva,
            'total' => round($subtotal + $iva + $casetas, 2),
            'km' => $km,
            'km_incluidos' => $kmIncluidos,
            'km_excedentes' => $kmExcedentes,
            'costo_km' => $costoKm,
            'banderazo' => $banderazo,
            'cargo_zona_especial' => $cargoZona,
            'descuento' => round(max(0, $bruto - $subtotal), 2),
            'casetas' => $casetas,
        ];
    }
}
