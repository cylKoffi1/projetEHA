<?php

namespace App\Http\Controllers;

use App\Models\Acteur;
use App\Models\DecoupageAdminPays;
use App\Models\Domaine;
use App\Models\Ecran;
use App\Models\Financer;
use App\Models\GroupeProjetPaysUser;
use App\Models\LocalitesPays;
use App\Models\Pays;
use App\Models\Projet;
use App\Models\ProjetStatut;
use App\Models\SousDomaine;
use App\Models\TypeStatut;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SigAdminInfrastructureController extends Controller
{
    /* ------------------------------
     * PAGE
     * ------------------------------ */
    public function pageInfras(Request $request)
    {
        $ecran = Ecran::find($request->input('ecran_id'));
        $user  = Auth::user();

        $alpha3 = session('pays_selectionne');
        if (!$alpha3) return redirect()->route('projets.index')->with('error', 'Vous n\'avez pas de pays.');

        $pays = Pays::where('alpha3', $alpha3)->first();
        if (!$pays) return redirect()->route('projets.index')->with('error', 'Le pays sélectionné est introuvable.');

        $codeAlpha3 = $pays->alpha3;
        $codeZoom   = Pays::select('minZoom', 'maxZoom')->where('alpha3', $codeAlpha3)->first();

        $groupeSelectionne = session('projet_selectionne');
        if (!$groupeSelectionne) return redirect()->route('projets.index')->with('error', 'Vous n\'avez pas de groupe projet.');

        $gp = GroupeProjetPaysUser::where('groupe_projet_id', $groupeSelectionne)->with('groupeProjet')->first();
        if (!$gp) return redirect()->route('projets.index')->with('error', 'Le groupe projet n\'existe pas.');

        $codeGroupeProjet = $gp->groupe_projet_id;


        $domainesAssocie = Domaine::where('groupe_projet_code', $codeGroupeProjet)
            ->select('code','libelle')->orderBy('libelle')->get();

        $niveau = DecoupageAdminPays::where('id_pays', $pays->id)
            ->join('decoupage_administratif', 'decoupage_admin_pays.code_decoupage', '=', 'decoupage_administratif.code_decoupage')
            ->select('decoupage_admin_pays.code_decoupage','decoupage_admin_pays.num_niveau_decoupage','decoupage_administratif.libelle_decoupage')
            ->orderBy('num_niveau_decoupage')->get();

        $Bailleurs    = Acteur::whereHas('bailleurs')->get();
        $TypesStatuts = TypeStatut::all();

        return view('GestionSig.sigInfra', compact(
            'ecran', 'codeZoom', 'niveau', 'codeAlpha3', 'codeGroupeProjet',
            'domainesAssocie', 'Bailleurs', 'TypesStatuts'
        ));
    }

    /* ------------------------------
     * LÉGENDE dynamique
     * - typeFin 1 = métrique "count" (nb d’infras bénéficiaires)
     * - typeFin 2 = "cost"
     * ------------------------------ */
    public function legendByGroupInfras($groupe)
    {
        $typeFin = (int) request('typeFin', 1);
        $fallback = [
            'label'  => $typeFin === 2 ? 'Montant réparti des projets' : 'Nombre d’infrastructures bénéficiaires',
            'seuils' => $typeFin === 2
                ? [
                    ['borneInf'=>0,'borneSup'=>0,'couleur'=>'#f1f5f9'],
                    ['borneInf'=>1_000_000,'borneSup'=>500_000_000,'couleur'=>'#fde68a'],
                    ['borneInf'=>500_000_000,'borneSup'=>2_000_000_000,'couleur'=>'#fbbf24'],
                    ['borneInf'=>2_000_000_000,'borneSup'=>5_000_000_000,'couleur'=>'#f59e0b'],
                    ['borneInf'=>5_000_000_000,'borneSup'=>null,'couleur'=>'#d97706'],
                ]
                : [
                    ['borneInf'=>0,'borneSup'=>0,'couleur'=>'#f1f5f9'],
                    ['borneInf'=>1,'borneSup'=>2,'couleur'=>'#c7d2fe'],
                    ['borneInf'=>3,'borneSup'=>5,'couleur'=>'#93c5fd'],
                    ['borneInf'=>6,'borneSup'=>10,'couleur'=>'#60a5fa'],
                    ['borneInf'=>11,'borneSup'=>null,'couleur'=>'#2563eb'],
                ],
        ];

        $lg = DB::table('legende_carte as l')
            ->where('l.groupe_projet', $typeFin === 2 ? 'COMMUN' : session('projet_selectionne'))
            ->where('l.typeFin', $typeFin)
            ->leftJoin('seuil_legende as s','s.idLegende','=','l.id')
            ->select('l.label','s.borneInf','s.borneSup','s.couleur')
            ->orderBy('s.borneInf')
            ->get();

        if ($lg->isEmpty()) return response()->json($fallback);

        $label = $lg->first()->label ?: $fallback['label'];
        $seuils = $lg->map(fn($r)=>['borneInf'=>$r->borneInf,'borneSup'=>$r->borneSup,'couleur'=>$r->couleur])->values();

        return response()->json(['label'=>$label,'seuils'=>$seuils]);
    }

    /* ------------------------------
     * AGRÉGAT (nb d'infras bénéficiaires) pour la carte
     * ------------------------------ */
    public function aggregateProjectsInfras(Request $request)
    {
        $alpha3 = session('pays_selectionne');
        $group  = session('projet_selectionne');
        if (!$alpha3 || !$group) return response()->json(['error'=>'Contexte pays/groupe manquant'], 400);

        $rows = DB::table('jouir as j')
            ->join('projets as p', 'p.code_projet', '=', 'j.code_projet')
            ->join('infrastructures as i', 'i.code', '=', 'j.code_Infrastructure')
            ->where('i.code_pays', $alpha3)
            ->select([
                'p.code_projet','p.cout_projet','p.code_sous_domaine',
                DB::raw("SUBSTRING(p.code_projet,4,3) as gcode"),
                DB::raw("SUBSTRING(p.code_projet,7,1) as type_fin"),
                'i.code as infra_code','i.code_localite'
            ])->get();

        if ($rows->isEmpty()) return response()->json([]);

        $infraByProject=[]; foreach($rows as $r){ $infraByProject[$r->code_projet]=($infraByProject[$r->code_projet]??0)+1; }

        $agg=[]; $seen=[]; $seenDom=[];
        foreach($rows as $r){
            if ($r->gcode !== $group && $group !== 'BTP') continue;

            $alloc = (!empty($r->cout_projet) && $infraByProject[$r->code_projet]>0) ? (float)$r->cout_projet/$infraByProject[$r->code_projet] : 0;
            $isPublic = ((string)$r->type_fin) === '1';
            $dom2 = substr($r->code_sous_domaine ?? '00', 0, 2);

            $niv1 = substr($r->code_localite ?? '', 0, 2);
            $niv2 = substr($r->code_localite ?? '', 0, 4);
            $niv3 = substr($r->code_localite ?? '', 0, 6);

            foreach([1=>$niv1,2=>$niv2,3=>$niv3] as $level=>$code){
                if (!$code) continue;
                if (!isset($agg[$code])) $agg[$code]=['name'=>null,'level'=>$level,'code'=>$code,'count'=>0,'public'=>0,'private'=>0,'cost'=>0.0,'byDomain'=>[]];

                $k=$code.'|'.$r->infra_code;
                if (!isset($seen[$k])){ $agg[$code]['count']++; $isPublic?$agg[$code]['public']++:$agg[$code]['private']++; $seen[$k]=1; }
                $agg[$code]['cost'] += $alloc;

                if (!isset($agg[$code]['byDomain'][$dom2])) $agg[$code]['byDomain'][$dom2]=['count'=>0,'cost'=>0.0,'public'=>0,'private'=>0];
                $kd=$code.'|'.$dom2.'|'.$r->infra_code;
                if (!isset($seenDom[$kd])){ $agg[$code]['byDomain'][$dom2]['count']++; $isPublic?$agg[$code]['byDomain'][$dom2]['public']++:$agg[$code]['byDomain'][$dom2]['private']++; $seenDom[$kd]=1; }
                $agg[$code]['byDomain'][$dom2]['cost'] += $alloc;
            }
        }

        // libellés
        $pays = Pays::where('alpha3',$alpha3)->first();
        $locs = LocalitesPays::where('id_pays',$pays->id)->get(['code_rattachement','libelle']);
        $names=[]; foreach($locs as $l){ $names[$l->code_rattachement]=$l->libelle; }
        foreach($agg as $k=>$v){ $agg[$k]['name']=$names[$v['code']] ?? $v['code']; }

        return response()->json(array_values($agg));
    }

    /* ------------------------------
     * API pour récupérer les coordonnées (centroïde) d'une localité
     * à partir de son code en utilisant les GeoJSON
     * ------------------------------ */
    public function getLocaliteCoordinates(Request $request)
    {
        $alpha3 = session('pays_selectionne');
        $codeLocalite = $request->input('code'); // ex: "01", "0101", "010101"
        
        if (!$alpha3 || !$codeLocalite) {
            return response()->json(['error' => 'Paramètres manquants'], 400);
        }
        
        // Déterminer le niveau selon la longueur du code
        $level = strlen($codeLocalite) / 2; // 2 chars = niveau 1, 4 = niveau 2, 6 = niveau 3
        
        if ($level < 1 || $level > 3) {
            return response()->json(['error' => 'Code de localité invalide'], 400);
        }
        
        $level = (int)$level;
        
        // Charger le GeoJSON correspondant
        $geoJsonPath = storage_path("geojson/gadm41_{$alpha3}_{$level}.json");
        
        if (!file_exists($geoJsonPath)) {
            Log::warning("[LocaliteCoords] GeoJSON non trouvé: {$geoJsonPath}");
            return response()->json(['error' => 'GeoJSON non trouvé'], 404);
        }
        
        $geoJson = json_decode(file_get_contents($geoJsonPath), true);
        
        if (!$geoJson || !isset($geoJson['features'])) {
            return response()->json(['error' => 'GeoJSON invalide'], 500);
        }
        
        // Trouver la feature correspondante
        // Les codes peuvent être dans différentes propriétés selon le niveau
        $feature = null;
        foreach ($geoJson['features'] as $feat) {
            $props = $feat['properties'] ?? [];
            
            // Essayer différents formats de codes
            $found = false;
            if ($level === 1) {
                // Niveau 1 : chercher dans GID_1 ou NAME_1
                $gid1 = substr($props['GID_1'] ?? '', -2);
                $name1 = $props['NAME_1'] ?? '';
                if ($gid1 === $codeLocalite || $name1 === $codeLocalite) {
                    $found = true;
                }
            } elseif ($level === 2) {
                // Niveau 2 : chercher dans GID_2
                $gid2 = substr($props['GID_2'] ?? '', -4);
                if ($gid2 === $codeLocalite) {
                    $found = true;
                }
            } elseif ($level === 3) {
                // Niveau 3 : chercher dans GID_3
                $gid3 = substr($props['GID_3'] ?? '', -6);
                if ($gid3 === $codeLocalite) {
                    $found = true;
                }
            }
            
            if ($found) {
                $feature = $feat;
                break;
            }
        }
        
        if (!$feature) {
            // Essayer de trouver par le libellé de la localité
            $pays = Pays::where('alpha3', $alpha3)->first();
            if ($pays) {
                $localite = LocalitesPays::where('code_rattachement', $codeLocalite)
                    ->where('id_pays', $pays->id)
                    ->first();
            
                if ($localite) {
                    $libelle = $localite->libelle;
                    foreach ($geoJson['features'] as $feat) {
                        $props = $feat['properties'] ?? [];
                        $nameKey = "NAME_{$level}";
                        if (isset($props[$nameKey]) && $props[$nameKey] === $libelle) {
                            $feature = $feat;
                            break;
                        }
                    }
                }
            }
        }
        
        if (!$feature) {
            return response()->json(['error' => 'Localité non trouvée dans le GeoJSON'], 404);
        }
        
        // Calculer le centroïde de la feature
        $geometry = $feature['geometry'] ?? null;
        if (!$geometry || $geometry['type'] !== 'Polygon' && $geometry['type'] !== 'MultiPolygon') {
            return response()->json(['error' => 'Géométrie invalide'], 500);
        }
        
        // Calcul simple du centroïde (moyenne des coordonnées)
        $coords = [];
        if ($geometry['type'] === 'Polygon') {
            foreach ($geometry['coordinates'][0] as $coord) {
                $coords[] = [$coord[1], $coord[0]]; // lat, lng
            }
        } elseif ($geometry['type'] === 'MultiPolygon') {
            foreach ($geometry['coordinates'] as $polygon) {
                foreach ($polygon[0] as $coord) {
                    $coords[] = [$coord[1], $coord[0]]; // lat, lng
                }
            }
        }
        
        if (empty($coords)) {
            return response()->json(['error' => 'Impossible de calculer le centroïde'], 500);
        }
        
        // Calculer la moyenne
        $sumLat = 0;
        $sumLng = 0;
        $count = count($coords);
        foreach ($coords as $coord) {
            $sumLat += $coord[0];
            $sumLng += $coord[1];
        }
        
        $centroid = [
            'lat' => $sumLat / $count,
            'lng' => $sumLng / $count
        ];
        
        return response()->json([
            'code' => $codeLocalite,
            'level' => $level,
            'centroid' => $centroid
        ]);
    }
    
    /**
     * Version interne de getLocaliteCoordinates (sans Request)
     * pour être utilisée dans markersInfras
     */
    private function getLocaliteCoordinatesInternal($alpha3, $codeLocalite, $level = null)
    {
        if (!$alpha3 || !$codeLocalite) {
            return null;
        }
        
        // Déterminer le niveau si non fourni
        if ($level === null) {
            $level = strlen($codeLocalite) / 2;
            if ($level < 1 || $level > 3) {
                return null;
            }
            $level = (int)$level;
        }
        
        // Charger le GeoJSON correspondant
        $geoJsonPath = storage_path("geojson/gadm41_{$alpha3}_{$level}.json");
        
        if (!file_exists($geoJsonPath)) {
            return null;
        }
        
        $geoJson = json_decode(file_get_contents($geoJsonPath), true);
        
        if (!$geoJson || !isset($geoJson['features'])) {
            return null;
        }
        
        // Trouver la feature correspondante
        $feature = null;
        foreach ($geoJson['features'] as $feat) {
            $props = $feat['properties'] ?? [];
            
            $found = false;
            if ($level === 1) {
                $gid1 = substr($props['GID_1'] ?? '', -2);
                $name1 = $props['NAME_1'] ?? '';
                if ($gid1 === $codeLocalite || $name1 === $codeLocalite) {
                    $found = true;
                }
            } elseif ($level === 2) {
                $gid2 = substr($props['GID_2'] ?? '', -4);
                if ($gid2 === $codeLocalite) {
                    $found = true;
                }
            } elseif ($level === 3) {
                $gid3 = substr($props['GID_3'] ?? '', -6);
                if ($gid3 === $codeLocalite) {
                    $found = true;
                }
            }
            
            if ($found) {
                $feature = $feat;
                break;
            }
        }
        
        if (!$feature) {
            // Essayer par libellé
            $pays = Pays::where('alpha3', $alpha3)->first();
            if ($pays) {
                $localite = LocalitesPays::where('code_rattachement', $codeLocalite)
                    ->where('id_pays', $pays->id)
                    ->first();
                
                if ($localite) {
                    $libelle = $localite->libelle;
                    foreach ($geoJson['features'] as $feat) {
                        $props = $feat['properties'] ?? [];
                        $nameKey = "NAME_{$level}";
                        if (isset($props[$nameKey]) && $props[$nameKey] === $libelle) {
                            $feature = $feat;
                            break;
                        }
                    }
                }
            }
        }
        
        if (!$feature) {
            return null;
        }
        
        // Calculer le centroïde
        $geometry = $feature['geometry'] ?? null;
        if (!$geometry || ($geometry['type'] !== 'Polygon' && $geometry['type'] !== 'MultiPolygon')) {
            return null;
        }
        
        $coords = [];
        if ($geometry['type'] === 'Polygon') {
            foreach ($geometry['coordinates'][0] as $coord) {
                $coords[] = [$coord[1], $coord[0]];
            }
        } elseif ($geometry['type'] === 'MultiPolygon') {
            foreach ($geometry['coordinates'] as $polygon) {
                foreach ($polygon[0] as $coord) {
                    $coords[] = [$coord[1], $coord[0]];
                }
            }
        }
        
        if (empty($coords)) {
            return null;
        }
        
        $sumLat = 0;
        $sumLng = 0;
        $count = count($coords);
        foreach ($coords as $coord) {
            $sumLat += $coord[0];
            $sumLng += $coord[1];
        }
        
        return [
            'code' => $codeLocalite,
            'level' => $level,
            'centroid' => [
                'lat' => $sumLat / $count,
                'lng' => $sumLng / $count
            ]
        ];
    }

    /* ------------------------------
     * MARKERS agrégés (icône/couleur) par niveau
     * - level: 1,2,3
     * - code: préfixe de localité (ex 01, 0101, 010101)
     * - status: all | done | todo  (basé sur i.IsOver)
     * - logique BTP: niveau 1 = domaines, 2 = sous-domaines, 3 = sous-domaines
     * - logique autre groupe: 1 = groupes, 2 = domaines, 3 = sous-domaines
     * ------------------------------ */
    public function markersInfras(Request $request)
    {
        $alpha3  = session('pays_selectionne');
        $group   = session('projet_selectionne'); // peut être 'BTP'
        
        Log::info('[InfraMarkers API] Requête reçue', [
            'alpha3' => $alpha3,
            'group' => $group,
            'level' => $request->input('level'),
            'prefix' => $request->input('code'),
            'status' => $request->input('status', 'all')
        ]);
        
        if (!$alpha3) {
            Log::warning('[InfraMarkers API] Pas de pays sélectionné');
            return response()->json(['markers'=>[]]);
        }

        $level  = (int) $request->input('level', 1);
        if ($level < 1) $level = 1;
        $prefix = (string) $request->input('code', '');
        $status = $request->input('status', 'all'); // all|done|todo
        // niveau > 3: infrastructures individuelles avec libellé + nb projets
        if ($level > 3) {
            $q = DB::table('infrastructures as i')
                ->leftJoin('jouir as j', 'j.code_Infrastructure', '=', 'i.code')
                ->leftJoin('projets as p', 'p.code_projet', '=', 'j.code_projet')
                ->where('i.code_pays', $alpha3)
                ->select([
                    'i.code as infra_code',
                    'i.libelle as infra_lib',
                    'i.latitude','i.longitude',
                    'i.code_localite',
                    'i.code_Ssys as famille_code',
                    DB::raw('COUNT(DISTINCT p.code_projet) as nb_projets')
                ])
                ->groupBy('i.code','i.libelle','i.latitude','i.longitude','i.code_localite','i.code_Ssys');

            if ($status === 'done') $q->where('i.IsOver', 1);
            if ($status === 'todo') $q->where(function($s){ $s->whereNull('i.IsOver')->orWhere('i.IsOver', 0); });

            $rows = $q->get();
            
            // Récupérer les coordonnées pour les infrastructures sans lat/lng
            $pays = Pays::where('alpha3', $alpha3)->first();
            if (!$pays) {
                Log::warning("[InfraMarkers] Pays non trouvé pour alpha3: {$alpha3}");
                return response()->json(['markers'=>[]]);
            }
            
            $localitesCache = [];
            
            foreach ($rows as $r) {
                if (empty($r->latitude) || empty($r->longitude)) {
                    $codeLocalite = $r->code_localite;
                    
                    if ($codeLocalite) {
                        // Essayer différents niveaux (3, 2, 1)
                        $found = false;
                        $prefixLocalite = null;
                        for ($lvl = 3; $lvl >= 1; $lvl--) {
                            $len = $lvl * 2;
                            if (strlen($codeLocalite) >= $len) {
                                $prefixLocalite = substr($codeLocalite, 0, $len);
                                
                                if (!isset($localitesCache[$prefixLocalite])) {
                                    // Appeler l'API interne pour obtenir les coordonnées
                                    try {
                                        $coordsResponse = $this->getLocaliteCoordinatesInternal($alpha3, $prefixLocalite, $lvl);
                                        if ($coordsResponse && isset($coordsResponse['centroid'])) {
                                            $localitesCache[$prefixLocalite] = $coordsResponse['centroid'];
                                            $found = true;
                                            break;
                                        }
                                    } catch (\Exception $e) {
                                        Log::warning("[InfraMarkers] Erreur récupération coordonnées pour {$prefixLocalite}: " . $e->getMessage());
                                    }
                                }
                                } else {
                                    $found = true;
                                    break;
                                }
                            }
                        }
                        
                        if ($found && $prefixLocalite && isset($localitesCache[$prefixLocalite])) {
                            $r->latitude = $localitesCache[$prefixLocalite]['lat'];
                            $r->longitude = $localitesCache[$prefixLocalite]['lng'];
                        }
                    }
                }
            }
            
            // Filtrer les lignes qui ont maintenant des coordonnées
            $rows = $rows->filter(function($r) {
                return !empty($r->latitude) && !empty($r->longitude);
            })->values();
            
            // Système de décalage pour éviter les chevauchements
            $markers = [];
            $usedPositions = [];
            $offsetDistance = 0.005; // Distance plus petite pour les infrastructures individuelles
            
            foreach ($rows as $r) {
                $baseLat = (float) $r->latitude;
                $baseLng = (float) $r->longitude;
                
                $finalLat = $baseLat;
                $finalLng = $baseLng;
                $maxAttempts = 10;
                $attempt = 0;
                
                while ($attempt < $maxAttempts) {
                    $tooClose = false;
                    foreach ($usedPositions as $used) {
                        $distance = sqrt(
                            pow($finalLat - $used['lat'], 2) + 
                            pow($finalLng - $used['lng'], 2)
                        );
                        
                        if ($distance < $offsetDistance) {
                            $tooClose = true;
                            break;
                        }
                    }
                    
                    if (!$tooClose) {
                        break;
                    }
                    
                    $attempt++;
                    $angle = ($attempt * 60) * (M_PI / 180);
                    $radius = $offsetDistance * $attempt;
                    $finalLat = $baseLat + ($radius * cos($angle));
                    $finalLng = $baseLng + ($radius * sin($angle));
                }
                
                $usedPositions[] = [
                    'lat' => $finalLat,
                    'lng' => $finalLng
                ];
                
                $markers[] = [
                    'lat'   => $finalLat,
                    'lng'   => $finalLng,
                    'count' => (int) $r->nb_projets,
                    'class' => 'infra',
                    'code'  => $r->infra_code,
                    'label' => $r->infra_lib,
                    'famille_code' => $r->famille_code,
                ];
            }
            
            Log::info('[InfraMarkers API] Niveau > 3 - ' . count($markers) . ' marqueur(s) retourné(s)');
            return response()->json(['markers'=>$markers]);
        }
        
        $level = max(1, min(3, $level));

        // jointure infra ↔ jouir ↔ projets (pour récupérer sous-domaine & groupe projet)
        $q = DB::table('infrastructures as i')
            ->join('jouir as j', 'j.code_Infrastructure', '=', 'i.code')
            ->join('projets as p', 'p.code_projet', '=', 'j.code_projet')
            ->where('i.code_pays', $alpha3)
            ->when($group, function($qq) use ($group) {
                $qq->where('i.code_groupe_projet', $group);
            })
            ->when($prefix !== '', function($qq) use ($level,$prefix){
                $len = $level * 2;
                $qq->where(DB::raw("LEFT(i.code_localite, {$len})"), $prefix);
            });

        if ($status === 'done') $q->where('i.IsOver', 1);
        if ($status === 'todo') $q->where(function($s){ $s->whereNull('i.IsOver')->orWhere('i.IsOver', 0); });

        // champs utiles - inclure aussi les infrastructures sans coordonnées
        $rows = $q->select([
                'i.latitude','i.longitude','i.code_localite','i.IsOver',
                DB::raw("SUBSTRING(p.code_projet,4,3) as gcode"),
                DB::raw("LEFT(p.code_sous_domaine,2) as domain2"),
                'p.code_sous_domaine as sdomaine'
            ])->get();

        if ($rows->isEmpty()) return response()->json(['markers'=>[]]);
        
        // Récupérer les coordonnées pour les infrastructures sans lat/lng
        $pays = Pays::where('alpha3', $alpha3)->first();
        if (!$pays) {
            Log::warning("[InfraMarkers] Pays non trouvé pour alpha3: {$alpha3}");
            return response()->json(['markers'=>[]]);
        }
        
        $localitesCache = []; // Cache pour éviter les appels multiples
        
        foreach ($rows as $r) {
            if (empty($r->latitude) || empty($r->longitude)) {
                $codeLocalite = $r->code_localite;
                
                if (!empty($codeLocalite)) {
                    // Extraire le préfixe selon le niveau
                    $len = $level * 2;
                    if (strlen($codeLocalite) >= $len) {
                        $prefixLocalite = substr($codeLocalite, 0, $len);
                        
                        if (!isset($localitesCache[$prefixLocalite])) {
                            // Appeler l'API interne pour obtenir les coordonnées
                            try {
                                $coordsResponse = $this->getLocaliteCoordinatesInternal($alpha3, $prefixLocalite, $level);
                                if ($coordsResponse && isset($coordsResponse['centroid'])) {
                                    $localitesCache[$prefixLocalite] = $coordsResponse['centroid'];
                                }
                            } catch (\Exception $e) {
                                Log::warning("[InfraMarkers] Erreur récupération coordonnées pour {$prefixLocalite}: " . $e->getMessage());
                            }
                        }
                        
                        if (isset($localitesCache[$prefixLocalite])) {
                            $r->latitude = $localitesCache[$prefixLocalite]['lat'];
                            $r->longitude = $localitesCache[$prefixLocalite]['lng'];
                        }
                    }
                }
            }
        
        // Filtrer les lignes qui ont maintenant des coordonnées
        $rows = $rows->filter(function($r) {
            return !empty($r->latitude) && !empty($r->longitude);
        })->values();
        
        if ($rows->isEmpty()) return response()->json(['markers'=>[]]);

        // agrégation : on calcule un centroïde SIMPLE (moyenne lat/lng) par groupe projet
        // Pour admin/autresRequetes : les marqueurs représentent les groupes projets
        // Niveau 1-3 : groupes projets (BAT, ENE, EHA, TRP, TIC, AXU, BTP)
        // IMPORTANT : On utilise directement code_groupe_projet depuis l'infrastructure
        
        $buckets = []; // key => ['sumLat','sumLng','n','count','label','class','code']
        foreach ($rows as $r) {
            // Utiliser le groupe projet directement depuis l'infrastructure
            $class = 'group';
            $code = $r->gcode; // code_groupe_projet de l'infrastructure
            $label = $code;
            
            // Si le code est vide, ignorer cette infrastructure
            if (empty($code)) {
                continue;
            }
            
            // Libellés des groupes projets (fallback si pas dans la base)
            $groupLabels = [
                'BAT' => 'Bâtiment',
                'ENE' => 'Énergie',
                'EHA' => 'Eau, Hygiène et Assainissement',
                'TRP' => 'Transport',
                'TIC' => 'Informatique et télécommunication',
                'AXU' => 'Aménagement des axes urbains',
                'BTP' => 'Bâtiments Travaux Publics'
            ];
            
            if (isset($groupLabels[$code])) {
                $label = $groupLabels[$code];
            }

            $k = $class.'|'.$code;
            if (!isset($buckets[$k])) {
                $buckets[$k] = [
                    'sumLat' => 0,
                    'sumLng' => 0,
                    'n' => 0,
                    'count' => 0,
                    'class' => $class,
                    'code' => $code,
                    'label' => $label
                ];
            }
            $buckets[$k]['sumLat'] += (float)$r->latitude;
            $buckets[$k]['sumLng'] += (float)$r->longitude;
            $buckets[$k]['n']++;
            $buckets[$k]['count']++;
        }

        // Système de décalage pour éviter les chevauchements
        $markers = [];
        $usedPositions = []; // Pour tracker les positions utilisées
        $offsetDistance = 0.01; // Distance de décalage en degrés (environ 1km)
        
        foreach ($buckets as $b) {
            $baseLat = $b['sumLat'] / max(1, $b['n']);
            $baseLng = $b['sumLng'] / max(1, $b['n']);
            
            // Vérifier si la position est déjà utilisée
            $finalLat = $baseLat;
            $finalLng = $baseLng;
            $offset = 0;
            $maxAttempts = 10;
            $attempt = 0;
            
            while ($attempt < $maxAttempts) {
                $tooClose = false;
                foreach ($usedPositions as $used) {
                    $distance = sqrt(
                        pow($finalLat - $used['lat'], 2) + 
                        pow($finalLng - $used['lng'], 2)
                    );
                    
                    // Si la distance est trop petite, décaler
                    if ($distance < $offsetDistance) {
                        $tooClose = true;
                        break;
                    }
                }
                
                if (!$tooClose) {
                    break;
                }
                
                // Décaler en spirale
                $attempt++;
                $angle = ($attempt * 60) * (M_PI / 180); // 60 degrés par tentative
                $radius = $offsetDistance * $attempt;
                $finalLat = $baseLat + ($radius * cos($angle));
                $finalLng = $baseLng + ($radius * sin($angle));
            }
            
            // Enregistrer la position utilisée
            $usedPositions[] = [
                'lat' => $finalLat,
                'lng' => $finalLng
            ];
            
            $markers[] = [
                'lat'   => $finalLat,
                'lng'   => $finalLng,
                'count' => $b['count'],
                'class' => $b['class'],     // group|domain|sub
                'code'  => $b['code'],
                'label' => $b['label'],
            ];
        }

        Log::info('[InfraMarkers API] Niveau ' . $level . ' - ' . count($markers) . ' marqueur(s) retourné(s)', [
            'group' => $group,
            'exemples' => array_slice($markers, 0, 3)
        ]);
        
        return response()->json(['markers'=>$markers]);
    }

    /* ------------------------------
     * Détails projets (tiroir latéral)
     * ------------------------------ */
    public function projectDetailsInfras(Request $request)
    {
        $country = session('pays_selectionne');
        $group   = session('projet_selectionne');
        if (!$country || !$group) return response()->json(['error'=>'Contexte manquant'],400);

        $locPrefix     = $request->input('code');
        $financeFilter = $request->input('filter','cumul'); // cumul|public|private
        $domainPrefix  = $request->input('domain');
        $limit         = (int) $request->input('limit', 1000);
        if (!$locPrefix) return response()->json(['error'=>'code requis'],422);

        $codePattern = $country.$group.'%';
        $projects = Projet::where('code_projet','like',$codePattern)
            ->when($domainPrefix, fn($q)=>$q->where('code_sous_domaine','like',$domainPrefix.'%'))
            ->limit($limit)->get();

        $res=[];
        foreach($projects as $p){
            $cp = $p->code_projet;
            $isPublic = substr($cp,6,1)==='1';
            if ($financeFilter==='public' && !$isPublic) continue;
            if ($financeFilter==='private' &&  $isPublic) continue;

            $loc = explode('_',$cp)[1] ?? '0000';
            if (strpos($loc, $locPrefix)!==0) continue;

            $res[]=[
                'code_projet'=>$cp,
                'libelle_projet'=>$p->libelle_projet,
                'cout_projet'=>$p->cout_projet ?? 0,
                'is_public'=>$isPublic,
                'code_sous_domaine'=>$p->code_sous_domaine,
                'code_localisation'=>$loc,
                'date_demarrage_prevue'=>$p->date_demarrage_prevue,
                'date_fin_prevue'=>$p->date_fin_prevue,
                'code_devise'=>$p->code_devise
            ];
        }

        return response()->json(['count'=>count($res),'projects'=>$res]);
    }

    /* ------------------------------
     * Filtres (bailleurs/statuts) — réutilisable
     * ------------------------------ */
    public function filterOptionsAndAggregateInfras(Request $request)
    {
        $country = session('pays_selectionne');
        $group   = session('projet_selectionne');
        if (!$country || !$group) return response()->json(['error'=>'Contexte manquant'], 400);

        $start  = $request->input('start_date');
        $end    = $request->input('end_date');
        $type   = $request->input('date_type');
        $statut = $request->input('status');
        $bail   = $request->input('bailleur');

        $q = Projet::where('code_projet', 'like', $country.$group.'%');

        if ($start || $end) $type = $type ?: 'prévisionnelles';
        if ($type === 'prévisionnelles') {
            if ($start) $q->where('date_demarrage_prevue', '>=', $start);
            if ($end)   $q->where('date_fin_prevue',      '<=', $end);
        } elseif ($type === 'effectives') {
            $q->whereHas('dateEffective', function ($qq) use ($start, $end) {
                if ($start) $qq->where('date_debut_effective', '>=', $start);
                if ($end)   $qq->where('date_fin_effective',   '<=', $end);
            });
        }

        if (!empty($bail))   $q->whereHas('financements', fn($qq) => $qq->where('code_acteur', $bail));
        if (!empty($statut)) $q->whereHas('statuts',      fn($qq) => $qq->where('type_statut', $statut));

        // renvoie les mêmes agrégats que aggregateProjects() pour homogénéité
        return $this->aggregateProjectsInfras($request);
    }

    /* ------------------------------
     * Carte Afrique (simple) — si besoin ailleurs
     * ------------------------------ */
    public function allProjectsInfras()
    {
        $projects = Projet::with('pays')->get()->map(function ($p) {
            return [
                'code_projet' => $p->code_projet,
                'is_public'   => substr($p->code_projet, 6, 1) === '1',
                'country_name'=> optional($p->pays)->libelle ?? substr($p->code_projet, 0, 3)
            ];
        });
        return response()->json($projects);
    }

}
