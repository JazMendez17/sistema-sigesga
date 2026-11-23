<?php

// Controlador de subida de archivos

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\UploadFileRequest;
use App\Services\ImageOptimizerService;

class UploadController extends Controller
{
    // Subir imagen optimizada al servidor
    public function store(UploadFileRequest $request, ImageOptimizerService $optimizer)
    {
        $type = $request->input('type');
        $directory = $type === 'foto' ? 'fotos' : 'empresa';
        $maxWidth = match ($type) {
            'logo', 'foto' => 512,
            'servicio' => 1200,
            default => 1600,
        };

        $path = $optimizer->guardarOptimizada($request->file('file'), $directory, $maxWidth);

        return back()->with('success', 'Imagen subida y optimizada correctamente')->with('uploaded_path', $path);
    }
}
