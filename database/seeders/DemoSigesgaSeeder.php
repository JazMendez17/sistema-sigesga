<?php

// Datos de demostración para recorrer el sistema y grabar el video.
// Todo es idempotente: puede ejecutarse varias veces sin duplicar registros.

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoSigesgaSeeder extends Seeder
{
    public function run(): void
    {
        $empresaId = DB::table('empresas')->value('id');
        $oficinaId = DB::table('oficinas')->value('id');
        $adminId = DB::table('usuarios')->where('email', 'admin@sigesga.com')->value('id');
        $cotizadorId = DB::table('usuarios')->where('email', 'cotizador@sigesga.com')->value('id') ?? $adminId;
        $clienteUserId = DB::table('usuarios')->where('email', 'cliente@sigesga.com')->value('id');
        $operadorId = DB::table('operadores')->value('id');

        // Dirección completa del administrador para "Mi Cuenta"
        $direccionAdminId = DB::table('direcciones')->insertGetId([
            'calle' => 'Av. Paseo de la Reforma',
            'numero_exterior' => '222',
            'numero_interior' => 'Piso 8',
            'colonia' => 'Juárez',
            'codigo_postal' => '06600',
            'municipio_alcaldia' => 'Cuauhtémoc',
            'ciudad' => 'Ciudad de México',
            'estado' => 'Ciudad de México',
            'pais' => 'México',
            'referencias' => 'Entre Insurgentes y Bucareli. Edificio corporativo.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('empleados')
            ->where('empresa_id', $empresaId)
            ->where('puesto', 'admin')
            ->update([
                'direccion_id' => $direccionAdminId,
                'telefono_local' => '55 1234 5678',
                'nacionalidad' => 'Mexicana',
                'updated_at' => now(),
            ]);

        // El usuario cliente se vincula al primer cliente para ver sus cotizaciones
        $primerClienteId = DB::table('clientes')->where('empresa_id', $empresaId)->orderBy('id')->value('id');
        if ($clienteUserId && $primerClienteId) {
            DB::table('clientes')->where('id', $primerClienteId)->update(['usuario_id' => $clienteUserId]);
        }

        // Unidades
        $unidad1 = DB::table('unidades')->updateOrInsert(
            ['empresa_id' => $empresaId, 'placas' => 'ABC-1234'],
            [
                'oficina_id' => $oficinaId,
                'operador_asignado_id' => $operadorId,
                'marca' => 'Freightliner',
                'tipo' => 'Grúa de plataforma',
                'modelo' => '2022',
                'numero_economico' => 'ECO-001',
                'seguro_vencimiento' => Carbon::now()->addMonths(8)->toDateString(),
                'estado_emplacado' => 'Ciudad de México',
                'activo' => true,
                'eliminado' => false,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        DB::table('unidades')->updateOrInsert(
            ['empresa_id' => $empresaId, 'placas' => 'XYZ-9876'],
            [
                'oficina_id' => $oficinaId,
                'operador_asignado_id' => null,
                'marca' => 'Isuzu',
                'tipo' => 'Grúa con gancho',
                'modelo' => '2021',
                'numero_economico' => 'ECO-002',
                'seguro_vencimiento' => Carbon::now()->addMonths(3)->toDateString(),
                'estado_emplacado' => 'Estado de México',
                'activo' => true,
                'eliminado' => false,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $unidad1Id = DB::table('unidades')->where('placas', 'ABC-1234')->value('id');
        $unidad2Id = DB::table('unidades')->where('placas', 'XYZ-9876')->value('id');

        $clientes = DB::table('clientes')->where('empresa_id', $empresaId)->orderBy('id')->pluck('id');
        if ($clientes->count() < 3) {
            return;
        }

        $base = [
            'empresa_id' => $empresaId,
            'usuario_creador_id' => $cotizadorId,
            'convenio_aplicado_id' => null,
            'tarifa_empresa_aplicada_id' => null,
            'incluye_peajes' => false,
            'costo_aprox_casetas' => 0,
            'cargo_zona_especial' => 0,
            'km_excedente' => 0,
            'descuento_pct' => 0,
            'monto_descuento' => 0,
            'ruta_polyline' => null,
        ];

        $cotizaciones = [
            [
                'id' => 1, 'folio' => 'COT-00001', 'cliente_id' => $clientes[0], 'tipo_servicio_id' => 1,
                'origen_direccion' => 'Av. Insurgentes Sur 452, Roma Norte, CDMX',
                'origen_lat' => 19.4106, 'origen_lng' => -99.1632,
                'destino_direccion' => 'Calzada de Tlalpan 1200, Portales, CDMX',
                'destino_lat' => 19.3798, 'destino_lng' => -99.1465,
                'distancia_km' => 18.40, 'tiempo_estimado_minutos' => 35,
                'costo_banderazo' => 450, 'costo_km' => 32, 'costo_total' => 1038.80,
                'subtotal' => 895.52, 'monto_iva' => 143.28, 'km_incluidos' => 10,
                'estatus' => 'pendiente',
            ],
            [
                'id' => 2, 'folio' => 'COT-00002', 'cliente_id' => $clientes[1], 'tipo_servicio_id' => 2,
                'origen_direccion' => 'Benito Juárez 12, Centro, Toluca',
                'origen_lat' => 19.2925, 'origen_lng' => -99.6557,
                'destino_direccion' => 'Av. Paseo de la Reforma 105, Juárez, CDMX',
                'destino_lat' => 19.4299, 'destino_lng' => -99.1660,
                'distancia_km' => 32.70, 'tiempo_estimado_minutos' => 55,
                'costo_banderazo' => 850, 'costo_km' => 45, 'costo_total' => 2321.50,
                'subtotal' => 2001.29, 'monto_iva' => 320.21, 'km_incluidos' => 20,
                'estatus' => 'aprobado', 'cliente_aprobada_at' => now()->subDays(2),
                'aprobada_internamente_at' => now()->subDays(2),
            ],
            [
                'id' => 3, 'folio' => 'COT-00003', 'cliente_id' => $clientes[2], 'tipo_servicio_id' => 4,
                'origen_direccion' => 'José María Morelos 88, San Ángel, CDMX',
                'origen_lat' => 19.3467, 'origen_lng' => -99.1908,
                'destino_direccion' => 'Calzada de Tlalpan 4567, Portales, CDMX',
                'destino_lat' => 19.3629, 'destino_lng' => -99.1478,
                'distancia_km' => 12.20, 'tiempo_estimado_minutos' => 28,
                'costo_banderazo' => 600, 'costo_km' => 38, 'costo_total' => 1063.60,
                'subtotal' => 916.90, 'monto_iva' => 146.70, 'km_incluidos' => 8,
                'estatus' => 'aprobado', 'cliente_aprobada_at' => now()->subDays(6),
                'aprobada_internamente_at' => now()->subDays(6),
            ],
            [
                'id' => 4, 'folio' => 'COT-00004', 'cliente_id' => $clientes[0], 'tipo_servicio_id' => 3,
                'origen_direccion' => 'Av. Universidad 3000, Coyoacán, CDMX',
                'origen_lat' => 19.3317, 'origen_lng' => -99.1854,
                'destino_direccion' => 'Periférico Sur 4348, Jardines del Pedregal, CDMX',
                'destino_lat' => 19.3177, 'destino_lng' => -99.1909,
                'distancia_km' => 8.60, 'tiempo_estimado_minutos' => 20,
                'costo_banderazo' => 380, 'costo_km' => 28, 'costo_total' => 620.80,
                'subtotal' => 535.17, 'monto_iva' => 85.63, 'km_incluidos' => 5,
                'estatus' => 'aprobado', 'cliente_aprobada_at' => now()->subDay(),
                'aprobada_internamente_at' => now()->subDay(),
            ],
            [
                'id' => 5, 'folio' => 'COT-00005', 'cliente_id' => $clientes[4] ?? $clientes[0], 'tipo_servicio_id' => 2,
                'origen_direccion' => 'Calzada de Tlalpan 1200, Portales Sur, CDMX',
                'origen_lat' => 19.3655, 'origen_lng' => -99.1467,
                'destino_direccion' => 'Carretera México-Querétaro Km 32, Cuautitlán',
                'destino_lat' => 19.6742, 'destino_lng' => -99.1797,
                'distancia_km' => 44.50, 'tiempo_estimado_minutos' => 70,
                'costo_banderazo' => 950, 'costo_km' => 48, 'costo_total' => 3086.00,
                'subtotal' => 2660.34, 'monto_iva' => 425.66, 'km_incluidos' => 25,
                'estatus' => 'pendiente',
            ],
            [
                'id' => 6, 'folio' => 'COT-00006', 'cliente_id' => $clientes[0], 'tipo_servicio_id' => 1,
                'origen_direccion' => 'Av. Revolución 1500, Mixcoac, CDMX',
                'origen_lat' => 19.3762, 'origen_lng' => -99.1874,
                'destino_direccion' => 'Av. Insurgentes Norte 123, Guerrero, CDMX',
                'destino_lat' => 19.4533, 'destino_lng' => -99.1621,
                'distancia_km' => 15.80, 'tiempo_estimado_minutos' => 32,
                'costo_banderazo' => 450, 'costo_km' => 32, 'costo_total' => 955.60,
                'subtotal' => 823.79, 'monto_iva' => 131.81, 'km_incluidos' => 10,
                'estatus' => 'aprobado', 'cliente_aprobada_at' => now()->subHours(5),
                'aprobada_internamente_at' => now()->subHours(5),
            ],
        ];

        foreach ($cotizaciones as $data) {
            $id = $data['id'];
            unset($data['id']);
            DB::table('cotizaciones')->updateOrInsert(
                ['id' => $id],
                array_merge($base, $data, ['eliminado' => false, 'created_at' => now()->subDays(8 - $id), 'updated_at' => now()])
            );
        }

        // Servicio finalizado con factura y calificación
        DB::table('servicios')->updateOrInsert(
            ['cotizacion_id' => 3],
            [
                'empresa_id' => $empresaId,
                'operador_id' => $operadorId,
                'unidad_id' => $unidad1Id,
                'oficina_id' => $oficinaId,
                'estado' => 'finalizado',
                'kms_salida' => 45210,
                'kms_llegada_cliente' => 45218,
                'kms_termino_servicio' => 45230,
                'kms_regreso_base' => 45235,
                'kms_cobrados_reales' => 20,
                'cargo_zona_especial' => 0,
                'costo_final_real' => 1063.60,
                'observaciones' => 'Servicio concluido sin incidencias. Vehículo entregado en el destino.',
                'eliminado' => false,
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(4),
            ]
        );

        // Servicio en curso (etapas del operador)
        DB::table('servicios')->updateOrInsert(
            ['cotizacion_id' => 6],
            [
                'empresa_id' => $empresaId,
                'operador_id' => $operadorId,
                'unidad_id' => $unidad2Id,
                'oficina_id' => $oficinaId,
                'estado' => 'en_sitio_origen',
                'kms_salida' => 12840,
                'kms_llegada_cliente' => null,
                'kms_termino_servicio' => null,
                'kms_regreso_base' => null,
                'kms_cobrados_reales' => null,
                'cargo_zona_especial' => 0,
                'costo_final_real' => null,
                'observaciones' => 'Unidad en el punto de origen, esperando confirmación del cliente.',
                'eliminado' => false,
                'created_at' => now()->subHours(3),
                'updated_at' => now(),
            ]
        );

        $servicioFinalizadoId = DB::table('servicios')->where('cotizacion_id', 3)->value('id');
        $servicioEnCursoId = DB::table('servicios')->where('cotizacion_id', 6)->value('id');

        DB::table('bitacora_tiempos_servicio')->updateOrInsert(
            ['servicio_id' => $servicioFinalizadoId],
            [
                'hora_asignado' => now()->subDays(5)->setTime(8, 10),
                'hora_inicio_servicio' => now()->subDays(5)->setTime(8, 20),
                'hora_en_sitio_origen' => now()->subDays(5)->setTime(8, 45),
                'hora_salida_destino' => now()->subDays(5)->setTime(9, 0),
                'hora_en_destino' => now()->subDays(5)->setTime(9, 40),
                'hora_finalizado' => now()->subDays(5)->setTime(10, 5),
            ]
        );

        DB::table('bitacora_tiempos_servicio')->updateOrInsert(
            ['servicio_id' => $servicioEnCursoId],
            [
                'hora_asignado' => now()->subHours(3)->setTime(11, 0),
                'hora_inicio_servicio' => now()->subHours(3)->setTime(11, 5),
                'hora_en_sitio_origen' => now()->subHours(2)->setTime(11, 35),
                'hora_salida_destino' => null,
                'hora_en_destino' => null,
                'hora_finalizado' => null,
            ]
        );

        DB::table('facturas')->updateOrInsert(
            ['servicio_id' => $servicioFinalizadoId],
            [
                'empresa_id' => $empresaId,
                'cliente_id' => $clientes[2],
                'uuid_fiscal' => 'A1B2C3D4-5E6F-7890-ABCD-EF1234567890',
                'folio_factura' => 'FAC-00001',
                'correo_envio_factura' => DB::table('clientes')->where('id', $clientes[2])->value('email') ?? 'cliente@sigesga.com',
                'subtotal' => 916.90,
                'iva' => 146.70,
                'total' => 1063.60,
                'xml_url' => null,
                'pdf_url' => null,
                'estatus' => 'vigente',
                'eliminado' => false,
                'created_at' => now()->subDays(4),
                'updated_at' => now()->subDays(4),
            ]
        );

        DB::table('calificaciones_servicio')->updateOrInsert(
            ['servicio_id' => $servicioFinalizadoId],
            [
                'cliente_id' => $clientes[2],
                'estrellas' => 5,
                'comentario' => 'Excelente servicio, el operador llegó rápido y fue muy profesional.',
                'created_at' => now()->subDays(4),
            ]
        );

        // Mantenimientos con alertas próximas
        DB::table('unidad_mantenimientos')->updateOrInsert(
            ['unidad_id' => $unidad1Id, 'fecha' => now()->subMonth()->toDateString()],
            [
                'empresa_id' => $empresaId,
                'tipo' => 'Preventivo',
                'kilometraje' => 45000,
                'costo' => 3850.00,
                'proximo_mantenimiento_fecha' => now()->addDays(12)->toDateString(),
                'proximo_mantenimiento_km' => 50000,
                'observaciones' => 'Cambio de aceite, filtros y revisión de frenos.',
                'created_at' => now()->subMonth(),
                'eliminado' => false,
            ]
        );

        DB::table('unidad_mantenimientos')->updateOrInsert(
            ['unidad_id' => $unidad2Id, 'fecha' => now()->subDays(20)->toDateString()],
            [
                'empresa_id' => $empresaId,
                'tipo' => 'Correctivo',
                'kilometraje' => 12500,
                'costo' => 2100.00,
                'proximo_mantenimiento_fecha' => now()->addDays(4)->toDateString(),
                'proximo_mantenimiento_km' => 15000,
                'observaciones' => 'Reparación del sistema hidráulico del gancho.',
                'created_at' => now()->subDays(20),
                'eliminado' => false,
            ]
        );

        // Notificaciones del administrador
        if ($adminId) {
            DB::table('notificaciones')->where('usuario_id', $adminId)->where('eliminado', true)->update(['eliminado' => false]);
            $existentes = DB::table('notificaciones')->where('usuario_id', $adminId)->count();
            if ($existentes === 0) {
                DB::table('notificaciones')->insert([
                    [
                        'empresa_id' => $empresaId,
                        'usuario_id' => $adminId,
                        'mensaje' => 'Cotización COT-00006 aprobada por el cliente. Lista para asignar servicio.',
                        'canal' => 'sistema_push',
                        'estado' => 'enviado',
                        'intentos_envio' => 0,
                        'eliminado' => false,
                        'created_at' => now()->subHours(5),
                        'updated_at' => now()->subHours(5),
                    ],
                    [
                        'empresa_id' => $empresaId,
                        'usuario_id' => $adminId,
                        'mensaje' => 'Servicio SVC-00002 en sitio de origen (operador Roberto Méndez).',
                        'canal' => 'sistema_push',
                        'estado' => 'enviado',
                        'intentos_envio' => 0,
                        'eliminado' => false,
                        'created_at' => now()->subHours(2),
                        'updated_at' => now()->subHours(2),
                    ],
                    [
                        'empresa_id' => $empresaId,
                        'usuario_id' => $adminId,
                        'mensaje' => 'Mantenimiento de la unidad XYZ-9876 programado en 4 días.',
                        'canal' => 'sistema_push',
                        'estado' => 'pendiente',
                        'intentos_envio' => 0,
                        'eliminado' => false,
                        'created_at' => now()->subDay(),
                        'updated_at' => now()->subDay(),
                    ],
                ]);
            }
        }

        $this->command->info('DemoSigesgaSeeder: datos de demostración listos.');
    }
}
