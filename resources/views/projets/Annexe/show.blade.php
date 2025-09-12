@extends('layouts.app')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-12">
                <li class="breadcrumb-item" style="list-style: none; text-align: right; padding: 5px; font-family: Arial, Helvetica, sans-serif;">
                    <span id="date-now" style="color: #34495E; font-family: Verdana, Geneva, Tahoma, sans-serif; margin-left: 15px;"></span>
                </li>
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Fiche du Projet</h3>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="">Autres éditions</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Détails du projet</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <!-- En-tête du projet -->
    <div class="card project-header-card mb-4">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="project-title">{{ $projet->libelle_projet }}</h1>
                    <p class="project-code">{{ $projet->code_projet }}</p>
                    <div class="status-badge">
                        <span class="badge bg-danger">{{ $projet->statuts?->statut?->libelle ?? 'Non défini' }}</span>
                    </div>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="project-budget" >
                        <h3 style="color: #fff;">{{ number_format($projet->cout_projet, 0, ',', ' ') }} {{ $projet->code_devise }}</h3>
                        <p style="color: #fff;">Budget total</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Colonne principale -->
        <div class="col-lg-8">
            <!-- Informations générales -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i>Informations Générales</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-item">
                                <label>Maître d'Ouvrage</label>
                                <p class="mb-0">
                                    {{ $projet->maitreOuvrage?->acteur?->libelle_court ?? '—' }} {{ $projet->maitreOuvrage?->acteur?->libelle_long ?? '—' }}
                                    @if($projet->maitreOuvrage?->secteur?->libelle)
                                    <small class="text-muted"> ({{ $projet->maitreOuvrage->secteur->libelle }})</small>
                                    @endif
                                </p>
                            </div>

                           
                            <div class="info-item">
                                <label>Date de début</label>
                                <p>{{ \Carbon\Carbon::parse($projet->date_demarrage_prevue)->format('d/m/Y') }}</p>
                            </div>
                            @php
                                use Carbon\Carbon;
                                $dateDebut = Carbon::parse($projet->date_demarrage_prevue);
                                $dateFin = Carbon::parse($projet->date_fin_prevue);
                            @endphp
                            <div class="info-item">
                                <label>Durée</label>
                                <p>{{ $dateDebut->diffInDays($dateFin) }} jours</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                               <div class="info-item">
                                    <label>Maîtres d'œuvre</label>

                                    @if($projet->maitresOeuvre->count())
                                        <ul class="list-unstyled mb-0">
                                        @foreach($projet->maitresOeuvre as $moe)
                                            @php
                                            // Secteur depuis la ligne d’exécution
                                            $secteurLib = $moe->secteurActivite?->libelle;

                                            // Fallback éventuel via l’acteur (si tu veux couvrir ce cas)
                                            if (!$secteurLib && $moe->relationLoaded('acteur') && $moe->acteur) {
                                                // si tu veux aller chercher le premier secteur lié à l’acteur :
                                                $first = $moe->acteur->secteurActiviteActeur->first() ?? null;
                                                $secteurLib = $first?->secteur?->libelle;
                                            }
                                            @endphp

                                            <li>
                                            {{ $moe->acteur?->libelle_court ?? '—' }} {{ $moe->acteur?->libelle_long ?? '—' }}
                                            @if($secteurLib)
                                                <small class="text-muted"> ({{ $secteurLib }})</small>
                                            @endif
                                            </li>
                                        @endforeach
                                        </ul>
                                    @else
                                        <p>—</p>
                                    @endif
                                </div>

                            </div>
                            <div class="info-item">
                                <label>Date de fin prévue</label>
                                <p>{{ \Carbon\Carbon::parse($projet->date_fin_prevue)->format('d/m/Y') }}</p>
                            </div>
                            <div class="info-item">
                                <label>Description</label>
                                <p class="text-justify">{{ $projet->commentaire ?? 'Aucune description' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Localisations -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0"><i class="bi bi-geo-alt me-2"></i>Localisations</h5>
                </div>
                <div class="card-body">
                    @if($projet->localisations->count())
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Localité</th>
                                        <th>Niveau</th>
                                        <th>Découpage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($projet->localisations as $loc)
                                        <tr>
                                            <td>{{ $loc->localite?->libelle ?? '—' }}</td>
                                            <td>{{ $loc->niveau }}</td>
                                            <td>{{ $loc->localite?->decoupage?->libelle_decoupage ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">Aucune localisation renseignée.</p>
                    @endif
                </div>
            </div>

            <!-- Infrastructures -->
            <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0"><i class="bi bi-wrench me-2"></i>Infrastructures</h5>
            </div>
            <div class="card-body">
                @if($projet->infrastructures->count())
                <div class="accordion" id="infraAccordion">
                    @foreach($projet->infrastructures as $pi)
                    @php
                        // Identifiants uniques et stables
                        $uid = 'infra-'.($pi->id ?? $loop->index);
                        $collapseId = $uid.'-collapse';
                        // Toujours une Collection (évite erreurs)
                        $caracs = collect($pi->infra?->valeursCaracteristiques);
                    @endphp

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="{{ $uid }}-header">
                        <button class="accordion-button collapsed"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#{{ $collapseId }}"
                                aria-expanded="false"
                                aria-controls="{{ $collapseId }}">
                            {{ $pi->infra?->libelle ?? '—' }}
                        </button>
                        </h2>

                        <div id="{{ $collapseId }}"
                            class="accordion-collapse collapse"
                            aria-labelledby="{{ $uid }}-header"
                            data-bs-parent="#infraAccordion">
                        <div class="accordion-body overflow-auto" style="max-height: 320px;">
                        @php
                        $caracs = collect($pi->infra?->valeursCaracteristiques);
                        @endphp

                        @if($caracs->count())
                        <dl class="char-grid">
                            @foreach($caracs as $carac)
                            <dt>{{ $carac->caracteristique->libelleCaracteristique ?? '—' }}</dt>
                            <dd>{{ $carac->valeur }} {{ $carac->unite?->symbole }}</dd>
                            @endforeach
                        </dl>
                        @else
                        <p class="text-muted mb-0">Aucune caractéristique technique enregistrée.</p>
                        @endif

                        </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-muted">Aucune infrastructure renseignée.</p>
                @endif
            </div>
            </div>


            <!-- Actions à Mener -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0"><i class="bi bi-list-task me-2"></i>Actions à Mener</h5>
                </div>
                <div class="card-body">
                    @if($projet->actions->count())
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Action</th>
                                        <th>Quantité</th>
                                        <th>Unité</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($projet->actions as $act)
                                        <tr>
                                            <td>{{ $act->Num_ordre }}</td>
                                            <td>{{ $act->actionMener?->libelle }}</td>
                                            <td>{{ $act->Quantite }}</td>
                                            <td>{{ $act->Unite }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">Aucune action à mener renseignée.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Colonne latérale -->
        <div class="col-lg-4">
            <!-- Financements -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0"><i class="bi bi-currency-exchange me-2"></i>Financements</h5>
                </div>
                <div class="card-body">
                    @if($projet->financements->count())
                        <div class="financement-list">
                            @foreach($projet->financements as $f)
                                <div class="financement-item mb-3 p-3 border rounded">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-1">{{ $f->bailleur?->libelle_court ?? '—' }}</h6>
                                        <span class="badge bg-{{ $f->financement_local ? 'info' : 'warning' }}">
                                            {{ $f->financement_local ? 'Local' : 'Externe' }}
                                        </span>
                                    </div>
                                    <div class="mt-2">
                                        <h5 class="text-primary">{{ number_format($f->montant_finance, 0, ',', ' ') }} {{ $f->devise }}</h5>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">Aucun financement défini.</p>
                    @endif
                </div>
            </div>

            <!-- Documents -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0"><i class="bi bi-folder me-2"></i>Documents</h5>
                </div>
                <div class="card-body">
                    @if($projet->documents->count())
                        <div class="document-list">
                            @foreach($projet->documents as $doc)
                                <div class="document-item d-flex justify-content-between align-items-center p-2 border-bottom">
                                    <div class="document-info">
                                        <i class="bi bi-file-earmark-text me-2"></i>
                                        <span class="document-name">{{ $doc->file_name }}</span>
                                    </div>
                                    <a href="{{ asset($doc->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-download"></i>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">Aucun document joint.</p>
                    @endif
                </div>
            </div>

            <!-- Métadonnées -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0"><i class="bi bi-info-square me-2"></i>Métadonnées</h5>
                </div>
                <div class="card-body">
                    <div class="metadata-item">
                        <small class="text-muted">Créé le: {{ $projet->created_at->format('d/m/Y H:i') }}</small>
                    </div>
                    <div class="metadata-item">
                        <small class="text-muted">Modifié le: {{ $projet->updated_at->format('d/m/Y H:i') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .project-header-card {
        background: linear-gradient(135deg, #4b6cb7 0%, #182848 100%);
        color: white;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .project-title {
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 0.2rem;
    }
    
    .project-code {
        font-size: 1rem;
        opacity: 0.9;
        margin-bottom: 1rem;
    }
    
    .project-budget h3 {
        font-weight: 700;
        margin-bottom: 0;
    }
    
    .project-budget p {
        margin-bottom: 0;
        opacity: 0.8;
    }
    
    .info-item {
        margin-bottom: 1.5rem;
    }
    
    .info-item label {
        font-weight: 600;
        color: #6c757d;
        margin-bottom: 0.3rem;
        display: block;
    }
    
    .chips-container {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .chip {
        background-color: #e9ecef;
        padding: 0.35rem 0.75rem;
        border-radius: 16px;
        font-size: 0.9rem;
    }
    
    .financement-item {
        transition: all 0.3s ease;
    }
    
    .financement-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .document-item {
        transition: background-color 0.2s ease;
    }
    
    .document-item:hover {
        background-color: #f8f9fa;
    }
    
    .accordion-button:not(.collapsed) {
        background-color: #f8f9fa;
        color: #495057;
        font-weight: 600;
    }
    
    .card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }
    
    .card-header {
        border-radius: 10px 10px 0 0 !important;
        border-bottom: 1px solid #e3e6f0;
    }
    
    .metadata-item {
        margin-bottom: 0.5rem;
    }
    .char-grid{
  display:grid;
  /* 1 colonne libellé + 1 colonne valeur (en mobile) */
  grid-template-columns:max-content 1fr;
  column-gap:1rem;
  row-gap:.35rem;
  align-items:baseline;
  color:#000;
}
@media (min-width:768px){
  /* 2 paires par ligne en desktop */
  .char-grid{ grid-template-columns:max-content 1fr max-content 1fr; }
}
.char-grid dt{
  font-weight:600;
  margin:0;
  position:relative;
  white-space:nowrap;
}
.char-grid dt::after{
  content:":";
  margin-left:.25rem;
}
.char-grid dd{
  margin:0;
  word-break:break-word;
}

</style>

<script>
    setInterval(function() {
        document.getElementById('date-now').textContent = getCurrentDate();
    }, 1000);

    function getCurrentDate() {
        var currentDate = new Date();
        return currentDate.toLocaleString();
    }
</script>
@endsection