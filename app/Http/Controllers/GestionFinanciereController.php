<?php

namespace App\Http\Controllers;

use App\Models\Projet;
use App\Models\Acteur;
use App\Models\Banque;
use App\Models\Financer;
use App\Models\Decaissement;
use App\Models\AchatMateriau;
use App\Models\AchatMateriauLigne;
use App\Models\AchatStatut;
use App\Models\Devise;
use App\Models\Ecran;
use App\Models\GroupeProjet;
use App\Models\ReglementPrestataire;
use App\Models\ReglementStatut;
use App\Models\ModePaiement;
use App\Models\Pays;
use App\Models\Pib;
use App\Models\Renforcement;
use App\Models\TravauxConnexes;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
class GestionFinanciereController extends Controller
{
  
    /******************* DECAISSEMENTS ***********************/
        /** Liste + écran principal */
        public function decaissementsIndex(Request $request)
        {
            $pays   = session('pays_selectionne');
            $groupe = session('projet_selectionne');
    
            $ecranId = $request->input('ecran_id');
            $ecran   = $ecranId ? Ecran::find($ecranId) : null;
    
            if ($ecran && Gate::denies('consulter_ecran_'.$ecran->id)) {
                abort(403);
            }
    
            $projets = Projet::where('code_projet', 'like', $pays.$groupe.'%')
                ->orderBy('code_projet')
                ->get(['code_projet','libelle_projet','code_devise']);
    
            $decaissements = Decaissement::with(['projet','bailleur'])
                ->whereHas('projet', fn($q)=>$q->where('code_projet','like',$pays.$groupe.'%'))
                ->orderByDesc('date_decaissement')
                ->paginate(25);
    
                $alpha3 = session('pays_selectionne');
            // nécessaires à la vue
            $banques = Banque::where('actif', true)
            ->where(function ($q) use ($alpha3) {
                $q->where('code_pays', $alpha3)
                ->orWhere('est_internationale', true);
            })
                ->orderBy('sigle')
                ->get(['id','sigle','nom','code_pays','est_internationale']);
    
            $modes = ModePaiement::all(['id','libelle']); // si tu en as besoin dans cette vue
    
            return view('GestionFinanciere.decaissementBailleurs', compact(
                'ecran','projets','decaissements','banques','modes'
            ));
        }
    
        /** Dernière date de décaissement pour (projet, bailleur), optionnellement en excluant un id. */
        private function getLastDecaissementDate(string $codeProjet, string $codeActeur, ?int $excludeId = null): ?Carbon
        {
            $q = Decaissement::where('code_projet', $codeProjet)
                ->where('code_acteur', $codeActeur);
    
            if ($excludeId) {
                $q->where('id', '!=', $excludeId);
            }
    
            $last = $q->max('date_decaissement');
            return $last ? Carbon::parse($last) : null;
        }
    
        public function getNextTranche(Request $request)
        {
            $code_projet = $request->get('code_projet');
            $financer_id = $request->get('financer_id'); // peut être null
    
            // Résoudre le bailleur si financer_id fourni
            $codeActeur = null;
            if (!empty($financer_id)) {
                $fin = Financer::with('bailleur')->find($financer_id);
                $codeActeur = $fin?->bailleur?->code_acteur;
            }
    
            $max = Decaissement::where('code_projet', $code_projet)
                ->when($codeActeur, fn($q) => $q->where('code_acteur', $codeActeur))
                ->max('tranche_no');
    
            $next = $max ? ((int)$max + 1) : 1;
    
            return response()->json(['next' => $next]);
        }
    
        private function computeNextTranche(string $codeProjet, $financerId = null, $codeActeur = null): int
        {
            if (!$codeActeur && $financerId) {
                $fin = Financer::with('bailleur')->find($financerId);
                $codeActeur = $fin?->bailleur?->code_acteur;
            }
    
            $max = Decaissement::where('code_projet', $codeProjet)
                ->when($codeActeur, fn($q) => $q->where('code_acteur', $codeActeur))
                ->max('tranche_no');
    
            return $max ? ((int)$max + 1) : 1;
        }
    
        /** JSON: listage des financements par projet */
        public function financementsByProjet(string $codeProjet)
        {
            $financements = Financer::with(['bailleur.secteurActiviteActeur.secteur'])
                ->where('code_projet', $codeProjet)
                ->get();
    
            $payload = $financements->map(function ($f) {
                $b = $f->bailleur;
                $isMinistere = trim($b->libelle_court) === 'Ministère' || trim($b->libelle_long) === 'Ministère';
                $secteur = optional($b->secteurActiviteActeur->first())->secteur?->libelle;
                $bailleurLabel = trim(($b->libelle_court ?? '').' '.($b->libelle_long ?? ''));
                if ($isMinistere && $secteur) $bailleurLabel = 'Ministère de '.$secteur;
    
                return [
                    'id'               => $f->id,
                    'montant'          => (float) $f->montant_finance,
                    'montant_fmt'      => number_format((float)$f->montant_finance, 0, ',', ' '),
                    'devise'           => $f->devise,
                    'date'             => optional($f->date_engagement)->format('Y-m-d'),
                    'bailleur_id'      => $b?->code_acteur,
                    'bailleur_label'   => $bailleurLabel,
                    'is_ministere'     => $isMinistere,
                    'bailleur_secteur' => $secteur,
                ];
            })->values();
    
            return response()->json($payload);
        }
    
        /** Plafond/déjà pour (projet, bailleur), option financer_id */
        private function getPlafondEtDeja(string $codeProjet, ?int $financerId, string $codeActeur, ?int $ignoreDecaissementId = null): array
        {
            if ($financerId) {
                $plafond = (float) optional(Financer::find($financerId))->montant_finance ?? 0.0;
            } else {
                $plafond = (float) Financer::where('code_projet', $codeProjet)
                    ->whereHas('bailleur', fn($q) => $q->where('code_acteur', $codeActeur))
                    ->sum('montant_finance');
            }
    
            $deja = (float) Decaissement::where('code_projet', $codeProjet)
                ->where('code_acteur', $codeActeur)
                ->when($ignoreDecaissementId, fn($q) => $q->where('id', '!=', $ignoreDecaissementId))
                ->sum('montant');
    
            return ['plafond' => $plafond, 'deja' => $deja];
        }
    
        /** Création */
        public function decaissementsStore(Request $request)
        {
            // Autorisation création par écran
            $ecran = Ecran::find($request->input('ecran_id'));
            if ($ecran && Gate::denies('ajouter_ecran_'.$ecran->id)) {
                return response()->json(['ok'=>false,'message'=>"Vous n'êtes pas autorisé à ajouter."], 403);
            }
    
            try {
                // Si un financement est choisi, enrichir bailleur/devise
                if ($request->filled('financer_id')) {
                    $fin = Financer::with('bailleur')->find($request->input('financer_id'));
                    if ($fin && $fin->bailleur) {
                        $request->merge([
                            'code_acteur' => $request->input('code_acteur') ?: $fin->bailleur->code_acteur,
                            'devise'      => $request->input('devise') ?: $fin->devise,
                        ]);
                    }
                }
    
                $data = $request->validate([
                    'code_projet'       => ['required','string','exists:projets,code_projet'],
                    'financer_id'       => ['nullable','integer','exists:financer,id'],
                    'banqueId'          => ['nullable','integer','exists:banques,id'],
                    'mode_id'           => ['nullable','integer','exists:mode_paiement,id'],
                    'code_acteur'       => ['required_without:financer_id','string','exists:acteur,code_acteur'],
                    'reference'         => ['nullable','string','max:100'],
                    'tranche_no'        => ['nullable','integer','min:1'],
                    'montant'           => ['required','numeric','min:0.01'],
                    'devise'            => ['nullable','string','max:10'],
                    'date_decaissement' => ['required','date'], // <<< requis pour la règle “date > dernière”
                    'commentaire'       => ['nullable','string'],
                ], [
                    'code_acteur.required_without' => "Le bailleur est obligatoire si aucun financement n'est sélectionné.",
                ]);
    
                // Règle métier : date strictement > à la dernière
                $last = $this->getLastDecaissementDate($data['code_projet'], $data['code_acteur']);
                if ($last && Carbon::parse($data['date_decaissement'])->lte($last)) {
                    return response()->json([
                        'ok' => false,
                        'message' => "La date de décaissement doit être strictement postérieure au dernier décaissement (".$last->format('d/m/Y').").",
                    ], 422);
                }
    
                // Plafond / déjà
                $codeProjet = $data['code_projet'];
                $codeActeur = $data['code_acteur'];
                $financerId = $data['financer_id'] ?? null;
                $montantCourant = (float) $data['montant'];
    
                ['plafond' => $plafond, 'deja' => $deja] = $this->getPlafondEtDeja($codeProjet, $financerId, $codeActeur, null);
                if ($plafond > 0 && ($deja + $montantCourant) - $plafond > 1e-9) {
                    $reste = max(0, $plafond - $deja);
                    return response()->json([
                        'ok'=>false,
                        'message'=>"Plafond dépassé : déjà décaissé ".number_format($deja,2,',',' ')." sur "
                            .number_format($plafond,2,',',' ').". Reste autorisé : ".number_format($reste,2,',',' ').".",
                    ], 422);
                }
    
                if (!empty($financerId)) {
                    $fin = Financer::find($financerId);
                    if ($fin && $data['montant'] > (float) $fin->montant_finance) {
                        return response()->json(['ok'=>false,'message'=>'Le montant décaissé dépasse le montant financé.'], 422);
                    }
                }
    
                if (empty($data['tranche_no'])) {
                    $data['tranche_no'] = $this->computeNextTranche($codeProjet, $financerId, $codeActeur);
                }
    
                $dec = Decaissement::create($data + ['created_by' => auth()->id()]);
    
                return response()->json([
                    'ok'=>true,
                    'message'=>'Décaissement créé avec succès.',
                    'data'=>$dec->load(['bailleur'])
                ], 201);
    
            } catch (\Illuminate\Validation\ValidationException $e) {
                return response()->json(['ok'=>false,'message'=>'Veuillez corriger les erreurs.','errors'=>$e->errors()], 422);
            } catch (\Throwable $e) {
                Log::error('Création décaissement - erreur', ['msg'=>$e->getMessage()]);
                return response()->json(['ok'=>false,'message'=>'Erreur lors de la création du décaissement.'], 500);
            }
        }
    
        /** Mise à jour */
        public function decaissementsUpdate(Request $request, $id)
        {
            $ecran = Ecran::find($request->input('ecran_id'));
            if ($ecran && Gate::denies('modifier_ecran_'.$ecran->id)) {
                return response()->json(['ok'=>false,'message'=>"Vous n'êtes pas autorisé à modifier."], 403);
            }
    
            $dec = Decaissement::findOrFail($id);
    
            try {
                if ($request->filled('financer_id')) {
                    $fin = Financer::with('bailleur')->find($request->input('financer_id'));
                    if ($fin && $fin->bailleur) {
                        $request->merge([
                            'code_acteur' => $request->input('code_acteur') ?: $fin->bailleur->code_acteur,
                            'devise'      => $request->input('devise') ?: $fin->devise,
                        ]);
                    }
                }
    
                $data = $request->validate([
                    'code_projet'       => ['required','string','exists:projets,code_projet'],
                    'financer_id'       => ['nullable','integer','exists:financer,id'],
                    'banqueId'          => ['nullable','integer','exists:banques,id'],
                    'mode_id'           => ['nullable','integer','exists:mode_paiement,id'],
                    'code_acteur'       => ['required_without:financer_id','string','exists:acteur,code_acteur'],
                    'reference'         => ['nullable','string','max:100'],
                    'tranche_no'        => ['nullable','integer','min:1'],
                    'montant'           => ['required','numeric','min:0.01'],
                    'devise'            => ['nullable','string','max:10'],
                    'date_decaissement' => ['required','date'],
                    'commentaire'       => ['nullable','string'],
                ], [
                    'code_acteur.required_without' => "Le bailleur est obligatoire si aucun financement n'est sélectionné.",
                ]);
    
                // Règle métier: date > dernière (hors courant)
                $last = $this->getLastDecaissementDate($data['code_projet'], $data['code_acteur'], $dec->id);
                if ($last && Carbon::parse($data['date_decaissement'])->lte($last)) {
                    return response()->json([
                        'ok'=>false,
                        'message'=>"La date de décaissement doit être strictement postérieure au dernier décaissement (".$last->format('d/m/Y').").",
                    ], 422);
                }
    
                $codeProjet     = $data['code_projet'];
                $codeActeur     = $data['code_acteur'];
                $financerId     = $data['financer_id'] ?? null;
                $montantCourant = (float) $data['montant'];
    
                ['plafond' => $plafond, 'deja' => $dejaHors] = $this->getPlafondEtDeja($codeProjet, $financerId, $codeActeur, $dec->id);
                if ($plafond > 0 && ($dejaHors + $montantCourant) - $plafond > 1e-9) {
                    $reste = max(0, $plafond - $dejaHors);
                    return response()->json([
                        'ok'=>false,
                        'message'=>"Plafond dépassé : déjà décaissé ".number_format($dejaHors,2,',',' ')
                            ." sur ".number_format($plafond,2,',',' ')
                            .". Reste autorisé : ".number_format($reste,2,',',' ').".",
                    ], 422);
                }
    
                if (!empty($financerId)) {
                    $fin = Financer::find($financerId);
                    if ($fin && $data['montant'] > (float) $fin->montant_finance) {
                        return response()->json(['ok'=>false,'message'=>'Le montant décaissé dépasse le montant financé.'], 422);
                    }
                }
    
                if (empty($data['tranche_no'])) {
                    $data['tranche_no'] = $this->computeNextTranche($codeProjet, $financerId, $codeActeur);
                }
    
                $dec->update($data);
    
                return response()->json([
                    'ok'=>true,
                    'message'=>'Décaissement mis à jour.',
                    'data'=>$dec->fresh()->load(['bailleur'])
                ]);
    
            } catch (\Illuminate\Validation\ValidationException $e) {
                return response()->json(['ok'=>false,'message'=>'Veuillez corriger les erreurs.','errors'=>$e->errors()], 422);
            } catch (\Throwable $e) {
                Log::error('MAJ décaissement - erreur', ['msg'=>$e->getMessage()]);
                return response()->json(['ok'=>false,'message'=>'Erreur lors de la mise à jour.'], 500);
            }
        }
    
        /** Suppression */
        public function decaissementsDestroy(Request $request, $id)
        {
            $ecran = Ecran::find($request->input('ecran_id'));
            if ($ecran && Gate::denies('supprimer_ecran_'.$ecran->id)) {
                return response()->json(['ok'=>false,'message'=>"Vous n'êtes pas autorisé à supprimer."], 403);
            }
    
            try {
                $dec = Decaissement::findOrFail($id);
                $dec->delete();
                return response()->json(['ok'=>true,'message'=>'Décaissement supprimé.']);
            } catch (\Throwable $e) {
                return response()->json(['ok'=>false,'message'=>'Suppression impossible.'], 500);
            }
        }
    /******************* FIN DECAISSEMENTS ***********************/
    

    

    /******************* ACHAT DE MATERIEL ******************/
        public function achatsIndex(Request $request)
        {
            $pays   = session('pays_selectionne');
            $groupe = session('projet_selectionne');

            Log::info('[GF][Achats] Index', [
                'user_id' => auth()->id(),
                'pays'    => $pays,
                'groupe'  => $groupe,
                'query'   => $request->all()
            ]);

            $projets = Projet::where('code_projet', 'like', $pays.$groupe.'%')
                ->orderBy('code_projet')
                ->get(['code_projet','libelle_projet','code_devise']);

            $fournisseurs = Acteur::where('code_pays', $pays)
                ->where('type_acteur', 'FOU')
                ->orderBy('libelle_long')
                ->get(['code_acteur','libelle_court','libelle_long']);

            $statuts = AchatStatut::orderBy('id')->get();

            $achats = AchatMateriau::with(['projet','fournisseur','statut','lignes'])
                ->whereHas('projet', fn($q)=>$q->where('code_projet','like',$pays.$groupe.'%'))
                ->orderByDesc('date_commande')
                ->paginate(25);

            Log::info('[GF][Achats] Index loaded', [
                'user_id'   => auth()->id(),
                'count'     => $achats->total()
            ]);

            return view('GestionFinanciere.achatsMateriaux', compact('projets','fournisseurs','statuts','achats'));
        }

        /** Création (AJAX) */
        public function achatsStore(Request $request)
        {
            Log::info('[GF][Achats] Store: payload', [
                'user_id' => auth()->id(),
                'input'   => $request->all()
            ]);

            $request->validate([
                'code_projet'                 => ['required','string','exists:projets,code_projet'],
                'code_acteur'                 => ['required','string','exists:acteur,code_acteur'],
                'reference_bc'                => ['nullable','string','max:100'],
                'date_commande'               => ['required','date'],
                'devise'                      => ['nullable','string','max:10'],
                'statut_id'                   => ['required','integer','exists:gf_achat_statuts,id'],
                'commentaire'                 => ['nullable','string'],
                'lignes'                      => ['required','array','min:1'],
                'lignes.*.libelle_materiau'   => ['required','string','max:255'],
                'lignes.*.unite'              => ['nullable','string','max:50'],
                'lignes.*.quantite_prevue'    => ['required','numeric','min:0.0001'],
                'lignes.*.quantite_recue'     => ['nullable','numeric','min:0'],
                'lignes.*.prix_unitaire'      => ['required','numeric','min:0'],
                'lignes.*.tva'                => ['nullable','numeric','min:0','max:100'],
            ]);

            try {
                $achat = AchatMateriau::create([
                    'code_projet'  => $request->code_projet,
                    'code_acteur'  => $request->code_acteur,
                    'reference_bc' => $request->reference_bc,
                    'date_commande'=> $request->date_commande,
                    'devise'       => $request->devise,
                    'statut_id'    => $request->statut_id,
                    'commentaire'  => $request->commentaire,
                    'created_by'   => auth()->id(),
                ]);

                foreach ($request->lignes as $L) {
                    AchatMateriauLigne::create([
                        'achat_id'         => $achat->id,
                        'libelle_materiau' => $L['libelle_materiau'],
                        'unite'            => $L['unite'] ?? null,
                        'quantite_prevue'  => $L['quantite_prevue'],
                        'quantite_recue'   => $L['quantite_recue'] ?? 0,
                        'prix_unitaire'    => $L['prix_unitaire'],
                        'tva'              => $L['tva'] ?? 0,
                    ]);
                }

                Log::info('[GF][Achats] Store: success', [
                    'user_id' => auth()->id(),
                    'achat_id'=> $achat->id,
                    'lignes'  => count($request->lignes)
                ]);

                return response()->json([
                    'ok'       => true,
                    'message'  => 'Achat créé avec succès.',
                    'achat_id' => $achat->id
                ]);
            } catch (\Throwable $e) {
                Log::error('[GF][Achats] Store: error', [
                    'user_id'   => auth()->id(),
                    'exception' => $e->getMessage(),
                    'trace'     => $e->getTraceAsString()
                ]);
                return response()->json([
                    'ok'      => false,
                    'message' => 'Erreur lors de la création.',
                ], 500);
            }
        }

        /** Mise à jour (AJAX) */
        public function achatsUpdate(Request $request, $id)
        {
            Log::info('[GF][Achats] Update: payload', [
                'user_id' => auth()->id(),
                'achat_id'=> $id,
                'input'   => $request->all()
            ]);

            $achat = AchatMateriau::with('lignes')->findOrFail($id);

            $request->validate([
                'code_acteur'                 => ['required','string','exists:acteur,code_acteur'],
                'reference_bc'                => ['nullable','string','max:100'],
                'date_commande'               => ['required','date'],
                'devise'                      => ['nullable','string','max:10'],
                'statut_id'                   => ['required','integer','exists:gf_achat_statuts,id'],
                'commentaire'                 => ['nullable','string'],
                'lignes'                      => ['required','array','min:1'],
                'lignes.*.libelle_materiau'   => ['required','string','max:255'],
                'lignes.*.unite'              => ['nullable','string','max:50'],
                'lignes.*.quantite_prevue'    => ['required','numeric','min:0.0001'],
                'lignes.*.quantite_recue'     => ['nullable','numeric','min:0'],
                'lignes.*.prix_unitaire'      => ['required','numeric','min:0'],
                'lignes.*.tva'                => ['nullable','numeric','min:0','max:100'],
            ]);

            try {
                $achat->update($request->only([
                    'code_acteur','reference_bc','date_commande','devise','statut_id','commentaire'
                ]));

                $achat->lignes()->delete();
                foreach ($request->lignes as $L) {
                    AchatMateriauLigne::create([
                        'achat_id'         => $achat->id,
                        'libelle_materiau' => $L['libelle_materiau'],
                        'unite'            => $L['unite'] ?? null,
                        'quantite_prevue'  => $L['quantite_prevue'],
                        'quantite_recue'   => $L['quantite_recue'] ?? 0,
                        'prix_unitaire'    => $L['prix_unitaire'],
                        'tva'              => $L['tva'] ?? 0,
                    ]);
                }

                Log::info('[GF][Achats] Update: success', [
                    'user_id' => auth()->id(),
                    'achat_id'=> $achat->id,
                    'lignes'  => count($request->lignes)
                ]);

                return response()->json([
                    'ok'       => true,
                    'message'  => 'Achat mis à jour.',
                    'achat_id' => $achat->id
                ]);
            } catch (\Throwable $e) {
                Log::error('[GF][Achats] Update: error', [
                    'user_id'   => auth()->id(),
                    'achat_id'  => $id,
                    'exception' => $e->getMessage(),
                ]);
                return response()->json([
                    'ok'      => false,
                    'message' => 'Erreur lors de la mise à jour.',
                ], 500);
            }
        }

        /** Suppression (AJAX) */
        public function achatsDestroy($id)
        {
            Log::info('[GF][Achats] Destroy: start', ['user_id' => auth()->id(), 'achat_id' => $id]);

            try {
                $achat = AchatMateriau::with('lignes')->findOrFail($id);

                DB::transaction(function () use ($achat) {
                    // Si pas de FK ON DELETE CASCADE, on supprime explicitement les lignes :
                    $achat->lignes()->delete();
                    $achat->delete();
                });

                Log::info('[GF][Achats] Destroy: success', ['user_id' => auth()->id(), 'achat_id' => $id]);

                return response()->json([
                    'ok'      => true,
                    'message' => 'Achat supprimé avec succès.',
                ]);
            } catch (ModelNotFoundException $e) {
                Log::warning('[GF][Achats] Destroy: not found', ['user_id' => auth()->id(), 'achat_id' => $id]);

                return response()->json([
                    'ok'    => false,
                    'error' => 'Achat introuvable.',
                ], 404);
            } catch (\Throwable $e) {
                Log::error('[GF][Achats] Destroy: error', [
                    'user_id'   => auth()->id(),
                    'achat_id'  => $id,
                    'exception' => $e->getMessage(),
                ]);

                return response()->json([
                    'ok'    => false,
                    'error' => 'Impossible de supprimer cet achat.',
                ], 500);
            }
        }
    /******************* FIN ACHAT DE MATERIEL ******************/


    /******************* REGLEMENT ******************/
        private function validateReglement(Request $request, bool $update = false): array
        {
            $rules = [
                'ecran_id'            => ['required','integer','exists:ecrans,id'],
                'code_projet'         => [$update ? 'sometimes' : 'required','string','exists:projets,code_projet'],
                'code_acteur'         => ['required','string','exists:acteur,code_acteur'],
                'reference_facture'   => ['nullable','string','max:100'],
                'date_facture'        => ['nullable','date'],
                'montant_facture'     => ['nullable','numeric','min:0'],
                'montant_regle'       => ['required','numeric','min:0.01'],
                'devise'              => ['nullable','string','max:10'],
                'mode_id'             => ['required','integer','exists:mode_paiement,id'],
                'statut_id'           => ['required','integer','exists:gf_reglement_statuts,id'],
                'date_reglement'      => ['required','date'],
                'tranche_no'          => ['nullable','integer','min:1'],
                'banqueId'            => ['nullable','integer','exists:banques,id'],
                'mode_id'           => ['nullable','integer','exists:mode_paiement,id'],
                // Contexte (mutuellement exclusif)
                'code_travaux_connexe'=> ['nullable','string'],
                'code_renforcement'   => ['nullable','string'],
                'commentaire'         => ['nullable','string'],
            ];

            $data = $request->validate($rules);

            // Règle métier: un seul contexte à la fois (0 ou 1)
            $ctxCount = (!empty($data['code_travaux_connexe']) ? 1 : 0) + (!empty($data['code_renforcement']) ? 1 : 0);
            if ($ctxCount > 1) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'code_travaux_connexe' => ['Choisissez soit Travaux connexes, soit Formation, pas les deux.'],
                    'code_renforcement'    => ['Choisissez soit Formation, soit Travaux connexes, pas les deux.'],
                ]);
            }

            return $data;
        }

        private function computeNextTrancheReglement(string $codeProjet, string $codeActeur, ?string $refFacture = null): int
        {
            $q = ReglementPrestataire::where('code_projet',$codeProjet)
                ->where('code_acteur',$codeActeur);
            if ($refFacture) $q->where('reference_facture',$refFacture);

            $max = (int) $q->max('tranche_no');
            return $max ? $max + 1 : 1;
        }

        public function reglementsStore(Request $request)
        {
            try {
                $data  = $this->validateReglement($request);
                $ecran = Ecran::find($request->input('ecran_id'));

                // Autorisation: création
                if (Gate::denies('ajouter_ecran_'.$ecran->id)) {
                    return response()->json(['ok'=>false,'error'=>"Vous n'êtes pas autorisé à ajouter."], 403);
                }

                // tranche auto si vide
                if (empty($data['tranche_no'])) {
                    $data['tranche_no'] = $this->computeNextTrancheReglement(
                        $data['code_projet'],
                        $data['code_acteur'],
                        $data['reference_facture'] ?? null
                    );
                }

                $reg = ReglementPrestataire::create($data + ['created_by' => auth()->id()]);

                return response()->json(['ok'=>true,'message'=>'Règlement enregistré.','id'=>$reg->id]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                return response()->json(['ok'=>false,'errors'=>$e->errors()], 422);
            } catch (\Throwable $e) {
                Log::error('[GF][Reglements] create error', ['msg'=>$e->getMessage()]);
                return response()->json(['ok'=>false,'error'=>"Erreur lors de l’enregistrement."], 500);
            }
        }

        public function reglementsUpdate(Request $request, $id)
        {
            try {
                $reg   = ReglementPrestataire::findOrFail($id);
                $data  = $this->validateReglement($request, true);
                $ecran = Ecran::find($request->input('ecran_id'));

                // Autorisation: modification
                if (Gate::denies('modifier_ecran_'.$ecran->id)) {
                    return response()->json(['ok'=>false,'error'=>"Vous n'êtes pas autorisé à modifier."], 403);
                }

                if (empty($data['tranche_no'])) {
                    $data['tranche_no'] = $this->computeNextTrancheReglement(
                        $data['code_projet'] ?? $reg->code_projet,
                        $data['code_acteur'],
                        $data['reference_facture'] ?? $reg->reference_facture
                    );
                }

                $reg->update($data);

                return response()->json(['ok'=>true,'message'=>'Règlement mis à jour.','id'=>$reg->id]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                return response()->json(['ok'=>false,'errors'=>$e->errors()], 422);
            } catch (ModelNotFoundException $e) {
                return response()->json(['ok'=>false,'error'=>'Règlement introuvable.'], 404);
            } catch (\Throwable $e) {
                Log::error('[GF][Reglements] update error', ['msg'=>$e->getMessage()]);
                return response()->json(['ok'=>false,'error'=>'Erreur lors de la mise à jour.'], 500);
            }
        }

        public function contextByProjet(string $codeProjet)
        {
            $tc = TravauxConnexes::where('code_projet',$codeProjet)
                ->orderBy('date_debut_previsionnelle','desc')
                ->get(['codeActivite as code','code_projet','commentaire'])
                ->map(fn($t)=>[
                    'code'=>$t->code,
                    'libelle'=>$t->commentaire ?: 'Travaux connexes',
                ]);

            $rf = Renforcement::whereHas('projets', fn($q)=>$q->where('code_projet',$codeProjet))
                ->orderBy('date_debut','desc')
                ->get(['code_renforcement as code','titre']);

            return response()->json([
                'travaux'       => $tc,
                'renforcements' => $rf->map(fn($r)=>['code'=>$r->code,'titre'=>$r->titre]),
            ]);
        }

        public function reglementsIndex(Request $request)
        {
            $ecran = Ecran::find($request->input('ecran_id')); // utilisé par les @can et la vue

            // Autorisation: consultation (optionnel si protégé ailleurs)
            if ($ecran && Gate::denies('consulter_ecran_'.$ecran->id)) {
                abort(403);
            }

            $pays   = session('pays_selectionne');
            $groupe = session('projet_selectionne');

            $projets = Projet::where('code_projet','like',$pays.$groupe.'%')
                ->orderBy('code_projet')
                ->get(['code_projet','libelle_projet','code_devise']);

            $prestataires = Acteur::where('code_pays', $pays)
                ->orderBy('libelle_long')
                ->get(['code_acteur','libelle_court','libelle_long']);

            $modes    = ModePaiement::orderBy('id')->get();
            $statuts  = ReglementStatut::orderBy('id')->get();
            $banques  = Banque::where('actif', true)->orderBy('sigle')->get(['id','sigle','nom','code_pays','est_internationale']);

            $reglements = ReglementPrestataire::with(['projet','prestataire','mode','statut','banque'])
                ->whereHas('projet', fn($q)=>$q->where('code_projet','like',$pays.$groupe.'%'))
                ->orderByDesc('date_reglement')
                ->paginate(25);

            return view('GestionFinanciere.reglementsPrestataires', compact(
                'ecran','projets','prestataires','modes','statuts','banques','reglements'
            ));
        }

        public function reglementsDestroy(Request $request, $id)
        {
            $ecran = Ecran::find($request->input('ecran_id'));

            if ($ecran && Gate::denies('supprimer_ecran_'.$ecran->id)) {
                return response()->json(['ok'=>false,'error'=>"Vous n'êtes pas autorisé à supprimer."], 403);
            }

            Log::info('[GF][Reglements] Destroy: start', ['user_id' => auth()->id(), 'id' => $id]);

            try {
                $r = ReglementPrestataire::findOrFail($id);
                $r->delete();

                Log::info('[GF][Reglements] Destroy: success', ['user_id' => auth()->id(), 'id' => $id]);

                return response()->json(['ok' => true,'message' => 'Règlement supprimé.']);
            } catch (ModelNotFoundException $e) {
                return response()->json(['ok' => false, 'error' => 'Règlement introuvable.'], 404);
            } catch (\Throwable $e) {
                Log::error('[GF][Reglements] Destroy: error', ['id' => $id, 'exception' => $e->getMessage()]);
                return response()->json(['ok' => false, 'error' => 'Suppression impossible.'], 500);
            }
        }
    /******************* FIN REGLEMENT ******************/
   
    public function representationGraphique(Request $request)
    {
        $annee  = (int)($request->input('annee') ?? date('Y'));
        $alpha3 = session('pays_selectionne');     // ex: 'CIV'
        $groupe = session('projet_selectionne');   // ex: 'BTP' ou '01'
    
        // Pour comparer les dates "≤ fin d'année"
        $finAnnee = sprintf('%d-12-31', $annee);
    
        // Récupérer l'ID du pays pour la table PIB (code_pays pointe vers pays.id)
        $paysId = DB::table('pays')->where('alpha3', $alpha3)->value('id');
        $pibAnnee = $paysId
            ? (float) (Pib::where('code_pays', $paysId)->where('annee', $annee)->value('montant_pib') ?? 0)
            : 0.0;
    
        // Helper de jointure (projet -> sous_domaine -> domaine_intervention -> groupe_projet)
        $joinProjetChain = function ($q, string $pAlias = 'p') {
            $q->join('sous_domaine as sd', 'sd.code_sous_domaine', '=', $pAlias.'.code_sous_domaine')
              ->join('domaine_intervention as di', 'di.code', '=', 'sd.code_domaine')
              ->join('groupe_projet as gp', 'gp.code', '=', 'di.groupe_projet_code');
            return $q;
        };
    
        /* =========================
           1) "PIB proxy" par domaine
           ========================= */
        $qPib = DB::table('projets as p');
        $joinProjetChain($qPib, 'p');
    
        $pibRows = $qPib
            ->select('di.libelle as secteur', DB::raw('SUM(p.cout_projet) as total'))
            ->when($alpha3, fn($q) => $q->where('p.code_alpha3_pays', $alpha3))
            ->when($groupe, fn($q)  => $q->where('gp.code', $groupe))
            ->whereDate('p.date_demarrage_prevue', '<=', $finAnnee)
            ->groupBy('di.libelle')
            ->orderByDesc('total')
            ->get();
    
        $labels   = $pibRows->pluck('secteur')->values();
        $montants = $pibRows->pluck('total')->map(fn($v)=>(float)$v)->values();
        $parts    = $pibRows->map(fn($r) => $pibAnnee > 0 ? round(((float)$r->total / $pibAnnee) * 100, 2) : null)->values();
    
        $pibParSecteur = [
            'annee'    => $annee,
            'labels'   => $labels,
            'montants' => $montants,
            'data'     => $montants,    // alias pour compat avec certaines vues
            'parts'    => $parts,
            'pib'      => $pibAnnee > 0 ? $pibAnnee : null,
        ];
    
        /* ===========================================
           2) Décaissements cumulés par mois (année N)
           =========================================== */
        $qDecM = DB::table('gf_decaissements as dec')
            ->join('projets as p', 'p.code_projet', '=', 'dec.code_projet');
        $joinProjetChain($qDecM, 'p');
    
        $decM = $qDecM
            ->selectRaw('MONTH(dec.date_decaissement) as m, SUM(dec.montant) as total')
            ->whereYear('dec.date_decaissement', $annee)
            ->when($alpha3, fn($q) => $q->where('p.code_alpha3_pays', $alpha3))
            ->when($groupe, fn($q)  => $q->where('gp.code', $groupe))
            ->groupBy('m')
            ->orderBy('m')
            ->pluck('total', 'm');
    
        $decaissementsMensuels = [];
        for ($m = 1; $m <= 12; $m++) {
            $decaissementsMensuels[] = (float)($decM[$m] ?? 0);
        }
    
        /* ========================================
           3) Dépenses par nature (Achats/Règlements)
           ======================================== */
    
        // Achats (header + lignes)
        $qAch = DB::table('gf_achat_lignes as l')
            ->join('gf_achats as a', 'a.id', '=', 'l.achat_id')
            ->join('projets as p', 'p.code_projet', '=', 'a.code_projet')
            ->join('sous_domaine as sd', 'sd.code_sous_domaine', '=', 'p.code_sous_domaine')
            ->join('domaine_intervention as di', 'di.code', '=', 'sd.code_domaine')
            ->join('groupe_projet as gp', 'gp.code', '=', 'di.groupe_projet_code')
            ->whereYear('a.date_commande', $annee)
            ->when($alpha3, fn($q) => $q->where('p.code_alpha3_pays', $alpha3))
            ->when($groupe, fn($q)  => $q->where('gp.code', $groupe));
    
        // expression de valorisation (sans gf_materiaux)
        $exprAchats = 'COALESCE(l.quantite_recue, l.quantite_receptionnee, l.quantite_prevue, 0)
                       * COALESCE(l.cout_unitaire, l.prix_unitaire, 0)';
    
        $achatsTotal = (float) $qAch->sum(DB::raw($exprAchats));
    
        // Règlements prestataires
        $qReg = DB::table('gf_reglements as r')
            ->join('projets as p', 'p.code_projet', '=', 'r.code_projet');
        $joinProjetChain($qReg, 'p');
    
        $reglementsTotal = (float) $qReg
            ->whereYear('r.date_reglement', $annee)
            ->when($alpha3, fn($q) => $q->where('p.code_alpha3_pays', $alpha3))
            ->when($groupe, fn($q)  => $q->where('gp.code', $groupe))
            ->sum('r.montant_regle');
    
        $depensesParNature = [
            'labels' => ['Achats de matériaux', 'Règlements prestataires'],
            'data'   => [$achatsTotal, $reglementsTotal],
        ];
    
        /* ==================================
           4) Top 5 bailleurs par décaissement
           ================================== */
        $qTop = DB::table('gf_decaissements as dec')
            ->join('projets as p', 'p.code_projet', '=', 'dec.code_projet');
        $joinProjetChain($qTop, 'p');
    
        $topRows = $qTop
            ->join('acteur as a', 'a.code_acteur', '=', 'dec.code_acteur')
            ->selectRaw('a.code_acteur, a.libelle_court, a.libelle_long, SUM(dec.montant) as total')
            ->whereYear('dec.date_decaissement', $annee)
            ->when($alpha3, fn($q) => $q->where('p.code_alpha3_pays', $alpha3))
            ->when($groupe, fn($q)  => $q->where('gp.code', $groupe))
            ->groupBy('a.code_acteur','a.libelle_court','a.libelle_long')
            ->orderByDesc('total')
            ->limit(5)
            ->get();
    
        $topBailleurs = [
            'labels' => $topRows->map(fn($r)=> trim(($r->libelle_court ?? '').' '.($r->libelle_long ?? '')) ?: $r->code_acteur)->values(),
            'data'   => $topRows->pluck('total')->map(fn($v)=>(float)$v)->values(),
        ];
    
        /* ========= Extras (vue “plus”) ========= */
    
        // Décaissements par devise
        $qDev = DB::table('gf_decaissements as d')
            ->join('projets as p', 'p.code_projet', '=', 'd.code_projet');
        $joinProjetChain($qDev, 'p');
    
        $byDevise = $qDev
            ->selectRaw('COALESCE(d.devise,"N/A") as devise, SUM(d.montant) as total')
            ->whereYear('d.date_decaissement', $annee)
            ->when($alpha3, fn($q) => $q->where('p.code_alpha3_pays', $alpha3))
            ->when($groupe, fn($q)  => $q->where('gp.code', $groupe))
            ->groupBy('devise')
            ->orderByDesc('total')
            ->get();
    
        $decaisseParDevise = [
            'labels' => $byDevise->pluck('devise')->values(),
            'data'   => $byDevise->pluck('total')->map(fn($v)=>(float)$v)->values(),
        ];
    
        // Décaissements par secteur
        $qSect = DB::table('gf_decaissements as dec')
            ->join('projets as p', 'p.code_projet', '=', 'dec.code_projet');
        $joinProjetChain($qSect, 'p');
    
        $bySect = $qSect
            ->selectRaw('di.libelle as secteur, SUM(dec.montant) as total')
            ->whereYear('dec.date_decaissement', $annee)
            ->when($alpha3, fn($q) => $q->where('p.code_alpha3_pays', $alpha3))
            ->when($groupe, fn($q)  => $q->where('gp.code', $groupe))
            ->groupBy('di.libelle')
            ->orderByDesc('total')
            ->get();
    
        $decaisseParSecteur = [
            'labels' => $bySect->pluck('secteur')->values(),
            'data'   => $bySect->pluck('total')->map(fn($v)=>(float)$v)->values(),
        ];
    
        // Délai moyen de règlement (jours) par mois
        $qDelai = DB::table('gf_reglements as r')
            ->join('projets as p', 'p.code_projet', '=', 'r.code_projet');
        $joinProjetChain($qDelai, 'p');
    
        $delaiRows = $qDelai
            ->whereYear('r.date_reglement', $annee)
            ->whereNotNull('r.date_facture')
            ->when($alpha3, fn($q) => $q->where('p.code_alpha3_pays', $alpha3))
            ->when($groupe, fn($q)  => $q->where('gp.code', $groupe))
            ->selectRaw('MONTH(r.date_reglement) as m, AVG(DATEDIFF(r.date_reglement, r.date_facture)) as avg_delay')
            ->groupBy('m')->orderBy('m')->pluck('avg_delay','m');
    
        $delaiMoyenParMois = [];
        for ($m=1; $m<=12; $m++) {
            $delaiMoyenParMois[] = isset($delaiRows[$m]) ? round((float)$delaiRows[$m], 1) : null;
        }
    
        // Achats mensuels
        $achatsMensuels = array_fill(0, 12, 0.0);
        if (Schema::hasTable('gf_achats')) {
            $qAm = DB::table('gf_achat_lignes as l')
                ->join('gf_achats as a', 'a.id', '=', 'l.achat_id')
                ->join('projets as p', 'p.code_projet', '=', 'a.code_projet');
            $joinProjetChain($qAm, 'p');
    
            $expr = 'COALESCE(l.quantite_recue, l.quantite_receptionnee, l.quantite_prevue, 0)
                     * COALESCE(l.cout_unitaire, l.prix_unitaire, 0)';
    
            $rowsAm = $qAm->whereYear('a.date_commande', $annee)
                ->when($alpha3, fn($q) => $q->where('p.code_alpha3_pays', $alpha3))
                ->when($groupe, fn($q)  => $q->where('gp.code', $groupe))
                ->selectRaw("MONTH(a.date_commande) as m, SUM($expr) as total")
                ->groupBy('m')->orderBy('m')->pluck('total','m');
    
            for ($m=1; $m<=12; $m++) {
                $achatsMensuels[$m-1] = (float)($rowsAm[$m] ?? 0);
            }
        }
    
        /* ================= KPIs ================= */
        $qBudget = DB::table('projets as p');
        $joinProjetChain($qBudget, 'p');
    
        $budgetTotal = (float) $qBudget
            ->when($alpha3, fn($q) => $q->where('p.code_alpha3_pays', $alpha3))
            ->when($groupe, fn($q)  => $q->where('gp.code', $groupe))
            ->whereDate('p.date_demarrage_prevue', '<=', $finAnnee)
            ->sum('p.cout_projet');
    
        $decaissementsTotal = array_sum($decaissementsMensuels);
    
        return view('GestionFinanciere.representationGraphique', compact(
            'annee',
            'pibParSecteur',
            'decaissementsMensuels',
            'depensesParNature',
            'topBailleurs',
            'budgetTotal',
            'decaissementsTotal',
            'achatsMensuels',
            'delaiMoyenParMois',
            'decaisseParDevise',
            'decaisseParSecteur'
        ));
    }

    /******************* PIB ********************** */
      /** Page unifiée PIB (séries + gestion + graphe par secteur) */
      public function pibIndex(Request $request)
      {
        $ecran = Ecran::find($request->input('ecran_id'));
        if (!$ecran) {
            return redirect()->back()->with('error', 'Écran non trouvé.');
        }
          $annee = (int) ($request->input('annee') ?? date('Y'));
  
          // Pays courant depuis la session
          $alpha3 = session('pays_selectionne');
          $groupe = session('projet_selectionne');
          $pays   = Pays::where('alpha3', $alpha3)->first();
          $groupeProjet = GroupeProjet::where('code', $groupe)->first();
          
          // Liste PIB pour ce pays
          $pibs = Pib::when($pays, fn($q) => $q->where('code_pays', $pays->id))
                      ->orderBy('annee')
                      ->get();
  
          // Devises (adapte si besoin)
          $devises = Devise::orderBy('code_long')->get();
  
          return view('GestionFinanciere.pib', compact('ecran','annee','groupeProjet', 'pays', 'pibs', 'devises'));
      }
  
      /** Données JSON pour le graphe “PIB par secteur” */
      public function pibParSecteurData(Request $request)
      {
          $annee    = (int) ($request->input('annee') ?? date('Y'));
          $codePays = session('pays_selectionne');    // ex: 'CIV'
          $groupe   = session('projet_selectionne');  // ex: 'BTP'
  
          // 1) valeur du PIB (si saisie)
          $pibAnnee = Pib::where('code_pays', function($q) use ($codePays){
                              $q->select('id')->from('pays')->where('alpha3', $codePays)->limit(1);
                          })
                          ->where('annee', $annee)
                          ->value('montant_pib');
  
          // 2) agrégat des montants par secteur
          $rows = DB::table('projets as p')
              ->join('sous_domaine as sd', 'sd.code_sous_domaine', '=', 'p.code_sous_domaine')
              ->join('domaine_intervention as d', 'd.code', '=', 'sd.code_domaine')
              ->join('groupe_projet as g', 'g.code', '=', 'd.groupe_projet_code')
              ->select('d.libelle as secteur', DB::raw('SUM(p.cout_projet) as total'))
              ->when($codePays, fn($q) => $q->where('p.code_alpha3_pays', $codePays))
              ->where('g.code', $groupe)
              ->whereYear('p.date_demarrage_prevue', '<=', $annee)
              ->groupBy('d.libelle')
              ->orderByDesc('total')
              ->get();
  
          $labels   = $rows->pluck('secteur')->values();
          $montants = $rows->pluck('total')->map(fn($v) => (float) $v)->values();
          $parts    = $rows->map(fn($r) => $pibAnnee ? round(($r->total / $pibAnnee) * 100, 2) : null)->values();
  
          $warn = $pibAnnee ? null : "Aucun PIB saisi pour {$codePays} en {$annee}. Les pourcentages sont indisponibles.";
  
          return response()->json([
              'ok'       => true,
              'annee'    => $annee,
              'pays'     => $codePays,
              'pib'      => $pibAnnee ? (float) $pibAnnee : null,
              'labels'   => $labels,
              'montants' => $montants,
              'parts'    => $parts,
              'table'    => $rows->map(fn($r) => [
                                  'secteur' => $r->secteur,
                                  'montant' => (float) $r->total,
                                  'part'    => $pibAnnee ? round($r->total / $pibAnnee * 100, 2) : null,
                             ])->values(),
              'warn'     => $warn,
          ]);
      }
  
      /** STORE PIB */
      public function storePIB(Request $request)
      {
          $request->validate([
              'annee'  => 'required|numeric|min:1900|max:2100',
              'montant'=> 'required|numeric|min:0',
              'devise' => 'required|string|max:10',
          ]);
  
          $alpha3 = session('pays_selectionne');
          $pays   = Pays::where('alpha3', $alpha3)->first();
  
          if (!$pays) {
              return back()->with('error', 'Pays sélectionné invalide.');
          }
  
          Pib::create([
              'code_pays'   => $pays->id,
              'annee'       => (int) $request->annee,
              'montant_pib' => (float) $request->montant,
          ]);
  
          return back()->with('success', 'PIB ajouté avec succès.');
      }
  
      /** UPDATE PIB */
      public function updatePIB(Request $request, $id)
      {
          $request->validate([
              'annee'  => 'required|numeric|min:1900|max:2100',
              'montant'=> 'required|numeric|min:0',
              'devise' => 'required|string|max:10',
          ]);
  
          $pib = Pib::where('code', $id)->firstOrFail();
          $pib->update([
              'annee'       => (int) $request->annee,
              'montant_pib' => (float) $request->montant,
          ]);
  
          return back()->with('success', 'PIB mis à jour avec succès.');
      }
  
      /** DELETE PIB */
      public function destroyPIB($id)
      {
          $pib = Pib::where('code', $id)->firstOrFail();
          $pib->delete();
  
          return back()->with('success', 'PIB supprimé avec succès.');
      }
    /******************* FIN PIB ********************** */
 
}
