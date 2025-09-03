<?php

namespace App\Http\Controllers;

use App\Models\Ecran;
use Illuminate\Http\Request;
use App\Models\GanttTache;
use App\Models\GanttLien;
use App\Models\Links;
use App\Models\Projet;
use App\Models\Taches;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;

class GanttController extends Controller
{
    // Afficher la liste des projets et leurs Gantt Charts
    public function index(Request $request)
    {
        $ecran = Ecran::find($request->input('ecran_id'));
        $pays = session('pays_selectionne');
        $groupe = session('projet_selectionne');

        $projects = Projet::where('code_projet', 'like', $pays . $groupe . '%')
            ->whereHas('statuts', function ($query) {
                $query->whereIn('type_statut', [1, 2]);
            })
            ->get();  // Obtenez tous les projets
        return view('etudes_projets.plan', compact('projects', 'ecran'));
    }
    // Récupération des tâches et des liens
    public function get(Request $request)
    {
        try {
            $codeProjet = $request->input('CodeProjet');
    
            // Tâches avec formatage explicite
            $tasks = Taches::where('CodeProjet', $codeProjet)
                ->whereNotNull('start_date')
                ->where('is_deleted', 0)
                ->orderBy('sortorder')
                ->get()
                ->map(function ($t) {
                    return [
                        'id' => $t->id,
                        'text' => $t->text,
                        'start_date' => \Carbon\Carbon::parse($t->start_date)->format('Y-m-d H:i:s'),
                        'duration' => (int) $t->duration,
                        'progress' => (float) $t->progress,
                        'parent' => (int) $t->parent,
                        'sortorder' => (int) $t->sortorder,
                        'CodeProjet' => $t->CodeProjet,
                        'type' => $t->type ?? 'task' // Ajouter ici
                    ];
                });
                
    
            // Liens inchangés
            $links = Links::where('CodeProjet', $codeProjet)
                ->where('is_deleted', 0)
                ->get();
    
            return response()->json([
                "data" => $tasks,
                "links" => $links
            ]);
        } catch (Exception $e) {
            return response()->json([
                "error" => "Échec de la récupération des données",
                "message" => $e->getMessage()
            ], 500);
        }
    }
    

    public function show($id)
    {
        $task = Taches::find($id);
        if (!$task) {
            return response()->json([
                "success" => false,
                "message" => "Tâche introuvable avec l'ID $id"
            ], 404);
        }

    }
    // Insertion d'une nouvelle tâche
    public function store(Request $request)
    {
        try {
            $request->validate([
                'text' => 'required|string|max:255',
                'start_date' => 'required|date',
                'duration' => 'nullable|integer|min:0',
                'type' => 'in:task,project,milestone',
            ]);
    
            DB::beginTransaction();
    
            $task = new Taches();
            $task->text = $request->text;
            $task->start_date = $request->start_date;
            $task->type = $request->type ?? 'task';
    
            $task->duration = ($request->type === 'milestone') ? 0 : $request->duration;
            $task->progress = $request->has("progress") ? $request->progress : 0;
            $task->parent = $request->parent ?? 0;
            $task->sortorder = Taches::max("sortorder") + 1;
            $task->CodeProjet = $request->codeProjet;
    
            $task->save();
            DB::commit();
    
            return response()->json([
                "action" => "inserted",
                "tid" => $task->id
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                "error" => "Failed to insert task",
                "message" => $e->getMessage()
            ], 500);
        }
    }
    
    // Mise à jour d'une tâche existante
    public function update($id, Request $request)
    {
        try {
            $request->validate([
                'text' => 'required|string|max:255',
                'start_date' => 'required|date',
                'duration' => 'nullable|integer|min:0',
                'type' => 'in:task,project,milestone',
            ]);
    
            DB::beginTransaction();
    
            $task = Taches::findOrFail($id);
            $task->text = $request->text;
            $task->start_date = $request->start_date;
            $task->type = $request->type ?? 'task';
            $task->duration = ($request->type === 'milestone') ? 0 : $request->duration;
            $task->progress = $request->has("progress") ? $request->progress : 0;
            $task->parent = $request->parent ?? 0;
    
            if ($request->has("codeProjet")) {
                $task->CodeProjet = $request->codeProjet;
            }
    
            if ($request->has("target")) {
                $this->updateOrder($id, $request->target);
            }
    
            $task->save();
            DB::commit();
    
            return response()->json(["action" => "updated"]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                "error" => "Failed to update task",
                "message" => $e->getMessage()
            ], 500);
        }
    }
    


    // Suppression d'une tâche
    public function destroy($id)
    {
        try {
            DB::beginTransaction(); // Démarrage d'une transaction

            $task = Taches::findOrFail($id); // Récupérer la tâche, lancer une erreur si introuvable
            if ($task) {
                $task->is_deleted = 1; // Marquer comme supprimé
                $task->save();
                return response()->json(['success' => true]);
            }

            DB::commit(); // Valider la transaction

            return response()->json([
                "action" => "deleted"
            ]);
        } catch (Exception $e) {
            DB::rollBack(); // Annuler la transaction en cas d'erreur
            return response()->json([
                "error" => "Failed to delete task",
                "message" => $e->getMessage()
            ], 500);
        }
    }

    // Mise à jour de l'ordre des tâches
    private function updateOrder($taskId, $target)
    {
        try {
            $nextTask = false;
            $targetId = $target;

            if (strpos($target, "next:") === 0) {
                $targetId = substr($target, strlen("next:"));
                $nextTask = true;
            }

            if ($targetId == "null") return;

            $targetOrder = Taches::findOrFail($targetId)->sortorder;
            if ($nextTask) $targetOrder++;

            Taches::where("sortorder", ">=", $targetOrder)->increment("sortorder");

            $updatedTask = Taches::findOrFail($taskId);
            $updatedTask->sortorder = $targetOrder;
            $updatedTask->save();
        } catch (Exception $e) {
            throw new Exception("Failed to update task order: " . $e->getMessage());
        }
    }

    // Insertion d'un nouveau lien
    public function storelink(Request $request)
    {
        try {
            DB::beginTransaction(); // Démarrage d'une transaction

            $link = new Links();
            $link->type = $request->type;
            $link->source = $request->source;
            $link->target = $request->target;
            $link->CodeProjet = $request->codeProjet;

            $link->save();

            DB::commit(); // Valider la transaction

            return response()->json([
                "action" => "inserted",
                "tid" => $link->id
            ]);
        } catch (Exception $e) {
            DB::rollBack(); // Annuler la transaction en cas d'erreur
            return response()->json([
                "error" => "Failed to insert link",
                "message" => $e->getMessage()
            ], 500);
        }
    }

    // Mise à jour d'un lien existant
    public function updatelink($id, Request $request)
    {
        try {
            DB::beginTransaction(); // Démarrage d'une transaction

            $link = Links::findOrFail($id); // Récupérer le lien, lancer une erreur si introuvable
            $link->type = $request->type;
            $link->source = $request->source;
            $link->target = $request->target;
            // Mettre à jour le CodeProjet
            if ($request->has("codeProjet")) {
                $link->CodeProjet = $request->codeProjet; // Mise à jour du code projet
            }
            $link->save();

            DB::commit(); // Valider la transaction

            return response()->json([
                "action" => "updated"
            ]);
        } catch (Exception $e) {
            DB::rollBack(); // Annuler la transaction en cas d'erreur
            return response()->json([
                "error" => "Failed to update link",
                "message" => $e->getMessage()
            ], 500);
        }
    }

    // Suppression d'un lien
    public function destroylink($id)
    {
        try {
            DB::beginTransaction(); // Démarrage d'une transaction

            $link = Links::findOrFail($id); // Récupérer le lien, lancer une erreur si introuvable
            if ($link) {
                $link->is_deleted = 1; // Marquer comme supprimé
                $link->save();
                return response()->json(['success' => true]);
            }
            DB::commit(); // Valider la transaction

            return response()->json([
                "action" => "deleted"
            ]);
        } catch (Exception $e) {
            DB::rollBack(); // Annuler la transaction en cas d'erreur
            return response()->json([
                "error" => "Failed to delete link",
                "message" => $e->getMessage()
            ], 500);
        }
    }


    /////////////////////CALENDRIRER
    public function getSchedulerData(Request $request)
    {
        $codeProjet = $request->input('CodeProjet');
    
        $events = Taches::where('CodeProjet', $codeProjet)
            ->whereNotNull('start_date')
            ->where('is_deleted', 0)
            ->get()
            ->map(function ($task) {
                return [
                    "id" => $task->id,
                    "text" => $task->text,
                    "start_date" => Carbon::parse($task->start_date)->format('Y-m-d H:i:s'),
                    "end_date" => Carbon::parse($task->start_date)->addDays($task->duration)->format('Y-m-d H:i:s'),
                ];
            });
    
        return response()->json([
            "data" => $events
        ]);
    }
    // Lister tous les événements
public function indexscheduler(Request $request)
{
    $codeProjet = $request->input('CodeProjet');

    $events = Taches::where('CodeProjet', $codeProjet)
        ->where('is_deleted', 0)
        ->get()
        ->map(function ($task) {
            return [
                "id" => $task->id,
                "text" => $task->text,
                "start_date" => Carbon::parse($task->start_date)->format('Y-m-d H:i:s'),
                "end_date" => Carbon::parse($task->start_date)->addDays($task->duration)->format('Y-m-d H:i:s'),
            ];
        });

    return response()->json($events);
}

// Créer un événement
public function storescheduler(Request $request)
{
    try {
        $task = new Taches();
        $task->text = $request->text;
        $task->start_date = $request->start_date;
        $task->type = $request->type ?? 'task';
        $task->duration = Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date));
        $task->CodeProjet = $request->codeProjet ?? 'INCONNU';
        $task->sortorder = Taches::max("sortorder") + 1;
        $task->parent = 0;
        $task->progress = 0;
        $task->save();

        return response()->json([
            "action" => "inserted",
            "tid" => $task->id
        ]);
    } catch (Exception $e) {
        return response()->json(["error" => $e->getMessage()], 500);
    }
}

// Lire un événement
public function showscheduler($id)
{
    $task = Taches::find($id);
    return $task
        ? response()->json($task)
        : response()->json(["error" => "Événement introuvable"], 404);
}

// Mettre à jour un événement
public function updatescheduler(Request $request, $id)
{
    try {
        $task = Taches::findOrFail($id);
        $task->text = $request->text;
        $task->start_date = $request->start_date;
        $task->duration = Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date));
        $task->save();

        return response()->json(["action" => "updated"]);
    } catch (Exception $e) {
        return response()->json(["error" => $e->getMessage()], 500);
    }
}

// Supprimer un événement
public function destroyscheduler($id)
{
    try {
        $task = Taches::findOrFail($id);
        $task->is_deleted = 1;
        $task->save();

        return response()->json(["action" => "deleted"]);
    } catch (Exception $e) {
        return response()->json(["error" => $e->getMessage()], 500);
    }
}


}
