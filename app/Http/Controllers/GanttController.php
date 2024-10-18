<?php

namespace App\Http\Controllers;

use App\Models\Ecran;
use Illuminate\Http\Request;
use App\Models\ProjetEha2;
use App\Models\GanttTache;
use App\Models\GanttLien;
use App\Models\Links;
use App\Models\Taches;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;

class GanttController extends Controller
{

    // Récupération des tâches et des liens
    public function get(Request $request)
    {
        try {
            $codeProjet = $request->input('CodeProjet');

            // Vérifiez si un code projet est fourni
            if (!$codeProjet) {
                return response()->json([
                    "error" => "Code projet manquant"
                ], 400);
            }

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










    // Afficher la liste des projets et leurs Gantt Charts
    public function index(Request $request)
    {
        $ecran = Ecran::find($request->input('ecran_id'));
        $projects = ProjetEha2::all();  // Obtenez tous les projets
        return view('etudes_projets.plan', compact('projects', 'ecran'));
    }
    public function load($project_id)
    {
        try {
            // Charger les tâches et les liens du projet donné
            $tasks = GanttTache::where('project_id', $project_id)->get();
            $links = GanttLien::where('project_id', $project_id)->get();

            // Formater les dates en ISO 8601
            $tasks = $tasks->map(function($task) {
                if (!empty($task->start_date)) {
                    $task->start_date = Carbon::parse($task->start_date)->toISOString(); // ISO 8601
                }

                if (!empty($task->end_date)) {
                    $task->end_date = Carbon::parse($task->end_date)->toISOString(); // ISO 8601
                }

                return $task;
            });

            return response()->json([
                "data" => $tasks,
                "links" => $links
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors du chargement des données: ' . $e->getMessage()], 500);
        }
    }


    public function save(Request $request, $projectId)
    {
        $data = $request->input('data');
        $tasks = $data['tasks'];
        $links = $data['links'];

        foreach ($tasks as $taskData) {
            // Assurez-vous que `parent` est un entier ou null
            $taskData['parent'] = is_numeric($taskData['parent']) ? (int)$taskData['parent'] : null;
            $taskData['start_date'] = date('Y-m-d H:i:s', strtotime($taskData['start_date']));
            $taskData['end_date'] = date('Y-m-d H:i:s', strtotime($taskData['end_date']));

            GanttTache::updateOrCreate(
                ['id' => $taskData['id']],
                array_merge($taskData, ['project_id' => $projectId])
            );
        }

        foreach ($links as $linkData) {
            GanttLien::updateOrCreate(
                ['id' => $linkData['id']],
                array_merge($linkData, ['project_id' => $projectId])
            );
        }

        return response()->json(['status' => 'success']);
    }

    public function delete($projectId)
    {
        // Supprimer le projet et toutes ses tâches et liens
        GanttTache::where('project_id', $projectId)->delete();
        GanttLien::where('project_id', $projectId)->delete();

        return response()->json(['status' => 'success']);
    }
/*
    public function saveGantt(Request $request)
    {
        $project_id = $request->input('project_id');
        $tasks = $request->input('tasks', []);
        $links = $request->input('links', []);

        try {
            // Boucle pour enregistrer ou mettre à jour les tâches
            foreach ($tasks as $task) {
                GanttTache::updateOrCreate(
                    ['id' => $task['id']],
                    array_merge($task, ['project_id' => $project_id])
                );
            }

            // Boucle pour enregistrer ou mettre à jour les liens
            foreach ($links as $link) {
                GanttLien::updateOrCreate(
                    ['id' => $link['id']],
                    $link
                );
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la sauvegarde : ' . $e->getMessage()], 500);
        }
    }

    public function loadData($project_id)
    {
        try {
            $tasks = GanttTache::where('project_id', $project_id)->get();
            $links = GanttLien::all();

            $tasks = $tasks->map(function($task) {
                // Assurez-vous que les dates sont bien formatées
                if ($task->start_date instanceof \Carbon\Carbon || $task->start_date instanceof \DateTime) {
                    $task->start_date = $task->start_date->format('Y-m-d H:i:s');
                }

                if ($task->end_date instanceof \Carbon\Carbon || $task->end_date instanceof \DateTime) {
                    $task->end_date = $task->end_date->format('Y-m-d H:i:s');
                }

                return $task;
            });

            return response()->json([
                "data" => [
                    "tasks" => $tasks,
                    "links" => $links
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors du chargement des données: ' . $e->getMessage()], 500);
        }
    }


    public function checkData($project_id)
    {
        $exists = GanttTache::where('project_id', $project_id)->exists();

        return response()->json(['exists' => $exists]);
    }

    public function saveTask(Request $request)
    {
        try {
            $data = $request->only(['id', 'project_id', 'text', 'start_date', 'end_date', 'duration', 'progress', 'parent']);
            $task = GanttTache::updateOrCreate(['id' => $data['id']], $data);

            return response()->json(['tid' => $task->id]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la sauvegarde de la tâche : ' . $e->getMessage()], 500);
        }
    }
    public function checkProjectData($projectId)
    {
        // Vérifiez s'il y a des tâches associées à ce projet
        $tasksExist = GanttTache::where('project_id', $projectId)->exists();

        // Renvoie un JSON avec l'état des données
        return response()->json(['exists' => $tasksExist]);
    }
    public function updateTask(Request $request, $id)
    {
        try {
            $task = GanttTache::find($id);
            if ($task) {
                $task->update($request->all());
            }
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la mise à jour de la tâche : ' . $e->getMessage()], 500);
        }
    }

    public function deleteTask($id)
    {
        try {
            GanttTache::destroy($id);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la suppression de la tâche : ' . $e->getMessage()], 500);
        }
    }

    public function saveLink(Request $request)
    {
        try {
            $data = $request->only(['id', 'source', 'target', 'type']);
            $link = GanttLien::updateOrCreate(['id' => $data['id']], $data);

            return response()->json(['tid' => $link->id]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la sauvegarde du lien : ' . $e->getMessage()], 500);
        }
    }

    public function updateLink(Request $request, $id)
    {
        try {
            $link = GanttLien::find($id);
            if ($link) {
                $link->update($request->all());
            }
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la mise à jour du lien : ' . $e->getMessage()], 500);
        }
    }

    public function deleteLink($id)
    {
        try {
            GanttLien::destroy($id);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la suppression du lien : ' . $e->getMessage()], 500);
        }
    }




    // Enregistrer un lien (dépendance entre tâches)
    public function storeLink(Request $request)
    {
        $link = GanttLien::create($request->all());
        return response()->json(["action" => "inserted", "tid" => $link->id]);
    }
*/

}
