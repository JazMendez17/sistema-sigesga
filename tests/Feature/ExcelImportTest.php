<?php

namespace Tests\Feature;

use App\Models\Aseguradora;
use App\Models\CatalogoServicio;
use App\Models\Convenio;
use App\Models\Empresa;
use App\Models\Usuario;
use App\Services\ImportaClientes;
use App\Services\ImportaConvenios;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ExcelImportTest extends TestCase
{
    use DatabaseMigrations;

    public function test_importa_cliente_desde_archivo_excel_con_columnas_reordenadas(): void
    {
        $empresa = Empresa::create(['nombre' => 'Empresa prueba']);
        $columnas = ImportaClientes::COLUMNAS;
        [$columnas[3], $columnas[6]] = [$columnas[6], $columnas[3]];
        $archivo = $this->excelConHojas([
            ImportaClientes::HOJA => [
                'columnas' => $columnas,
                'filas' => [[
                    'nombre' => 'María Elena',
                    'fecha_nacimiento' => '1985-01-01',
                    'tipo_cliente' => 'persona_fisica',
                ]],
            ],
        ]);

        $respuesta = $this->actingAs(Usuario::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => 'admin',
        ]))->post('/panel/clientes/importar', ['archivo' => $archivo]);

        $respuesta->assertSessionHas('importacion', fn ($resultado) =>
            $resultado['creados'] === 1 && $resultado['errores'] === []
        );
        $cliente = \App\Models\Cliente::where('empresa_id', $empresa->id)->where('nombre', 'María Elena')->firstOrFail();
        $this->assertSame('1985-01-01', $cliente->fecha_nacimiento->format('Y-m-d'));
        $this->assertSame('persona_fisica', $cliente->tipo_cliente);
    }

    public function test_importa_convenio_y_hojas_multiples_desde_el_machote(): void
    {
        $empresa = Empresa::create(['nombre' => 'Empresa prueba']);
        $aseguradora = Aseguradora::create(['empresa_id' => $empresa->id, 'nombre' => 'GNP Seguros']);
        CatalogoServicio::create(['empresa_id' => $empresa->id, 'nombre' => 'Grúa plataforma']);

        $hojas = [];
        foreach (ImportaConvenios::COLUMNAS as $nombre => $columnas) {
            $hojas[$nombre] = ['columnas' => $columnas, 'filas' => []];
        }
        $hojas[ImportaConvenios::HOJA_PRINCIPAL]['filas'] = [[
            'codigo_convenio' => 'CONV-GNP-2026',
            'nombre_convenio_poliza' => 'Convenio GNP 2026',
            'aseguradora' => $aseguradora->nombre,
            'tipo_servicio' => 'Grúa plataforma',
            'tipo_ruta' => 'local',
            'tipo_cobertura' => 'Amplia',
            'alcance_geografico' => 'Zona metropolitana',
            'costo_banderazo' => 850,
            'costo_km' => 42.5,
            'tope_presupuesto' => 150000,
            'km_maximo_amparado' => 300,
            'dias_credito' => 30,
        ]];

        $respuesta = $this->actingAs(Usuario::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => 'admin',
        ]))->post('/panel/convenios/importar', ['archivo' => $this->excelConHojas($hojas)]);

        $respuesta->assertSessionHas('importacion', fn ($resultado) =>
            $resultado['creados'] === 1 && $resultado['errores'] === []
        );
        $this->assertDatabaseHas('convenios', [
            'empresa_id' => $empresa->id,
            'aseguradora_id' => $aseguradora->id,
            'codigo_convenio' => 'CONV-GNP-2026',
            'nombre_convenio_poliza' => 'Convenio GNP 2026',
        ]);
    }

    private function excelConHojas(array $hojas): UploadedFile
    {
        $libro = new Spreadsheet();
        $libro->removeSheetByIndex(0);

        foreach ($hojas as $nombre => $definicion) {
            $hoja = $libro->createSheet();
            $hoja->setTitle($nombre);
            $columnas = $definicion['columnas'];
            foreach ($columnas as $indice => $columna) {
                $letra = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($indice + 1);
                $hoja->setCellValue($letra . '2', $columna['etiqueta'] . (!empty($columna['requerido']) ? ' *' : ''));
            }
            foreach ($definicion['filas'] as $offset => $fila) {
                foreach ($columnas as $indice => $columna) {
                    $letra = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($indice + 1);
                    $hoja->setCellValue($letra . ($offset + 4), $fila[$columna['campo']] ?? null);
                }
            }
        }

        $ruta = tempnam(sys_get_temp_dir(), 'sigesga-import-');
        (new Xlsx($libro))->save($ruta);
        $contenido = file_get_contents($ruta);
        unlink($ruta);

        return UploadedFile::fake()->createWithContent('importacion.xlsx', $contenido);
    }
}
