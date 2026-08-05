<?php

// Rutas principales de la aplicación (landing y panel)

use App\Http\Controllers\LandingController;
use App\Http\Controllers\Panel\DashboardController;
use App\Http\Controllers\Panel\CotizacionesController;
use App\Http\Controllers\Panel\ServiciosController;
use App\Http\Controllers\Panel\AutorizacionesCancelacionController;
use App\Http\Controllers\Panel\FacturacionController;
use App\Http\Controllers\Panel\ClientesController;
use App\Http\Controllers\Panel\AseguradorasController;
use App\Http\Controllers\Panel\TiposServicioController;
use App\Http\Controllers\Panel\ConveniosController;
use App\Http\Controllers\Panel\TarifasPropiasController;
use App\Http\Controllers\Panel\OficinasController;
use App\Http\Controllers\Panel\UnidadesController;
use App\Http\Controllers\Panel\MantenimientosController;
use App\Http\Controllers\Panel\EmpleadosController;
use App\Http\Controllers\Panel\OperadoresController;
use App\Http\Controllers\Panel\UsuariosController;
use App\Http\Controllers\Panel\ConfiguracionController;
use App\Http\Controllers\Panel\IntegracionesController;
use App\Http\Controllers\Panel\UploadController;
use App\Http\Controllers\Panel\ReportesController;
use App\Http\Controllers\Panel\NotificacionesController;
use App\Http\Controllers\Panel\PerfilController;
use App\Http\Controllers\ContactoController;
use Illuminate\Support\Facades\Route;

// === Rutas públicas (Landing Page) ===
Route::get('/', [LandingController::class, 'index']);

Route::post('/contacto', [ContactoController::class, 'store']);

Route::get('/solicitar', [LandingController::class, 'solicitar'])->name('solicitar');
Route::post('/solicitar', [LandingController::class, 'solicitarStore'])->name('solicitar.store');
Route::get('/rastrear', [LandingController::class, 'rastrear']);
Route::get('/soporte', [LandingController::class, 'soporte']);

// Ruta temporal para probar el envío de emails
Route::get('/test-email', function () {
    try {
        \Illuminate\Support\Facades\Mail::raw('Este es un correo de prueba enviado desde SIGESGA. La configuración SMTP de Gmail funciona correctamente.', function ($message) {
            $message->to(env('MAIL_FROM_ADDRESS'))
                    ->subject('Prueba de conexión Gmail - SIGESGA');
        });
        return 'Correo enviado exitosamente. Revisa tu bandeja de entrada.';
    } catch (\Exception $e) {
        return 'Error al enviar: ' . $e->getMessage();
    }
});

// === Rutas protegidas del panel ===
Route::middleware(['auth'])->prefix('panel')->name('panel.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // === Módulos accesibles para admin y cotizador ===
    Route::middleware('role:admin,cotizador')->group(function () {
        Route::get('/cotizaciones', [CotizacionesController::class, 'index'])->name('cotizaciones.index');
        Route::get('/cotizaciones/create', [CotizacionesController::class, 'create'])->name('cotizaciones.create');
        Route::post('/cotizaciones', [CotizacionesController::class, 'store'])->name('cotizaciones.store');
        Route::get('/cotizaciones/{id}', [CotizacionesController::class, 'show'])->name('cotizaciones.show');
        Route::get('/cotizaciones/{id}/edit', [CotizacionesController::class, 'edit'])->name('cotizaciones.edit');
        Route::put('/cotizaciones/{id}', [CotizacionesController::class, 'update'])->name('cotizaciones.update');
        Route::delete('/cotizaciones/{id}', [CotizacionesController::class, 'destroy'])->name('cotizaciones.destroy');

        Route::get('/facturacion', [FacturacionController::class, 'index'])->name('facturacion.index');
        Route::post('/facturacion', [FacturacionController::class, 'store'])->name('facturacion.store');
        Route::get('/facturacion/{id}', [FacturacionController::class, 'show'])->name('facturacion.show');
        Route::post('/facturacion/{id}/enviar', [FacturacionController::class, 'enviarEmail'])->name('facturacion.enviar');

        Route::get('/clientes', [ClientesController::class, 'index'])->name('clientes.index');
        Route::get('/clientes/create', [ClientesController::class, 'create'])->name('clientes.create');
        Route::post('/clientes', [ClientesController::class, 'store'])->name('clientes.store');
        Route::get('/clientes/{id}', [ClientesController::class, 'show'])->name('clientes.show');
        Route::get('/clientes/{id}/edit', [ClientesController::class, 'edit'])->name('clientes.edit');
        Route::put('/clientes/{id}', [ClientesController::class, 'update'])->name('clientes.update');
        Route::delete('/clientes/{id}', [ClientesController::class, 'destroy'])->name('clientes.destroy');

        Route::get('/aseguradoras', [AseguradorasController::class, 'index'])->name('aseguradoras.index');
        Route::get('/aseguradoras/create', [AseguradorasController::class, 'create'])->name('aseguradoras.create');
        Route::post('/aseguradoras', [AseguradorasController::class, 'store'])->name('aseguradoras.store');
        Route::get('/aseguradoras/{id}', [AseguradorasController::class, 'show'])->name('aseguradoras.show');
        Route::get('/aseguradoras/{id}/edit', [AseguradorasController::class, 'edit'])->name('aseguradoras.edit');
        Route::put('/aseguradoras/{id}', [AseguradorasController::class, 'update'])->name('aseguradoras.update');
        Route::delete('/aseguradoras/{id}', [AseguradorasController::class, 'destroy'])->name('aseguradoras.destroy');

        Route::get('/convenios', [ConveniosController::class, 'index'])->name('convenios.index');
        Route::get('/convenios/create', [ConveniosController::class, 'create'])->name('convenios.create');
        Route::post('/convenios', [ConveniosController::class, 'store'])->name('convenios.store');
        Route::get('/convenios/{id}', [ConveniosController::class, 'show'])->name('convenios.show');
        Route::get('/convenios/{id}/edit', [ConveniosController::class, 'edit'])->name('convenios.edit');
        Route::put('/convenios/{id}', [ConveniosController::class, 'update'])->name('convenios.update');
        Route::delete('/convenios/{id}', [ConveniosController::class, 'destroy'])->name('convenios.destroy');

        Route::get('/tarifas-propias', [TarifasPropiasController::class, 'index'])->name('tarifas-propias.index');
        Route::get('/tarifas-propias/create', [TarifasPropiasController::class, 'create'])->name('tarifas-propias.create');
        Route::post('/tarifas-propias', [TarifasPropiasController::class, 'store'])->name('tarifas-propias.store');
        Route::get('/tarifas-propias/{id}/edit', [TarifasPropiasController::class, 'edit'])->name('tarifas-propias.edit');
        Route::put('/tarifas-propias/{id}', [TarifasPropiasController::class, 'update'])->name('tarifas-propias.update');
        Route::delete('/tarifas-propias/{id}', [TarifasPropiasController::class, 'destroy'])->name('tarifas-propias.destroy');

        Route::get('/reportes', [ReportesController::class, 'index'])->name('reportes.index');
        Route::post('/reportes/servicios', [ReportesController::class, 'servicios'])->name('reportes.servicios');
        Route::post('/reportes/costos', [ReportesController::class, 'costos'])->name('reportes.costos');
        Route::post('/reportes/rendimiento', [ReportesController::class, 'rendimiento'])->name('reportes.rendimiento');
        Route::post('/reportes/calificaciones', [ReportesController::class, 'calificaciones'])->name('reportes.calificaciones');
    });

    // === Módulos accesibles para admin, cotizador y operador ===
    Route::middleware('role:admin,cotizador,operador')->group(function () {
        Route::get('/servicios', [ServiciosController::class, 'index'])->name('servicios.index');
        Route::get('/servicios/create', [ServiciosController::class, 'create'])->name('servicios.create');
        Route::post('/servicios', [ServiciosController::class, 'store'])->name('servicios.store');
        Route::get('/servicios/{id}', [ServiciosController::class, 'show'])->name('servicios.show');
        Route::get('/servicios/{id}/edit', [ServiciosController::class, 'edit'])->name('servicios.edit');
        Route::put('/servicios/{id}', [ServiciosController::class, 'update'])->name('servicios.update');
        Route::delete('/servicios/{id}', [ServiciosController::class, 'destroy'])->name('servicios.destroy');

        Route::get('/autorizaciones-cancelacion', [AutorizacionesCancelacionController::class, 'index'])->name('autorizaciones-cancelacion.index');
        Route::post('/autorizaciones-cancelacion/{id}/aprobar', [AutorizacionesCancelacionController::class, 'aprobar'])->name('autorizaciones-cancelacion.aprobar');
        Route::post('/autorizaciones-cancelacion/{id}/rechazar', [AutorizacionesCancelacionController::class, 'rechazar'])->name('autorizaciones-cancelacion.rechazar');
    });

    // === Módulos exclusivos para administradores ===
    Route::middleware('role:admin')->group(function () {
        Route::get('/tipos-servicio', [TiposServicioController::class, 'index'])->name('tipos-servicio.index');
        Route::get('/tipos-servicio/create', [TiposServicioController::class, 'create'])->name('tipos-servicio.create');
        Route::post('/tipos-servicio', [TiposServicioController::class, 'store'])->name('tipos-servicio.store');
        Route::get('/tipos-servicio/{id}/edit', [TiposServicioController::class, 'edit'])->name('tipos-servicio.edit');
        Route::put('/tipos-servicio/{id}', [TiposServicioController::class, 'update'])->name('tipos-servicio.update');
        Route::delete('/tipos-servicio/{id}', [TiposServicioController::class, 'destroy'])->name('tipos-servicio.destroy');

        Route::get('/oficinas', [OficinasController::class, 'index'])->name('oficinas.index');
        Route::get('/oficinas/create', [OficinasController::class, 'create'])->name('oficinas.create');
        Route::post('/oficinas', [OficinasController::class, 'store'])->name('oficinas.store');
        Route::get('/oficinas/{id}', [OficinasController::class, 'show'])->name('oficinas.show');
        Route::get('/oficinas/{id}/edit', [OficinasController::class, 'edit'])->name('oficinas.edit');
        Route::put('/oficinas/{id}', [OficinasController::class, 'update'])->name('oficinas.update');
        Route::delete('/oficinas/{id}', [OficinasController::class, 'destroy'])->name('oficinas.destroy');

        Route::get('/unidades', [UnidadesController::class, 'index'])->name('unidades.index');
        Route::get('/unidades/create', [UnidadesController::class, 'create'])->name('unidades.create');
        Route::post('/unidades', [UnidadesController::class, 'store'])->name('unidades.store');
        Route::get('/unidades/{id}', [UnidadesController::class, 'show'])->name('unidades.show');
        Route::get('/unidades/{id}/edit', [UnidadesController::class, 'edit'])->name('unidades.edit');
        Route::put('/unidades/{id}', [UnidadesController::class, 'update'])->name('unidades.update');
        Route::delete('/unidades/{id}', [UnidadesController::class, 'destroy'])->name('unidades.destroy');

        Route::get('/mantenimientos', [MantenimientosController::class, 'index'])->name('mantenimientos.index');
        Route::get('/mantenimientos/create', [MantenimientosController::class, 'create'])->name('mantenimientos.create');
        Route::post('/mantenimientos', [MantenimientosController::class, 'store'])->name('mantenimientos.store');
        Route::get('/mantenimientos/{id}', [MantenimientosController::class, 'show'])->name('mantenimientos.show');
        Route::get('/mantenimientos/{id}/edit', [MantenimientosController::class, 'edit'])->name('mantenimientos.edit');
        Route::put('/mantenimientos/{id}', [MantenimientosController::class, 'update'])->name('mantenimientos.update');
        Route::delete('/mantenimientos/{id}', [MantenimientosController::class, 'destroy'])->name('mantenimientos.destroy');

        Route::get('/empleados', [EmpleadosController::class, 'index'])->name('empleados.index');
        Route::get('/empleados/create', [EmpleadosController::class, 'create'])->name('empleados.create');
        Route::post('/empleados', [EmpleadosController::class, 'store'])->name('empleados.store');
        Route::get('/empleados/{id}', [EmpleadosController::class, 'show'])->name('empleados.show');
        Route::get('/empleados/{id}/edit', [EmpleadosController::class, 'edit'])->name('empleados.edit');
        Route::put('/empleados/{id}', [EmpleadosController::class, 'update'])->name('empleados.update');
        Route::delete('/empleados/{id}', [EmpleadosController::class, 'destroy'])->name('empleados.destroy');

        Route::get('/operadores', [OperadoresController::class, 'index'])->name('operadores.index');
        Route::get('/operadores/create', [OperadoresController::class, 'create'])->name('operadores.create');
        Route::post('/operadores', [OperadoresController::class, 'store'])->name('operadores.store');
        Route::get('/operadores/{id}', [OperadoresController::class, 'show'])->name('operadores.show');
        Route::get('/operadores/{id}/edit', [OperadoresController::class, 'edit'])->name('operadores.edit');
        Route::put('/operadores/{id}', [OperadoresController::class, 'update'])->name('operadores.update');
        Route::delete('/operadores/{id}', [OperadoresController::class, 'destroy'])->name('operadores.destroy');

        Route::get('/usuarios', [UsuariosController::class, 'index'])->name('usuarios.index');
        Route::get('/usuarios/create', [UsuariosController::class, 'create'])->name('usuarios.create');
        Route::post('/usuarios', [UsuariosController::class, 'store'])->name('usuarios.store');
        Route::get('/usuarios/{id}/edit', [UsuariosController::class, 'edit'])->name('usuarios.edit');
        Route::put('/usuarios/{id}', [UsuariosController::class, 'update'])->name('usuarios.update');
        Route::delete('/usuarios/{id}', [UsuariosController::class, 'destroy'])->name('usuarios.destroy');

        Route::get('/configuracion', [ConfiguracionController::class, 'index'])->name('configuracion.index');
        Route::post('/configuracion', [ConfiguracionController::class, 'update'])->name('configuracion.update');
        Route::post('/upload', [UploadController::class, 'store'])->name('upload.store');

        Route::get('/integraciones', [IntegracionesController::class, 'index'])->name('integraciones.index');
        Route::put('/integraciones/{id}', [IntegracionesController::class, 'update'])->name('integraciones.update');

        Route::get('/notificaciones', [NotificacionesController::class, 'index'])->name('notificaciones.index');
        Route::post('/notificaciones/{id}/reenviar', [NotificacionesController::class, 'reenviar'])->name('notificaciones.reenviar');
    });

    // === Rutas del perfil de usuario (todos los roles) ===
    Route::get('/mi-perfil', [PerfilController::class, 'index'])->name('mi-perfil');
    Route::put('/mi-perfil/telefono', [PerfilController::class, 'updateTelefono'])->name('mi-perfil.telefono');
    Route::put('/mi-perfil/password', [PerfilController::class, 'updatePassword'])->name('mi-perfil.password');
    Route::post('/mi-perfil/foto', [PerfilController::class, 'updateFoto'])->name('mi-perfil.foto');
    Route::delete('/mi-perfil/foto', [PerfilController::class, 'destroyFoto'])->name('mi-perfil.foto.destroy');
});

require __DIR__.'/auth.php';
