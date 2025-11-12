<?php

use App\Http\Controllers\ConfigController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*Route::get('/', function () {
    return view('welcome');
});*/
Route::redirect('/', '/monitor');

Route::middleware(['auth', 'can:not-suspended'])->group(function () {
    /* Profile */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profile/test/telegram', [ProfileController::class, 'test_telegram'])->name('profile.test.telegram');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /* Server */
    Route::resource('server', ServerController::class)->except(['index']);
    Route::get('/monitor', [ServerController::class, 'monitorPage'])->name('server.monitor');
    Route::get('/servers', [ServerController::class, 'index'])->name('server.index');
    Route::get('/server/{server}/run', [ServerController::class, 'runJob'])->name('server.runChecks');
    Route::get('/servers/run', [ServerController::class, 'runBatch'])->name('server.runBatch');

    /* User */
    Route::resource('user', UserController::class)->except(['index', 'store']);
    Route::get('/users', [UserController::class, 'index'])->name('user.index');

    /* Config */
    Route::get('/config', [ConfigController::class, 'edit'])->name('config.edit');
    Route::patch('/config', [ConfigController::class, 'update'])->name('config.update');
    Route::get('/config/heartbeat', [ConfigController::class, 'heartbeat'])->name('config.heartbeat');
});

require __DIR__.'/auth.php';
