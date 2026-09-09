<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\EmpresaServicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ServiciosLandingController extends Controller
{
    public function index()
    {
        $empresa = Auth::user()->empresa;

        return Inertia::render('Panel/ServiciosLanding/Index', [
            'servicios' => EmpresaServicio::where('empresa_id', $empresa->id)->orderBy('orden')->get(),
            'coloresDisponibles' => $this->colores($empresa),
        ]);
    }

    public function create()
    {
        return Inertia::render('Panel/ServiciosLanding/Form', [
            'coloresDisponibles' => $this->colores(Auth::user()->empresa),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, true);
        $data['empresa_id'] = Auth::user()->empresa_id;
        $data['foto'] = $request->file('foto')->store('servicios', 'public');
        EmpresaServicio::create($data);

        return redirect()->route('panel.servicios-landing.index')->with('success', 'Servicio agregado correctamente.');
    }

    public function edit(EmpresaServicio $servicioLanding)
    {
        $this->authorizeAccess($servicioLanding);

        return Inertia::render('Panel/ServiciosLanding/Form', [
            'servicio' => $servicioLanding,
            'coloresDisponibles' => $this->colores(Auth::user()->empresa),
        ]);
    }

    public function update(Request $request, EmpresaServicio $servicioLanding)
    {
        $this->authorizeAccess($servicioLanding);
        $data = $this->validated($request, false);

        if ($request->hasFile('foto')) {
            if ($servicioLanding->foto) Storage::disk('public')->delete($servicioLanding->foto);
            $data['foto'] = $request->file('foto')->store('servicios', 'public');
        }

        $servicioLanding->update($data);
        return redirect()->route('panel.servicios-landing.index')->with('success', 'Servicio actualizado correctamente.');
    }

    public function destroy(EmpresaServicio $servicioLanding)
    {
        $this->authorizeAccess($servicioLanding);
        if ($servicioLanding->foto) Storage::disk('public')->delete($servicioLanding->foto);
        $servicioLanding->delete();
        return back()->with('success', 'Servicio eliminado correctamente.');
    }

    private function validated(Request $request, bool $imageRequired): array
    {
        $allowedColors = collect($this->colores(Auth::user()->empresa))->pluck('valor')->all();
        $rules = [
            'tipo' => ['required', 'string', 'max:100'],
            'descripcion' => ['required', 'string', 'max:1000'],
            'orden' => ['nullable', 'integer', 'min:0'],
            'activo' => ['boolean'],
            'color' => ['required', 'string', 'max:20', Rule::in($allowedColors)],
        ];
        $rules['foto'] = [$imageRequired ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'];
        $data = $request->validate($rules);
        $data['orden'] = (int) ($data['orden'] ?? 0);
        $data['activo'] = $request->boolean('activo');
        return $data;
    }

    private function colores($empresa): array
    {
        return collect([
            ['nombre' => 'Primario', 'valor' => $empresa->color_primario],
            ['nombre' => 'Secundario', 'valor' => $empresa->color_secundario],
            ['nombre' => 'Fondo', 'valor' => $empresa->color_fondo],
            ['nombre' => 'Texto', 'valor' => $empresa->color_texto],
        ])->filter(fn ($color) => filled($color['valor']))->values()->all();
    }

    private function authorizeAccess(EmpresaServicio $servicio): void
    {
        abort_unless($servicio->empresa_id === Auth::user()->empresa_id, 403);
    }
}
