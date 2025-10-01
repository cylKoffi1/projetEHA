<?php

namespace App\Http\Controllers\ParSpecifique;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use App\Models\Acteur;
use App\Models\Ecran;
use App\Models\Pays;
use App\Models\TypeActeur;
use App\Models\TypeFinancement;
use App\Models\FormeJuridique;
use App\Models\SecteurActivite;
use App\Models\FonctionUtilisateur;
use App\Models\Genre;
use App\Models\Pieceidentite;
use App\Models\SituationMatrimonial;
use App\Models\PersonnePhysique;
use App\Models\PersonneMorale;
use App\Models\Possederpiece;
use App\Models\Representants;
use App\Models\SecteurActiviteActeur;

class ActeurController extends Controller
{
    // =======================
    // Helpers logging
    // =======================
    private function logStart(string $fn, array $ctx = []): float
    {
        $t0 = microtime(true);
        Log::info("[Acteurs::$fn] ▶️ start", array_merge([
            'uid' => optional(Auth::user())->id,
            'session_pays' => session('pays_selectionne'),
        ], $ctx));
        return $t0;
    }

    private function logEnd(string $fn, float $t0, array $ctx = []): void
    {
        $ms = round((microtime(true) - $t0) * 1000, 2);
        Log::info("[Acteurs::$fn] ✅ end", array_merge(['ms' => $ms], $ctx));
    }

    private function logCatch(string $fn, \Throwable $e, array $ctx = []): void
    {
        Log::error("[Acteurs::$fn] ❌ error: ".$e->getMessage(), array_merge([
            'code' => $e->getCode(),
            'file' => $e->getFile().':'.$e->getLine(),
            'trace' => str_replace("\n", ' | ', (string)$e->getTraceAsString()),
        ], $ctx));
    }

    // =======================
    // INDEX
    // =======================
    public function index(Request $request)
    {
        $t0 = $this->logStart('index', [
            'query'  => $request->query(),
            'ajax'   => $request->ajax(),
        ]);

        try {
            // Contexte & vues
            $view    = $request->get('view', 'cards'); // 'cards' | 'table'
            $perPage = 12;

            // Pays via session
            $paysSelectionne = session('pays_selectionne');
            if (!$paysSelectionne) {
                Log::warning('[Acteurs::index] pays_selectionne absent en session');
                $this->logEnd('index', $t0, ['redirect' => 'admin']);
                return redirect()
                    ->route('admin', ['ecran_id' => $request->input('ecran_id')])
                    ->with('error', 'Veuillez contacter l’administrateur pour vous attribuer un pays avant de continuer.');
            }

            $pays = Pays::where('alpha3', $paysSelectionne)->first();
            if (!$pays) {
                Log::warning('[Acteurs::index] Pays introuvable', ['alpha3' => $paysSelectionne]);
                $this->logEnd('index', $t0, ['redirect' => 'admin']);
                return redirect()
                    ->route('admin', ['ecran_id' => $request->input('ecran_id')])
                    ->with('error', 'Pays introuvable pour le code sélectionné.');
            }

            // Filtres
            $q      = trim((string) $request->get('q', ''));
            $type   = $request->get('type');
            $fin    = $request->get('fin');
            $status = $request->get('status'); // 'active'|'inactive'|null

            Log::debug('[Acteurs::index] filtres', compact('q','type','fin','status','view'));

            // Base query (inclure inactifs)
            $acteursQuery = Acteur::withInactive()
                ->with(['pays', 'type', 'user.fonctionUtilisateur'])
                ->where('code_pays', $pays->alpha3);

            if ($q !== '') {
                $acteursQuery->where(function ($w) use ($q) {
                    $w->where('libelle_court', 'like', "%{$q}%")
                      ->orWhere('libelle_long', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%")
                      ->orWhere('telephone', 'like', "%{$q}%");
                });
            }
            if ($type)   $acteursQuery->where('type_acteur', $type);
            if ($fin)    $acteursQuery->where('type_financement', $fin);
            if ($status === 'active')   $acteursQuery->where('is_active', true);
            if ($status === 'inactive') $acteursQuery->where('is_active', false);

            $acteursQuery->orderByRaw("COALESCE(NULLIF(libelle_court,''), libelle_long) asc");

            // On log le total avant chargement
            $totalFiltered = (clone $acteursQuery)->count();
            Log::info('[Acteurs::index] totals', [
                'total_filtered' => $totalFiltered,
                'mode' => $view,
            ]);

            // Dataset
            if ($view === 'table') {
                $acteurs = $acteursQuery->get();
                Log::info('[Acteurs::index] dataset (table)', ['count' => $acteurs->count()]);
            } else {
                $acteurs = $acteursQuery->paginate($perPage)->withQueryString();
                Log::info('[Acteurs::index] dataset (cards)', [
                    'per_page' => $perPage,
                    'current'  => $acteurs->currentPage(),
                    'total'    => $acteurs->total(),
                    'count'    => $acteurs->count(),
                ]);
            }

            // Listes
            $TypeActeurs            = TypeActeur::orderBy('libelle_type_acteur')->get();
            $typeFinancements       = TypeFinancement::orderBy('libelle')->get();
            $formeJuridiques        = FormeJuridique::orderBy('forme')->get();
            $SecteurActivites       = SecteurActivite::orderBy('libelle')->get();
            $genres                 = Genre::orderBy('libelle_genre')->get();
            $SituationMatrimoniales = SituationMatrimonial::orderBy('libelle')->get();
            $fonctionUtilisateurs   = FonctionUtilisateur::orderBy('libelle_fonction')->get();
            $tousPays               = Pays::whereNotIn('id', [0,300,301,302,303,304])->orderBy('nom_fr_fr')->get();
            $ecran                  = Ecran::find($request->input('ecran_id'));
            $Pieceidentite          = Pieceidentite::orderBy('libelle_long')->get();

            Log::debug('[Acteurs::index] listes chargées', [
                'types' => $TypeActeurs->count(),
                'fin'   => $typeFinancements->count(),
                'sect'  => $SecteurActivites->count(),
                'genres'=> $genres->count(),
                'sitmat'=> $SituationMatrimoniales->count(),
                'pays'  => $tousPays->count(),
                'piece' => $Pieceidentite->count(),
                'ecran' => optional($ecran)->id,
            ]);

            // Représentants (PP actives du pays)
            $acteurRepres = DB::table('acteur as a')
                ->join('personne_physique as pp', 'pp.code_acteur', '=', 'a.code_acteur')
                ->select('a.code_acteur', 'a.libelle_long', 'a.libelle_court',
                         'pp.telephone_mobile', 'pp.telephone_bureau', 'pp.email')
                ->where('a.code_pays', $pays->alpha3)
                ->where('a.is_active', 1)
                ->get();
            Log::info('[Acteurs::index] acteurRepres (PP) count', ['count' => $acteurRepres->count()]);

            if ($request->ajax()) {
                Log::info('[Acteurs::index] AJAX partial _list');
                $this->logEnd('index', $t0, ['ajax' => true]);
                return view('parSpecifique.acteurs._list', compact('acteurs','ecran','pays'));
            }

            $this->logEnd('index', $t0, ['view' => 'full']);
            return view('parSpecifique.acteur', compact(
                'acteurs',
                'ecran',
                'TypeActeurs',
                'typeFinancements',
                'Pieceidentite',
                'formeJuridiques',
                'SecteurActivites',
                'genres',
                'SituationMatrimoniales',
                'fonctionUtilisateurs',
                'tousPays',
                'acteurRepres',
                'pays'
            ));
        } catch (\Throwable $e) {
            $this->logCatch('index', $e);
            $this->logEnd('index', $t0, ['error' => true]);
            return back()->with('error', 'Impossible de charger la liste des acteurs.');
        }
    }

    // =======================
    // EDIT (JSON)
    // =======================
    public function edit($id)
    {
        $t0 = $this->logStart('edit', ['id' => $id]);

        try {
            $a = Acteur::with([
                'personnePhysique',
                'personneMorale',
                'secteurActiviteActeur',
                'representants',
                'possederpiece',
                'user',
            ])->where('code_acteur', $id)->firstOrFail();

            Log::info('[Acteurs::edit] found', [
                'code_acteur' => $a->code_acteur,
                'pp' => (bool)$a->personnePhysique,
                'pm' => (bool)$a->personneMorale,
                'sectors' => $a->secteurActiviteActeur->count(),
                'reps' => $a->representants->count(),
                'pieces' => $a->possederpiece->count(),
            ]);

            $json = $this->normalizeJson($a);
            Log::debug('[Acteurs::edit] normalizeJson', [
                'type_personne' => $json['type_personne'] ?? null,
                'nomRL_count'   => is_array($json['nomRL'] ?? null) ? count($json['nomRL']) : 0,
                'nomPC_count'   => is_array($json['nomPC'] ?? null) ? count($json['nomPC']) : 0,
            ]);

            $this->logEnd('edit', $t0);
            return response()->json($json);
        } catch (\Throwable $e) {
            $this->logCatch('edit', $e, ['id' => $id]);
            $this->logEnd('edit', $t0, ['error' => true]);
            return response()->json(['error' => 'Acteur introuvable'], 404);
        }
    }

    // =======================
    // STORE
    // =======================
    public function store(Request $r)
    {
        $t0 = $this->logStart('store', [
            'type_personne' => $r->input('type_personne'),
            'type_acteur'   => $r->input('type_acteur'),
            'type_fin'      => $r->input('type_financement'),
            'has_photo'     => $r->hasFile('photo'),
        ]);

        $rules = $this->rules($r->input('type_personne'), 'store');
        Log::debug('[Acteurs::store] validation rules', ['keys' => array_keys($rules)]);
        $r->validate($rules, $this->messages());

        try {
            return DB::transaction(function() use ($r, $t0) {
                $alpha3 = session('pays_selectionne') ?? $r->input('code_pays');

                $acteur = new Acteur();
                $acteur->code_pays        = $alpha3;
                $acteur->type_acteur      = $r->input('type_acteur'); // code
                $acteur->type_financement = $r->input('type_financement');
                $acteur->libelle_long     = $r->input('libelle_long') ?: $r->input('nom');
                $acteur->libelle_court    = $r->input('libelle_court') ?: $r->input('prenom');
                $acteur->email            = $r->input('emailI') ?: $r->input('emailRL');
                $acteur->telephone        = $r->input('telephoneBureauIndividu') ?: $r->input('telephone1RL');
                $acteur->adresse          = $r->input('adresseSiegeIndividu') ?: $r->input('AdresseSiègeEntreprise');
                $acteur->is_active        = true;
                $acteur->save();

                Log::info('[Acteurs::store] acteur créé', [
                    'code_acteur' => $acteur->code_acteur,
                    'pays' => $acteur->code_pays,
                    'type' => $acteur->type_acteur,
                    'fin'  => $acteur->type_financement,
                ]);

                if ($r->hasFile('photo')) {
                    $path = $r->file('photo')->store('acteurs', 'public');
                    $acteur->photo = 'storage/'.$path;
                    $acteur->save();
                    Log::info('[Acteurs::store] photo stockée', ['photo' => $acteur->photo]);
                }

                if ($r->input('type_personne') === 'physique') {
                    $pp = PersonnePhysique::create([
                        'code_acteur'              => $acteur->code_acteur,
                        'nom'                      => $r->input('nom'),
                        'prenom'                   => $r->input('prenom'),
                        'date_naissance'           => $r->input('date_naissance'),
                        'nationalite'              => $r->input('nationnalite'),
                        'email'                    => $r->input('emailI'),
                        'code_postal'              => $r->input('CodePostalI'),
                        'adresse_postale'          => $r->input('AdressePostaleIndividu'),
                        'adresse_siege'            => $r->input('adresseSiegeIndividu'),
                        'telephone_bureau'         => $r->input('telephoneBureauIndividu'),
                        'telephone_mobile'         => $r->input('telephoneMobileIndividu'),
                        'num_fiscal'               => $r->input('numeroFiscal'),
                        'genre_id'                 => $r->input('genre'),
                        'situation_matrimoniale_id'=> $r->input('situationMatrimoniale'),
                        'is_active'                => true,
                    ]);
                    Log::info('[Acteurs::store] PP créée', ['pp_id' => $pp->id]);

                    if ($r->filled(['piece_identite','numeroPiece'])) {
                        $pc = Possederpiece::create([
                            'idPieceIdent'        => $r->input('piece_identite'),
                            'idPersonnePhysique'  => $acteur->code_acteur,
                            'NumPieceIdent'       => $r->input('numeroPiece'),
                            'DateEtablissement'   => $r->input('dateEtablissement'),
                            'DateExpiration'      => $r->input('dateExpiration'),
                        ]);
                        Log::info('[Acteurs::store] pièce identité créée', ['possederpiece_id' => $pc->id ?? null]);
                    }

                    $sect = (array)$r->input('SecteurActI', []);
                    foreach ($sect as $s) {
                        SecteurActiviteActeur::create([
                            'code_acteur' => $acteur->code_acteur,
                            'code_secteur'=> $s,
                        ]);
                    }
                    Log::info('[Acteurs::store] secteurs PP', ['count' => count($sect)]);

                    if ($acteur->user && $r->filled('fonctionUser')) {
                        $acteur->user->update(['fonction_utilisateur' => $r->input('fonctionUser')]);
                        Log::info('[Acteurs::store] user fonction maj', ['fonction' => $r->input('fonctionUser')]);
                    }
                } else {
                    $pm = PersonneMorale::create([
                        'code_acteur'         => $acteur->code_acteur,
                        'raison_sociale'      => $r->input('libelle_long'),
                        'date_creation'       => $r->input('date_creation'),
                        'forme_juridique'     => $r->input('FormeJuridique'),
                        'num_immatriculation' => $r->input('NumeroImmatriculation'),
                        'nif'                 => $r->input('nif'),
                        'rccm'                => $r->input('rccm'),
                        'capital'             => $r->input('CapitalSocial'),
                        'numero_agrement'     => $r->input('Numéroagrement'),
                        'code_postal'         => $r->input('CodePostaleEntreprise'),
                        'adresse_postale'     => $r->input('AdressePostaleEntreprise'),
                        'adresse_siege'       => $r->input('AdresseSiègeEntreprise'),
                    ]);
                    Log::info('[Acteurs::store] PM créée', ['pm_id' => $pm->id]);

                    $rl = (array)$r->input('nomRL', []);
                    foreach ($rl as $repId) {
                        Representants::create([
                            'entreprise_id'       => $pm->code_acteur,
                            'representant_id'     => $repId,
                            'role'                => 'Représentant Légal',
                            'idPays'              => $acteur->code_pays,
                            'date_representation' => Carbon::today(),
                        ]);
                    }
                    Log::info('[Acteurs::store] RL ajoutés', ['count' => count($rl)]);

                    $pcs = (array)$r->input('nomPC', []);
                    foreach ($pcs as $pcId) {
                        Representants::create([
                            'entreprise_id'       => $pm->code_acteur,
                            'representant_id'     => $pcId,
                            'role'                => 'Personne de Contact',
                            'idPays'              => $acteur->code_pays,
                            'date_representation' => Carbon::today(),
                        ]);
                    }
                    Log::info('[Acteurs::store] PC ajoutés', ['count' => count($pcs)]);

                    $sect = (array)$r->input('secteurActivite', []);
                    foreach ($sect as $s) {
                        SecteurActiviteActeur::create([
                            'code_acteur' => $acteur->code_acteur,
                            'code_secteur'=> $s,
                        ]);
                    }
                    Log::info('[Acteurs::store] secteurs PM', ['count' => count($sect)]);
                }

                $this->logEnd('store', $t0, ['code_acteur' => $acteur->code_acteur]);
                return redirect()->route('acteurs.index', ['ecran_id' => $r->input('ecran_id')])
                    ->with('success', 'Acteur créé avec succès.');
            });
        } catch (\Throwable $e) {
            $this->logCatch('store', $e);
            $this->logEnd('store', $t0, ['error' => true]);
            return back()->withErrors("Erreur lors de la création : ".$e->getMessage());
        }
    }

    // =======================
    // UPDATE
    // =======================
    public function update(Request $r, $id)
    {
        $t0 = $this->logStart('update', ['id' => $id, 'type_personne' => $r->input('type_personne')]);

        $acteur = Acteur::with(['personnePhysique','personneMorale','possederpiece','representants','secteurActiviteActeur','user'])
            ->where('code_acteur',$id)->firstOrFail();

        $typePersonne = $r->input('type_personne', $acteur->personnePhysique ? 'physique' : 'morale');

        $rules = $this->rules($typePersonne,'update');
        Log::debug('[Acteurs::update] validation rules', ['keys' => array_keys($rules)]);
        $r->validate($rules, $this->messages());

        try {
            return DB::transaction(function() use ($r,$acteur,$typePersonne,$t0) {
                // communs
                $acteur->type_acteur      = $r->input('type_acteur', $acteur->type_acteur);
                $acteur->type_financement = $r->input('type_financement', $acteur->type_financement);
                $acteur->libelle_long     = $r->input('libelle_long') ?: $acteur->libelle_long;
                $acteur->libelle_court    = $r->input('libelle_court') ?: $acteur->libelle_court;
                $acteur->email            = $r->input('email') ?: $r->input('emailRL') ?: $r->input('emailI') ?: $acteur->email;
                $acteur->telephone        = $r->input('telephone') ?: $r->input('telephone1RL') ?: $r->input('telephoneBureauIndividu') ?: $acteur->telephone;
                $acteur->adresse          = $r->input('adresse') ?: $r->input('adresseSiegeIndividu') ?: $r->input('AdresseSiègeEntreprise') ?: $acteur->adresse;
                $acteur->code_pays        = session('pays_selectionne') ?? $r->input('code_pays', $acteur->code_pays);
                $acteur->save();

                Log::info('[Acteurs::update] acteur maj', [
                    'code_acteur' => $acteur->code_acteur,
                    'type' => $acteur->type_acteur,
                    'fin'  => $acteur->type_financement,
                ]);

                if ($r->hasFile('photo')) {
                    if ($acteur->photo && str_starts_with($acteur->photo, 'storage/')) {
                        $rel = substr($acteur->photo, strlen('storage/'));
                        if (Storage::disk('public')->exists($rel)) Storage::disk('public')->delete($rel);
                    }
                    $path = $r->file('photo')->store('acteurs','public');
                    $acteur->photo = 'storage/'.$path;
                    $acteur->save();
                    Log::info('[Acteurs::update] photo remplacée', ['photo' => $acteur->photo]);
                }

                if ($typePersonne === 'physique') {
                    if ($pp = $acteur->personnePhysique) {
                        $pp->update([
                            'nom'                      => $r->input('nom'),
                            'prenom'                   => $r->input('prenom'),
                            'date_naissance'           => $r->input('date_naissance'),
                            'nationalite'              => $r->input('nationnalite'),
                            'email'                    => $r->input('emailI'),
                            'code_postal'              => $r->input('CodePostalI'),
                            'adresse_postale'          => $r->input('AdressePostaleIndividu'),
                            'adresse_siege'            => $r->input('adresseSiegeIndividu'),
                            'telephone_bureau'         => $r->input('telephoneBureauIndividu'),
                            'telephone_mobile'         => $r->input('telephoneMobileIndividu'),
                            'num_fiscal'               => $r->input('numeroFiscal'),
                            'genre_id'                 => $r->input('genre'),
                            'situation_matrimoniale_id'=> $r->input('situationMatrimoniale'),
                        ]);
                        Log::info('[Acteurs::update] PP maj', ['pp_id' => $pp->id]);
                    }

                    $piece = $acteur->possederpiece->first();
                    if ($r->filled(['piece_identite','numeroPiece'])) {
                        if ($piece) {
                            $piece->update([
                                'idPieceIdent'      => $r->input('piece_identite'),
                                'NumPieceIdent'     => $r->input('numeroPiece'),
                                'DateEtablissement' => $r->input('dateEtablissement'),
                                'DateExpiration'    => $r->input('dateExpiration'),
                            ]);
                            Log::info('[Acteurs::update] pièce maj', ['possederpiece_id' => $piece->id ?? null]);
                        } else {
                            $pc = Possederpiece::create([
                                'idPieceIdent'       => $r->input('piece_identite'),
                                'idPersonnePhysique' => $acteur->code_acteur,
                                'NumPieceIdent'      => $r->input('numeroPiece'),
                                'DateEtablissement'  => $r->input('dateEtablissement'),
                                'DateExpiration'     => $r->input('dateExpiration'),
                            ]);
                            Log::info('[Acteurs::update] pièce créée', ['possederpiece_id' => $pc->id ?? null]);
                        }
                    }

                    SecteurActiviteActeur::where('code_acteur', $acteur->code_acteur)->delete();
                    $sect = (array)$r->input('SecteurActI', []);
                    foreach ($sect as $s) {
                        SecteurActiviteActeur::create([
                            'code_acteur' => $acteur->code_acteur,
                            'code_secteur'=> $s
                        ]);
                    }
                    Log::info('[Acteurs::update] secteurs PP maj', ['count' => count($sect)]);

                    if ($acteur->user && $r->filled('fonctionUser')) {
                        $acteur->user->update(['fonction_utilisateur' => $r->input('fonctionUser')]);
                        Log::info('[Acteurs::update] user fonction maj', ['fonction' => $r->input('fonctionUser')]);
                    }
                } else {
                    if ($pm = $acteur->personneMorale) {
                        $pm->update([
                            'raison_sociale'      => $r->input('libelle_long'),
                            'date_creation'       => $r->input('date_creation'),
                            'forme_juridique'     => $r->input('FormeJuridique'),
                            'num_immatriculation' => $r->input('NumeroImmatriculation'),
                            'nif'                 => $r->input('nif'),
                            'rccm'                => $r->input('rccm'),
                            'capital'             => $r->input('CapitalSocial'),
                            'numero_agrement'     => $r->input('Numéroagrement'),
                            'code_postal'         => $r->input('CodePostaleEntreprise'),
                            'adresse_postale'     => $r->input('AdressePostaleEntreprise'),
                            'adresse_siege'       => $r->input('AdresseSiègeEntreprise'),
                        ]);
                        Log::info('[Acteurs::update] PM maj', ['pm_id' => $pm->id]);
                    }

                    // RL diffs
                    $incomingRL = collect((array)$r->input('nomRL', []))->filter()->values();
                    $existingRL = $acteur->representants()->where('role','Représentant Légal')->pluck('representant_id');
                    $toAdd = $incomingRL->diff($existingRL);
                    $toDel = $existingRL->diff($incomingRL);
                    Log::info('[Acteurs::update] RL diffs', ['add' => $toAdd->values(), 'del' => $toDel->values()]);

                    foreach ($toAdd as $representantId) {
                        Representants::updateOrCreate(
                            ['entreprise_id'=>$acteur->code_acteur,'representant_id'=>$representantId,'role'=>'Représentant Légal'],
                            ['idPays'=>$acteur->code_pays,'date_representation'=>Carbon::today()]
                        );
                    }
                    if ($toDel->isNotEmpty()) {
                        Representants::where('entreprise_id',$acteur->code_acteur)
                            ->where('role','Représentant Légal')
                            ->whereIn('representant_id',$toDel)->delete();
                    }

                    // PC diffs
                    $incomingPC = collect((array)$r->input('nomPC', []))->filter()->values();
                    $existingPC = $acteur->representants()->where('role','Personne de Contact')->pluck('representant_id');
                    $toAdd = $incomingPC->diff($existingPC);
                    $toDel = $existingPC->diff($incomingPC);
                    Log::info('[Acteurs::update] PC diffs', ['add' => $toAdd->values(), 'del' => $toDel->values()]);

                    foreach ($toAdd as $contactId) {
                        Representants::updateOrCreate(
                            ['entreprise_id'=>$acteur->code_acteur,'representant_id'=>$contactId,'role'=>'Personne de Contact'],
                            ['idPays'=>$acteur->code_pays,'date_representation'=>Carbon::today()]
                        );
                    }
                    if ($toDel->isNotEmpty()) {
                        Representants::where('entreprise_id',$acteur->code_acteur)
                            ->where('role','Personne de Contact')
                            ->whereIn('representant_id',$toDel)->delete();
                    }

                    SecteurActiviteActeur::where('code_acteur', $acteur->code_acteur)->delete();
                    $sect = (array)$r->input('secteurActivite', []);
                    foreach ($sect as $s) {
                        SecteurActiviteActeur::create([
                            'code_acteur' => $acteur->code_acteur,
                            'code_secteur'=> $s
                        ]);
                    }
                    Log::info('[Acteurs::update] secteurs PM maj', ['count' => count($sect)]);
                }

                $this->logEnd('update', $t0, ['code_acteur' => $acteur->code_acteur]);
                return back()->with('success','Acteur mis à jour.');
            });
        } catch (\Throwable $e) {
            $this->logCatch('update', $e, ['id' => $id]);
            $this->logEnd('update', $t0, ['error' => true]);
            return back()->withErrors("Erreur lors de la mise à jour : ".$e->getMessage());
        }
    }

    // =======================
    // DESTROY
    // =======================
    public function destroy($id)
    {
        $t0 = $this->logStart('destroy', ['id' => $id]);
        try {
            $acteur = Acteur::withInactive()->where('code_acteur',$id)
                ->where('code_pays', session('pays_selectionne'))->firstOrFail();

            $acteur->update(['is_active'=>false]);
            Log::info('[Acteurs::destroy] désactivé', ['code_acteur' => $acteur->code_acteur]);

            $this->logEnd('destroy', $t0);
            return back()->with('success','Acteur désactivé.');
        } catch (\Throwable $e) {
            $this->logCatch('destroy', $e, ['id' => $id]);
            $this->logEnd('destroy', $t0, ['error' => true]);
            return back()->withErrors('Erreur lors de la désactivation.');
        }
    }

    // =======================
    // RESTORE
    // =======================
    public function restore($id)
    {
        $t0 = $this->logStart('restore', ['id' => $id]);
        try {
            $acteur = Acteur::withInactive()->where('code_acteur',$id)
                ->where('code_pays', session('pays_selectionne'))->firstOrFail();

            $acteur->update(['is_active'=>true]);
            Log::info('[Acteurs::restore] réactivé', ['code_acteur' => $acteur->code_acteur]);

            $this->logEnd('restore', $t0);
            return back()->with('success','Acteur réactivé.');
        } catch (\Throwable $e) {
            $this->logCatch('restore', $e, ['id' => $id]);
            $this->logEnd('restore', $t0, ['error' => true]);
            return back()->withErrors('Erreur lors de la réactivation.');
        }
    }

    // =======================
    // Validation rules/messages (loggés)
    // =======================
    private function rules(?string $typePersonne, string $ctx='store'): array
    {
        Log::debug('[Acteurs::rules] build', ['type_personne' => $typePersonne, 'ctx' => $ctx]);

        $base = [
            'code_pays'         => ['nullable','string','size:3'],
            'type_acteur'       => ['required','string','max:20'], // code type (ind/etp/…)
            'type_financement'  => ['required','string','max:50'],
            'type_personne'     => ['required', Rule::in(['physique','morale'])],
            'photo'             => ['nullable','image','max:2048'],
        ];
        if ($typePersonne === 'physique') {
            $rules = $base + [
                'nom'                      => ['required','string','max:150'],
                'prenom'                   => ['required','string','max:150'],
                'emailI'                   => ['nullable','email','max:200'],
                'date_naissance'           => ['nullable','date'],
                'nationnalite'             => ['nullable'],
                'CodePostalI'              => ['nullable','string','max:20'],
                'AdressePostaleIndividu'   => ['nullable','string','max:255'],
                'adresseSiegeIndividu'     => ['nullable','string','max:255'],
                'telephoneBureauIndividu'  => ['nullable','string','max:40'],
                'telephoneMobileIndividu'  => ['nullable','string','max:40'],
                'numeroFiscal'             => ['nullable','string','max:60'],
                'genre'                    => ['nullable'],
                'situationMatrimoniale'    => ['nullable'],
                'piece_identite'           => ['nullable'],
                'numeroPiece'              => ['nullable','string','max:80'],
                'dateEtablissement'        => ['nullable','date'],
                'dateExpiration'           => ['nullable','date','after_or_equal:dateEtablissement'],
                'SecteurActI'              => ['nullable','array'],
                'fonctionUser'             => ['nullable','string','max:50'],
            ];
            Log::debug('[Acteurs::rules] physique count', ['n' => count($rules)]);
            return $rules;
        }
        $rules = $base + [
            'libelle_long'            => ['required','string','max:255'],
            'libelle_court'           => ['required','string','max:150'],
            'date_creation'           => ['nullable','date'],
            'FormeJuridique'          => ['nullable'],
            'NumeroImmatriculation'   => ['nullable','string','max:120'],
            'nif'                     => ['nullable','string','max:60'],
            'rccm'                    => ['nullable','string','max:60'],
            'CapitalSocial'           => ['nullable','numeric'],
            'Numéroagrement'          => ['nullable','string','max:120'],
            'CodePostaleEntreprise'   => ['nullable','string','max:20'],
            'AdressePostaleEntreprise'=> ['nullable','string','max:255'],
            'AdresseSiègeEntreprise'  => ['nullable','string','max:255'],
            'emailRL'                 => ['nullable','email','max:200'],
            'telephone1RL'            => ['nullable','string','max:40'],
            'telephone2RL'            => ['nullable','string','max:40'],
            'nomRL'                   => ['nullable','array'],
            'nomPC'                   => ['nullable','array'],
            'secteurActivite'         => ['nullable','array'],
        ];
        Log::debug('[Acteurs::rules] morale count', ['n' => count($rules)]);
        return $rules;
    }

    private function messages(): array
    {
        $messages = [
            'type_acteur.required'       => "Le type d'acteur est requis.",
            'type_financement.required'  => "Le statut (type de financement) est requis.",
            'type_personne.required'     => "Le type de personne est requis.",
            'libelle_long.required'      => "La raison sociale est requise.",
            'libelle_court.required'     => "Le nom abrégé est requis.",
            'photo.image'                => 'La photo doit être une image.',
            'photo.max'                  => 'La photo ne doit pas dépasser 2 Mo.',
        ];
        Log::debug('[Acteurs::messages] loaded', ['keys' => array_keys($messages)]);
        return $messages;
    }

    // =======================
    // normalizeJson (log)
    // =======================
    private function normalizeJson(Acteur $a): array
    {
        $isPhysique = (bool) $a->personnePhysique;
        $isMorale   = (bool) $a->personneMorale;

        $json = [
            'id'                => $a->code_acteur,
            'code_pays'         => $a->code_pays,
            'photo_url'         => $a->photo_url ?? ($a->photo ?? null),

            'type_personne'     => $isPhysique ? 'physique' : ($isMorale ? 'morale' : null),
            'type_acteur_code'  => $a->type_acteur,

            'type_financement'  => $a->type_financement,

            // PM
            'libelle_long'           => $isMorale ? ($a->personneMorale->raison_sociale ?? $a->libelle_long) : $a->libelle_long,
            'libelle_court'          => $a->libelle_court,
            'date_creation'          => $isMorale ? optional($a->personneMorale->date_creation)->format('Y-m-d') : null,
            'FormeJuridique'         => $isMorale ? $a->personneMorale->forme_juridique : null,
            'NumeroImmatriculation'  => $isMorale ? $a->personneMorale->num_immatriculation : null,
            'nif'                    => $isMorale ? $a->personneMorale->nif : null,
            'rccm'                   => $isMorale ? $a->personneMorale->rccm : null,
            'CapitalSocial'          => $isMorale ? $a->personneMorale->capital : null,
            'Numéroagrement'         => $isMorale ? $a->personneMorale->numero_agrement : null,
            'CodePostaleEntreprise'  => $isMorale ? $a->personneMorale->code_postal : null,
            'AdressePostaleEntreprise'=> $isMorale ? $a->personneMorale->adresse_postale : null,
            'AdresseSiègeEntreprise'  => $isMorale ? $a->personneMorale->adresse_siege : null,
            'emailRL'                => $isMorale ? $a->email : null,
            'telephone1RL'           => $isMorale ? $a->telephone : null,
            'telephone2RL'           => $isMorale ? ($a->personneMorale->telephone_bureau ?? null) : null,
            'nomRL'                  => $isMorale ? $a->representants->where('role','Représentant Légal')->pluck('representant_id')->values()->all() : [],
            'nomPC'                  => $isMorale ? $a->representants->where('role','Personne de Contact')->pluck('representant_id')->values()->all() : [],
            'secteurActivite'        => $isMorale ? $a->secteurActiviteActeur->pluck('code_secteur')->values()->all() : [],

            // PP
            'email'                  => $isPhysique ? ($a->personnePhysique->email ?? $a->email) : $a->email,
            'date_naissance'         => $isPhysique ? optional($a->personnePhysique->date_naissance)->format('Y-m-d') : null,
            'nationnalite'           => $isPhysique ? $a->personnePhysique->nationalite : null,
            'CodePostalI'            => $isPhysique ? $a->personnePhysique->code_postal : null,
            'AdressePostaleIndividu' => $isPhysique ? $a->personnePhysique->adresse_postale : null,
            'adresseSiegeIndividu'   => $isPhysique ? $a->personnePhysique->adresse_siege : null,
            'telephoneBureauIndividu'=> $isPhysique ? $a->personnePhysique->telephone_bureau : null,
            'telephoneMobileIndividu'=> $isPhysique ? $a->personnePhysique->telephone_mobile : null,
            'numeroFiscal'           => $isPhysique ? $a->personnePhysique->num_fiscal : null,
            'genre'                  => $isPhysique ? $a->personnePhysique->genre_id : null,
            'situationMatrimoniale'  => $isPhysique ? $a->personnePhysique->situation_matrimoniale_id : null,
            'piece_identite'         => $isPhysique ? optional($a->possederpiece->first())->idPieceIdent : null,
            'numeroPiece'            => $isPhysique ? optional($a->possederpiece->first())->NumPieceIdent : null,
            'dateEtablissement'      => $isPhysique ? optional(optional($a->possederpiece->first())->DateEtablissement)->format('Y-m-d') : null,
            'dateExpiration'         => $isPhysique ? optional(optional($a->possederpiece->first())->DateExpiration)->format('Y-m-d') : null,
            'fonctionUser'           => optional($a->user)->fonction_utilisateur,
            'SecteurActI'            => $isPhysique ? $a->secteurActiviteActeur->pluck('code_secteur')->values()->all() : [],
        ];

        Log::debug('[Acteurs::normalizeJson] result', [
            'id' => $json['id'] ?? null,
            'type_personne' => $json['type_personne'] ?? null,
            'has_photo' => (bool)($json['photo_url'] ?? null),
            'sect_count' => count($json['secteurActivite'] ?? $json['SecteurActI'] ?? []),
        ]);

        return $json;
    }
}
