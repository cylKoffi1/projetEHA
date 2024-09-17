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
    public function saveGantt(Request $request)
    {
        $tasks = $request->input('tasks', []);
        $links = $request->input('links', []);

        try {
            // Boucle pour enregistrer les tâches
            foreach ($tasks as $task) {
                GanttTache::updateOrCreate(
                    ['id' => $task['id']],
                    [
                        'text' => $task['text'],
                        'start_date' => $task['start_date'],
                        'duration' => $task['duration'],
                        'progress' => $task['progress'],
                        'parent' => $task['parent']
                    ]
                );
            }

            // Boucle pour enregistrer les liens
            foreach ($links as $link) {
                GanttLien::updateOrCreate(
                    ['id' => $link['id']],
                    [
                        'source' => $link['source'],
                        'target' => $link['target'],
                        'type' => $link['type']
                    ]
                );
            }

            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la sauvegarde : ' . $e->getMessage()], 500);
        }
    }


    // Charger les tâches d'un projet donné
    public function loadData($project_id)
    {
        $tasks = GanttTache::where('project_id', $project_id)->get();
        $links = GanttLien::all();

        return response()->json([
            "data" => $tasks,
            "links" => $links
        ]);
    }



    public function storeTask(Request $request)
    {
        try {
            // Convertir la date au format compatible MySQL
            $start_date = Carbon::parse($request->input('start_date'))->format('Y-m-d H:i:s');

            // Créer une nouvelle instance de GanttTache
            $task = new GanttTache();

            // Ajouter les informations de la tâche
            $task->text = $request->input('text');
            $task->start_date = $start_date; // Date convertie
            $task->duration = $request->input('duration');
            $task->progress = $request->input('progress', 0); // Par défaut à 0 si non fourni
            $task->parent = $request->input('parent', 0); // Parent par défaut à 0

            // Ajouter le code projet
            $task->project_id = $request->input('project_id'); // Récupérer et sauvegarder le code projet

            // Sauvegarder la tâche
            $task->save();

            // Retourner une réponse JSON avec l'ID de la tâche sauvegardée
            return response()->json(['tid' => $task->id], 200);

        } catch (\Exception $e) {
            // En cas d'erreur, renvoyer un message d'erreur détaillé
            return response()->json(['error' => 'Erreur lors de la sauvegarde : ' . $e->getMessage()], 500);
        }
    }
    public function checkProjectData($projectId)
    {
        // Vérifiez s'il y a des tâches associées à ce projet
        $tasksExist = GanttTache::where('project_id', $projectId)->exists();

        // Renvoie un JSON avec l'état des données
        return response()->json(['exists' => $tasksExist]);
    }





    // Mettre à jour une tâche
    public function updateTask($id, Request $request)
    {
        $task = GanttTache::find($id);
        $task->update($request->all());
        return response()->json(["action" => "updated"]);
    }

    // Supprimer une tâche
    public function deleteTask($id)
    {
        GanttTache::destroy($id);
        return response()->json(["action" => "deleted"]);
    }

    // Enregistrer un lien (dépendance entre tâches)
    public function storeLink(Request $request)
    {
        $link = GanttLien::create($request->all());
        return response()->json(["action" => "inserted", "tid" => $link->id]);
    }

    // Mettre à jour un lien
    public function updateLink($id, Request $request)
    {
        $link = GanttLien::find($id);
        $link->update($request->all());
        return response()->json(["action" => "updated"]);
    }

    // Supprimer un lien
    public function deleteLink($id)
    {
        GanttLien::destroy($id);
        return response()->json(["action" => "deleted"]);
    }
}
