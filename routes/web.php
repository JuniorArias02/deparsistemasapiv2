<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Serve storage files without symlink (shared hosting fix)
// This works even if public/storage doesn't exist or is broken
Route::get('/storage/{path}', function (string $path) {
    $fullPath = storage_path('app/public/' . $path);

    if (!file_exists($fullPath)) {
        abort(404);
    }

    // Security: only allow safe file extensions
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];
    $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) {
        abort(403);
    }

    return response()->file($fullPath);
})->where('path', '.*');
