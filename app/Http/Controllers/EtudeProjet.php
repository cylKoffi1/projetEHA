<?php

namespace App\Http\Controllers;

use App\Models\Approbateur;
use App\Models\Ecran;
use App\Models\Entreprise;
use App\Models\EtudeProject;
use App\Models\EtudeProjectFile;
use App\Models\MotDePasseUtilisateur;
use App\Models\NatureTravaux;
use App\Models\Particulier;
use App\Models\Personnel;
use App\Models\ProjetEha2;
use App\Models\Renforcement;
use App\Models\Task;
use App\Models\TravauxConnexes;
use App\Models\TypeTravauxConnexes;
use App\Models\User;
use App\Models\Validations;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EtudeProjet extends Controller
{
        //////////////////////////////////ETUDE DE PROJET///////////////////////////////////
        public function createNaissance(Request $request)
        {
            $generatedCodeProjet = $this->generateProjectCode('CI', 'EHA');
            $ecran = Ecran::find($request->input('ecran_id'));
            $natures = NatureTravaux::all();
            return view('etudes_projets.naissance', compact('ecran','generatedCodeProjet','natures'));
        }
        const MAX_FILE_SIZE_KB = 2048; // 2 Mo
        const MAX_FILE_SIZE_MB = 2;
        public function storeNaissance(Request $request)
        {
            DB::beginTransaction();
            try {
                // Validation des données
                $validatedData = $request->validate([
                    'title' => 'required|string|max:255',
                    'description' => 'required|string',
                    'files.*' => 'required|file|mimes:zip,pdf,docx,xlsx,jpeg,png|max:' . self::MAX_FILE_SIZE_KB,
                ]);

                $location = 'CI';  // Fixe pour le moment
                $category = 'EHA'; // Fixe pour le moment

                // Générer le code projet
                $codeEtudeProjets = $this->generateProjectCode($location, $category);

                // Créer le projet
                $project = EtudeProject::create([
                    'codeEtudeProjets' => $codeEtudeProjets,
                    'title' => $validatedData['title'],
                    'typeDemandeur' => $request->typeDemandeur,
                ]);

                // Sauvegarder les informations en fonction du type de demandeur
                if ($request->typeDemandeur == 'entreprise') {
                    Entreprise::create([
                        'codeEtudeProjets' => $project->codeEtudeProjets,
                        'nomEntreprise' => $request->companyName,
                        'raisonSociale' => $request->legalStatus,
                        'numeroImmatriculation' => $request->registrationNumber,
                        'adresseSiegeSocial' => $request->headOfficeAddress,
                        'numeroTelephone' => $request->phoneNumber,
                        'adresseEmail' => $request->emailAddress,
                        'siteWeb' => $request->website,
                        'nomResponsableProjet' => $request->projectManager,
                        'fonctionResponsable' => $request->managerRole,
                        'capitalSocial' => $request->capital,
                        'infoSupplementaire1' => $request->additionalInfo1,
                        'infoSupplementaire2' => $request->additionalInfo2,
                    ]);
                } elseif ($request->typeDemandeur == 'particulier') {
                    Particulier::create([
                        'codeEtudeProjets' => $project->codeEtudeProjets,
                        'nomPrenom' => $request->fullName,
                        'statutProfessionnel' => $request->professionalStatus,
                        'numeroImmatriculationIndividuelle' => $request->individualRegistrationNumber,
                        'adresseEntreprise' => $request->individualAddress,
                        'numeroTelephone' => $request->individualPhone,
                        'adresseEmail' => $request->individualEmail,
                        'activitePrincipale' => $request->mainActivity,
                        'nomCommercial' => $request->tradeName,
                        'coordonneesBancaires' => $request->bankDetails,
                        'references' => $request->references,
                        'infoSupplementaire3' => $request->additionalInfo3,
                        'infoSupplementaire4' => $request->additionalInfo4,
                    ]);
                }

                // Traiter chaque fichier uploadé
                if ($request->hasFile('files')) {
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
                            'codeEtudeProjets' => $project->codeEtudeProjets,
                            'file_path' => $filePath,
                            'file_name' => $fileName,
                        ]);
                    }

                    if (!empty($errorFiles)) {
                        $errorFileNames = implode(', ', $errorFiles);
                        return redirect()->back()->withErrors([
                            'files' => "Les fichiers suivants dépassent la taille maximale autorisée de " . self::MAX_FILE_SIZE_MB . " Mo : $errorFileNames"
                        ]);
                    }
                }
                DB::commit();
                return redirect()->back()->with('success', 'Projet enregistré avec succès');
            } catch (PostTooLargeException $e) {
                return redirect()->back()->withErrors([
                    'files' => 'Le fichier dépasse la taille maximale autorisée de ' . self::MAX_FILE_SIZE_MB . ' Mo.'
                ]);
            } catch (\Exception $e) {
                DB::rollback();
                return redirect()->back()->withErrors([
                    'general' => 'Une erreur est survenue lors de l\'enregistrement du projet : ' . $e->getMessage()
                ]);
            }
        }



        private function generateProjectCode($location, $category)
        {
            $year = date('Y');
            $lastProject = EtudeProject::where('codeEtudeProjets', 'like', "{$location}_PROJ_{$category}_{$year}_%")
                                        ->orderBy('codeEtudeProjets', 'desc')
                                        ->first();

            $lastNumber = $lastProject ? (int)substr($lastProject->codeEtudeProjets, -2) : 0;
            $newNumber = str_pad($lastNumber + 1, 2, '0', STR_PAD_LEFT);

            return "{$location}_{$category}_{$year}_{$newNumber}";
        }

        ////////////////////////////////////Validation de projet/////////////////////////////////

        public function validation(Request $request){
            $ecran = Ecran::find($request->input('ecran_id'));
            $user = auth()->user();
            if (!$user || !$user->approbateur) {
                return redirect()->route('projets.index')->with('error', 'Vous devez être un approbateur pour accéder à cette page.');
            }

            $currentApprover = $user->approbateur->codeAppro;
            $projects = EtudeProject::with(['files', 'entreprise', 'particulier'])
            ->where('status', 'pending')
            ->where('current_approver', auth()->user()->approbateur->codeAppro)
            ->get();

            return view('etudes_projets.validation', compact('ecran','projects'));

        }
        // Afficher les détails du projet
        public function show($codeEtudeProjets)
        {
            try {
                $project = EtudeProject::where('codeEtudeProjets', $codeEtudeProjets)->firstOrFail();
                $files = $project->files;
                $validations = Validations::where('project_code', $codeEtudeProjets)
                    ->with('user')
                    ->orderBy('created_at')
                    ->get();
                $users = User::all();   // Récupérer les utilisateurs pour l'affichage des validations

                // Vérifier si l'utilisateur a déjà validé le projet
                $user = auth()->user();
                $userHasValidated = Validations::where('project_code', $codeEtudeProjets)
                    ->where('user_id', $user->id)
                    ->exists();

                // Récupérer les projets en attente si l'utilisateur n'a pas encore validé
                $projects = $userHasValidated ? collect([]) : EtudeProject::with(['files', 'entreprise', 'particulier'])
                    ->where('status', 'pending')
                    ->where('current_approver', $user->approbateur->codeAppro)
                    ->get();

                return view('etudes_projets.validation', compact('project', 'files', 'validations', 'users', 'userHasValidated', 'projects'));
            } catch (ModelNotFoundException $e) {
                return redirect()->back()->with('error', 'Projet non trouvé.');
            }
        }


        // Valider le projet
        public function validateProject(Request $request, $codeEtudeProjets)
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
        $renforcements = Renforcement::with(['beneficiaires.personnel', 'projets'])->get();

        $ecran = Ecran::find($request->input('ecran_id'));
        $projets = ProjetEha2::all();
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
        $projets = ProjetEha2::all();
        $typesTravaux = TypeTravauxConnexes::all();

        return view('etudes_projets.activite', compact('ecran','travaux', 'projets', 'typesTravaux'));
    }

    // Enregistrer un nouveau travail connexe
    public function storeConnexe(Request $request)
    {
        // Validation des champs du formulaire
        $request->validate([
            'code_projet' => 'required',
            'type_travaux_id' => 'required',
            'cout_projet' => 'required|numeric',
            'date_debut_previsionnelle' => 'required|date',
            'date_fin_previsionnelle' => 'required|date|after_or_equal:date_debut_previsionnelle',
        ]);

        try {
            
            // Créer et enregistrer le travail connexe
            TravauxConnexes::create([
                'CodeProjet' => $request->input('code_projet'),
                'type_travaux_id' => $request->input('type_travaux_id'),
                'cout_projet' => $request->input('cout_projet'),
                'date_debut_previsionnelle' => $request->input('date_debut_previsionnelle'),
                'date_fin_previsionnelle' => $request->input('date_fin_previsionnelle'),
                'date_debut_effective' => $request->input('date_debut_effective'),
                'date_fin_effective' => $request->input('date_fin_effective'),
                'commentaire' => $request->input('commentaire'),
            ]);

            // Rediriger avec un message de succès
            $ecran_id = $request->input('ecran_id');
            return redirect()->route('activite.index', ['ecran_id' => $ecran_id])
                ->with('success', 'Travail connexe enregistré avec succès.');

        } catch (\Exception $e) {
            // En cas d'erreur, retourner avec un message d'erreur
            return back()->with('error', 'Erreur lors de l\'enregistrement du travail connexe. Détails : ' . $e->getMessage());
        }
    }


    // Modifier un travail connexe
    public function updateConnexe(Request $request, $id)
    {
        // Valider les champs du formulaire
        $request->validate([
            'type_travaux_id' => 'required',
            'cout_projet' => 'required|numeric',
            'date_debut_previsionnelle' => 'required|date',
            'date_fin_previsionnelle' => 'required|date|after_or_equal:date_debut_previsionnelle',
        ]);

        // Mise à jour du travail connexe
        $travaux = TravauxConnexes::findOrFail($id);
        $travaux->update($request->all());

        return redirect()->route('travaux_connexes.index')->with('success', 'Travail connexe modifié avec succès.');
    }

    // Supprimer un travail connexe
    public function destroyConnexe($id)
    {
        $travaux = TravauxConnexes::findOrFail($id);
        $travaux->delete();

        return redirect()->route('travaux_connexes.index')->with('success', 'Travail connexe supprimé avec succès.');
    }

}
