<?php

use Illuminate\Support\Facades\Route;
use App\Modules\GestionCompras\Presentation\Controllers\InventarioSearchController;

Route::prefix('gestion-compras')->middleware('auth:api')->group(function () {
    Route::get('/inventario/buscar', [InventarioSearchController::class, 'search']);
});
