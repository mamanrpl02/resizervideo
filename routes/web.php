<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VideoController;

Route::get('/', [VideoController::class, 'index'])->name('home');
Route::post('/resize', [VideoController::class, 'resize'])->name('resize');
