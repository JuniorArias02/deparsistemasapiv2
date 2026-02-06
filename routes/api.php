<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/ping', function () {
    return response()->json([
        'status' => 'ok',
        'api' => 'pure',
        'laravel' => 12
    ]);
});

Route::group(['middleware' => 'api', 'prefix' => 'auth'], function ($router) {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('me', [AuthController::class, 'me']);
});

Route::group(['middleware' => 'api'], function () {
    Route::apiResource('usuarios', App\Http\Controllers\UsuarioController::class);
    // Inventario Routes
    Route::post('inventario', [App\Http\Controllers\InventarioController::class, 'store']);
    Route::put('inventario/{id}', [App\Http\Controllers\InventarioController::class, 'update']);
    Route::delete('inventario/{id}', [App\Http\Controllers\InventarioController::class, 'destroy']);
    
    // Roles Routes
    Route::apiResource('roles', App\Http\Controllers\RolController::class);
    Route::put('roles/{id}/permissions', [App\Http\Controllers\RolController::class, 'assignPermissions']);

    // CP Tables Routes
    Route::apiResource('cp-productos', App\Http\Controllers\CpProductoController::class);
    Route::apiResource('cp-productos-servicios', App\Http\Controllers\CpProductoServicioController::class);
    Route::apiResource('cp-proveedores', App\Http\Controllers\CpProveedorController::class);
    Route::apiResource('cp-tipos-solicitud', App\Http\Controllers\CpTipoSolicitudController::class);

    // Sedes and Dependencias Routes
    Route::apiResource('sedes', App\Http\Controllers\SedeController::class);
    Route::apiResource('dependencias-sedes', App\Http\Controllers\DependenciaSedeController::class);

    // Personal and Cargo Routes
    Route::apiResource('p-cargos', App\Http\Controllers\PCargoController::class);
    Route::apiResource('personal', App\Http\Controllers\PersonalController::class);
});
