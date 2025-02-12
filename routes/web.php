<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ServerController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'can:not-suspended'])->group(function () {
    /* Profile */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /* Server */
    Route::resource('server', ServerController::class)->except(['index']);
    Route::get('/monitor', [ServerController::class, 'monitorPage'])->name('server.monitor');
    Route::get('/servers', [ServerController::class, 'index'])->name('server.index');


    /* User */
    Route::resource('user', UserController::class)->except(['index', 'create', 'store']);
    Route::get('/users', [UserController::class, 'index'])->name('user.index');
});

Route::get('/run-seed', function () {
    try {
        Artisan::call('migrate:fresh', ["--force" => true, '--schema-path' => 'do not run schema path']);
    } catch (Exception $e) {
        return $this->response($e->getMessage());
    }
    try {
        Artisan::call('db:seed');
    } catch (Exception $e) {
        return $this->response($e->getMessage());
    }

    return 'success';
});

require __DIR__.'/auth.php';
