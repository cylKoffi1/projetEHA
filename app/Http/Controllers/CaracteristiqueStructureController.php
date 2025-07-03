<?php

namespace App\Http\Controllers;

use App\Models\Caracteristique;
use App\Models\FamilleInfrastructure;
use App\Models\TypeCaracteristique;
use App\Models\Unite;
use App\Models\ValeurPossible;
use App\Services\CaracteristiqueBuilderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CaracteristiqueStructureController extends Controller
{
   
    public function getStructures($id)
    {
        $famille = FamilleInfrastructure::find($id);
        LOG::error($famille);
        if (!$famille) {
            return response()->json(['status' => 'error', 'message' => 'Famille non trouvée.'], 404);
        }
    
        $structure = (new CaracteristiqueBuilderService())->buildFromFamille($famille);
        
        return response()->json(['status' => 'success', 'data' => $structure]);
    }
    

    public function saveStructure(Request $request, $familleId)
    {
        DB::beginTransaction();
    
        try {
            Log::debug("💾 [saveStructure] Début - familleId: $familleId");
    
            $famille = FamilleInfrastructure::findOrFail($familleId);
            Log::debug("✅ Famille trouvée", ['id' => $famille->idFamille, 'nom' => $famille->libelleFamille]);
    
            $data = $request->validate([
                'structure' => 'required|array'
            ]);
            Log::debug("📦 Données reçues", ['structure' => $data['structure']]);
    
            // Supprimer les anciennes liaisons
            $caracteristiqueASupprimer = Caracteristique::where('code_famille', $famille->code_Ssys)->get();

            Log::debug("🧹 Vérification des anciennes caractéristiques à supprimer", [
                'famille' => $famille->code_Ssys,
                'total' => $caracteristiqueASupprimer->count()
            ]);

            foreach ($caracteristiqueASupprimer as $carac) {
                if ($carac->valeursCaracteristiques()->exists()) {
                    Log::debug("⛔️ Non supprimée : la caractéristique a des valeurs", [
                        'id' => $carac->idCaracteristique,
                        'valeur' => $carac->valeur,
                    ]);
                    return response()->json(['message' => 'Erreur: Des valeurs sont associées à certaines caractéristiques.']); // Ne pas supprimer
                }

                $carac->valeursPossibles()->delete(); // Supprimer options si c'est une liste
                $carac->familles()->detach();         // Retirer de la pivot
                $carac->delete();                     // Supprimer la caractéristique

                Log::debug("✅ Supprimée : caractéristique sans valeurs", [
                    'id' => $carac->idCaracteristique,
                    'name' => $carac->libelleCaracteristique,
                ]);
            }

            // Traiter la nouvelle structure
            $this->processStructure($data['structure'], null, [
                'id' => $famille->idFamille,
                'code' => $famille->code_Ssys,
            ]);
            Log::debug("🔁 Structure traitée avec succès");
    
            DB::commit();
    
            Log::debug("✅ Transaction validée");
            return response()->json([
                'status' => 'success',
                'message' => 'Structure enregistrée avec succès'
            ]);
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("❌ Erreur lors de saveStructure", [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);
    
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de l\'enregistrement: ' . $e->getMessage()
            ], 500);
        }
    }
    protected function processStructure(array $nodes, $parentId, array $familleData)
    {
        foreach ($nodes as $node) {
            // Créer ou mettre à jour la caractéristique
            $caracteristique = Caracteristique::updateOrCreate(
                ['idCaracteristique' => $node['id'] ?? null],
                [
                    'libelleCaracteristique' => $node['name'],
                    'idTypeCaracteristique' => $this->getTypeId($node['type']),
                    'parent_id' => $parentId,
                    'is_repetable' => $node['repeatable'] ?? false,
                    'ordre' => $node['order'] ?? 1,
                    'code_famille' => $familleData['code'], // ✅ Ligne ajoutée pour corriger l'erreur SQL
                    'description' => $node['description'] ?? null,
                ]
            );
            
            // Gérer l'unité pour les nombres
            if ($node['typeLabel'] === 'nombre' && isset($node['unit'])) {
                $unite = Unite::firstOrCreate(
                    ['symbole' => $node['unit']],
                    ['libelleUnite' => $node['unit']]
                );
                $caracteristique->idUnite = $unite->idUnite;
                $caracteristique->save();
            }
            
            // Gérer les valeurs possibles pour les listes
            if ($this->isListeType($node['type'])) {
                $this->syncValeursPossibles($caracteristique, $node['options']);
            }
            
          
            
            // Associer à la famille
            $caracteristique->familles()->syncWithoutDetaching($familleData['id']);
            
            // Traiter les enfants récursivement
            if (!empty($node['children'])) {
                $this->processStructure($node['children'], $caracteristique->idCaracteristique, $familleData);
            }
        }
    }
    
    protected function isListeType($typeId)
    {
        return TypeCaracteristique::find($typeId)?->libelleTypeCaracteristique === 'Liste';
    }
    
    
    protected function getTypeId($typeId)
    {
        $type = TypeCaracteristique::find($typeId);
    
        if (!$type) {
            throw new \Exception("Type de caractéristique inconnu : " . $typeId);
        }
    
        return $type->idTypeCaracteristique;
    }
    
    
    protected function syncValeursPossibles(Caracteristique $caracteristique, array $options)
    {
        // Supprimer les anciennes valeurs
        $caracteristique->valeursPossibles()->delete();
        
        // Ajouter les nouvelles
        foreach ($options as $option) {
            $created = ValeurPossible::create([
                'idCaracteristique' => $caracteristique->idCaracteristique,
                'valeur' => trim($option)
            ]);
            Log::info("Créé valeur possible : " . $created->valeur . ' pour ' . $created->idCaracteristique);
        }
        
    }

    /**
     * GET /familles/{famille}/caracteristiques
     * Affiche les caractéristiques hiérarchiques d'une famille.
     */
    public function index(FamilleInfrastructure $famille)
    {
        $caracs = $famille->caracteristiques()->with(['enfants', 'unite', 'type'])->get();

        // Optionnel : transformer en arbre
        $tree = $this->buildHierarchy($caracs);
     
        return response()->json($tree);
    }

    /**
     * POST /familles/{famille}/caracteristiques
     * Enregistre une nouvelle structure hiérarchique complète.
     */
    public function store(Request $request, FamilleInfrastructure $famille)
    {
        $data = $request->all();

        if (!is_array($data)) {
            return response()->json(['status' => 'error', 'message' => 'Format de données invalide.'], 422);
        }

        DB::transaction(function () use ($famille, $data) {
            $famille->caracteristiques()->detach(); // Nettoyer les anciennes

            $this->storeTree($data, $famille);
        });

        return response()->json(['status' => 'success', 'message' => 'Caractéristiques enregistrées avec succès.']);
    }

    /**
     * PUT /familles/{famille}/caracteristiques
     * Remplace complètement les caractéristiques existantes.
     */
    public function update(Request $request, FamilleInfrastructure $famille)
    {
        $data = $request->all();

        DB::transaction(function () use ($famille, $data) {
            $famille->caracteristiques()->detach();
            Caracteristique::whereHas('familles', fn ($q) => $q->where('idFamille', $famille->idFamille))->delete();

            $this->storeTree($data, $famille);
        });

        return response()->json(['status' => 'success', 'message' => 'Caractéristiques mises à jour.']);
    }

    /**
     * DELETE /familles/{famille}/caracteristiques
     * Supprime toutes les caractéristiques associées.
     */
    public function destroy(FamilleInfrastructure $famille)
    {
        DB::transaction(function () use ($famille) {
            $ids = $famille->caracteristiques()->pluck('idCaracteristique');
            Caracteristique::whereIn('idCaracteristique', $ids)->delete();
            $famille->caracteristiques()->detach();
        });

        return response()->json(['status' => 'success', 'message' => 'Toutes les caractéristiques supprimées.']);
    }

    /**
     * GET /caracteristiques/{id}
     * (Optionnel) Récupère une seule caractéristique.
     */
    public function show($id)
    {
        $carac = Caracteristique::with(['unite', 'type', 'enfants'])->findOrFail($id);
        return response()->json($carac);
    }

    /**
     * PUT /caracteristiques/{id}
     * (Optionnel) Met à jour une seule caractéristique.
     */
    public function updateSingle(Request $request, $id)
    {
        $carac = Caracteristique::findOrFail($id);
        $carac->update($request->only([
            'libelleCaracteristique',
            'idTypeCaracteristique',
            'idUnite',
            'ordre',
            'is_repetable',
            'description'
        ]));

        return response()->json(['status' => 'success', 'message' => 'Caractéristique mise à jour.']);
    }

    /**
     * DELETE /caracteristiques/{id}
     * (Optionnel) Supprime une seule caractéristique.
     */
    public function destroySingle($id)
    {
        Caracteristique::destroy($id);
        return response()->json(['status' => 'success', 'message' => 'Caractéristique supprimée.']);
    }

    /**
     * Construit l'arbre hiérarchique depuis la liste plate.
     */
    public function buildHierarchy($caracs, $parentId = null) {
        return $caracs
            ->where('parent_id', $parentId)
            ->map(function ($carac) use ($caracs) {
                return [
                    'idCaracteristique' => $carac->idCaracteristique,
                    'libelleCaracteristique' => $carac->libelleCaracteristique,
                    'type' => $carac->type,
                    'unite' => $carac->unite,
                    'enfants' => $this->buildHierarchy($caracs, $carac->idCaracteristique)
                ];
            })
            ->values();
    }
    

    /**
     * Enregistre récursivement une structure hiérarchique.
     */
    private function storeTree(array $nodes, FamilleInfrastructure $famille, $parentId = null)
    {
        foreach ($nodes as $node) {
            $carac = Caracteristique::create([
                'libelleCaracteristique' => $node['name'] ?? '',
                'idTypeCaracteristique' => $node['type'] ?? null,
                'idUnite' => $node['unit'] ?? null,
                'parent_id' => $parentId,
                'ordre' => $node['order'] ?? 1,
                'is_repetable' => $node['repeatable'] ?? false,
                'description' => $node['description'] ?? null,
            ]);

            // Lier à la famille
            $famille->caracteristiques()->attach($carac->idCaracteristique);

            // Valeurs possibles ? (si modèle ValeurPossible disponible)
            if (!empty($node['options']) && is_array($node['options'])) {
                foreach ($node['options'] as $val) {
                    if (!empty($val)) {
                        $carac->valeursPossibles()->create(['valeur' => $val]);
                    }
                }
            }

            // Conditions (optionnelles) ?
            // À implémenter si table disponible (non dans ton code actuel)

            // Traitement récursif
            if (!empty($node['children'])) {
                $this->storeTree($node['children'], $famille, $carac->idCaracteristique);
            }
        }
    }
}

