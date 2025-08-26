<?php

namespace App\Http\Controllers;

use App\Models\Caracteristique;
use App\Models\Domaine;
use App\Models\FamilleCaracteristique;
use App\Models\FamilleInfrastructure;
use App\Models\GroupeProjetPaysUser;
use App\Models\Infrastructure;
use App\Models\InfrastructureImage;
use App\Models\Localite;
use App\Models\LocalitesPays;
use App\Models\Pays;
use App\Models\ProjetActionAMener;
use App\Models\ProjetInfrastructure;
use App\Models\TypeCaracteristique;
use App\Models\UniteDerivee;
use App\Models\ValeurCaracteristique;
use App\Services\FileProcService;
use App\Services\GridFsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;

use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Color\Color;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class InfrastructureController extends Controller
{


    // Afficher la liste des infrastructures
    public function index()
    {
        $infrastructures = Infrastructure::with(['familleInfrastructure.familleDomaine', 'localisation', 'projetInfra'])
            ->where('code_groupe_projet', session('projet_selectionne'))
            ->where('code_pays', session('pays_selectionne'))
            ->get();
        
        $localite = LocalitesPays::where('id_pays', session('pays_selectionne'))->get();
        $domaines = Domaine::where('groupe_projet_code', session('projet_selectionne'))->get();
        $infrasExistantes = Infrastructure::where('code_groupe_projet', session('projet_selectionne'))
        ->where('code_pays', session('pays_selectionne'))
        ->get();
        $familles = FamilleInfrastructure::where('famille_domaine.code_groupe_projet', session('projet_selectionne'))
        ->join('famille_domaine', 'familleinfrastructure.code_Ssys', '=', 'famille_domaine.code_Ssys')->get();
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
    
        $mappingFamilleDomaine = FamilleInfrastructure::pluck('code_Ssys', 'code_Ssys');
    
        return view('infrastructures.index', compact('infrasExistantes','infrastructures', 'domaines', 'familles', 'niveaux', 'mappingFamilleDomaine', 'localite'));

    }
    public function getFamillesByDomaine($codeDomaine)
    {
        $projet = session('projet_selectionne');

        return FamilleInfrastructure::where('famille_domaine.code_domaine', $codeDomaine)
        ->join('famille_domaine', 'familleinfrastructure.code_Ssys', '=', 'famille_domaine.code_Ssys')
            ->where('famille_domaine.code_groupe_projet', $projet)
            ->select('familleinfrastructure.idFamille', 'familleinfrastructure.code_Ssys', 'familleinfrastructure.libelleFamille')
            ->get();
    }

      
     // Afficher le formulaire de création
     public function create()
     {
         $familles = FamilleInfrastructure::where('famille_domaine.code_groupe_projet', session('projet_selectionne'))
        ->join('famille_domaine', 'familleinfrastructure.code_Ssys', '=', 'famille_domaine.code_Ssys')->get();
       
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
        $caracs = [];
        $valeurs = [];
        $domaines = Domaine::where('groupe_projet_code', session('projet_selectionne'))->get();
        $infrasExistantes = Infrastructure::where('code_groupe_projet', session('projet_selectionne'))
        ->where('code_pays', session('pays_selectionne'))
        ->get();

       
        $unitesDerivees = UniteDerivee::with('uniteBase')
            ->get()
            ->groupBy('id_unite_base');
         return view('infrastructures.create', compact('unitesDerivees','caracs','valeurs','nomPays','familles', 'localites','pays', 'domaines', 'typeCaracteristiques',
        'infrasExistantes'));
     }
     public function getCaracteristiques($idFamille)
     {
         $caracs = Caracteristique::where('code_famille', $idFamille)
             ->with(['type', 'valeursPossibles', 'enfants.type', 'enfants.valeursPossibles'])
             ->get();
     
         // Regrouper par ID
         $grouped = $caracs->groupBy('parent_id');
     
         // Construction récursive de l'arbre
         $buildTree = function ($parentId = null) use (&$buildTree, $grouped) {
             return ($grouped[$parentId] ?? collect())->map(function ($carac) use ($buildTree) {
                 $carac->libelleTypeCaracteristique = $carac->type->libelleTypeCaracteristique ?? null;
                 if ($carac->isListe()) {
                     $carac->valeurs_possibles = $carac->valeursPossibles->pluck('valeur')->toArray();
                 }
                 $carac->enfants = $buildTree($carac->idCaracteristique);
                 return $carac;
             })->values();
         };
     
         $tree = $buildTree();
     
         return response()->json($tree);
     }
     
     
     public function store(Request $request)
     {
         try {
             Log::info("Début du store() infrastructure", $request->all());
     
             $request->validate([
                 'libelle' => 'required|string|max:255',
                 'code_famille_infrastructure' => 'required|exists:familleinfrastructure,code_Ssys',
                 'code_localite' => 'required|exists:localites_pays,id',
             ]);
     
             $famille = FamilleInfrastructure::where('code_Ssys', $request->code_famille_infrastructure)->firstOrFail();
             $familleId = $famille->idFamille;
     
             Log::info("Famille trouvée", ['famille_id' => $familleId]);
             
             $codePays = session('pays_selectionne'); // Exemple: 'CIV'
             $codeFamille = $request->code_famille_infrastructure; // Exemple: 'HEB'
             $prefix = $codePays . $codeFamille;
         

             $last = Infrastructure::where('code', 'like', $prefix . '%')->orderByDesc('code')->first();
             $nextNumber = $last ? ((int) substr($last->code, strlen($prefix))) + 1 : 1;
             $code = $prefix . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

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
             // Gestion du rattachement à une infrastructure mère
                if ($request->filled('code_infras_rattacher')) {
                    $infraMere = Infrastructure::where('code', $request->code_infras_rattacher)->first();

                    if ($infraMere) {
                        $infrastructure->code_localite = $infraMere->code_localite;
                        $infrastructure->latitude = $infraMere->latitude;
                        $infrastructure->longitude = $infraMere->longitude;
                        $infrastructure->code_infras_rattacher = $infraMere->code;
                        $infrastructure->save(); // Sauvegarde mise à jour
                    }
                }

            
     
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
                         case 'Liste':
                             $option = $caracteristique->valeursPossibles->firstWhere('valeur', $valeur)
                                 ?? $caracteristique->valeursPossibles->firstWhere('id', $valeur);
                             $valeurFinale = $option?->valeur;
                             Log::info("Valeur 'Liste' convertie", ['finale' => $valeurFinale]);
                             break;
     
                         case 'Boolean':
                             $valeurFinale = $valeur ? 1 : 0;
                             Log::info("Valeur 'Boolean' convertie", ['finale' => $valeurFinale]);
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
                             'valeur' => $valeurFinale,
                             'idUnite' => $infrastructure->idUnite,
                             'ordre' => $infrastructure->ordre,
                             'parent_saisie_id' => $infrastructure->parent_saisie_id,
                             'description' => $infrastructure->description
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
                    if (!$file || !$file->isValid()) continue;
            
                    // Envoi direct dans GridFS + enregistrement en MySQL (fichiers)
                    $res = app(FileProcService::class)->handle([
                        'owner_type'  => 'Infrastructure',
                        'owner_id'    => (string)$infrastructure->code, 
                        'categorie'   => 'INFRA_IMAGE',
                        'file'        => $file,
                        'uploaded_by' => optional($request->user())->id,
                    ]);
            
                    // On ne remplit plus chemin_image (legacy) : on met l’ID du fichier
                    InfrastructureImage::create([
                        'infrastructure_code' => $infrastructure->code,
                        'chemin_image'        => $res['id'],  
                    ]);
            
                    Log::info("Image infra enregistrée", [
                        'infra_code' => $infrastructure->code,
                        'fichier_id' => $res['id'],
                        'mime'       => $res['mime'] ?? null,
                        'size'       => $res['size'] ?? null,
                    ]);
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
     
    

     public function updateCaracteristiques(Request $request, Infrastructure $infrastructure)
     {
         try {
             if (!$infrastructure) {
                 Log::warning('🚫 Infrastructure introuvable.');
                 return redirect()
                     ->route('infrastructures.index')
                     ->with('error', 'Infrastructure introuvable.');
             }
     
             DB::beginTransaction();
     
             Log::debug('🔁 Début mise à jour des caractéristiques', [
                 'infrastructure_id' => $infrastructure->id,
                 'code' => $infrastructure->code,
                 'caracteristiques' => $request->input('caracteristiques', [])
             ]);
     
             foreach ($request->input('caracteristiques', []) as $idKey => $valeur) {
                 // 🔧 Nettoyer l'ID si formaté avec "new_"
                 $idCaracteristique = is_string($idKey) && str_starts_with($idKey, 'new_')
                     ? intval(str_replace('new_', '', $idKey))
                     : intval($idKey);
     
                 Log::debug("🧼 ID nettoyé : {$idKey} → {$idCaracteristique}");
     
                 // Vérifier que la caractéristique existe
                 $caracteristique = Caracteristique::with('type', 'valeursPossibles')->find($idCaracteristique);
     
                 if (!$caracteristique) {
                     Log::warning("⚠️ ID de caractéristique introuvable : {$idKey}");
                     continue;
                 }
     
                 // Rechercher une valeur existante
                 $valeurExistante = ValeurCaracteristique::where('infrastructure_code', $infrastructure->code)
                     ->where('idCaracteristique', $idCaracteristique)
                     ->first();
     
                 if ($valeurExistante) {
                     Log::debug('✏️ Mise à jour valeur existante', [
                         'id' => $valeurExistante->id,
                         'ancienne_valeur' => $valeurExistante->valeur,
                         'nouvelle_valeur' => $valeur
                     ]);
     
                     $valeurExistante->update([
                         'valeur' => $valeur,
                         'idUnite' => $caracteristique->idUnite,
                         'parent_saisie_id' => $caracteristique->parent_id,
                         'uniteDerivee' => $request->input("unites_derivees.$idCaracteristique"),
                         'ordre' => $caracteristique->ordre,
                     ]);
                 } else {
                     Log::debug('➕ Création nouvelle valeur', [
                         'caracteristique_id' => $idCaracteristique,
                         'valeur' => $valeur
                     ]);
     
                     ValeurCaracteristique::create([
                         'idCaracteristique' => $idCaracteristique,
                         'valeur' => $valeur,
                         'infrastructure_code' => $infrastructure->code,
                         'idUnite' => $caracteristique->idUnite,                         
                        'idUniteDerivee' => $request->input("unites_derivees.$idCaracteristique"),
                         'parent_saisie_id' => $caracteristique->parent_id,
                         'ordre' => $caracteristique->ordre,
                     ]);
                 }
             }
     
             DB::commit();
     
             Log::info('✅ Caractéristiques enregistrées avec succès', [
                 'infrastructure_code' => $infrastructure->code,
                 'total_traitées' => count($request->input('caracteristiques', []))
             ]);
     
             return redirect()
                 ->route('infrastructures.show', $infrastructure->id)
                 ->with('success', 'Caractéristiques mises à jour avec succès.');
         } catch (\Exception $e) {
             DB::rollBack();
     
             Log::error('❌ Erreur lors de la mise à jour des caractéristiques', [
                 'message' => $e->getMessage(),
                 'line' => $e->getLine(),
                 'file' => $e->getFile(),
             ]);
     
             return redirect()
                 ->route('infrastructures.show', $infrastructure->id)
                 ->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
         }
     }
     
     
     
     

     private function buildCaracData(Infrastructure $infra): array
     {
         $caracsFamille = optional($infra->familleInfrastructure)->caracteristiques ?? collect();
         $valeurs       = $infra->valeursCaracteristiques->keyBy('idCaracteristique');
         $groupedCaracs = $caracsFamille->groupBy('groupe');
     
         return compact('caracsFamille','valeurs','groupedCaracs');
     }
     
     public function show($id)
     {
         $infrastructure = Infrastructure::with([
             'familleInfrastructure.caracteristiques.valeursPossibles',
             'valeursCaracteristiques.caracteristique.type',
             'valeursCaracteristiques.caracteristique.valeursPossibles',
             'valeursCaracteristiques.unite',
             'valeursCaracteristiques.uniteDerivee',
             'localisation',
         ])->findOrFail($id);
     
         ['caracsFamille'=>$caracsFamille,'valeurs'=>$valeurs,'groupedCaracs'=>$groupedCaracs]
             = $this->buildCaracData($infrastructure);
     
         $unitesDerivees       = UniteDerivee::with('uniteBase')->get()->groupBy('id_unite_base');
         $typeCaracteristiques = TypeCaracteristique::all();
     
         return view('infrastructures.show', compact(
             'infrastructure','typeCaracteristiques','unitesDerivees',
             'caracsFamille','valeurs','groupedCaracs'
         ));
     }
     

    // Afficher le formulaire d'édition
    public function edit($id)
    {
        $infrastructure = Infrastructure::with([
            'familleInfrastructure.familleDomaine',
            'localisation',
            'valeursCaracteristiques.caracteristique.valeursPossibles',
            'valeursCaracteristiques.caracteristique.type',
            'valeursCaracteristiques.unite',
            'valeursCaracteristiques.uniteDerivee',
            'InfrastructureImage',
            'projetInfra',
        ])->findOrFail($id);
        $infrasExistantes = Infrastructure::where('code_groupe_projet', session('projet_selectionne'))
        ->where('code_pays', session('pays_selectionne'))
        ->get();

        $caracs = Caracteristique::where('code_famille', $infrastructure->familleInfrastructure->code_Ssys)
            ->with(['type', 'valeursPossibles', 'enfants.type', 'enfants.valeursPossibles'])
            ->get();

        $familles = FamilleInfrastructure::where('famille_domaine.code_groupe_projet', session('projet_selectionne'))
        ->join('famille_domaine', 'familleinfrastructure.code_Ssys', '=', 'famille_domaine.code_Ssys')->get();
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
        $mappingFamilleDomaine = FamilleInfrastructure::pluck('code_Ssys', 'code_Ssys');
       
       
        $unitesDerivees = UniteDerivee::with('uniteBase')
            ->get()
            ->groupBy('id_unite_base');
        return view('infrastructures.create', compact(
            'infrastructure',
            'familles',
            'domaines',
            'typeCaracteristiques',
            'pays',
            'nomPays',
            'niveaux',
            'mappingFamilleDomaine',
            'valeursExistantes',
            'caracs' ,
            'infrasExistantes',
            'unitesDerivees'
        ));
    }
    public function deleteImage($id, $code)
    {
        try {
            $image = InfrastructureImage::findOrFail($id);
    
            // Vérifie que l'image appartient bien à cette infra
            if ($image->infrastructure_code !== $code) {
                return response()->json(['error' => 'Code infrastructure non valide.'], 403);
            }
    
            // Cas 1 : stockage GridFS (chemin_image = id fichiers)
            if ($image->chemin_image && ctype_digit((string)$image->chemin_image)) {
                $row = DB::table('fichiers')->where('id', (int)$image->chemin_image)->first();
                if ($row) {
                    // Supprimer dans GridFS
                    app(GridFsService::class)->delete($row->gridfs_id);
                    // Supprimer la ligne MySQL
                    DB::table('fichiers')->where('id', $row->id)->delete();
                }
            } 
            // Cas 2 : fallback legacy (ancien fichier physique dans public/)
            else {
                $imagePath = public_path($image->chemin_image);
                if ($image->chemin_image && file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
    
            // Supprime l'enregistrement InfrastructureImage
            $image->delete();
    
            return response()->json(['success' => 'Image supprimée.']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Image non trouvée.'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la suppression : ' . $e->getMessage()], 500);
        }
    }
    
    
 
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

    public function update(Request $request, $id)
    {
        try {
            Log::info("🛠️ Début update infrastructure ID: $id", $request->all());

            $request->validate([
                'libelle' => 'required|string|max:255',
                'code_famille_infrastructure' => 'required|exists:familleinfrastructure,code_Ssys',
                'code_localite' => 'required|exists:localites_pays,id',
            ]);

            $infrastructure = Infrastructure::findOrFail($id);
            $ancienCode = $infrastructure->code;

            if (
                $request->libelle !== $infrastructure->libelle ||
                $request->code_famille_infrastructure !== $infrastructure->code_Ssys
            ) {
                if (ProjetActionAMener::where('Infrastrucrues_id', $ancienCode)->exists()) {
                    return response()->json(['error' => 'Impossible de modifier le code : déjà utilisé dans un projet.'], 400);
                }
        
                $codePays = session('pays_selectionne');
                $codeFamille = $request->code_famille_infrastructure;
                $prefix = $codePays . $codeFamille;
        
                    
                $last = Infrastructure::where('code', 'like', $prefix . '%')->orderByDesc('code')->first();
                $nextNumber = $last ? ((int) substr($last->code, strlen($prefix))) + 1 : 1;
                $nouveauCode = $prefix . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
        
                if (Infrastructure::where('code', $nouveauCode)->where('id', '!=', $infrastructure->id)->exists()) {
                    return response()->json([
                        'error' => "Le code généré ($nouveauCode) est déjà utilisé."
                    ], 400);
                }

                if (ProjetActionAMener::where('Infrastrucrues_id', $ancienCode)->exists()) {
                    return response()->json([
                        'error' => "Impossible de modifier le code : déjà utilisé dans un projet."
                    ], 400);
                }

                $infrastructure->code = $nouveauCode;
            }
               
            
            

            $infrastructure->libelle = $request->libelle;
            $infrastructure->code_Ssys = $request->code_famille_infrastructure;
            $infrastructure->code_localite = $request->code_localite;
            $infrastructure->date_operation = $request->date_operation;
            $infrastructure->latitude = $request->latitude;
            $infrastructure->longitude = $request->longitude;
            // Gestion du rattachement ou suppression
            if ($request->filled('code_infras_rattacher')) {
                $infraMere = Infrastructure::where('code', $request->code_infras_rattacher)->first();

                if ($infraMere) {
                    $infrastructure->code_localite = $infraMere->code_localite;
                    $infrastructure->latitude = $infraMere->latitude;
                    $infrastructure->longitude = $infraMere->longitude;
                    $infrastructure->code_infras_rattacher = $infraMere->code;
                }
            } else {
                $infrastructure->code_infras_rattacher = null; // Suppression du lien si décoché
            }

            if ($request->hasFile('gallery')) {
                $files = $request->file('gallery');
            
                // Si un seul fichier a été envoyé, on le met dans un tableau pour uniformiser
                if (!is_array($files)) {
                    $files = [$files];
                }
            
                foreach ($files as $file) {
                    if ($file->isValid()) {
                        $filename = $infrastructure->code . '_Infras_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
                        $relativePath = 'Data/Infrastructure/';
                        $absolutePath = base_path('../public_html/projectsBTP/' . $relativePath);
            
                        if (!file_exists($absolutePath)) {
                            mkdir($absolutePath, 0775, true);
                        }
            
                        $file->move($absolutePath, $filename);
            
                        InfrastructureImage::create([
                            'infrastructure_code' => $infrastructure->code,
                            'chemin_image' => $relativePath . $filename
                        ]);
            
                        Log::info("📷 Image galerie enregistrée", [
                            'infra_code' => $infrastructure->code,
                            'chemin_image' => $relativePath . $filename
                        ]);
                    }
                }
            }
            $infrastructure->save();            

            Log::info("✅ Infrastructure mise à jour avec succès", ['id' => $infrastructure->id]);

            return response()->json(['success' => 'Infrastructure mise à jour avec succès.'], 200);

        } catch (\Throwable $e) {
            Log::error("❌ Erreur lors de la mise à jour de l'infrastructure ID: $id", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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
             'ordre' => $request->ordre,
             'parent_saisie_id' => $request->parent_saisie_id,
             'description' => $request->description,
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
            'valeursCaracteristiques.unite',
            'valeursCaracteristiques.uniteDerivee',
            'projetInfra.projet.projetNaturesTravaux.natureTravaux',
            'projetInfra.projet.devise',
        ])->findOrFail($id);
        
        $projets = [];

        if ($infrastructure->projetInfra && $infrastructure->projetInfra->projet) {
            $projet = $infrastructure->projetInfra->projet;
        
            $projets[] = [
                'code_projet' => $projet->code_projet,
                'nature' => $projet->projetNaturesTravaux?->natureTravaux?->libelle ?? '-',
                'date_debut' => $projet->date_demarrage_prevue ? \Carbon\Carbon::parse($projet->date_demarrage_prevue)->format('d/m/Y') : '-',
                'date_fin' => $projet->date_fin_prevue ? \Carbon\Carbon::parse($projet->date_fin_prevue)->format('d/m/Y') : '-',
                'cout' => number_format($projet->cout_projet, 0, ',', ' ') ?? '-',
                'devise' => $projet->devise?->monnaie ?? '-',
            ];
        }
        $armoiriePath = public_path(auth()->user()?->paysSelectionne()?->armoirie);

       
       
        $url = route('infrastructures.show', $infrastructure->id); // ou autre route


        $qrCode = new QrCode(
            data: $url,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 300, // taille du QR
            margin: 10,
            foregroundColor: new Color(0, 0, 0),
            backgroundColor: new Color(255, 255, 255),
        );
        
        // ✅ Récupère l’armoirie du pays sélectionné
        $armoiriePath = public_path(auth()->user()?->paysSelectionne()?->armoirie);
        
        $logo = null;
        if (file_exists($armoiriePath)) {
            $logo = Logo::create($armoiriePath)->setResizeToWidth(60); // ajuste selon tes besoins
        }
        
        $writer = new PngWriter();
        $result = $writer->write($qrCode, $logo); // 💥 Logo inséré ici
        
        // Encode en base64 pour affichage direct dans la vue
        $qrCodeBase64 = base64_encode($result->getString());
    
        // Génération PDF
        $pdf = \PDF::loadView('pdf.caracteristiqueInfrastructure', [
            'infrastructure' => $infrastructure,
            'qrCodeBase64' => $qrCodeBase64,
            'projets' => $projets
        ])->setPaper('a4', 'portrait');
    
    
        return $pdf->stream('Fiche_Infrastructure_' . $infrastructure->code . '.pdf');
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
