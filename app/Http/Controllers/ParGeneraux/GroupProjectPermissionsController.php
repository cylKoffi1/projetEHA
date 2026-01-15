<?php

namespace App\Http\Controllers\ParGeneraux;

use App\Http\Controllers\Controller;
use App\Models\Ecran;
use App\Models\GroupeUtilisateur;
use App\Models\GroupProjectPermission;
use App\Models\TypeUtilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GroupProjectPermissionsController extends Controller
{
    public function index(Request $request)
    {
        try {
            Log::info('Chargement des permissions pour groupes projets.');

            $ecran = Ecran::find($request->input('ecran_id'));
            $permissions = GroupProjectPermission::with('source')->get();
            $roles = GroupeUtilisateur::all();

            Log::info('Permissions pour groupes projets chargées avec succès.');

            return view('parGeneraux.group_project_permissions', compact('ecran', 'permissions', 'roles'));
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement des permissions pour groupes projets : ' . $e->getMessage());
            return redirect()->back()->withErrors("Une erreur est survenue lors du chargement des permissions pour groupes projets.");
        }
    }

    public function store(Request $request)
    {
        try {
            Log::info('Enregistrement d\'une permission pour groupes projets.', ['data' => $request->all()]);

            $request->validate([
                'role_source' => 'required|string|exists:groupe_utilisateur,code',
                'max_projects' => 'required|integer|min:0',
                'can_assign' => 'required|boolean',
            ]);

            if ($request->id) {
                // Mise à jour
                $permission = GroupProjectPermission::findOrFail($request->id);
                $permission->update($request->all());
                Log::info("Permission mise à jour avec succès : ID {$permission->id}");
            } else {
                // Création
                $permission = GroupProjectPermission::create($request->all());
                Log::info("Nouvelle permission créée avec succès : ID {$permission->id}");
            }

            return redirect()->back()->with('success', 'Permission pour groupes projets enregistrée avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'enregistrement de la permission pour groupes projets : ' . $e->getMessage());
            return redirect()->back()->withErrors("Une erreur est survenue lors de l'enregistrement de la permission pour groupes projets.");
        }
    }

    public function destroy($id)
    {
        try {
            Log::info("Tentative de suppression de la permission pour groupes projets : ID {$id}");

            $permission = GroupProjectPermission::findOrFail($id);
            $permission->delete();

            Log::info("Permission supprimée avec succès : ID {$id}");

            return redirect()->back()->with('success', 'Permission pour groupes projets supprimée avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression de la permission pour groupes projets : ' . $e->getMessage());
            return redirect()->back()->withErrors("Une erreur est survenue lors de la suppression de la permission pour groupes projets.");
        }
    }

    ////////GROUPE PROJET ///////////

     /**
     * Afficher la liste des groupes utilisateurs
     */
    public function groupe(Request $request)
    {
        try {
            Log::info('Chargement des groupes utilisateurs.');
            $TypeUtilisateur = TypeUtilisateur::all();
            $groupes = GroupeUtilisateur::all();
            $ecran = Ecran::find($request->input('ecran_id'));

            return view('parGeneraux.groupeUtilisateur', compact('TypeUtilisateur','groupes', 'ecran'));
        } catch (\Exception $e) {
            Log::error("Erreur lors du chargement des groupes utilisateurs : " . $e->getMessage());
            return redirect()->back()->with('error', "Une erreur est survenue lors du chargement des groupes.");
        }
    }

    /**
     * Ajouter un nouveau groupe utilisateur
     */
    public function storeGroupe(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:10|unique:groupe_utilisateur,code',
            'libelle_groupe' => 'required|string|max:255',
            'typeUtilisateur' => 'required|integer' 
        ]);

        try {
            GroupeUtilisateur::create([
                'code' => $request->code,
                'libelle_groupe' => $request->libelle_groupe,
                'type_utilisateur_id' => $request->typeUtilisateur
            ]);

            return redirect()->back()->with('success', 'Groupe utilisateur ajouté avec succès.');
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'ajout du groupe utilisateur : " . $e->getMessage());
            return redirect()->back()->with('error', "Une erreur est survenue lors de l'ajout.");
        }
    }

    /**
     * Modifier un groupe utilisateur
     */
    public function updateGroupe(Request $request, $id)
    {
        $request->validate([
            'libelle_groupe' => 'required|string|max:255'
        ]);

        try {
            $groupe = GroupeUtilisateur::findOrFail($id);
            $groupe->update(['libelle_groupe' => $request->libelle_groupe]);
            $groupe->update(['type_utilisateur_id' => $request->typeUtilisateur]);

            return redirect()->back()->with('success', 'Groupe utilisateur mis à jour avec succès.');
        } catch (\Exception $e) {
            Log::error("Erreur lors de la mise à jour du groupe utilisateur : " . $e->getMessage());
            return redirect()->back()->with('error', "Une erreur est survenue lors de la mise à jour.");
        }
    }

    /**
     * Supprimer un groupe utilisateur
     */
    public function destroyGroupe($id)
    {
        try {
            $groupe = GroupeUtilisateur::findOrFail($id);
            $groupe->delete();

            return redirect()->back()->with('success', 'Groupe utilisateur supprimé avec succès.');
        } catch (\Exception $e) {
            Log::error("Erreur lors de la suppression du groupe utilisateur : " . $e->getMessage());
            return redirect()->back()->with('error', "Une erreur est survenue lors de la suppression.");
        }
    }

    /**
     * Récupérer les données d'un groupe utilisateur pour modification
     */
    public function edit($id)
    {
        try {
            $groupe = GroupeUtilisateur::findOrFail($id);
            return response()->json($groupe);
        } catch (\Exception $e) {
            Log::error("Erreur lors de la récupération du groupe utilisateur ID {$id} : " . $e->getMessage());
            return response()->json(['error' => "Impossible de récupérer le groupe utilisateur."], 500);
        }
    }
}
