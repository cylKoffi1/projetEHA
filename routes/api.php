<?php

use App\Http\Controllers\EtudeProjet;
use App\Http\Controllers\FichierController;
use App\Http\Controllers\GanttController;
use App\Http\Controllers\ParSpecifique\ActeurController;
use App\Http\Controllers\WorkflowValidationController;
use App\Models\Acteur;
use App\Models\PersonnePhysique;
use App\Http\Controllers\sigAdminController;
use App\Http\Controllers\SigAdminInfrastructureController;
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

Route::get('/task/{id}', [GanttController::class, 'show'])->name('task.show');
// Routes pour les tâches (tasks)
Route::post('/task', [GanttController::class, 'store'])->name('task.store');
Route::put('/task/{id}', [GanttController::class, 'update'])->name('task.update');
Route::delete('/task/{id}', [GanttController::class, 'destroy'])->name('task.destroy');

// Routes pour les liens (links)
Route::post('/link', [GanttController::class, 'storelink'])->name('link.store');
Route::put('/link/{id}', [GanttController::class, 'updatelink'])->name('link.update');
Route::delete('/link/{id}', [GanttController::class, 'destroylink'])->name('link.destroy');

// Route pour les calendrier
Route::get('/scheduler', [GanttController::class, 'indexscheduler']);
Route::post('/scheduler', [GanttController::class, 'storescheduler']);
Route::get('/scheduler/{id}', [GanttController::class, 'showscheduler']);
Route::put('/scheduler/{id}', [GanttController::class, 'updatescheduler']);
Route::delete('/scheduler/{id}', [GanttController::class, 'destroyscheduler']);

Route::get('/scheduler-data', [GanttController::class, 'getSchedulerData']);

Route::get('/acteurs', [ActeurController::class, 'search']);
Route::post('/acteurs', [ActeurController::class, 'stores']);



Route::get('/bailleurs', [EtudeProjet::class, 'search']);
Route::get('/get-representant-legal/{code_acteur}', function ($code_acteur) {
    $representant = PersonnePhysique::where('code_acteur', $code_acteur)->first();
    return response()->json($representant);
});



// Route pour récupérer les projets filtrés
Route::get('/projects', [sigAdminController::class, 'getProjects'])
    ->name('api.projects');

// Route pour le filtrage de la carte
Route::get('/filter-map', [sigAdminController::class, 'filterMap'])
    ->name('filter.map');
Route::get('/projects/all', [sigAdminController::class, 'getAllProjects']);
Route::get('/legende/{groupe}', [sigAdminController::class, 'getByGroupe']);
Route::get('/filtrer-projets', [sigAdminController::class, 'getFiltreOptionsEtProjets']);

// Nouveau: Détails projets pour un code/niveau donné
Route::get('/project-details', [sigAdminController::class, 'getProjectDetails']);


Route::middleware('auth')->group(function () {
    Route::post('/fichiers',        [FichierController::class, 'upload'])->name('fichiers.upload');
    Route::get('/fichiers/{id}',    [FichierController::class, 'download'])->name('fichiers.download');
    Route::delete('/fichiers/{id}', [FichierController::class, 'destroy'])->name('fichiers.destroy');
});

// APIs carte Infras
Route::prefix('api/infras')->group(function () {
    // Agrégat pour la carte (par localité & niveaux)
    Route::get('aggregate', [sigAdminController::class, 'aggregate']);
    // Détails (drawer) — projets & infras pour une localité donnée
    Route::get('details',   [sigAdminController::class, 'details']);
    // Légende dynamique (cohérente avec métrique)
    Route::get('legend',    [sigAdminController::class, 'legend']);
    // Listes pour filtres (groupes, domaines, sous-domaines)
    Route::get('filters',   [sigAdminController::class, 'filters']);
});

Route::get('/projectsInfras',                 [SigAdminInfrastructureController::class, 'aggregateProjectsInfras']);
Route::get('/legendeInfras/{groupe}',         [SigAdminInfrastructureController::class, 'legendByGroupInfras']);

// markers & détails
Route::get('/infrasInfras/markers',           [SigAdminInfrastructureController::class, 'markersInfras']);
Route::get('/project-detailsInfras',          [SigAdminInfrastructureController::class, 'projectDetailsInfras']);

// filtres
Route::get('/filtrer-projetsInfras',          [SigAdminInfrastructureController::class, 'filterOptionsAndAggregateInfras']);

// carte afrique (optionnel)
Route::get('/projectsInfras/all',             [SigAdminInfrastructureController::class, 'allProjectsInfras']);