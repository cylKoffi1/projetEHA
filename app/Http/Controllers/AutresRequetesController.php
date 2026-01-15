<?php

namespace App\Http\Controllers;

use App\Models\Acteur;
use App\Models\DecoupageAdminPays;
use App\Models\Ecran;
use App\Models\GroupeProjet;
use App\Models\GroupeProjetPaysUser;
use App\Models\LocalitesPays;
use App\Models\Pays;
use App\Models\TypeStatut;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutresRequetesController extends Controller
{
    /**
     * Page admin/autresRequetes
     * - Carte similaire à admin/carte
     * - Données: infrastructures bénéficiaires (table jouir)
     * - Bulle: répartition par groupes projet (au lieu des domaines)
     */
    public function page(Request $request)
    {
        $ecran = Ecran::find($request->input('ecran_id'));
        Auth::user(); // garde la même logique que les autres pages (auth obligatoire)

        $alpha3 = session('pays_selectionne');
        Log::info('[autresRequetes.page] ouverture page', [
            'alpha3_session' => $alpha3,
            'groupe_session' => session('projet_selectionne'),
            'ecran_id' => $request->input('ecran_id'),
            'user_id' => Auth::id(),
        ]);
        if (!$alpha3) {
            return redirect()->route('projets.index')->with('error', 'Vous n\'avez pas de pays.');
        }

        $pays = Pays::where('alpha3', $alpha3)->first();
        if (!$pays) {
            return redirect()->route('projets.index')->with('error', 'Le pays sélectionné est introuvable.');
        }

        $codeAlpha3 = $pays->alpha3;
        $codeZoom = Pays::select('minZoom', 'maxZoom')->where('alpha3', $codeAlpha3)->first();

        $groupeProjetSelectionne = session('projet_selectionne');
        if (!$groupeProjetSelectionne) {
            return redirect()->route('projets.index')->with('error', 'Vous n\'avez pas de groupe projet.');
        }

        $gp = GroupeProjetPaysUser::where('groupe_projet_id', $groupeProjetSelectionne)
            ->with('groupeProjet')
            ->first();
        if (!$gp) {
            return redirect()->route('projets.index')->with('error', 'Le groupe projet n\'existe pas.');
        }

        $codeGroupeProjet = $gp->groupe_projet_id;

        // Niveaux administratifs (labels)
        $niveau = DecoupageAdminPays::where('id_pays', $pays->id)
            ->join('decoupage_administratif', 'decoupage_admin_pays.code_decoupage', '=', 'decoupage_administratif.code_decoupage')
            ->select(
                'decoupage_admin_pays.code_decoupage',
                'decoupage_admin_pays.num_niveau_decoupage',
                'decoupage_administratif.libelle_decoupage'
            )
            ->orderBy('num_niveau_decoupage')
            ->get();

        // Groupes projet présents dans les infrastructures bénéficiaires
        $groupCodes = DB::table('jouir as j')
            ->join('projets as p', 'p.code_projet', '=', 'j.code_projet')
            ->join('infrastructures as i', 'i.code', '=', 'j.code_Infrastructure')
            ->where('i.code_pays', $alpha3)
            ->whereNotNull('i.code_groupe_projet')
            ->select(DB::raw('DISTINCT i.code_groupe_projet as code'))
            ->pluck('code')
            ->filter()
            ->unique()
            ->values();

        Log::info('[autresRequetes.page] groupes détectés via jouir', [
            'alpha3' => $alpha3,
            'count' => $groupCodes->count(),
            'sample' => $groupCodes->take(10)->values(),
        ]);

        $groupesProjet = GroupeProjet::query()
            ->when($groupCodes->count() > 0, fn ($q) => $q->whereIn('code', $groupCodes))
            ->select('code', 'libelle', 'icon', 'icon_color')
            ->orderBy('libelle')
            ->get();

        Log::info('[autresRequetes.page] groupesProjet chargés', [
            'count' => $groupesProjet->count(),
            'codes_sample' => $groupesProjet->take(10)->pluck('code')->values(),
        ]);

        $Bailleurs = Acteur::whereHas('bailleurs')->get();
        $TypesStatuts = TypeStatut::all();

        return view('GestionSig.sigInfra', compact(
            'ecran',
            'codeZoom',
            'niveau',
            'codeAlpha3',
            'codeGroupeProjet',
            'groupesProjet',
            'Bailleurs',
            'TypesStatuts'
        ));
    }

    /**
     * Agrégat pour la carte (choroplèthe)
     * - unité: infrastructure bénéficiaire (via jouir)
     * - par niveaux: préfixe de code_localite (2/4/6)
     * - byGroup: répartition par groupe projet (3 lettres)
     */
    public function aggregate(Request $request)
    {
        $alpha3 = session('pays_selectionne');
        if (!$alpha3) {
            Log::info('[autresRequetes.aggregate] contexte manquant (alpha3)', [
                'alpha3_session' => session('pays_selectionne'),
                'groupe_session' => session('projet_selectionne'),
            ]);
            return response()->json(['error' => 'pays_selectionne manquant'], 400);
        }

        $sessionGroup = session('projet_selectionne');
        $requestedGroup = $request->input('groupe');

        // Si groupe session != BTP, on force le filtre sur ce groupe (comportement attendu)
        // Si groupe session == BTP, on autorise l’agrégation sur tous les groupes (ou filtre explicite)
        $groupFilter = null;
        if ($requestedGroup) {
            $groupFilter = $requestedGroup;
        } elseif ($sessionGroup && strtoupper($sessionGroup) !== 'BTP') {
            $groupFilter = $sessionGroup;
        }

        $dateDeb = $request->input('start_date');
        $dateFin = $request->input('end_date');
        $dateType = $request->input('date_type', 'prévisionnelles'); // prévisionnelles|effectives
        $statut = $request->input('status');
        $bailleur = $request->input('bailleur');

        Log::info('[autresRequetes.aggregate] requête reçue', [
            'alpha3' => $alpha3,
            'sessionGroup' => $sessionGroup,
            'requestedGroup' => $requestedGroup,
            'groupFilter' => $groupFilter,
            'dateType' => $dateType,
            'start_date' => $dateDeb,
            'end_date' => $dateFin,
            'status' => $statut,
            'bailleur' => $bailleur,
        ]);

        $rows = DB::table('jouir as j')
            ->join('projets as p', 'p.code_projet', '=', 'j.code_projet')
            ->join('infrastructures as i', 'i.code', '=', 'j.code_Infrastructure')
            ->where('i.code_pays', $alpha3)
            ->when($groupFilter, function ($q) use ($groupFilter) {
                // Données = infrastructures -> filtre sur le groupe de l'infrastructure
                $q->where('i.code_groupe_projet', $groupFilter);
            })
            ->when($dateDeb || $dateFin, function ($q) use ($dateDeb, $dateFin, $dateType) {
                if ($dateType === 'effectives') {
                    $q->whereExists(function ($subq) use ($dateDeb, $dateFin) {
                        $subq->select(DB::raw(1))
                            ->from('date_effective_projet as dep')
                            ->whereColumn('dep.code_projet', 'p.code_projet');
                        if ($dateDeb) $subq->where('dep.date_debut_effective', '>=', $dateDeb);
                        if ($dateFin) $subq->where('dep.date_fin_effective', '<=', $dateFin);
                    });
                } else {
                    if ($dateDeb) $q->whereDate('p.date_demarrage_prevue', '>=', $dateDeb);
                    if ($dateFin) $q->whereDate('p.date_fin_prevue', '<=', $dateFin);
                }
            })
            ->when($statut, function ($q) use ($statut) {
                $q->whereExists(function ($subq) use ($statut) {
                    $subq->select(DB::raw(1))
                        ->from('projet_statut as ps')
                        ->whereColumn('ps.code_projet', 'p.code_projet')
                        ->where('ps.type_statut', $statut)
                        ->whereRaw('ps.date_statut = (SELECT MAX(ps2.date_statut) FROM projet_statut ps2 WHERE ps2.code_projet = p.code_projet)');
                });
            })
            ->when($bailleur, function ($q) use ($bailleur) {
                $q->whereExists(function ($subq) use ($bailleur) {
                    $subq->select(DB::raw(1))
                        ->from('financer as f')
                        ->whereColumn('f.code_projet', 'p.code_projet')
                        ->where('f.code_acteur', $bailleur);
                });
            })
            ->select([
                'p.code_projet',
                'p.cout_projet',
                'p.code_devise',
                DB::raw("i.code_groupe_projet as gcode"),
                DB::raw("SUBSTRING(p.code_projet,7,1) as type_fin"), // 1 public / 2 privé
                'i.code as infra_code',
                'i.code_localite',
                'i.latitude',
                'i.longitude',
            ])
            ->get();

        Log::info('[autresRequetes.aggregate] lignes brutes récupérées', [
            'rows_count' => $rows->count(),
            'sample' => $rows->take(3),
        ]);

        if ($rows->isEmpty()) {
            return response()->json(['projets' => []]);
        }

        // Nb d’infras par projet (pour répartir le coût)
        $infrasParProjet = [];
        foreach ($rows as $r) {
            $infrasParProjet[$r->code_projet] = ($infrasParProjet[$r->code_projet] ?? 0) + 1;
        }

        // Index des coordonnées des localités (fallback quand l’infrastructure n’a pas de lat/lng)
        // NB: dans ta base, localites_pays.id_pays = alpha3 (voir app/Console/Commands/GeocodeLocalites.php)
        $localiteCoords = LocalitesPays::where('id_pays', $alpha3)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('latitude', '!=', 0)
            ->where('longitude', '!=', 0)
            ->get(['code_rattachement', 'latitude', 'longitude'])
            ->keyBy('code_rattachement')
            ->map(fn ($l) => ['lat' => (float) $l->latitude, 'lng' => (float) $l->longitude])
            ->toArray();

        $agg = [];
        $seenInfraPerLocalite = [];
        $seenInfraTypePerLocalite = []; // codeLocalite|infra_code => 'public'|'private' (pour conserver public+private=count)
        $seenInfraPerLocaliteAndGroup = [];

        foreach ($rows as $r) {
            $gcode = (string) ($r->gcode ?? '');
            if ($gcode === '') continue;

            $alloc = 0.0;
            if (!empty($r->cout_projet) && ($infrasParProjet[$r->code_projet] ?? 0) > 0) {
                $alloc = (float) $r->cout_projet / (float) $infrasParProjet[$r->code_projet];
            }

            $isPublic = ((string) $r->type_fin) === '1';
            $loc = (string) ($r->code_localite ?? '');
            if ($loc === '') continue;

            $niv1 = substr($loc, 0, 2);
            $niv2 = substr($loc, 0, 4);
            $niv3 = substr($loc, 0, 6);

            // Coordonnées de l'infrastructure (fallback sur LocalitesPays rattachée à l'infra)
            $coord = null;
            $ilat = $r->latitude !== null ? (float) $r->latitude : null;
            $ilng = $r->longitude !== null ? (float) $r->longitude : null;
            if ($ilat !== null && $ilng !== null && $ilat != 0.0 && $ilng != 0.0) {
                $coord = ['lat' => $ilat, 'lng' => $ilng];
            } else {
                // Essayer la localité exacte de l'infrastructure, puis ses préfixes
                $try = [
                    $loc,
                    substr($loc, 0, 6),
                    substr($loc, 0, 4),
                    substr($loc, 0, 2),
                ];
                foreach ($try as $c) {
                    if ($c && isset($localiteCoords[$c])) {
                        $coord = $localiteCoords[$c];
                        break;
                    }
                }
            }

            foreach ([1 => $niv1, 2 => $niv2, 3 => $niv3] as $level => $code) {
                if (!$code) continue;

                if (!isset($agg[$code])) {
                    $agg[$code] = [
                        'name' => null,
                        'level' => $level,
                        'code' => $code,
                        'count' => 0,
                        'public' => 0,
                        'private' => 0,
                        'cost' => 0.0,
                        'byGroup' => [], // gcode => {count, cost, public, private}
                        // centroïde calculé (moyenne des coordonnées des infras, fallback localité)
                        'lat' => null,
                        'lng' => null,
                        // accumulateurs internes
                        '_sumLat' => 0.0,
                        '_sumLng' => 0.0,
                        '_nCoord' => 0,
                    ];
                }

                // Dédoublonnage: 1 infra = 1 unité de count par localité (et par niveau)
                $uniqInfraKey = $code . '|' . $r->infra_code;
                if (!isset($seenInfraPerLocalite[$uniqInfraKey])) {
                    $agg[$code]['count'] += 1;
                    $seenInfraPerLocalite[$uniqInfraKey] = true;

                    // Coordonnées: infra si dispo, sinon localité rattachée à l'infra (avec fallback préfixes)
                    if ($coord) {
                        $agg[$code]['_sumLat'] += (float) $coord['lat'];
                        $agg[$code]['_sumLng'] += (float) $coord['lng'];
                        $agg[$code]['_nCoord'] += 1;
                    }

                    // Classement public/privé: 1 seule affectation pour conserver public+private = count
                    $uniqTypeKey = $code . '|' . $r->infra_code;
                    if (!isset($seenInfraTypePerLocalite[$uniqTypeKey])) {
                        if ($isPublic) $agg[$code]['public'] += 1;
                        else $agg[$code]['private'] += 1;
                        $seenInfraTypePerLocalite[$uniqTypeKey] = $isPublic ? 'public' : 'private';
                    }
                }

                $agg[$code]['cost'] += $alloc;

                if (!isset($agg[$code]['byGroup'][$gcode])) {
                    $agg[$code]['byGroup'][$gcode] = ['count' => 0, 'cost' => 0.0, 'public' => 0, 'private' => 0];
                }

                $uniqInfraGroupKey = $code . '|' . $gcode . '|' . $r->infra_code;
                if (!isset($seenInfraPerLocaliteAndGroup[$uniqInfraGroupKey])) {
                    $agg[$code]['byGroup'][$gcode]['count'] += 1;
                    if ($isPublic) $agg[$code]['byGroup'][$gcode]['public'] += 1;
                    else $agg[$code]['byGroup'][$gcode]['private'] += 1;
                    $seenInfraPerLocaliteAndGroup[$uniqInfraGroupKey] = true;
                }
                $agg[$code]['byGroup'][$gcode]['cost'] += $alloc;
            }
        }

        // Finaliser lat/lng (moyenne des coordonnées captées)
        foreach ($agg as $k => $v) {
            // Priorité: coordonnées de la localité elle-même (si disponibles), sinon moyenne des infras
            if (isset($localiteCoords[$k])) {
                $agg[$k]['lat'] = (float) $localiteCoords[$k]['lat'];
                $agg[$k]['lng'] = (float) $localiteCoords[$k]['lng'];
            } else {
                $n = (int) ($agg[$k]['_nCoord'] ?? 0);
                if ($n > 0) {
                    $agg[$k]['lat'] = $agg[$k]['_sumLat'] / $n;
                    $agg[$k]['lng'] = $agg[$k]['_sumLng'] / $n;
                } else {
                    $agg[$k]['lat'] = null;
                    $agg[$k]['lng'] = null;
                }
            }
            unset($agg[$k]['_sumLat'], $agg[$k]['_sumLng'], $agg[$k]['_nCoord']);
        }

        Log::info('[autresRequetes.aggregate] agrégat construit', [
            'zones_count' => count($agg),
            'zones_sample' => array_slice(array_values($agg), 0, 2),
        ]);

        // Libellés des localités
        $pays = Pays::where('alpha3', $alpha3)->first();
        if ($pays) {
            $locs = LocalitesPays::where('id_pays', $alpha3)->get(['code_rattachement', 'libelle']);
            $idxNames = [];
            foreach ($locs as $l) $idxNames[$l->code_rattachement] = $l->libelle;
            foreach ($agg as $k => $v) {
                $agg[$k]['name'] = $idxNames[$v['code']] ?? $v['code'];
            }
        } else {
            foreach ($agg as $k => $v) $agg[$k]['name'] = $v['code'];
        }

        Log::info('[autresRequetes.aggregate] réponse finale', [
            'projets_count' => count($agg),
        ]);

        return response()->json(['projets' => array_values($agg)]);
    }

    /**
     * Répartition par groupe pour une localité (code préfixe: 2/4/6)
     */
    public function repartition(Request $request)
    {
        $alpha3 = session('pays_selectionne');
        if (!$alpha3) return response()->json(['error' => 'pays_selectionne manquant'], 400);

        $codePrefix = $request->input('code');
        if (!$codePrefix) return response()->json(['error' => 'Paramètre code requis'], 422);

        Log::info('[autresRequetes.repartition] requête reçue', [
            'alpha3' => $alpha3,
            'code' => $codePrefix,
            'groupe' => $request->input('groupe'),
            'groupe_session' => session('projet_selectionne'),
        ]);

        $sessionGroup = session('projet_selectionne');
        $requestedGroup = $request->input('groupe');
        $groupFilter = null;
        if ($requestedGroup) {
            $groupFilter = $requestedGroup;
        } elseif ($sessionGroup && strtoupper($sessionGroup) !== 'BTP') {
            $groupFilter = $sessionGroup;
        }

        $rows = DB::table('jouir as j')
            ->join('projets as p', 'p.code_projet', '=', 'j.code_projet')
            ->join('infrastructures as i', 'i.code', '=', 'j.code_Infrastructure')
            ->where('i.code_pays', $alpha3)
            ->where('i.code_localite', 'like', $codePrefix . '%')
            ->when($groupFilter, fn ($q) => $q->where('i.code_groupe_projet', $groupFilter))
            ->select([
                'p.code_projet',
                'p.cout_projet',
                DB::raw("i.code_groupe_projet as gcode"),
                DB::raw("SUBSTRING(p.code_projet,7,1) as type_fin"),
                'i.code as infra_code',
            ])
            ->get();

        Log::info('[autresRequetes.repartition] lignes brutes récupérées', [
            'rows_count' => $rows->count(),
        ]);

        if ($rows->isEmpty()) {
            return response()->json(['code' => $codePrefix, 'byGroup' => []]);
        }

        $infrasParProjet = [];
        foreach ($rows as $r) {
            $infrasParProjet[$r->code_projet] = ($infrasParProjet[$r->code_projet] ?? 0) + 1;
        }

        $byGroup = [];
        $seen = [];

        foreach ($rows as $r) {
            $gcode = (string) ($r->gcode ?? '');
            if ($gcode === '') continue;

            if (!isset($byGroup[$gcode])) {
                $byGroup[$gcode] = ['count' => 0, 'cost' => 0.0, 'public' => 0, 'private' => 0];
            }

            $isPublic = ((string) $r->type_fin) === '1';
            $alloc = 0.0;
            if (!empty($r->cout_projet) && ($infrasParProjet[$r->code_projet] ?? 0) > 0) {
                $alloc = (float) $r->cout_projet / (float) $infrasParProjet[$r->code_projet];
            }
            $byGroup[$gcode]['cost'] += $alloc;

            $uniq = $gcode . '|' . $r->infra_code;
            if (!isset($seen[$uniq])) {
                $byGroup[$gcode]['count'] += 1;
                if ($isPublic) $byGroup[$gcode]['public'] += 1;
                else $byGroup[$gcode]['private'] += 1;
                $seen[$uniq] = true;
            }
        }

        Log::info('[autresRequetes.repartition] byGroup construit', [
            'groups_count' => count($byGroup),
            'sample' => array_slice($byGroup, 0, 2, true),
        ]);

        return response()->json(['code' => $codePrefix, 'byGroup' => $byGroup]);
    }

    /**
     * Détails: liste des infrastructures bénéficiaires (par préfixe localité + groupe optionnel)
     * (utile si on veut un drawer/une liste à l’avenir)
     */
    public function details(Request $request)
    {
        $alpha3 = session('pays_selectionne');
        if (!$alpha3) return response()->json(['error' => 'pays_selectionne manquant'], 400);

        $codePrefix = $request->input('code');
        $groupe = $request->input('groupe');
        $limit = (int) $request->input('limit', 2000);

        if (!$codePrefix) return response()->json(['error' => 'Paramètre code requis'], 422);

        Log::info('[autresRequetes.details] requête reçue', [
            'alpha3' => $alpha3,
            'code' => $codePrefix,
            'groupe' => $groupe,
            'limit' => $limit,
        ]);

        $rows = DB::table('jouir as j')
            ->join('projets as p', 'p.code_projet', '=', 'j.code_projet')
            ->join('infrastructures as i', 'i.code', '=', 'j.code_Infrastructure')
            ->where('i.code_pays', $alpha3)
            ->where('i.code_localite', 'like', $codePrefix . '%')
            ->when($groupe, fn ($q) => $q->where('i.code_groupe_projet', $groupe))
            ->select([
                'i.code as infra_code',
                'i.libelle as infra_lib',
                'i.latitude',
                'i.longitude',
                DB::raw('i.code_groupe_projet as gcode'),
            ])
            ->limit($limit)
            ->get()
            ->unique('infra_code')
            ->values();

        Log::info('[autresRequetes.details] résultat', [
            'count' => $rows->count(),
            'sample' => $rows->take(3),
        ]);

        return response()->json([
            'count' => $rows->count(),
            'infras' => $rows,
        ]);
    }

    /**
     * Markers: renvoie les infrastructures bénéficiaires avec coordonnées.
     */
    public function markers(Request $request)
    {
        $alpha3 = session('pays_selectionne');
        if (!$alpha3) return response()->json(['markers' => []]);

        $codePrefix = (string) $request->input('code', '');
        $groupe = $request->input('groupe');
        $limit = (int) $request->input('limit', 20000);

        Log::info('[autresRequetes.markers] requête reçue', [
            'alpha3' => $alpha3,
            'code' => $codePrefix,
            'groupe' => $groupe,
            'limit' => $limit,
        ]);

        $rows = DB::table('jouir as j')
            ->join('projets as p', 'p.code_projet', '=', 'j.code_projet')
            ->join('infrastructures as i', 'i.code', '=', 'j.code_Infrastructure')
            ->where('i.code_pays', $alpha3)
            ->when($codePrefix !== '', fn ($q) => $q->where('i.code_localite', 'like', $codePrefix . '%'))
            ->when($groupe, fn ($q) => $q->where('i.code_groupe_projet', $groupe))
            ->whereNotNull('i.latitude')
            ->whereNotNull('i.longitude')
            ->select([
                'i.code as infra_code',
                'i.libelle as infra_lib',
                'i.latitude',
                'i.longitude',
                DB::raw('i.code_groupe_projet as gcode'),
            ])
            ->limit($limit)
            ->get()
            ->unique('infra_code')
            ->values();

        Log::info('[autresRequetes.markers] résultat', [
            'rows_count' => $rows->count(),
        ]);

        $markers = $rows->map(fn ($r) => [
            'lat' => (float) $r->latitude,
            'lng' => (float) $r->longitude,
            'code' => $r->infra_code,
            'label' => $r->infra_lib,
            'groupe' => $r->gcode,
        ])->values();

        return response()->json(['markers' => $markers]);
    }

    /**
     * Légende pour la carte (count / cost)
     */
    public function legend(Request $request)
    {
        $metric = $request->input('metric', 'count'); // count|cost

        Log::info('[autresRequetes.legend] requête reçue', [
            'metric' => $metric,
        ]);

        $seuils = ($metric === 'cost')
            ? [
                ['borneInf' => 0, 'borneSup' => 0, 'couleur' => '#f1f5f9'],
                ['borneInf' => 1_000_000, 'borneSup' => 500_000_000, 'couleur' => '#fde68a'],
                ['borneInf' => 500_000_000, 'borneSup' => 2_000_000_000, 'couleur' => '#fbbf24'],
                ['borneInf' => 2_000_000_000, 'borneSup' => 5_000_000_000, 'couleur' => '#f59e0b'],
                ['borneInf' => 5_000_000_000, 'borneSup' => null, 'couleur' => '#d97706'],
            ]
            : [
                ['borneInf' => 0, 'borneSup' => 0, 'couleur' => '#f1f5f9'],
                ['borneInf' => 1, 'borneSup' => 2, 'couleur' => '#c7d2fe'],
                ['borneInf' => 3, 'borneSup' => 5, 'couleur' => '#93c5fd'],
                ['borneInf' => 6, 'borneSup' => 10, 'couleur' => '#60a5fa'],
                ['borneInf' => 11, 'borneSup' => null, 'couleur' => '#2563eb'],
            ];

        return response()->json([
            'label' => $metric === 'count'
                ? 'Nombre d’infrastructures bénéficiaires'
                : 'Montant réparti des projets',
            'seuils' => $seuils,
        ]);
    }
}
