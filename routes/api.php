<?php

use App\Http\Controllers\GanttController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


// Route pour récupérer les données
Route::get('/data', [GanttController::class, 'get'])->name('gantt.data');

// Routes pour les tâches (tasks)
Route::post('/task', [GanttController::class, 'store'])->name('task.store');
Route::put('/task/{id}', [GanttController::class, 'update'])->name('task.update');
Route::delete('/task/{id}', [GanttController::class, 'destroy'])->name('task.destroy');

// Routes pour les liens (links)
Route::post('/link', [GanttController::class, 'storelink'])->name('link.store');
Route::put('/link/{id}', [GanttController::class, 'updatelink'])->name('link.update');
Route::delete('/link/{id}', [GanttController::class, 'destroylink'])->name('link.destroy');
