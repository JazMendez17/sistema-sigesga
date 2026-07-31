<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmpresaSeeder extends Seeder
{
    public function run(): void
    {
        $empresa = DB::table('empresas')->where('nombre', 'Grúas y Equipos del Valle, S.A. de C.V.')->first();

        if ($empresa) {
            return;
        }

        $empresaId = DB::table('empresas')->insertGetId([
            'nombre' => 'Grúas y Equipos del Valle, S.A. de C.V.',
            'siglas' => 'GEV',
            'slogan' => 'Asistencia vial conectada: del mapa a la ruta en un solo clic',
            'logo' => null,
            'imagen_fondo' => null,
            'texto_derechos' => '© 2026 Grúas y Equipos del Valle, S.A. de C.V. Todos los derechos reservados.',
            'color_primario' => '#4F46E5',
            'color_secundario' => '#7C3AED',
            'color_fondo' => '#E8EDF2',
            'color_texto' => '#1F2937',
            'modo_oscuro' => false,
            'telefono_contacto' => '55-1234-5678',
            'email_contacto' => 'contacto@gevsigesga.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('empresa_nosotros')->insert([
            'empresa_id' => $empresaId,
            'quienes_somos' => 'Somos una empresa líder en asistencia vial y servicios de grúas en el Valle de México. Con más de 10 años de experiencia, ofrecemos soluciones rápidas, seguras y eficientes para el traslado de vehículos y maquinaria pesada.',
            'mision' => 'Proporcionar soluciones de asistencia vial y transporte especializado con los más altos estándares de calidad, seguridad y rapidez, superando las expectativas de nuestros clientes.',
            'vision' => 'Ser la empresa de referencia en asistencia vial y servicios de grúa en México, reconocida por nuestra innovación tecnológica, compromiso con la seguridad y excelencia en el servicio al cliente.',
            'prioridad' => 'La seguridad de nuestros clientes, operadores y unidades es nuestra prioridad absoluta. Cada servicio se planifica y ejecuta bajo estrictos protocolos de seguridad vial.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $valores = [
            ['empresa_id' => $empresaId, 'valor' => 'Seguridad', 'descripcion' => 'Priorizamos la integridad de nuestros colaboradores, clientes y unidades en cada operación.', 'orden' => 1],
            ['empresa_id' => $empresaId, 'valor' => 'Responsabilidad', 'descripcion' => 'Cumplimos con nuestros compromisos de manera puntual y profesional.', 'orden' => 2],
            ['empresa_id' => $empresaId, 'valor' => 'Eficiencia', 'descripcion' => 'Optimizamos cada servicio para ofrecer tiempos de respuesta rápidos y efectivos.', 'orden' => 3],
        ];
        DB::table('empresa_valores')->insert($valores);

        $servicios = [
            ['empresa_id' => $empresaId, 'tipo' => 'Servicio de Grúa Ligera', 'descripcion' => 'Para vehículos compactos, sedanes y camionetas ligeras. Servicio rápido y seguro en calles y avenidas de la ciudad.', 'orden' => 1],
            ['empresa_id' => $empresaId, 'tipo' => 'Servicio de Grúa Pesada', 'descripcion' => 'Para camiones de carga, autobuses y maquinaria pesada. Equipo especializado para maniobras complejas.', 'orden' => 2],
        ];
        DB::table('empresa_servicios')->insert($servicios);

        $modulos = [
            ['empresa_id' => $empresaId, 'modulo' => 'dashboard', 'color' => '#4F46E5'],
            ['empresa_id' => $empresaId, 'modulo' => 'cotizaciones', 'color' => '#059669'],
            ['empresa_id' => $empresaId, 'modulo' => 'servicios', 'color' => '#D97706'],
            ['empresa_id' => $empresaId, 'modulo' => 'clientes', 'color' => '#7C3AED'],
            ['empresa_id' => $empresaId, 'modulo' => 'unidades', 'color' => '#2563EB'],
            ['empresa_id' => $empresaId, 'modulo' => 'mantenimientos', 'color' => '#DC2626'],
            ['empresa_id' => $empresaId, 'modulo' => 'empleados', 'color' => '#0891B2'],
            ['empresa_id' => $empresaId, 'modulo' => 'operadores', 'color' => '#7C3AED'],
            ['empresa_id' => $empresaId, 'modulo' => 'usuarios', 'color' => '#4F46E5'],
            ['empresa_id' => $empresaId, 'modulo' => 'configuracion', 'color' => '#6B7280'],
            ['empresa_id' => $empresaId, 'modulo' => 'reportes', 'color' => '#059669'],
            ['empresa_id' => $empresaId, 'modulo' => 'notificaciones', 'color' => '#D97706'],
            ['empresa_id' => $empresaId, 'modulo' => 'facturacion', 'color' => '#2563EB'],
            ['empresa_id' => $empresaId, 'modulo' => 'oficinas', 'color' => '#0891B2'],
        ];
        DB::table('empresa_modulo_colores')->insert($modulos);
    }
}
