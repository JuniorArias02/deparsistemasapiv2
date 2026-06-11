<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Autenticacion\Presentation\Controllers\AuthController;
use App\Modules\Autenticacion\Presentation\Controllers\UsuarioController;

use App\Modules\Autenticacion\Presentation\Controllers\ActivityController;
use App\Modules\Autenticacion\Presentation\Controllers\ProfileController;

Route::group(['middleware' => 'api', 'prefix' => 'auth'], function ($router) {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('me', [AuthController::class, 'me']);
    Route::post('forgot-password', [AuthController::class, 'sendResetCode']);
    Route::post('verify-code', [AuthController::class, 'verifyResetCode']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
});

Route::group(['middleware' => 'api'], function () {
    Route::apiResource('usuarios', UsuarioController::class);
    
    Route::middleware(['auth:api', 'activity'])->group(function () {
        Route::get('usuarios/por-permiso/{permiso}', [UsuarioController::class, 'getByPermission']);

        // Profile Routes
        Route::post('profile/update', [ProfileController::class, 'update']);
        Route::post('profile/change-password', [ProfileController::class, 'changePassword']);
        Route::post('profile/upload-signature', [ProfileController::class, 'uploadSignature']);
        Route::post('profile/upload-photo', [ProfileController::class, 'uploadPhoto']);
        Route::post('profile/delete-photo', [ProfileController::class, 'deletePhoto']);

        // Activity Route
        Route::post('/heartbeat', [ActivityController::class, 'heartbeat']);
    });
});
