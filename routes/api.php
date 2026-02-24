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

// TEMPORARY DEBUG - REMOVE AFTER FIXING
Route::post('/debug-login', function (\Illuminate\Http\Request $request) {
    $usuario = $request->input('usuario');
    $contrasena = $request->input('contrasena');

    $user = \App\Models\Usuario::where('usuario', $usuario)->first();

    if (!$user) {
        return response()->json(['error' => 'User not found', 'searched_by' => $usuario]);
    }

    $rawHash = $user->getAttributes()['contrasena'] ?? 'NULL';
    $isBcrypt = str_starts_with($rawHash, '$2y$') || str_starts_with($rawHash, '$2a$');
    $hashLength = strlen($rawHash);
    $hashCheck = \Illuminate\Support\Facades\Hash::check($contrasena, $rawHash);

    return response()->json([
        'user_found' => true,
        'user_id' => $user->id,
        'hash_starts_with' => substr($rawHash, 0, 7),
        'hash_length' => $hashLength,
        'is_bcrypt_format' => $isBcrypt,
        'hash_check_result' => $hashCheck,
        'getAuthPassword_works' => $user->getAuthPassword() !== null,
    ]);
});

// DB CONNECTION TEST
Route::get('/db-test', function () {
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        $dbName = \Illuminate\Support\Facades\DB::connection()->getDatabaseName();
        return response()->json([
            'status' => 'success',
            'message' => 'Conexión a base de datos exitosa',
            'database' => $dbName
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Error al conectar a la base de datos',
            'error' => $e->getMessage()
        ], 500);
    }
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
    Route::middleware(['auth:api', 'activity'])->group(function () {
        Route::post('/heartbeat', [App\Http\Controllers\ActivityController::class, 'heartbeat']);

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
        Route::get('pc-equipos/{id}/hoja-de-vida', [App\Http\Controllers\PcEquipoController::class, 'hojaDeVida']);
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

        // Mantenimientos Routes
        Route::get('mantenimientos/mis-mantenimientos', [App\Http\Controllers\MantenimientoController::class, 'misMantenimientos']);
        Route::apiResource('mantenimientos', App\Http\Controllers\MantenimientoController::class);
        Route::post('mantenimientos/{id}/marcar-revisado', [App\Http\Controllers\MantenimientoController::class, 'marcarRevisado']);

        // Usuarios por permiso
        Route::get('usuarios/por-permiso/{permiso}', [App\Http\Controllers\UsuarioController::class, 'getByPermission']);

        // Agenda Mantenimientos Routes
        Route::get('agenda-mantenimientos/mantenimiento/{mantenimiento_id}', [App\Http\Controllers\AgendaMantenimientoController::class, 'getByMantenimiento']);
        Route::apiResource('agenda-mantenimientos', App\Http\Controllers\AgendaMantenimientoController::class);
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
            Route::post('update-items', [App\Http\Controllers\CpPedidoController::class, 'updateItems']);
            Route::patch('tracking', [App\Http\Controllers\CpPedidoController::class, 'updateTracking']);
            Route::get('exportar-excel', [App\Http\Controllers\CpPedidoController::class, 'exportExcel']);
        });
        Route::post('cp-pedidos/exportar-consolidado', [App\Http\Controllers\CpPedidoController::class, 'exportConsolidadoExcel']);
    });

    // CP Entrega Activos Fijos Routes (Protected with JWT)
    Route::middleware('auth:api')->group(function () {
        Route::apiResource('cp-entrega-activos-fijos', App\Http\Controllers\CpEntregaActivosFijosController::class);
    });
});
