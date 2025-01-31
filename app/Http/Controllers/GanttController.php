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
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;

class GanttController extends Controller
{
    // Afficher la liste des projets et leurs Gantt Charts
    public function index(Request $request)
    {
        $ecran = Ecran::find($request->input('ecran_id'));
        $projects = Projet::all();  // Obtenez tous les projets
        return view('etudes_projets.plan', compact('projects', 'ecran'));
    }
    // Récupération des tâches et des liens
    public function get(Request $request)
    {
        try {
            $codeProjet = $request->input('CodeProjet');

            

            // Récupérer les tâches filtrées par CodeProjet
            $tasks = Taches::where('CodeProjet', $codeProjet)
                ->where('is_deleted', 0)
                ->orderBy('sortorder')
                ->get();

            // Récupérer les liens (s'il y en a)
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


    // Insertion d'une nouvelle tâche
    public function store(Request $request)
    {
        try {
            DB::beginTransaction(); // Démarrage d'une transaction

            $task = new Taches();
            $task->text = $request->text;
            $task->start_date = $request->start_date;
            $task->duration = $request->duration;
            $task->progress = $request->has("progress") ? $request->progress : 0;
            $task->parent = $request->parent;
            $task->sortorder = Taches::max("sortorder") + 1;
            $task->CodeProjet = $request->CodeProjet; // Récupération du code projet


            $task->save();

            DB::commit(); // Valider la transaction

            return response()->json([
                "action" => "inserted",
                "tid" => $task->id
            ]);
        } catch (Exception $e) {
            DB::rollBack(); // Annuler la transaction en cas d'erreur
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
            DB::beginTransaction(); // Démarrage d'une transaction

            $task = Taches::findOrFail($id); // Récupérer la tâche, lancer une erreur si introuvable

            $task->text = $request->text;
            $task->start_date = $request->start_date;
            $task->duration = $request->duration;
            $task->progress = $request->has("progress") ? $request->progress : 0;
            $task->parent = $request->parent;

            // Mettre à jour le CodeProjet
            if ($request->has("projectSelect")) {
                $task->CodeProjet = $request->CodeProjet; // Mise à jour du code projet
            }

            // Mise à jour de l'ordre des tâches si nécessaire
            if ($request->has("target")) {
                $this->updateOrder($id, $request->target);
            }

            $task->save();

            DB::commit(); // Valider la transaction

            return response()->json([
                "action" => "updated"
            ]);
        } catch (Exception $e) {
            DB::rollBack(); // Annuler la transaction en cas d'erreur
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
            $link->CodeProjet = $request->CodeProjet;

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
            if ($request->has("projectSelect")) {
                $link->CodeProjet = $request->CodeProjet; // Mise à jour du code projet
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
    public function getSchedulerData()
    {
        $events = Taches::select('id', 'text as title', 'start_date', 'duration', 'progress', 'parent')->get();

        return response()->json([
            "data" => $events
        ]);
    }

}
