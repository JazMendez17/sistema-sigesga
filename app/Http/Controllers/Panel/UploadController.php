<?php

// Controlador de subida de archivos

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\UploadFileRequest;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    // Subir imagen al servidor
    public function store(UploadFileRequest $request)
    {
        $directory = $request->type === 'foto' ? 'fotos' : 'empresa';
        $path = $request->file('file')->store($directory, 'public');

        return back()->with('success', 'Imagen subida correctamente')->with('uploaded_path', $path);
    }
}
