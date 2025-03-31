<?php

namespace App\Http\Controllers;

use App\Models\Acteur;
use App\Models\ActionMener;
use App\Models\Approbateur;
use App\Models\Bailleur;
use App\Models\DecoupageAdministratif;
use App\Models\DecoupageAdminPays;
use App\Models\Devise;
use App\Models\Domaine;
use App\Models\Ecran;
use App\Models\Entreprise;
use App\Models\EntrepriseParticulier;
use App\Models\EtudeProject;
use App\Models\EtudeProjectFile;
use App\Models\FormeJuridique;
use App\Models\Genre;
use App\Models\GroupeProjet;
use App\Models\GroupeProjetPaysUser;
use App\Models\GroupeUtilisateur;
use App\Models\LocalitesPays;
use App\Models\Ministere;
use App\Models\MotDePasseUtilisateur;
use App\Models\NatureTravaux;
use App\Models\Particulier;
use App\Models\Pays;
use App\Models\Personnel;
use App\Models\Pieceidentite;
use App\Models\Possederpiece;
use App\Models\ProjectApproval;
use App\Models\Projet;
use App\Models\ProjetEha2;
use App\Models\Renforcement;
use App\Models\SecteurActivite;
use App\Models\SituationMatrimonial;
use App\Models\SousDomaine;
use App\Models\Task;
use App\Models\TravauxConnexes;
use App\Models\TypeTravauxConnexes;
use App\Models\User;
use App\Models\Validations;
use App\Models\FamilleInfrastructure;
use App\Models\TypeCaracteristique;
use App\Models\Caracteristique; 
use App\Models\Unite;
use App\Models\ProjetLocalisation;
use App\Models\ProjetInfrastructure;
use App\Models\ValeurCaracteristique;
use App\Models\Infrastructure;
use App\Models\ProjetActionAMener;
use App\Models\Jouir;
use App\Models\Profiter;
use App\Models\Beneficier;
use App\Models\Executer;
use App\Models\Financer;
use App\Models\Posseder;
use App\Models\ProjetDocument;
use App\Models\projets_natureTravaux;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Str;

class EtudeProjet extends Controller
{
        //////////////////////////////////ETUDE DE PROJET///////////////////////////////////
        public function createNaissance(Request $request)
        {
            // Générer le code par défaut pour Public (1)
            $generatedCodeProjet = $this->generateProjectCode('CI', 'EHA', 1); // 1 pour Public
            $paysSelectionne = session('pays_selectionne');
            $groupeSelectionne = session('projet_selectionne');
            $user = auth()->user();
            $groupe = GroupeUtilisateur::where('code', $user->groupe_utilisateur_id)->first();
            $ecran = Ecran::find($request->input('ecran_id'));
            $natures = NatureTravaux::all();
            $GroupeProjets = GroupeProjet::all();
            $Domaines = Domaine::where('groupe_projet_code', $groupeSelectionne)->get();
            $SousDomaines = SousDomaine::all();
            $SecteurActivites = SecteurActivite::all();
            $localite = LocalitesPays::all();
            $Pays = GroupeProjetPaysUser::with('pays')
            ->select('pays_code') // Sélectionne uniquement le code pays
            ->distinct() // Évite les doublons
            ->where('pays_code', $paysSelectionne)
            ->get()
            ->pluck('pays.nom_fr_fr', 'pays.alpha3') // Associe alpha3 avec le nom
            ->sort();
            $deviseCouts = Devise::where('libelle', '!=', 'neutre')
            ->whereNotNull('libelle')
            ->where('libelle', '!=', '')
            ->get();
            $actionMener = ActionMener::all();

            $tousPays = Pays::whereNotIn('id',  [0, 300, 301, 302, 303, 304])->get();
            $DecoupageAdminPays = DecoupageAdminPays::all();
            $Niveau = DecoupageAdministratif::all();
            $formeJuridiques = FormeJuridique::all();
            $genres = Genre::all();
            $NaturesTravaux = NatureTravaux::all();
            $SituationMatrimoniales = SituationMatrimonial::all();

            $acteurRepres = DB::table('acteur as a')
            ->join('personne_physique as pp', 'pp.code_acteur', '=', 'a.code_acteur')
            ->select('a.code_acteur', 'a.libelle_long', 'a.libelle_court', 'pp.telephone_mobile', 'pp.telephone_bureau', 'pp.email')
            ->where('a.type_acteur', 'etp')
            ->where('a.code_pays', $paysSelectionne)
            ->get();

            $devises = Pays::where('alpha3', $paysSelectionne)->first()->code_devise;
            $Pieceidentite = Pieceidentite::all();
            $TypeCaracteristiques = TypeCaracteristique::all();
            $infrastructures = Infrastructure::all();
            $acteurs = Acteur::where('type_acteur', '=', 'etp')
            ->where('code_pays', $paysSelectionne)
            ->get();
            
            $codes = ['NEU', 'ARB', 'AFQ', 'ONU', 'ZAF'];

            $bailleurActeurs = Acteur::whereIn('code_pays', ['NEU', 'ARB', 'AFQ', 'ONU', 'ZAF', $paysSelectionne])->get();

            $Devises = Pays::where('alpha3', $paysSelectionne)->get();
            return view('etudes_projets.naissance', compact('Devises', 'bailleurActeurs', 'infrastructures', 'acteurs','TypeCaracteristiques','deviseCouts','acteurRepres','Pieceidentite','NaturesTravaux', 'formeJuridiques','SituationMatrimoniales','genres', 'SecteurActivites', 'Pays','SousDomaines','Domaines','GroupeProjets','ecran','generatedCodeProjet','natures','groupeSelectionne', 'tousPays', 'devises','actionMener'));
        }
        public function search(Request $request)
        {
            $query = $request->input('search');

            if (!$query) {
                return response()->json([]);
            }

            // Recherche des bailleurs en filtrant sur `libelle_long` ou `libelle_court`
            $bailleurs = Acteur::where(function($q) use ($query) {
                                $q->where('libelle_long', 'LIKE', "%{$query}%")
                                  ->orWhere('libelle_court', 'LIKE', "%{$query}%");
                            })
                            ->where('is_active', true) // Ajouter une condition pour les actifs
                            ->limit(10)
                            ->get(['code_acteur', 'libelle_long', 'libelle_court']); // Sélection des colonnes nécessaires

            return response()->json($bailleurs);
        }
        // Récupérer les localités associées à un pays donné
        public function getLocalites($paysCode)
        {

            $localites = LocalitesPays::where('id_pays', $paysCode)
            ->orderBy('libelle', 'asc')
            ->get(['id', 'libelle', 'code_rattachement', 'id_pays']);
            return response()->json($localites);
        }

        // Récupérer le niveau et découpage associés à une localité sélectionnée
        public function getDecoupageNiveau($localiteId)
        {
            // Récupération de la localité (un seul objet, pas une collection)
            $localite = LocalitesPays::find($localiteId); // ou ->where('id', $localiteId)->first();
        
            if (!$localite) {
                return response()->json(['message' => 'Localité non trouvée'], 404);
            }
            
            // Récupération du découpage administratif du pays
            $niveau = DecoupageAdminPays::where('code_decoupage', $localite->code_decoupage)
                                        ->first();
            // Récupération du libellé de découpage
            $libelle = null;
            if ($niveau) {
                $decoupage = DecoupageAdministratif::where('code_decoupage', $localite->code_decoupage)->first();
                $libelle = $decoupage ? $decoupage->libelle_decoupage : null;
            }
        
            return response()->json([
                'niveau' => $niveau ? $niveau->num_niveau_decoupage : 'Non défini',
                'code_decoupage' => $localite->code_decoupage,
                'libelle_decoupage' => $libelle ?? 'Non défini'
            ]);
        }
        
        public function getFamilles($code_sous_domaine)
        {
            $familles = FamilleInfrastructure::where('code_sdomaine', $code_sous_domaine)->get();

            return response()->json($familles);
        }

        public function getActeurs(Request $request)
        {
            // Vérification du type de requête : Maître d’Ouvrage ou Maître d’Œuvre
            $type_mo = $request->input('type_mo'); // Public ou Privé (Maître d'Ouvrage)
            $priveType = $request->input('priveType'); // Entreprise ou Individu (Maître d'Ouvrage)

            $type_ouvrage = $request->input('type_ouvrage'); // Public ou Privé (Maître d'Œuvre)
            $priveMoeType = $request->input('priveMoeType'); // Entreprise ou Individu (Maître d'Œuvre)

            // Initialisation d'une collection vide
            $acteurs = collect();
            $paysSelectionne = session('pays_selectionne');

            // Vérification si le pays est bien défini
            $pays = Pays::where('alpha3', $paysSelectionne)->first();
            $code_pays = $pays ? $pays->id : null;
            
            if ($code_pays) {
                if (!empty($type_ouvrage)) {
                    // 🔹 Logique pour le Maître d'Œuvre
                    if ($type_ouvrage === 'Public') {
                        $acteurs = Acteur::whereIn('code_pays', [$paysSelectionne, 'NEU'])
                            ->whereIn('type_acteur', ['eta', 'clt'])
                            ->get();
                    } elseif ($type_ouvrage === 'Privé' && $priveMoeType === 'Entreprise') {
                        $acteurs = Acteur::whereIn('code_pays', [$paysSelectionne])
                            ->whereIn('type_acteur', ['ogi', 'fat', 'sa', 'sar', 'sup', 'op'])
                            ->get();
                    } elseif ($type_ouvrage === 'Privé' && $priveMoeType === 'Individu') {
                        $acteurs = Acteur::whereIn('code_pays', [$paysSelectionne])
                            ->where('type_acteur', 'etp')
                            ->get();
                    }
                } elseif (!empty($type_mo)) {
                    // 🔹 Logique pour le Maître d'Ouvrage
                    if ($type_mo === 'Public') {
                        $acteurs = Acteur::whereIn('code_pays', [$paysSelectionne, 'NEU'])
                            ->whereIn('type_acteur', ['eta', 'clt'])
                            ->get();
                    } elseif ($type_mo === 'Privé' && $priveType === 'Entreprise') {
                        $acteurs = Acteur::whereIn('code_pays', [$paysSelectionne])
                            ->whereIn('type_acteur', ['ogi', 'fat', 'sa', 'sar', 'sup', 'op'])
                            ->get();
                    } elseif ($type_mo === 'Privé' && $priveType === 'Individu') {
                        $acteurs = Acteur::whereIn('code_pays', [$paysSelectionne])
                            ->where('type_acteur', 'etp')
                            ->get();
                    }
                }
            }

            // Transformation des résultats
            $acteurs = $acteurs->map(function ($acteur) {
                return [
                    'code_acteur' => $acteur->code_acteur,
                    'libelle_long' => trim(($acteur->libelle_court ?? '') . ' ' . ($acteur->libelle_long ?? '')),
                ];
            });

            return response()->json($acteurs);
        }

        public function getNiveauxAdministratifs($alpha3)
        {
            $pays = Pays::where('alpha3', $alpha3)->first();

            if ($pays) {
                $niveaux = DecoupageAdminPays::where('id_pays', $pays->id)
                    ->join('decoupage_administratif', 'decoupage_admin_pays.code_decoupage', '=', 'decoupage_administratif.code_decoupage')
                    ->select('decoupage_administratif.libelle_decoupage', 'decoupage_admin_pays.num_niveau_decoupage')
                    ->orderBy('num_niveau_decoupage')
                    ->get();

                return response()->json($niveaux);
            }

            return response()->json([], 404);
        }


        public function getLocalitesByNiveau($alpha3, $niveau, Request $request)
        {
            $pays = Pays::where('alpha3', $alpha3)->first();

            if ($pays) {
                $query = LocalitesPays::where('id_pays', $pays->alpha3)
                    ->where('id_niveau', $niveau);

                // Filtrer selon `code_rattachement` si disponible
                if ($request->has('code_rattachement')) {
                    $query->where('code_rattachement', 'LIKE', $request->code_rattachement . '%');
                }

                $localites = $query->get(['id', 'libelle', 'code_rattachement']);

                return response()->json($localites);
            }

            return response()->json([], 404);
        }

        public function getCaracteristiques($idType)
        {
            $caracteristiques = Caracteristique::where('idTypeCaracteristique', $idType)->get();

            return response()->json($caracteristiques);
        }

        public function getUnites($idCaracteristique)
        {
            $unites = Unite::where('idCaracteristique', $idCaracteristique)->get();

            return response()->json($unites);
        }

        private function genererCodeProjet($codeSousDomaine, $typeFinancement, $codeLocalisation, $dateDebut)
        {
            $paysAlpha3 = session('pays_selectionne');        // ex: CIV
            $groupeProjet = session('projet_selectionne');    // ex: BAT

            $date = Carbon::parse($dateDebut);
            $annee = $date->format('Y');

            // Extraire les 2 premiers caractères du code sous-domaine pour déterminer le domaine
            $codeDomaine = strtoupper(substr($codeSousDomaine, 0, 2));

            // Compter les projets déjà enregistrés avec la même configuration
            $ordre = Projet::where('code_alpha3_pays', $paysAlpha3)
                ->where('code_sous_domaine', 'like', $codeDomaine . '%')
                ->whereYear('date_demarrage_prevue', $annee)
                ->whereMonth('date_demarrage_prevue', $mois)
                ->count() + 1;

            return strtoupper("{$paysAlpha3}{$groupeProjet}{$typeFinancement}_{$codeLocalisation}_{$codeDomaine}_{$annee}_{$ordre}");
        }

        private function genererCodeEtude($codePays, $codeGroupeProjet)
        {
            $now = Carbon::now();
            $annee = $now->format('Y');
            $mois = $now->format('m');

            // Compte les études existantes pour ce mois/pays/groupe
            $ordre = EtudeProject::where('codeEtudeProjets', 'like', "{$codePays}_{$codeGroupeProjet}_{$annee}_{$mois}_%")->count() + 1;

            return strtoupper("{$codePays}_{$codeGroupeProjet}_{$annee}_{$mois}_{$ordre}");
        }

        public function abortProjet(Request $request)
        {
            $request->validate([
                'code_projet' => 'required|string|exists:projets,code_projet',
            ]);

            $code = $request->code_projet;

            $tables = [
                'projets_natureTravaux' => 'code_projet',
                'projetinfrastructure' => 'code_projet',
                'projet_action_a_mener' => 'code_projet',
                'executer' => 'code_projet',
                'posseder' => 'code_projet',
                'financer' => 'code_projet',
                'profiter' => 'code_projet',
                'beneficier' => 'code_projet',
                'jouir' => 'code_projet',
                'projet_documents' => 'code_projet',
                'etudeprojects' => 'code_projet',
            ];

            DB::beginTransaction();
            try {
                foreach ($tables as $table => $key) {
                    DB::table($table)->where($key, $code)->delete();
                }

                // Supprimer projet principal
                Projet::where('code_projet', $code)->delete();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => "Le projet temporaire a été annulé et supprimé.",
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error("Échec du rollback : " . $e->getMessage());

                return response()->json([
                    'success' => false,
                    'message' => "Échec de l'annulation du projet.",
                ], 500);
            }
        }

        public function saveStep1(Request $request)
        {
            $request->validate([
                'libelle_projet' => 'required|string|max:255',
                'code_sous_domaine' => 'required|string|max:10',
                'date_demarrage_prevue' => 'required|date',
                'date_fin_prevue' => 'required|date|after_or_equal:date_demarrage_prevue',
                'cout_projet' => 'nullable|numeric',
                'code_devise' => 'nullable|string|max:3',
                'code_nature' => 'required|string|max:10',
                'code_pays' => 'required|string|max:3',
            ]);

            try {
                // Générer un ID temporaire unique
                $tempId = 'TEMP-' . Str::uuid();

                if (empty($tempId)) {
                    throw new \Exception("Failed to generate project code");
                }

                $projet = Projet::create([
                    'code_projet' => $tempId,
                    'libelle_projet' => $request->libelle_projet,
                    'commentaire' => $request->commentaire,
                    'code_sous_domaine' => $request->code_sous_domaine,
                    'date_demarrage_prevue' => $request->date_demarrage_prevue,
                    'date_fin_prevue' => $request->date_fin_prevue,
                    'cout_projet' => $request->cout_projet,
                    'code_devise' => $request->code_devise,
                    'code_alpha3_pays' => $request->code_pays ?? session('pays_selectionne'),
                ]);

                projets_natureTravaux::create([
                    'code_projet' => $tempId,
                    'code_nature' => $request->code_nature,
                    'date' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'code_projet' => $tempId
                ]);

            } catch (\Exception $e) {
                \Log::error('Project creation failed: ' . $e->getMessage());
                DB::rollBack();
                // 🔥 Log de l'erreur dans le log Laravel
                \Log::error('Erreur lors de l\'enregistrement de l\'étape 3', [
                    'code_projet' => $codeProjet,
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                // Suppression des données partielles
                $this->abortProjet(new Request(['code_projet' => $tempId]));
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create project: ' . $e->getMessage()
                ], 500);
            }
        }

        public function saveStep2(Request $request)
        {
            $request->validate([
                'code_projet' => 'required|string|exists:projets,code_projet',
                'localites' => 'nullable|array',
                'localites.*.id' => 'required|string',
                'localites.*.niveau' => 'nullable|string',
                'localites.*.decoupage' => 'nullable|string',
                'infrastructures' => 'nullable|array',
            ]);

            try{

                $codeProjet = $request->code_projet;

                // 🔁 1. Sauvegarder les localisations
                if ($request->has('localites')) {
                    foreach ($request->localites as $loc) {
                        ProjetLocalisation::updateOrCreate(
                            [
                                'code_projet' => $codeProjet,
                                'code_localite' => $loc['id'],
                            ],
                            [
                                'pays_code' => $request->pays_code ?? session('pays_selectionne'),
                                'niveau' => $loc['niveau'] ?? null,
                                'decoupage' => $loc['decoupage'] ?? null,
                            ]
                        );
                    }
    
                    // Stocker le premier code_localisation pour génération future du code projet
                    if (count($request->localites)) {
                        session(['code_localisation' => $request->localites[0]['id']]);
                    }
                }
    
                // 🔁 2. Sauvegarder les infrastructures + caractéristiques
                if ($request->has('infrastructures')) {
                    foreach ($request->infrastructures as $infra) {
                        // 1. Créer l'infrastructure de base
                        $infraDB = Infrastructure::create([
                            'code' => 'INFRA-' . strtoupper(Str::random(4)), // ou autre logique de code
                            'libelle' => $request->infrastructureName ?? 'Infrastructure sans nom',
                            'code_famille_infrastructure' => $infra['famille_code'] ?? null,
                        ]);
                    
                        // 2. Créer l’entrée dans projetinfrastructure
                        $projetInfra = ProjetInfrastructure::create([
                            'idInfrastructure' => $infraDB->id, // Lien par ID (entier)
                            'code_projet' => $codeProjet,
                            'localisation_id' => $infra['localisation_id'] ?? null,
                            'statut' => $infra['statut'] ?? 'prévu',
                        ]);
                    
                        // 3. Enregistrer les caractéristiques
                        if (!empty($infra['caracteristiques'])) {
                            foreach ($infra['caracteristiques'] as $carac) {
                                ValeurCaracteristique::create([
                                    'idInfrastructure' => $infraDB->id, // Utiliser le même ID
                                    'idCaracteristique' => $carac['id'],
                                    'idUnite' => $carac['unite_id'],
                                    'valeur' => $carac['valeur'],
                                ]);
                            }
                        }
                    }
                    
                }
    
                return response()->json([
                    'success' => true,
                    'message' => 'Étape 2 enregistrée avec succès.',
                ]);
            }catch (\Exception $e) {
                DB::rollBack();
                // 🔥 Log de l'erreur dans le log Laravel
                \Log::error('Erreur lors de l\'enregistrement de l\'étape 3', [
                    'code_projet' => $codeProjet,
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                // Suppression des données partielles
                $this->abortProjet(new Request(['code_projet' => $codeProjet]));
            
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l’enregistrement. Toutes les données ont été annulées.',
                ], 500);
            }
            
        }

        public function saveStep3(Request $request)
        {
            $request->validate([
                'code_projet' => 'required|exists:projets,code_projet',
                'actions' => 'required|array',
            ]);

            $codeProjet = $request->code_projet;

            try{

                foreach ($request->actions as $action) {
                    // 🔹 1. Enregistrement de l’action dans projet_action_a_mener
                    $actionModel = ProjetActionAMener::create([
                        
                        'code_projet' => $codeProjet,
                        'Num_ordre' => $action['ordre'],
                        'Action_mener' => $action['action_code'],
                        'Quantite' => $action['quantite'],
                        'Infrastrucrues_id' => $action['infrastructure_code'],
                    ]);
    
                    // 🔹 2. Répartition par type de bénéficiaire
                    foreach ($action['beneficiaires'] as $beneficiaire) {
                        switch ($beneficiaire['type']) {
                            case 'localite':
                                Profiter::create([
                                    'code_projet' => $codeProjet,
                                    'code_pays' => $beneficiaire['codePays'],
                                    'code_rattachement' => $beneficiaire['codeRattachement'],
                                ]);
                                break;
    
                            case 'acteur':
                                Beneficier::create([
                                    'code_projet' => $codeProjet,
                                    'code_acteur' => $beneficiaire['code'],
                                    'is_active' => true,
                                ]);
                                break;
    
                            case 'infrastructure':
                                Jouir::create([
                                    'code_projet' => $codeProjet,
                                    'code_Infrastructure' => $beneficiaire['code'],
                                ]);
                                break;
                        }
                    }
                }
    
                return response()->json([
                    'success' => true,
                    'message' => 'Étape 3 enregistrée avec succès.',
                ]);
            }catch (\Exception $e) {
                DB::rollBack();
                // 🔥 Log de l'erreur dans le log Laravel
                \Log::error('Erreur lors de l\'enregistrement de l\'étape 3', [
                    'code_projet' => $codeProjet,
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                // Suppression des données partielles
                $this->abortProjet(new Request(['code_projet' => $codeProjet]));

                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l’enregistrement. Toutes les données ont été annulées.',
                ], 500);
            }

        }

        public function saveStep4(Request $request)
        {
            $request->validate([
                'code_projet' => 'required|exists:projets,code_projet',
                'code_acteur_moe' => 'required|exists:acteur,code_acteur',
                'type_ouvrage' => 'nullable|string',
                'priveMoeType' => 'nullable|string',
                'sectActivEntMoe' => 'nullable|string',
                'descriptionMoe' => 'nullable|string',
            ]);

            try{
                $codeProjet = $request->code_projet;
                // On désactive les anciens maîtres d’ouvrage (si en mise à jour)
                Posseder::where('code_projet', $request->code_projet)->update(['is_active' => false]);

                // Nouveau maître d’ouvrage actif
                Posseder::create([
                    'code_projet' => $request->code_projet,
                    'code_acteur' => $request->code_acteur_moe,
                    'secteur_id' => $request->sectActivEntMoe,
                    'date' => now(),
                    'is_active' => true,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Maître d’ouvrage enregistré avec succès.',
                ]);
            }catch (\Exception $e) {
                DB::rollBack();
                // 🔥 Log de l'erreur dans le log Laravel
                \Log::error('Erreur lors de l\'enregistrement de l\'étape 3', [
                    'code_projet' => $codeProjet,
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                // Suppression des données partielles
                $this->abortProjet(new Request(['code_projet' => $codeProjet]));

                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l’enregistrement. Toutes les données ont été annulées.',
                ], 500);
            }

        }

        public function saveStep5(Request $request)
        {
            $request->validate([
                'code_projet' => 'required|string|exists:projets,code_projet',
                'acteurs' => 'required|array',
                'acteurs.*.code_acteur' => 'required|string|exists:acteur,code_acteur',
                'acteurs.*.secteur_id' => 'nullable|string',
            ]);
            try{
                $codeProjet = $request->code_projet;
                foreach ($request->acteurs as $acteur) {
                    $exists = Executer::where('code_projet', $request->code_projet)
                        ->where('code_acteur', $acteur['code_acteur'])
                        ->exists();
            
                    if (!$exists) {
                        Executer::create([
                            'code_projet' => $request->code_projet,
                            'code_acteur' => $acteur['code_acteur'],
                            'secteur_id' => $acteur['secteur_id'] ?? null,
                            'is_active' => true,
                        ]);
                    }
                }
            
                return response()->json([
                    'success' => true,
                    'message' => 'Maîtres d’œuvre enregistrés avec succès.'
                ]);
            }catch (\Exception $e) {
                DB::rollBack();
                // 🔥 Log de l'erreur dans le log Laravel
                \Log::error('Erreur lors de l\'enregistrement de l\'étape 3', [
                    'code_projet' => $codeProjet,
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                // Suppression des données partielles
                $this->abortProjet(new Request(['code_projet' => $codeProjet]));

                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l’enregistrement. Toutes les données ont été annulées.',
                ], 500);
            }

        }
        


        public function saveStep6(Request $request)
        {
            $request->validate([
                'code_projet' => 'required|string|exists:projets,code_projet',
                'financements' => 'required|array',
                'type_financement' => 'required|in:public,privé,mixte',
            ]);

            try{

                $codeProjet = $request->code_projet;

                foreach ($request->financements as $item) {
                    Financer::create([
                        'code_projet' => $codeProjet,
                        'code_acteur' => $item['bailleur'],
                        'montant_finance' => $item['montant'],
                        'devise' => $item['devise'],
                        'financement_local' => $item['local'] === 'Oui',
                        'commentaire' => $item['commentaire'],
                        'FinancementType' => $request->type_financement,
                        'is_active' => true,
                    ]);
                }
    
                return response()->json([
                    'success' => true,
                    'message' => 'Financements enregistrés avec succès.',
                ]);
            }catch (\Exception $e) {
                DB::rollBack();
                // 🔥 Log de l'erreur dans le log Laravel
                \Log::error('Erreur lors de l\'enregistrement de l\'étape 3', [
                    'code_projet' => $codeProjet,
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                // Suppression des données partielles
                $this->abortProjet(new Request(['code_projet' => $codeProjet]));

                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l’enregistrement. Toutes les données ont été annulées.',
                ], 500);
            }

        }
        public function saveStep7(Request $request)
        {
            $request->validate([
                'code_projet' => 'required|string|exists:projets,code_projet',
                'fichiers.*' => 'required|file|max:10240'
            ]);
        
            try {
                $codeProjet = $request->code_projet;
        
                if (!$request->hasFile('fichiers')) {
                    throw new \Exception("Aucun fichier reçu.");
                }
        
                $uploadPath = public_path('data/documentProjet/' . $codeProjet);
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
        
                foreach ($request->file('fichiers') as $file) {
                    if (!$file->isValid()) {
                        throw new \Exception("Fichier invalide : " . $file->getClientOriginalName());
                    }
        
                    $originalName = $file->getClientOriginalName();
                    $filename = time() . '_' . $originalName;
                    $file->move($uploadPath, $filename);
        
                    ProjetDocument::create([
                        'file_name' => $originalName,
                        'file_path' => 'data/documentProjet/' . $codeProjet . '/' . $filename,
                        'file_type' => $file->getClientMimeType(),
                        'file_size' => $file->getSize(),
                        'uploaded_at' => now(),
                        'code_projet' => $codeProjet,
                    ]);
                }
        
                return response()->json([
                    'success' => true,
                    'message' => 'Documents enregistrés avec succès.'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
        
                \Log::error('Erreur lors de l\'enregistrement des fichiers (étape 7)', [
                    'code_projet' => $request->code_projet ?? 'non défini',
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
        
                $this->abortProjet(new Request(['code_projet' => $request->code_projet]));
        
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l’enregistrement des fichiers. Toutes les données ont été annulées.',
                ], 500);
            }
        }
        
        
        
        public function finaliserProjet(Request $request)
        {
            $request->validate([
                'code_projet_temp' => 'required|string|exists:projets,code_projet',
                'type_financement' => 'required|in:1,2',
                'code_localisation' => 'required|string',
            ]);

            $tempCode = $request->code_projet_temp;

            $projeyat = Projet::where('code_projet', $tempCode)->firstOrFail();

            // ✅ Générer code projet final
            $codeProjetFinal = $this->genererCodeProjetFinal(
                $projet->code_sous_domaine,
                $request->type_financement,
                $request->code_localisation,
                $projet->date_demarrage_prevue
            );

            // Tables liées à mettre à jour
            $tables = [
                'projets_natureTravaux' => 'code_projet',
                'projetinfrastructure' => 'code_projet',
                'projet_action_a_mener' => 'code_projet',
                'executer' => 'code_projet',
                'posseder' => 'code_projet',
                'financer' => 'code_projet',
                'profiter' => 'code_projet',
                'beneficier' => 'code_projet',
                'jouir' => 'code_projet',
                'projet_documents' => 'code_projet',
            ];

            DB::beginTransaction();
            try {
                // 🔁 Mise à jour du code projet principal
                $projet->update(['code_projet' => $codeProjetFinal]);

                // 🔁 Mise à jour des tables liées
                foreach ($tables as $table => $key) {
                    DB::table($table)
                        ->where($key, $tempCode)
                        ->update([$key => $codeProjetFinal]);
                }

                // ✅ Création du code étude
                $codeEtude = $this->genererCodeEtude(
                    session('pays_selectionne'),
                    session('projet_selectionne')
                );

                EtudeProject::create([
                    'codeEtudeProjets' => $codeEtude,
                    'code_projet' => $codeProjetFinal,
                    'valider' => false,
                    'is_deleted' => false,
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'code_projet_final' => $codeProjetFinal,
                    'code_etude' => $codeEtude,
                    'message' => 'Code projet finalisé et code étude créé avec succès.'
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Finalisation projet échouée : ' . $e->getMessage());
                \Log::error('Erreur lors de l\'enregistrement des fichiers (étape 7)', [
                    'code_projet' => $request->code_projet ?? 'non défini',
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
        
                $this->abortProjet(new Request(['code_projet' => $request->code_projet]));
        
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la finalisation du projet.',
                ], 500);
            }
        }
        

        const MAX_FILE_SIZE_KB = 2048; // 2 Mo
        const MAX_FILE_SIZE_MB = 2;
        
        public function storeNaissance(Request $request)
        {
            DB::beginTransaction();
            try {


                $location = 'CI';  // Fixe pour le moment
                $category = 'EHA'; // Fixe pour le moment

                // Générer le code projet
                $codeEtudeProjets = $request->input('codeProjet');

                // Créer le projet
                $project = EtudeProject::create([
                    'codeEtudeProjets' => $codeEtudeProjets,
                    'natureTravaux' => $request->input('nature_travaux'),
                    'typeDemandeur' => $request->typeDemandeur,
                    'public' =>  $request->has('maitreOuvrage') ? true : false,
                    'collectivite_territoriale' => $request->input('collectivite'),
                    'ministere' =>$request->input('ministere')
                ]);

                // Sauvegarder les informations en fonction du type de demandeur
                $this->storeDemandeurInfo($request, $codeEtudeProjets);

                // Traiter chaque fichier uploadé
                if ($request->hasFile('files')) {
                    $this->handleFileUploads($request, $project->codeEtudeProjets);
                }

                DB::commit();
                return redirect()->back()->with('success', 'Projet enregistré avec succès');
            } catch (PostTooLargeException $e) {
                Log::error('Fichier trop volumineux : ' . $e->getMessage());
                return redirect()->back()->withErrors(['files' => 'Le fichier dépasse la taille maximale autorisée de ' . self::MAX_FILE_SIZE_MB . ' Mo.']);
            } catch (\Exception $e) {
                DB::rollback();
                Log::error('Erreur lors de l\'enregistrement du projet : ' . $e->getMessage(), [
                    'request' => $request->all(),
                    'stack_trace' => $e->getTraceAsString(),
                ]);
                return redirect()->back()->withErrors(['general' => 'Une erreur est survenue lors de l\'enregistrement du projet : ' . $e->getMessage()]);
            }
        }


        private function storeDemandeurInfo($request, $codeEtudeProjets)
        {
            if ($request->typeDemandeur == 'entreprise') {
                EntrepriseParticulier::create([
                    'codeEtudeProjets' =>  $codeEtudeProjets,
                    'nomEntreprise' => $request->input('companyName'),
                    'raisonSociale' => $request->input('legalStatus'),
                    'numeroImmatriculation' => $request->input('registrationNumber'),
                    'adresseSiegeSocial' => $request->input('headOfficeAddress'),
                    'numeroTelephone' => $request->input('phoneNumber'),
                    'adresseEmail' => $request->input('emailAddress'),
                    'siteWeb' => $request->input('website'),
                    'nomResponsableProjet' => $request->input('projectManager'),
                    'fonctionResponsable' => $request->input('managerRole'),
                    'capitalSocial' => $request->input('capital'),
                    'infoSupplementaire1' => $request->input('additionalInfo1'),
                    'infoSupplementaire2' => $request->input('additionalInfo2'),
                ]);
            } elseif ($request->typeDemandeur == 'particulier') {
                EntrepriseParticulier::create([
                    'codeEtudeProjets' => $codeEtudeProjets,
                    'nom' => $request->input('nom'),
                    'prenom' => $request->input('prenom'),
                    'statutProfessionnel' => $request->input('professionalStatus'),
                    'numeroImmatriculationIndividuelle' => $request->input('individualRegistrationNumber'),
                    'adresseEntreprise' => $request->input('individualAddress'),
                    'numeroTelephone' => $request->input('individualPhone'),
                    'adresseEmail' => $request->input('individualEmail'),
                    'activitePrincipale' => $request->input('mainActivity'),
                    'nomCommercial' => $request->input('tradeName'),
                    'coordonneesBancaires' => $request->input('bankDetails'),
                    'references' => $request->input('references'),
                    'infoSupplementaire3' => $request->input('additionalInfo3'), // Fix typo: $request->inpu -> $request->input
                ]);
            }
        }

        private function handleFileUploads($request, $codeEtudeProjets)
        {
            $errorFiles = [];
            foreach ($request->file('files') as $file) {
                if ($file->getSize() > self::MAX_FILE_SIZE_KB * 1024) {
                    $errorFiles[] = $file->getClientOriginalName();
                    continue;
                }

                $fileName = $file->getClientOriginalName();
                $filePath = $file->storeAs('uploads/projects', $fileName, 'public');

                // Sauvegarder les informations du fichier dans la base de données
                EtudeProjectFile::create([
                    'codeEtudeProjets' => $codeEtudeProjets,
                    'file_path' => $filePath,
                    'file_name' => $fileName,
                ]);
            }

            if (!empty($errorFiles)) {
                $errorFileNames = implode(', ', $errorFiles);
                throw new \Exception("Les fichiers suivants dépassent la taille maximale autorisée de " . self::MAX_FILE_SIZE_MB . " Mo : $errorFileNames");
            }
        }


        public function getLatestProjectNumber($location, $category, $typeFinancement)
        {
            $year = date('Y');
            $lastProject = EtudeProject::where('codeEtudeProjets', 'like', "{$location}PROJ{$category}{$typeFinancement}{$year}_%")
                                        ->orderBy('codeEtudeProjets', 'desc')
                                        ->first();

            $lastNumber = $lastProject ? (int)substr($lastProject->codeEtudeProjets, -2) : 0;
            $newNumber = str_pad($lastNumber + 1, 2, '0', STR_PAD_LEFT);

            return response()->json(['newNumber' => $newNumber]);
        }

        private function generateProjectCode($location, $category, $typeFinancement)
        {
            $year = date('Y');
            $lastProject = EtudeProject::where('codeEtudeProjets', 'like', "{$location}PROJ{$category}{$typeFinancement}{$year}_%")
                                        ->orderBy('codeEtudeProjets', 'desc')
                                        ->first();

            $lastNumber = $lastProject ? (int)substr($lastProject->codeEtudeProjets, -2) : 0;
            $newNumber = str_pad($lastNumber + 1, 2, '0', STR_PAD_LEFT);

            return "{$location}{$category}{$typeFinancement}{$year}{$newNumber}";
        }

        ////////////////////////////////////Validation de projet/////////////////////////////////

        public function validation(Request $request)
        {
            $ecran = Ecran::find($request->input('ecran_id'));
            $user = auth()->user();

            // Vérifier si l'utilisateur est connecté et est un approbateur
            if (!$user || !$user->approbateur) {
                return redirect()->route('projets.index')->with('error', 'Vous devez être un approbateur pour accéder à cette page.');
            }

            // Récupérer l'approbateur actuel
            $approver = Approbateur::where('code_personnel', $user->approbateur->code_personnel)->first();

            // Vérifier que l'approbateur existe
            if (!$approver) {
                return redirect()->route('projets.index')->with('error', 'Vous devez être un approbateur pour accéder à cette page.');
            }

            // Récupérer les projets qui n'ont pas encore été approuvés par l'approbateur ou qui ont été approuvés par un approbateur précédent
            $projects = EtudeProject::select('etudeprojects.*')
            ->where('etudeprojects.is_deleted', 0)
            ->whereNotExists(function($query) use ($approver) {
                // Sous-requête pour vérifier si l'approbateur actuel a déjà approuvé le projet
                $query->select(DB::raw(1))
                    ->from('project_approbation as pa')
                    ->whereColumn('pa.codeEtudeProjets', 'etudeprojects.codeEtudeProjets') // Assure que nous comparons les bonnes colonnes
                    ->where('pa.codeAppro', $approver->codeAppro)
                    ->where('pa.is_approved', true);
            })
            ->get();

            return view('etudes_projets.validation', compact('ecran',  'projects'));
        }

        public function suivreApp(Request $request){
            $ecran = Ecran::find($request->input('ecran_id'));
            $approvedProjects = EtudeProject::select('etudeprojects.codeEtudeProjets', 'etudeprojects.natureTravaux', 'etudeprojects.created_at', 'pa.approved_at')
                ->join('project_approbation as pa', 'etudeprojects.codeEtudeProjets', '=', 'pa.codeEtudeProjets')
                ->join('approbateur as app', 'app.codeAppro', '=', 'pa.codeAppro')
                ->join('acteur as pers', 'pers.code_acteur', '=', 'app.code_acteur')
                ->where('pa.is_approved', true) // Filtre pour les projets approuvés
                ->where('etudeprojects.is_deleted', 0) // Assurez-vous que le projet n'est pas supprimé
                ->groupBy('etudeprojects.codeEtudeProjets', 'etudeprojects.natureTravaux', 'etudeprojects.created_at', 'pa.approved_at') // Grouper par projet
                ->addSelect(DB::raw('GROUP_CONCAT(CONCAT("N°", app.numOrdre, ": ", pers.nom, " ", pers.prenom) SEPARATOR "; ") as approbateurs')) // Concaténation des approbateurs
                ->get();
            return view('etudes_projets.suivreApp', compact('ecran',  'approvedProjects'));
        }
        public function historiqueApp(Request $request)
        {
            $ecran = Ecran::find($request->input('ecran_id'));
            // Récupérer tous les projets approuvés avec les approbations
            $approvalHistory = ProjectApproval::select('project_approbation.*', 'etudeprojects.natureTravaux', 'pers.nom', 'pers.prenom')
                ->join('etudeprojects', 'project_approbation.codeEtudeProjets', '=', 'etudeprojects.codeEtudeProjets')
                ->join('approbateur as app', 'project_approbation.codeAppro', '=', 'app.code_acteur')
                ->join('personnel as pers', 'app.code_acteur', '=', 'pers.code_personnel')
                ->where('project_approbation.is_approved', true) // Filtre pour les approbations
                ->orderBy('project_approbation.approved_at', 'desc') // Trier par date d'approbation
                ->get();

            return view('etudes_projets.historiqueApp', compact('ecran','approvalHistory'));
        }
        // Afficher les détails du projet
        public function show($codeEtudeProjets)
        {
            try {
                $project = EtudeProject::where('codeEtudeProjets', $codeEtudeProjets)->firstOrFail();
                $files = $project->files;
                $validations = Validations::where('codeEtudeProjets', $codeEtudeProjets)
                    ->with('user')
                    ->orderBy('created_at')
                    ->get();
                $users = User::all();   // Récupérer les utilisateurs pour l'affichage des validations

                // Vérifier si l'utilisateur a déjà validé le projet
                $user = auth()->user();
                $userHasValidated = Validations::where('codeEtudeProjets', $codeEtudeProjets)
                    ->where('user_id', $user->id)
                    ->exists();

                // Récupérer les projets en attente si l'utilisateur n'a pas encore validé
                $projects = $userHasValidated ? collect([]) : EtudeProject::with(['files', 'entreprise', 'particulier'])

                    ->where('current_approver', $user->approbateur->codeAppro)
                     ->get();

                return view('etudes_projets.validation', compact('project', 'files', 'validations', 'users', 'userHasValidated', 'projects'));
            } catch (ModelNotFoundException $e) {
                return redirect()->back()->with('error', 'Projet non trouvé.');
            }
        }


        // Valider le projet
        /*public function validateProject(Request $request, $codeEtudeProjets)
        {
            $approbateur = $request->user()->approbateur; // Récupérer l'approbateur actuel

            try {
                $project = EtudeProject::where('codeEtudeProjets', $codeEtudeProjets)->firstOrFail();

                // Vérifier si le projet est dans l'état correct pour la validation
                if ($project->status !== 'pending' || $project->current_approver != $approbateur->codeAppro) {
                    return redirect()->back()->with('error', 'Le projet ne peut pas être validé à ce stade.');
                }

                // Mettre à jour le statut du projet et définir le prochain approbateur
                $nextApprover = Approbateur::where('numOrdre', '>', $approbateur->numOrdre)
                    ->orderBy('numOrdre')
                    ->first();

                $project->update([
                    'status' => 'approved',
                    'current_approver' => $nextApprover ? $nextApprover->codeAppro : null,
                ]);

                return redirect()->back()->with('success', 'Projet validé avec succès.');

            } catch (ModelNotFoundException $e) {
                return redirect()->back()->with('error', 'Projet non trouvé.');
            }
        }*/
        public function approve(Request $request, $id)
        {
            $userId = auth()->user();

            // Récupérer l'approbateur actuel en fonction de l'utilisateur connecté
            $approver = Approbateur::where('code_personnel', $userId->approbateur->code_personnel)->first();

            if (!$approver) {
                return back()->with('error', 'Vous n\'êtes pas un approbateur valide pour ce projet.');
            }
            // Vérifier l'existence de projets dans ProjectApproval
            $projectExists = ProjectApproval::where('codeEtudeProjets', $id)->exists();

            if (!$projectExists) {
                // Aucun projet n'existe, seul l'approbateur avec numOrdre = 1 peut enregistrer
                if ($approver->numOrdre === 1) {
                    // Enregistrement d'approbation
                    ProjectApproval::create([
                        'codeEtudeProjets' => $id,
                        'codeAppro' => $approver->codeAppro,
                        'is_approved' => true,
                        'approved_at' => now(),
                    ]);
                    return back()->with('success', 'Projet approuvé ');
                } else {
                    return back()->with('error', 'Vous ne pouvez pas encore valider le projet.');
                }
            } else {
                // Un projet existe, vérifier si l'approbateur précédent a approuvé
                $previousApproverNumOrdre = $approver->numOrdre - 1;

                // Vérifier si l'approbateur précédent a approuvé
                $previousApproverApproved = ProjectApproval::where('codeEtudeProjets', $id)
                    ->join('approbateur', 'project_approbation.codeAppro', '=', 'approbateur.codeAppro')
                    ->where('approbateur.numOrdre', $previousApproverNumOrdre)
                    ->where('project_approbation.is_approved', true)
                    ->exists();

                if (!$previousApproverApproved) {
                    // L'approbateur avec numOrdre inférieur n'a pas encore validé
                    return back()->with('error', 'Vous ne pouvez pas encore valider le projet.');
                }

                // Enregistrement d'approbation
                ProjectApproval::create([
                    'codeEtudeProjets' => $id,
                    'codeAppro' => $approver->codeAppro,
                    'is_approved' => true,
                    'approved_at' => now(),
                ]);

                return back()->with('success', 'Projet approuvé .');
            }
        }



    /////////////////////////////RENFORCEMENT DES CAPACITE//////////////////////

    public function deleteRenforcement($id)
    {
        // Trouver le renforcement par son code
        $renforcement = Renforcement::where('code_renforcement', $id)->firstOrFail();

        if (!$renforcement) {
            return response()->json(['error' => 'Le renforcement de capacité que vous essayez de supprimer n\'existe pas.'], 404);
        }

        try {
            // Supprimer le renforcement et les relations associées (grâce au hook deleting)
            $renforcement->delete();

            return response()->json(['success' => 'Le renforcement de capacité et ses relations associées ont été supprimés avec succès'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la suppression du renforcement de capacité. Détails : ' . $e->getMessage()], 500);
        }
    }


    public function renfo(Request $request)
    {
        $renforcements = Renforcement::with(['beneficiaires', 'projets'])->get();

        $ecran = Ecran::find($request->input('ecran_id'));
        $projets = Projet::all();
        $beneficiaires = User::all();
        return view('etudes_projets.renforcement', compact('renforcements', 'projets', 'beneficiaires', 'ecran'));
    }

    public function store(Request $request)
    {
        try {
            // Valider les données d'entrée (les projets ne sont pas obligatoires)
            $validatedData = $request->validate([
                'titre' => 'required|string|max:255',
                'description' => 'required|string',
                'date_renforcement' => 'required|date',
                'date_fin' => 'required|date',
                'beneficiaires' => 'required|array|min:1',  // Au moins un bénéficiaire est requis
                'beneficiaires.*' => 'exists:mot_de_passe_utilisateur,code_personnel', // Valider que chaque bénéficiaire existe
                'projets' => 'nullable|array',  // Projets non obligatoires
                'projets.*' => 'exists:projet_eha2,CodeProjet',  // Si des projets sont fournis, vérifier qu'ils existent
            ]);

            // Générer un code personnalisé pour le renforcement
            $codeRenforcement = Renforcement::generateCodeRenforcement();

            // Créer un renforcement
            $renforcement = Renforcement::create([
                'code_renforcement' => $codeRenforcement,
                'titre' => $validatedData['titre'],
                'description' => $validatedData['description'],
                'date_debut' => $validatedData['date_renforcement'],
                'date_fin' => $validatedData['date_fin']
            ]);

            // Associer les bénéficiaires s'ils sont présents
            if (isset($validatedData['beneficiaires'])) {
                $renforcement->beneficiaires()->attach($validatedData['beneficiaires']);
            }

            // Associer les projets s'ils sont présents
            if (isset($validatedData['projets'])) {
                $renforcement->projets()->attach($validatedData['projets']);
            }
            $ecran_id = $request->input('ecran_id');
            // Rediriger vers la liste des renforcements après la sauvegarde
            return redirect()->route('renforcements.index', ['ecran_id' => $ecran_id])->with('success', 'Renforcement créé avec succès !');

        } catch (\Exception $e) {
            // Capture et gestion des erreurs
            return redirect()->back()->withInput()->withErrors(['error' => 'Une erreur est survenue lors de la création du renforcement : ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Trouver le renforcement par son identifiant
            $renforcement = Renforcement::where('code_renforcement', $id)->firstOrFail();

            // Mettre à jour les détails du renforcement
            $renforcement->update([
                'titre' => $request->titre,
                'description' => $request->description,
                'date_debut' => $request->date_renforcement,
                'date_fin' => $request->date_fin
            ]);

            // Mettre à jour les bénéficiaires associés
            if ($request->has('beneficiaires')) {
                $renforcement->beneficiaires()->sync($request->beneficiaires);
            } else {
                $renforcement->beneficiaires()->detach();
            }

            // Mettre à jour les projets associés
            if ($request->has('projets')) {
                $renforcement->projets()->sync($request->projets);
            } else {
                $renforcement->projets()->detach();
            }

            $ecran_id = $request->input('ecran_id');
            // Rediriger avec succès
            return redirect()->route('renforcements.index', ['ecran_id' => $ecran_id])->with('success', 'Renforcement modifié avec succès !');
        } catch (\Exception $e) {
            // En cas d'erreur, rediriger avec un message d'erreur
            return back()->with('error', 'Une erreur s\'est produite lors de la modification : ' . $e->getMessage());
        }
    }

    ////////////////////////////////////ACTIVITE CONNEXE//////////////////////////////
    // Afficher la liste des travaux connexes
    public function activite(Request $request)
    {
        $ecran = Ecran::find($request->input('ecran_id'));
        $travaux = TravauxConnexes::with('typeTravaux', 'projet')->get();
        $projets = Projet::all();
        $typesTravaux = TypeTravauxConnexes::orderBy('libelle', 'asc')->get();

        return view('etudes_projets.activite', compact('ecran','travaux', 'projets', 'typesTravaux'));
    }

    // Enregistrer un nouveau travail connexe
    public function storeConnexe(Request $request)
    {
        $request->merge([
            'cout_projet' => str_replace(' ', '', $request->input('cout_projet')),
        ]);
        // Validation des champs du formulaire
        $request->validate([
            'code_projet' => 'required',
            'type_travaux_id' => 'required',
            'cout_projet' => 'required|numeric',
            'date_debut_previsionnelle' => 'required|date',
            'date_fin_previsionnelle' => 'required|date|after_or_equal:date_debut_previsionnelle',
        ]);

        try {
            // Générer un code personnalisé pour l'activité connexe
            $codeActivite = TravauxConnexes::generateCodeTravauxConnexe();

            // Créer et enregistrer le travail connexe
            TravauxConnexes::create([
                'codeActivite' => $codeActivite,
                'CodeProjet' => $request->input('code_projet'), // Utiliser le code projet fourni
                'type_travaux_id' => $request->input('type_travaux_id'),
                'cout_projet' => $request->input('cout_projet'), // Enlever les espaces
                'date_debut_previsionnelle' => $request->input('date_debut_previsionnelle'),
                'date_fin_previsionnelle' => $request->input('date_fin_previsionnelle'),
                'date_debut_effective' => $request->input('date_debut_effective'),
                'date_fin_effective' => $request->input('date_fin_effective'),
                'commentaire' => $request->input('commentaire'),
            ]);

            // Rediriger avec un message de succès
            return redirect()->route('activite.index', ['ecran_id' => $request->input('ecran_id')])
                ->with('success', 'Travail connexe enregistré avec succès.');

        } catch (\Exception $e) {
            // En cas d'erreur, retourner avec un message d'erreur
            return back()->with('error', 'Erreur lors de l\'enregistrement du travail connexe. Détails : ' . $e->getMessage());
        }
    }



    // Modifier un travail connexe
    public function updateConnexe(Request $request, $id)
    {
        $request->merge([
            'cout_projet' => str_replace(' ', '', $request->input('cout_projet')),
        ]);
        // Valider les champs du formulaire
        $request->validate([
            'type_travaux_id' => 'required',
            'cout_projet' => 'required|numeric',
            'date_debut_previsionnelle' => 'required|date',
            'date_fin_previsionnelle' => 'required|date|after_or_equal:date_debut_previsionnelle',
        ]);

        try {
            // Récupérer le travail connexe à modifier par son code d'activité (codeActivite)
            $travauxConnexe = TravauxConnexes::where('codeActivite', $id)->firstOrFail();

            // Mettre à jour les informations du travail connexe
            $travauxConnexe->update([
                'type_travaux_id' => $request->input('type_travaux_id'),
                'cout_projet' =>$request->input('cout_projet'), // Enlever les espaces avant d'enregistrer
                'date_debut_previsionnelle' => $request->input('date_debut_previsionnelle'),
                'date_fin_previsionnelle' => $request->input('date_fin_previsionnelle'),
                'date_debut_effective' => $request->input('date_debut_effective'),
                'date_fin_effective' => $request->input('date_fin_effective'),
                'commentaire' => $request->input('commentaire'),
            ]);

            // Rediriger avec un message de succès
            return redirect()->route('activite.index', ['ecran_id' => $request->input('ecran_id')])->with('success', 'Travail connexe modifié avec succès.');

        } catch (\Exception $e) {
            // Gérer les erreurs et rediriger avec un message d'erreur
            return back()->with('error', 'Erreur lors de la modification du travail connexe. Détails : ' . $e->getMessage());
        }
    }


    // Supprimer un travail connexe

    public function deleteActivite($id)
    {
        // Trouver le renforcement par son code
        $travaux = TravauxConnexes::where('codeActivite', $id)->firstOrFail();

        if (!$travaux) {
            return response()->json(['error' => 'L\'activité connexe que vous essayez de supprimer n\'existe pas.'], 404);
        }

        try {
            // Supprimer le renforcement et les relations associées (grâce au hook deleting)
            $travaux->delete();

            return response()->json(['success' => 'L\'activite connexe a été supprimés avec succès'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la suppression. Détails : ' . $e->getMessage()], 500);
        }
    }
    ///////////////MODELISER
    public function modelisation(Request $request)
    {
        $ecran = Ecran::find($request->input('ecran_id'));
        return view('etudes_projets.modeliser', compact('ecran'));
    }
}




