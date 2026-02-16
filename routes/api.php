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
    Route::post('forgot-password', [AuthController::class, 'sendResetCode']);
    Route::post('verify-code', [AuthController::class, 'verifyResetCode']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
});

Route::group(['middleware' => 'api'], function () {
    Route::apiResource('usuarios', App\Http\Controllers\UsuarioController::class);
    
    // Inventario Routes (protected with JWT authentication)
    Route::middleware('auth:api')->group(function () {
        Route::get('inventario', [App\Http\Controllers\InventarioController::class, 'index']);
        Route::get('inventario/{id}', [App\Http\Controllers\InventarioController::class, 'show']);
        Route::post('inventario', [App\Http\Controllers\InventarioController::class, 'store']);
        Route::put('inventario/{id}', [App\Http\Controllers\InventarioController::class, 'update']);
        Route::delete('inventario/{id}', [App\Http\Controllers\InventarioController::class, 'destroy']);
        Route::get('/inventario/by-responsable-coordinador', [App\Http\Controllers\InventarioController::class, 'getByResponsableAndCoordinador']);

        // Roles Routes
        Route::apiResource('roles', App\Http\Controllers\RolController::class);
        Route::put('roles/{id}/permissions', [App\Http\Controllers\RolController::class, 'assignPermissions']);

        // CP Tables Routes
        Route::apiResource('cp-productos', App\Http\Controllers\CpProductoController::class);
        Route::apiResource('cp-productos-servicios', App\Http\Controllers\CpProductoServicioController::class);
        Route::apiResource('cp-proveedores', App\Http\Controllers\CpProveedorController::class);
        Route::apiResource('cp-tipos-solicitud', App\Http\Controllers\CpTipoSolicitudController::class);
        Route::apiResource('pc-equipos', App\Http\Controllers\PcEquipoController::class);
        Route::apiResource('pc-caracteristicas-tecnicas', App\Http\Controllers\PcCaracteristicasTecnicasController::class);
        Route::get('pc-caracteristicas-tecnicas/equipo/{equipo_id}', [App\Http\Controllers\PcCaracteristicasTecnicasController::class, 'showByEquipo']);
        Route::apiResource('pc-mantenimientos', App\Http\Controllers\PcMantenimientoController::class);
        Route::get('pc-mantenimientos/equipo/{equipo_id}', [App\Http\Controllers\PcMantenimientoController::class, 'showByEquipo']);
        
        Route::apiResource('pc-entregas', App\Http\Controllers\PcEntregaController::class);
        Route::apiResource('pc-devueltos', App\Http\Controllers\PcDevueltoController::class);
        Route::apiResource('pc-perifericos-entregados', App\Http\Controllers\PcPerifericoEntregadoController::class);
        Route::get('pc-perifericos-entregados/entrega/{entrega_id}', [App\Http\Controllers\PcPerifericoEntregadoController::class, 'showByEntrega']);
        Route::apiResource('datos-empresa', App\Http\Controllers\DatosEmpresaController::class);
        Route::apiResource('pc-licencias-software', App\Http\Controllers\PcLicenciaSoftwareController::class);
        Route::get('pc-licencias-software/equipo/{equipo_id}', [App\Http\Controllers\PcLicenciaSoftwareController::class, 'showByEquipo']);
        Route::apiResource('pc-config-cronograma', App\Http\Controllers\PcConfigCronogramaController::class);

        // Sedes and Dependencias Routes
        Route::apiResource('sedes', App\Http\Controllers\SedeController::class);
        Route::apiResource('dependencias-sedes', App\Http\Controllers\DependenciaSedeController::class);

        // Personal and Cargo Routes
        Route::apiResource('p-cargos', App\Http\Controllers\PCargoController::class);

        // Dashboard Stats
        Route::get('/dashboard/stats', [App\Http\Controllers\DashboardStatsController::class, 'index']);

        // Profile Routes
        Route::post('profile/update', [App\Http\Controllers\ProfileController::class, 'update']);
        Route::post('profile/change-password', [App\Http\Controllers\ProfileController::class, 'changePassword']);
        Route::post('profile/upload-signature', [App\Http\Controllers\ProfileController::class, 'uploadSignature']);
        Route::post('profile/upload-photo', [App\Http\Controllers\ProfileController::class, 'uploadPhoto']);
        Route::post('profile/delete-photo', [App\Http\Controllers\ProfileController::class, 'deletePhoto']);

        // Permisos Routes
        Route::apiResource('permisos', App\Http\Controllers\PermisoController::class);
        Route::get('permisos/roles-assignments/list', [App\Http\Controllers\PermisoController::class, 'getRoles']);
        Route::post('permisos/assign', [App\Http\Controllers\PermisoController::class, 'assignPermisos']);
    });
    Route::apiResource('personal', App\Http\Controllers\PersonalController::class);
    // Cp Dependencias
    Route::apiResource('cp-dependencias', App\Http\Controllers\CpDependenciaController::class);
    // Areas
    Route::apiResource('areas', App\Http\Controllers\AreaController::class);
    // Cp Centro Costos
    Route::apiResource('cp-centro-costos', App\Http\Controllers\CpCentroCostoController::class);

    // CP Pedidos Routes (Protected with JWT)
    Route::middleware('auth:api')->group(function () {
        Route::apiResource('cp-pedidos', App\Http\Controllers\CpPedidoController::class)->except(['update']);
        Route::prefix('cp-pedidos/{id}')->group(function () {
            Route::post('aprobar-compras', [App\Http\Controllers\CpPedidoController::class, 'aprobarCompras']);
            Route::post('rechazar-compras', [App\Http\Controllers\CpPedidoController::class, 'rechazarCompras']);
            Route::post('aprobar-gerencia', [App\Http\Controllers\CpPedidoController::class, 'aprobarGerencia']);
            Route::post('rechazar-gerencia', [App\Http\Controllers\CpPedidoController::class, 'rechazarGerencia']);
        });
    });

    // CP Entrega Activos Fijos Routes (Protected with JWT)
    Route::middleware('auth:api')->group(function () {
        Route::apiResource('cp-entrega-activos-fijos', App\Http\Controllers\CpEntregaActivosFijosController::class);
    });
});
