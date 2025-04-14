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
use App\Models\ProjetApprobation;
use App\Models\ProjetDocument;
use App\Models\projets_natureTravaux;
use App\Models\ProjetStatut;
use App\Models\TypeFinancement;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
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
            $Dpays = Pays::where('alpha3', $paysSelectionne)->first();

            $deviseCouts  = Devise::select('libelle', 'code_long')
                            ->where('code_long', $Dpays->code_devise)
                            ->first();
            
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

            $typeFinancements = TypeFinancement::all();

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
            return view('etudes_projets.naissance', compact('typeFinancements','Devises', 'bailleurActeurs', 'infrastructures', 'acteurs','TypeCaracteristiques','deviseCouts','acteurRepres','Pieceidentite','NaturesTravaux', 'formeJuridiques','SituationMatrimoniales','genres', 'SecteurActivites', 'Pays','SousDomaines','Domaines','GroupeProjets','ecran','generatedCodeProjet','natures','groupeSelectionne', 'tousPays', 'devises','actionMener'));
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
        
        public function getFamilles($codeDomaine)
        {
            $familles = FamilleInfrastructure::whereIn('code_groupe_projet',  [session('projet_selectionne')])
            ->where('code_domaine', $codeDomaine)->get();

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

       /* private function genererCodeProjet($codeSousDomaine, $typeFinancement, $codeLocalisation, $dateDebut)
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

            return strtoupper("{$paysAlpha3}{$groupeProjet}{$typeFinancement}_{$codeLocalisation}_{$codeSousDomaine}_{$annee}_{$ordre}");
        }
        private function genererCodeProjet($codeSousDomaine, $typeFinancement, $codeLocalisation, $dateDebut)
        {
            $paysAlpha3 = session('pays_selectionne'); // ex: CIV
            $groupeProjet = session('projet_selectionne'); // ex: BAT
        
            $date = Carbon::parse($dateDebut);
            $annee = $date->format('Y');
            $mois = $date->format('m');
        
            // Extraire les 2 premiers caractères du code sous-domaine
            $codeDomaine = substr($codeSousDomaine, 0, 2);
        
            // Compter les projets existants
            $ordre = Projet::where('code_alpha3_pays', $paysAlpha3)
                ->where('code_sous_domaine', 'like', $codeDomaine . '%')
                ->whereYear('date_demarrage_prevue', $annee)
                ->whereMonth('date_demarrage_prevue', $mois)
                ->count() + 1;
        
            return sprintf('%s%s%s_%s_%s_%s_%02d',
                strtoupper($paysAlpha3),
                strtoupper($groupeProjet),
                $typeFinancement, // 1 ou 2
                strtoupper($codeLocalisation),
                strtoupper($codeDomaine),
                $annee,
                $ordre
            );
        }*/
        private function genererCodeEtude($codePays, $codeGroupeProjet)
        {
            $now = Carbon::now();
            $annee = $now->format('Y');
            $mois = $now->format('m');

            // Compte les études existantes pour ce mois/pays/groupe
            $ordre = EtudeProject::where('codeEtudeProjets', 'like', "{$codePays}_{$codeGroupeProjet}_{$annee}_{$mois}_%")->count() + 1;

            return strtoupper("{$codePays}_{$codeGroupeProjet}_{$annee}_{$mois}_{$ordre}");
        }

        public function saveStep1(Request $request)
        {
            try {
                $request->validate([
                    'libelle_projet' => 'required|string|max:255',
                    'code_sous_domaine' => 'required|string|max:10',
                    'date_demarrage_prevue' => 'required|date',
                    'date_fin_prevue' => 'required|date|after_or_equal:date_demarrage_prevue',
                    'cout_projet' => 'required|numeric',
                    'code_devise' => 'required|string|max:3',
                    'code_nature' => 'required|string|max:10',
                    'code_pays' => 'required|string|max:3',
                    'commentaire' => 'nullable|string|max:500'
                ]);
        
                $data = $request->only([
                    'libelle_projet', 'commentaire', 'code_sous_domaine',
                    'date_demarrage_prevue', 'date_fin_prevue',
                    'cout_projet', 'code_devise', 'code_nature', 'code_pays'
                ]);
        
                session(['form_step1' => $data]);
        
                // Vérification du stockage en session
                if (session()->has('form_step1')) {
                    \Log::info('Données correctement stockées en session', [
                        'session_data' => session('form_step1')
                    ]);
                } else {
                    \Log::error('Échec du stockage en session');
                }
        
                return response()->json(['success' => true]);
        
            } catch (\Throwable $e) {
                \Log::error('Erreur lors de l\'enregistrement des données step1', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
        
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'error' => 'Une erreur est survenue. Veuillez réessayer.'
                ], 500);
            }
        }
        
        public function saveStep2(Request $request)
        {
            try {
                $request->validate([
                    'localites' => 'required|array|min:1',
                    'localites.*.code_rattachement' => 'required|string',
                    'localites.*.niveau' => 'nullable|string',
                    'localites.*.decoupage' => 'nullable|string',
                    'infrastructures' => 'nullable|array',
                ]);
        
                $data = $request->only(['localites', 'infrastructures']);
                session(['form_step2' => $data]);
        
                // Stocker aussi le premier code_localisation s’il existe
                if (!empty($request->localites)) {
                    session(['code_localisation' => $request->localites[0]['code_rattachement']]);
                }
        
                // Vérification
                if (session()->has('form_step2')) {
                    \Log::info('Étape 2 stockée en session avec succès.', [
                        'session_data' => session('form_step2')
                    ]);
                } else {
                    \Log::error('Échec de la sauvegarde en session (étape 2).');
                }
        
                return response()->json([
                    'success' => true,
                    'message' => 'Étape 2 enregistrée temporairement en session.',
                ]);
            } catch (\Throwable $e) {
                \Log::error('Erreur lors de l\'enregistrement de l\'étape 2', [
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
        
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }
        }
        
        public function saveStep3(Request $request)
        {
            try {
                $request->validate([
                     'actions' => 'required|array',
                ]);
        
                $data = $request->only(['actions']);
                session(['form_step3' => $data]);
        
                if (session()->has('form_step3')) {
                    \Log::info('Étape 3 stockée en session avec succès.', [
                        'session_data' => session('form_step3')
                    ]);
                } else {
                    \Log::error('Échec de la sauvegarde en session (étape 3).');
                }
        
                return response()->json([
                    'success' => true,
                    'message' => 'Étape 3 enregistrée temporairement en session.',
                ]);
            } catch (\Throwable $e) {
                \Log::error('Erreur lors de l\'enregistrement de l\'étape 3', [
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
        
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }
        }
        
        public function saveStep4(Request $request)
        {
            try {
                $request->validate([
                    'code_acteur_moe' => 'required|exists:acteur,code_acteur',
                    'type_ouvrage' => 'nullable|string',
                    'priveMoeType' => 'nullable|string',
                    'sectActivEntMoe' => 'nullable|string',
                    'descriptionMoe' => 'nullable|string',
                ]);
        
                $data = $request->only([
                    'code_acteur_moe', 'type_ouvrage', 'priveMoeType', 'sectActivEntMoe', 'descriptionMoe'
                ]);
        
                session(['form_step4' => $data]);
        
                if (session()->has('form_step4')) {
                    \Log::info('Étape 4 stockée en session avec succès.', [
                        'session_data' => session('form_step4')
                    ]);
                } else {
                    \Log::error('Échec du stockage en session (étape 4).');
                }
        
                return response()->json([
                    'success' => true,
                    'message' => 'Étape 4 enregistrée temporairement en session.',
                ]);
        
            } catch (\Exception $e) {
                \Log::error('Erreur lors de l\'enregistrement de l\'étape 4', [
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
        
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }
        }
        
        public function saveStep5(Request $request)
        {
            try {
                $request->validate([
                    'acteurs' => 'required|array',
                    'acteurs.*.code_acteur' => 'required|string|exists:acteur,code_acteur',
                    'acteurs.*.secteur_id' => 'nullable|string',
                ]);
        
                $data = $request->only(['acteurs']);
        
                // Stockage en session
                session(['form_step5' => $data]);
        
                if (session()->has('form_step5')) {
                    \Log::info('Étape 5 stockée en session avec succès.', [
                        'session_data' => session('form_step5')
                    ]);
                } else {
                    \Log::error('Échec du stockage en session (étape 5).');
                }
        
                return response()->json([
                    'success' => true,
                    'message' => 'Étape 5 enregistrée temporairement en session.',
                ]);
            } catch (\Exception $e) {
                \Log::error('Erreur lors de l\'enregistrement de l\'étape 5', [
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
        
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }
        }
        
        public function saveStep6(Request $request)
        {
            try {
                $request->validate([
                    'financements' => 'required|array',
                    'financements.*.bailleur' => 'required|string|exists:acteur,code_acteur',
                    'financements.*.montant' => 'required|numeric',
                    'financements.*.devise' => 'required|string|max:3',
                    'financements.*.local' => 'required|in:Oui,Non,oui,non,1,0,true,false',
                    'financements.*.commentaire' => 'nullable|string|max:500',
                    'type_financement' => 'required|string|exists:type_financement,code_type_financement',
                ]);
        
                $data = $request->only(['financements', 'type_financement']);
        
                // Stocker en session
                session(['form_step6' => $data]);
        
                if (session()->has('form_step6')) {
                    \Log::info('Étape 6 stockée en session avec succès.', [
                        'session_data' => session('form_step6')
                    ]);
                } else {
                    \Log::error('Échec du stockage en session (étape 6).');
                }
        
                return response()->json([
                    'success' => true,
                    'message' => 'Étape 6 enregistrée temporairement en session.',
                ]);
            } catch (\Exception $e) {
                \Log::error('Erreur lors de l\'enregistrement de l\'étape 6', [
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
        
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }
        }
        
        public function saveStep7(Request $request)
        {
            try {
                $request->validate([
                    'fichiers.*' => 'required|file|max:102400|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,zip,rar,dwg,dxf,ifc'
                ]);
        
                $uploadedFiles = [];
                foreach ($request->file('fichiers') as $file) {
                    if (!$file->isValid()) continue;
                    
                    // Stockage temporaire dans storage/app/temp
                    $path = $file->store('temp/projet', 'local');
                    
                    $uploadedFiles[] = [
                        'original_name' => $file->getClientOriginalName(),
                        'extension' => $file->getClientOriginalExtension(),
                        'mime_type' => $file->getClientMimeType(),
                        'size' => $file->getSize(),
                        'storage_path' => $path // Chemin dans le storage
                    ];
                }
        
                session(['form_step7' => ['fichiers' => $uploadedFiles]]);
        
                \Log::info('Fichiers stockés temporairement', [
                    'count' => count($uploadedFiles),
                    'files' => $uploadedFiles
                ]);
        
                return response()->json([
                    'success' => true,
                    'message' => count($uploadedFiles) . ' fichier(s) enregistré(s)'
                ]);
        
            } catch (\Exception $e) {
                \Log::error('Erreur étape 7', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }
        }
        
        public function finaliserProjet()
        {
            DB::beginTransaction();
            try {
                $step1 = session('form_step1');
                $step2 = session('form_step2');
                $step3 = session('form_step3');
                $step4 = session('form_step4');
                $step5 = session('form_step5');
                $step6 = session('form_step6');
                $step7 = session('form_step7');
        
                $codeLocalisation = $step2['localites'][0]['code_rattachement'] ?? null;

        
                // Générer le code projet
                $codeProjet = $this->genererCodeProjet(
                    $step1['code_sous_domaine'],
                    $step6['type_financement'],
                    $codeLocalisation,
                    $step1['date_demarrage_prevue']
                );
        
                // Enregistrer projet principal
                $projet = Projet::create([
                    'code_projet' => $codeProjet,
                    'libelle_projet' => $step1['libelle_projet'],
                    'commentaire' => $step1['commentaire'],
                    'code_sous_domaine' => $step1['code_sous_domaine'],
                    'date_demarrage_prevue' => $step1['date_demarrage_prevue'],
                    'date_fin_prevue' => $step1['date_fin_prevue'],
                    'cout_projet' => $step1['cout_projet'],
                    'code_devise' => $step1['code_devise'],
                    'code_nature' => $step1['code_nature'],
                    'code_alpha3_pays' => $step1['code_pays'],
                ]);

                ProjetStatut::create([
                    'code_projet' => $codeProjet, 
                    'type_statut' => 1, // Remplace par l'ID réel du statut (ex : 1 = Prévu, etc.)
                    'date_statut' => now(),
                ]);
                
                // Enregistrer localisations
                foreach ($step2['localites'] as $loc) {
                    ProjetLocalisation::create([
                        'code_projet' => $codeProjet,
                        'code_localite' => $loc['code_rattachement'],
                        'niveau' => $loc['niveau'] ?? null,
                        'decoupage' => $loc['decoupage'] ?? null,
                        'pays_code' => $step1['code_pays'],
                    ]);
                }
        
                // Infrastructures
                foreach ($step2['infrastructures'] ?? [] as $infra) {
                    $infraDB = Infrastructure::create([
                        'code' => 'INFRA-' . strtoupper(Str::random(4)),
                        'libelle' => $infra['libelle'],
                        'code_famille_infrastructure' => $infra['famille_code'] ?? null,
                        'code_groupe_projet' => session('projet_selectionne'),
                        'code_pays' => session('pays_selectionne'),
                    ]);
        
                    $projetInfra = ProjetInfrastructure::create([
                        'idInfrastructure' => $infraDB->id,
                        'code_projet' => $codeProjet,
                        'localisation_id' => $codeLocalisation ?? null,
                    ]);
        
                    foreach ($infra['caracteristiques'] ?? [] as $carac) {
                        ValeurCaracteristique::create([
                            'idInfrastructure' => $infraDB->id,
                            'idCaracteristique' => $carac['id'],
                            'idUnite' => $carac['unite_id'],
                            'valeur' => $carac['valeur'],
                        ]);
                    }
                }
        
                // Actions à mener
                foreach ($step3['actions'] ?? [] as $action) {
                    ProjetActionAMener::create([
                        'code_projet' => $codeProjet,
                        'Num_ordre' => $action['ordre'],
                        'Action_mener' => $action['action_code'],
                        'Quantite' => $action['quantite'],
                        'Infrastrucrues_id' => $action['infrastructure_code'],
                    ]);
        
                    foreach ($action['beneficiaires'] ?? [] as $b) {
                        match ($b['type']) {
                            'acteur' => Beneficier::create(
                                [
                                'code_projet' => $codeProjet,
                                'code_acteur' => $b['code'],
                                'is_active' => true
                            ]),
                            'localite' => Profiter::create([
                                'code_projet' => $codeProjet,
                                'code_pays' => $b['codePays'],
                                'code_rattachement' => $b['codeRattachement'],
                            ]),
                            'infrastructure' => Jouir::create([
                                'code_projet' => $codeProjet,
                                'code_Infrastructure' => $b['code'],
                            ])
                        };
                    }
                }
        
                // Maître d’Ouvrage
                Posseder::create([
                    'code_projet' => $codeProjet,
                    'code_acteur' => $step4['code_acteur_moe'],
                    'secteur_id' => $step4['sectActivEntMoe'] ?? null,
                    'date' => now(),
                    'is_active' => true,
                ]);
        
                // Maîtres d’œuvre
                foreach ($step5['acteurs'] as $acteur) {
                    Executer::create([
                        'code_projet' => $codeProjet,
                        'code_acteur' => $acteur['code_acteur'],
                        'secteur_id' => $acteur['secteur_id'] ?? null,
                        'is_active' => true,
                    ]);
                }
        
                // Financements
                foreach ($step6['financements'] as $fin) {
                    Financer::create([
                        'code_projet' => $codeProjet,
                        'code_acteur' => $fin['bailleur'],
                        'montant_finance' => $fin['montant'],
                        'devise' => $fin['devise'],
                        'financement_local' => in_array(strtolower($fin['local']), ['oui', '1', 'true']),
                        'commentaire' => $fin['commentaire'] ?? null,
                        'FinancementType' => $step6['type_financement'],
                        'is_active' => true,
                    ]);
                }
        
                // Documents
                $uploadPath = public_path('data/documentProjet/' . $codeProjet);
                File::ensureDirectoryExists($uploadPath);
        
                foreach ($step7['fichiers'] ?? [] as $file) {
                    $filename = Str::slug(pathinfo($file['original_name'], PATHINFO_FILENAME)) . '_' . time() . '.' . $file['extension'];
                    File::copy(storage_path('app/' . $file['storage_path']), $uploadPath . '/' . $filename);
        
                    ProjetDocument::create([
                        'file_name' => $file['original_name'],
                        'file_path' => 'data/documentProjet/' . $codeProjet . '/' . $filename,
                        'file_type' => $file['mime_type'],
                        'file_size' => $file['size'],
                        'uploaded_at' => now(),
                        'code_projet' => $codeProjet,
                    ]);
                }
        
                // Création étude
                $codeEtude = $this->genererCodeEtude(
                    session('pays_selectionne'),
                    session('projet_selectionne')
                );
        
                EtudeProject::create([
                    'codeEtudeProjets' => $codeEtude,
                    'code_projet' => $codeProjet,
                    'valider' => false,
                    'is_deleted' => false,
                ]);
        
                DB::commit();
        
                // 🔁 Nettoyage
                $this->nettoyerSessionsEtFichiers();
        
                return response()->json([
                    'success' => true,
                    'code_projet' => $codeProjet,
                    'code_etude' => $codeEtude,
                    'message' => 'Demande effectuée avec succes.',
                ]);
        
            } catch (\Exception $e) {
                DB::rollBack();
        
                \Log::error('Erreur lors de la finalisation du projet', [
                    'exception' => $e->getMessage(),
                    
                    'trace' => $e->getTraceAsString()
                ]);
        
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la finalisation du projet.'
                ], 500);
            }
        }
        
        private function nettoyerSessionsEtFichiers()
        {
            foreach (session('form_step7.fichiers', []) as $file) {
                $fullPath = storage_path('app/' . $file['storage_path']);
                if (file_exists($fullPath)) {
                    @unlink($fullPath);
                }
            }
            
            session()->forget([
                'form_step1', 'form_step2', 'form_step3',
                'form_step4', 'form_step5', 'form_step6', 'form_step7',
                'code_localisation'
            ]);
        }
        
        private function genererCodeProjet($codeSousDomaine, $typeFinancement, $codeLocalisation, $dateDebut)
        {
            $paysAlpha3 = session('pays_selectionne');
            $groupeProjet = session('projet_selectionne');
            $date = Carbon::parse($dateDebut);

            $codeLocalisation = substr($codeLocalisation, 0, 4);

            // Génère la partie fixe du code projet (jusqu'à l’année)
            $prefix = sprintf('%s%s%s_%s_%s_%s',
                strtoupper($paysAlpha3),
                strtoupper($groupeProjet),
                $typeFinancement,
                strtoupper($codeLocalisation),
                strtoupper($codeSousDomaine),
                $date->format('Y')
            );

            // Compte les projets déjà existants avec ce préfixe
            $ordre = Projet::where('code_projet', 'like', $prefix . '_%')->count() + 1;

            // Ajoute le suffixe (ordre)
            return $prefix . '_' . str_pad($ordre, 2, '0', STR_PAD_LEFT);
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
            $approver = Approbateur::where('code_acteur', $user->approbateur->code_acteur)->first();

            // Vérifier que l'approbateur existe
            if (!$approver) {
                return redirect()->route('projets.index')->with('error', 'Vous devez être un approbateur pour accéder à cette page.');
            }

            // Récupérer les projets qui n'ont pas encore été approuvés par l'approbateur ou qui ont été approuvés par un approbateur précédent
            $projects = EtudeProject::where('valider', 0)
            ->get();
            
            return view('etudes_projets.validations', compact('ecran',  'projects'));
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
            try {
                $approvalHistory = ProjetApprobation::with([
                    'etude',
                    'etude.projet',
                    'approbateur.acteur',
                    'statutValidation'
                ])
                ->whereIn('statut_validation_id', [2, 3]) // Validé ou refusé
                ->orderByDesc('approved_at')
                ->get();
        
                return view('etudes_projets.historiqueApp', compact('approvalHistory'));
        
            } catch (Exception $e) {
                Log::error("Erreur chargement historique approbation : " . $e->getMessage());
                return back()->with('error', 'Impossible de charger l’historique des validations.');
            }
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




