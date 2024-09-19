<?php

namespace App\Http\Controllers;

use App\Models\Ecran;
use Illuminate\Http\Request;
use App\Models\ProjetEha2;
use App\Models\GanttTache;
use App\Models\GanttLien;
use Carbon\Carbon;

class GanttController extends Controller
{
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
            // Charger les tâches pour le projet donné
            $tasks = GanttTache::where('project_id', $project_id)->get();
            $links = GanttLien::where('project_id', $project_id)->get();

            // Formater les dates en ISO 8601
            $tasks = $tasks->map(function($task) {
                if (!empty($task->start_date)) {
                    $task->start_date = Carbon::parse($task->start_date)->toISOString(); // Conversion ISO 8601
                }

                if (!empty($task->end_date)) {
                    $task->end_date = Carbon::parse($task->end_date)->toISOString(); // Conversion ISO 8601
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
