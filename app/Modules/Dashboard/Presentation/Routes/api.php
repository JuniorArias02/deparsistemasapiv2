<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Dashboard\Presentation\Controllers\DashboardStatsController;

Route::group(['middleware' => ['api', 'auth:api', 'activity']], function () {
    Route::get('dashboard/stats', [DashboardStatsController::class, 'index']);
});
