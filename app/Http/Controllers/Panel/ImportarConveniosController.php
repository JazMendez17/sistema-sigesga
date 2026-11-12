<?php

// Descarga del machote multi-hoja y carga masiva de convenios.

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\ImportaConvenios;
use Illuminate\Http\Request;

class ImportarConveniosController extends Controller
{
    public function __construct(private readonly ImportaConvenios $importador)
    {
    }

    public function machote()
    {
        return $this->importador->machote();
    }

    public function store(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls|max:5120',
        ], [
            'archivo.required' => 'Selecciona el archivo Excel con los convenios.',
            'archivo.mimes' => 'El archivo debe ser un Excel (.xlsx o .xls).',
            'archivo.max' => 'El archivo no debe exceder los 5 MB.',
        ]);

        $resultado = $this->importador->importar(
            $request->file('archivo'),
            $request->user()->empresa_id
        );

        return back()->with('importacion', $resultado + ['modulo' => 'Convenios']);
    }
}
