<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OverlayController;

Route::get(
    '/overlay/{tahun}',
    [OverlayController::class, 'index']
);