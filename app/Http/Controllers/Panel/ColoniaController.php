<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Colonia;
use Illuminate\Http\JsonResponse;

class ColoniaController extends Controller
{
    public function buscarPorCP(string $cp): JsonResponse
    {
        $colonias = Colonia::where('codigo_postal', $cp)
            ->orderBy('colonia')
            ->get(['colonia', 'municipio', 'estado']);

        return response()->json($colonias);
    }
}
