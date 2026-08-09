<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// Seeder para inyectar 70 maniobras especiales (10 por convenio)
class ConvenioManiobraEspecialSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $maniobras = [
            // Quálitas (convenio 1)
            [1, 'Motocicletas', true, 'tarifa fija', 350.00],
            [1, 'Vehículos pesados (camiones/autobuses)', false, 'N/A', 0.00],
            [1, 'Maquinaria / equipo especial', false, 'N/A', 0.00],
            [1, 'Volcadura', true, 'por maniobra', 1100.00],
            [1, 'Rescate en barranco / zanja', true, 'por maniobra', 1600.00],
            [1, 'Doble tracción / maniobra especial', true, 'por maniobra', 800.00],
            [1, 'Cambio de llanta', true, 'incluido en asistencia vial', 0.00],
            [1, 'Paso de corriente', true, 'incluido en asistencia vial', 0.00],
            [1, 'Suministro de gasolina (hasta 5 lts)', true, 'tarifa fija + costo de gasolina', 130.00],
            [1, 'Cerrajería menor', true, 'tarifa fija', 300.00],
            // GNP (convenio 2)
            [2, 'Motocicletas', true, 'tarifa fija', 400.00],
            [2, 'Vehículos pesados (camiones/autobuses)', true, 'tarifa fija mayor', 2800.00],
            [2, 'Maquinaria / equipo especial', true, 'cotización caso por caso', 0.00],
            [2, 'Volcadura', true, 'por maniobra', 1200.00],
            [2, 'Rescate en barranco / zanja', true, 'por maniobra', 1750.00],
            [2, 'Doble tracción / maniobra especial', true, 'por maniobra', 900.00],
            [2, 'Cambio de llanta', true, 'incluido en asistencia vial', 0.00],
            [2, 'Paso de corriente', true, 'incluido en asistencia vial', 0.00],
            [2, 'Suministro de gasolina (hasta 5 lts)', true, 'tarifa fija + costo de gasolina', 150.00],
            [2, 'Cerrajería menor', true, 'tarifa fija', 350.00],
            // AXA (convenio 3)
            [3, 'Motocicletas', true, 'tarifa fija', 380.00],
            [3, 'Vehículos pesados (camiones/autobuses)', false, 'N/A', 0.00],
            [3, 'Maquinaria / equipo especial', false, 'N/A', 0.00],
            [3, 'Volcadura', true, 'por maniobra', 1150.00],
            [3, 'Rescate en barranco / zanja', true, 'por maniobra', 1700.00],
            [3, 'Doble tracción / maniobra especial', true, 'por maniobra', 850.00],
            [3, 'Cambio de llanta', true, 'incluido en asistencia vial', 0.00],
            [3, 'Paso de corriente', true, 'incluido en asistencia vial', 0.00],
            [3, 'Suministro de gasolina (hasta 5 lts)', true, 'tarifa fija + costo de gasolina', 140.00],
            [3, 'Cerrajería menor', true, 'tarifa fija', 320.00],
            // Mapfre (convenio 4)
            [4, 'Motocicletas', true, 'tarifa fija', 330.00],
            [4, 'Vehículos pesados (camiones/autobuses)', false, 'N/A', 0.00],
            [4, 'Maquinaria / equipo especial', false, 'N/A', 0.00],
            [4, 'Volcadura', true, 'por maniobra', 1020.00],
            [4, 'Rescate en barranco / zanja', true, 'por maniobra', 1530.00],
            [4, 'Doble tracción / maniobra especial', true, 'por maniobra', 760.00],
            [4, 'Cambio de llanta', true, 'incluido en asistencia vial', 0.00],
            [4, 'Paso de corriente', true, 'incluido en asistencia vial', 0.00],
            [4, 'Suministro de gasolina (hasta 5 lts)', true, 'tarifa fija + costo de gasolina', 125.00],
            [4, 'Cerrajería menor', true, 'tarifa fija', 295.00],
            // Atlas (convenio 5)
            [5, 'Motocicletas', true, 'tarifa fija', 450.00],
            [5, 'Vehículos pesados (camiones/autobuses)', false, 'N/A', 0.00],
            [5, 'Maquinaria / equipo especial', false, 'N/A', 0.00],
            [5, 'Volcadura', true, 'por maniobra', 890.00],
            [5, 'Rescate en barranco / zanja', false, 'N/A', 0.00],
            [5, 'Doble tracción / maniobra especial', true, 'por maniobra', 650.00],
            [5, 'Cambio de llanta', true, 'incluido en asistencia vial', 0.00],
            [5, 'Paso de corriente', true, 'incluido en asistencia vial', 0.00],
            [5, 'Suministro de gasolina (hasta 5 lts)', true, 'tarifa fija + costo de gasolina', 110.00],
            [5, 'Cerrajería menor', true, 'tarifa fija', 260.00],
            // BBVA (convenio 6)
            [6, 'Motocicletas', true, 'tarifa fija', 420.00],
            [6, 'Vehículos pesados (camiones/autobuses)', true, 'tarifa fija mayor', 2950.00],
            [6, 'Maquinaria / equipo especial', true, 'cotización caso por caso', 0.00],
            [6, 'Volcadura', true, 'por maniobra', 1260.00],
            [6, 'Rescate en barranco / zanja', true, 'por maniobra', 1890.00],
            [6, 'Doble tracción / maniobra especial', true, 'por maniobra', 945.00],
            [6, 'Cambio de llanta', true, 'incluido en asistencia vial', 0.00],
            [6, 'Paso de corriente', true, 'incluido en asistencia vial', 0.00],
            [6, 'Suministro de gasolina (hasta 5 lts)', true, 'tarifa fija + costo de gasolina', 170.00],
            [6, 'Cerrajería menor', true, 'tarifa fija', 395.00],
            // Alianza (convenio 7)
            [7, 'Motocicletas', true, 'tarifa fija', 420.00],
            [7, 'Vehículos pesados (camiones/autobuses)', true, 'tarifa fija mayor', 2700.00],
            [7, 'Maquinaria / equipo especial', true, 'cotización caso por caso', 0.00],
            [7, 'Volcadura', true, 'por maniobra', 1150.00],
            [7, 'Rescate en barranco / zanja', true, 'por maniobra', 1720.00],
            [7, 'Doble tracción / maniobra especial', true, 'por maniobra', 860.00],
            [7, 'Cambio de llanta', true, 'incluido en asistencia vial', 0.00],
            [7, 'Paso de corriente', true, 'incluido en asistencia vial', 0.00],
            [7, 'Suministro de gasolina (hasta 5 lts)', true, 'tarifa fija + costo de gasolina', 150.00],
            [7, 'Cerrajería menor', true, 'tarifa fija', 350.00],
        ];

        foreach ($maniobras as $m) {
            DB::table('convenio_maniobras_especiales')->updateOrInsert(
                ['convenio_id' => $m[0], 'concepto' => $m[1]],
                ['aplica' => $m[2], 'forma_cobro' => $m[3], 'costo' => $m[4]]
            );
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('ConvenioManiobraEspecialSeeder: 70 maniobras sincronizadas correctamente.');
    }
}
