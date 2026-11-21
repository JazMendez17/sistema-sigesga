<?php

// Machote e importación masiva de clientes desde Excel.

namespace App\Services;

use App\Models\Aseguradora;
use App\Models\Cliente;
use App\Models\Direccion;
use App\Models\Usuario;
use App\Support\MachoteExcel;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportaClientes
{
    public const HOJA = 'Clientes';

    public const PASSWORD_TEMPORAL = 'Cliente123.';

    /** Columnas del machote: 'campo' es la clave interna y 'etiqueta' la que ve el usuario. */
    public const COLUMNAS = [
        ['campo' => 'nombre', 'etiqueta' => 'Nombre', 'requerido' => true, 'ancho' => 24, 'ejemplo' => 'María Elena', 'ayuda' => 'Nombre(s) del cliente. Es el único dato obligatorio.'],
        ['campo' => 'apellido_paterno', 'etiqueta' => 'Apellido Paterno', 'ancho' => 20, 'ejemplo' => 'Hernández'],
        ['campo' => 'apellido_materno', 'etiqueta' => 'Apellido Materno', 'ancho' => 20, 'ejemplo' => 'Gómez'],
        ['campo' => 'tipo_cliente', 'etiqueta' => 'Tipo Cliente', 'ancho' => 18, 'tipo' => MachoteExcel::TIPO_TEXTO, 'opciones' => ['persona_fisica', 'persona_moral'], 'ejemplo' => 'persona_fisica', 'ayuda' => 'persona_fisica o persona_moral.'],
        ['campo' => 'sexo', 'etiqueta' => 'Sexo', 'ancho' => 10, 'opciones' => ['M', 'F'], 'ejemplo' => 'F', 'ayuda' => 'M o F.'],
        ['campo' => 'curp', 'etiqueta' => 'CURP', 'ancho' => 22, 'ejemplo' => 'HEGM850101MDFZRR08', 'ayuda' => 'Exactamente 18 caracteres. Si ya existe un cliente con esta CURP, la fila lo actualiza en lugar de duplicarlo.'],
        ['campo' => 'fecha_nacimiento', 'etiqueta' => 'Fecha Nacimiento', 'ancho' => 18, 'tipo' => MachoteExcel::TIPO_FECHA, 'ejemplo' => '1985-01-01'],
        ['campo' => 'telefono', 'etiqueta' => 'Teléfono', 'ancho' => 16, 'ejemplo' => '5512345678', 'ayuda' => 'Máximo 20 caracteres.'],
        ['campo' => 'telefono_local', 'etiqueta' => 'Teléfono Local', 'ancho' => 16, 'ejemplo' => '5555550000'],
        ['campo' => 'email', 'etiqueta' => 'Email', 'ancho' => 28, 'ejemplo' => 'maria@example.com', 'ayuda' => 'Si no existe, crea un acceso de cliente con la contraseña temporal que se muestra al terminar.'],
        ['campo' => 'folio_ine', 'etiqueta' => 'Folio INE', 'ancho' => 18],
        ['campo' => 'nacionalidad', 'etiqueta' => 'Nacionalidad', 'ancho' => 16, 'ejemplo' => 'Mexicana'],
        ['campo' => 'contacto_enlace', 'etiqueta' => 'Contacto Enlace', 'ancho' => 22],
        ['campo' => 'aseguradora', 'etiqueta' => 'Aseguradora', 'ancho' => 26, 'ejemplo' => 'GNP Seguros', 'ayuda' => 'Nombre exacto de una aseguradora ya dada de alta en tu empresa.'],
        ['campo' => 'numero_poliza', 'etiqueta' => 'Número de Póliza', 'ancho' => 18, 'ejemplo' => 'POL-2026-0001'],
        ['campo' => 'tipo_cobertura_poliza', 'etiqueta' => 'Tipo Cobertura Póliza', 'ancho' => 22, 'ejemplo' => 'Amplia'],
        ['campo' => 'calle', 'etiqueta' => 'Calle', 'ancho' => 24, 'ejemplo' => 'Av. Insurgentes Sur', 'ayuda' => 'Si capturas calle se crea la dirección completa; si la dejas vacía se ignora el resto de la dirección.'],
        ['campo' => 'numero_exterior', 'etiqueta' => 'Número Exterior', 'ancho' => 14, 'ejemplo' => '1234'],
        ['campo' => 'numero_interior', 'etiqueta' => 'Número Interior', 'ancho' => 14],
        ['campo' => 'colonia', 'etiqueta' => 'Colonia', 'ancho' => 20, 'ejemplo' => 'Del Valle'],
        ['campo' => 'codigo_postal', 'etiqueta' => 'Código Postal', 'ancho' => 14, 'ejemplo' => '03100'],
        ['campo' => 'municipio_alcaldia', 'etiqueta' => 'Municipio/Alcaldía', 'ancho' => 20, 'ejemplo' => 'Benito Juárez'],
        ['campo' => 'ciudad', 'etiqueta' => 'Ciudad', 'ancho' => 18, 'ejemplo' => 'Ciudad de México'],
        ['campo' => 'estado', 'etiqueta' => 'Estado', 'ancho' => 18, 'ejemplo' => 'Ciudad de México'],
        ['campo' => 'pais', 'etiqueta' => 'País', 'ancho' => 14, 'ejemplo' => 'México'],
        ['campo' => 'referencias', 'etiqueta' => 'Referencias', 'ancho' => 30, 'ayuda' => 'Máximo 255 caracteres.'],
    ];

    public function machote(): StreamedResponse
    {
        return MachoteExcel::descargar('machote_clientes_sigesga.xlsx', [
            self::HOJA => [
                'titulo' => 'Machote de clientes — captura un cliente por fila',
                'columnas' => self::COLUMNAS,
                'notas' => [
                    'Borra la fila 3 (la de ejemplo) antes de subir el archivo.',
                    'Empieza a capturar en la fila 4; el encabezado de la fila 2 no debe modificarse.',
                    'Una fila actualiza al cliente existente si repite su CURP o su correo.',
                    'Los campos sin asterisco son opcionales.',
                ],
            ],
        ]);
    }

    /** @return array{creados:int, actualizados:int, errores:array<int, array{hoja:string, fila:int, mensaje:string}>} */
    public function importar(UploadedFile $archivo, int $empresaId): array
    {
        $filas = MachoteExcel::leer($archivo, self::COLUMNAS, self::HOJA);

        if ($filas === []) {
            throw ValidationException::withMessages([
                'archivo' => 'El archivo no contiene filas de datos en la hoja «' . self::HOJA . '».',
            ]);
        }

        $aseguradoras = Aseguradora::where('empresa_id', $empresaId)->pluck('id', 'nombre');

        $creados = 0;
        $actualizados = 0;
        $errores = [];

        foreach ($filas as $fila) {
            $error = $this->guardarFila($fila, $empresaId, $aseguradoras);

            if ($error !== null) {
                $errores[] = ['hoja' => self::HOJA, 'fila' => $fila['__fila'], 'mensaje' => $error];
            } elseif ($fila['__existia']) {
                $actualizados++;
            } else {
                $creados++;
            }
        }

        return ['creados' => $creados, 'actualizados' => $actualizados, 'errores' => $errores];
    }

    /** Devuelve el mensaje de error de la fila, o null si se guardó correctamente. */
    private function guardarFila(array &$fila, int $empresaId, $aseguradoras): ?string
    {
        $validacion = Validator::make($fila, $this->reglas(), $this->mensajes(), $this->atributos());

        if ($validacion->fails()) {
            return $validacion->errors()->first();
        }

        $datos = collect($fila)->except(['__fila', '__existia'])->filter(fn ($v) => $v !== null && $v !== '')->all();

        if (empty($datos['nombre'])) {
            return 'El nombre es obligatorio.';
        }

        $curp = isset($datos['curp']) ? strtoupper(trim($datos['curp'])) : null;
        $email = isset($datos['email']) ? mb_strtolower(trim($datos['email'])) : null;

        // CURP es única en toda la base, no solo por empresa.
        if ($curp && Cliente::where('curp', $curp)->where('empresa_id', '!=', $empresaId)->exists()) {
            return "La CURP {$curp} ya está registrada en otra empresa.";
        }

        if ($curp || $email) {
            $existente = Cliente::where('empresa_id', $empresaId)
                ->where(function ($q) use ($curp, $email) {
                    if ($curp) {
                        $q->orWhere('curp', $curp);
                    }
                    if ($email) {
                        $q->orWhere('email', $email);
                    }
                })
                ->first();
        } else {
            $existente = null;
        }

        $fila['__existia'] = $existente !== null;

        if ($curp) {
            $datos['curp'] = $curp;
        }
        if ($email) {
            $datos['email'] = $email;
        }

        if (isset($datos['aseguradora'])) {
            $nombreAseguradora = trim($datos['aseguradora']);
            $idAseguradora = $aseguradoras->get($nombreAseguradora);

            if (!$idAseguradora) {
                return "La aseguradora «{$nombreAseguradora}» no existe en tu empresa.";
            }
            $datos['aseguradora_id'] = $idAseguradora;
            unset($datos['aseguradora']);
        }

        try {
            DB::transaction(function () use (&$datos, &$existente, $empresaId) {
                $direccion = $this->extraeDireccion($datos);

                if ($direccion !== null) {
                    if ($existente?->direccion_id) {
                        Direccion::find($existente->direccion_id)?->update($direccion);
                    } else {
                        $datos['direccion_id'] = Direccion::create($direccion)->id;
                    }
                }

                if ($existente) {
                    if (
                        !$existente->usuario_id
                        && !empty($datos['email'])
                        && !Usuario::where('email', $datos['email'])->exists()
                    ) {
                        $usuario = Usuario::create([
                            'empresa_id' => $empresaId,
                            'name' => trim(
                                ($datos['nombre'] ?? $existente->nombre) . ' ' .
                                ($datos['apellido_paterno'] ?? $existente->apellido_paterno ?? '') . ' ' .
                                ($datos['apellido_materno'] ?? $existente->apellido_materno ?? '')
                            ),
                            'email' => $datos['email'],
                            'password' => self::PASSWORD_TEMPORAL,
                            'debe_cambiar_password' => true,
                            'email_verified_at' => now(),
                            'rol' => 'cliente',
                        ]);
                        $datos['usuario_id'] = $usuario->id;
                    }

                    $existente->update($datos);

                    return;
                }

                $datos['empresa_id'] = $empresaId;

                if (!empty($datos['email']) && !Usuario::where('email', $datos['email'])->exists()) {
                    $usuario = Usuario::create([
                        'empresa_id' => $empresaId,
                        'name' => trim($datos['nombre'] . ' ' . ($datos['apellido_paterno'] ?? '') . ' ' . ($datos['apellido_materno'] ?? '')),
                        'email' => $datos['email'],
                        'password' => self::PASSWORD_TEMPORAL,
                        'debe_cambiar_password' => true,
                        'email_verified_at' => now(),
                        'rol' => 'cliente',
                    ]);
                    $datos['usuario_id'] = $usuario->id;
                }

                Cliente::create($datos);
            });
        } catch (QueryException $e) {
            Log::warning('Error importando fila de clientes', ['empresa_id' => $empresaId, 'error' => $e->getMessage()]);

            return 'No se pudo guardar el registro por un conflicto de datos (verifica CURP o correo duplicados).';
        }

        return null;
    }

    /** Separa las columnas de dirección; devuelve null si no se capturó ninguna. */
    private function extraeDireccion(array &$datos): ?array
    {
        $campos = ['calle', 'numero_exterior', 'numero_interior', 'colonia', 'codigo_postal', 'municipio_alcaldia', 'ciudad', 'estado', 'pais', 'referencias'];

        $direccion = [];
        foreach ($campos as $campo) {
            if (array_key_exists($campo, $datos)) {
                $direccion[$campo] = $datos[$campo];
                unset($datos[$campo]);
            }
        }

        if (empty($direccion['calle'])) {
            return null;
        }

        foreach (['numero_exterior', 'colonia', 'codigo_postal', 'municipio_alcaldia', 'ciudad', 'estado'] as $obligatoria) {
            $direccion[$obligatoria] = $direccion[$obligatoria] ?? '';
        }
        $direccion['pais'] = $direccion['pais'] ?? 'México';

        return $direccion;
    }

    private function reglas(): array
    {
        return [
            'nombre' => 'required|string|max:150',
            'apellido_paterno' => 'nullable|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'tipo_cliente' => 'nullable|in:persona_fisica,persona_moral',
            'sexo' => 'nullable|in:M,F',
            'curp' => 'nullable|string|size:18',
            'fecha_nacimiento' => 'nullable|date',
            'telefono' => 'nullable|string|max:20',
            'telefono_local' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'folio_ine' => 'nullable|string|max:20',
            'nacionalidad' => 'nullable|string|max:50',
            'contacto_enlace' => 'nullable|string|max:150',
            'aseguradora' => 'nullable|string|max:150',
            'numero_poliza' => 'nullable|string|max:50',
            'tipo_cobertura_poliza' => 'nullable|string|max:100',
            'calle' => 'nullable|string|max:150',
            'numero_exterior' => 'nullable|string|max:20',
            'numero_interior' => 'nullable|string|max:20',
            'colonia' => 'nullable|string|max:100',
            'codigo_postal' => 'nullable|string|max:10',
            'municipio_alcaldia' => 'nullable|string|max:100',
            'ciudad' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:60',
            'pais' => 'nullable|string|max:60',
            'referencias' => 'nullable|string|max:255',
        ];
    }

    private function atributos(): array
    {
        return collect(self::COLUMNAS)
            ->mapWithKeys(fn ($c) => [$c['campo'] => $c['etiqueta']])
            ->all();
    }

    private function mensajes(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'curp.size' => 'La CURP debe tener exactamente 18 caracteres.',
            'tipo_cliente.in' => 'Tipo Cliente debe ser persona_fisica o persona_moral.',
            'sexo.in' => 'El Sexo debe ser M o F.',
            'fecha_nacimiento.date' => 'La Fecha Nacimiento no tiene un formato válido (usa aaaa-mm-dd).',
            'email.email' => 'El Email no tiene un formato válido.',
        ];
    }
}
