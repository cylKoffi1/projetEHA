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
use App\Models\StatutProjet;
use Exception;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Models\Project;     // si tu as un modèle Projet
use App\Models\User;
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
        

        return view('projets.projet', compact('chefs', 'projets', 'contrats'));
    }

    
    public function store(Request $request)
    {
        try {
            // --- 1) Validation "de base" ---
            $validator = Validator::make(
                $request->all(),
                [
                    'projet_id'      => ['required', 'string', Rule::exists('projets', 'id')], // adapte table/colonne
                    'chef_projet_id' => ['required', 'string', Rule::exists('acteur', 'code_acteur')],    // adapte table/colonne
                    'date_debut'     => ['required', 'date', 'before_or_equal:date_fin'],
                    'date_fin'       => ['required', 'date', 'after_or_equal:date_debut'],
                ],
                [
                    'projet_id.required'      => 'Le projet est obligatoire.',
                    'projet_id.exists'        => 'Le projet sélectionné est introuvable.',
                    'chef_projet_id.required' => "Le chef de projet est obligatoire.",
                    'chef_projet_id.exists'   => "Le chef de projet sélectionné est introuvable.",
                    'date_debut.required'     => 'La date de début est obligatoire.',
                    'date_debut.date'         => 'La date de début n’est pas valide.',
                    'date_debut.before_or_equal' => 'La date de début doit être antérieure ou égale à la date de fin.',
                    'date_fin.required'       => 'La date de fin est obligatoire.',
                    'date_fin.date'           => 'La date de fin n’est pas valide.',
                    'date_fin.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début.',
                ]
            );
    
            // --- 2) Règles de gestion supplémentaires ---
            $validator->after(function ($validator) use ($request) {
    
                $start = Carbon::parse($request->input('date_debut'));
                $end   = Carbon::parse($request->input('date_fin'));
    
                // (A) Fin pas avant aujourd'hui
                if ($end->lt(today())) {
                    $validator->errors()->add('date_fin', "La date de fin ne peut pas être antérieure à aujourd'hui.");
                }
    
                // (B) Durée minimale (ex. 1 mois)
                $minMonths = 1;
                if ($start->diffInMonths($end) < $minMonths) {
                    $validator->errors()->add('date_fin', "La durée d’un contrat ne peut pas être inférieure à $minMonths mois.");
                }
    
                // (C) Pas de chevauchement pour le même acteur (sur contrats actifs)
                $overlap = controler::query()
                    ->where('code_acteur', $request->input('chef_projet_id'))
                    ->when(schema()->hasColumn((new controler)->getTable(), 'is_active'), function ($q) {
                        $q->where('is_active', true);
                    })
                    ->where(function ($q) use ($start, $end) {
                        $q->whereBetween('date_debut', [$start, $end])
                          ->orWhereBetween('date_fin', [$start, $end])
                          ->orWhere(function ($q2) use ($start, $end) {
                              $q2->where('date_debut', '<=', $start)
                                 ->where('date_fin', '>=', $end);
                          });
                    })
                    ->exists();
    
                if ($overlap) {
                    $validator->errors()->add('date_debut', "Ce chef de projet a déjà un contrat actif qui chevauche ces dates.");
                    $validator->errors()->add('date_fin',   "Ce chef de projet a déjà un contrat actif qui chevauche ces dates.");
                }
    
                // (D) Le contrat commence au plus tôt à la date de démarrage prévue du projet
                if (class_exists(Projet::class)) {
                    $projet = Projet::query()->find($request->input('projet_id'));
                    if ($projet) {
                        $pStart = $projet->date_demarrage_prevue ? Carbon::parse($projet->date_demarrage_prevue) : null;
                        if ($pStart && $start->lt($pStart)) {
                            $validator->errors()->add('date_debut', "La date de début du contrat doit être ≥ à la date de début du projet ({$pStart->toDateString()}).");
                        }
                    }
                }
    
                // (E) Période non nulle
                if ($start->equalTo($end)) {
                    $validator->errors()->add('date_fin', "La période doit couvrir au moins une journée (dates strictement différentes).");
                }
            });
    
            if ($validator->fails()) {
                return response()->json([
                    'message' => "Des erreurs ont été détectées.",
                    'errors'  => $validator->errors(),
                ], 422);
            }
    
            $validated = $validator->validated();
    
            // --- 3) Création (transaction + statut 201) ---
            return DB::transaction(function () use ($validated, $request) {
    
                $contrat = controler::create([
                    'code_projet' => $validated['projet_id'],
                    'code_acteur' => $validated['chef_projet_id'],
                    'date_debut'  => $validated['date_debut'],
                    'date_fin'    => $validated['date_fin'],
                    'is_active'   => true,
                ]);
    
                Log::info('Contrat créé', [
                    'user_id' => auth()->id(),
                    'payload' => [
                        'projet_id'      => $validated['projet_id'],
                        'chef_projet_id' => $validated['chef_projet_id'],
                        'date_debut'     => $validated['date_debut'],
                        'date_fin'       => $validated['date_fin'],
                    ],
                    'contrat_id' => $contrat->id,
                ]);
    
                return response()->json([
                    'success' => 'Contrat enregistré avec succès.',
                    'data'    => $contrat,
                ], 201);
            });
    
        } catch (\Throwable $e) {
            Log::error('Erreur création contrat', [
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);
    
            return response()->json([
                'error' => 'Erreur lors de l\'enregistrement du contrat.',
            ], 500);
        }
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
            'acteur_nom' => $execution->acteur->libelle_court . ' ' . $execution->acteur->libelle_long,
            'secteur_id' => $execution->secteur_id,
            'motif' => $execution->motif,
            'acteur_type' => $execution->acteur->type_acteur
        ]);
        
    }

    public function getProjetSupprimer($code_projet)
    {
        $execution = Projet::where('code_projet', $code_projet)
            ->first();

        if (!$execution) {
            return response()->json(null);
        }

        return response()->json([
            'code_projet' => $execution->code_projet,
            'nature' => $execution->statuts?->statut->libelle,
            'domaine' => $execution->sousDomaine?->Domaine?->libelle,
            'sousDomaine' =>$execution->sousDomaine?->lib_sous_domaine,
            'cout' => $execution->cout_projet,
            'maitreOeuvre' => $execution->maitresOeuvre->map(function($moe) {
                                return $moe->acteur->libelle_long ?? null;
                            })->filter()->values(),

            'maitreOuvrage' => $execution->maitreOuvrage?->acteur->libelle_long ,
            'localite' => $execution->localisations?->map(function($mose) {
                return $mose->localite->libelle ?? null;
            })->filter()->values(),
            'devise' => $execution->code_devise,
            'libelle_projet' => $execution->libelle_projet,
            'date_demarrage_prevue' => $execution->date_demarrage_prevue,
            'date_fin_prevue' => $execution->date_fin_prevue
        ]);
        
    }



    
    public function update(Request $request, $id)
    {
        try {
            // --- 1) Validation "de base" ---
            $validator = Validator::make(
                $request->all(),
                [
                    'projet_id'      => ['required', 'string', Rule::exists('projets', 'id')], // adapte la table/colonne
                    'chef_projet_id' => ['required', 'string', Rule::exists('users', 'id')],    // adapte la table/colonne
                    'date_debut'     => ['required', 'date', 'before_or_equal:date_fin'],
                    'date_fin'       => ['required', 'date', 'after_or_equal:date_debut'],
                ],
                [
                    'projet_id.required'      => 'Le projet est obligatoire.',
                    'projet_id.exists'        => 'Le projet sélectionné est introuvable.',
                    'chef_projet_id.required' => "Le chef de projet est obligatoire.",
                    'chef_projet_id.exists'   => "Le chef de projet sélectionné est introuvable.",
                    'date_debut.required'     => 'La date de début est obligatoire.',
                    'date_debut.date'         => 'La date de début n’est pas valide.',
                    'date_debut.before_or_equal' => 'La date de début doit être antérieure ou égale à la date de fin.',
                    'date_fin.required'       => 'La date de fin est obligatoire.',
                    'date_fin.date'           => 'La date de fin n’est pas valide.',
                    'date_fin.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début.',
                ]
            );
    
            // --- 2) Règles de gestion "métier" supplémentaires ---
            $validator->after(function ($validator) use ($request, $id) {
    
                $start = Carbon::parse($request->input('date_debut'));
                $end   = Carbon::parse($request->input('date_fin'));
    
                // (A) Interdire un contrat qui se termine avant aujourd'hui ? -> décommente si voulu
                if ($end->lt(today())) {
                     $validator->errors()->add('date_fin', "La date de fin ne peut pas être antérieure à aujourd'hui.");
                 }
    
                // (B) Durée minimale (ex: 1 mois) -> optionnel
                $maxMonths = 1; // adapte ou supprime
                if ($start->diffInMonths($end) < $maxMonths) {
                    $validator->errors()->add('date_fin', "La durée d’un contrat ne peut être inférrieur $maxMonths mois.");
                }
    
                // (C) Chevauchement de périodes pour le même acteur (chef_projet_id)
                $overlap = controler::query()
                    ->where('code_acteur', $request->input('chef_projet_id'))
                    ->where('id', '!=', $id)
                    ->where(function ($q) use ($start, $end) {
                        $q->whereBetween('date_debut', [$start, $end])
                          ->orWhereBetween('date_fin', [$start, $end])
                          ->orWhere(function ($q2) use ($start, $end) {
                              $q2->where('date_debut', '<=', $start)
                                 ->where('date_fin', '>=', $end);
                          });
                    })
                    ->exists();
    
                if ($overlap) {
                    $validator->errors()->add('date_debut', "Ce chef de projet a déjà un contrat qui chevauche ces dates.");
                    $validator->errors()->add('date_fin',   "Ce chef de projet a déjà un contrat qui chevauche ces dates.");
                }
    
                // (D) Vérifier que le contrat est dans la fenêtre du projet (si le projet a des dates)
                if (class_exists(Projet::class)) {
                    $projet = Projet::query()->find($request->input('projet_id'));
                    if ($projet) {
                        // Adapte les noms de colonnes si nécessaire
                        $pStart = optional($projet->date_demarrage_prevue) ? Carbon::parse($projet->date_demarrage_prevue) : null;
                      
                        if ($pStart && $start->lt($pStart)) {
                            $validator->errors()->add('date_debut', "La date de début du contrat doit être ≥ à la date de début du projet ({$pStart->toDateString()}).");
                        }
                    }
                }
    
                // (E) Interdire d’affecter un chef de projet à un autre projet simultanément ? (déjà couvert par chevauchement)
                // (F) Empêcher date_debut == date_fin si tu veux au moins 1 jour
                if ($start->equalTo($end)) {
                    $validator->errors()->add('date_fin', "La période doit couvrir au moins une journée (dates strictement différentes).");
                }
            });
    
            if ($validator->fails()) {
                // 422 = Unprocessable Entity avec détails des erreurs
                return response()->json([
                    'message' => "Des erreurs ont été détectées.",
                    'errors'  => $validator->errors(),
                ], 422);
            }
    
            $validated = $validator->validated();
    
            // --- 3) Mise à jour ---
            $contrat = controler::findOrFail($id);
    
            $contrat->update([
                'code_projet' => $validated['projet_id'],
                'code_acteur' => $validated['chef_projet_id'],
                'date_debut'  => $validated['date_debut'],
                'date_fin'    => $validated['date_fin'],
            ]);
    
            Log::info('Contrat modifié', [
                'user_id' => auth()->id(),
                'contrat' => $contrat,
            ]);
    
            return response()->json([
                'success' => 'Contrat modifié avec succès.',
                'data'    => $contrat,
            ]);
    
        } catch (\Throwable $e) {
            Log::error('Erreur mise à jour contrat', [
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);
    
            return response()->json([
                'error' => 'Erreur lors de la mise à jour du contrat.',
            ], 500);
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
            ->select('controler.*') 
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
            'code_acteur' => $data['nouveau_chef_id'], 
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
        $projets = Projet::where('projet_statut.code_projet', 'like', $pays . $groupe . '%')
        ->join('projet_statut', 'projet_statut.code_projet', '=', 'projets.code_projet')
        ->whereIn('projet_statut.type_statut', [1, 2, 5, 6])->get();


        return view('annulationProjet', compact('projets'));
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
    
        // Projets éligibles à la suspension
        $projets = Projet::where('code_alpha3_pays', $paysSelectionne)
            ->where('code_projet', 'like', $paysSelectionne . $groupeSelectionne . '%')
            ->whereHas('dernierStatut', function ($query) {
                $query->whereIn('type_statut', [1, 2, 6]);
            })
            ->get();
    
        // Projets suspendus (dont dernier statut est 5 ou 6)
        $projetsSuspendus = Projet::where('code_projet', 'like', $paysSelectionne . $groupeSelectionne . '%')
            ->whereHas('dernierStatut', function ($query) {
                $query->whereIn('type_statut', [5, 6]);
            })
            ->with([
                'statuts' => function ($q) {
                    $q->whereIn('type_statut', [5, 6])
                      ->orderBy('date_statut');
                },
                'dernierStatut.statut'
            ])
            ->get();
    
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
                'type_statut' => 5,
                'date_statut' => now(),
                'motif' => $request->motif,
            ]);

            Log::info('Projet suspendu', [
                'code_projet' => $request->code_projet,
                
                'user_id' => auth()->id()
            ]);

            return redirect()->route('projets.suspension.form')->with('success', 'Projet suspendu avec succès.');
        } catch (\Throwable $e) {
            Log::error('Erreur suspension projet', ['message' => $e->getMessage()]);
            return back()->with('error', 'Erreur lors de la suspension du projet.');
        }
    }

    public function redemarrerProjet(Request $request){
        try{
            $request->validate([
                'code_projet' => 'required|string|exists:projets,code_projet',
               
            ]);

            $reslt = ProjetStatut::where('code_projet', $request->code_projet )
                ->where('type_statut', 5)
                ->orderByDesc('date_statut')
                ->first();

            if ($reslt && $reslt->date_statut >= $request->dateRedemarrage) {
                return response()->json([
                    'error' => 'La date de redémarrage doit être supérieure à la date de suspension.'
                ]);
            } else {
                ProjetStatut::create([
                    'code_projet' => $request->code_projet,
                    'type_statut' => 6,
                    'date_statut' => $request->dateRedemarrage,
                ]);
            }
            Log::info('Projet suspendu', [
                'code_projet' => $request->code_projet,                
                'user_id' => auth()->id()
            ]);

            return response()->json([
                 'success' => 'Projet redemarré avec succes.',
                 //'redirect' => route('projets.suspension.store')
            ], 200);
        }catch(\Throwable $e){
            Log::error('Erreur redemarrage projet', ['message' => $e->getMessage()]);
            return response()->json([
                'error' => 'Erreur lors du redemarrage de projet.'
           ], 200);
            return back()->with('error', 'Erreur lors du redemarrage de projet.');
        }
    }
}

