<?php

use Illuminate\Support\Facades\Route;
use App\Modules\BuzonSugerencias\Presentation\Controllers\BuzonSugerenciaController;

Route::middleware('auth:api')->prefix('buzon-sugerencias')->group(function () {
    Route::get('/no-leidos-count', [BuzonSugerenciaController::class, 'getNoLeidosCount']);
    Route::get('/tickets-no-leidos', [BuzonSugerenciaController::class, 'getTicketsNoLeidos']);
    Route::get('/', [BuzonSugerenciaController::class, 'index']);
    Route::post('/', [BuzonSugerenciaController::class, 'store']);
    Route::get('/{codigo}', [BuzonSugerenciaController::class, 'show']);
    Route::post('/{id}/adjuntos', [BuzonSugerenciaController::class, 'uploadAdjuntos']);
    Route::post('/{id}/comentarios', [BuzonSugerenciaController::class, 'storeComentario']);
    Route::post('/{id}/leer-comentarios', [BuzonSugerenciaController::class, 'marcarComentariosLeidos']);
    Route::patch('/{id}/estado', [BuzonSugerenciaController::class, 'updateEstado']);
    Route::patch('/{id}/asignar', [BuzonSugerenciaController::class, 'asignarResponsable']);
});
