<?php

namespace App\Http\Controllers\ParSpecifique;


use App\Http\Controllers\Controller;
use App\Models\Acteur;
use App\Models\Ecran;
use App\Models\FormeJuridique;
use App\Models\Genre;
use App\Models\GroupeProjetPaysUser;
use App\Models\Pays;
use App\Models\PaysUser;
use App\Models\PersonneMorale;
use App\Models\PersonnePhysique;
use App\Models\Pieceidentite;
use App\Models\Possederpiece;
use App\Models\Representants;
use App\Models\SecteurActivite;
use App\Models\SecteurActiviteActeur;
use App\Models\SituationMatrimonial;
use App\Models\TypeActeur;
use App\Models\TypeFinancement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class ActeurController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Récupérer le pays de l'utilisateur connecté via GroupeProjetPaysUser
            $user = Auth::user();
            $userCountryCode = GroupeProjetPaysUser::where('user_id', $user->acteur_id)->value('pays_code');

            // Vérifiez si un pays est sélectionné dans la session
            $paysSelectionne = session('pays_selectionne');
            if (!$paysSelectionne) {
                return redirect()->route('admin', ['ecran_id' => $request->input('ecran_id')])
                    ->with('error', 'Veuillez contacter l\'administrateur pour vous attribuer un pays avant de continuer.');
            }
            $tousPays = Pays::whereNotIn('id',  [0, 300, 301, 302, 303, 304])->get();


            $pays = Pays::where('alpha3', $paysSelectionne)->first();
            $ecran = Ecran::find($request->input('ecran_id'));
            $groupe = auth()->user()->groupe_utilisateur_id;

            $TypeActeurs = TypeActeur::all();
            // Filtrer les acteurs selon le statut (activé ou désactivé)
            $filter = $request->input('filter'); // Récupérer le paramètre "filter" de la requête
            if ($filter === 'inactif') {
                // Afficher uniquement les acteurs désactivés
                $acteurs = Acteur::with(['pays', 'type'])
                    ->withInactive() // Supprime la portée globale
                    ->where('is_active', 0)
                    ->where('code_pays', $paysSelectionne) // Utilisation de `code_pays` au lieu de `pays_code`
                    ->get();
            } else {
                // Afficher tous les acteurs associés au pays
                $acteurs = Acteur::with(['pays', 'type'])
                    ->where('code_pays', $paysSelectionne) // Utilisation de `code_pays` au lieu de `pays_code`
                    ->get();
            }
            $SecteurActivites = SecteurActivite::all();
            $SituationMatrimoniales = SituationMatrimonial::all();
            $genres = Genre::all();
            $Pieceidentite = Pieceidentite::all();
            $formeJuridiques = FormeJuridique::all();
            $typeFinancements = TypeFinancement::all();

            $acteurRepres = DB::table('acteur as a')
            ->join('personne_physique as pp', 'pp.code_acteur', '=', 'a.code_acteur')
            ->select('a.code_acteur', 'a.libelle_long', 'a.libelle_court', 'pp.telephone_mobile', 'pp.telephone_bureau', 'pp.email')
            ->where('a.type_acteur', 'etp')
            ->where('a.code_pays',  $pays->alpha3)
            ->get();
          //  dd( $acteurRepres );
            return view('parSpecifique.Acteur', compact('acteurRepres','tousPays','typeFinancements','formeJuridiques','Pieceidentite','genres','SituationMatrimoniales','SecteurActivites','ecran', 'TypeActeurs', 'acteurs', 'pays', 'filter'));
        } catch (\Exception $e) {
            Log::error("Erreur lors de la récupération des acteurs : " . $e->getMessage());
            return redirect()->back()->withErrors('Une erreur est survenue lors du chargement des acteurs.');
        }
    }





    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $acteur = new Acteur();
            $acteur->libelle_long = $request->libelle_long ?? $request->nom;
            $acteur->libelle_court = $request->libelle_court ?? $request->prenom;
            $acteur->type_acteur = $request->type_acteur;
            $acteur->email = $request->emailI ?? $request->emailRL ?? null;
            $acteur->telephone = $request->telephoneBureauIndividu ?? $request->telephone1RL ?? null;
            $acteur->adresse = $request->adresseSiegeIndividu ?? $request->AdresseSiègeEntreprise ?? null;
            $acteur->code_pays = $request->code_pays;
            $acteur->is_user = false;
            $acteur->type_financement = $request->type_financement;
            $acteur->save();
    
            // 📷 Photo
            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                $filename = 'acteur_' . time() . '.' . $file->getClientOriginalExtension();
                $path = 'Data/acteur/';
                $file->move(public_path($path), $filename);
                $acteur->photo = $path . $filename;
                $acteur->save();
            }
    
            // 🧑🏻‍💼 Personne Physique
            if ($request->type_personne == 'physique') {
                $physique = new PersonnePhysique([
                    'code_acteur' => $acteur->code_acteur,
                    'nom' => $request->nom,
                    'prenom' => $request->prenom,
                    'date_naissance' => $request->date_naissance,
                    'nationalite' => $request->nationnalite,
                    'email' => $request->emailI,
                    'code_postal' => $request->CodePostalI,
                    'adresse_postale' => $request->AdressePostaleIndividu,
                    'adresse_siege' => $request->adresseSiegeIndividu,
                    'telephone_bureau' => $request->telephoneBureauIndividu,
                    'telephone_mobile' => $request->telephoneMobileIndividu,
                    'num_fiscal' => $request->numeroFiscal,
                    'genre_id' => $request->genre,
                    'situation_matrimoniale_id' => $request->situationMatrimoniale,
                    'is_active' => true
                ]);
                $physique->save();
    
                // 💳 Pièce d'identité
                if ($request->piece_identite && $request->numeroPiece) {
                    Possederpiece::create([
                        'idPieceIdent' => $request->piece_identite,
                        'idPersonnePhysique' => $acteur->code_acteur,
                        'NumPieceIdent' => $request->numeroPiece,
                        'DateEtablissement' => $request->dateEtablissement,
                        'DateExpiration' => $request->dateExpiration,
                    ]);
                }
    
                // 🧩 Secteurs d'activité
                if ($request->filled('SecteurActI')) {
                    foreach ($request->SecteurActI as $secteur) {
                        SecteurActiviteActeur::create([
                            'code_acteur' => $acteur->code_acteur,
                            'code_secteur' => $secteur
                        ]);
                    }
                }
            }
    
            // 🏢 Personne Morale
            if ($request->type_personne == 'morale') {
                $morale = new PersonneMorale([
                    'code_acteur' => $acteur->code_acteur,
                    'raison_sociale' => $request->libelle_long,
                    'date_creation' => $request->date_creation,
                    'forme_juridique' => $request->FormeJuridique,
                    'num_immatriculation' => $request->NumeroImmatriculation,
                    'nif' => $request->nif,
                    'rccm' => $request->rccm,
                    'capital' => $request->CapitalSocial,
                    'numero_agrement' => $request->Numéroagrement,
                    'code_postal' => $request->CodePostaleEntreprise,
                    'adresse_postale' => $request->AdressePostaleEntreprise,
                    'adresse_siege' => $request->AdresseSiègeEntreprise,
                ]);
                $morale->save();
    
                // 👨‍⚖️ Représentants Légaux
                if ($request->filled('nomRL')) {
                    $representants = is_array($request->nomRL) ? $request->nomRL : [$request->nomRL];
                    foreach ($representants as $repId) {
                        Representants::create([
                            'entreprise_id' => $morale->code_acteur,
                            'representant_id' => $repId,
                            'role' => 'Représentant Légal',
                            'idPays' => $acteur->code_pays,
                            'date_représentation' => Carbon::today()
                        ]);
                    }
                }
    
                // 📞 Personnes de Contact
                if ($request->filled('nomPC')) {
                    foreach ($request->nomPC as $pcId) {
                        Representants::create([
                            'entreprise_id' => $morale->code_acteur,
                            'representant_id' => $pcId,
                            'role' => 'Personne de Contact',
                            'idPays' => $acteur->code_pays,
                            'date_représentation' => Carbon::today()
                        ]);
                    }
                }
    
                // 🧩 Secteurs d'activité
                if ($request->filled('secteurActivite')) {
                    foreach ($request->secteurActivite as $secteur) {
                        SecteurActiviteActeur::create([
                            'code_acteur' => $acteur->code_acteur,
                            'code_secteur' => $secteur
                        ]);
                    }
                }
            }
    
            DB::commit();
            Log::info("✅ Acteur ajouté avec succès : " . $acteur->libelle_long);
            return redirect()->back()->with('success', 'Acteur ajouté avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("❌ Erreur lors de l'enregistrement d'un acteur : " . $e->getMessage());
            return redirect()->back()->withErrors("Erreur lors de l'enregistrement : " . $e->getMessage());
        }
    }
    

    public function update(Request $request, $id)
    {
        try {
            $acteur = Acteur::with(['personnePhysique', 'personneMorale'])->findOrFail($id);
    
            // 🔄 Mise à jour de la photo
            if ($request->hasFile('photo')) {
                if ($acteur->photo && file_exists(public_path($acteur->photo))) {
                    unlink(public_path($acteur->photo));
                }
                $file = $request->file('photo');
                $filename = 'acteur_' . time() . '.' . $file->getClientOriginalExtension();
                $path = 'Data/acteur/';
                $file->move(public_path($path), $filename);
                $acteur->photo = $path . $filename;
            }
    
            // 🔁 Mise à jour des champs généraux
            $acteur->libelle_long = $request->libelle_long ?? $acteur->libelle_long;
            $acteur->libelle_court = $request->libelle_court ?? $acteur->libelle_court;
            $acteur->type_acteur = $request->type_acteur;
            $acteur->email = $request->email ?? $request->emailRL ?? $request->emailI;
            $acteur->telephone = $request->telephone ?? $request->telephone1RL ?? $request->telephoneBureauIndividu;
            $acteur->adresse = $request->adresse ?? $request->adresseSiegeIndividu ?? $request->AdresseSiègeEntreprise;
            $acteur->code_pays = $request->code_pays;
            $acteur->type_financement = $request->type_financement;
            $acteur->save();
    
            // 🧑‍💼 Mise à jour Personne Physique
            if ($request->type_personne == 'physique') {
                if ($acteur->personnePhysique) {
                    $acteur->personnePhysique->update([
                        'nom' => $request->nom,
                        'prenom' => $request->prenom,
                        'date_naissance' => $request->date_naissance,
                        'nationalite' => $request->nationnalite,
                        'email' => $request->emailI,
                        'code_postal' => $request->CodePostalI,
                        'adresse_postale' => $request->AdressePostaleIndividu,
                        'adresse_siege' => $request->adresseSiegeIndividu,
                        'telephone_bureau' => $request->telephoneBureauIndividu,
                        'telephone_mobile' => $request->telephoneMobileIndividu,
                        'num_fiscal' => $request->numeroFiscal,
                        'genre_id' => $request->genre,
                        'situation_matrimoniale_id' => $request->situationMatrimoniale,
                    ]);
                }
    
                // 🔐 Mise à jour ou création de la pièce d'identité
                $piece = $acteur->possederpiece->first();

                if ($request->piece_identite && $request->numeroPiece) {
                    if ($piece) {
                        $piece->update([
                            'idPieceIdent' => $request->piece_identite,
                            'NumPieceIdent' => $request->numeroPiece,
                            'DateEtablissement' => $request->dateEtablissement,
                            'DateExpiration' => $request->dateExpiration
                        ]);
                    } else {
                        Possederpiece::create([
                            'idPieceIdent' => $request->piece_identite,
                            'idPersonnePhysique' => $acteur->personnePhysique->code_acteur,
                            'NumPieceIdent' => $request->numeroPiece,
                            'DateEtablissement' => $request->dateEtablissement,
                            'DateExpiration' => $request->dateExpiration
                        ]);
                    }
                }

    
                // 🧩 Mise à jour secteurs d’activité
                SecteurActiviteActeur::where('code_acteur', $id)->delete();
                if($request->SecteurActI){
                    foreach($request->SecteurActI as $secteurs){
                        SecteurActiviteActeur::create([
                            'code_acteur' => $acteur->code_acteur,
                            'code_secteur' => $secteurs
                        ]);
                    }
                }
            }
    
            // 🏢 Mise à jour Personne Morale
            if ($request->type_personne == 'morale') {
                if ($acteur->personneMorale) {
                    $acteur->personneMorale->update([
                        'raison_sociale' => $request->libelle_long,
                        'date_creation' => $request->date_creation,
                        'forme_juridique' => $request->FormeJuridique,
                        'num_immatriculation' => $request->NumeroImmatriculation,
                        'nif' => $request->nif,
                        'rccm' => $request->rccm,
                        'capital' => $request->CapitalSocial,
                        'numero_agrement' => $request->Numéroagrement,
                        'code_postal' => $request->CodePostaleEntreprise,
                        'adresse_postale' => $request->AdressePostaleEntreprise,
                        'adresse_siege' => $request->AdresseSiègeEntreprise,
                    ]);
                }
    
               // 🔁 Mise à jour ou création des représentants légaux
               if ($request->filled('nomRL')) {
                $nomRLs = is_array($request->nomRL) ? $request->nomRL : [$request->nomRL];
                
                    foreach ($nomRLs as $representantId) {
                        Representants::updateOrCreate(
                            [
                                'entreprise_id' => $acteur->personneMorale->code_acteur,
                                'representant_id' => $representantId,
                                'role' => 'Représentant Légal',
                            ],
                            [
                                'idPays' => $acteur->code_pays,
                                'date_représentation' => Carbon::today()
                            ]
                        );
                    }
                }
            

                // 🔁 Mise à jour ou création des personnes de contact
                if ($request->has('nomPC') && is_array($request->nomPC)) {
                    foreach ($request->nomPC as $contactId) {
                        Representants::updateOrCreate(
                            [
                                'entreprise_id' => $acteur->personneMorale->code_acteur,
                                'representant_id' => $contactId,
                                'role' => 'Personne de Contact',
                            ],
                            [
                                'idPays' => $acteur->code_pays,
                                'date_représentation' => Carbon::today()
                            ]
                        );
                    }
                }

                 // 🧩 Mise à jour secteurs
                SecteurActiviteActeur::where('code_acteur', $id)->delete();
                if($request->secteurActivite){
                    foreach($request->secteurActivite as $secteurs){
                        SecteurActiviteActeur::create([
                            'code_acteur' => $acteur->code_acteur,
                            'code_secteur' => $secteurs
                        ]);
                    }
                }
               
                            
            }
    
            Log::info("✅ Acteur mis à jour avec succès : " . $acteur->libelle_long);
            return redirect()->back()->with('success', 'Acteur mis à jour avec succès.');
        } catch (\Exception $e) {
            Log::error("❌ Erreur lors de la mise à jour d'un acteur : " . $e->getMessage());
            return redirect()->back()->withErrors("Une erreur est survenue : " . $e->getMessage());
        }
    }
    

    public function destroy($id)
    {
        try {
            // Trouver l'acteur spécifique par son ID (code_acteur)
            $acteur = Acteur::where('code_acteur', $id)->firstOrFail();

            // Désactiver l'acteur en mettant is_active à false
            $acteur->update(['is_active' => false]);

            return redirect()->back()->with('success', 'Acteur désactivé avec succès.');
        } catch (\Exception $e) {
            Log::error("Erreur lors de la désactivation d'un acteur : " . $e->getMessage());
            return redirect()->back()->withErrors('Une erreur est survenue lors de la désactivation de l\'acteur.');
        }
    }


    public function restore(Request $request, $id)
    {
        try {
            // Log pour vérifier l'ID reçu
            Log::info('ID reçu pour réactivation : ' . $id);

            // Désactiver les portées globales pour inclure les acteurs désactivés
            $acteur = Acteur::withoutGlobalScope('active')->where('code_acteur', $id)->first();

            // Si aucun acteur n'est trouvé, consignez une erreur
            if (!$acteur) {
                Log::error("Aucun acteur trouvé avec le code_acteur : " . $id);
                return redirect()->back()->withErrors('Aucun acteur trouvé pour réactivation.');
            }

            // Réactiver l'acteur
            $acteur->update(['is_active' => true]);

            return redirect()->route('acteurs.index', ['ecran_id' => $request->input('ecran_id')])
                ->with('success', 'Acteur réactivé avec succès.');
        } catch (\Exception $e) {
            Log::error("Erreur lors de la réactivation d'un acteur : " . $e->getMessage());
            return redirect()->back()->withErrors('Une erreur est survenue lors de la réactivation de l\'acteur.');
        }
    }


    public function search(Request $request)
    {
            $query = Acteur::where(function ($q) use ($request) {
            // Condition pour "libelle_court" correspondant à "nom"
            $q->where('libelle_court', 'LIKE', "%" . $request->search . "%");

            // Condition pour "libelle_long" correspondant à "prénoms"
            $q->orWhere('libelle_long', 'LIKE', "%" . $request->search . "%");

            // Concatenation des deux colonnes
            $q->orWhereRaw("CONCAT(libelle_court, ' ', libelle_long) LIKE ?", ["%" . $request->search . "%"]);
        })
        ->limit(5)
        ->get();

        return response()->json($query);

    }


    public function stores(Request $request)
    {
        $acteur = Acteur::create([
            'libelle_court' => $request->name,
            'libelle_long' => $request->name
        ]);
        return response()->json($acteur);
    }

    public function edit($id)
    {
        try {
            $acteur = Acteur::with([
                'personnePhysique', 
                'personneMorale',
                'secteurActiviteActeur',
                'possederpiece',
                'representants'
            ])->findOrFail($id);
    
            // Préparer les données pour le formulaire
            $data = [
                'id' => $acteur->code_acteur,
                'libelle_long' => $acteur->libelle_long,
                'libelle_court' => $acteur->libelle_court,
                'type_acteur' => $acteur->type_acteur,
                'email' => $acteur->email,
                'telephone' => $acteur->telephone,
                'adresse' => $acteur->adresse,
                'code_pays' => $acteur->code_pays,
                'photo' => $acteur->photo ? asset($acteur->photo) : null,
                'type_financement' => $acteur->type_financement,
                'is_user' => $acteur->is_user,
            ];
    
            // Ajouter les données spécifiques au type de personne
            if ($acteur->personnePhysique) {
                $data = array_merge($data, [
                    'type_personne' => 'physique',
                    'nom' => $acteur->personnePhysique->nom,
                    'prenom' => $acteur->personnePhysique->prenom,
                    'date_naissance' => $acteur->personnePhysique->date_naissance,
                    'nationnalite' => $acteur->personnePhysique->nationalite,
                    'CodePostalI' => $acteur->personnePhysique->code_postal,
                    'AdressePostaleIndividu' => $acteur->personnePhysique->adresse_postale,
                    'adresseSiegeIndividu' => $acteur->personnePhysique->adresse_siege,
                    'telephoneBureauIndividu' => $acteur->personnePhysique->telephone_bureau,
                    'telephoneMobileIndividu' => $acteur->personnePhysique->telephone_mobile,
                    'numeroFiscal' => $acteur->personnePhysique->num_fiscal,
                    'genre' => $acteur->personnePhysique->genre_id,
                    'situationMatrimoniale' => $acteur->personnePhysique->situation_matrimoniale_id,
                    'piece_identite' => $acteur->possederpiece->first() ? $acteur->possederpiece->first()->idPieceIdent : null,
                    'numeroPiece' => $acteur->possederpiece->first() ? $acteur->possederpiece->first()->NumPieceIdent : null,
                    'dateEtablissement' => $acteur->possederpiece->first() ? $acteur->possederpiece->first()->DateEtablissement : null,
                    'dateExpiration' => $acteur->possederpiece->first() ? $acteur->possederpiece->first()->DateExpiration : null,
                    'SecteurActI' => $acteur->secteurActiviteActeur->pluck('code_secteur')->toArray(),
                ]);
            } elseif ($acteur->personneMorale) {
                $data = array_merge($data, [
                    'type_personne' => 'morale',
                    'libelle_long' => $acteur->personneMorale->raison_sociale,
                    'libelle_court' => $acteur->libelle_court,
                    'date_creation' => $acteur->personneMorale->date_creation,
                    'FormeJuridique' => $acteur->personneMorale->forme_juridique,
                    'NumeroImmatriculation' => $acteur->personneMorale->num_immatriculation,
                    'nif' => $acteur->personneMorale->nif,
                    'rccm' => $acteur->personneMorale->rccm,
                    'CapitalSocial' => $acteur->personneMorale->capital,
                    'Numéroagrement' => $acteur->personneMorale->numero_agrement,
                    'CodePostaleEntreprise' => $acteur->personneMorale->code_postal,
                    'AdressePostaleEntreprise' => $acteur->personneMorale->adresse_postale,
                    'AdresseSiègeEntreprise' => $acteur->personneMorale->adresse_siege,
                    'nomRL' => $acteur->representants->where('role', 'Représentant Légal')->pluck('representant_id')->toArray(),
                    'emailRL' => $acteur->email,
                    'telephone1RL' => $acteur->telephone,
                    'telephone2RL' => $acteur->personneMorale->telephone_bureau ?? null,
                    'nomPC' => $acteur->representants->where('role', 'Personne de Contact')->pluck('representant_id')->toArray(),
                    'secteurActivite' => $acteur->secteurActiviteActeur->pluck('code_secteur')->toArray(),
                ]);
            }
    
            return response()->json($data);
        } catch (\Exception $e) {
            Log::error("Erreur lors de la récupération des données de l'acteur : " . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la récupération des données'], 500);
        }
    }

}

