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
    
    private function validateHierarchyOrder(array $nodes, string $expectedPrefix = '')
    {
        foreach ($nodes as $i => $node) {
            $expectedOrder = $expectedPrefix ? "{$expectedPrefix}-" . ($i + 1) : (string)($i + 1);
    
            if (($node['order'] ?? null) !== $expectedOrder) {
                throw new \Exception("Ordre invalide : attendu '{$expectedOrder}', trouvé '{$node['order']}' pour '{$node['name']}'");
            }
    
            if (!empty($node['children'])) {
                $this->validateHierarchyOrder($node['children'], $expectedOrder);
            }
        }
    }
    
    public function saveStructure(Request $request, $familleId)
    {
        DB::beginTransaction();
    
        try {
            Log::info("💾 [saveStructure] Début - familleId: $familleId");
    
            $famille = FamilleInfrastructure::findOrFail($familleId);
            Log::info('[saveStructure] Structure reçue', [
                'payload' => $request->all()
            ]);
            
            $data = $request->validate([
                'structure' => 'required|array',
                'structure.*.name' => 'required|string|max:255',
                'structure.*.type' => 'required|exists:typecaracteristique,idTypeCaracteristique',
                'structure.*.order' => 'nullable|integer|min:1',
                'structure.*.unit' => 'nullable|string|max:50',
                'structure.*.options' => 'nullable|array',
                'structure.*.options.*' => 'nullable|string|max:255',
                'structure.*.description' => 'nullable|string|max:1000',
                'structure.*.children' => 'nullable|array',
            ]);
    
            $structure = $data['structure'];
    
            foreach ($structure as &$node) {
                try {
                    $this->prepareNode($node);
                } catch (\Exception $e) {
                    return response()->json([
                        'status' => 'error',
                        'message' => $e->getMessage()
                    ]);
                }
            }
            Log::debug('🧱 Structure préparée pour enregistrement', json_decode(json_encode($structure), true));


            // Suppression des caractéristiques existantes (hors celles avec valeurs)
            $caracteristiquesExistantes = Caracteristique::where('code_famille', $famille->code_Ssys)->get();
            $nonSupprimees = [];
    
            foreach ($caracteristiquesExistantes as $carac) {
                if ($carac->valeursCaracteristiques()->exists()) {
                    $nonSupprimees[] = $carac->libelleCaracteristique;
                    continue;
                }
    
                $carac->valeursPossibles()->delete();
                $carac->familles()->detach();
                $carac->delete();
            }
    
            // Traitement
            $this->processStructure($structure, null, [
                'id' => $famille->idFamille,
                'code' => $famille->code_Ssys
            ]);
    
            DB::commit();
    
            return response()->json([
                'status' => count($nonSupprimees) > 0 ? 'partial' : 'success',
                'message' => count($nonSupprimees) > 0
                    ? 'Structure partiellement enregistrée (caractéristiques avec valeurs conservées).'
                    : 'Structure enregistrée avec succès.',
                'non_supprimables' => $nonSupprimees
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("❌ Erreur lors de saveStructure", [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
    
            return response()->json([
                'status' => 'error',
                'message' => "Erreur lors de l'enregistrement : " . $e->getMessage()
            ], 500);
        }
    }
    
    protected function isNombreType($typeId)
    {
        $type = TypeCaracteristique::find($typeId);
        return $type && strtolower($type->libelleTypeCaracteristique) === 'nombre';
    }
    protected function prepareNode(&$node)
    {
        unset($node['typeLabel']);
        unset($node['parentId']); // Nettoyer le parentId qui pourrait interférer
    
        $node['description'] = isset($node['description']) ? strip_tags($node['description']) : null;
    
        // Normaliser options
        $node['options'] = isset($node['options']) && is_array($node['options']) 
            ? $node['options'] 
            : [];
    
        // Nettoyage des options
        foreach ($node['options'] as &$option) {
            $option = preg_replace('/[<>{};()=]|script|php/i', '', $option);
        }
    
        // Préserver la structure des enfants
        if (isset($node['children'])) {
            if (!is_array($node['children'])) {
                $node['children'] = [];
            }
            
            // Préparer récursivement chaque enfant
            foreach ($node['children'] as &$child) {
                $this->prepareNode($child);
            }
        } else {
            $node['children'] = [];
        }
    }
    
    protected function processStructure(array $nodes, $parentId, array $familleData)
    {
        Log::debug('Début processStructure', [
            'nodes_count' => count($nodes),
            'parent_id' => $parentId,
            'first_node' => $nodes[0] ?? null
        ]);
        foreach ($nodes as $index => $node) {
            Log::info("🧩 Traitement d'une caractéristique", [
                'nom' => $node['name'] ?? 'inconnu',
                'parent_id' => $parentId,
                'ordre' => $node['order'] ?? 'non défini',
                'niveau' => $parentId ? 'enfant' : 'racine',
                'index' => $index,
                'has_children' => !empty($node['children']),
                'children_count' => count($node['children'] ?? []),
                'children_data' => $node['children'] ?? [] 
            ]);
    
            // Sécurité : ignorer les IDs non valides
            $id = $node['id'] ?? null;
            $id = is_numeric($id) ? $id : null;
    
            try {
                $caracteristique = Caracteristique::updateOrCreate(
                    ['idCaracteristique' => $id],
                    [
                        'libelleCaracteristique' => $node['name'],
                        'idTypeCaracteristique' => $this->getTypeId($node['type']),
                        'parent_id' => $parentId,
                        'ordre' => $node['order'] ?? 1,
                        'code_famille' => $familleData['code'],
                        'description' => $node['description'] ?? null,
                    ]
                );
    
                Log::info("✅ Caractéristique enregistrée", [
                    'id' => $caracteristique->idCaracteristique,
                    'libelle' => $caracteristique->libelleCaracteristique,
                    'parent_id' => $caracteristique->parent_id
                ]);
    
                // Unité
                if ($this->isNombreType($node['type']) && isset($node['unit'])) {
                    Log::info("Unités", ['unit' => $node['unit']]);

                    $unite = Unite::find($node['unit']);
                    if ($unite) {
                        $caracteristique->idUnite = $unite->idUnite;
                        $caracteristique->save();

                        Log::info("🔢 Unité associée", ['symbole' => $unite->symbole]);
                    } else {
                        Log::warning("⚠️ Unité non trouvée", ['id' => $node['unit']]);
                    }

                }
    
                // Options liste
                if ($this->isListeType($node['type'])) {
                    $this->syncValeursPossibles($caracteristique, $node['options']);
                    Log::info("📋 Options enregistrées", ['nb_options' => count($node['options'])]);
                }
    
                // Liaison famille
                $caracteristique->familles()->syncWithoutDetaching($familleData['id']);
    
                // 🔁 Récursion enfants - CORRECTION ICI
                if (!empty($node['children']) && is_array($node['children'])) {
                    Log::info("🔁 Traitement des enfants", [
                        'parent' => $node['name'],
                        'parent_id' => $caracteristique->idCaracteristique,
                        'children_count' => count($node['children'])
                    ]);
                    
                    // Log détaillé des enfants
                    foreach ($node['children'] as $childIndex => $child) {
                        Log::debug("👶 Enfant à traiter", [
                            'index' => $childIndex,
                            'nom' => $child['name'] ?? 'inconnu',
                            'parent_attendu' => $caracteristique->idCaracteristique
                        ]);
                    }
                    
                    $this->processStructure($node['children'], $caracteristique->idCaracteristique, $familleData);
                } else {
                    Log::info("🚫 Pas d'enfants à enregistrer", [
                        'nom' => $node['name'],
                        'children_empty' => empty($node['children']),
                        'children_is_array' => is_array($node['children'] ?? null)
                    ]);
                }
    
            } catch (\Throwable $e) {
                Log::error("❌ Erreur lors de l'enregistrement d'une caractéristique", [
                    'message' => $e->getMessage(),
                    'node' => $node,
                    'parent_id' => $parentId,
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e; // Propager l'erreur pour annuler la transaction
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
                    'type' => $carac->idTypeCaracteristique,
                    'typeLabel' => strtolower($carac->type?->libelleTypeCaracteristique ?? 'inconnu'),
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

