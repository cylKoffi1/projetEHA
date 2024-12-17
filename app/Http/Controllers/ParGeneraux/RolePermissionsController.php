<?php

namespace App\Http\Controllers\ParGeneraux;

use App\Http\Controllers\Controller;
use App\Models\Ecran;
use App\Models\GroupeUtilisateur;
use App\Models\RolePermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RolePermissionsController extends Controller
{
    public function index(Request $request)
    {
        try {
            Log::info('Chargement des permissions de rôles.');

            $ecran = Ecran::find($request->input('ecran_id'));
            $permissions = RolePermission::with(['source', 'target'])->get();
            $roles = GroupeUtilisateur::all();

            Log::info('Permissions de rôles chargées avec succès.');

            return view('parGeneraux.role_permissions', compact('ecran', 'permissions', 'roles'));
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement des permissions de rôles : ' . $e->getMessage());
            return redirect()->back()->withErrors("Une erreur est survenue lors du chargement des permissions de rôles.");
        }
    }

    public function store(Request $request)
    {
        try {
            Log::info('Enregistrement d\'une permission de rôle.', ['data' => $request->all()]);

            $request->validate([
                'role_source' => 'required|string|exists:groupe_utilisateur,code',
                'role_target' => 'required|string|exists:groupe_utilisateur,code',
                'can_assign' => 'required|boolean',
            ]);

            if ($request->id) {
                // Mise à jour
                $permission = RolePermission::findOrFail($request->id);
                $permission->update($request->all());
                Log::info("Permission de rôle mise à jour avec succès : ID {$permission->id}");
            } else {
                // Création
                $permission = RolePermission::create($request->all());
                Log::info("Nouvelle permission de rôle créée avec succès : ID {$permission->id}");
            }

            return redirect()->back()->with('success', 'Permission de rôle enregistrée avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'enregistrement de la permission de rôle : ' . $e->getMessage());
            return redirect()->back()->withErrors("Une erreur est survenue lors de l'enregistrement de la permission de rôle.");
        }
    }

    public function destroy($id)
    {
        try {
            Log::info("Tentative de suppression de la permission de rôle : ID {$id}");

            $permission = RolePermission::findOrFail($id);
            $permission->delete();

            Log::info("Permission de rôle supprimée avec succès : ID {$id}");

            return redirect()->back()->with('success', 'Permission de rôle supprimée avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression de la permission de rôle : ' . $e->getMessage());
            return redirect()->back()->withErrors("Une erreur est survenue lors de la suppression de la permission de rôle.");
        }
    }
}
