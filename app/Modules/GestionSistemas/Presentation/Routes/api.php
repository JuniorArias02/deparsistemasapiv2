<?php

use Illuminate\Support\Facades\Route;
use App\Modules\GestionSistemas\Presentation\Controllers\ActaEntregaController;
use App\Modules\GestionSistemas\Presentation\Controllers\ActaDevolucionController;

Route::middleware('auth:api')->prefix('gestion-sistemas')->group(function () {
    // Actas de Entrega
    Route::get('/actas-entrega', [ActaEntregaController::class, 'index']);
    Route::post('/actas-entrega', [ActaEntregaController::class, 'store']);
    Route::get('/actas-entrega/{id}', [ActaEntregaController::class, 'show']);
    Route::get('/actas-entrega/{id}/exportar-excel', [ActaEntregaController::class, 'exportExcel']);
    Route::get('/actas-entrega/{id}/exportar-pdf', [ActaEntregaController::class, 'exportPdf']);
    Route::match(['put', 'post'], '/actas-entrega/{id}', [ActaEntregaController::class, 'update']);
    Route::delete('/actas-entrega/{id}', [ActaEntregaController::class, 'destroy']);

    // Actas de Devolución
    Route::get('/actas-devolucion', [ActaDevolucionController::class, 'index']);
    Route::post('/actas-devolucion', [ActaDevolucionController::class, 'store']);
    Route::get('/actas-devolucion/{id}', [ActaDevolucionController::class, 'show']);
    Route::delete('/actas-devolucion/{id}', [ActaDevolucionController::class, 'destroy']);

    // PcEquipos
    Route::get('/pc-equipos/buscar', [\App\Modules\GestionSistemas\Presentation\Controllers\PcEquipoController::class, 'buscar']);
    Route::apiResource('/pc-equipos', \App\Modules\GestionSistemas\Presentation\Controllers\PcEquipoController::class);
    Route::get('/pc-equipos/{id}/hoja-vida', [\App\Modules\GestionSistemas\Presentation\Controllers\PcEquipoHojaVidaController::class, 'show']);

    // Características Técnicas
    Route::get('/pc-caracteristicas-tecnicas/equipo/{equipo_id}', [\App\Modules\GestionSistemas\Presentation\Controllers\PcCaracteristicasTecnicasController::class, 'showByEquipo']);
    Route::apiResource('/pc-caracteristicas-tecnicas', \App\Modules\GestionSistemas\Presentation\Controllers\PcCaracteristicasTecnicasController::class);

    // Licencias de Software
    Route::get('/pc-licencias-software/equipo/{equipo_id}', [\App\Modules\GestionSistemas\Presentation\Controllers\PcLicenciasSoftwareController::class, 'showByEquipo']);
    Route::post('/pc-licencias-software', [\App\Modules\GestionSistemas\Presentation\Controllers\PcLicenciasSoftwareController::class, 'storeOrUpdate']);

    // Mantenimientos
    Route::get('/pc-mantenimientos/cronograma', [\App\Modules\GestionSistemas\Presentation\Controllers\PcMantenimientoController::class, 'cronograma']);
    Route::get('/pc-mantenimientos', [\App\Modules\GestionSistemas\Presentation\Controllers\PcMantenimientoController::class, 'index']);
    Route::get('/pc-mantenimientos/equipo/{equipo_id}', [\App\Modules\GestionSistemas\Presentation\Controllers\PcMantenimientoController::class, 'showByEquipo']);
    Route::post('/pc-mantenimientos', [\App\Modules\GestionSistemas\Presentation\Controllers\PcMantenimientoController::class, 'store']);
    Route::get('/pc-mantenimientos/{id}', [\App\Modules\GestionSistemas\Presentation\Controllers\PcMantenimientoController::class, 'show']);
    Route::put('/pc-mantenimientos/{id}', [\App\Modules\GestionSistemas\Presentation\Controllers\PcMantenimientoController::class, 'update']);
    Route::delete('/pc-mantenimientos/{id}', [\App\Modules\GestionSistemas\Presentation\Controllers\PcMantenimientoController::class, 'destroy']);
    Route::post('/pc-mantenimientos/{id}/actualizar-firmas', [\App\Modules\GestionSistemas\Presentation\Controllers\PcMantenimientoController::class, 'actualizarFirmas']);
});
