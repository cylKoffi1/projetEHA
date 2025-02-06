<?php

namespace App\Http\Controllers;

use App\Models\Acteur;
use App\Models\Approbateur;
use App\Models\Bailleur;
use App\Models\DecoupageAdministratif;
use App\Models\DecoupageAdminPays;
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
use App\Models\LocalitesPays;
use App\Models\Ministere;
use App\Models\MotDePasseUtilisateur;
use App\Models\NatureTravaux;
use App\Models\Particulier;
use App\Models\Pays;
use App\Models\Personnel;
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
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EtudeProjet extends Controller
{
        //////////////////////////////////ETUDE DE PROJET///////////////////////////////////
        public function createNaissance(Request $request)
        {
            // Générer le code par défaut pour Public (1)
            $generatedCodeProjet = $this->generateProjectCode('CI', 'EHA', 1); // 1 pour Public
            $paysSelectionne = session('pays_selectionne');
            $groupeSelectionne = session('projet_selectionne');

            $ecran = Ecran::find($request->input('ecran_id'));
            $natures = NatureTravaux::all();
            $GroupeProjets = GroupeProjet::all();
            $Domaines = Domaine::all();
            $SousDomaines = SousDomaine::all();
            $SecteurActivites = SecteurActivite::all();
            $localite = LocalitesPays::all();
            $Pays = GroupeProjetPaysUser::with('pays')
            ->select('pays_code') // Sélectionne uniquement le code pays
            ->distinct() // Évite les doublons
            ->get()
            ->pluck('pays.nom_fr_fr', 'pays.alpha3') // Associe alpha3 avec le nom
            ->sort();
            $DecoupageAdminPays = DecoupageAdminPays::all();
            $Niveau = DecoupageAdministratif::all();
            $formeJuridiques = FormeJuridique::all();
            $genres = Genre::all();
            $SituationMatrimoniales = SituationMatrimonial::all();
            return view('etudes_projets.naissance', compact('formeJuridiques','SituationMatrimoniales','genres', 'SecteurActivites', 'Pays','SousDomaines','Domaines','GroupeProjets','ecran','generatedCodeProjet','natures'));
        }
        public function getActeurs(Request $request)
        {

            // Vérification du type de requête : Maître d’Ouvrage ou Maître d’Œuvre
            $type_mo = $request->input('type_mo'); // Public ou Privé (Maître d'Ouvrage)
            $priveType = $request->input('priveType'); // Entreprise ou Individu (Maître d'Ouvrage)

            $type_ouvrage = $request->input('type_ouvrage'); // Public ou Privé (Maître d'Œuvre)
            $priveMoeType = $request->input('priveMoeType'); // Entreprise ou Individu (Maître d'Œuvre)
            //dd('Privé type:'.$priveMoeType, 'Type Ouvrage:'.$type_ouvrage);
            $acteurs = collect(); // Collection vide par défaut

            if (!empty($type_ouvrage)) {
                // 🔹 Logique pour le Maître d'Œuvre
                if ($type_ouvrage === 'Public') {
                    $acteurs = Acteur::whereIn('type_acteur', ['eta', 'clt'])->get();
                } elseif ($type_ouvrage === 'Privé' && $priveMoeType === 'Entreprise') {
                    $acteurs = Acteur::whereIn('type_acteur', ['ogi', 'fat', 'sa', 'sar', 'sup', 'op'])->get();
                } elseif ($type_ouvrage === 'Privé' && $priveMoeType === 'Individu') {
                    $acteurs = Acteur::where('type_acteur', 'etp')->get();
                }
            } elseif(!empty($type_mo)) {
                // 🔹 Logique pour le Maître d'Ouvrage
                if ($type_mo === 'Public') {
                    $acteurs = Acteur::whereIn('type_acteur', ['eta', 'clt'])->get();
                } elseif ($type_mo === 'Privé' && $priveType === 'Entreprise') {
                    $acteurs = Acteur::whereIn('type_acteur', ['ogi', 'fat', 'sa', 'sar', 'sup', 'op'])->get();
                } elseif ($type_mo === 'Privé' && $priveType === 'Individu') {
                    $acteurs = Acteur::where('type_acteur', 'etp')->get();
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




