<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'can:not-suspended'])->group(function () {
    /* Profile */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /* Server */
    Route::get('/servers', [ServerController::class, 'index'])->name('server.index');
    Route::get('/server/{server}', [ServerController::class, 'show'])->name('server.show');
    Route::get('/server/{server}/edit', [ServerController::class, 'edit'])->name('server.edit');
    Route::patch('/server/{server}/edit', [ServerController::class, 'update'])->name('server.update');
    Route::delete('/server/{server}/edit', [ServerController::class, 'destroy'])->name('server.destroy');
    // ->can('admin-only')
});

require __DIR__.'/auth.php';
