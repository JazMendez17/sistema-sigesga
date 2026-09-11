<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\EmpresaAccesosRapido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class RedesSocialesController extends Controller
{
    public function index()
    {
        return Inertia::render('Panel/AccesosRapidos/Index', [
            'accesos' => EmpresaAccesosRapido::where('empresa_id', Auth::user()->empresa_id)
                ->orderBy('orden')
                ->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Panel/AccesosRapidos/Form');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if ($request->hasFile('imagen')) {
            $data['imagen'] = $this->guardarImagen($request);
        }

        EmpresaAccesosRapido::create($data + [
            'empresa_id' => Auth::user()->empresa_id,
            'orden' => $this->siguienteOrden(Auth::user()->empresa_id),
        ]);

        return redirect()->route('panel.accesos-rapidos.index')->with('success', 'Red social agregada correctamente.');
    }

    public function edit(EmpresaAccesosRapido $accesoRapido)
    {
        $this->authorizeAccess($accesoRapido);

        return Inertia::render('Panel/AccesosRapidos/Form', [
            'acceso' => $accesoRapido,
        ]);
    }

    public function update(Request $request, EmpresaAccesosRapido $accesoRapido)
    {
        $this->authorizeAccess($accesoRapido);

        $data = $this->validated($request);

        if ($request->hasFile('imagen')) {
            $data['imagen'] = $this->guardarImagen($request);
            $this->borrarImagenLocal($accesoRapido->imagen);
        }

        $accesoRapido->update($data);

        return redirect()->route('panel.accesos-rapidos.index')->with('success', 'Red social actualizada correctamente.');
    }

    public function destroy(EmpresaAccesosRapido $accesoRapido)
    {
        $this->authorizeAccess($accesoRapido);
        $this->borrarImagenLocal($accesoRapido->imagen);
        $accesoRapido->delete();

        return back()->with('success', 'Red social eliminada correctamente.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'titulo' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'link' => ['required', 'url', 'max:255'],
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'icono' => ['nullable', 'string', 'max:100'],
            'activo' => ['boolean'],
        ]);

        if (!$request->hasFile('imagen') && array_key_exists('imagen', $data)) {
            unset($data['imagen']);
        }

        return $data + [
            'activo' => $request->boolean('activo'),
        ];
    }

    private function guardarImagen(Request $request): string
    {
        $path = $request->file('imagen')->store('accesos-rapidos', 'public');

        return '/storage/' . $path;
    }

    private function siguienteOrden(int $empresaId): int
    {
        return (int) EmpresaAccesosRapido::where('empresa_id', $empresaId)->max('orden') + 1;
    }

    private function borrarImagenLocal(?string $ruta): void
    {
        if (!$ruta || !str_starts_with($ruta, '/storage/')) {
            return;
        }

        Storage::disk('public')->delete(substr($ruta, strlen('/storage/')));
    }

    private function authorizeAccess(EmpresaAccesosRapido $accesoRapido): void
    {
        abort_unless($accesoRapido->empresa_id === Auth::user()->empresa_id, 403);
    }
}
