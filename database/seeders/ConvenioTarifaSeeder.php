<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// Seeder para inyectar las 56 tarifas de convenio con precios exactos
class ConvenioTarifaSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Limpiar todas las tarifas existentes antes de insertar
        DB::table('convenio_tarifas')->truncate();
        $this->command->info('Tarifas anteriores eliminadas.');

        $tarifas = [
            // Quálitas (convenio 1)
            [1, 1, 1, 'Arrastre con gancho',             'Local (Tipo A)',   520.00, 30, 17.50, 15, 25, 15, 150.00, 12, 'Escalonado'],
            [2, 1, 1, 'Arrastre con gancho',             'Foráneo (Tipo B)', 760.00, 30, 17.50, 15, 25, 15, 150.00, 12, 'Escalonado'],
            [3, 1, 2, 'Arrastre en plataforma hidráulica','Local (Tipo A)',   690.00, 30, 22.00, 15, 25, 15, 150.00, 12, 'Escalonado'],
            [4, 1, 2, 'Arrastre en plataforma hidráulica','Foráneo (Tipo B)',1010.00, 30, 22.00, 15, 25, 15, 150.00, 12, 'Escalonado'],
            [5, 1, 3, 'Asistencia vial',                 'Local (Tipo A)',   410.00,  0,  0.00, 15, 25, 15, 150.00, 12, 'Escalonado'],
            [6, 1, 3, 'Asistencia vial',                 'Foráneo (Tipo B)', 610.00,  0,  0.00, 15, 25, 15, 150.00, 12, 'Escalonado'],
            [7, 1, 4, 'Rescate',                          'Local (Tipo A)',  1150.00, 25, 28.00, 15, 25, 15, 150.00, 12, 'Escalonado'],
            [8, 1, 4, 'Rescate',                          'Foráneo (Tipo B)',1560.00, 25, 28.00, 15, 25, 15, 150.00, 12, 'Escalonado'],
            // GNP (convenio 2)
            [9,  2, 1, 'Arrastre con gancho',             'Local (Tipo A)',   610.00, 30, 21.00, 25, 35, 20, 185.00,  8, 'Fijo'],
            [10, 2, 1, 'Arrastre con gancho',             'Foráneo (Tipo B)', 890.00, 30, 21.00, 25, 35, 20, 185.00,  8, 'Fijo'],
            [11, 2, 2, 'Arrastre en plataforma hidráulica','Local (Tipo A)',   800.00, 30, 26.50, 25, 35, 20, 185.00,  8, 'Fijo'],
            [12, 2, 2, 'Arrastre en plataforma hidráulica','Foráneo (Tipo B)',1170.00, 30, 26.50, 25, 35, 20, 185.00,  8, 'Fijo'],
            [13, 2, 3, 'Asistencia vial',          'Local (Tipo A)',   470.00,  0,  0.00, 25, 35, 20, 185.00,  8, 'Fijo'],
            [14, 2, 3, 'Asistencia vial',          'Foráneo (Tipo B)', 700.00,  0,  0.00, 25, 35, 20, 185.00,  8, 'Fijo'],
            [15, 2, 4, 'Rescate',                          'Local (Tipo A)',  1330.00, 25, 33.00, 25, 35, 20, 185.00,  8, 'Fijo'],
            [16, 2, 4, 'Rescate',                          'Foráneo (Tipo B)',1810.00, 25, 33.00, 25, 35, 20, 185.00,  8, 'Fijo'],
            // AXA (convenio 3)
            [17, 3, 1, 'Arrastre con gancho',             'Local (Tipo A)',   650.00, 30, 22.50, 20, 30, 15, 195.00,  5, 'Fijo'],
            [18, 3, 1, 'Arrastre con gancho',             'Foráneo (Tipo B)', 950.00, 30, 22.50, 20, 30, 15, 195.00,  5, 'Fijo'],
            [19, 3, 2, 'Arrastre en plataforma hidráulica','Local (Tipo A)',   850.00, 30, 28.50, 20, 30, 15, 195.00,  5, 'Fijo'],
            [20, 3, 2, 'Arrastre en plataforma hidráulica','Foráneo (Tipo B)',1240.00, 30, 28.50, 20, 30, 15, 195.00,  5, 'Fijo'],
            [21, 3, 3, 'Asistencia vial',          'Local (Tipo A)',   500.00,  0,  0.00, 20, 30, 15, 195.00,  5, 'Fijo'],
            [22, 3, 3, 'Asistencia vial',          'Foráneo (Tipo B)', 745.00,  0,  0.00, 20, 30, 15, 195.00,  5, 'Fijo'],
            [23, 3, 4, 'Rescate',                          'Local (Tipo A)',  1420.00, 25, 35.50, 20, 30, 15, 195.00,  5, 'Fijo'],
            [24, 3, 4, 'Rescate',                          'Foráneo (Tipo B)',1930.00, 25, 35.50, 20, 30, 15, 195.00,  5, 'Fijo'],
            // Mapfre (convenio 4)
            [25, 4, 1, 'Arrastre con gancho',             'Local (Tipo A)',   560.00, 30, 18.70, 15, 25, 10, 160.00, 10, 'Escalonado'],
            [26, 4, 1, 'Arrastre con gancho',             'Foráneo (Tipo B)', 815.00, 30, 18.70, 15, 25, 10, 160.00, 10, 'Escalonado'],
            [27, 4, 2, 'Arrastre en plataforma hidráulica','Local (Tipo A)',   735.00, 30, 23.80, 15, 25, 10, 160.00, 10, 'Escalonado'],
            [28, 4, 2, 'Arrastre en plataforma hidráulica','Foráneo (Tipo B)',1075.00, 30, 23.80, 15, 25, 10, 160.00, 10, 'Escalonado'],
            [29, 4, 3, 'Asistencia vial',          'Local (Tipo A)',   435.00,  0,  0.00, 15, 25, 10, 160.00, 10, 'Escalonado'],
            [30, 4, 3, 'Asistencia vial',          'Foráneo (Tipo B)', 650.00,  0,  0.00, 15, 25, 10, 160.00, 10, 'Escalonado'],
            [31, 4, 4, 'Rescate',                          'Local (Tipo A)',  1225.00, 25, 29.80, 15, 25, 10, 160.00, 10, 'Escalonado'],
            [32, 4, 4, 'Rescate',                          'Foráneo (Tipo B)',1665.00, 25, 29.80, 15, 25, 10, 160.00, 10, 'Escalonado'],
            // Atlas (convenio 5)
            [33, 5, 1, 'Arrastre con gancho',             'Local (Tipo A)',   480.00, 30, 15.80, 10, 20, 10, 135.00,  6, 'Fijo'],
            [34, 5, 1, 'Arrastre con gancho',             'Foráneo (Tipo B)', 700.00, 30, 15.80, 10, 20, 10, 135.00,  6, 'Fijo'],
            [35, 5, 2, 'Arrastre en plataforma hidráulica','Local (Tipo A)',   630.00, 30, 20.20, 10, 20, 10, 135.00,  6, 'Fijo'],
            [36, 5, 2, 'Arrastre en plataforma hidráulica','Foráneo (Tipo B)', 920.00, 30, 20.20, 10, 20, 10, 135.00,  6, 'Fijo'],
            [37, 5, 3, 'Asistencia vial',          'Local (Tipo A)',   375.00,  0,  0.00, 10, 20, 10, 135.00,  6, 'Fijo'],
            [38, 5, 3, 'Asistencia vial',          'Foráneo (Tipo B)', 555.00,  0,  0.00, 10, 20, 10, 135.00,  6, 'Fijo'],
            [39, 5, 4, 'Rescate',                          'Local (Tipo A)',  1050.00, 25, 25.50, 10, 20, 10, 135.00,  6, 'Fijo'],
            [40, 5, 4, 'Rescate',                          'Foráneo (Tipo B)',1425.00, 25, 25.50, 10, 20, 10, 135.00,  6, 'Fijo'],
            // BBVA (convenio 6)
            [41, 6, 1, 'Arrastre con gancho',             'Local (Tipo A)',   700.00, 30, 24.50, 30, 40, 20, 210.00, 15, 'Escalonado'],
            [42, 6, 1, 'Arrastre con gancho',             'Foráneo (Tipo B)',1020.00, 30, 24.50, 30, 40, 20, 210.00, 15, 'Escalonado'],
            [43, 6, 2, 'Arrastre en plataforma hidráulica','Local (Tipo A)',   915.00, 30, 31.00, 30, 40, 20, 210.00, 15, 'Escalonado'],
            [44, 6, 2, 'Arrastre en plataforma hidráulica','Foráneo (Tipo B)',1335.00, 30, 31.00, 30, 40, 20, 210.00, 15, 'Escalonado'],
            [45, 6, 3, 'Asistencia vial',          'Local (Tipo A)',   540.00,  0,  0.00, 30, 40, 20, 210.00, 15, 'Escalonado'],
            [46, 6, 3, 'Asistencia vial',          'Foráneo (Tipo B)', 805.00,  0,  0.00, 30, 40, 20, 210.00, 15, 'Escalonado'],
            [47, 6, 4, 'Rescate',                          'Local (Tipo A)',  1530.00, 25, 38.00, 30, 40, 20, 210.00, 15, 'Escalonado'],
            [48, 6, 4, 'Rescate',                          'Foráneo (Tipo B)',2080.00, 25, 38.00, 30, 40, 20, 210.00, 15, 'Escalonado'],
            // Alianza (convenio 7)
            [49, 7, 1, 'Arrastre con gancho',             'Local (Tipo A)',   640.00, 30, 22.00, 20, 30, 15, 200.00,  7, 'Fijo'],
            [50, 7, 1, 'Arrastre con gancho',             'Foráneo (Tipo B)', 935.00, 30, 22.00, 20, 30, 15, 200.00,  7, 'Fijo'],
            [51, 7, 2, 'Arrastre en plataforma hidráulica','Local (Tipo A)',   840.00, 30, 28.00, 20, 30, 15, 200.00,  7, 'Fijo'],
            [52, 7, 2, 'Arrastre en plataforma hidráulica','Foráneo (Tipo B)',1225.00, 30, 28.00, 20, 30, 15, 200.00,  7, 'Fijo'],
            [53, 7, 3, 'Asistencia vial',          'Local (Tipo A)',   495.00,  0,  0.00, 20, 30, 15, 200.00,  7, 'Fijo'],
            [54, 7, 3, 'Asistencia vial',          'Foráneo (Tipo B)', 735.00,  0,  0.00, 20, 30, 15, 200.00,  7, 'Fijo'],
            [55, 7, 4, 'Rescate',                          'Local (Tipo A)',  1400.00, 25, 35.00, 20, 30, 15, 200.00,  7, 'Fijo'],
            [56, 7, 4, 'Rescate',                          'Foráneo (Tipo B)',1900.00, 25, 35.00, 20, 30, 15, 200.00,  7, 'Fijo'],
        ];

        foreach ($tarifas as $t) {
            DB::table('convenio_tarifas')->updateOrInsert(
                ['id' => $t[0]],
                [
                    'convenio_id' => $t[1],
                    'servicio_id' => $t[2],
                    'servicio' => $t[3],
                    'alcance' => $t[4],
                    'banderazo' => $t[5],
                    'km_incluidos' => $t[6],
                    'costo_km_extra' => $t[7],
                    'tarifa_nocturna_recargo_pct' => $t[8],
                    'tarifa_domingo_festivo_recargo_pct' => $t[9],
                    'minutos_espera_incluidos' => $t[10],
                    'costo_espera_adicional_hora' => $t[11],
                    'descuento_pct' => $t[12],
                    'tipo_descuento' => $t[13],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('ConvenioTarifaSeeder: 56 tarifas sincronizadas correctamente.');
    }
}
