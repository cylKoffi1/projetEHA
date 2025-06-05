<?php

namespace App\Http\Controllers;

use App\Models\Acteur;
use App\Models\controler;
use App\Models\Ecran;
use App\Models\Executer;
use App\Models\MotifStatutProjet;
use App\Models\Projet;
use App\Models\ProjetStatut;
use App\Models\SecteurActivite;
use Exception;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class ProjetController extends Controller
{
    //////////////////////////////////////DEFINITION DE PROJET////////////////////////////////
    public function projet(Request $request)
    {
        $country = session('pays_selectionne');
        $group = session('projet_selectionne');

        $projets = Projet::where('projets.code_alpha3_pays', $country)
            ->where('projets.code_projet', 'like', $country . $group . '%')
            ->whereNotIn('projets.code_projet', function ($query) {
                $query->select('code_projet')
                    ->from('controler')
                    ->where('is_active', true);
            })
            ->join('projet_statut', 'projet_statut.code_projet', '=', 'projets.code_projet')
            ->where('projet_statut.type_statut', 2)
            ->get();


        $chefs = Acteur::where('type_acteur', 'etp')
            ->where('code_pays', $country)
            ->get();

            $contrats = controler::with('acteur')
            ->join('projets', 'projets.code_projet', '=', 'controler.code_projet')
            ->join('projet_statut', 'projet_statut.code_projet', '=', 'projets.code_projet')
            ->where('projets.code_alpha3_pays', $country)
            ->where('projets.code_projet', 'like', $country . $group . '%')
            ->where('projet_statut.type_statut', 2)
            ->where('controler.is_active', true)
            ->select('controler.*') // ← très important !
            ->get(); 
        

        return view('projet', compact('chefs', 'projets', 'contrats'));
    }
    public function reatributionProjet(Request $request)
    {
        $paysSelectionne = session('pays_selectionne');
        $groupeSelectionne = session('projet_selectionne');
    
        $projets = Projet::where('projets.code_alpha3_pays', $paysSelectionne)
            ->join('executer', 'executer.code_projet', '=', 'projets.code_projet')
            ->where('projets.code_projet', 'like', $paysSelectionne . $groupeSelectionne . '%')            
            ->get();
    
        $acteurs = Acteur::where('type_acteur', 'etp')
            ->where('code_pays', $paysSelectionne)
            ->get();
    
        $executions = Executer::with('acteur')
            ->where('is_active', true)
            ->where('code_projet', 'like', $paysSelectionne . $groupeSelectionne . '%')
            ->get();
    
        $SecteurActivites = SecteurActivite::all();
    
        return view('reattributionProjet', compact(
            'projets',
            'acteurs',
            'executions',
            'SecteurActivites'
        ));
    }
    
    public function storeReatt(Request $request)
    {
        try {
            $validated = $request->validate([
                'projet_id' => 'required|string',
                'acteur_id' => 'required|string',
                'secteur_id' => 'nullable|integer',
                'motif' => 'nullable|string|max:255'
            ]);
    
            $execution = Executer::create([
                'code_projet' => $validated['projet_id'],
                'code_acteur' => $validated['acteur_id'],
                'secteur_id' => $validated['secteur_id'],
                'motif' => $validated['motif'],
                'is_active' => true
            ]);
    
            Log::info('Maître d’œuvre affecté', [
                'user_id' => auth()->id(),
                'data' => $execution
            ]);
    
            return response()->json(['success' => 'Maître d’œuvre attribué avec succès.']);
    
        } catch (\Throwable $e) {
            Log::error('Erreur attribution MOE', [
                'user_id' => auth()->id(),
                'message' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Erreur lors de l\'attribution du maître d’œuvre.'], 500);
        }
    }
    
    public function updateReatt(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'projet_id' => 'required|string',
                'acteur_id' => 'required|string',
                'secteur_id' => 'nullable|integer',
                'motif' => 'required|string|max:255'
            ]);
    
            $execution = Executer::findOrFail($id);
    
            $execution->update([
                'code_projet' => $validated['projet_id'],
                'code_acteur' => $validated['acteur_id'],
                'secteur_id' => $validated['secteur_id'],
                'motif' => $validated['motif'],
            ]);
    
            Log::info('Maître d’œuvre modifié', [
                'user_id' => auth()->id(),
                'data' => $execution
            ]);
    
            return response()->json(['success' => 'Maître d’œuvre mis à jour.']);
    
        } catch (\Throwable $e) {
            Log::error('Erreur modification MOE', [
                'user_id' => auth()->id(),
                'message' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Erreur lors de la mise à jour du maître d’œuvre.'], 500);
        }
    }
    
    public function destroyReatt($id)
    {
        try {
            $execution = Executer::findOrFail($id);
            $execution->delete();
    
            Log::info('Maître d’ouvrage supprimé', [
                'user_id' => auth()->id(),
                'id' => $id
            ]);
    
            return response()->json(['success' => 'Maître d’ouvrage supprimé avec succès.']);
    
        } catch (\Throwable $e) {
            Log::error('Erreur suppression MO', [
                'user_id' => auth()->id(),
                'message' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Erreur lors de la suppression.'], 500);
        }
    }
    public function getExecutionByProjet($code_projet)
    {
        $execution = Executer::with('acteur')
            ->where('code_projet', $code_projet)
            ->where('is_active', true)
            ->first();

        if (!$execution) {
            return response()->json(null);
        }

        return response()->json([
            'id' => $execution->id,
            'code_projet' => $execution->code_projet,
            'code_acteur' => $execution->code_acteur,
            'secteur_id' => $execution->secteur_id,
            'motif' => $execution->motif,
            'acteur_type' => $execution->acteur->type_acteur
        ]);
    }

    /*
        public function store(Request $request)
        {
            try {
                $data = $request->validate([
                    'chef_projet_id' => 'required|exists:acteur,code_acteur',
                    'projet_id' => 'required|exists:projets,code_projet',
                    'date_debut' => 'required|date',
                    'date_fin' => 'required|date|after_or_equal:date_debut',
                ]);
        
                // Récupère le projet concerné
                $projet = Projet::where('code_projet', $data['projet_id'])->first();
        
                if (!$projet) {
                    return redirect()->back()->with('error', 'Projet introuvable.');
                }
        
                $dateDebutContrat = $data['date_debut'];
                $dateFinContrat = $data['date_fin'];
                $dateDemarragePrevue = $projet->date_demarrage_prevue;
                $dateFinPrevue = $projet->date_fin_prevue;
        
                // Vérification des dates du contrat par rapport à celles du projet
                if ($dateDebutContrat < $dateDemarragePrevue || $dateFinContrat > $dateFinPrevue) {
                    return redirect()->back()->with('error', "La période du contrat doit être comprise entre le {$dateDemarragePrevue} et le {$dateFinPrevue}.");
                }
        
                // Création du contrat
                $contrat = controler::create([
                    'code_projet' => $data['projet_id'],
                    'code_acteur' => $data['chef_projet_id'],
                    'date_debut' => $dateDebutContrat,
                    'date_fin' => $dateFinContrat,
                    'is_active' => true,
                ]);
        
                return redirect()->route('contrats.fiche', $contrat->id)
                                ->with('success', 'Contrat enregistré avec succès.');
            
            } catch (\Illuminate\Validation\ValidationException $e) {
                // Erreurs de validation
                return redirect()->back()->withErrors($e->validator)->withInput();
        
            } catch (\Exception $e) {
                // Toute autre erreur (base de données, logique, etc.)
                Log::error('Erreur lors de la création du contrat : ' . $e->getMessage());
                return redirect()->back()->with('error', 'Une erreur est survenue. Veuillez réessayer.');
            }
        }
    */

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'projet_id' => 'required|string',
                'chef_projet_id' => 'required|string',
                'date_debut' => 'required|date',
                'date_fin' => 'required|date|after_or_equal:date_debut',
            ]);

            $contrat = controler::create([
                'code_projet' => $validated['projet_id'],
                'code_acteur' => $validated['chef_projet_id'],
                'date_debut' => $validated['date_debut'],
                'date_fin' => $validated['date_fin'],
                'is_active' => true,
            ]);

            Log::info('Contrat créé', ['user_id' => auth()->id(), 'contrat' => $contrat]);

            return response()->json(['success' => 'Contrat enregistré avec succès.', 'data' => $contrat]);

        } catch (\Throwable $e) {
            Log::error('Erreur création contrat', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Erreur lors de l\'enregistrement du contrat.'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'projet_id' => 'required|string',
                'chef_projet_id' => 'required|string',
                'date_debut' => 'required|date',
                'date_fin' => 'required|date|after_or_equal:date_debut',
            ]);

            $contrat = controler::findOrFail($id);
            $contrat->update([
                'code_projet' => $validated['projet_id'],
                'code_acteur' => $validated['chef_projet_id'],
                'date_debut' => $validated['date_debut'],
                'date_fin' => $validated['date_fin'],
            ]);

            Log::info('Contrat modifié', ['user_id' => auth()->id(), 'contrat' => $contrat]);

            return response()->json(['success' => 'Contrat modifié avec succès.', 'data' => $contrat]);

        } catch (\Throwable $e) {
            Log::error('Erreur mise à jour contrat', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Erreur lors de la mise à jour du contrat.'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $contrat = controler::findOrFail($id);
            $contrat->delete();

            Log::info('Contrat supprimé', ['user_id' => auth()->id(), 'contrat_id' => $id]);

            return response()->json(['success' => 'Contrat supprimé avec succès.']);
        } catch (\Throwable $e) {
            Log::error('Erreur suppression contrat', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Erreur lors de la suppression du contrat.'], 500);
        }
    }

    public function fiche($id)
    {
        $contrat = controler::with('acteur')->findOrFail($id);
        return view('contracts.fiche_chef_projet', compact('contrat'));
    }

    public function pdf($id)
    {
        $contrat = controler::with([
            'acteur',
            'projet.maitreOuvrage.acteur',
            'projet.localisations.localite.decoupage'
        ])->find($id);
    
        // Vérification si le contrat ou le projet est manquant
        if (!$contrat || !$contrat->projet) {
            return redirect()->back()->with('error', 'Aucune donnée disponible pour générer cette fiche de contrat.');
        }
        $pdf = pdf::loadView('contracts.fiche_chef_projet', compact('contrat'));
        return $pdf->download('fiche_contrat_' . $contrat->id . '.pdf');
    }
    /*
        public function update(Request $request, $id)
        {
            $data = $request->validate([
                'chef_projet_id' => 'required|exists:acteur,code_acteur',
                'projet_id' => 'required|exists:projets,code_projet',
                'date_debut' => 'required|date',
                'date_fin' => 'required|date|after_or_equal:date_debut',
            ]);
        
            try {
                $contrat = Controler::findOrFail($id);
                $contrat->update([
                    'code_projet' => $data['projet_id'],
                    'code_acteur' => $data['chef_projet_id'],
                    'date_debut' => $data['date_debut'],
                    'date_fin' => $data['date_fin'],
                ]);
        
                return redirect()->route('projet')->with('success', 'Contrat modifié avec succès.');
        
            } catch (\Exception $e) {
                Log::error('Erreur lors de la mise à jour du contrat: ' . $e->getMessage());
                return back()->with('error', 'Une erreur est survenue lors de la mise à jour du contrat.');
            }
        }
        
        public function destroy($id)
        {
            $contrat = controler::findOrFail($id);
            $contrat->delete();
        
            return redirect()->route('projet')->with('success', 'Contrat supprimé.');
        }
    */

    public function changerChef(Request $request)
    {
        $country = session('pays_selectionne');
        $group = session('projet_selectionne');

        $projets = Projet::where('projets.code_alpha3_pays', $country)
            ->where('projets.code_projet', 'like', $country . $group . '%')
            ->join('projet_statut', 'projet_statut.code_projet', '=', 'projets.code_projet')
            ->where('projet_statut.type_statut', 2)
            ->where()
            ->get();

        $chefs = Acteur::where('type_acteur', 'etp')
            ->where('code_pays', $country)
            ->get();

            $contrats = controler::with('acteur')
            ->join('projets', 'projets.code_projet', '=', 'controler.code_projet')
            ->join('projet_statut', 'projet_statut.code_projet', '=', 'projets.code_projet')
            ->where('projets.code_alpha3_pays', $country)
            ->where('projets.code_projet', 'like', $country . $group . '%')
            ->where('projet_statut.type_statut', 2)
            ->select('controler.*') // ← très important !
            ->get();
        

        return view('changementChefProjet', compact('chefs', 'projets', 'contrats'));
    }
    public function changerChefUpdate(Request $request)
    {
        $data = $request->validate([
            'contrat_id' => 'required|exists:controler,id',
            'nouveau_chef_id' => 'required|exists:acteur,code_acteur',
            'motif' => 'required|string|max:1000',
        ]);
    
        // Récupération du contrat d'origine
        $ancienContrat = controler::findOrFail($data['contrat_id']);
        $ancienChef = $ancienContrat->code_acteur;
    
        // Désactivation de l'ancien contrat
        $ancienContrat->update(['is_active' => false]);
    
        // Création d'un nouveau contrat avec le nouveau chef
        $contrat = controler::create([
            'code_projet' => $ancienContrat->code_projet,
            'code_acteur' => $data['nouveau_chef_id'], // ✅ corrigé ici
            'date_debut' => now()->toDateString(),
            'date_fin' => $ancienContrat->date_fin,
            'is_active' => true,
            'motif' => $data['motif'],
        ]);
    
        // Journalisation
        Log::info("Changement de chef projet sur contrat #{$ancienContrat->id} : {$ancienChef} => {$data['nouveau_chef_id']}. Motif: {$data['motif']}");
    
        return back()->with('success', 'Le chef de projet a été changé avec succès.');
    }
    
    public function formAnnulation()
    {
        $pays = session('pays_selectionne');        
        $groupe = session('projet_selectionne');
        $projets = Projet::where('code_projet', 'like', $pays . $groupe . '%')
        ->join('projet_statut', 'projet_statut.code_projet', '=', 'projets.code_projet')
        ->whereIn('projet_statut.type_statut', [1, 2, 5, 6])->get();

        // Liste des projets annulés
        $projetsAnnules = Projet::whereHas('statuts', function ($query) {
            $query->where('type_statut', 4);
        })->with(['statuts', 'statuts.statut'])->get();

        return view('annulationProjet', compact('projets', 'projetsAnnules'));
    }


    public function annulerProjet(Request $request)
    {
        $request->validate([
            'code_projet' => 'required|string|exists:projets,code_projet',
            'motif' => 'required|string|min:5',
        ]);
    
        try {
            // Enregistrement du statut "annulé"
            ProjetStatut::create([
                'code_projet' => $request->code_projet,
                'type_statut' => 4, // ID = 4 pour "Annulé"
                'date_statut' => now(),
            ]);
    
            // Enregistrement du motif lié à ce statut
            MotifStatutProjet::create([
                'code_projet' => $request->code_projet,
                'type_statut' => 4,
                'motif' => $request->motif,
                'code_acteur' => auth()->user()?->acteur_id,
                'date_motif' => now(),
            ]);
    
            Log::info('Projet annulé', [
                'code_projet' => $request->code_projet,
                'motif' => $request->motif,
                'user_id' => auth()->id(),
            ]);
    
            return redirect()->route('projets.annulation.form')
                             ->with('success', 'Projet annulé avec succès.');
    
        } catch (\Throwable $e) {
            Log::error('Erreur lors de l’annulation du projet', [
                'code_projet' => $request->code_projet,
                'message' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
    
            return back()->with('error', 'Erreur lors de l’annulation du projet.');
        }
    }
    
    public function formSuspension()
    {
        $paysSelectionne = session('pays_selectionne');
        $groupeSelectionne = session('projet_selectionne');

        $projets = Projet::where('code_alpha3_pays', $paysSelectionne)
            ->where('code_projet', 'like', $paysSelectionne . $groupeSelectionne . '%')
            ->whereIn('projet_statut.type_statut', [1, 2, 6])
            ->get();

        $projetsSuspendus = Projet::whereHas('statuts', function ($query) {
            $query->where('type_statut', 5);
        })->get();

        return view('suspendreProjet', compact('projets', 'projetsSuspendus'));
    }

    public function suspendreProjet(Request $request)
    {
        $request->validate([
            'code_projet' => 'required|string|exists:projets,code_projet',
            'motif' => 'required|string|min:5',
        ]);

        try {
            ProjetStatut::create([
                'code_projet' => $request->code_projet,
                'type_statut' => 5, // 5 = Suspendu
                'date_statut' => now(),
            ]);

            Log::info('Projet suspendu', [
                'code_projet' => $request->code_projet,
                'motif' => $request->motif,
                'user_id' => auth()->id()
            ]);

            return redirect()->route('projets.suspension.form')->with('success', 'Projet suspendu avec succès.');
        } catch (\Throwable $e) {
            Log::error('Erreur suspension projet', ['message' => $e->getMessage()]);
            return back()->with('error', 'Erreur lors de la suspension du projet.');
        }
    }
}

