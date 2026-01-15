{{-- resources/views/approbations/objects/ETUDE/creation.blade.php --}}
@extends('layouts.app')

@php
    /** @var \App\Models\EtudeProjet $record */
    use Illuminate\Support\Facades\DB;

    $etude = $record;

    // Libellés utiles
    $typeEtude = \App\Models\EtudeType::where('code', $etude->type_etude_code)->first();
    $sousDomaine = \App\Models\SousDomaine::where('code_sous_domaine', $etude->code_sous_domaine)->first();
    $domaine = $sousDomaine?->domaine ?? \App\Models\Domaine::where('code', $etude->code_domaine)->first();

    // Maître(s) d’ouvrage (table posseder)
    $maitresOuvrage = DB::table('posseder as p')
        ->leftJoin('acteur as a', 'a.code_acteur', '=', 'p.code_acteur')
        ->where('p.code_projet', $etude->code_projet_etude)
        ->selectRaw('p.*, a.libelle_court, a.libelle_long, a.type_acteur')
        ->get();

    // Maître(s) d’œuvre (table executer)
    $maitresOeuvre = DB::table('executer as e')
        ->leftJoin('acteur as a', 'a.code_acteur', '=', 'e.code_acteur')
        ->where('e.code_projet', $etude->code_projet_etude)
        ->selectRaw('e.*, a.libelle_court, a.libelle_long, a.type_acteur')
        ->get();

    // Financements (table financer)
    $financements = DB::table('financer as f')
        ->leftJoin('acteur as a', 'a.code_acteur', '=', 'f.code_acteur')
        ->where('f.code_projet', $etude->code_projet_etude)
        ->selectRaw('f.*, a.libelle_court, a.libelle_long')
        ->get();

    // Documents (table projet_documents)
    $documents = DB::table('projet_documents')
        ->where('code_projet', $etude->code_projet_etude)
        ->orderByDesc('uploaded_at')
        ->get();

    // Livrables (belongsToMany)
    $livrables = $etude->livrables()->orderBy('libelle')->get();

    // Contexte (session)
    $ctxPays   = session('pays_selectionne')    ?: $etude->code_pays ?: '—';
    $ctxGroupe = session('projet_selectionne')  ?: $etude->groupe_projet_code ?: '—';

    $fmtDate = fn($d) => $d ? \Carbon\Carbon::parse($d)->format('d/m/Y') : '—';
    $fmtNum  = fn($n) => is_numeric($n) ? number_format($n, 0, ',', ' ') : ($n ?? '—');
@endphp

@section('content')
<div class="container-fluid">

    {{-- Alerte simple en alert() si success/error en session (identique à ton template) --}}
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
                <small class="d-block"><strong>{{ $etude->code_projet_etude }}</strong></small>
                <small class="d-block">Nature : <strong>{{ $typeEtude?->libelle ?? '—' }}</strong></small>
            </div>

            <div>
                <small class="d-block"><strong style="width: 10px;">Domaine</strong>       :
                    <strong>{{ $domaine?->libelle ?? ($etude->code_domaine ?: '—') }}</strong>
                </small>
                <small class="d-block"><strong style="width: 10px;">Sous domaine</strong> :
                    <strong>{{ $sousDomaine?->lib_sous_domaine ?? ($etude->code_sous_domaine ?: '—') }}</strong>
                </small>
            </div>

        </div>

        <br>

        <div class="card-body">
            <div class="row g-4">

                {{-- Période --}}
                <div class="col-md-4">
                    <div class="d-flex align-items-start mb-3">
                        <i class="bi bi-calendar-check me-3 fs-4 text-primary"></i>
                        <div>
                            <h6 class="mb-1 fw-bold text-muted">Période</h6>
                            <p class="mb-0">
                                Du {{ $fmtDate($etude->date_debut_previsionnel) }}<br>
                                Au {{ $fmtDate($etude->date_fin_previsionnel) }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Budget --}}
                <div class="col-md-4">
                    <div class="d-flex align-items-start mb-3">
                        <i class="bi bi-cash-coin me-3 fs-4 text-primary"></i>
                        <div>
                            <h6 class="mb-1 fw-bold text-muted">Budget prévisionnel</h6>
                            <p class="mb-0">{{ $fmtNum($etude->montant_budget_previsionnel) }}</p>
                        </div>
                    </div>
                </div>

                {{-- Maître d’ouvrage (premier si plusieurs) --}}
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
                                <p class="mb-0">{{ $moaLabel }}</p>
                            @else
                                <p class="mb-0">—</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Maîtres d’œuvre --}}
                <div class="col-md-4">
                    @if($maitresOeuvre->count())
                    <div class="d-flex align-items-start mb-3">
                        <i class="bi bi-people me-3 fs-4 text-primary"></i>
                        <div>
                            <h6 class="mb-1 fw-bold text-muted">Maîtres d’œuvre</h6>
                            @foreach($maitresOeuvre as $moe)
                                <p class="mb-0">{{ $moe->libelle_court ?: ($moe->libelle_long ?: '—') }}</p>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Localisation : (optionnel — si tu as une table dédiée aux localisations d’étude, branche ici) --}}
                <div class="col-md-4">
                    <div class="d-flex align-items-start mb-3">
                        <i class="bi bi-geo-alt me-3 fs-4 text-primary"></i>
                        <div>
                            <h6 class="mb-1 fw-bold text-muted">Localisations</h6>
                            <p class="text-muted mb-0 small">Non renseignées pour l’étude.</p>
                        </div>
                    </div>
                </div>

                {{-- Description --}}
                <div class="col-md-4">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-info-circle me-3 fs-4 text-primary"></i>
                        <div>
                            <h6 class="mb-1 fw-bold text-muted">Description</h6>
                            <p class="mb-0">{{ $etude->description ?: 'Aucune description fournie.' }}</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Form refus --}}
    @if(($etude->valider ?? 0) == 0)
        <div class="collapse mt-3" id="refusFormTop">
            <form method="POST" action="{{ route('projets.validation.refuser', $etude->code_projet_etude) }}">
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

    {{-- ========== ONGLET SECTIONS ========== --}}
    <ul class="nav nav-tabs" id="tabsProjet" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-approbations" type="button" role="tab">Approbations</button></li>

        {{-- Ces trois onglets existent dans ton template "projet".
             Pour l’étude, on les masque par défaut (pas de données natives).
             Décommente si tu ajoutes les relations côté étude. --}}
        {{-- <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-infra" type="button" role="tab">Infrastructures</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-actions" type="button" role="tab">Actions</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-beneficiaires" type="button" role="tab">Bénéficiaires</button></li> --}}

        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-finance" type="button" role="tab">Financements</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-docs" type="button" role="tab">Documents</button></li>
    </ul>

    <div class="tab-content mt-3">
        {{-- Approbations --}}
        <div class="tab-pane fade show active" id="tab-approbations" role="tabpanel">
            <h5 class="mb-3">Circuit d'approbation</h5>
            {{-- Si tu as des approbations attachées à l’étude, boucle ici. Sinon, message informatif. --}}
            <p class="text-muted">Intégrer ici l’historique/circuit d’approbation de l’étude.</p>

            {{-- Exemple de rendu si tu as une collection $etude->approbations :
            @foreach ($etude->approbations->sortBy('num_ordre') as $app)
                <div class="mb-3 border-start ps-3 @if($app->statut_validation_id == 2) border-success @elseif($app->statut_validation_id == 3) border-danger @endif">
                    <p><strong>Ordre {{ $app->num_ordre }} :</strong> {{ $app->approbateur?->acteur?->libelle_court }} {{ $app->approbateur?->acteur?->libelle_long }}</p>
                    <p class="mb-0">
                        <span class="badge 
                            @if($app->statut_validation_id == 2) bg-success
                            @elseif($app->statut_validation_id == 3) bg-danger
                            @else bg-secondary @endif">
                            {{ $app->statutValidation?->libelle }}
                        </span>
                    </p>
                    @if($app->statut_validation_id == 3 && $app->commentaire_refus)
                        <p class="text-danger small mt-2"><strong>Motif :</strong> {{ $app->commentaire_refus }}</p>
                    @endif
                </div>
            @endforeach
            --}}
        </div>

        {{-- Infrastructures --}}
        {{-- <div class="tab-pane fade" id="tab-infra" role="tabpanel">
            <h5 class="mb-3">Infrastructures concernées</h5>
            <p class="text-muted">Aucune infrastructure rattachée à l’étude.</p>
        </div> --}}

        {{-- Actions --}}
        {{-- <div class="tab-pane fade" id="tab-actions" role="tabpanel">
            <h5 class="mb-3">Actions à mener</h5>
            <p class="text-muted">Aucune action renseignée pour l’étude.</p>
        </div> --}}

        {{-- Bénéficiaires --}}
        {{-- <div class="tab-pane fade" id="tab-beneficiaires" role="tabpanel">
            <h5 class="mb-3">Bénéficiaires</h5>
            <p class="text-muted">Aucun bénéficiaire renseigné pour l’étude.</p>
        </div> --}}

        {{-- Financements --}}
        <div class="tab-pane fade" id="tab-finance" role="tabpanel">
            <h5 class="mb-3">Financements</h5>
            @if($financements->count())
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Bailleur</th>
                                <th class="text-end">Montant</th>
                                <th>Devise</th>
                                <th>Type</th>
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
                <p class="text-muted">Aucun financement renseigné.</p>
            @endif
        </div>

        {{-- Documents --}}
        <div class="tab-pane fade" id="tab-docs" role="tabpanel">
            <h5 class="mb-3">Documents joints</h5>
            <div class="row">
                @forelse($documents as $doc)
                    @php $url = $doc->file_path ? asset($doc->file_path) : null; @endphp
                    <div class="col-md-4 mb-3">
                        <div class="border rounded p-3 text-center h-100">
                            <i class="bi bi-file-earmark-text fs-1 text-primary"></i>
                            <p class="mt-2 mb-1 text-truncate">{{ $doc->file_name }}</p>
                            <p class="text-muted small">
                                {{ is_numeric($doc->file_size ?? null) ? number_format(($doc->file_size)/1024, 2, ',', ' ') . ' KB' : '—' }}
                            </p>
                            @if($url)
                                <a href="{{ $url }}" target="_blank" class="btn btn-sm btn-outline-primary">Ouvrir</a>
                            @else
                                <span class="badge bg-danger">Fichier introuvable</span>
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

{{-- Si tu utilises SweetAlert comme dans ton template (optionnel) --}}
<script>
    document.addEventListener("DOMContentLoaded", function () {
        @if(session('success'))
            if (window.Swal) {
                Swal.fire({ icon: 'success', title: 'Succès', text: "{{ session('success') }}" });
            }
        @elseif(session('error'))
            if (window.Swal) {
                Swal.fire({ icon: 'error', title: 'Erreur', text: "{{ session('error') }}" });
            }
        @endif
    });
</script>
@endsection
