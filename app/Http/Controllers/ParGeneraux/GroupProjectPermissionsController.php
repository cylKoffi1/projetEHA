<?php

namespace App\Http\Controllers\ParGeneraux;

use App\Http\Controllers\Controller;
use App\Models\Ecran;
use App\Models\GroupeUtilisateur;
use App\Models\GroupProjectPermission;
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
}
