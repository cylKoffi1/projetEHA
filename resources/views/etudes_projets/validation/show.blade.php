
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
                <small class="d-block"><strong>{{ $etude->projet->code_projet}}</strong></small>
                <small class="d-block">Nature : <strong>{{ $etude->projet->statuts?->statut->libelle }}</strong></small>
            </div>

            <div>
                <small class="d-block"><strong style="width: 10px;">Domaine</strong>       : <strong>{{ $etude->projet->sousDomaine?->Domaine?->libelle}}</strong></small>
                <small class="d-block"><strong style="width: 10px;">Sous domaine</strong> : <strong>{{ $etude->projet->sousDomaine?->lib_sous_domaine}}</strong></small>
            </div>

            @if($etude->valider == 0)
            <div class="d-flex align-items-center gap-3">
                <form method="POST" action="{{ route('projets.validation.valider', $etude->codeEtudeProjets) }}">
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
                    <d  iv class="d-flex align-items-start mb-3">
                    <i class="bi bi-calendar-check me-3 fs-4 text-primary"></i>
                    <div>
                        <h6 class="mb-1 fw-bold text-muted">Période</h6>
                        <p class="mb-0">Du {{ \Carbon\Carbon::parse($etude->projet->date_demarrage_prevue)->format('d/m/Y') }}
                        
                        Au {{ \Carbon\Carbon::parse($etude->projet->date_fin_prevue)->format('d/m/Y') }}</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-start mb-3">
                        <i class="bi bi-cash-coin me-3 fs-4 text-primary"></i>
                        <div>
                            <h6 class="mb-1 fw-bold text-muted">Budget</h6>
                            <p class="mb-0">{{ number_format($etude->projet->cout_projet, 0, ',', ' ') }} {{ $etude->projet->code_devise }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-start mb-3">
                        <i class="bi bi-building me-3 fs-4 text-primary"></i>
                        <div>
                            <h6 class="mb-1 fw-bold text-muted">Maître d’ouvrage</h6>
                            <p class="mb-0">{{ $etude->projet->maitreOuvrage?->acteur?->libelle_court ?? '—' }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    @if($etude->projet->maitresOeuvre->count())
                    <div class="d-flex align-items-start mb-3">
                        <i class="bi bi-people me-3 fs-4 text-primary"></i>
                        <div>
                            <h6 class="mb-1 fw-bold text-muted">Maîtres d’œuvre</h6>
                            @foreach($etude->projet->maitresOeuvre as $moe)
                                <p class="mb-0">{{ $moe->acteur->libelle_court ?? '—' }}</p>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-start mb-3">
                        <i class="bi bi-geo-alt me-3 fs-4 text-primary"></i>
                        <div>
                            <h6 class="mb-1 fw-bold text-muted">Localisations</h6>
                            @forelse($etude->projet->localisations as $loc)
                                <p class="mb-1 small">
                                    {{ $loc->localite->libelle ?? '—' }} -
                                    Niveau : {{ $loc->niveau ?? 'Niveau ?' }} /
                                    {{ $loc->localite->decoupage->libelle_decoupage ?? 'Découpage ?' }}
                                </p>
                            @empty
                                <p class="text-muted mb-0 small">Aucune localisation définie.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-info-circle me-3 fs-4 text-primary"></i>
                        <div>
                            <h6 class="mb-1 fw-bold text-muted">Description</h6>
                            <p class="mb-0">{{ $etude?->projet?->commentaire ?: 'Aucune description fournie.' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>  
    </div>
    @if($etude->valider == 0)
        <div class="collapse mt-3" id="refusFormTop">
            <form method="POST" action="{{ route('projets.validation.refuser', $etude->codeEtudeProjets) }}">
                @csrf
                <div class="card card-body bg-light">
                    <h6 class="mb-2">Motif du refus</h6>
                    <textarea name="commentaire_refus" rows="3" class="form-control mb-2"  placeholder="Indiquez pourquoi vous refusez ce projet..."></textarea>
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
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-approbations">Approbations</button></li>
        @if($etude->projet->infrastructures->count())
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-infra">Infrastructures</button></li>
        @endif
        @if($etude->projet->actions->count())
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-actions">Actions</button></li>
        @endif
        @if($etude->projet->financements->count())
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-finance">Financements</button></li>
        @endif
        @if($etude->projet->documents->count())
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-docs">Documents</button></li>
        @endif
    </ul>

    <div class="tab-content mt-3">
        {{-- Approbations --}}
        <div class="tab-pane fade show active" id="tab-approbations">
            <h5 class="mb-3">Circuit d'approbation</h5>
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
        </div>

        {{-- Infrastructures --}}
        <div class="tab-pane fade" id="tab-infra">
            <h5 class="mb-3">Infrastructures concernées</h5>
            @foreach($etude->projet->infrastructures as $infra)
                <div class="mb-3">
                    <strong>{{ $infra->libelle }}</strong>
                    <ul class="list-group list-group-flush">
                        @foreach($infra->valeursCaracteristiques as $val)
                            <li class="list-group-item d-flex justify-content-between">
                                {{ $val->caracteristique?->libelle }} : {{ $val->valeur }} {{ $val->idUnite }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
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
                        <th>Unité</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($etude->projet->actions as $act)
                        <tr>
                            <td>{{ $act->Num_ordre }}</td>
                            <td>{{ $act->Action_mener }}</td>
                            <td>{{ $act->Quantite }}</td>
                            <td>{{ $act->Unite }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Financements --}}
        <div class="tab-pane fade" id="tab-finance">
            <h5 class="mb-3">Financements</h5>
            <table class="table table-sm">
                <thead class="table-light">
                    <tr>
                        <th>Bailleur</th>
                        <th>Montant</th>
                        <th>Type</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($etude->projet->financements as $f)
                        <tr>
                            <td>{{ $f->bailleur?->libelle_court }} {{ $f->bailleur?->libelle_long }}</td>
                            <td>{{ number_format($f->montant_finance, 0, ',', ' ') }} {{ $f->devise }}</td>
                            <td>
                                <span class="badge {{ $f->financement_local ? 'bg-info' : 'bg-primary' }}">
                                    {{ $f->financement_local ? 'Local' : 'Externe' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Documents --}}
        <div class="tab-pane fade" id="tab-docs">
            <h5 class="mb-3">Documents joints</h5>
            <div class="row">
                @foreach($etude->projet->documents as $doc)
                <div class="col-md-4 mb-3">
                    <div class="border rounded p-3 text-center">
                        <i class="bi bi-file-earmark-text fs-1 text-primary"></i>
                        <p class="mt-2 mb-1 text-truncate">{{ $doc->file_name }}</p>
                        @php $path = public_path($doc->file_path); @endphp
                        @if(file_exists($path))
                            <p class="text-muted small">{{ round(filesize($path)/1024, 2) }} KB</p>
                            <a href="{{ asset($doc->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">Télécharger</a>
                        @else
                            <p class="text-danger small">Fichier introuvable</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Succès',
                text: "{{ session('success') }}",
            });
        @elseif(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: "{{ session('error') }}",
            });
        @endif
    });
</script>