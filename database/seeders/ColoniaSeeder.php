<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ColoniaSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = database_path('seeders/colonias_data.json');

        if (!file_exists($jsonPath)) {
            $this->command?->error('colonias_data.json not found at: ' . $jsonPath);
            return;
        }

        $raw = file_get_contents($jsonPath);
        $data = json_decode($raw, true);

        if (!is_array($data)) {
            $this->command?->error('Failed to decode colonias_data.json');
            return;
        }

        $rows = [];

        foreach ($data as $cp => $colonias) {
            if (!is_array($colonias)) {
                continue;
            }
            foreach ($colonias as $colonia) {
                $rows[] = [
                    'codigo_postal' => $cp,
                    'colonia'       => $colonia['colonia'] ?? '',
                    'municipio'     => $colonia['municipio'] ?? '',
                    'estado'        => $colonia['estado'] ?? '',
                ];
            }
        }

        $chunks = array_chunk($rows, 500);

        foreach ($chunks as $chunk) {
            DB::table('colonias')->insertOrIgnore($chunk);
        }

        $this->command?->info("Seeded " . count($rows) . " colonias from colonias_data.json");
    }
}
