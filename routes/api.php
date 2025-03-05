<?php

use App\Http\Controllers\EtudeProjet;
use App\Http\Controllers\GanttController;
use App\Http\Controllers\ParSpecifique\ActeurController;
use App\Http\Controllers\WorkflowValidationController;
use App\Models\Acteur;
use App\Models\PersonnePhysique;
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

// Route pour les calendrier

Route::get('/scheduler-data', [GanttController::class, 'getSchedulerData']);

Route::get('/acteurs', [ActeurController::class, 'search']);
Route::post('/acteurs', [ActeurController::class, 'stores']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/workflow/soumettre', [WorkflowValidationController::class, 'soumettreDemande']);
    Route::get('/workflow/demandes-en-attente', [WorkflowValidationController::class, 'demandesEnAttente']);
    Route::post('/workflow/valider/{id}', [WorkflowValidationController::class, 'validerEtape']);
    Route::post('/workflow/rejeter/{id}', [WorkflowValidationController::class, 'rejeterDemande']);
    Route::get('/workflow/mes-demandes', [WorkflowValidationController::class, 'mesDemandes']);
});

Route::get('/bailleurs', [EtudeProjet::class, 'search']);
Route::get('/get-representant-legal/{code_acteur}', function ($code_acteur) {
    $representant = PersonnePhysique::where('code_acteur', $code_acteur)->first();
    return response()->json($representant);
});



