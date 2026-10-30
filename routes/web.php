<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClaimController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/claims');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.store');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::middleware('auth')->group(function () {
    Route::get('/claims', [ClaimController::class, 'index'])->name('claims.index');
    Route::get('/claims/create', [ClaimController::class, 'create'])->name('claims.create');
    Route::post('/claims', [ClaimController::class, 'store'])->name('claims.store');
    Route::get('/claims/{claim}', [ClaimController::class, 'show'])->name('claims.show');
});
