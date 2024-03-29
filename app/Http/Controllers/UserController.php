<?php

namespace App\Http\Controllers;

use App\Helpers\CodeGenerator;
use App\Models\AgenceExecution;
use App\Models\AvoirExpertise;
use App\Models\Bailleur;
use App\Models\CouvrirRegion;
use App\Models\Departement;
use App\Models\District;
use App\Models\Domaine;
use App\Models\Ecran;
use App\Models\FonctionUtilisateur;
use App\Models\Ministere;
use App\Models\OccuperFonction;
use App\Models\Pays;
use App\Models\Personnel;
use App\Models\Region;
use App\Models\Sous_prefecture;
use App\Models\SousDomaine;
use App\Models\User;
use App\Models\NiveauAccesDonnees;
use App\Models\StructureRattachement;
use App\Models\UtilisateurDomaine;
use Faker\Provider\ar_EG\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /***********************************  PERSONNES  *******************************/
    public function personnel(Request $request)
    {
        $niveauxAcces = NiveauAccesDonnees::all();
        $groupe_utilisateur = Role::all();
        $fonctions = FonctionUtilisateur::all();
        $users = User::all();
        $bailleurs=Bailleur::all();
        $agences=AgenceExecution::all();
        $ministeres=Ministere::all();
        $structureRattachement = StructureRattachement::all();
        $personnel = Personnel::all();
        $ecran = Ecran::find($request->input('ecran_id'));
        return view('users.personnel', compact('ecran','bailleurs','agences','ministeres','structureRattachement','niveauxAcces', 'personnel', 'users', 'groupe_utilisateur', 'fonctions'));
    }
    public function createPersonnel(Request $request)
    {
        $niveauxAcces = NiveauAccesDonnees::all();
        $groupe_utilisateur = Role::all();
        $fonctions = FonctionUtilisateur::all();
        $bailleurs = Bailleur::orderBy('libelle_long', 'asc')->get();
        $agences = AgenceExecution::orderBy('nom_agence', 'asc')->get();
        $ministeres = Ministere::orderBy('libelle', 'asc')->get();
        $domaines = Domaine::all();
        $sous_domaines = [];
        if (!auth()->user()->personnel->domaine) {
            $sous_domaines = SousDomaine::all();
        } else {
            $sous_domaines = SousDomaine::where("code_domaine", auth()->user()->personnel->domaine->code)->get();
        }
        $pays = Pays::orderBy('nom_fr_fr', 'asc')->get();
        $ecran = Ecran::find($request->input('ecran_id'));
        $districts = District::where('id_pays', config('app_settings.id_pays'))->get();
        $regions = Region::whereHas('district', function ($query) {
            $query->where('id_pays', config('app_settings.id_pays'));
        })->get();
        $departements = Departement::whereHas('region.district.pays', function ($query) {
            $query->where('id', config('app_settings.id_pays'));
        })->get();
        $sous_prefectures = Sous_prefecture::whereHas('departement.region.district.pays', function ($query) {
            $query->where('id', config('app_settings.id_pays'));
        })->get();
        return view('users.create-personne', compact('ecran','sous_prefectures', 'niveauxAcces', 'pays',  'districts', 'regions', 'departements', 'groupe_utilisateur', 'domaines', 'sous_domaines', 'bailleurs', 'agences', 'ministeres', 'fonctions'));
    }
    public function storePersonnel(Request $request)
    {

        // $requestData = $request->all();
        // dump($requestData);

        // $validator = Validator::make($request->json()->all(), [
        //     'tel' => 'required|string|max:20',
        //     'adresse' => 'required|string|max:150',
        //     'email' => 'required|email|unique:personnel,email',
        //     'fonction' => 'required|exists:fonction_utilisateur,code',
        // ]);


        // if ($validator->fails()) {
        //     // Rediriger avec les erreurs de validation
        //     return redirect()->route('personnel.create')->withErrors($validator)->withInput();
        // }

        $code = CodeGenerator::generateCode();

        Personnel::create([
            'code_personnel' => $code,
            'nom' => $request->input('nom'),
            'prenom' => $request->input('prenom'),
            'addresse' => $request->input('adresse'),
            'telephone' => $request->input('tel'),
            'email' => $request->input('email'),
        ]);

        $structureRattachement = new StructureRattachement([
            'code_personnel' => $code,
            'date' => now(),
        ]);

        if ($request->input('structure') == "bai") {
            $structureRattachement->code_structure = $request->input('bailleur');
            $structureRattachement->type_structure = 'bailleurss';
        } elseif ($request->input('structure') == "age") {
            $structureRattachement->code_structure = $request->input('agence');
            $structureRattachement->type_structure = 'agence_execution';
        } else {
            $structureRattachement->code_structure = $request->input('ministere');
            $structureRattachement->type_structure = 'ministere';
        }

        $structureRattachement->save();

        if ($request->input('niveau_acces_id') == "de") {
            CouvrirRegion::create([
                'code_personnel' => $code,
                'code_departement' => $request->input('dep'),
            ]);

        } else if ($request->input('niveau_acces_id') == "di") {
            CouvrirRegion::create([
                'code_personnel' => $code,
                'code_district' => $request->input('dis'),
            ]);
        } else if ($request->input('niveau_acces_id') == "re") {
            CouvrirRegion::create([
                'code_personnel' => $code,
                'code_region' => $request->input('reg'),
            ]);
        } else {
            CouvrirRegion::create([
                'code_personnel' => $code,
                'id_pays' => $request->input('na'),
            ]);
        }
        OccuperFonction::create([
            'code_personnel' => $code,
            'code_fonction' => $request->input('fonction'),
        ]);
        $ecran_id = $request->input('ecran_id');
        return redirect()->route('personnel.create', ['ecran_id' => $ecran_id])->with('success', 'Personne crée avec succès.');

    }


    public function detailsPersonne(Request $request, $personneId)
    {
        $personne = Personnel::find($personneId);

        if (!$personne) {
            // Gérer le cas où l'utilisateur n'est pas trouvé
            return redirect()->route('users.personnel')->with('error', 'Personne non trouvée.');
        }
       $ecran = Ecran::find($request->input('ecran_id'));
        $niveauxAcces = NiveauAccesDonnees::all();
        $groupe_utilisateur = Role::all();
        $fonctions = FonctionUtilisateur::all();
        return view('users.personne-profile', compact('ecran','niveauxAcces', 'personne', 'groupe_utilisateur', 'fonctions'));
    }

    public function getPersonne(Request $request, $personneId)
    {
        $personne = Personnel::find($personneId);

        if (!$personne) {
            // Gérer le cas où l'utilisateur n'est pas trouvé
            return redirect()->route('users.personnel')->with('error', 'Personne non trouvé.');
        }
        $niveauxAcces = NiveauAccesDonnees::all();
        $groupe_utilisateur = Role::all();
        $fonctions = FonctionUtilisateur::all();
        $structureRattachement = StructureRattachement::where('code_personnel', $personneId)->orderBy('date', 'DESC')->first();

        $bailleurs = Bailleur::orderBy('libelle_long', 'asc')->get();
        $agences = AgenceExecution::orderBy('nom_agence', 'asc')->get();
        $ministeres = Ministere::orderBy('libelle', 'asc')->get();
        $domaines = Domaine::all();
        $sous_domaines = [];
        if (!auth()->user()->personnel->domaine) {
            $sous_domaines = SousDomaine::all();
        } else {
            $sous_domaines = SousDomaine::where("code_domaine", auth()->user()->personnel->domaine->code)->get();
        }
        $regions = Region::whereHas('district', function ($query) {
            $query->where('id_pays', config('app_settings.id_pays'));
        })->get();
        $departements = Departement::whereHas('region.district.pays', function ($query) {
            $query->where('id', config('app_settings.id_pays'));
        })->get();
        $sous_prefectures = Sous_prefecture::whereHas('departement.region.district.pays', function ($query) {
            $query->where('id', config('app_settings.id_pays'));
        })->get();

        $pays = Pays::orderBy('nom_fr_fr', 'asc')->get();
       $ecran = Ecran::find($request->input('ecran_id'));
        $districts = District::where('id_pays', config('app_settings.id_pays'))->get();
        return view('users.personne-update', compact('ecran','structureRattachement','niveauxAcces', 'sous_prefectures', 'personne', 'bailleurs', 'agences', 'ministeres', 'domaines', 'sous_domaines', 'pays',  'districts', 'fonctions', 'regions', 'departements'));
    }
    public function updatePersonne(Request $request, $personnelId)
    {
        $request->validate([
            'nom' => 'required',
            'prenom' => 'required',
            // 'email' => 'required|email',
            'tel' => 'required',
            // 'adresse' => 'required',
        ]);

        // Mettez à jour les informations de l'utilisateur
        $personne = Personnel::find($personnelId);

        // Assurez-vous que l'utilisateur et la personne associée existent
        if (!$personne) {
            // Gérer le cas où l'utilisateur n'est pas trouvé
            return redirect()->route('personne.update', ['personnelId' => $personnelId])->with('error', 'Personne non trouvé.');
        }

        // Mettez à jour les informations de la personne
        $personne->update([
            'nom' => $request->input('nom'),
            'prenom' => $request->input('prenom'),
            'email' => $request->input('email'),
            'telephone' => $request->input('tel'),
            'addresse' => $request->input('adresse'),
        ]);
        if ($request->input('niveau_acces_id') == "de") {
            CouvrirRegion::create([
                'code_personnel' => $personnelId,
                'code_departement' => $request->input('dep'),
            ]);

        } else if ($request->input('niveau_acces_id') == "di") {
            CouvrirRegion::create([
                'code_personnel' => $personnelId,
                'code_district' => $request->input('dis'),
            ]);
        } else if ($request->input('niveau_acces_id') == "re") {
            CouvrirRegion::create([
                'code_personnel' => $personnelId,
                'code_region' => $request->input('reg'),
            ]);
        } else {
            CouvrirRegion::create([
                'code_personnel' => $personnelId,
                'id_pays' => $request->input('na'),
            ]);
        }


        // Récupérez d'abord l'objet StructureRattachement à partir de la base de données
        $structureRattachement = StructureRattachement::where('code_personnel', $personnelId)->firstOrFail();

        // Vérifiez si la structure est "bai", "age" ou "min"
        if ($request->input('structure') == "bai") {
            $structureRattachement->update([
                'code_structure' => $request->input('bailleur'),
                'type_structure' => 'bailleurss',
            ]);
        } elseif ($request->input('structure') == "age") {
            $structureRattachement->update([
                'code_structure' => $request->input('agence'),
                'type_structure' => 'agence_execution',
            ]);
        } else {
            $structureRattachement->update([
                'code_structure' => $request->input('ministere'),
                'type_structure' => 'ministere',
            ]);
        }


        OccuperFonction::create([
            'code_personnel' => $personnelId,
            'code_fonction' => $request->input('fonction'),
        ]);

        // Vérifiez si un nouveau fichier photo a été téléchargé
        if ($request->hasFile('photo')) {
            // Supprimez l'ancienne photo s'il en existe une
            if ($personne->photo) {
                // Assurez-vous que le fichier existe avant de le supprimer
                $oldPhotoPath = public_path("users/{$personne->photo}");
                if (file_exists($oldPhotoPath)) {
                    unlink($oldPhotoPath);
                }
            }

            // Téléchargez et enregistrez la nouvelle photo
            $file = $request->file('photo');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move('users', $filename);

            // Mettez à jour le champ de la photo dans la base de données
            $personne->photo = $filename;
            $personne->save();
        }
        $ecran_id = $request->input('ecran_id');
        // Rediriger avec un message de succès
        return redirect()->route('users.personnel',['ecran_id' => $ecran_id])->with('success', 'Personne mise à jour avec succès.');
    }

    /*********************************** FIN  PERSONNES  *******************************/




    public function users(Request $request)
    {
        $niveauxAcces = NiveauAccesDonnees::all();
        $groupe_utilisateur = Role::all();
        $fonctions = FonctionUtilisateur::all();
        $users = User::where('is_active', true)->get();
       $ecran = Ecran::find($request->input('ecran_id'));
        return view('users.users', compact('ecran','niveauxAcces',  'users', 'groupe_utilisateur', 'fonctions'));
    }
    // Méthode pour afficher le formulaire de création d'utilisateur
    public function getIndicatif($paysId)
    {
        // Récupérer l'indicatif du pays en fonction de son ID depuis la base de données
        $pays = Pays::find($paysId);

        // Vérifier si le pays existe
        if ($pays) {
            // Retourner l'indicatif du pays
            return response()->json(['indicatif' => $pays->codeTel]);
        } else {
            // Si le pays n'existe pas, retourner une réponse d'erreur
            return response()->json(['error' => 'Pays non trouvé'], 404);
        }
    }
    public function create(Request $request)
    {
        $niveauxAcces = NiveauAccesDonnees::all();
        $groupe_utilisateur = Role::all();
        $fonctions = FonctionUtilisateur::all();

        $personnes = Personnel::orderBy('nom', 'asc')->whereNotIn('code_personnel', User::pluck('code_personnel')->toArray())->get();
        $personneId = $request->input('personne');

        $structureRattachement = StructureRattachement::where('code_personnel', $personneId)->orderBy('date', 'DESC')->first();

        $bailleurs = Bailleur::orderBy('libelle_long', 'asc')->get();
        $agences = AgenceExecution::orderBy('nom_agence', 'asc')->get();
        $ministeres = Ministere::orderBy('libelle', 'asc')->get();
        $domaines = Domaine::all();
        $sous_domaines = SousDomaine::all();
        $pays = Pays::orderBy('nom_fr_fr', 'asc')->get();
        $ecran = Ecran::find($request->input('ecran_id'));
        $districts = District::where('id_pays', config('app_settings.id_pays'))->get();
        $regions = Region::whereHas('district', function ($query) {
            $query->where('id_pays', config('app_settings.id_pays'));
        })->get();
        $departements = Departement::whereHas('region.district.pays', function ($query) {
            $query->where('id', config('app_settings.id_pays'));
        })->get();
        $sous_prefectures = Sous_prefecture::whereHas('departement.region.district.pays', function ($query) {
            $query->where('id', config('app_settings.id_pays'));
        })->get();
        // Vérifie si l'utilisateur a sélectionné "Champ d'exercice national"
        $champExercice = $request->input('niveau_acces_id');
        if ($champExercice == 'na') {
            // Ajoute automatiquement la Côte d'Ivoire à la sélection
            $pays->prepend(Pays::find(110));
        }



        return view('users.create', compact('ecran', 'structureRattachement', 'niveauxAcces', 'domaines', 'sous_domaines', 'bailleurs', 'pays', 'districts', 'regions', 'departements', 'agences', 'ministeres', 'personnes', 'groupe_utilisateur', 'fonctions'));
    }


    // Méthode pour traiter la soumission du formulaire et créer un utilisateur

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:mot_de_passe_utilisateur,login',
            'email' => 'required|email',
            'niveau_acces_id' => 'required|exists:niveau_acces_donnees,id',
            'group_user' => 'required|exists:groupe_utilisateur,code',
            'personne' => 'required|exists:personnel,code_personnel',
            // 'sous_domaine' => 'required|exists:sous_domaine,code',
        ]);

        // if ($validator->fails()) {
        //     // Redirect with validation errors
        //     return redirect()->route('users.create')->withErrors($validator)->withInput();
        // }
        $donnees = $request->all();
        $sousDomaines = json_decode($request->input('sd'), true);
        $domainesSel = json_decode($request->input('domS'), true);
        $personne = Personnel::find($request->input('personne'));

        if ($personne) {

            if ($request->input('niveau_acces_id') == "de") {
                CouvrirRegion::create([
                    'code_personnel' => $request->input('personne'),
                    'code_departement' => $request->input('dep'),
                ]);

            } else if ($request->input('niveau_acces_id') == "di") {
                CouvrirRegion::create([
                    'code_personnel' => $request->input('personne'),
                    'code_district' => $request->input('dis'),
                ]);
            } else if ($request->input('niveau_acces_id') == "re") {
                CouvrirRegion::create([
                    'code_personnel' => $request->input('personne'),
                    'code_region' => $request->input('reg'),
                ]);
            } else {
                CouvrirRegion::create([
                    'code_personnel' => $request->input('personne'),
                    'id_pays' => $request->input('na'),
                ]);
            }

            $structureRattachement = new StructureRattachement([
                'code_personnel' => $request->input('personne'),
                'date' => now(),
            ]);

            if ($request->input('structure') == "bai") {
                $structureRattachement->code_structure = $request->input('bailleur');
                $structureRattachement->type_structure = 'bailleurss';
            } elseif ($request->input('structure') == "age") {
                $structureRattachement->code_structure = $request->input('agence');
                $structureRattachement->type_structure = 'agence_execution';
            } else {
                $structureRattachement->code_structure = $request->input('ministere');
                $structureRattachement->type_structure = 'ministere';
            }

            $personne->update(['email' => $request->input('email')]);


            OccuperFonction::create([
                'code_personnel' => $request->input('personne'),
                'code_fonction' => $request->input('fonction'),
            ]);
            $role = Role::find($request->input('group_user'));
            $user = User::create([
                'code_personnel' => $request->input('personne'),
                'login' => $request->input('username'),
                'password' => Hash::make(config('app_settings.default_password')),
                'niveau_acces_id' => $request->input('niveau_acces_id'),
                'email' => $request->input('email'),
            ]);
            $user->assignRole($role);

            $sous_dom = AvoirExpertise::where('code_personnel', $personne->code_personnel)->get();
            $sousDomainesSelectionnes = $sousDomaines['sous_domaine'];
            $dom = UtilisateurDomaine::where('code_personnel', $personne->code_personnel)->get();
            $domSEl = $domainesSel['domaine'];

            $sousDomainesExistants = $sous_dom->pluck('sous_domaine')->toArray();
            $sousDomainesASupprimer = array_diff($sousDomainesExistants, $sousDomainesSelectionnes);
            $DomainesExistants = $dom->pluck('code_domaine')->toArray();
            $DomainesASupprimer = array_diff($DomainesExistants, $domSEl);

            // Supprimez les associations qui ne sont plus sélectionnées
            AvoirExpertise::where('code_personnel', $personne->code_personnel)
                ->whereIn('sous_domaine', $sousDomainesASupprimer)
                ->delete();

            // Supprimez les associations qui ne sont plus sélectionnées
            UtilisateurDomaine::where('code_personnel', $personne->code_personnel)
                ->whereIn('code_domaine', $DomainesASupprimer)
                ->delete();

            // Ajoutez les nouvelles associations sélectionnées
            foreach ($sousDomainesSelectionnes as $sousDomaine) {
                AvoirExpertise::updateOrCreate(
                    [
                        'code_personnel' => $personne->code_personnel,
                        'sous_domaine' => $sousDomaine
                    ]
                );
            }
            // Ajoutez les nouvelles associations sélectionnées
            foreach ($domSEl as $do) {
                UtilisateurDomaine::updateOrCreate(
                    [
                        'code_personnel' => $personne->code_personnel,
                        'code_domaine' => $do
                    ]
                );
            }
        }
        $ecran_id = $request->input('ecran_id');
        return redirect()->route('users.create',['ecran_id' => $ecran_id])->with('success', 'Utilisateur créé avec succès!');
    }


    // UserController.php

    public function checkUsername(Request $request)
    {
        $username = $request->input('username');

        $user = User::where('login', $username)->first();

        return response()->json(['exists' => $user !== null]);
    }
    public function checkEmail(Request $request)
    {
        $email = $request->input('email');

        // Ajoutez ces journaux pour déboguer
        \Log::info('Email from request: ' . $email);
        \Log::info('User exists: ' . (int) User::whereHas('personnel', function ($query) use ($email) {
            $query->where('email', $email);
        })->exists());

        $exists = User::whereHas('personnel', function ($query) use ($email) {
            $query->where('email', $email);
        })->exists();

        return response()->json(['exists' => $exists]);
    }
    public function checkEmail_personne(Request $request)
    {
        $email = $request->input('email');

        $exists = Personnel::where('email', $email)->exists();
        return response()->json(['exists' => $exists]);
    }


    public function getPersonneInfos(Request $request, $personneId)
    {
        // Récupérer les informations sur la personne
        $personne = Personnel::with('latestFonction', 'latestRegion')->find($personneId);

        // Récupérer les informations sur la structure rattachée à la personne
        $structureRattachement = StructureRattachement::where('code_personnel', $personneId)->orderBy('date', 'DESC')->first();

        // Ajouter les informations sur la structure rattachée à la personne aux données de la personne
        $personne->structure = $structureRattachement;

        // Retourner les données de la personne avec les informations sur la structure rattachée
        return response()->json($personne);
    }
    public function structureRattachement()
    {
        return $this->hasOne(StructureRattachement::class, 'code_personnel', 'code_personnel');
    }



    public function getUser(Request $request, $userId)
    {
        $user = User::with('personnel')->find($userId);

        $users =  Personnel::find($userId);

        if (!$user && !$users) {
            // Gérer le cas où l'utilisateur n'est pas trouvé
            return redirect()->route('users.users')->with('error', 'Utilisateur non trouvé.');
        }
        $structureRattachement = StructureRattachement::where('code_personnel', $user->code_personnel)->orderBy('date', 'DESC')->first();
        $niveauxAcces = NiveauAccesDonnees::all();
        $groupe_utilisateur = Role::all();
        $fonctions = FonctionUtilisateur::all();
        $bailleurs = Bailleur::orderBy('libelle_long', 'asc')->get();
        $agences = AgenceExecution::orderBy('nom_agence', 'asc')->get();
        $ministeres = Ministere::orderBy('libelle', 'asc')->get();
        $domaines = Domaine::all();
        $sous_domaines = SousDomaine::all();
        $personnes = Personnel::orderBy('nom', 'asc')->get();
        $ecran = Ecran::find($request->input('ecran_id'));
        $sous_dom = AvoirExpertise::where('code_personnel', $user->code_personnel)->get();
        $dom = UtilisateurDomaine::where('code_personnel', $user->code_personnel)->get();
        $districts = District::where('id_pays', config('app_settings.id_pays'))->get();
        $regions = Region::whereHas('district', function ($query) {
            $query->where('id_pays', config('app_settings.id_pays'));
        })->get();
        $departements = Departement::whereHas('region.district.pays', function ($query) {
            $query->where('id', config('app_settings.id_pays'));
        })->get();
        $pays = Pays::orderBy('nom_fr_fr', 'asc')->get();
        $sous_prefectures = Sous_prefecture::whereHas('departement.region.district.pays', function ($query) {
            $query->where('id', config('app_settings.id_pays'));
        })->get();
        return view('users.user-update', compact('ecran','users','structureRattachement','regions', 'pays', 'departements','sous_prefectures', 'districts','niveauxAcces', 'domaines', 'personnes', 'sous_domaines', 'bailleurs', 'agences', 'ministeres', 'user', 'groupe_utilisateur', 'fonctions', 'sous_dom', 'dom'));
    }


    public function detailsUser(Request $request, $userId)
    {
        $user = User::with('personnel')->find($userId);

        if (!$user) {
            // Gérer le cas où l'utilisateur n'est pas trouvé
            return redirect()->route('users.users')->with('error', 'Utilisateur non trouvé.');
        }
        $niveauxAcces = NiveauAccesDonnees::all();
        $groupe_utilisateur = Role::all();
        $fonctions = FonctionUtilisateur::all();
        $bailleurs = Bailleur::orderBy('libelle_long', 'asc')->get();
        $agences = AgenceExecution::orderBy('nom_agence', 'asc')->get();
        $ministeres = Ministere::orderBy('libelle', 'asc')->get();
        $domaines = Domaine::all();
        $sous_domaines = SousDomaine::all();
        $personnes = Personnel::orderBy('nom', 'asc')->get();
        $ecran = Ecran::find($request->input('ecran_id'));
        $sous_dom = AvoirExpertise::where('code_personnel', $user->code_personnel)->get();
        $dom = UtilisateurDomaine::where('code_personnel', $user->code_personnel)->get();
        return view('users.user-profile', compact('ecran','niveauxAcces', 'domaines', 'personnes', 'sous_domaines', 'bailleurs', 'agences', 'ministeres', 'user', 'groupe_utilisateur', 'fonctions', 'sous_dom', 'dom'));
    }
    // Mettre à jour l'utilisateur
    public function update(Request $request, $userId)
    {
        try {
            $donnees = $request->all();
            // Décoder la chaîne JSON des sous-domaines
            $sousDomaines = json_decode($request->input('sd'), true);
            $domainesSel = json_decode($request->input('domS'), true);

            $request->validate([
                'group_user' => 'required',
                'structure' => 'required',
                'code_fonction' => 'required',
                'niveau_acces_id' => 'required',
                'username' => 'required',
                'email' => 'required|email',
            ]);


            $user = User::find($userId);

            if (!$user || !$user->personnel) {

                return response()->json(['error' => 'Utilisateur non trouvé.'], 404);
                // Gérer le cas où l'utilisateur n'est pas trouvé
                //return redirect()->route('users.users', ['userId' => $userId])->with('error', 'Utilisateur non trouvé.');
            }



            // Mettez à jour les informations de la personne
            $user->personnel->update([
                'nom' => $request->input('nom'),
                'prenom' => $request->input('prenom'),
                'email' => $request->input('email'),
                'telephone' => $request->input('tel'),
                'addresse' => $request->input('adresse'),
            ]);

            // Créer une nouvelle instance de CouvrirRegion
            $newCouvrirRegion = new CouvrirRegion([
                'code_personnel' => $user->personnel->code_personnel,
                'date' => now()
            ]);

            // Définir les champs en fonction du niveau d'accès
            if ($request->input('niveau_acces_id') == "de") {
                $newCouvrirRegion->code_departement = $request->input('dep');
            } elseif ($request->input('niveau_acces_id') == "di") {
                $newCouvrirRegion->code_district = $request->input('dis');
            } elseif ($request->input('niveau_acces_id') == "re") {
                $newCouvrirRegion->code_region = $request->input('reg');
            } else {
                $newCouvrirRegion->id_pays = $request->input('na');
            }

            // Sauvegarder la nouvelle instance de CouvrirRegion
            $newCouvrirRegion->save();


            // Vérifiez s'il existe déjà une entrée correspondante dans la base de données
            $existingStructureRattachement = StructureRattachement::where('code_personnel', $user->personnel->code_personnel)->first();

            if ($existingStructureRattachement) {
                // Mettez à jour les données existantes
                $existingStructureRattachement->date = now();
                if ($request->input('structure') == "bai") {
                    $existingStructureRattachement->code_structure = $request->input('bailleur');
                    $existingStructureRattachement->type_structure = 'bailleurss';
                } elseif ($request->input('structure') == "age") {
                    $existingStructureRattachement->code_structure = $request->input('agence');
                    $existingStructureRattachement->type_structure = 'agence_execution';
                } elseif ($request->input('structure') == "min"){
                    $existingStructureRattachement->code_structure = $request->input('ministere');
                    $existingStructureRattachement->type_structure = 'ministere';
                }
                $existingStructureRattachement->save();
            } else {
                // Créez une nouvelle entrée si aucune entrée correspondante n'existe
                $newStructureRattachement = new StructureRattachement([
                    'code_personnel' => $user->personnel->code_personnel,
                    'date' => now(),
                ]);
                if ($request->input('structure') == "bai") {
                    $newStructureRattachement->code_structure = $request->input('bailleur');
                    $newStructureRattachement->type_structure = 'bailleurss';
                } elseif ($request->input('structure') == "age") {
                    $newStructureRattachement->code_structure = $request->input('agence');
                    $newStructureRattachement->type_structure = 'agence_execution';
                } elseif ($request->input('structure') == "min"){
                    $newStructureRattachement->code_structure = $request->input('ministere');
                    $newStructureRattachement->type_structure = 'ministere';
                }
                $newStructureRattachement->save();
            }




            $newRoleId = $request->input('group_user'); // Récupérez le nouvel identifiant de rôle depuis la requête

            if($newRoleId){
                // Supprimez tous les rôles de l'utilisateur
                $user->roles()->detach();

                // Assignez le nouveau rôle à l'utilisateur
                $user->assignRole($newRoleId);
            }


            // Vérifiez et mettez à jour la fonction utilisateur si nécessaire
            if ($user->personnel && $user->latestFonction && $user->latestFonction->code_fonction != $request->input('code_fonction')) {
                OccuperFonction::create([
                    'code_personnel' => $user->personnel->code_personnel,
                    'code_fonction' => $request->input('code_fonction'),
                ]);
            }else{
                OccuperFonction::create([
                    'code_personnel' => $user->personnel->code_personnel,
                    'code_fonction' => $request->input('code_fonction'),
                ]);
            }

            // Mettez à jour le nom d'utilisateur
            $user->update([
                'login' => $request->input('username'),
                'niveau_acces_id' => $request->input('niveau_acces_id'),
                'email' => $request->input('email'),
            ]);

            // Vérifiez si un nouveau fichier photo a été téléchargé
            if ($request->hasFile('photo')) {
                // Supprimez l'ancienne photo s'il en existe une
                if ($user->personnel->photo) {
                    // Assurez-vous que le fichier existe avant de le supprimer
                    $oldPhotoPath = public_path("users/{$user->personnel->photo}");
                    if (file_exists($oldPhotoPath)) {
                        unlink($oldPhotoPath);
                    }
                }
                //$sous_domaines = SousDomaine::all();

                // Téléchargez et enregistrez la nouvelle photoreturn response()->json(['error' => 'Utilisateur non trouvé.'], 404);
                $file = $request->file('photo');
                $extension = $file->getClientOriginalExtension();
                $filename = time() . '.' . $extension;
                $file->move('users', $filename);

                // Mettez à jour le champ de la photo dans la base de données
                $user->personnel->photo = $filename;
                $user->personnel->save();
            }

            $sous_dom = AvoirExpertise::where('code_personnel', $user->code_personnel)->get();
            $sousDomainesSelectionnes = $sousDomaines['sous_domaine'];
            $dom = UtilisateurDomaine::where('code_personnel', $user->code_personnel)->get();
            $domSEl = $domainesSel['domaine'];

            $sousDomainesExistants = $sous_dom->pluck('sous_domaine')->toArray();
            $sousDomainesASupprimer = array_diff($sousDomainesExistants, $sousDomainesSelectionnes);
            $DomainesExistants = $dom->pluck('code_domaine')->toArray();
            $DomainesASupprimer = array_diff($DomainesExistants, $domSEl);

            // Supprimez les associations qui ne sont plus sélectionnées
            AvoirExpertise::where('code_personnel', $user->code_personnel)
                ->whereIn('sous_domaine', $sousDomainesASupprimer)
                ->delete();

            // Supprimez les associations qui ne sont plus sélectionnées
            UtilisateurDomaine::where('code_personnel', $user->code_personnel)
                ->whereIn('code_domaine', $DomainesASupprimer)
                ->delete();

            // Ajoutez les nouvelles associations sélectionnées
            foreach ($sousDomainesSelectionnes as $sousDomaine) {
                AvoirExpertise::updateOrCreate(
                    [
                        'code_personnel' => $user->code_personnel,
                        'sous_domaine' => $sousDomaine
                    ]
                );
            }
            // Ajoutez les nouvelles associations sélectionnées
            foreach ($domSEl as $do) {
                UtilisateurDomaine::updateOrCreate(
                    [
                        'code_personnel' => $user->code_personnel,
                        'code_domaine' => $do
                    ]
                );
            }
            //return response()->json($[sd]);
            return response()->json(['success' => 'Utilisateur mis à jour avec succès.', 'donnees' => $donnees]);
        } catch (ValidationException $e) {
            // Renvoyer les erreurs de validation avec le code de statut 422 (Unprocessable Entity)
            return response()->json(['error' => $e->errors()], 422);
        } catch (\Exception $e) {
            // Renvoyer toute autre exception avec le code de statut 500 (Internal Server Error)
            return response()->json(['error' => $e->getMessage()], 500);
        }// Rediriger avec un message de succès
            //return redirect()->route('users.users')->with('success', 'Utilisateur mis à jour avec succès.');
    }


    public function update_auth(Request $request, $userId)
    {
        $request->validate([
            'username' => 'required',
            'nom' => 'required',
            'prenom' => 'required',
            'email' => 'required|email',
            'tel' => 'required',
            'adresse' => 'required',
        ]);

        // Mettez à jour les informations de l'utilisateur
        $user = User::find($userId);

        // Assurez-vous que l'utilisateur et la personne associée existent
        if (!$user || !$user->personnel) {
            // Gérer le cas où l'utilisateur n'est pas trouvé
            return redirect()->route('users.details', ['userId' => $userId])->with('error', 'Utilisateur non trouvé.');
        }
        // Décoder la chaîne JSON des sous-domaines
        $sousDomaines = json_decode($request->input('sd'), true);
        $domainesSel = json_decode($request->input('domS'), true);

        // Mettez à jour les informations de la personne
        $user->personnel->update([
            'nom' => $request->input('nom'),
            'prenom' => $request->input('prenom'),
            'email' => $request->input('email'),
            'telephone' => $request->input('tel'),
            'addresse' => $request->input('adresse'),
        ]);

        if ($request->input('structure') == "bai") {
            $user->personnel->update([
                'code_structure_bailleur' => $request->input('bailleur'),
                'code_structure_agence' => null,
                'code_structure_ministere' => null,
            ]);
        } else if ($request->input('structure') == "age") {
            $user->personnel->update([
                'code_structure_agence' => $request->input('agence'),
                'code_structure_bailleur' => null,
                'code_structure_ministere' => null,
            ]);
        } else {
            $user->personnel->update([
                'code_structure_ministere' => $request->input('ministere'),
                'code_structure_agence' => null,
                'code_structure_bailleur' => null,
            ]);
        }


        $newRoleId = $request->input('group_user'); // Récupérez le nouvel identifiant de rôle depuis la requête

        if($newRoleId){
            // Supprimez tous les rôles de l'utilisateur
            $user->roles()->detach();

            // Assignez le nouveau rôle à l'utilisateur
            $user->assignRole($newRoleId);
        }

        // Vérifiez et mettez à jour la fonction utilisateur si nécessaire
        if ($request->filled('fonction') && $user->latestFonction->code_fonction != $request->input('fonction')) {
            OccuperFonction::create([
                'code_personnel' => $user->personnel->code_personnel,
                'code_fonction' => $request->input('fonction'),
            ]);
        }


        // Mettez à jour le nom d'utilisateur
        $user->update([
            'login' => $request->input('username'),
            'email' => $request->input('email'),
        ]);

        // Vérifiez si un nouveau fichier photo a été téléchargé
        if ($request->hasFile('photo')) {
            // Supprimez l'ancienne photo s'il en existe une
            if ($user->personnel->photo) {
                $oldPhotoPath = public_path("users/{$user->personnel->photo}");
                if (file_exists($oldPhotoPath)) {
                    unlink($oldPhotoPath);
                }
            }

            // Téléchargez et enregistrez la nouvelle photo
            $file = $request->file('photo');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move('users', $filename);

            // Mettez à jour le champ de la photo dans la base de données
            $user->personnel->photo = $filename;
            $user->personnel->save();
        }


        $sous_dom = AvoirExpertise::where('code_personnel', $user->code_personnel)->get();
        $sousDomainesSelectionnes = $sousDomaines['sous_domaine'];
        $dom = UtilisateurDomaine::where('code_personnel', $user->code_personnel)->get();
        $domSEl = $domainesSel['domaine'];

        $sousDomainesExistants = $sous_dom->pluck('sous_domaine')->toArray();
        $sousDomainesASupprimer = array_diff($sousDomainesExistants, $sousDomainesSelectionnes);
        $DomainesExistants = $dom->pluck('code_domaine')->toArray();
        $DomainesASupprimer = array_diff($DomainesExistants, $domSEl);

        // Supprimez les associations qui ne sont plus sélectionnées
        AvoirExpertise::where('code_personnel', $user->code_personnel)
            ->whereIn('sous_domaine', $sousDomainesASupprimer)
            ->delete();

        // Supprimez les associations qui ne sont plus sélectionnées
        UtilisateurDomaine::where('code_personnel', $user->code_personnel)
            ->whereIn('code_domaine', $DomainesASupprimer)
            ->delete();

        // Ajoutez les nouvelles associations sélectionnées
        foreach ($sousDomainesSelectionnes as $sousDomaine) {
            AvoirExpertise::updateOrCreate(
                [
                    'code_personnel' => $user->code_personnel,
                    'sous_domaine' => $sousDomaine
                ]
            );
        }
        // Ajoutez les nouvelles associations sélectionnées
        foreach ($domSEl as $do) {
            UtilisateurDomaine::updateOrCreate(
                [
                    'code_personnel' => $user->code_personnel,
                    'code_domaine' => $do
                ]
            );
        }
        return response()->json(['success' => 'Profile  mis à jour avec succès.']);
        // Rediriger avec un message de succès
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'old' => 'required',
            'new' => 'required|min:8',
            'confirm_new' => 'required|same:new',
        ]);

        $user = auth()->user();

        // Vérifiez si l'ancien mot de passe est correct
        if (!Hash::check($request->old, $user->password)) {
            return redirect()->back()->withErrors(['old' => 'L\'ancien mot de passe est incorrect.']);
        }


        // Mettez à jour le mot de passe
        $user->update([
            'password' => Hash::make($request->new),
        ]);

        return redirect()->route('users.details', ['userId' => $user->id])->with('success', 'Mot de passe modifié avec succès.');
    }

    public function deleteUser($id)
    {
        try {
            // Recherchez l'utilisateur par le code_personnel
            $utilisateur = User::find($id);

            if (!$utilisateur) {
                return response()->json(['message' => 'Utilisateur non trouvé.'], 404);
            }
            // Supprimez l'utilisateur
            // $utilisateur->delete();

            // Désactiver l'utilisateur
            $utilisateur->deactivate();
            return response()->json(['message' => 'Utilisateur supprimé avec succès.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la suppression de l\'utilisateur.', 'error' => $e->getMessage()], 500);
        }
    }
}
