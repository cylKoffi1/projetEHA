<?php

namespace App\Http\Controllers;

use App\Models\Caracteristique;
use App\Models\Domaine;
use App\Models\FamilleCaracteristique;
use App\Models\FamilleInfrastructure;
use App\Models\GroupeProjetPaysUser;
use App\Models\Infrastructure;
use App\Models\InfrastructureImage;
use App\Models\LocalitesPays;
use App\Models\Pays;
use App\Models\ProjetActionAMener;
use App\Models\ProjetInfrastructure;
use App\Models\TypeCaracteristique;
use App\Models\ValeurCaracteristique;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InfrastructureController extends Controller
{
    // Afficher la liste des infrastructures
    public function index()
    {
        $infrastructures = Infrastructure::with(['familleInfrastructure.domaine', 'localisation', 'projetInfra'])
            ->where('code_groupe_projet', session('projet_selectionne'))
            ->where('code_pays', session('pays_selectionne'))
            ->get();
    
        $domaines = Domaine::where('groupe_projet_code', session('projet_selectionne'))->get();
    
        $familles = FamilleInfrastructure::where('code_groupe_projet', session('projet_selectionne'))->get();
    
        // Récupère tous les niveaux uniques depuis les localisations associées aux infrastructures
        
        $niveaux = DB::table('localites_pays')
        ->join('decoupage_admin_pays', 'decoupage_admin_pays.num_niveau_decoupage', '=', 'localites_pays.id_niveau')
        ->join('decoupage_administratif', 'decoupage_administratif.code_decoupage', '=', 'decoupage_admin_pays.code_decoupage')
        ->join('pays', 'pays.id', '=', 'decoupage_admin_pays.id_pays')
        ->where('pays.alpha3', session('pays_selectionne'))
        ->where('localites_pays.id_pays', session('pays_selectionne'))
        ->select('decoupage_administratif.libelle_decoupage', 'localites_pays.id_niveau')
        ->distinct()
        ->orderBy('decoupage_administratif.libelle_decoupage')
        ->pluck('decoupage_administratif.libelle_decoupage', 'localites_pays.id_niveau');
    
        $mappingFamilleDomaine = FamilleInfrastructure::pluck('code_domaine', 'code_famille');
    
        return view('infrastructures.index', compact('infrastructures', 'domaines', 'familles', 'niveaux', 'mappingFamilleDomaine'));

    }
    public function getFamillesByDomaine($codeDomaine)
    {
        $projet = session('projet_selectionne');

        return FamilleInfrastructure::where('code_domaine', $codeDomaine)
            ->where('code_groupe_projet', $projet)
            ->select('idFamille', 'code_famille', 'libelleFamille')
            ->get();
    }

      
     // Afficher le formulaire de création
     public function create()
     {
         $familles = FamilleInfrastructure::where('code_groupe_projet', session('projet_selectionne'))->get();
         $localites = LocalitesPays::all();
         $pays = GroupeProjetPaysUser::with('pays')
         ->select('pays_code') // Sélectionne uniquement le code pays
         ->distinct() // Évite les doublons
         ->where('pays_code', session('pays_selectionne'))
         ->get()
         ->pluck('pays.nom_fr_fr', 'pays.alpha3') // Associe alpha3 avec le nom
         ->sort(); 
         $typeCaracteristiques = TypeCaracteristique::all();
         $paysCode = session('pays_selectionne');
        $nomPays = Pays::where('alpha3', $paysCode)->value('nom_fr_fr');

        $domaines = Domaine::where('groupe_projet_code', session('projet_selectionne'))->get();

         return view('infrastructures.create', compact('nomPays','familles', 'localites','pays', 'domaines', 'typeCaracteristiques'));
     }
 
     public function store(Request $request)
     {
         try {
             Log::info("Début du store() infrastructure", $request->all());
     
             $request->validate([
                 'libelle' => 'required|string|max:255',
                 'code_famille_infrastructure' => 'required|exists:familleinfrastructure,code_famille',
                 'code_localite' => 'required|exists:localites_pays,id',
             ]);
     
             $famille = FamilleInfrastructure::where('code_famille', $request->code_famille_infrastructure)->firstOrFail();
             $familleId = $famille->idFamille;
     
             Log::info("Famille trouvée", ['famille_id' => $familleId]);
     
             $prefix = $request->code_famille_infrastructure;

             // Récupérer le dernier code pour cette famille
             $lastInfrastructure = Infrastructure::where('code', 'like', $prefix . '%')
                 ->orderByDesc('code')
                 ->first();
             
             if ($lastInfrastructure) {
                 // Extraire la partie numérique, à partir du 4ème caractère
                 $lastNumber = (int)substr($lastInfrastructure->code, 3);
                 $nextNumber = $lastNumber + 1;
             } else {
                 $nextNumber = 1;
             }
             
             // Formater avec 8 zéros
             $code = $prefix . str_pad($nextNumber, 8, '0', STR_PAD_LEFT);
             
             Log::info("Code généré pour infrastructure", ['code' => $code]);
     
             $infrastructure = Infrastructure::create([
                 'code' => $code,
                 'libelle' => $request->libelle,
                 'code_famille_infrastructure' => $request->code_famille_infrastructure,
                 'code_groupe_projet' => session('projet_selectionne'),
                 'code_localite' => $request->code_localite,
                 'date_operation' => $request->date_operation,
                 'code_pays' => session('pays_selectionne'),
                 'latitude' => $request->latitude,
                 'longitude' => $request->longitude
             ]);
     
             Log::info("Infrastructure créée", ['infra_id' => $infrastructure->id]);
     
             if ($request->has('caracteristiques')) {
                 foreach ($request->input('caracteristiques', []) as $idCarac => $valeur) {
                     Log::info("Traitement caractéristique", ['idCaracteristique' => $idCarac, 'valeur_reçue' => $valeur]);
     
                     $caracValide = FamilleCaracteristique::where('idFamille', $familleId)
                         ->where('idCaracteristique', $idCarac)
                         ->exists();
     
                     if (!$caracValide) {
                         Log::warning("Caractéristique non autorisée pour cette famille", ['id' => $idCarac]);
                         return response()->json([
                             'error' => "La caractéristique (ID: $idCarac) n'est pas autorisée pour cette famille."
                         ], 422);
                     }
     
                     $caracteristique = Caracteristique::with('type', 'valeursPossibles')->findOrFail($idCarac);
                     $type = strtolower($caracteristique->type->libelleTypeCaracteristique);
                     $valeurFinale = null;
     
                     switch ($type) {
                         case 'liste':
                             $option = $caracteristique->valeursPossibles->firstWhere('valeur', $valeur)
                                 ?? $caracteristique->valeursPossibles->firstWhere('id', $valeur);
                             $valeurFinale = $option?->valeur;
                             Log::info("Valeur 'liste' convertie", ['finale' => $valeurFinale]);
                             break;
     
                         case 'boolean':
                             $valeurFinale = $valeur ? 1 : 0;
                             Log::info("Valeur 'boolean' convertie", ['finale' => $valeurFinale]);
                             break;
     
                         default:
                             $valeurFinale = $valeur;
                             Log::info("Valeur par défaut", ['finale' => $valeurFinale]);
                             break;
                     }
     
                     if ($valeurFinale !== null && $valeurFinale !== '') {
                         ValeurCaracteristique::create([
                             'infrastructure_code' => $code,
                             'idCaracteristique' => $idCarac,
                             'valeur' => $valeurFinale
                         ]);
     
                         Log::info("Valeur enregistrée", [
                             'infra_id' => $infrastructure->id,
                             'carac_id' => $idCarac,
                             'valeur' => $valeurFinale
                         ]);
                     }
                 }
             }
     
             if ($request->hasFile('gallery')) {
                 foreach ($request->file('gallery') as $file) {
                     if ($file->isValid()) {
                         $filename = $code . '_Infras_' . uniqid() . '.' . $file->getClientOriginalExtension();
                         $path = 'Data/Infrastructure/';
                         $file->move(public_path($path), $filename);
     
                         InfrastructureImage::create([
                             'infrastructure_code' => $code,
                             'chemin_image' => $path . $filename
                         ]);
     
                         Log::info("Image enregistrée", ['filename' => $filename]);
                     }
                 }
             }
     
             return response()->json([
                 'success' => 'Infrastructure créée avec succès.',
                 'redirect' => route('infrastructures.show', $infrastructure->id)
             ], 200);
     
         } catch (\Throwable $e) {
             Log::error("Erreur dans store()", ['exception' => $e]);
             return response()->json(['error' => 'Erreur : ' . $e->getMessage()], 500);
         }
     }
     
    
    

    // Afficher les détails d'une infrastructure
    public function show($id)
    {
        $infrastructure = Infrastructure::with(['familleInfrastructure', 'localisation', 'valeursCaracteristiques.caracteristique.type', 'valeursCaracteristiques.unite'])
            ->findOrFail($id);
    $typeCaracteristiques = TypeCaracteristique::all();
            
        return view('infrastructures.show', compact('infrastructure', 'typeCaracteristiques'));
    }

    // Afficher le formulaire d'édition
    public function edit($id)
    {
        $infrastructure = Infrastructure::with([
            'familleInfrastructure.domaine',
            'localisation',
            'valeursCaracteristiques.caracteristique.valeursPossibles',
            'valeursCaracteristiques.caracteristique.type',
            'valeursCaracteristiques.unite',
            'InfrastructureImage',
            'projetInfra',
        ])->findOrFail($id);
    
        $familles = FamilleInfrastructure::where('code_groupe_projet', session('projet_selectionne'))->get();
        $domaines = Domaine::where('groupe_projet_code', session('projet_selectionne'))->get();
        $typeCaracteristiques = TypeCaracteristique::all();
        $valeursExistantes = ValeurCaracteristique::where('infrastructure_code', $infrastructure->code)
            ->pluck('valeur', 'idCaracteristique')
            ->toArray();
        
       // dd($valeursExistantes);
        // Pays pour carte & localisation
        $pays = GroupeProjetPaysUser::with('pays')
            ->select('pays_code')
            ->distinct()
            ->where('pays_code', session('pays_selectionne'))
            ->get()
            ->pluck('pays.nom_fr_fr', 'pays.alpha3')
            ->sort();
    
        $paysCode = session('pays_selectionne');
        $nomPays = Pays::where('alpha3', $paysCode)->value('nom_fr_fr');
    
        // Niveaux de découpage (comme dans index)
        $niveaux = DB::table('localites_pays')
            ->join('decoupage_admin_pays', 'decoupage_admin_pays.num_niveau_decoupage', '=', 'localites_pays.id_niveau')
            ->join('decoupage_administratif', 'decoupage_administratif.code_decoupage', '=', 'decoupage_admin_pays.code_decoupage')
            ->join('pays', 'pays.id', '=', 'decoupage_admin_pays.id_pays')
            ->where('pays.alpha3', session('pays_selectionne'))
            ->where('localites_pays.id_pays', session('pays_selectionne'))
            ->select('decoupage_administratif.libelle_decoupage', 'localites_pays.id_niveau')
            ->distinct()
            ->orderBy('decoupage_administratif.libelle_decoupage')
            ->pluck('decoupage_administratif.libelle_decoupage', 'localites_pays.id_niveau');
    
        // Pour le JS : correspondance famille → domaine
        $mappingFamilleDomaine = FamilleInfrastructure::pluck('code_domaine', 'code_famille');
    
        return view('infrastructures.create', compact(
            'infrastructure',
            'familles',
            'domaines',
            'typeCaracteristiques',
            'pays',
            'nomPays',
            'niveaux',
            'mappingFamilleDomaine',
            'valeursExistantes'
        ));
    }
     
     
    /* public function edit($id)
     {
         $country = session('pays_selectionne');
         $group = session('projet_selectionne');
     
         $infrastructure = Infrastructure::with('localisation')->findOrFail($id);
         $familles = FamilleInfrastructure::all();
     
         // Ajout ici : récupérer toutes les localités disponibles
         $localites = LocalitesPays::where('id_pays', $country)
                         ->orderBy('libelle')
                         ->get();
     
         // Préparer les pays disponibles
         $pays = GroupeProjetPaysUser::with('pays')
             ->select('pays_code')
             ->where('pays_code', $country)
             ->get()
             ->filter(fn($item) => str_starts_with($item->code_projet, $country . $group))
             ->mapWithKeys(fn($item) => [$item->pays->alpha3 => $item->pays->nom_fr_fr])
             ->sort();
     
         $domaines = Domaine::where('groupe_projet_code', $group)->get();
     
         $selectedDomaineCode = $familles
             ->firstWhere('code_famille', $infrastructure->code_famille_infrastructure)
             ->code_domaine ?? null;
     
         return view('infrastructures.edit', compact(
             'infrastructure',
             'familles',
             'pays',
             'localites',
             'domaines',
             'selectedDomaineCode'
         ));
     }*/
     
 
     public function getByPays(Request $request)
{
    $request->validate(['pays_code' => 'required|string|size:3']);
    
    $localites = LocalitesPays::where('pays_code', $request->pays_code)
        ->orderBy('libelle')
        ->get(['id', 'code_rattachement', 'libelle']);
    
    return response()->json($localites);
}

public function getNiveaux(Request $request)
{
    $request->validate(['localite_id' => 'required|integer']);
    
    $localite = LocalitesPays::findOrFail($request->localite_id);
    
    return response()->json([
        'niveau' => $localite->niveau,
        'code_decoupage' => $localite->code_decoupage,
        'libelle_decoupage' => $localite->libelle_decoupage
    ]);
}

     // Mettre à jour une infrastructure
     public function update(Request $request, $id)
     {
         try {
             // 1. Validation des champs requis
             $request->validate([
                 'libelle' => 'required|string|max:255',
                 'code_famille_infrastructure' => 'required|exists:familleinfrastructure,code_famille',
                 'code_localite' => 'required|exists:localites_pays,id',
             ]);
     
             // 2. Récupération de l'infrastructure existante
             $infrastructure = Infrastructure::findOrFail($id);
             $ancienCode = $infrastructure->code;
     
             // 3. Déterminer si on doit générer un nouveau code
             if (
                 $request->libelle !== $infrastructure->libelle ||
                 $request->code_famille_infrastructure !== $infrastructure->code_famille_infrastructure
             ) {
                 // Génération du nouveau code
                 $nouveauCode = $request->code_famille_infrastructure .
                                strtoupper(substr(str_replace(' ', '', $request->libelle), 0, 3)) .
                                strtoupper(substr(md5(uniqid()), 0, 2));
     
                 // Vérifier si ce code est déjà utilisé ailleurs (autre enregistrement)
                 $codeExiste = Infrastructure::where('code', $nouveauCode)
                     ->where('id', '!=', $infrastructure->id)
                     ->exists();
     
                 if ($codeExiste) {
                     return response()->json([
                         'error' => "Le code généré ($nouveauCode) est déjà utilisé. Veuillez modifier le libellé ou la famille pour générer un code unique."
                     ], 400);
                 }
     
                 // Vérifier si l'ancien code est utilisé dans un projet
                 $utiliseDansProjet = ProjetActionAMener::where('Infrastrucrues_id', $ancienCode)->exists();
     
                 if ($utiliseDansProjet) {
                     return response()->json([
                         'error' => "Impossible de modifier le code car il est déjà utilisé dans un projet."
                     ], 400);
                 }
     
                 // Tout est OK : on met à jour le code
                 $infrastructure->code = $nouveauCode;
             }
     
             // 4. Mise à jour des champs standards
             $infrastructure->libelle = $request->libelle;
             $infrastructure->code_famille_infrastructure = $request->code_famille_infrastructure;
             $infrastructure->code_localite = $request->code_localite;
             $infrastructure->date_operation = $request->date_operation;
             $infrastructure->latitude = $request->latitude;
             $infrastructure->longitude = $request->longitude;
     
             // 5. Gérer la photo si envoyée
             if ($request->hasFile('photo')) {
                 $file = $request->file('photo');
                 $filename = $infrastructure->code . '_' . time() . '.' . $file->getClientOriginalExtension();
                 $path = 'Data/Infrastructure/';
                 $file->move(public_path($path), $filename);
     
                 // Supprimer l’ancienne photo si elle existe
                 if ($infrastructure->imageInfras && file_exists(public_path($infrastructure->imageInfras))) {
                     @unlink(public_path($infrastructure->imageInfras));
                 }
     
                 $infrastructure->imageInfras = $path . $filename;
             }
     
             // 6. Sauvegarde
             $infrastructure->save();
     
             return response()->json(['success' => 'Infrastructure mise à jour avec succès.'], 200);
         } catch (\Throwable $e) {
             return response()->json(['error' => 'Erreur : ' . $e->getMessage()], 500);
         }
     }
     
     // Supprimer une infrastructure
     public function destroy($id)
     {
         $infrastructure = Infrastructure::findOrFail($id);
         $infrastructure->delete();
 
         return redirect()->route('infrastructures.index')
             ->with('success', 'Infrastructure supprimée avec succès.');
     }
 
     // Gestion des caractéristiques
     public function storeCaracteristique(Request $request, $id)
     {
         $request->validate([
             'idCaracteristique' => 'required|exists:caracteristiques,idCaracteristique',
             'idUnite' => 'required|exists:unites,idUnite',
             'valeur' => 'required',
         ]);
 
         ValeurCaracteristique::create([
             'idInfrastructure' => $id,
             'idCaracteristique' => $request->idCaracteristique,
             'idUnite' => $request->idUnite,
             'valeur' => $request->valeur,
         ]);
 
         return redirect()->back()->with('success', 'Caractéristique ajoutée avec succès.');
     }
 
     public function destroyCaracteristique($id)
     {
         $valeur = ValeurCaracteristique::findOrFail($id);
         $valeur->delete();
 
         return redirect()->back()->with('success', 'Caractéristique supprimée avec succès.');
     }

     public function historique($id)
    {
        $country = session('pays_selectionne');
        $group = session('projet_selectionne');
        $infraProjet = ProjetInfrastructure::with(['infra', 'statuts.statut'])
        ->where('code_projet', 'like', $country . $group . '%')->findOrFail($id);

        return view('infrastructures.historique', compact('infraProjet'));
    }
    
    public function print($id)
    {
        $infrastructure = Infrastructure::with([
            'familleInfrastructure',
            'localisation',
            'valeursCaracteristiques.caracteristique.type',
            'valeursCaracteristiques.unite'
        ])->findOrFail($id);

        $pdf = Pdf::loadView('pdf/caracteristiqueInfrastructure', compact('infrastructure'))
                ->setPaper('a4', 'portrait');

        return $pdf->stream('Fiche_Infrastructure_'.$infrastructure->code.'.pdf');
    }
    public function imprimer(Request $request)
    {
        $query = Infrastructure::with(['familleInfrastructure', 'localisation']);

        if ($request->domaine) {
            $query->whereHas('familleInfrastructure', fn($q) => $q->where('code_domaine', $request->domaine));
        }
        if ($request->famille) {
            $query->where('code_famille_infrastructure', $request->famille);
        }
        if ($request->niveau) {
            $query->whereHas('localisation', fn($q) => $q->where('niveau', 'like', '%' . $request->niveau . '%'));
        }

        $infrastructures = $query->get();

        $pdf = Pdf::loadView('pdf.infrastructures_filtrees', compact('infrastructures'))
            ->setPaper('a4', 'landscape');

        return $pdf->stream('infrastructures_filtrees.pdf');
    }

}
