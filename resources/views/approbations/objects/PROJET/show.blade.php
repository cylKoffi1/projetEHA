{{-- resources/views/approbations/objects/PROJET/creation.blade.php --}}

@php
    /** @var \App\Models\Projet $record */
    use Illuminate\Support\Facades\DB;

    $projet = $record;

    // Référentiels rapides
    $sousDomaine = \App\Models\SousDomaine::where('code_sous_domaine', $projet->code_sous_domaine)->first();
    $domaine     = $sousDomaine?->domaine
                   ?? (\App\Models\Domaine::where('code', substr((string)$projet->code_sous_domaine, 0, 2))->first());

    // MOA
    $maitresOuvrage = DB::table('posseder as p')
        ->leftJoin('acteur as a', 'a.code_acteur', '=', 'p.code_acteur')
        ->where('p.code_projet', $projet->code_projet)
        ->selectRaw('p.*, a.libelle_court, a.libelle_long, a.type_acteur')
        ->get();

    // MOE
    $maitresOeuvre = DB::table('executer as e')
        ->leftJoin('acteur as a', 'a.code_acteur', '=', 'e.code_acteur')
        ->where('e.code_projet', $projet->code_projet)
        ->selectRaw('e.*, a.libelle_court, a.libelle_long, a.type_acteur')
        ->get();

    // Financements
    $financements = DB::table('financer as f')
        ->leftJoin('acteur as a', 'a.code_acteur', '=', 'f.code_acteur')
        ->where('f.code_projet', $projet->code_projet)
        ->selectRaw('f.*, a.libelle_court, a.libelle_long')
        ->get();

    // Documents
    $documents = DB::table('projet_documents')
        ->where('code_projet', $projet->code_projet)
        ->orderByDesc('uploaded_at')
        ->get();

    // Infrastructures + caractéristiques
    $projetsInfra = \App\Models\ProjetInfrastructure::with([
        'infra.valeursCaracteristiques.caracteristique.type',
        'infra.valeursCaracteristiques.unite'
    ])->where('code_projet', $projet->code_projet)->get();

    // Actions à mener
    $actions = \App\Models\ProjetActionAMener::where('code_projet', $projet->code_projet)->get();

    // Bénéficiaires (3 types)
    $benefActeurs = \App\Models\Beneficier::with('acteur')
                    ->where('code_projet', $projet->code_projet)->get();
    $benefLocalites = \App\Models\Profiter::where('code_projet', $projet->code_projet)->get();
    $benefInfra = \App\Models\Jouir::with('infrastructure')
                    ->where('code_projet', $projet->code_projet)->get();

    // Contexte
    $ctxPays   = session('pays_selectionne')   ?: $projet->code_alpha3_pays ?? '—';
    $ctxGroupe = session('projet_selectionne') ?: '—';

    $fmtDate = fn($d) => $d ? \Carbon\Carbon::parse($d)->format('d/m/Y') : '—';
    $fmtNum  = fn($n) => is_numeric($n) ? number_format($n, 0, ',', ' ') : ($n ?? '—');
@endphp

<div class="container-fluid">
    @if(session('success') || session('error'))
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let type = "{{ session('success') ? 'success' : 'error' }}";
            let message = "{{ session('success') ?? session('error') }}";
            alert((type === 'success' ? '✅ ' : '❌ ') + message);
        });
    </script>
    @endif

    <div class="card shadow-sm border-primary mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center" style="height: 57px;">
            <div>
                <small class="d-block"><strong>{{ $projet->code_projet }}</strong></small>
                <small class="d-block">Nature : <strong>Projet</strong></small>
            </div>

            <div class="text-end">
                <small class="d-block"><strong style="width:10px;">Domaine</strong> :
                    <strong>{{ $domaine?->libelle ?? '—' }}</strong>
                </small>
                <small class="d-block"><strong style="width:10px;">Sous domaine</strong> :
                    <strong>{{ $sousDomaine?->lib_sous_domaine ?? ($projet->code_sous_domaine ?: '—') }}</strong>
                </small>
            </div>

            @php $valider = $projet->valider ?? 0; @endphp
            @if((int)$valider === 0)
                <div class="d-flex align-items-center gap-3">
                    <form method="POST" action="{{ route('projets.validation.valider', $projet->code_projet) }}">
                        @csrf
                        <button type="submit" style="background-color: green; color: white;" class="btn btn-outline-success">Valider</button>
                    </form>
                    <button class="btn btn-outline-danger" data-bs-toggle="collapse" data-bs-target="#refusFormTop">
                        Refuser
                    </button>
                </div>
            @endif
        </div>

        <br>

        <div class="card-body">
            <div class="row g-4">

                <div class="col-md-4">
                    <div class="d-flex align-items-start mb-3">
                        <i class="bi bi-calendar-check me-3 fs-4 text-primary"></i>
                        <div>
                            <h6 class="mb-1 fw-bold text-muted">Période</h6>
                            <p class="mb-0">
                                Du {{ $fmtDate($projet->date_demarrage_prevue) }}<br>
                                Au {{ $fmtDate($projet->date_fin_prevue) }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="d-flex align-items-start mb-3">
                        <i class="bi bi-cash-coin me-3 fs-4 text-primary"></i>
                        <div>
                            <h6 class="mb-1 fw-bold text-muted">Budget</h6>
                            <p class="mb-0">{{ $fmtNum($projet->cout_projet) }} {{ $projet->code_devise }}</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="d-flex align-items-start mb-3">
                        <i class="bi bi-building me-3 fs-4 text-primary"></i>
                        <div>
                            <h6 class="mb-1 fw-bold text-muted">Maître d’ouvrage</h6>
                            @if($maitresOuvrage->count())
                                @php
                                    $moa = $maitresOuvrage->first();
                                    $moaLabel = $moa->libelle_court ?: ($moa->libelle_long ?: '—');
                                @endphp
                                <p class="mb-0">
                                    {{ $moaLabel }}
                                    @if(!empty($moa->isAssistant)) <span class="badge bg-info ms-2">Assistant</span> @endif
                                </p>
                            @else
                                <p class="mb-0">—</p>
                            @endif
                        </div>
                    </div>
                </div>

                @if($maitresOeuvre->count())
                <div class="col-md-4">
                    <div class="d-flex align-items-start mb-3">
                        <i class="bi bi-people me-3 fs-4 text-primary"></i>
                        <div>
                            <h6 class="mb-1 fw-bold text-muted">Maîtres d’œuvre</h6>
                            @foreach($maitresOeuvre as $moe)
                                <p class="mb-0">{{ $moe->libelle_court ?: ($moe->libelle_long ?: '—') }}</p>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <div class="col-md-4">
                    <div class="d-flex align-items-start mb-3">
                        <i class="bi bi-geo-alt me-3 fs-4 text-primary"></i>
                        <div>
                            <h6 class="mb-1 fw-bold text-muted">Pays / Groupe</h6>
                            <p class="mb-0 small">Pays : {{ $ctxPays }}<br>Groupe : {{ $ctxGroupe }}</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-info-circle me-3 fs-4 text-primary"></i>
                        <div>
                            <h6 class="mb-1 fw-bold text-muted">Description</h6>
                            <p class="mb-0">{{ $projet->commentaire ?: 'Aucune description fournie.' }}</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @if((int)$valider === 0)
        <div class="collapse mt-3" id="refusFormTop">
            <form method="POST" action="{{ route('projets.validation.refuser', $projet->code_projet) }}">
                @csrf
                <div class="card card-body bg-light">
                    <h6 class="mb-2">Motif du refus</h6>
                    <textarea name="commentaire_refus" rows="3" class="form-control mb-2" placeholder="Indiquez pourquoi vous refusez ce projet..."></textarea>
                    <div class="text-end">
                        <button class="btn btn-secondary me-2" data-bs-toggle="collapse" data-bs-target="#refusFormTop">Annuler</button>
                        <button type="submit" class="btn btn-danger">Confirmer le refus</button>
                    </div>
                </div>
            </form>
        </div>
    @endif

    {{-- ========================= ONGLET SECTIONS ========================= --}}
    <ul class="nav nav-tabs" id="tabsProjet" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-approbations" type="button">Approbations</button></li>
        @if($projetsInfra->count())
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-infra" type="button">Infrastructures</button></li>
        @endif
        @if($actions->count())
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-actions" type="button">Actions</button></li>
        @endif
        @if($benefActeurs->count() || $benefLocalites->count() || $benefInfra->count())
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-beneficiaires" type="button">Bénéficiaires</button></li>
        @endif
        @if($financements->count())
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-finance" type="button">Financements</button></li>
        @endif
        @if($documents->count())
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-docs" type="button">Documents</button></li>
        @endif
    </ul>

    <div class="tab-content mt-3">
        {{-- Approbations --}}
        <div class="tab-pane fade show active" id="tab-approbations">
            <h5 class="mb-3">Circuit d'approbation</h5>
            {{-- Si tu as la relation $projet->approbations : décommente
            @foreach ($projet->approbations->sortBy('num_ordre') as $app)
                <div class="mb-3 border-start ps-3 @if($app->statut_validation_id == 2) border-success @elseif($app->statut_validation_id == 3) border-danger @endif">
                    <p><strong>Ordre {{ $app->num_ordre }} :</strong> {{ $app->approbateur?->acteur?->libelle_court }} {{ $app->approbateur?->acteur?->libelle_long }}</p>
                    <p class="mb-0">
                        <span class="badge 
                            @if($app->statut_validation_id == 2) bg-success
                            @elseif($app->statut_validation_id == 3) bg-danger
                            @else bg-secondary @endif">
                            {{ $app->statutValidation?->Libelle ?? $app->statutValidation?->libelle }}
                        </span>
                    </p>
                    @if($app->statut_validation_id == 3 && $app->commentaire_refus)
                        <p class="text-danger small mt-2"><strong>Motif :</strong> {{ $app->commentaire_refus }}</p>
                    @endif
                </div>
            @endforeach
            --}}
            <p class="text-muted">Le circuit d’approbation s’affichera ici.</p>
        </div>

        {{-- Infrastructures --}}
        <div class="tab-pane fade" id="tab-infra">
            <h5 class="mb-3">Infrastructures concernées</h5>
            <table class="table table-bordered table-sm">
                <thead class="table-light">
                <tr>
                    <th>Infrastructure</th>
                    <th>Caractéristique</th>
                    <th>Valeur</th>
                    <th>Unité</th>
                </tr>
                </thead>
                <tbody>
                @foreach($projetsInfra as $pi)
                    @php $infra = $pi->infra; @endphp
                    @foreach(($infra?->valeursCaracteristiques ?? []) as $val)
                        @php
                            $carac = $val->caracteristique;
                            $type  = strtolower($carac?->type?->libelleTypeCaracteristique ?? '');
                            switch ($type) {
                                case 'boolean': $affichage = ((string)$val->valeur === '1') ? 'Oui' : 'Non'; break;
                                case 'liste':   $affichage = $val->valeursPossibles?->valeur ?? $val->valeur; break;
                                case 'nombre':  $affichage = is_numeric($val->valeur) ? number_format($val->valeur, 2, ',', ' ') : $val->valeur; break;
                                default:        $affichage = $val->valeur; break;
                            }
                        @endphp
                        <tr>
                            <td>{{ $infra?->libelle ?? '—' }}</td>
                            <td>{{ $carac?->libelleCaracteristique ?? '—' }}</td>
                            <td>{{ $affichage }}</td>
                            <td>{{ $val->unite?->symbole ?? '' }}</td>
                        </tr>
                    @endforeach
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- Actions --}}
        <div class="tab-pane fade" id="tab-actions">
            <h5 class="mb-3">Actions à mener</h5>
            <table class="table table-bordered table-sm">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Action</th>
                        <th>Quantité</th>
                        <th>Infrastructure</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($actions as $act)
                        <tr>
                            <td>{{ $act->Num_ordre }}</td>
                            <td>{{ $act->actionMener?->libelle ?? $act->Action_mener }}</td>
                            <td>{{ $act->Quantite }}</td>
                            <td>{{ $act->Infrastrucrues_id }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Bénéficiaires --}}
        <div class="tab-pane fade" id="tab-beneficiaires">
            <h5 class="mb-3">Bénéficiaires du projet</h5>
            <table class="table table-bordered table-sm">
                <thead class="table-light">
                    <tr>
                        <th>Type</th>
                        <th>Libellé</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($benefActeurs as $b)
                        <tr>
                            <td>Acteur</td>
                            <td>{{ $b->acteur?->libelle_court ?? $b->acteur?->libelle_long ?? '—' }}</td>
                        </tr>
                    @empty @endforelse

                    @forelse($benefLocalites as $b)
                        <tr>
                            <td>Localité</td>
                            <td>{{ $b->code_rattachement ?? '—' }}</td>
                        </tr>
                    @empty @endforelse

                    @forelse($benefInfra as $b)
                        <tr>
                            <td>Infrastructure</td>
                            <td>{{ $b->infrastructure?->libelle ?? $b->code_Infrastructure ?? '—' }}</td>
                        </tr>
                    @empty @endforelse
                </tbody>
            </table>
        </div>

        {{-- Financements --}}
        <div class="tab-pane fade" id="tab-finance">
            <h5 class="mb-3">Financements</h5>
            @if($financements->count())
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>Bailleur</th>
                            <th class="text-end">Montant</th>
                            <th>Devise</th>
                            <th>Type financement</th>
                            <th>Local ?</th>
                            <th>Commentaire</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($financements as $f)
                            <tr>
                                <td>{{ $f->libelle_court ?: ($f->libelle_long ?: $f->code_acteur) }}</td>
                                <td class="text-end">{{ $fmtNum($f->montant_finance) }}</td>
                                <td>{{ $f->devise ?? '—' }}</td>
                                <td>{{ $f->FinancementType ?? '—' }}</td>
                                <td>
                                    <span class="badge {{ $f->financement_local ? 'bg-info' : 'bg-primary' }}">
                                        {{ $f->financement_local ? 'Local' : 'Externe' }}
                                    </span>
                                </td>
                                <td class="small">{{ $f->commentaire ?? '—' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted mb-0">Aucun financement renseigné.</p>
            @endif
        </div>

        {{-- Documents --}}
        <div class="tab-pane fade" id="tab-docs">
            <h5 class="mb-3">Documents joints</h5>
            <div class="row">
                @forelse($documents as $doc)
                    @php $url = $doc->file_path ? asset($doc->file_path) : null; @endphp
                    <div class="col-md-4 mb-3">
                        <div class="border rounded p-3 text-center h-100">
                            <i class="bi bi-file-earmark-text fs-1 text-primary"></i>
                            <p class="mt-2 mb-1 text-truncate">{{ $doc->file_name }}</p>
                            <p class="text-muted small mb-2">
                                {{ is_numeric($doc->file_size ?? null) ? number_format(($doc->file_size)/1024, 2, ',', ' ') . ' KB' : '—' }}
                            </p>
                            @if($url)
                                <a href="{{ $url }}" target="_blank" class="btn btn-sm btn-outline-primary">Ouvrir</a>
                            @else
                                <span class="badge bg-danger">Introuvable</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-muted">Aucun document joint.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        @if(session('success'))
            if (window.Swal) { Swal.fire({ icon: 'success', title: 'Succès', text: "{{ session('success') }}" }); }
        @elseif(session('error'))
            if (window.Swal) { Swal.fire({ icon: 'error', title: 'Erreur', text: "{{ session('error') }}" }); }
        @endif
    });
</script>