<?php

// Genera machotes .xlsx y lee archivos Excel subidos para las importaciones del panel.

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MachoteExcel
{
    public const TIPO_TEXTO = 'texto';
    public const TIPO_NUMERO = 'numero';
    public const TIPO_FECHA = 'fecha';
    public const TIPO_BOOLEANO = 'booleano';

    /**
     * Descarga un machote con una hoja por cada entrada de $hojas.
     *
     * Cada hoja: ['titulo' => ..., 'columnas' => [...], 'notas' => [...]]
     * Cada columna: ['campo', 'etiqueta', 'requerido', 'ejemplo', 'opciones', 'tipo', 'ancho', 'ayuda']
     */
    public static function descargar(string $nombreArchivo, array $hojas): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(11);

        foreach ($hojas as $nombreHoja => $definicion) {
            self::pintarHoja($spreadsheet->createSheet(), $nombreHoja, $definicion);
        }

        $spreadsheet->removeSheetByIndex(0);

        // Las pestañas de ayuda van al final para que las de captura queden juntas.
        $ayudas = [];
        for ($i = $spreadsheet->getSheetCount() - 1; $i >= 0; $i--) {
            $hoja = $spreadsheet->getSheet($i);
            if (str_ends_with($hoja->getTitle(), ' ayuda')) {
                $ayudas[] = $hoja;
                $spreadsheet->removeSheetByIndex($i);
            }
        }
        foreach (array_reverse($ayudas) as $hoja) {
            $spreadsheet->addSheet($hoja);
        }

        $spreadsheet->setActiveSheetIndex(0);

        return response()->streamDownload(
            fn () => (new Xlsx($spreadsheet))->save('php://output'),
            $nombreArchivo,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    private static function pintarHoja(Worksheet $hoja, string $nombre, array $definicion): void
    {
        $columnas = $definicion['columnas'];
        $notas = $definicion['notas'] ?? [];
        $ultimaLetra = Coordinate::stringFromColumnIndex(count($columnas));

        $hoja->setTitle($nombre);
        $hoja->setCellValue('A1', $definicion['titulo'] ?? $nombre);
        $hoja->mergeCells("A1:{$ultimaLetra}1");
        $hoja->getStyle('A1')->getFont()->setBold(true)->setSize(13)->getColor()->setRGB('FFFFFF');
        $hoja->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('4F46E5');
        $hoja->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $hoja->getRowDimension(1)->setRowHeight(24);

        foreach ($columnas as $i => $columna) {
            $letra = Coordinate::stringFromColumnIndex($i + 1);
            $etiqueta = $columna['etiqueta'];
            if (!empty($columna['requerido'])) {
                $etiqueta .= ' *';
            }
            $hoja->setCellValue("{$letra}2", $etiqueta);
            $hoja->getColumnDimension($letra)->setWidth($columna['ancho'] ?? 22);

            $hoja->getStyle("{$letra}3")->getFont()->setItalic(true)->setSize(10)->getColor()->setRGB('6B7280');
            if (isset($columna['ejemplo'])) {
                $hoja->setCellValueExplicit(
                    "{$letra}3",
                    (string) $columna['ejemplo'],
                    DataType::TYPE_STRING
                );
            }
        }

        $rangoEncabezados = "A2:{$ultimaLetra}2";
        $hoja->getStyle($rangoEncabezados)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $hoja->getStyle($rangoEncabezados)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('111827');
        $hoja->getStyle($rangoEncabezados)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);
        $hoja->getRowDimension(2)->setRowHeight(30);

        // Las filas de datos empiezan en la 4: la 3 es un ejemplo que debe borrarse.
        $hoja->getStyle("A4:{$ultimaLetra}203")->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F9FAFB');

        self::agregarValidaciones($hoja, $columnas);
        self::agregarNotas($hoja, $columnas, $notas);

        $hoja->freezePane('A4');
    }

    /** Desplegados para enums y formato para fechas, sobre las filas de datos. */
    private static function agregarValidaciones(Worksheet $hoja, array $columnas): void
    {
        foreach ($columnas as $i => $columna) {
            $letra = Coordinate::stringFromColumnIndex($i + 1);
            $rango = "{$letra}4:{$letra}203";

            if (!empty($columna['opciones'])) {
                $validacion = new DataValidation();
                $validacion->setType(DataValidation::TYPE_LIST)
                    ->setAllowBlank(true)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setErrorTitle('Valor no válido')
                    ->setError('Debes elegir uno de los valores de la lista.')
                    ->setFormula1('"' . implode(',', $columna['opciones']) . '"');
                $hoja->setDataValidation($rango, $validacion);
            }

            match ($columna['tipo'] ?? self::TIPO_TEXTO) {
                // Formato texto: evita que Excel pierda ceros a la izquierda en teléfonos y CP.
                self::TIPO_TEXTO => $hoja->getStyle($rango)->getNumberFormat()->setFormatCode('@'),
                self::TIPO_FECHA => $hoja->getStyle($rango)->getNumberFormat()->setFormatCode('yyyy-mm-dd'),
                default => null,
            };
        }
    }

    /** Segunda hoja de referencia con el detalle de cada columna. */
    private static function agregarNotas(Worksheet $hoja, array $columnas, array $notas): void
    {
        if ($notas === []) {
            return;
        }

        $libro = $hoja->getParent();
        $ayuda = $libro->createSheet();
        $ayuda->setTitle(mb_substr($hoja->getTitle(), 0, 25) . ' ayuda');

        $ayuda->setCellValue('A1', 'Columna');
        $ayuda->setCellValue('B1', 'Obligatorio');
        $ayuda->setCellValue('C1', 'Formato');
        $ayuda->setCellValue('D1', 'Valores permitidos');
        $ayuda->setCellValue('E1', 'Descripción');
        $ayuda->getStyle('A1:E1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $ayuda->getStyle('A1:E1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('111827');

        $fila = 2;
        foreach ($columnas as $columna) {
            $ayuda->setCellValue("A{$fila}", $columna['etiqueta']);
            $ayuda->setCellValue("B{$fila}", !empty($columna['requerido']) ? 'Sí' : 'No');
            $ayuda->setCellValue("C{$fila}", self::describeFormato($columna));
            $ayuda->setCellValue("D{$fila}", isset($columna['opciones']) ? implode(', ', $columna['opciones']) : '—');
            $ayuda->setCellValue("E{$fila}", $columna['ayuda'] ?? '');
            $fila++;
        }

        $fila++;
        $ayuda->setCellValue("A{$fila}", 'Notas');
        $ayuda->getStyle("A{$fila}")->getFont()->setBold(true);
        $fila++;
        foreach ($notas as $nota) {
            $ayuda->setCellValue("A{$fila}", $nota);
            $fila++;
        }

        foreach (['A' => 26, 'B' => 13, 'C' => 24, 'D' => 34, 'E' => 60] as $letra => $ancho) {
            $ayuda->getColumnDimension($letra)->setWidth($ancho);
        }
        $ayuda->getStyle("A2:E{$fila}")->getAlignment()->setWrapText(true);
        $ayuda->getStyle("B2:B{$fila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    private static function describeFormato(array $columna): string
    {
        return match ($columna['tipo'] ?? self::TIPO_TEXTO) {
            self::TIPO_NUMERO => 'Número',
            self::TIPO_FECHA => 'Fecha (aaaa-mm-dd)',
            self::TIPO_BOOLEANO => 'Sí / No',
            default => 'Texto',
        };
    }

    /**
     * Lee el archivo subido y devuelve las filas de datos indexadas por 'campo'.
     * Cada fila incluye '__fila' con el número de fila real del Excel para el reporte de errores.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function leer(UploadedFile $archivo, array $columnas, string $nombreHoja): array
    {
        return self::leerHoja(self::abrir($archivo), $columnas, $nombreHoja);
    }

    /** Abre el libro una sola vez para leer varias hojas sin recargarlo. */
    public static function abrir(UploadedFile $archivo): Spreadsheet
    {
        $ruta = $archivo->getRealPath() ?: $archivo->getPathname();

        try {
            return IOFactory::load($ruta);
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'archivo' => 'El archivo Excel no es válido o está dañado.',
            ]);
        }
    }

    /** @return array<int, array<string, mixed>> */
    public static function leerHoja(Spreadsheet $libro, array $columnas, string $nombreHoja): array
    {
        $hoja = $libro->getSheetByName($nombreHoja);

        if (!$hoja) {
            throw ValidationException::withMessages([
                'archivo' => "El archivo no contiene la hoja «{$nombreHoja}». Usa el machote descargable como base.",
            ]);
        }

        $etiquetas = [];
        foreach ($columnas as $columna) {
            $etiquetas[self::normalizaEncabezado($columna['etiqueta'])] = $columna;
        }

        $ultimaColumna = $hoja->getHighestDataColumn();
        $mapeo = [];
        $encabezados = $hoja->rangeToArray("A2:{$ultimaColumna}2", null, true, false)[0] ?? [];
        foreach ($encabezados as $indice => $encabezado) {
            $clave = self::normalizaEncabezado((string) $encabezado);
            if ($clave !== '' && isset($etiquetas[$clave])) {
                $mapeo[$indice] = $etiquetas[$clave];
            }
        }

        $faltantes = collect($columnas)
            ->where('requerido', true)
            ->pluck('campo')
            ->reject(fn ($campo) => in_array($campo, array_column($mapeo, 'campo'), true))
            ->values();

        if ($faltantes->isNotEmpty()) {
            $nombres = collect($columnas)->whereIn('campo', $faltantes)->pluck('etiqueta')->implode(', ');
            throw ValidationException::withMessages([
                'archivo' => "Faltan columnas obligatorias en la hoja «{$nombreHoja}»: {$nombres}.",
            ]);
        }

        $filas = [];
        $ultimaFila = $hoja->getHighestRow();
        if ($ultimaFila < 4) {
            return [];
        }
        $datos = $hoja->rangeToArray("A4:{$ultimaColumna}{$ultimaFila}", null, true, false);

        foreach ($datos as $offset => $valores) {
            $fila = $offset + 4;
            if (collect($valores)->every(fn ($v) => $v === null || (is_string($v) && trim($v) === ''))) {
                continue;
            }

            $registro = ['__fila' => $fila];
            foreach ($mapeo as $indice => $columna) {
                $registro[$columna['campo']] = self::limpiaValor($valores[$indice] ?? null, $columna);
            }
            $filas[] = $registro;
        }

        return $filas;
    }

    private static function normalizaEncabezado(string $texto): string
    {
        $texto = str_replace(' *', '', trim($texto));
        $texto = mb_convert_case($texto, MB_CASE_LOWER, 'UTF-8');

        return trim(preg_replace('/\s+/', ' ', $texto));
    }

    private static function limpiaValor(mixed $valor, array $columna): mixed
    {
        if ($valor === null) {
            return null;
        }

        if (is_float($valor) && $valor == floor($valor)) {
            $valor = (int) $valor;
        }

        if ((is_int($valor) || is_float($valor)) && ($columna['tipo'] ?? self::TIPO_TEXTO) === self::TIPO_TEXTO) {
            $valor = (string) $valor;
        }

        if (is_string($valor)) {
            $valor = trim($valor);

            if ($valor === '') {
                return null;
            }

            if ($valor === 'Borrar esta fila de ejemplo') {
                return null;
            }
        }

        if (($columna['tipo'] ?? null) === self::TIPO_FECHA) {
            return self::normalizaFecha($valor);
        }

        if (($columna['tipo'] ?? null) === self::TIPO_BOOLEANO) {
            return self::normalizaBooleano($valor);
        }

        return $valor;
    }

    /** Acepta fecha serial de Excel, objeto DateTime, aaaa-mm-dd y dd/mm/aaaa. */
    private static function normalizaFecha(mixed $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        if (is_numeric($valor)) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $valor)->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }

        if ($valor instanceof \DateTimeInterface) {
            return $valor->format('Y-m-d');
        }

        $texto = trim((string) $valor);
        if ($texto === '') {
            return null;
        }

        if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{2,4})$#', $texto, $m)) {
            $dia = (int) $m[1];
            $mes = (int) $m[2];
            $anio = strlen($m[3]) === 2
                ? ((int) $m[3] >= 50 ? 1900 + (int) $m[3] : 2000 + (int) $m[3])
                : (int) $m[3];

            if (!checkdate($mes, $dia, $anio)) {
                return $texto;
            }

            return sprintf('%04d-%02d-%02d', $anio, $mes, $dia);
        }

        if (preg_match('#^(\d{4})-(\d{1,2})-(\d{1,2})$#', $texto, $m)) {
            if (!checkdate((int) $m[2], (int) $m[3], (int) $m[1])) {
                return $texto;
            }

            return sprintf('%04d-%02d-%02d', (int) $m[1], (int) $m[2], (int) $m[3]);
        }

        return $texto;
    }

    private static function normalizaBooleano(mixed $valor): ?bool
    {
        if (is_bool($valor)) {
            return $valor;
        }
        if (is_int($valor)) {
            return match ($valor) {
                1 => true,
                0 => false,
                default => null,
            };
        }

        $texto = mb_strtolower(trim((string) $valor), 'UTF-8');

        return match (true) {
            in_array($texto, ['si', 'sí', 'yes', '1', 'verdadero', 'true', 'activo'], true) => true,
            in_array($texto, ['no', 'false', '0', 'falso', 'inactivo'], true) => false,
            default => null,
        };
    }
}
