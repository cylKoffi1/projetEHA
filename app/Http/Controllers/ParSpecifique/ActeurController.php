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
use App\Models\Pieceidentite;
use App\Models\SecteurActivite;
use App\Models\SituationMatrimonial;
use App\Models\TypeActeur;
use App\Models\TypeFinancement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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


            $pays = Pays::where('alpha3', $paysSelectionne)->first();
            $ecran = Ecran::find($request->input('ecran_id'));
            $groupe = auth()->user()->groupe_utilisateur_id;

            $TypeActeurs = match ($groupe) {
                'ab' => TypeActeur::where('cd_type_acteur', 'ogi')->get(),
                'ad' => TypeActeur::where('cd_type_acteur', 'eta')->get(),
                'ag' => TypeActeur::whereNotIn('cd_type_acteur', ['eta', 'ogi'])->get(),
                default => TypeActeur::all(),
            };
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
            return view('parSpecifique.Acteur', compact('typeFinancements','formeJuridiques','Pieceidentite','genres','SituationMatrimoniales','SecteurActivites','ecran', 'TypeActeurs', 'acteurs', 'pays', 'filter'));
        } catch (\Exception $e) {
            Log::error("Erreur lors de la récupération des acteurs : " . $e->getMessage());
            return redirect()->back()->withErrors('Une erreur est survenue lors du chargement des acteurs.');
        }
    }





    public function store(Request $request)
    {
        try {
            // 🔍 **Validation stricte des données**

            // 🔹 **Création de l'acteur**
            $acteur = new Acteur([
                'libelle_long' => $request->libelle_long ?? $request->prenom,
                'libelle_court' => $request->libelle_court ?? $request->nom,
                'type_acteur' => $request->type_acteur,
                'email' => $request->emailI ?? $request->emailRL,
                'telephone' => $request->telephoneBureau ?? $request->telephone1RL,
                'adresse' => $request->AdresseSiègeEntreprise ?? $request->adresseSiegeIndividu,
                'code_pays' => $request->code_pays,
                'is_user' => false,
                'type_financement' => $request->type_financementtype_financement,
            ]);

            // 📌 **Gestion du stockage de l'image**
            if ($request->hasFile('photo')) {
                $acteur->photo = $request->file('photo')->store('Data/acteur', 'public');
            }

            $acteur->save();

            // 🔹 **Gestion des Personnes Physiques**
            if ($request->type_personne == "physique") {
                $personne = PersonnePhysique::create([
                    'code_acteur' => $acteur->code_acteur,
                    'nom' => $request->nom,
                    'prenom' => $request->prenom,
                    'date_naissance' => $request->date_naissance,
                    'nationalite' => $request->nationalite,
                    'secteur_activite' => $request->SecteurActiviteIndividu,
                    'email' => $request->emailI,
                    'code_postal' => $request->CodePostalI,
                    'adresse_postale' => $request->AdressePostaleIndividu,
                    'adresse_siege' => $request->adresseSiegeIndividu,
                    'telephone_bureau' => $request->telephoneBureauIndividu,
                    'telephone_mobile' => $request->telephoneMobileIndividu,
                    'num_fiscal' => $request->numeroFiscal,
                    'genre_id' => $request->genre,
                    'situation_matrimoniale_id' => $request->situationMatrimoniale,
                    'is_active' => true,
                ]);

                // 📌 **Enregistrement de la pièce d'identité**
                if ($request->piece_identite && $request->numeroPiece) {
                    Possederpiece::create([
                        'idPieceIdent' => $request->piece_identite,
                        'idPersonnePhysique' => $personne->id, // Correction ici
                        'NumPieceIdent' => $request->numeroPiece,
                        'DateEtablissement' => $request->dateEtablissement,
                        'DateExpiration' => $request->dateExpiration,
                    ]);
                }
            }

            // 🔹 **Gestion des Personnes Morales**
            elseif ($request->type_personne == "morale") {
                $entreprise = PersonneMorale::create([
                    'code_acteur' => $acteur->code_acteur,
                    'raison_sociale' => $request->libelle_long,
                    'date_creation' => $request->date_creation,
                    'secteur_activite' => $request->SecteurActiviteEntreprise,
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

                // 📌 **Enregistrement du représentant légal**
                if ($request->nomRL && $request->emailRL) {
                    $representantRL = Acteur::create([
                        'libelle_long' => $request->nomRL,
                        'email' => $request->emailRL,
                        'telephone' => $request->telephone1RL ?? $request->telephone2RL,
                        'code_pays' => $request->code_pays,
                        'type_financement' => $request->type_financement,
                        'type_acteur' => 'Représentant Légal',
                        'is_user' => false
                    ]);

                    Representants::create([
                        'entreprise_id' => $entreprise->id,
                        'representant_id' => $representantRL->code_acteur,
                        'role' => 'Représentant Légal',
                    ]);
                }

                // 📌 **Enregistrement de la personne de contact**
                if ($request->nomPC && $request->emailPC) {
                    $personneContact = Acteur::create([
                        'libelle_long' => $request->nomPC,
                        'email' => $request->emailPC,
                        'telephone' => $request->telephone1PC ?? $request->telephone2PC,
                        'code_pays' => $request->code_pays,
                        'type_financement' => $request->type_financement,
                        'type_acteur' => 'Personne de Contact',
                        'is_user' => false
                    ]);

                    Representants::create([
                        'entreprise_id' => $entreprise->id,
                        'representant_id' => $personneContact->code_acteur,
                        'role' => 'Personne de Contact',
                    ]);
                }
            }

            Log::info("✅ Acteur ajouté avec succès : " . $acteur->libelle_long);
            return redirect()->back()->with('success', 'Acteur ajouté avec succès.');
        } catch (\Exception $e) {
            Log::error("❌ Erreur lors de l'enregistrement d'un acteur : " . $e->getMessage());
            return redirect()->back()->withErrors('Une erreur est survenue lors de l\'enregistrement de l\'acteur.');
        }
    }





    public function update(Request $request, $id)
    {
        try {
            // 🔍 **Validation des champs**
            $request->validate([
                'libelle_long' => 'required|string|max:255',
                'libelle_court' => 'required|string|max:255',
                'type_acteur' => 'string|max:5',
                'code_pays' => 'required|exists:pays,alpha3',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // 🔹 **Récupération de l'acteur existant**
            $acteur = Acteur::where('code_acteur', $id)->firstOrFail();

            // 📌 **Suppression de l'ancienne photo si une nouvelle est téléchargée**
            if ($request->hasFile('photo')) {
                $oldPhotoPath = public_path($acteur->Photo); // Chemin absolu de l'ancienne photo

                if (file_exists($oldPhotoPath) && is_file($oldPhotoPath)) {
                    unlink($oldPhotoPath); // Suppression de l'ancienne image
                }

                // 📌 **Sauvegarde de la nouvelle photo**
                $file = $request->file('photo');
                $extension = $file->getClientOriginalExtension();
                $filename = 'Acteur_' . time() . '.' . $extension;
                $destinationPath = public_path('Data/acteur/'); // Dossier de destination
                $file->move($destinationPath, $filename); // Déplacement du fichier

                // Mettre à jour le chemin de la photo
                $acteur->photo = 'Data/acteur/' . $filename;
            }

            // 📌 **Mise à jour des autres champs**
            $acteur->update([
                'libelle_long' => $request->libelle_long,
                'libelle_court' => $request->libelle_court,
                'type_acteur' => $request->type_acteur,
                'email' => $request->email,
                'telephone' => $request->telephone,
                'adresse' => $request->adresse,
                'code_pays' => $request->code_pays,
            ]);

            Log::info("✅ Acteur mis à jour avec succès : " . $acteur->libelle_long);
            return redirect()->back()->with('success', 'Acteur mis à jour avec succès.');

        } catch (\Exception $e) {
            Log::error("❌ Erreur lors de la mise à jour d'un acteur : " . $e->getMessage());
            return redirect()->back()->withErrors('Une erreur est survenue lors de la mise à jour de l\'acteur.');
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


}

