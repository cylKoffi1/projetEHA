<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRenforcementRequest;
use App\Http\Requests\UpdateRenforcementRequest;
use App\Models\Acteur;
use App\Models\ActionMener;
use App\Models\ActionType;
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
use App\Models\FamilleDomaine;
use App\Models\Financer;
use App\Models\Modalite;
use App\Models\Posseder;
use App\Models\ProjetApprobation;
use App\Models\ProjetDocument;
use App\Models\projets_natureTravaux;
use App\Models\ProjetStatut;
use App\Models\StatutOperation;
use App\Models\TypeFinancement;
use App\Models\UniteDerivee;
use App\Services\FileProcService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\FacadesLog;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class EtudeProjet extends Controller
{
        //////////////////////////////////ETUDE DE PROJET///////////////////////////////////
        public function createNaissance(Request $request)
        {
            $ecran   = Ecran::find($request->input('ecran_id'));
            // Générer le code par défaut pour Public (1)
            $generatedCodeProjet = $this->generateProjectCode('CI', 'EHA', 1); // 1 pour Public
            $paysSelectionne = session('pays_selectionne');
            $groupeSelectionne = session('projet_selectionne');
            $user = auth()->user();
            $groupe = GroupeUtilisateur::where('code', $user->groupe_utilisateur_id)->first();

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

            $infrastructures = Infrastructure::where('code_pays', $paysSelectionne)
            ->get();

            $acteurs = Acteur::where('type_acteur', '=', 'etp')
            ->where('code_pays', $paysSelectionne)
            ->get();
            $familleInfrastructures = FamilleInfrastructure::all();
            $codes = ['NEU', 'ARB', 'AFQ', 'ONU', 'ZAF'];

            $bailleurActeurs = Acteur::whereIn('code_pays', ['NEU', 'ARB', 'AFQ', 'ONU', 'ZAF', $paysSelectionne])->get();

            $Devises = Pays::where('alpha3', $paysSelectionne)->get();

            $unitesDerivees = UniteDerivee::with('uniteBase')
            ->get()
            ->groupBy('id_unite_base');

            return view('etudes_projets.naissance', compact('ecran','unitesDerivees', 'familleInfrastructures','typeFinancements','Devises', 'bailleurActeurs', 'infrastructures', 'acteurs','TypeCaracteristiques','deviseCouts','acteurRepres','Pieceidentite','NaturesTravaux', 'formeJuridiques','SituationMatrimoniales','genres', 'SecteurActivites', 'Pays','SousDomaines','Domaines','GroupeProjets','ecran','generatedCodeProjet','natures','groupeSelectionne', 'tousPays', 'devises','actionMener'));
        }

        public function getInfrastructures($domaine, $sousDomaine, $pays)
        {
            $infras = Infrastructure::where('code_pays', $pays)
                ->whereHas('familleDomaine', function ($q) use ($domaine, $sousDomaine) {
                    $q->where('code_domaine', $domaine)
                    ->where('code_sdomaine', $sousDomaine);
                })
                ->get(['code', 'libelle']);

            return response()->json($infras); // ✅ Important : retourner du JSON
        }

        public function getLocaliteInfrastructure($code)
        {
            $infra = Infrastructure::where('code', $code)
                ->with('localisation')
                ->first();

            if (!$infra || !$infra->localisation) {
                return response()->json(null, 404);
            }

            return response()->json([
                'id' => $infra->localisation->id,
                'code_rattachement' => $infra->localisation->code_rattachement,
                'libelle' => $infra->localisation->libelle,
                'niveau' => $infra->localisation->niveau,
                'code_decoupage' => $infra->localisation->code_decoupage,
                'libelle_decoupage' => $infra->localisation->libelle_decoupage,
            ]);
        }

        public function getBailleursParStatutLocal(Request $request)
        {
            $pays = session('pays_selectionne');
            $local = $request->input('local');

            $query = Acteur::query();

            if ($local == 1) {
                $query->whereIn('code_pays', [ $pays, 'NEU']);
            } else {
                $query->whereIn('code_pays', [ 'ARB', 'AFQ', 'ONU', 'ZAF']);
            }

            $bailleurs = $query->get(['code_acteur', 'libelle_court', 'libelle_long']);

            return response()->json($bailleurs);
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
            $localites = DB::table('localites_pays')
                ->join('decoupage_administratif', 'localites_pays.code_decoupage', '=', 'decoupage_administratif.code_decoupage')
                ->where('localites_pays.id_pays', $paysCode)
                ->orderBy('localites_pays.libelle', 'asc')
                ->select(
                    'localites_pays.id',
                    'localites_pays.libelle',
                    'localites_pays.code_rattachement',
                    'localites_pays.id_pays',
                    'decoupage_administratif.libelle_decoupage'
                )
                ->get();

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
            $codeProjet = session('projet_selectionne');

            // Vérifie que la session contient bien un projet
            if (!$codeProjet) {
                return response()->json(['error' => 'Aucun projet sélectionné.'], 400);
            }

            $familles = FamilleDomaine::join('familleinfrastructure', 'famille_domaine.code_Ssys', '=', 'familleinfrastructure.code_Ssys')
                ->where('famille_domaine.code_domaine', $codeDomaine)
                ->whereIn('famille_domaine.code_groupe_projet', [$codeProjet])
                ->select('familleinfrastructure.*')
                ->get();

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
        public function historiqueApp(Request $request)
        {
            try {
                $country = session('pays_selectionne');
                $group = session('projet_selectionne');
                $ecran   = Ecran::find($request->input('ecran_id'));
                $approvalHistory = ProjetApprobation::with([
                        'etude',
                        'etude.projet',
                        'approbateur.acteur',
                        'statutValidation'
                    ])
                    ->whereIn('statut_validation_id', [2, 3]) // Validé ou refusé
                    ->whereHas('etude.projet', function ($query) use ($country, $group) {
                        $query->where('code_projet', 'like', $country . $group . '%');
                    })
                    ->orderByDesc('approved_at')
                    ->get();

                return view('etudes_projets.historiqueApp', compact('ecran', 'approvalHistory'));

            } catch (Exception $e) {
                Log::error("Erreur chargement historique approbation : " . $e->getMessage());
                return back()->with('error', 'Impossible de charger l’historique des validations.');
            }
        }










    /////////////////////////////RENFORCEMENT DES CAPACITE//////////////////////

    public function indexRenfo(Request $request)
    {
        $country = session('pays_selectionne');
        $group   = session('projet_selectionne');
        $ecran   = Ecran::find($request->input('ecran_id'));

        Log::info("📌 indexRenfo appelé", [
            'country' => $country,
            'group'   => $group,
            'ecran_id'=> $request->input('ecran_id'),
            'statutFilter' => $request->query('statut')
        ]);

        $statutFilter = $request->query('statut');

        $query = Renforcement::with(['beneficiaires','projets','modalite','actionType','statut', 'fichiers'])
            ->where('code_renforcement', 'like', $country.'_'.$group.'%')
            ->when($statutFilter, fn($q) => $q->where('statutId', $statutFilter))
            ->orderByDesc('date_debut');

        $renforcements = $query->paginate(25)->withQueryString();

        Log::info("✅ indexRenfo résultats paginés", [
            'total' => $renforcements->total()
        ]);

        $projets        = Projet::where('code_projet', 'like', $country.$group.'%')->get();
        $beneficiaires  = Acteur::where('code_pays', $country)->get();
        $modalites      = Modalite::orderBy('Libelle')->get();
        $actionTypes    = ActionType::orderBy('Libelle')->get();
        $statuts        = StatutOperation::orderBy('Libelle')->get();

        $stats = Renforcement::select('statutId', DB::raw('COUNT(*) as total'))
            ->where('code_renforcement', 'like', $country.'_'.$group.'%')
            ->groupBy('statutId')
            ->pluck('total', 'statutId');

        return view('etudes_projets.renforcement', compact(
            'ecran',
            'renforcements',
            'projets',
            'beneficiaires',
            'modalites',
            'actionTypes',
            'statuts',
            'stats',
            'statutFilter'
        ));
    }

    public function storeRenfo(StoreRenforcementRequest $request)
    {
        DB::beginTransaction();
        try {
            Log::info("📌 storeRenfo début", $request->all());

            $country = session('pays_selectionne');
            $group   = session('projet_selectionne');
            $code    = Renforcement::generateCodeRenforcement($country, $group);

            $renfo = Renforcement::create([
                'code_renforcement'        => $code,
                'titre'                    => $request->titre,
                'description'              => $request->description,
                'actionTypeId'             => $request->actionTypeId,
                'thematique'               => $request->thematique,
                'organisme'                => $request->organisme,
                'lieu'                     => $request->lieu,
                'modaliteId'               => $request->modaliteId,
                'nb_participants_prev'     => $request->nb_participants_prev,
                'nb_participants_effectif' => $request->nb_participants_effectif,
                'cout_previsionnel'        => $request->cout_previsionnel,
                'cout_reel'                => $request->cout_reel,
                'source_financement'       => $request->source_financement,
                'pretest_moy'              => $request->pretest_moy,
                'posttest_moy'             => $request->posttest_moy,
                'statutId'                 => 'plan',
                'date_debut'               => $request->date_debut,
                'date_fin'                 => $request->date_fin,
            ]);

            Log::info("✅ Renforcement créé", ['code' => $renfo->code_renforcement]);

            $renfo->beneficiaires()->sync($request->beneficiaires);
            $renfo->projets()->sync($request->projets ?? []);

            // === Pièces jointes -> GridFS ===
            // On stocke CHAQUE fichier dans GridFS et on crée une ligne dans `fichiers`
            // owner_type = 'Renforcement' ; owner_id = code renforcement
            if ($request->hasFile('pieces')) {
                foreach ($request->file('pieces') as $file) {
                    if (!$file || !$file->isValid()) continue;

                    $res = app(\App\Services\FileProcService::class)->handle([
                        'owner_type'  => 'Renforcement',
                        'owner_id'    => (string)$code, // ou (string)$renfo->code_renforcement en update
                        'categorie'   => 'DOC_RENFO',
                        'file'        => $file,                       // UploadedFile
                        'uploaded_by' => optional($request->user())->id,
                        // optionnel: 'metadata' => ['context' => 'renfo-piece'],
                    ]);

                    // $res contient typiquement: id (fichiers.id), gridfs_id, filename, mime, size
                    Log::info("📦 Pièce jointe enregistrée (GridFS)", [
                        'renfo'     => (string)$code,
                        'fichierId' => $res['id'] ?? null,
                        'mime'      => $res['mime'] ?? null,
                        'size'      => $res['size'] ?? null,
                    ]);
                }
            }


            DB::commit();
            return response()->json([
                'ok' => true,
                'message' => 'Renforcement créé avec succès.',
                'data' => ['code' => $renfo->code_renforcement]
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("❌ Erreur storeRenfo", ['exception' => $e]);
            return response()->json([
                'ok' => false,
                'message' => 'Erreur lors de la création.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateRenfo(Request $request, string $code)
    {
        DB::beginTransaction();
        try {
            Log::info("📌 updateRenfo début", ['code' => $code, 'payload' => $request->all()]);

            $renfo = Renforcement::where('code_renforcement',$code)->firstOrFail();

            $renfo->update($request->only([
                'titre','description','actionTypeId','thematique','organisme','lieu',
                'modaliteId','nb_participants_prev','nb_participants_effectif',
                'cout_previsionnel','cout_reel','source_financement',
                'pretest_moy','posttest_moy','date_debut','date_fin'
            ]));

            $renfo->beneficiaires()->sync($request->beneficiaires);
            $renfo->projets()->sync($request->projets ?? []);
            // === Pièces jointes -> GridFS (update) ===
            if ($request->hasFile('pieces')) {
                foreach ($request->file('pieces') as $file) {
                    if (!$file || !$file->isValid()) continue;

                    $res = app(\App\Services\FileProcService::class)->handle([
                        'owner_type'  => 'Renforcement',
                        'owner_id'    => (string)$renfo->code_renforcement,
                        'categorie'   => 'DOC_RENFO',
                        'file'        => $file,
                        'uploaded_by' => optional($request->user())->id,
                    ]);

                    \Log::info("📦 Pièce jointe ajoutée (GridFS/update)", [
                        'renfo'     => $renfo->code_renforcement,
                        'fichierId' => $res['id'] ?? null,
                        'mime'      => $res['mime'] ?? null,
                        'size'      => $res['size'] ?? null,
                    ]);
                }
            }


            DB::commit();
            Log::info("✅ Renforcement mis à jour", ['code' => $renfo->code_renforcement]);

            return response()->json([
                'ok' => true,
                'message' => 'Renforcement modifié avec succès.',
                'data' => ['code' => $renfo->code_renforcement]
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("❌ Erreur updateRenfo", ['exception' => $e]);
            return response()->json([
                'ok' => false,
                'message' => 'Erreur lors de la modification.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateRenfoStatus(Request $request, string $code)
    {
        $request->validate([
            'statutId' => ['required','in:plan,enc,achv,annul,repr'],
            'motif_annulation' => ['nullable','string','max:2000'],
        ]);

        Log::info("📌 updateRenfoStatus début", ['code' => $code, 'payload' => $request->all()]);

        $renfo = \App\Models\Renforcement::where('code_renforcement',$code)->firstOrFail();

        $payload = ['statutId' => $request->statutId];
        if ($request->statutId === 'annul') {
            $payload['motif_annulation'] = $request->motif_annulation ?? 'Non précisé';
        } else {
            $payload['motif_annulation'] = null;
        }

        $renfo->update($payload);

        Log::info("✅ Statut mis à jour", ['code' => $code, 'statutId' => $renfo->statutId]);

        return response()->json([
            'ok' => true,
            'message' => 'Statut mis à jour.',
            'data' => ['statutId' => $renfo->statutId]
        ]);
    }

    public function destroyRenfo(string $code)
    {
        try {
            Log::warning("📌 destroyRenfo demandé", ['code' => $code]);

            $renfo = \App\Models\Renforcement::where('code_renforcement',$code)->firstOrFail();
            $renfo->delete();

            Log::info("✅ Renforcement supprimé", ['code' => $code]);

            return response()->json([
                'ok' => true,
                'message' => 'Renforcement supprimé avec succès.'
            ]);
        } catch (\Throwable $e) {
            Log::error("❌ Erreur destroyRenfo", ['code' => $code, 'exception' => $e]);
            return response()->json([
                'ok' => false,
                'message' => 'Erreur lors de la suppression.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    private function parseMoney(?string $raw): ?float
    {
        if ($raw === null || $raw === '') return null;
        // retire espaces & NBSP, remplace virgule par point
        $v = str_replace(["\xC2\xA0",' '], '', $raw);
        $v = str_replace(',', '.', $v);
        return is_numeric($v) ? (float)$v : null;
    }

    ////////////////////////////////////ACTIVITE CONNEXE//////////////////////////////
    public function activite(Request $request)
    {
        $country = (string)session('pays_selectionne');
        $group   = (string)session('projet_selectionne');

        $ecran   = Ecran::find($request->integer('ecran_id'));

        $travaux = TravauxConnexes::with(['typeTravaux','projet'])
            ->where('codeActivite', 'like', "{$country}_{$group}%")
            ->orderByDesc('date_debut_previsionnelle')
            ->get();

        $projets      = Projet::where('code_projet', 'like', $country.$group.'%')->get();
        $typesTravaux = TypeTravauxConnexes::orderBy('libelle')->get();

        return view('etudes_projets.activite', compact('ecran','travaux','projets','typesTravaux'));
    }

    private function normalizeCout(Request $request): void
    {
        // Accepte "12 345", "12.345,67", etc. et convertit en décimal standard "12345.67"
        $raw = (string) $request->input('cout_projet', '');
        $raw = str_replace([' ', "\u{00A0}"], '', $raw); // espaces & espaces insécables
        // remplace la virgule décimale par un point si nécessaire
        if (preg_match('/,\d{1,2}$/', $raw)) {
            $raw = str_replace('.', '', $raw); // enlever séparateurs de milliers
            $raw = str_replace(',', '.', $raw); // virgule -> point
        }
        $raw = preg_replace('/[^\d.]/', '', $raw);
        $request->merge(['cout_projet' => $raw === '' ? null : $raw]);
    }

    // Store
    public function storeConnexe(Request $request)
    {
        $this->normalizeCout($request);

        // Validation
        $request->validate([
            'code_projet'                => ['required','exists:projets,code_projet'],
            'type_travaux_id'            => ['required','exists:types_travaux_connexes,id'],
            'cout_projet'                => ['required','numeric','min:0'],
            'date_debut_previsionnelle'  => ['required','date'],
            'date_fin_previsionnelle'    => ['required','date','after_or_equal:date_debut_previsionnelle'],
            'date_debut_effective'       => ['nullable','date'],
            'date_fin_effective'         => ['nullable','date','after_or_equal:date_debut_effective'],
            'commentaire'                => ['nullable','string','max:2000'],
        ], [], [
            'code_projet' => 'projet',
            'type_travaux_id' => 'type de travaux',
            'cout_projet' => 'coût du projet',
            'date_debut_previsionnelle' => 'début prévisionnel',
            'date_fin_previsionnelle' => 'fin prévisionnel',
            'date_debut_effective' => 'début effectif',
            'date_fin_effective' => 'fin effectif',
        ]);

        try {
            $country = (string)session('pays_selectionne');
            $group   = (string)session('projet_selectionne');
            $code    = TravauxConnexes::generateCodeTravauxConnexe($country, $group);

            TravauxConnexes::create([
                'codeActivite'               => $code,
                'code_projet'                => $request->code_projet,
                'type_travaux_id'            => $request->type_travaux_id,
                'cout_projet'                => $request->cout_projet, // mutator normalise
                'date_debut_previsionnelle'  => $request->date_debut_previsionnelle,
                'date_fin_previsionnelle'    => $request->date_fin_previsionnelle,
                'date_debut_effective'       => $request->date_debut_effective,
                'date_fin_effective'         => $request->date_fin_effective,
                'commentaire'                => $request->commentaire,
            ]);

            Log::info('✅ Activité connexe créée', [
                'code' => $code,
                'user_id' => optional($request->user())->id,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'ok' => true,
                'message' => 'Travail connexe enregistré avec succès.',
                'data' => ['code' => $code]
            ]);
        } catch (\Throwable $e) {
            Log::error('❌ storeConnexe erreur', [
                'err' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', "Erreur lors de l'enregistrement : ".$e->getMessage())
                         ->withInput();
        }
    }

    // Update (id = codeActivite)
    public function updateConnexe(Request $request, string $id)
    {
        $this->normalizeCout($request);

        $request->validate([
            'cout_projet' => ['required','numeric','min:0'],
            'type_travaux_id'            => ['required','exists:types_travaux_connexes,id'],
            'cout_projet'                => ['required','numeric','min:0'],
            'date_debut_previsionnelle'  => ['required','date'],
            'date_fin_previsionnelle'    => ['required','date','after_or_equal:date_debut_previsionnelle'],
            'date_debut_effective'       => ['nullable','date'],
            'date_fin_effective'         => ['nullable','date','after_or_equal:date_debut_effective'],
            'commentaire'                => ['nullable','string','max:2000'],
        ]);

        try {
            $row = TravauxConnexes::where('codeActivite', $id)->firstOrFail();

            $row->update([
                'type_travaux_id'            => $request->type_travaux_id,
                'cout_projet'                => $request->cout_projet, // mutator
                'date_debut_previsionnelle'  => $request->date_debut_previsionnelle,
                'date_fin_previsionnelle'    => $request->date_fin_previsionnelle,
                'date_debut_effective'       => $request->date_debut_effective,
                'date_fin_effective'         => $request->date_fin_effective,
                'commentaire'                => $request->commentaire,
            ]);

            Log::info('✅ Activité connexe modifiée', [
                'code' => $id,
                'user_id' => optional($request->user())->id,
                'ip' => $request->ip(),
            ]);

            return $request->expectsJson()
            ? response()->json(['ok'=>true,'message'=>'Travail connexe modifié avec succès.','data'=>['code'=>$row->codeActivite]])
            : redirect()->route('activite.index')->with('success','Travail connexe modifié avec succès.');

        } catch (\Throwable $e) {
            Log::error('❌ updateConnexe erreur', ['code'=>$id, 'err'=>$e->getMessage()]);
            if ($request->expectsJson()) {
                return response()->json(['ok'=>false,'message'=>"Erreur : ".$e->getMessage()], 422);
            }
            return back()->with('error', "Erreur lors de la modification : ".$e->getMessage())->withInput();
        }

    }

    // Delete JSON
    public function deleteActivite(string $id)
    {
        try {
            $row = TravauxConnexes::where('codeActivite', $id)->firstOrFail();
            $row->delete();

            Log::warning('🗑️ Activité connexe supprimée', ['code' => $id]);

            return response()->json([
                'ok' => true,
                'message' => 'Activité connexe supprimée.'
            ]);
        } catch (\Throwable $e) {
            Log::error('❌ deleteActivite erreur', [
                'code' => $id,
                'err' => $e->getMessage(),
            ]);
            return response()->json(['ok' => false, 'message' => "Erreur : ".$e->getMessage()], 500);
        }
    }
    ///////////////MODELISER
    public function modelisation(Request $request)
    {
        $ecran = Ecran::find($request->input('ecran_id'));
        return view('etudes_projets.modeliser', compact('ecran'));
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
                Log::info('Données correctement stockées en session', [
                    'session_data' => session('form_step1')
                ]);
            } else {
                Log::error('Échec du stockage en session');
            }

            return response()->json(['success' => true]);

        } catch (\Throwable $e) {
            Log::error('Erreur lors de l\'enregistrement des données step1', [
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
                Log::info('Étape 2 stockée en session avec succès.', [
                    'session_data' => session('form_step2')
                ]);
            } else {
                Log::error('Échec de la sauvegarde en session (étape 2).');
            }

            return response()->json([
                'success' => true,
                'message' => 'Étape 2 enregistrée temporairement en session.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Erreur lors de l\'enregistrement de l\'étape 2', [
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
                Log::info('Étape 3 stockée en session avec succès.', [
                    'session_data' => session('form_step3')
                ]);
            } else {
                Log::error('Échec de la sauvegarde en session (étape 3).');
            }

            return response()->json([
                'success' => true,
                'message' => 'Étape 3 enregistrée temporairement en session.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Erreur lors de l\'enregistrement de l\'étape 3', [
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
                'acteurs' => 'required|array|min:1',
                'acteurs.*.code_acteur' => 'required|exists:acteur,code_acteur',
                'acteurs.*.secteur_id' => 'nullable|string',
                'type_ouvrage' => 'nullable|string',
                'priveMoeType' => 'nullable|string',
                'sectActivEntMoe' => 'nullable|string',
                'descriptionMoe' => 'nullable|string',
            ]);

            $data = $request->only([
                'type_ouvrage', 'priveMoeType', 'descriptionMoe', 'acteurs'
            ]);

            session(['form_step4' => $data]);

            if (session()->has('form_step4')) {
                Log::info('Étape 4 stockée en session avec succès.', [
                    'session_data' => session('form_step4')
                ]);
            } else {
                Log::error('Échec du stockage en session (étape 4).');
            }

            return response()->json([
                'success' => true,
                'message' => 'Étape 4 enregistrée temporairement en session.',
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'enregistrement de l\'étape 4', [
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
                Log::info('Étape 5 stockée en session avec succès.', [
                    'session_data' => session('form_step5')
                ]);
            } else {
                Log::error('Échec du stockage en session (étape 5).');
            }

            return response()->json([
                'success' => true,
                'message' => 'Étape 5 enregistrée temporairement en session.',
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'enregistrement de l\'étape 5', [
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
                Log::info('Étape 6 stockée en session avec succès.', [
                    'session_data' => session('form_step6')
                ]);
            } else {
                Log::error('Échec du stockage en session (étape 6).');
            }

            return response()->json([
                'success' => true,
                'message' => 'Étape 6 enregistrée temporairement en session.',
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'enregistrement de l\'étape 6', [
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
        try
        {
                // 1) Validation + journalisation d’entrée
                Log::info('[Step7] Début upload', [
                    'has_files' => $request->hasFile('fichiers'),
                    'count'     => $request->hasFile('fichiers') ? count($request->file('fichiers')) : 0,
                ]);

                $request->validate([
                    'fichiers'   => 'required|array|min:1',
                    'fichiers.*' => 'required|file|max:102400|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,zip,rar,dwg,dxf,ifc',
                ]);

                $uploadedFiles = [];
                if ($request->hasFile('fichiers')) {
                    foreach ($request->file('fichiers') as $idx => $file) {
                        if (!$file->isValid()) {
                            Log::warning('[Step7] Fichier invalide, on saute', [
                                'index' => $idx,
                                'name'  => $file->getClientOriginalName(),
                                'error' => $file->getError(),
                            ]);
                            continue;
                        }

                        try {
                            // Stockage temporaire
                            $path = $file->store('temp/projet', 'local');

                            $uploadedFiles[] = [
                                'original_name' => $file->getClientOriginalName(),
                                'extension'     => $file->getClientOriginalExtension(),
                                'mime_type'     => $file->getClientMimeType(),
                                'size'          => $file->getSize(),
                                'storage_path'  => $path,
                            ];

                            Log::info('[Step7] Fichier stocké temporairement', [
                                'index' => $idx,
                                'path'  => $path,
                                'name'  => $file->getClientOriginalName(),
                                'size'  => $file->getSize(),
                                'mime'  => $file->getClientMimeType(),
                            ]);
                        } catch (Throwable $th) {
                            Log::error('[Step7] Échec stockage fichier', [
                                'index' => $idx,
                                'name'  => $file->getClientOriginalName(),
                                'ex'    => $th->getMessage(),
                            ]);
                        }
                    }
                }

                session(['form_step7' => ['fichiers' => $uploadedFiles]]);

                Log::info('[Step7] Fichiers stockés en session', [
                    'count' => count($uploadedFiles),
                    'files' => $uploadedFiles,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => count($uploadedFiles) . ' fichier(s) enregistré(s)',
                ]);

        } catch (Throwable $e) {
            Log::error('[Step7] Erreur globale', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => "Une erreur s'est produite lors de l’upload.",
            ], 500);
        }
    }

    public function finaliserProjet()
    {
        return DB::transaction(function () {

            try {
                Log::info('[Finaliser] Début finalisation');

                // 0) Récup sessions + garde-fous + logs
                $step1 = session('form_step1', []);
                $step2 = session('form_step2', []);
                $step3 = session('form_step3', []);
                $step4 = session('form_step4', []);
                $step5 = session('form_step5', []);
                $step6 = session('form_step6', []);
                $step7 = session('form_step7', []);

                Log::info('[Finaliser] États de session', [
                    'has_step1' => !empty($step1),
                    'has_step2' => !empty($step2),
                    'has_step3' => !empty($step3),
                    'has_step4' => !empty($step4),
                    'has_step5' => !empty($step5),
                    'has_step6' => !empty($step6),
                    'has_step7' => !empty($step7),
                ]);

                // Checks essentiels
                foreach ([
                    'form_step1' => $step1,
                    'form_step2' => $step2,
                    'form_step6' => $step6,
                ] as $k => $v) {
                    if (empty($v)) {
                        Log::error('[Finaliser] Session manquante', ['step' => $k]);
                        throw new \Exception("Données manquantes ($k).");
                    }
                }

                $codeLocalisation = collect($step2['localites'] ?? [])
                    ->pluck('code_rattachement')->filter()->first();

                Log::info('[Finaliser] Code localisation', [
                    'codeLocalisation' => $codeLocalisation,
                ]);

                // 1) Générer code projet
                $codeProjet = $this->genererCodeProjet(
                    $step1['code_sous_domaine'] ?? null,
                    $step6['type_financement'] ?? null,
                    $codeLocalisation,
                    $step1['date_demarrage_prevue'] ?? null,
                );

                if (!$codeProjet) {
                    throw new \Exception('Échec génération code projet');
                }

                Log::info('[Finaliser] Code projet généré', ['code_projet' => $codeProjet]);

                // 2) Projet principal
                $projet = Projet::create([
                    'code_projet'           => $codeProjet,
                    'libelle_projet'        => $step1['libelle_projet'] ?? null,
                    'commentaire'           => $step1['commentaire'] ?? null,
                    'code_sous_domaine'     => $step1['code_sous_domaine'] ?? null,
                    'date_demarrage_prevue' => $step1['date_demarrage_prevue'] ?? null,
                    'date_fin_prevue'       => $step1['date_fin_prevue'] ?? null,
                    'cout_projet'           => $step1['cout_projet'] ?? null,
                    'code_devise'           => $step1['code_devise'] ?? null,
                    'code_alpha3_pays'      => $step1['code_pays'] ?? null,
                ]);

                Log::info('[Finaliser] Projet créé', ['id' => $projet->id, 'code' => $codeProjet]);

                projets_natureTravaux::create([
                    'code_projet' => $codeProjet,
                    'code_nature' => $step1['code_nature'] ?? null,
                    'date'        => now(),
                ]);

                ProjetStatut::create([
                    'code_projet' => $codeProjet,
                    'type_statut' => 1,
                    'date_statut' => now(),
                ]);

                $codePays = session('pays_selectionne');

                // 3) Localisations
                foreach (($step2['localites'] ?? []) as $idx => $loc) {
                    Log::info('[Finaliser] Localisation', ['index' => $idx, 'loc' => $loc]);

                    ProjetLocalisation::create([
                        'code_projet'  => $codeProjet,
                        'code_localite'=> $loc['code_rattachement'] ?? null,
                        'niveau'       => $loc['niveau'] ?? null,
                        'decoupage'    => $loc['code_decoupage'] ?? null,
                        'pays_code'    => $step1['code_pays'] ?? null,
                    ]);
                }

                // 4) Infrastructures (+ caractéristiques)
                if (!empty($step2['infrastructures'])) {
                    foreach ($step2['infrastructures'] as $i => $infra) {
                        Log::info('[Finaliser] Infra entrée', ['i' => $i, 'infra' => $infra]);

                        $codeFamille = $infra['famille_code'] ?? null;
                        if (!$codeFamille) {
                            throw new \Exception("Famille d'infrastructure manquante (infra #$i).");
                        }

                        $infraDB = Infrastructure::where('code', $infra['code'] ?? '')
                            ->where('libelle', $infra['libelle'] ?? '')
                            ->first();

                        if (!$infraDB) {
                            $famille = FamilleInfrastructure::where('code_Ssys', $codeFamille)->first();
                            if (!$famille) {
                                throw new \Exception("Famille introuvable ($codeFamille) pour infra #$i.");
                            }

                            $familleId = $famille->idFamille;
                            Log::info('[Finaliser] Famille trouvée', ['famille_id' => $familleId]);

                            $prefix = ($codePays ?? '') . $codeFamille;
                            $last   = Infrastructure::where('code', 'like', $prefix.'%')->orderByDesc('code')->first();
                            $nextNumber = $last ? ((int) substr($last->code, strlen($prefix))) + 1 : 1;
                            $codeInfra  = $prefix . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

                            $infraDB = Infrastructure::create([
                                'code'               => $codeInfra,
                                'libelle'            => $infra['libelle'] ?? ("Infra_$codeInfra"),
                                'code_Ssys'          => $codeFamille,
                                'code_groupe_projet' => session('projet_selectionne'),
                                'code_pays'          => $codePays,
                                'code_localite'      => $infra['localisation_id'] ?? null,
                                'date_operation'     => now(),
                                'IsOver'             => false,
                            ]);

                            Log::info('[Finaliser] Infra créée', ['code' => $infraDB->code, 'id' => $infraDB->id]);
                        } else {
                            Log::info('[Finaliser] Infra existante', ['code' => $infraDB->code, 'id' => $infraDB->id]);
                        }

                        $projetInfra = ProjetInfrastructure::create([
                            'idInfrastructure' => $infraDB->id,
                            'code_projet'      => $codeProjet,
                            'localisation_id'  => $infra['localisation_id'] ?? $codeLocalisation,
                        ]);

                        Log::info('[Finaliser] Lien Projet-Infrastructure créé', ['pi_id' => $projetInfra->id]);

                        foreach (($infra['caracteristiques'] ?? []) as $index => $carac) {
                            if (!isset($carac['id'], $carac['valeur']) || $carac['valeur'] === '') {
                                Log::warning('[Finaliser] Caractéristique ignorée', ['i' => $i, 'carac' => $carac]);
                                continue;
                            }

                            ValeurCaracteristique::create([
                                'infrastructure_code' => $infraDB->code,
                                'idCaracteristique'   => $carac['id'],
                                'idUnite'             => $carac['unite_id'] ?? null,
                                'valeur'              => $carac['valeur'],
                                'ordre'               => $index + 1,
                            ]);
                        }
                    }
                }

                // 5) Actions à mener
                foreach (($step3['actions'] ?? []) as $k => $action) {
                    Log::info('[Finaliser] Action', ['k' => $k, 'action' => $action]);

                    $created = ProjetActionAMener::create([
                        'code_projet'       => $codeProjet,
                        'Num_ordre'         => $action['ordre'] ?? ($k+1),
                        'Action_mener'      => $action['action_code'] ?? null,
                        'Quantite'          => $action['quantite'] ?? null,
                        'Infrastrucrues_id' => $action['infrastructure_code'] ?? 0,
                    ]);

                    foreach (($action['beneficiaires'] ?? []) as $b) {
                        $type = $b['type'] ?? '';
                        Log::info('[Finaliser] Bénéficiaire', ['type' => $type, 'b' => $b]);

                        match ($type) {
                            'acteur' => Beneficier::create([
                                'code_projet' => $codeProjet,
                                'code_acteur' => $b['code'] ?? null,
                                'is_active'   => true,
                            ]),
                            'localite' => Profiter::create([
                                'code_projet'     => $codeProjet,
                                'code_pays'       => $b['codePays'] ?? null,
                                'code_rattachement'=> $b['codeRattachement'] ?? null,
                            ]),
                            'infrastructure' => Jouir::create([
                                'code_projet'       => $codeProjet,
                                'code_Infrastructure'=> $b['code'] ?? null,
                            ]),
                            default => Log::warning('[Finaliser] Type bénéficiaire inconnu', ['b' => $b]),
                        };
                    }
                }

                // 6) Maîtres d’Ouvrage
                foreach (($step4['acteurs'] ?? []) as $idx => $acteur) {
                    Log::info('[Finaliser] MOA', ['idx' => $idx, 'acteur' => $acteur]);

                    Posseder::create([
                        'code_projet' => $codeProjet,
                        'code_acteur' => $acteur['code_acteur'] ?? null,
                        'secteur_id'  => $acteur['secteur_code'] ?? null,
                        'isAssistant' => !empty($acteur['is_assistant']),
                        'date'        => now(),
                        'is_active'   => true,
                    ]);
                }

                // 7) Maîtres d’œuvre
                foreach (($step5['acteurs'] ?? []) as $idx => $acteur) {
                    Log::info('[Finaliser] MOE', ['idx' => $idx, 'acteur' => $acteur]);

                    Executer::create([
                        'code_projet' => $codeProjet,
                        'code_acteur' => $acteur['code_acteur'] ?? null,
                        'secteur_id'  => $acteur['secteur_id'] ?? null,
                        'is_active'   => true,
                    ]);
                }

                // 8) Financements
                foreach (($step6['financements'] ?? []) as $idx => $fin) {
                    Log::info('[Finaliser] Financement', ['idx' => $idx, 'fin' => $fin]);

                    Financer::create([
                        'code_projet'       => $codeProjet,
                        'code_acteur'       => $fin['bailleur'] ?? null,
                        'montant_finance'   => $fin['montant'] ?? null,
                        'devise'            => $fin['devise'] ?? null,
                        'financement_local' => in_array(strtolower((string)($fin['local'] ?? '')), ['oui','1','true'], true),
                        'commentaire'       => $fin['commentaire'] ?? null,
                        'FinancementType'   => $step6['type_financement'] ?? null,
                        'is_active'         => true,
                    ]);
                }

                // 9) Documents
                $uploadPath = public_path('data/documentProjet/' . $codeProjet);
                File::ensureDirectoryExists($uploadPath);
                Log::info('[Finaliser] Répertoire documents prêt', ['path' => $uploadPath]);

                foreach ((($step7['fichiers'] ?? [])) as $i => $f) {
                    Log::info('[Finaliser] Doc entrée', ['i' => $i, 'file' => $f]);

                    $rel = ltrim($f['storage_path'] ?? '', '/');
                    $absPath = storage_path('app/' . $rel);
                    if (!File::exists($absPath)) {
                        Log::error('[Finaliser] Fichier introuvable sur disque', ['i' => $i, 'abs' => $absPath]);
                        throw new \Exception("Fichier temporaire introuvable ($absPath).");
                    }

                    $res = app(FileProcService::class)->handlePath([
                        'owner_type'    => 'Projet',
                        'owner_id'      => $codeProjet,
                        'categorie'     => 'DOC_PROJET',
                        'path'          => $absPath,
                        'original_name' => $f['original_name'] ?? ('document_'.Str::random(6)),
                        'uploaded_by'   => optional(request()->user())->id,
                    ]);

                    Log::info('[Finaliser] Stockage fichier OK', ['gridfs_id' => $res['id'] ?? null, 'res' => $res]);

                    ProjetDocument::create([
                        'file_name'     => $f['original_name'] ?? ($res['filename'] ?? null),
                        'file_path'     => null,
                        'file_type'     => $f['mime_type'] ?? ($res['mime'] ?? null),
                        'file_size'     => $f['size'] ?? ($res['size'] ?? null),
                        'file_category' => 'DOC_PROJET',
                        'code_projet'   => $codeProjet,
                        'uploaded_at'   => now(),
                        'fichier_id'    => $res['id'] ?? null,
                    ]);

                    try {
                        @unlink($absPath);
                        Log::info('[Finaliser] Temp supprimé', ['abs' => $absPath]);
                    } catch (\Throwable $th) {
                        Log::warning('[Finaliser] Échec suppression temp', ['abs' => $absPath, 'ex' => $th->getMessage()]);
                    }
                }

                // 10) Étude
                $codeEtude = $this->genererCodeEtude(session('pays_selectionne'), session('projet_selectionne'));
                if (!$codeEtude) {
                    throw new \Exception('Échec génération code étude');
                }

                EtudeProject::create([
                    'codeEtudeProjets' => $codeEtude,
                    'code_projet'      => $codeProjet,
                    'valider'          => false,
                    'is_deleted'       => false,
                ]);

                Log::info('[Finaliser] Étude créée', ['code_etude' => $codeEtude]);

                // 🔁 Nettoyage
                $this->nettoyerSessionsEtFichiers();
                Log::info('[Finaliser] Nettoyage sessions OK');

                return response()->json([
                    'success'     => true,
                    'code_projet' => $codeProjet,
                    'code_etude'  => $codeEtude,
                    'message'     => 'Demande effectuée avec succès.',
                ]);
            } catch (\Throwable $e) {
                // 👉 Toute exception = rollback automatique (DB::transaction)
                Log::error('[Finaliser] ERREUR — rollback', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                // On relance l’exception pour que la transaction sache qu’il faut annuler
                throw $e;
            }
        }, 3 );
    }
}




