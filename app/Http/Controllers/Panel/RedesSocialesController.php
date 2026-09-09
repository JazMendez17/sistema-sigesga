<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\EmpresaAccesosRapido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        EmpresaAccesosRapido::create($this->validated($request) + [
            'empresa_id' => Auth::user()->empresa_id,
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
        $accesoRapido->update($this->validated($request));

        return redirect()->route('panel.accesos-rapidos.index')->with('success', 'Red social actualizada correctamente.');
    }

    public function destroy(EmpresaAccesosRapido $accesoRapido)
    {
        $this->authorizeAccess($accesoRapido);
        $accesoRapido->delete();

        return back()->with('success', 'Red social eliminada correctamente.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'titulo' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'link' => ['required', 'url', 'max:255'],
            'imagen' => ['nullable', 'string', 'max:255'],
            'icono' => ['nullable', 'string', 'max:100'],
            'orden' => ['nullable', 'integer', 'min:0'],
            'activo' => ['boolean'],
        ]) + [
            'orden' => (int) $request->input('orden', 0),
            'activo' => $request->boolean('activo'),
        ];
    }

    private function authorizeAccess(EmpresaAccesosRapido $accesoRapido): void
    {
        abort_unless($accesoRapido->empresa_id === Auth::user()->empresa_id, 403);
    }
}
