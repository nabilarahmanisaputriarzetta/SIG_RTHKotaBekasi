<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PetaController;
use App\Http\Controllers\DataController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;

use Illuminate\Support\Facades\Route;

// ── Public ────────────────────────────────────────────────────────────────────
Route::get('/',         [HomeController::class,  'index'])->name('home');
Route::get('/peta',     [PetaController::class,  'index'])->name('peta');
Route::get('/dashboard-data', [DataController::class,  'index'])->name('data');
Route::get('/dashboard', [DataController::class, 'index'])->name('dashboard');

// ── API (JSON, used by Leaflet / JS) ─────────────────────────────────────────
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/rth/{tahun}',          [PetaController::class, 'apiRth'])->name('rth');
    Route::get('/kepadatan/{tahun}',    [PetaController::class, 'apiKepadatan'])->name('kepadatan');

    // GeoJSON overlay (untuk Leaflet)
    Route::get('/overlay/{tahun}',     [PetaController::class, 'apiOverlay'])->name('overlay');

    Route::get('/compare/{tahun1}/{tahun2}', [PetaController::class, 'apiCompare'])->name('compare');
    Route::get('/summary',              [HomeController::class, 'apiSummary'])->name('summary');
});

// ── Auth ─────────────────────────────────────────────────────────────────────
Route::get('/admin/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/admin/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/admin/logout',[AuthController::class, 'logout'])->name('logout');

// ── Admin (auth protected) ────────────────────────────────────────────────────
Route::prefix('admin')->middleware('auth')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');

    // RTH CRUD - export HARUS sebelum {id} agar tidak tertangkap sebagai ID "export"
    Route::get('/rth',              [AdminController::class, 'rthIndex'])->name('rth.index');
    Route::post('/rth',             [AdminController::class, 'rthStore'])->name('rth.store');
    Route::put('/rth/{id}',         [AdminController::class, 'rthUpdate'])->name('rth.update');
    Route::delete('/rth/{id}',      [AdminController::class, 'rthDestroy'])->name('rth.destroy');

    // Kepadatan CRUD - export HARUS sebelum {id}
    Route::get('/kepadatan',        [AdminController::class, 'kepadatanIndex'])->name('kepadatan.index');
    Route::get('/kepadatan/export', [AdminController::class, 'kepadatanExport'])->name('kepadatan.export');
    Route::post('/kepadatan',       [AdminController::class, 'kepadatanStore'])->name('kepadatan.store');
    Route::put('/kepadatan/{id}',   [AdminController::class, 'kepadatanUpdate'])->name('kepadatan.update');
    Route::delete('/kepadatan/{id}',[AdminController::class, 'kepadatanDestroy'])->name('kepadatan.destroy');

});