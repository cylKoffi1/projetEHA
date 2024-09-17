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
    public static function generateCodeRenforcement()
    {
        $latest = self::latest()->first();
        $orderNumber = $latest ? $latest->id + 1 : 1;
        $month = now()->format('m');
        $year = now()->format('Y');
        return 'EHA_RF_' . $month . '_' . $year . '_' . str_pad($orderNumber, 3, '0', STR_PAD_LEFT);
    }
    public function renfo(Request $request)
    {
        $renforcements = Renforcement::all ();
        $ecran = Ecran::find($request->input('ecran_id'));
        $projets = ProjetEha2::all();
        $beneficiaires = User::all();
        return view('etudes_projets.renforcement', compact('renforcements', 'projets', 'beneficiaires', 'ecran'));
    }

    public function store(Request $request)
    {
        // Générer un code personnalisé pour le renforcement
        $codeRenforcement = Renforcement::generateCodeRenforcement();

        // Créer un renforcement
        $renforcement = Renforcement::create([
            'code_renforcement' => $codeRenforcement,
            'titre' => $request->titre,
            'description' => $request->description,
            'date_renforcement' => $request->date_renforcement,
        ]);

        // Associer les bénéficiaires
        $renforcement->beneficiaires()->attach($request->beneficiaires);

        // Si des projets sont sélectionnés, les associer
        if ($request->has('projets')) {
            $renforcement->projets()->attach($request->projets);
        }

        return redirect()->route('renforcements.index');
    }
}
