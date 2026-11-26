<?php

// Rutas principales de la aplicación (landing y panel)

use App\Http\Controllers\ContactoController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Panel\AseguradorasController;
use App\Http\Controllers\Panel\AutorizacionesCancelacionController;
use App\Http\Controllers\Panel\ClientesController;
use App\Http\Controllers\Panel\CodigoPostalController;
use App\Http\Controllers\Panel\ConfiguracionController;
use App\Http\Controllers\Panel\ConvenioConceptoAdicionalController;
use App\Http\Controllers\Panel\ConvenioManiobraEspecialController;
use App\Http\Controllers\Panel\ConveniosController;
use App\Http\Controllers\Panel\ConvenioTarifaController;
use App\Http\Controllers\Panel\CotizacionesController;
use App\Http\Controllers\Panel\DashboardController;
use App\Http\Controllers\Panel\EmpleadosController;
use App\Http\Controllers\Panel\FacturacionController;
use App\Http\Controllers\Panel\ImportarClientesController;
use App\Http\Controllers\Panel\ImportarConveniosController;
use App\Http\Controllers\Panel\IntegracionesController;
use App\Http\Controllers\Panel\MantenimientosController;
use App\Http\Controllers\Panel\NotificacionesController;
use App\Http\Controllers\Panel\OficinasController;
use App\Http\Controllers\Panel\OperadoresController;
use App\Http\Controllers\Panel\PagosController;
use App\Http\Controllers\Panel\PenalizacionController;
use App\Http\Controllers\Panel\PerfilController;
use App\Http\Controllers\Panel\RedesSocialesController;
use App\Http\Controllers\Panel\ReportesController;
use App\Http\Controllers\Panel\ServiciosController;
use App\Http\Controllers\Panel\ServiciosLandingController;
use App\Http\Controllers\Panel\TarifasPropiasController;
use App\Http\Controllers\Panel\TiposServicioController;
use App\Http\Controllers\Panel\UnidadesController;
use App\Http\Controllers\Panel\UploadController;
use App\Http\Controllers\Panel\UsuariosController;
use Illuminate\Support\Facades\Route;

// === Rutas públicas (Landing Page) ===
Route::get('/', [LandingController::class, 'index']);

Route::post('/contacto', [ContactoController::class, 'store'])
    ->middleware('throttle:10,1');

Route::get('/solicitar', [LandingController::class, 'solicitar'])->name('solicitar');
Route::post('/solicitar', [LandingController::class, 'solicitarStore'])
    ->middleware('throttle:10,1')
    ->name('solicitar.store');
Route::get('/rastrear', [LandingController::class, 'rastrear'])
    ->middleware('throttle:30,1')
    ->name('rastrear');
Route::get('/soporte', [LandingController::class, 'soporte'])->name('soporte');

// === Rutas protegidas del panel ===
Route::middleware(['auth'])->prefix('panel')->name('panel.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/pagos/{id}/comprobante', [PagosController::class, 'comprobante'])
        ->whereNumber('id')
        ->name('pagos.comprobante');

    // === Módulos accesibles para superadmin, admin y cotizador ===
    Route::middleware('role:admin,cotizador')->group(function () {
        Route::get('/api/codigo-postal/{cp}', [CodigoPostalController::class, 'buscar'])
            ->middleware('throttle:30,1')
            ->name('api.codigo-postal.buscar');
        Route::get('/api/cotizacion/tarifa', [CotizacionesController::class, 'obtenerTarifa'])
            ->name('api.cotizacion.tarifa');

        Route::get('/cotizaciones', [CotizacionesController::class, 'index'])->name('cotizaciones.index');
        Route::post('/cotizaciones/rutas', [CotizacionesController::class, 'rutas'])
            ->middleware('throttle:20,1')
            ->name('cotizaciones.rutas');
        Route::get('/cotizaciones/create', [CotizacionesController::class, 'create'])->name('cotizaciones.create');
        Route::post('/cotizaciones', [CotizacionesController::class, 'store'])->name('cotizaciones.store');
        Route::get('/cotizaciones/{id}', [CotizacionesController::class, 'show'])->whereNumber('id')->name('cotizaciones.show');
        Route::get('/cotizaciones/{id}/edit', [CotizacionesController::class, 'edit'])->whereNumber('id')->name('cotizaciones.edit');
        Route::put('/cotizaciones/{id}', [CotizacionesController::class, 'update'])->whereNumber('id')->name('cotizaciones.update');
        Route::delete('/cotizaciones/{id}', [CotizacionesController::class, 'destroy'])->whereNumber('id')->name('cotizaciones.destroy');
        Route::post('/cotizaciones/{id}/aprobar', [CotizacionesController::class, 'aprobar'])->whereNumber('id')->name('cotizaciones.aprobar');
        Route::post('/cotizaciones/{id}/rechazar', [CotizacionesController::class, 'rechazar'])->whereNumber('id')->name('cotizaciones.rechazar');
        Route::post('/cotizaciones/{id}/pagar-tarjeta', [CotizacionesController::class, 'pagarTarjeta'])
            ->whereNumber('id')
            ->middleware('throttle:10,1')
            ->name('cotizaciones.pagar');

        Route::get('/facturacion', [FacturacionController::class, 'index'])->name('facturacion.index');
        Route::post('/facturacion', [FacturacionController::class, 'store'])->name('facturacion.store');
        Route::get('/facturacion/{id}', [FacturacionController::class, 'show'])->whereNumber('id')->name('facturacion.show');
        Route::post('/facturacion/{id}/enviar', [FacturacionController::class, 'enviarEmail'])->whereNumber('id')->name('facturacion.enviar');

        Route::get('/pagos', [PagosController::class, 'index'])->name('pagos.index');

        // Machotes e importación Excel: solo administradores y superadmin.
        Route::middleware('role:admin')->group(function () {
            Route::get('/clientes/machote', [ImportarClientesController::class, 'machote'])->name('clientes.machote');
            Route::post('/clientes/importar', [ImportarClientesController::class, 'store'])->name('clientes.importar');
            Route::get('/convenios/machote', [ImportarConveniosController::class, 'machote'])->name('convenios.machote');
            Route::post('/convenios/importar', [ImportarConveniosController::class, 'store'])->name('convenios.importar');
        });

        Route::get('/clientes', [ClientesController::class, 'index'])->name('clientes.index');
        Route::get('/clientes/create', [ClientesController::class, 'create'])->name('clientes.create');
        Route::post('/clientes', [ClientesController::class, 'store'])->name('clientes.store');
        Route::get('/clientes/{id}', [ClientesController::class, 'show'])->whereNumber('id')->name('clientes.show');
        Route::get('/clientes/{id}/edit', [ClientesController::class, 'edit'])->whereNumber('id')->name('clientes.edit');
        Route::put('/clientes/{id}', [ClientesController::class, 'update'])->whereNumber('id')->name('clientes.update');
        Route::delete('/clientes/{id}', [ClientesController::class, 'destroy'])->whereNumber('id')->name('clientes.destroy');

        Route::get('/aseguradoras', [AseguradorasController::class, 'index'])->name('aseguradoras.index');
        Route::get('/aseguradoras/create', [AseguradorasController::class, 'create'])->name('aseguradoras.create');
        Route::post('/aseguradoras', [AseguradorasController::class, 'store'])->name('aseguradoras.store');
        Route::get('/aseguradoras/{id}', [AseguradorasController::class, 'show'])->whereNumber('id')->name('aseguradoras.show');
        Route::get('/aseguradoras/{id}/edit', [AseguradorasController::class, 'edit'])->whereNumber('id')->name('aseguradoras.edit');
        Route::put('/aseguradoras/{id}', [AseguradorasController::class, 'update'])->whereNumber('id')->name('aseguradoras.update');
        Route::put('/aseguradoras/{id}/contacto/{contacto}/toggle', [AseguradorasController::class, 'toggleContacto'])->whereNumber(['id', 'contacto'])->name('aseguradoras.contacto.toggle');
        Route::delete('/aseguradoras/{id}/contacto/{contacto}', [AseguradorasController::class, 'destroyContacto'])->whereNumber(['id', 'contacto'])->name('aseguradoras.contacto.destroy');
        Route::delete('/aseguradoras/{id}', [AseguradorasController::class, 'destroy'])->whereNumber('id')->name('aseguradoras.destroy');

        Route::get('/convenios', [ConveniosController::class, 'index'])->name('convenios.index');
        Route::get('/convenios/create', [ConveniosController::class, 'create'])->name('convenios.create');
        Route::post('/convenios', [ConveniosController::class, 'store'])->name('convenios.store');
        Route::get('/convenios/{id}', [ConveniosController::class, 'show'])->whereNumber('id')->name('convenios.show');
        Route::get('/convenios/{id}/edit', [ConveniosController::class, 'edit'])->whereNumber('id')->name('convenios.edit');
        Route::put('/convenios/{id}', [ConveniosController::class, 'update'])->whereNumber('id')->name('convenios.update');
        Route::delete('/convenios/{id}', [ConveniosController::class, 'destroy'])->whereNumber('id')->name('convenios.destroy');

        Route::get('/convenio-tarifas', [ConvenioTarifaController::class, 'index'])->name('convenio-tarifas.index');
        Route::get('/convenio-tarifas/create', [ConvenioTarifaController::class, 'create'])->name('convenio-tarifas.create');
        Route::post('/convenio-tarifas', [ConvenioTarifaController::class, 'store'])->name('convenio-tarifas.store');
        Route::get('/convenio-tarifas/{id}/edit', [ConvenioTarifaController::class, 'edit'])->whereNumber('id')->name('convenio-tarifas.edit');
        Route::put('/convenio-tarifas/{id}', [ConvenioTarifaController::class, 'update'])->whereNumber('id')->name('convenio-tarifas.update');
        Route::delete('/convenio-tarifas/{id}', [ConvenioTarifaController::class, 'destroy'])->whereNumber('id')->name('convenio-tarifas.destroy');

        Route::get('/servicios-especiales', [ConvenioManiobraEspecialController::class, 'index'])->name('servicios-especiales.index');
        Route::get('/servicios-especiales/create', [ConvenioManiobraEspecialController::class, 'create'])->name('servicios-especiales.create');
        Route::post('/servicios-especiales', [ConvenioManiobraEspecialController::class, 'store'])->name('servicios-especiales.store');
        Route::get('/servicios-especiales/{id}/edit', [ConvenioManiobraEspecialController::class, 'edit'])->whereNumber('id')->name('servicios-especiales.edit');
        Route::put('/servicios-especiales/{id}', [ConvenioManiobraEspecialController::class, 'update'])->whereNumber('id')->name('servicios-especiales.update');
        Route::delete('/servicios-especiales/{id}', [ConvenioManiobraEspecialController::class, 'destroy'])->whereNumber('id')->name('servicios-especiales.destroy');

        Route::get('/conceptos-adicionales', [ConvenioConceptoAdicionalController::class, 'index'])->name('conceptos-adicionales.index');
        Route::get('/conceptos-adicionales/create', [ConvenioConceptoAdicionalController::class, 'create'])->name('conceptos-adicionales.create');
        Route::post('/conceptos-adicionales', [ConvenioConceptoAdicionalController::class, 'store'])->name('conceptos-adicionales.store');
        Route::get('/conceptos-adicionales/{id}/edit', [ConvenioConceptoAdicionalController::class, 'edit'])->whereNumber('id')->name('conceptos-adicionales.edit');
        Route::put('/conceptos-adicionales/{id}', [ConvenioConceptoAdicionalController::class, 'update'])->whereNumber('id')->name('conceptos-adicionales.update');
        Route::delete('/conceptos-adicionales/{id}', [ConvenioConceptoAdicionalController::class, 'destroy'])->whereNumber('id')->name('conceptos-adicionales.destroy');

        Route::get('/penalizaciones', [PenalizacionController::class, 'index'])->name('penalizaciones.index');
        Route::get('/penalizaciones/create', [PenalizacionController::class, 'create'])->name('penalizaciones.create');
        Route::post('/penalizaciones', [PenalizacionController::class, 'store'])->name('penalizaciones.store');
        Route::get('/penalizaciones/{id}/edit', [PenalizacionController::class, 'edit'])->whereNumber('id')->name('penalizaciones.edit');
        Route::put('/penalizaciones/{id}', [PenalizacionController::class, 'update'])->whereNumber('id')->name('penalizaciones.update');
        Route::delete('/penalizaciones/{id}', [PenalizacionController::class, 'destroy'])->whereNumber('id')->name('penalizaciones.destroy');

        Route::get('/tarifas-propias', [TarifasPropiasController::class, 'index'])->name('tarifas-propias.index');
        Route::get('/tarifas-propias/create', [TarifasPropiasController::class, 'create'])->name('tarifas-propias.create');
        Route::post('/tarifas-propias', [TarifasPropiasController::class, 'store'])->name('tarifas-propias.store');
        Route::get('/tarifas-propias/{id}/edit', [TarifasPropiasController::class, 'edit'])->whereNumber('id')->name('tarifas-propias.edit');
        Route::put('/tarifas-propias/{id}', [TarifasPropiasController::class, 'update'])->whereNumber('id')->name('tarifas-propias.update');
        Route::delete('/tarifas-propias/{id}', [TarifasPropiasController::class, 'destroy'])->whereNumber('id')->name('tarifas-propias.destroy');

        Route::get('/reportes', [ReportesController::class, 'index'])->name('reportes.index');
        Route::post('/reportes/servicios', [ReportesController::class, 'servicios'])->name('reportes.servicios');
        Route::post('/reportes/costos', [ReportesController::class, 'costos'])->name('reportes.costos');
        Route::post('/reportes/rendimiento', [ReportesController::class, 'rendimiento'])->name('reportes.rendimiento');
        Route::post('/reportes/calificaciones', [ReportesController::class, 'calificaciones'])->name('reportes.calificaciones');
    });

    // === Módulos accesibles para superadmin, admin, cotizador y operador ===
    Route::middleware('role:admin,cotizador,operador')->group(function () {
        Route::put('/operadores/disponibilidad', [OperadoresController::class, 'cambiarDisponibilidad'])->name('operadores.disponibilidad');

        Route::get('/servicios', [ServiciosController::class, 'index'])->name('servicios.index');

        // Rutas estáticas de servicios declaradas ANTES de /servicios/{id}
        Route::middleware('role:admin,cotizador')->group(function () {
            Route::get('/servicios/create', [ServiciosController::class, 'create'])->name('servicios.create');
            Route::post('/servicios', [ServiciosController::class, 'store'])->name('servicios.store');
            Route::get('/servicios/{id}/edit', [ServiciosController::class, 'edit'])->whereNumber('id')->name('servicios.edit');
            Route::put('/servicios/{id}', [ServiciosController::class, 'update'])->whereNumber('id')->name('servicios.update');
            Route::delete('/servicios/{id}', [ServiciosController::class, 'destroy'])->whereNumber('id')->name('servicios.destroy');
        });

        Route::get('/servicios/{id}', [ServiciosController::class, 'show'])->whereNumber('id')->name('servicios.show');
        Route::post('/servicios/{id}/avanzar', [ServiciosController::class, 'avanzarEstado'])->whereNumber('id')->name('servicios.avanzar');
        Route::post('/servicios/{id}/solicitar-cancelacion', [ServiciosController::class, 'solicitarCancelacion'])->whereNumber('id')->name('servicios.solicitar-cancelacion');

        Route::get('/autorizaciones-cancelacion', [AutorizacionesCancelacionController::class, 'index'])->name('autorizaciones-cancelacion.index');
        Route::middleware('role:admin,cotizador')->group(function () {
            Route::post('/autorizaciones-cancelacion/{id}/aprobar', [AutorizacionesCancelacionController::class, 'aprobar'])->whereNumber('id')->name('autorizaciones-cancelacion.aprobar');
            Route::post('/autorizaciones-cancelacion/{id}/rechazar', [AutorizacionesCancelacionController::class, 'rechazar'])->whereNumber('id')->name('autorizaciones-cancelacion.rechazar');
        });
    });

    // === Módulos exclusivos para administradores y superadmin ===
    Route::middleware('role:admin')->group(function () {
        Route::get('/tipos-servicio', [TiposServicioController::class, 'index'])->name('tipos-servicio.index');
        Route::get('/tipos-servicio/create', [TiposServicioController::class, 'create'])->name('tipos-servicio.create');
        Route::post('/tipos-servicio', [TiposServicioController::class, 'store'])->name('tipos-servicio.store');
        Route::get('/tipos-servicio/{id}/edit', [TiposServicioController::class, 'edit'])->whereNumber('id')->name('tipos-servicio.edit');
        Route::put('/tipos-servicio/{id}', [TiposServicioController::class, 'update'])->whereNumber('id')->name('tipos-servicio.update');
        Route::delete('/tipos-servicio/{id}', [TiposServicioController::class, 'destroy'])->whereNumber('id')->name('tipos-servicio.destroy');

        Route::get('/oficinas', [OficinasController::class, 'index'])->name('oficinas.index');
        Route::get('/oficinas/create', [OficinasController::class, 'create'])->name('oficinas.create');
        Route::post('/oficinas', [OficinasController::class, 'store'])->name('oficinas.store');
        Route::get('/oficinas/{id}', [OficinasController::class, 'show'])->whereNumber('id')->name('oficinas.show');
        Route::get('/oficinas/{id}/edit', [OficinasController::class, 'edit'])->whereNumber('id')->name('oficinas.edit');
        Route::put('/oficinas/{id}', [OficinasController::class, 'update'])->whereNumber('id')->name('oficinas.update');
        Route::delete('/oficinas/{id}', [OficinasController::class, 'destroy'])->whereNumber('id')->name('oficinas.destroy');

        Route::get('/unidades', [UnidadesController::class, 'index'])->name('unidades.index');
        Route::get('/unidades/create', [UnidadesController::class, 'create'])->name('unidades.create');
        Route::post('/unidades', [UnidadesController::class, 'store'])->name('unidades.store');
        Route::get('/unidades/{id}', [UnidadesController::class, 'show'])->whereNumber('id')->name('unidades.show');
        Route::get('/unidades/{id}/edit', [UnidadesController::class, 'edit'])->whereNumber('id')->name('unidades.edit');
        Route::put('/unidades/{id}', [UnidadesController::class, 'update'])->whereNumber('id')->name('unidades.update');
        Route::delete('/unidades/{id}', [UnidadesController::class, 'destroy'])->whereNumber('id')->name('unidades.destroy');

        Route::get('/mantenimientos', [MantenimientosController::class, 'index'])->name('mantenimientos.index');
        Route::get('/mantenimientos/create', [MantenimientosController::class, 'create'])->name('mantenimientos.create');
        Route::post('/mantenimientos', [MantenimientosController::class, 'store'])->name('mantenimientos.store');
        Route::get('/mantenimientos/{id}', [MantenimientosController::class, 'show'])->whereNumber('id')->name('mantenimientos.show');
        Route::get('/mantenimientos/{id}/edit', [MantenimientosController::class, 'edit'])->whereNumber('id')->name('mantenimientos.edit');
        Route::put('/mantenimientos/{id}', [MantenimientosController::class, 'update'])->whereNumber('id')->name('mantenimientos.update');
        Route::delete('/mantenimientos/{id}', [MantenimientosController::class, 'destroy'])->whereNumber('id')->name('mantenimientos.destroy');

        Route::get('/empleados', [EmpleadosController::class, 'index'])->name('empleados.index');
        Route::get('/empleados/create', [EmpleadosController::class, 'create'])->name('empleados.create');
        Route::post('/empleados', [EmpleadosController::class, 'store'])->name('empleados.store');
        Route::get('/empleados/{id}', [EmpleadosController::class, 'show'])->whereNumber('id')->name('empleados.show');
        Route::get('/empleados/{id}/edit', [EmpleadosController::class, 'edit'])->whereNumber('id')->name('empleados.edit');
        Route::put('/empleados/{id}', [EmpleadosController::class, 'update'])->whereNumber('id')->name('empleados.update');
        Route::delete('/empleados/{id}', [EmpleadosController::class, 'destroy'])->whereNumber('id')->name('empleados.destroy');

        Route::get('/operadores', [OperadoresController::class, 'index'])->name('operadores.index');
        Route::get('/operadores/create', [OperadoresController::class, 'create'])->name('operadores.create');
        Route::post('/operadores', [OperadoresController::class, 'store'])->name('operadores.store');
        Route::get('/operadores/{id}', [OperadoresController::class, 'show'])->whereNumber('id')->name('operadores.show');
        Route::get('/operadores/{id}/edit', [OperadoresController::class, 'edit'])->whereNumber('id')->name('operadores.edit');
        Route::put('/operadores/{id}', [OperadoresController::class, 'update'])->whereNumber('id')->name('operadores.update');
        Route::delete('/operadores/{id}', [OperadoresController::class, 'destroy'])->whereNumber('id')->name('operadores.destroy');

        Route::get('/usuarios', [UsuariosController::class, 'index'])->name('usuarios.index');
        Route::get('/usuarios/create', [UsuariosController::class, 'create'])->name('usuarios.create');
        Route::post('/usuarios', [UsuariosController::class, 'store'])->name('usuarios.store');
        Route::get('/usuarios/{id}', [UsuariosController::class, 'show'])->whereNumber('id')->name('usuarios.show');
        Route::get('/usuarios/{id}/edit', [UsuariosController::class, 'edit'])->whereNumber('id')->name('usuarios.edit');
        Route::put('/usuarios/{id}', [UsuariosController::class, 'update'])->whereNumber('id')->name('usuarios.update');
        Route::delete('/usuarios/{id}', [UsuariosController::class, 'destroy'])->whereNumber('id')->name('usuarios.destroy');
        Route::put('/usuarios/{id}/rol', [UsuariosController::class, 'cambiarRol'])->whereNumber('id')->name('usuarios.rol');
        Route::put('/usuarios/{id}/desbloquear', [UsuariosController::class, 'desbloquear'])->whereNumber('id')->name('usuarios.desbloquear');

        Route::get('/configuracion', [ConfiguracionController::class, 'index'])->name('configuracion.index');
        Route::post('/configuracion', [ConfiguracionController::class, 'update'])->name('configuracion.update');
        Route::post('/upload', [UploadController::class, 'store'])
            ->middleware('throttle:20,1')
            ->name('upload.store');

        Route::resource('/accesos-rapidos', RedesSocialesController::class)
            ->parameters(['accesos-rapidos' => 'accesoRapido'])
            ->except(['show']);
        Route::resource('/servicios-landing', ServiciosLandingController::class)
            ->parameters(['servicios-landing' => 'servicioLanding'])
            ->except(['show']);
    });

    // === Módulos exclusivos para Super Administrador (llaves API / integraciones) ===
    Route::middleware('role:superadmin')->group(function () {
        Route::get('/integraciones', [IntegracionesController::class, 'index'])->name('integraciones.index');
        Route::put('/integraciones/{id}', [IntegracionesController::class, 'update'])->whereNumber('id')->name('integraciones.update');
    });

    // === Rutas del perfil y notificaciones (todos los roles autenticados) ===
    Route::get('/mi-perfil', [PerfilController::class, 'index'])->name('mi-perfil');
    Route::put('/mi-perfil/telefono', [PerfilController::class, 'updateTelefono'])->name('mi-perfil.telefono');
    Route::put('/mi-perfil/password', [PerfilController::class, 'updatePassword'])
        ->middleware('throttle:6,1')
        ->name('mi-perfil.password');
    Route::post('/mi-perfil/foto', [PerfilController::class, 'updateFoto'])
        ->middleware('throttle:15,1')
        ->name('mi-perfil.foto');
    Route::delete('/mi-perfil/foto', [PerfilController::class, 'destroyFoto'])->name('mi-perfil.foto.destroy');

    Route::get('/notificaciones', [NotificacionesController::class, 'index'])->name('notificaciones.index');
    Route::post('/notificaciones/{id}/marcar-leida', [NotificacionesController::class, 'marcarLeida'])->whereNumber('id')->name('notificaciones.marcar-leida');
    Route::post('/notificaciones/marcar-todas', [NotificacionesController::class, 'marcarTodas'])->name('notificaciones.marcar-todas');
    Route::get('/notificaciones/no-leidas', [NotificacionesController::class, 'unreadCount'])->name('notificaciones.no-leidas');
    Route::delete('/notificaciones/{id}', [NotificacionesController::class, 'eliminar'])->whereNumber('id')->name('notificaciones.eliminar');

    // === Rutas exclusivas para clientes ===
    Route::middleware('role:cliente')->group(function () {
        Route::get('/evaluar-servicio/{id}', [ServiciosController::class, 'evaluar'])->whereNumber('id')->name('servicios.evaluar');
        Route::post('/evaluar-servicio/{id}', [ServiciosController::class, 'guardarEvaluacion'])->whereNumber('id')->name('servicios.evaluar.store');
        Route::get('/mis-evaluaciones', [ServiciosController::class, 'misEvaluaciones'])->name('cliente.evaluaciones');

        Route::get('/cliente/servicios/{id}', [ServiciosController::class, 'show'])->whereNumber('id')->name('servicios.show.cliente');
        Route::get('/cliente/cotizaciones/{id}', [CotizacionesController::class, 'show'])->whereNumber('id')->name('cotizaciones.show.cliente');
        Route::post('/cliente/cotizaciones/{id}/aprobar', [CotizacionesController::class, 'aprobarCliente'])->whereNumber('id')->name('cotizaciones.aprobar.cliente');
        Route::post('/cliente/cotizaciones/{id}/pagar-tarjeta', [CotizacionesController::class, 'pagarTarjeta'])
            ->whereNumber('id')
            ->middleware('throttle:10,1')
            ->name('cotizaciones.pagar.cliente');
        Route::get('/cliente/facturacion/{id}', [FacturacionController::class, 'show'])->whereNumber('id')->name('facturacion.show.cliente');

        Route::get('/mis-cotizaciones', [CotizacionesController::class, 'misCotizaciones'])->name('cliente.cotizaciones');
        Route::get('/mis-facturas', [FacturacionController::class, 'misFacturas'])->name('cliente.facturas');
    });

    // Descarga PDF factura (autorizada internamente para superadmin, admin, cotizador y cliente dueño)
    Route::middleware('role:admin,cotizador,cliente')->group(function () {
        Route::get('/facturas/{id}/pdf', [FacturacionController::class, 'pdf'])->whereNumber('id')->name('facturas.pdf');
    });
});

require __DIR__.'/auth.php';
