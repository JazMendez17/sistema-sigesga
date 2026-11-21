<?php

// Machote multi-hoja e importación masiva de convenios desde Excel.

namespace App\Services;

use App\Models\Aseguradora;
use App\Models\CatalogoServicio;
use App\Models\Convenio;
use App\Models\ConvenioCobertura;
use App\Models\ConvenioConceptoAdicional;
use App\Models\ConvenioDocumentosRequerido;
use App\Models\ConvenioManiobrasEspeciale;
use App\Models\ConvenioSla;
use App\Models\ConvenioTarifa;
use App\Models\ConvenioUnidadesAutorizada;
use App\Support\MachoteExcel;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportaConvenios
{
    public const HOJA_PRINCIPAL = 'Convenios';

    /** Etiqueta de la columna que liga cada hoja con su convenio padre. */
    public const CAMPO_VINCULO = 'codigo_convenio';

    public const COLUMNAS = [
        'Convenios' => [
            ['campo' => 'codigo_convenio', 'etiqueta' => 'Código Convenio', 'requerido' => true, 'ancho' => 20, 'ejemplo' => 'CONV-GNP-2026', 'ayuda' => 'Identificador del convenio. Las hojas de tarifas, coberturas, unidades, maniobras, documentos, conceptos y SLA usan este mismo código para ligar sus filas. Si el código ya existe, la fila actualiza ese convenio.'],
            ['campo' => 'nombre_convenio_poliza', 'etiqueta' => 'Nombre Convenio/Póliza', 'requerido' => true, 'ancho' => 32, 'ejemplo' => 'Convenio GNP 2026'],
            ['campo' => 'aseguradora', 'etiqueta' => 'Aseguradora', 'requerido' => true, 'ancho' => 26, 'ejemplo' => 'GNP Seguros', 'ayuda' => 'Nombre exacto de una aseguradora ya dada de alta en tu empresa.'],
            ['campo' => 'tipo_servicio', 'etiqueta' => 'Tipo Servicio', 'ancho' => 20, 'ejemplo' => 'Grúa plataforma', 'ayuda' => 'Nombre exacto de un servicio del catálogo de tu empresa.'],
            ['campo' => 'fecha_inicio', 'etiqueta' => 'Fecha Inicio', 'ancho' => 16, 'tipo' => MachoteExcel::TIPO_FECHA, 'ejemplo' => '2026-01-01'],
            ['campo' => 'fecha_fin', 'etiqueta' => 'Fecha Fin', 'ancho' => 16, 'tipo' => MachoteExcel::TIPO_FECHA, 'ejemplo' => '2026-12-31'],
            ['campo' => 'estatus', 'etiqueta' => 'Estatus', 'ancho' => 18, 'opciones' => ['vigente', 'vencido', 'en_negociacion', 'cancelado'], 'ejemplo' => 'vigente'],
            ['campo' => 'tipo_ruta', 'etiqueta' => 'Tipo Ruta', 'ancho' => 14, 'opciones' => ['local', 'foraneo', 'nacional', 'regional'], 'ejemplo' => 'local'],
            ['campo' => 'tipo_cobertura', 'etiqueta' => 'Tipo Cobertura', 'ancho' => 20, 'ejemplo' => 'Amplia'],
            ['campo' => 'alcance_geografico', 'etiqueta' => 'Alcance Geográfico', 'ancho' => 26, 'ejemplo' => 'Zona metropolitana'],
            ['campo' => 'costo_banderazo', 'etiqueta' => 'Banderazo ($)', 'ancho' => 15, 'tipo' => MachoteExcel::TIPO_NUMERO, 'ejemplo' => 850],
            ['campo' => 'costo_km', 'etiqueta' => 'Costo por KM ($)', 'ancho' => 15, 'tipo' => MachoteExcel::TIPO_NUMERO, 'ejemplo' => 42.5],
            ['campo' => 'km_seguros_incluidos', 'etiqueta' => 'KM incluidos', 'ancho' => 14, 'tipo' => MachoteExcel::TIPO_NUMERO, 'ejemplo' => 10],
            ['campo' => 'km_maximo_amparado', 'etiqueta' => 'KM máximo amparado', 'ancho' => 18, 'tipo' => MachoteExcel::TIPO_NUMERO, 'ejemplo' => 300],
            ['campo' => 'tope_presupuesto', 'etiqueta' => 'Tope presupuesto ($)', 'ancho' => 18, 'tipo' => MachoteExcel::TIPO_NUMERO, 'ejemplo' => 150000],
            ['campo' => 'cubre_casetas_peaje', 'etiqueta' => 'Cubre casetas de peaje', 'ancho' => 18, 'tipo' => MachoteExcel::TIPO_BOOLEANO, 'opciones' => ['Sí', 'No'], 'ejemplo' => 'Sí'],
            ['campo' => 'renovacion_automatica', 'etiqueta' => 'Renovación automática', 'ancho' => 18, 'tipo' => MachoteExcel::TIPO_BOOLEANO, 'opciones' => ['Sí', 'No'], 'ejemplo' => 'No'],
            ['campo' => 'exclusivo', 'etiqueta' => 'Exclusivo', 'ancho' => 14, 'tipo' => MachoteExcel::TIPO_BOOLEANO, 'opciones' => ['Sí', 'No'], 'ejemplo' => 'No'],
            ['campo' => 'dias_credito', 'etiqueta' => 'Días de crédito', 'ancho' => 14, 'tipo' => MachoteExcel::TIPO_NUMERO, 'ejemplo' => 30],
            ['campo' => 'periodicidad_corte', 'etiqueta' => 'Periodicidad de corte', 'ancho' => 18, 'ejemplo' => 'Mensual'],
            ['campo' => 'requiere_folio_cfdi', 'etiqueta' => 'Requiere folio CFDI', 'ancho' => 17, 'tipo' => MachoteExcel::TIPO_BOOLEANO, 'opciones' => ['Sí', 'No'], 'ejemplo' => 'Sí'],
            ['campo' => 'iva_incluido', 'etiqueta' => 'IVA incluido', 'ancho' => 14, 'tipo' => MachoteExcel::TIPO_BOOLEANO, 'opciones' => ['Sí', 'No'], 'ejemplo' => 'Sí'],
            ['campo' => 'tope_credito', 'etiqueta' => 'Tope de crédito ($)', 'ancho' => 17, 'tipo' => MachoteExcel::TIPO_NUMERO, 'ejemplo' => 200000],
            ['campo' => 'aviso_previo_terminacion_dias', 'etiqueta' => 'Aviso previo terminación (días)', 'ancho' => 20, 'tipo' => MachoteExcel::TIPO_NUMERO, 'ejemplo' => 15],
            ['campo' => 'proceso_envio_facturas', 'etiqueta' => 'Proceso envío de facturas', 'ancho' => 30],
        ],

        'Tarifas' => [
            ['campo' => 'codigo_convenio', 'etiqueta' => 'Código Convenio', 'requerido' => true, 'ancho' => 20, 'ejemplo' => 'CONV-GNP-2026', 'ayuda' => 'Debe coincidir con un código de la hoja Convenios.'],
            ['campo' => 'servicio', 'etiqueta' => 'Servicio', 'requerido' => true, 'ancho' => 24, 'ejemplo' => 'Grúa plataforma', 'ayuda' => 'Si coincide con el catálogo se vincula; si no, se guarda como texto libre.'],
            ['campo' => 'alcance', 'etiqueta' => 'Alcance', 'ancho' => 20, 'ejemplo' => 'Urbano'],
            ['campo' => 'banderazo', 'etiqueta' => 'Banderazo ($)', 'ancho' => 15, 'tipo' => MachoteExcel::TIPO_NUMERO, 'ejemplo' => 850],
            ['campo' => 'km_incluidos', 'etiqueta' => 'KM incluidos', 'ancho' => 14, 'tipo' => MachoteExcel::TIPO_NUMERO, 'ejemplo' => 10],
            ['campo' => 'costo_km_extra', 'etiqueta' => 'Costo KM extra ($)', 'ancho' => 16, 'tipo' => MachoteExcel::TIPO_NUMERO, 'ejemplo' => 42.5],
            ['campo' => 'tarifa_nocturna_recargo_pct', 'etiqueta' => 'Recargo nocturno (%)', 'ancho' => 17, 'tipo' => MachoteExcel::TIPO_NUMERO, 'ejemplo' => 25],
            ['campo' => 'tarifa_domingo_festivo_recargo_pct', 'etiqueta' => 'Recargo domingo/festivo (%)', 'ancho' => 20, 'tipo' => MachoteExcel::TIPO_NUMERO, 'ejemplo' => 35],
            ['campo' => 'minutos_espera_incluidos', 'etiqueta' => 'Minutos espera incluidos', 'ancho' => 18, 'tipo' => MachoteExcel::TIPO_NUMERO, 'ejemplo' => 30],
            ['campo' => 'costo_espera_adicional_hora', 'etiqueta' => 'Costo espera adicional hora ($)', 'ancho' => 20, 'tipo' => MachoteExcel::TIPO_NUMERO, 'ejemplo' => 180],
            ['campo' => 'descuento_pct', 'etiqueta' => 'Descuento (%)', 'ancho' => 14, 'tipo' => MachoteExcel::TIPO_NUMERO, 'ejemplo' => 5],
            ['campo' => 'tipo_descuento', 'etiqueta' => 'Tipo descuento', 'ancho' => 18, 'ejemplo' => 'Por volumen'],
        ],

        'Coberturas' => [
            ['campo' => 'codigo_convenio', 'etiqueta' => 'Código Convenio', 'requerido' => true, 'ancho' => 20, 'ejemplo' => 'CONV-GNP-2026'],
            ['campo' => 'tipo_cobertura', 'etiqueta' => 'Tipo de Cobertura', 'requerido' => true, 'ancho' => 28, 'ejemplo' => 'Casetas y peajes'],
        ],

        'Unidades' => [
            ['campo' => 'codigo_convenio', 'etiqueta' => 'Código Convenio', 'requerido' => true, 'ancho' => 20, 'ejemplo' => 'CONV-GNP-2026'],
            ['campo' => 'tipo_grua', 'etiqueta' => 'Tipo Grúa', 'requerido' => true, 'ancho' => 14, 'opciones' => ['A', 'B', 'C', 'D'], 'ejemplo' => 'A'],
            ['campo' => 'peso_maximo_kg', 'etiqueta' => 'Peso máximo (kg)', 'ancho' => 16, 'tipo' => MachoteExcel::TIPO_NUMERO, 'ejemplo' => 3500],
            ['campo' => 'equipamiento', 'etiqueta' => 'Equipamiento', 'requerido' => true, 'ancho' => 24, 'opciones' => ['plataforma_hidraulica', 'gancho_arrastre', 'ambos'], 'ejemplo' => 'plataforma_hidraulica'],
        ],

        'Maniobras' => [
            ['campo' => 'codigo_convenio', 'etiqueta' => 'Código Convenio', 'requerido' => true, 'ancho' => 20, 'ejemplo' => 'CONV-GNP-2026'],
            ['campo' => 'concepto', 'etiqueta' => 'Concepto', 'requerido' => true, 'ancho' => 28, 'ejemplo' => 'Maniobra en sótano'],
            ['campo' => 'aplica', 'etiqueta' => 'Aplica', 'ancho' => 12, 'tipo' => MachoteExcel::TIPO_BOOLEANO, 'opciones' => ['Sí', 'No'], 'ejemplo' => 'Sí'],
            ['campo' => 'forma_cobro', 'etiqueta' => 'Forma de cobro', 'ancho' => 18, 'ejemplo' => 'Por evento'],
            ['campo' => 'costo', 'etiqueta' => 'Costo ($)', 'requerido' => true, 'ancho' => 14, 'tipo' => MachoteExcel::TIPO_NUMERO, 'ejemplo' => 500],
        ],

        'Documentos' => [
            ['campo' => 'codigo_convenio', 'etiqueta' => 'Código Convenio', 'requerido' => true, 'ancho' => 20, 'ejemplo' => 'CONV-GNP-2026'],
            ['campo' => 'documento', 'etiqueta' => 'Documento', 'requerido' => true, 'ancho' => 32, 'ejemplo' => 'Oficio de autorización'],
            ['campo' => 'obligatorio', 'etiqueta' => 'Obligatorio', 'ancho' => 14, 'tipo' => MachoteExcel::TIPO_BOOLEANO, 'opciones' => ['Sí', 'No'], 'ejemplo' => 'Sí'],
        ],

        'Conceptos Adicionales' => [
            ['campo' => 'codigo_convenio', 'etiqueta' => 'Código Convenio', 'requerido' => true, 'ancho' => 20, 'ejemplo' => 'CONV-GNP-2026'],
            ['campo' => 'cubre_casetas', 'etiqueta' => 'Cubre casetas', 'ancho' => 15, 'tipo' => MachoteExcel::TIPO_BOOLEANO, 'opciones' => ['Sí', 'No'], 'ejemplo' => 'Sí'],
            ['campo' => 'forma_pago_casetas', 'etiqueta' => 'Forma de pago casetas', 'ancho' => 22, 'ejemplo' => 'Tarjeta empresarial'],
            ['campo' => 'costo_estadia_dia', 'etiqueta' => 'Costo estadía día ($)', 'ancho' => 18, 'tipo' => MachoteExcel::TIPO_NUMERO, 'ejemplo' => 350],
            ['campo' => 'dias_gracia_estadia', 'etiqueta' => 'Días de gracia estadía', 'ancho' => 18, 'tipo' => MachoteExcel::TIPO_NUMERO, 'ejemplo' => 2],
            ['campo' => 'costo_resguardo_nocturno', 'etiqueta' => 'Costo resguardo nocturno ($)', 'ancho' => 20, 'tipo' => MachoteExcel::TIPO_NUMERO, 'ejemplo' => 200],
            ['campo' => 'genera_cargo_cliente_final', 'etiqueta' => 'Genera cargo a cliente final', 'ancho' => 20, 'tipo' => MachoteExcel::TIPO_BOOLEANO, 'opciones' => ['Sí', 'No'], 'ejemplo' => 'No'],
        ],

        'SLA' => [
            ['campo' => 'codigo_convenio', 'etiqueta' => 'Código Convenio', 'requerido' => true, 'ancho' => 20, 'ejemplo' => 'CONV-GNP-2026'],
            ['campo' => 'tiempo_max_respuesta_urbano_min', 'etiqueta' => 'Respuesta máxima urbano (min)', 'requerido' => true, 'ancho' => 22, 'tipo' => MachoteExcel::TIPO_NUMERO, 'ejemplo' => 30],
            ['campo' => 'tiempo_max_respuesta_carretera_min', 'etiqueta' => 'Respuesta máxima carretera (min)', 'requerido' => true, 'ancho' => 24, 'tipo' => MachoteExcel::TIPO_NUMERO, 'ejemplo' => 60],
            ['campo' => 'disponibilidad', 'etiqueta' => 'Disponibilidad', 'ancho' => 16, 'ejemplo' => '24/7'],
            ['campo' => 'penalizacion_incumplimiento', 'etiqueta' => 'Penalización por incumplimiento', 'ancho' => 30],
            ['campo' => 'protocolo_asignacion', 'etiqueta' => 'Protocolo de asignación', 'ancho' => 28],
        ],
    ];

    public function machote(): StreamedResponse
    {
        $hojas = [];

        foreach (self::COLUMNAS as $nombre => $columnas) {
            $hojas[$nombre] = [
                'titulo' => $nombre === self::HOJA_PRINCIPAL
                    ? 'Convenios — captura un convenio por fila'
                    : "{$nombre} — una fila por registro, ligada por Código Convenio",
                'columnas' => $columnas,
                'notas' => $nombre === self::HOJA_PRINCIPAL ? [
                    'Borra la fila 3 (la de ejemplo) antes de subir el archivo.',
                    'Empieza a capturar en la fila 4; el encabezado de la fila 2 no debe modificarse.',
                    'El Código Convenio es la llave que liga las hojas Tarifas, Coberturas, Unidades, Maniobras, Documentos, Conceptos Adicionales y SLA.',
                    'Puedes dejar vacías las hojas que no necesites; solo se importan las filas con datos.',
                    'Un código repetido actualiza el convenio existente en lugar de crear uno nuevo.',
                    'Los campos sin asterisco son opcionales.',
                ] : [],
            ];
        }

        return MachoteExcel::descargar('machote_convenios_sigesga.xlsx', $hojas);
    }

    /** @return array{creados:int, actualizados:int, errores:array<int, array{hoja:string, fila:int, mensaje:string}>} */
    public function importar(UploadedFile $archivo, int $empresaId): array
    {
        $libro = MachoteExcel::abrir($archivo);

        $filasPrincipales = MachoteExcel::leerHoja($libro, self::COLUMNAS[self::HOJA_PRINCIPAL], self::HOJA_PRINCIPAL);

        if ($filasPrincipales === []) {
            throw ValidationException::withMessages([
                'archivo' => 'La hoja «' . self::HOJA_PRINCIPAL . '» no contiene filas de datos.',
            ]);
        }

        $contexto = [
            'empresaId' => $empresaId,
            'aseguradoras' => $this->mapaAseguradoras($empresaId),
            'servicios' => $this->mapaServicios($empresaId),
            'convenios' => [],
        ];

        $creados = 0;
        $actualizados = 0;
        $errores = [];
        $codigosPadreRechazados = [];

        foreach ($filasPrincipales as $fila) {
            $resultado = $this->guardarConvenio($fila, $contexto);

            if (is_string($resultado)) {
                $errores[] = ['hoja' => self::HOJA_PRINCIPAL, 'fila' => $fila['__fila'], 'mensaje' => $resultado];
                $codigo = strtoupper(trim((string) ($fila[self::CAMPO_VINCULO] ?? '')));
                if ($codigo !== '') {
                    $codigosPadreRechazados[$codigo] = true;
                }
            } elseif ($resultado) {
                $actualizados++;
            } else {
                $creados++;
            }
        }

        foreach (array_keys(self::COLUMNAS) as $hoja) {
            if ($hoja === self::HOJA_PRINCIPAL) {
                continue;
            }

            [$nuevos, $cambios, $erroresHoja] = $this->importarHojaHija($libro, $hoja, $contexto, $codigosPadreRechazados);
            $creados += $nuevos;
            $actualizados += $cambios;
            $errores = array_merge($errores, $erroresHoja);
        }

        return ['creados' => $creados, 'actualizados' => $actualizados, 'errores' => $errores];
    }

    /** @return array<string, int> */
    private function mapaAseguradoras(int $empresaId): array
    {
        $mapa = [];

        $aseguradoras = Aseguradora::where('empresa_id', $empresaId)
            ->orderBy('id')
            ->get(['id', 'nombre', 'nombre_comercial']);

        foreach ($aseguradoras as $aseguradora) {
            foreach ([$aseguradora->nombre, $aseguradora->nombre_comercial] as $nombre) {
                $clave = self::normalizaTextoCatalogo($nombre);

                if ($clave !== '') {
                    $mapa[$clave] ??= $aseguradora->id;
                }
            }
        }

        // Nombre usado en el machote para la aseguradora registrada como AXA.
        $axa = $mapa[self::normalizaTextoCatalogo('AXA')] ?? null;
        if ($axa !== null) {
            $mapa[self::normalizaTextoCatalogo('AXA Seguros')] ??= $axa;
        }

        return $mapa;
    }

    private static function normalizaTextoCatalogo(?string $texto): string
    {
        $texto = Str::ascii(mb_strtolower(trim((string) $texto), 'UTF-8'));

        return trim(preg_replace('/\s+/', ' ', $texto) ?? $texto);
    }

    /** @return array<string, int> */
    private function mapaServicios(int $empresaId): array
    {
        $mapa = [];

        $servicios = CatalogoServicio::where('empresa_id', $empresaId)
            ->where('activo', true)
            ->orderBy('id')
            ->get(['id', 'nombre']);

        foreach ($servicios as $servicio) {
            $clave = self::normalizaTextoCatalogo($servicio->nombre);
            if ($clave !== '') {
                $mapa[$clave] ??= $servicio->id;
            }
        }

        // Alias usados por el machote de convenios para los servicios del catálogo.
        $aliases = [
            'grua plataforma' => ['arrastre en plataforma hidraulica'],
            'grua de arrastre' => ['arrastre con gancho'],
        ];

        foreach ($aliases as $alias => $nombresCatalogo) {
            foreach ($nombresCatalogo as $nombreCatalogo) {
                $id = $mapa[self::normalizaTextoCatalogo($nombreCatalogo)] ?? null;
                if ($id !== null) {
                    $mapa[self::normalizaTextoCatalogo($alias)] ??= $id;
                    break;
                }
            }
        }

        return $mapa;
    }

    /** Devuelve true si actualizó un convenio existente, false si creó uno nuevo, o un mensaje de error. */
    private function guardarConvenio(array $fila, array &$contexto): bool|string
    {
        if (isset($fila['tipo_ruta']) && is_string($fila['tipo_ruta'])) {
            $tipoRuta = self::normalizaTextoCatalogo($fila['tipo_ruta']);

            if ($tipoRuta === 'local' || $tipoRuta === 'locales') {
                $fila['tipo_ruta'] = 'local';
            } elseif (in_array($tipoRuta, ['foraneo', 'foranea', 'foraneos', 'foraneas'], true)) {
                $fila['tipo_ruta'] = 'foraneo';
            } elseif (in_array($tipoRuta, ['nacional', 'regional'], true)) {
                $fila['tipo_ruta'] = $tipoRuta;
            }
        }

        $validacion = Validator::make($fila, self::REGLAS[self::HOJA_PRINCIPAL], self::MENSAJES, self::atributos(self::HOJA_PRINCIPAL));

        if ($validacion->fails()) {
            return $validacion->errors()->first();
        }

        $datos = self::limpiaDatos($fila);

        $codigo = strtoupper($datos['codigo_convenio']);
        $datos['codigo_convenio'] = $codigo;

        $idAseguradora = $contexto['aseguradoras'][self::normalizaTextoCatalogo($datos['aseguradora'])] ?? null;
        if (!$idAseguradora) {
            return "La aseguradora «" . trim($datos['aseguradora']) . "» no está dada de alta en el catálogo de tu empresa. Agrégala en Aseguradoras y vuelve a importar esta fila.";
        }

        $convenio = Convenio::where('empresa_id', $contexto['empresaId'])
            ->where('codigo_convenio', $codigo)
            ->first();

        $datos['aseguradora_id'] = $idAseguradora;
        // Mantiene el valor por defecto también en motores de BD donde las
        // migraciones de compatibilidad no conservan el default de la columna.
        $datos['km_seguros_incluidos'] ??= 0;
        unset($datos['aseguradora']);

        if (isset($datos['tipo_servicio'])) {
            $nombre = trim($datos['tipo_servicio']);
            $idServicio = $contexto['servicios'][self::normalizaTextoCatalogo($nombre)] ?? null;

            if (!$idServicio) {
                return "El tipo de servicio «{$nombre}» no está en el catálogo de servicios de tu empresa.";
            }
            $datos['tipo_servicio_id'] = $idServicio;
            unset($datos['tipo_servicio']);
        }

        try {
            if ($convenio) {
                $convenio->update($datos);
            } else {
                $convenio = Convenio::create($datos + ['empresa_id' => $contexto['empresaId']]);
            }
        } catch (QueryException $e) {
            Log::warning('Error importando convenio', ['empresa_id' => $contexto['empresaId'], 'codigo' => $codigo, 'error' => $e->getMessage()]);

            return 'No se pudo guardar el convenio por un conflicto de datos en la base de datos.';
        }

        $contexto['convenios'][$codigo] = $convenio;

        return $convenio->wasRecentlyCreated ? false : true;
    }

    /** @return array{0:int, 1:int, 2:array} */
    private function importarHojaHija($libro, string $hoja, array &$contexto, array $codigosPadreRechazados = []): array
    {
        try {
            $filas = MachoteExcel::leerHoja($libro, self::COLUMNAS[$hoja], $hoja);
        } catch (ValidationException $e) {
            // Las hojas opcionales pueden faltar o traer encabezados distintos; se reportan sin abortar.
            $errores = [];
            foreach ($e->errors() as $mensajes) {
                $errores[] = ['hoja' => $hoja, 'fila' => 0, 'mensaje' => $mensajes[0]];
            }

            return [0, 0, $errores];
        }

        $creados = 0;
        $actualizados = 0;
        $errores = [];

        foreach ($filas as $fila) {
            $codigoPadre = strtoupper(trim((string) ($fila[self::CAMPO_VINCULO] ?? '')));
            if (
                isset($codigosPadreRechazados[$codigoPadre])
                && !isset($contexto['convenios'][$codigoPadre])
                && !Convenio::where('empresa_id', $contexto['empresaId'])->where('codigo_convenio', $codigoPadre)->exists()
            ) {
                continue;
            }

            $mensaje = $this->guardarHijo($hoja, $fila, $contexto);

            if ($mensaje !== null) {
                $errores[] = ['hoja' => $hoja, 'fila' => $fila['__fila'], 'mensaje' => $mensaje];
            } elseif ($fila['__nuevo']) {
                $creados++;
            } else {
                $actualizados++;
            }
        }

        return [$creados, $actualizados, $errores];
    }

    private function guardarHijo(string $hoja, array &$fila, array &$contexto): ?string
    {
        $validacion = Validator::make($fila, self::REGLAS[$hoja], self::MENSAJES, self::atributos($hoja));

        if ($validacion->fails()) {
            return $validacion->errors()->first();
        }

        $datos = self::limpiaDatos($fila);
        $codigo = strtoupper($datos[self::CAMPO_VINCULO]);
        unset($datos[self::CAMPO_VINCULO]);

        $convenio = $contexto['convenios'][$codigo]
            ?? Convenio::where('empresa_id', $contexto['empresaId'])->where('codigo_convenio', $codigo)->first();

        if (!$convenio) {
            return "El código «{$codigo}» no existe en la hoja Convenios ni entre los convenios de tu empresa.";
        }

        $contexto['convenios'][$codigo] = $convenio;
        $datos['convenio_id'] = $convenio->id;

        if ($hoja === 'Tarifas') {
            $nombre = trim($datos['servicio']);
            $datos['servicio_id'] = $contexto['servicios'][self::normalizaTextoCatalogo($nombre)] ?? null;
            $datos['servicio'] = $nombre;
            $clave = [
                'convenio_id' => $convenio->id,
                'servicio' => $nombre,
                'alcance' => $datos['alcance'] ?? null,
            ];
        } elseif ($hoja === 'Coberturas') {
            $clave = ['convenio_id' => $convenio->id, 'tipo_cobertura' => $datos['tipo_cobertura']];
            $datos = $clave;
        } elseif ($hoja === 'Unidades') {
            $clave = ['convenio_id' => $convenio->id, 'tipo_grua' => $datos['tipo_grua'], 'equipamiento' => $datos['equipamiento']];
            $datos = $clave + ['peso_maximo_kg' => $datos['peso_maximo_kg'] ?? null];
        } elseif ($hoja === 'Maniobras') {
            $clave = ['convenio_id' => $convenio->id, 'concepto' => $datos['concepto']];
        } elseif ($hoja === 'Documentos') {
            $clave = ['convenio_id' => $convenio->id, 'documento' => $datos['documento']];
        } else {
            // Conceptos Adicionales y SLA son 1:1 con el convenio.
            $clave = ['convenio_id' => $convenio->id];
        }

        $fila['__nuevo'] = false;

        try {
            DB::transaction(function () use ($hoja, $clave, $datos, &$fila) {
                $registro = self::modelo($hoja)::firstOrNew($clave);
                $registro->fill($datos);

                $fila['__nuevo'] = !$registro->exists;
                $registro->save();
            });
        } catch (QueryException $e) {
            Log::warning('Error importando registro hijo de convenio', ['empresa_id' => $contexto['empresaId'], 'hoja' => $hoja, 'error' => $e->getMessage()]);

            return 'No se pudo guardar el registro por un conflicto de datos en la base de datos.';
        }

        return null;
    }

    private static function modelo(string $hoja): string
    {
        return match ($hoja) {
            'Tarifas' => ConvenioTarifa::class,
            'Coberturas' => ConvenioCobertura::class,
            'Unidades' => ConvenioUnidadesAutorizada::class,
            'Maniobras' => ConvenioManiobrasEspeciale::class,
            'Documentos' => ConvenioDocumentosRequerido::class,
            'Conceptos Adicionales' => ConvenioConceptoAdicional::class,
            'SLA' => ConvenioSla::class,
        };
    }

    /** Quita las claves de control y deja solo los campos con valor. */
    private static function limpiaDatos(array $fila): array
    {
        return collect($fila)
            ->except(['__fila', '__nuevo'])
            ->filter(fn ($v) => $v !== null && $v !== '')
            ->all();
    }

    private static function atributos(string $hoja): array
    {
        return collect(self::COLUMNAS[$hoja])
            ->mapWithKeys(fn ($c) => [$c['campo'] => $c['etiqueta']])
            ->all();
    }

    public const REGLAS = [
        'Convenios' => [
            'codigo_convenio' => 'required|string|max:50',
            'nombre_convenio_poliza' => 'required|string|max:150',
            'aseguradora' => 'required|string|max:150',
            'tipo_servicio' => 'nullable|string|max:100',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'estatus' => 'nullable|in:vigente,vencido,en_negociacion,cancelado',
            'tipo_ruta' => 'nullable|in:local,foraneo,nacional,regional',
            'tipo_cobertura' => 'nullable|string|max:100',
            'alcance_geografico' => 'nullable|string|max:255',
            'costo_banderazo' => 'nullable|numeric|min:0',
            'costo_km' => 'nullable|numeric|min:0',
            'km_seguros_incluidos' => 'nullable|numeric|min:0',
            'km_maximo_amparado' => 'nullable|numeric|min:0',
            'tope_presupuesto' => 'nullable|numeric|min:0',
            'cubre_casetas_peaje' => 'nullable|boolean',
            'renovacion_automatica' => 'nullable|boolean',
            'exclusivo' => 'nullable|boolean',
            'dias_credito' => 'nullable|integer|min:0',
            'periodicidad_corte' => 'nullable|string|max:50',
            'requiere_folio_cfdi' => 'nullable|boolean',
            'iva_incluido' => 'nullable|boolean',
            'tope_credito' => 'nullable|numeric|min:0',
            'aviso_previo_terminacion_dias' => 'nullable|integer|min:0',
            'proceso_envio_facturas' => 'nullable|string',
        ],
        'Tarifas' => [
            'codigo_convenio' => 'required|string|max:50',
            'servicio' => 'required|string|max:150',
            'alcance' => 'nullable|string|max:100',
            'banderazo' => 'nullable|numeric|min:0',
            'km_incluidos' => 'nullable|integer|min:0',
            'costo_km_extra' => 'nullable|numeric|min:0',
            'tarifa_nocturna_recargo_pct' => 'nullable|numeric|between:0,999.99',
            'tarifa_domingo_festivo_recargo_pct' => 'nullable|numeric|between:0,999.99',
            'minutos_espera_incluidos' => 'nullable|integer|min:0',
            'costo_espera_adicional_hora' => 'nullable|numeric|min:0',
            'descuento_pct' => 'nullable|numeric|between:0,999.99',
            'tipo_descuento' => 'nullable|string|max:50',
        ],
        'Coberturas' => [
            'codigo_convenio' => 'required|string|max:50',
            'tipo_cobertura' => 'required|string|max:100',
        ],
        'Unidades' => [
            'codigo_convenio' => 'required|string|max:50',
            'tipo_grua' => 'required|in:A,B,C,D',
            'peso_maximo_kg' => 'nullable|numeric|min:0',
            'equipamiento' => 'required|in:plataforma_hidraulica,gancho_arrastre,ambos',
        ],
        'Maniobras' => [
            'codigo_convenio' => 'required|string|max:50',
            'concepto' => 'required|string|max:150',
            'aplica' => 'nullable|boolean',
            'forma_cobro' => 'nullable|string|max:50',
            'costo' => 'required|numeric|min:0',
        ],
        'Documentos' => [
            'codigo_convenio' => 'required|string|max:50',
            'documento' => 'required|string|max:150',
            'obligatorio' => 'nullable|boolean',
        ],
        'Conceptos Adicionales' => [
            'codigo_convenio' => 'required|string|max:50',
            'cubre_casetas' => 'nullable|boolean',
            'forma_pago_casetas' => 'nullable|string|max:100',
            'costo_estadia_dia' => 'nullable|numeric|min:0',
            'dias_gracia_estadia' => 'nullable|integer|min:0',
            'costo_resguardo_nocturno' => 'nullable|numeric|min:0',
            'genera_cargo_cliente_final' => 'nullable|boolean',
        ],
        'SLA' => [
            'codigo_convenio' => 'required|string|max:50',
            'tiempo_max_respuesta_urbano_min' => 'required|integer|min:0',
            'tiempo_max_respuesta_carretera_min' => 'required|integer|min:0',
            'disponibilidad' => 'nullable|string|max:50',
            'penalizacion_incumplimiento' => 'nullable|string',
            'protocolo_asignacion' => 'nullable|string|max:150',
        ],
    ];

    public const MENSAJES = [
        'codigo_convenio.required' => 'El Código Convenio es obligatorio.',
        'nombre_convenio_poliza.required' => 'El Nombre Convenio/Póliza es obligatorio.',
        'aseguradora.required' => 'La Aseguradora es obligatoria.',
        'servicio.required' => 'El Servicio es obligatorio.',
        'tipo_cobertura.required' => 'El Tipo de Cobertura es obligatorio.',
        'tipo_grua.required' => 'El Tipo Grúa es obligatorio.',
        'tipo_grua.in' => 'El Tipo Grúa debe ser A, B, C o D.',
        'equipamiento.required' => 'El Equipamiento es obligatorio.',
        'equipamiento.in' => 'El Equipamiento debe ser plataforma_hidraulica, gancho_arrastre o ambos.',
        'concepto.required' => 'El Concepto es obligatorio.',
        'costo.required' => 'El Costo es obligatorio.',
        'documento.required' => 'El Documento es obligatorio.',
        'tiempo_max_respuesta_urbano_min.required' => 'La Respuesta máxima urbano es obligatoria.',
        'tiempo_max_respuesta_carretera_min.required' => 'La Respuesta máxima carretera es obligatoria.',
        'estatus.in' => 'El Estatus debe ser vigente, vencido, en_negociacion o cancelado.',
        'tipo_ruta.in' => 'El Tipo Ruta debe ser local, foraneo, nacional o regional.',
        'fecha_fin.after_or_equal' => 'La Fecha Fin no puede ser anterior a la Fecha Inicio.',
        'fecha_inicio.date' => 'La Fecha Inicio no tiene un formato válido (usa aaaa-mm-dd).',
        'fecha_fin.date' => 'La Fecha Fin no tiene un formato válido (usa aaaa-mm-dd).',
    ];
}
