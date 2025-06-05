@extends('layouts.app')

@section('content')
<div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-sm-12">
                    <li class="breadcrumb-item" style="list-style: none; text-align: right; padding: 5px; font-family: Arial, Helvetica, sans-serif;"><span id="date-now" style="color: #34495E; font-family: Verdana, Geneva, Tahoma, sans-serif; margin-left: 15px;"></span></li>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Editions </h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="">Autres éditions</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Détails du projet</li>

                        </ol>
                    </nav>
                    <div class="row">
                        <script>
                            setInterval(function() {
                                document.getElementById('date-now').textContent = getCurrentDate();
                            }, 1000);

                            function getCurrentDate() {
                                // Implémentez la logique pour obtenir la date actuelle au format souhaité
                                var currentDate = new Date();
                                return currentDate.toLocaleString(); // Vous pouvez utiliser une autre méthode pour le formatage
                            }

                        </script>

                    </div>
                </div>
            </div>
        </div>
    </div>
<div class="container-fluid">
    <div class="card shadow mb-4 border-left-primary">
        <div class="card-header py-3 bg-primary text-white">
            <h4 class="m-0 font-weight-bold">Détails du Projet : {{ $projet->libelle_projet }}</h4>
        </div>

        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <p><strong>Code Projet :</strong> {{ $projet->code_projet }}</p>
                    <p><strong>Date Début :</strong> {{ \Carbon\Carbon::parse($projet->date_demarrage_prevue)->format('d/m/Y') }}</p>
                    <p><strong>Date Fin :</strong> {{ \Carbon\Carbon::parse($projet->date_fin_prevue)->format('d/m/Y') }}</p>
                    @php
                        use Carbon\Carbon;
                        $dateDebut = Carbon::parse($projet->date_demarrage_prevue);
                        $dateFin = Carbon::parse($projet->date_fin_prevue);
                    @endphp
                    <p><strong>Durée :</strong> {{ $dateDebut->diffInDays($dateFin) }} jours</p>
                    <p><strong>Statut :</strong> {{ $projet->statuts?->statut?->libelle ?? 'Non défini' }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Maître d’Ouvrage :</strong> {{ $projet->maitreOuvrage?->acteur?->libelle_court ?? '—' }}</p>
                    <p><strong>Budget :</strong> {{ number_format($projet->cout_projet, 0, ',', ' ') }} {{ $projet->code_devise }}</p>
                    <p><strong>Description :</strong> {{ $projet->commentaire ?? 'Aucune description' }}</p>
                </div>
            </div>

            <hr>

            <h5 class="text-primary">Maîtres d’œuvre</h5>
            @if($projet->maitresOeuvre->count())
                <ul>
                    @foreach($projet->maitresOeuvre as $moe)
                        <li>{{ $moe->acteur?->libelle_court }}</li>
                    @endforeach
                </ul>
            @else
                <p class="text-muted">Aucun maître d’œuvre enregistré.</p>
            @endif

            <h5 class="text-primary mt-4">Localisations</h5>
            @if($projet->localisations->count())
                <ul>
                    @foreach($projet->localisations as $loc)
                        <li>
                            {{ $loc->localite?->libelle ?? '—' }} - 
                            Niveau : {{ $loc->niveau }} / 
                            {{ $loc->localite?->decoupage?->libelle_decoupage ?? '—' }}
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-muted">Aucune localisation renseignée.</p>
            @endif

            <h5 class="text-primary mt-4">Financements</h5>
            @if($projet->financements->count())
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>Bailleur</th>
                            <th>Montant</th>
                            <th>Devise</th>
                            <th>Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($projet->financements as $f)
                        <tr>
                            <td>{{ $f->bailleur?->libelle_court ?? '—' }}</td>
                            <td>{{ number_format($f->montant_finance, 0, ',', ' ') }}</td>
                            <td>{{ $f->devise }}</td>
                            <td>{{ $f->financement_local ? 'Local' : 'Externe' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted">Aucun financement défini.</p>
            @endif

            <h5 class="text-primary mt-4">Infrastructures</h5>
            @if($projet->infrastructures->count())
                @foreach($projet->infrastructures as $infra)
                    <div class="mb-2">
                        <strong>{{ $infra->infra->libelle ?? '—' }}</strong>
                        <ul class="list-group list-group-flush">
                            @foreach($infra->valeursCaracteristiques as $carac)
                                <li class="list-group-item">
                                    {{ $carac->caracteristique->libelle ?? '—' }} : {{ $carac->valeur }} {{ $carac->unite?->symbole }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            @else
                <p class="text-muted">Aucune infrastructure renseignée.</p>
            @endif

            <h5 class="text-primary mt-4">Actions à Mener</h5>
            @if($projet->actions->count())
                <table class="table table-bordered table-sm">
                    <thead>
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
                                <td>{{ $act->Action_mener }}</td>
                                <td>{{ $act->Quantite }}</td>
                                <td>{{ $act->Unite }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted">Aucune action à mener renseignée.</p>
            @endif

            <h5 class="text-primary mt-4">Documents</h5>
            @if($projet->documents->count())
                <ul>
                    @foreach($projet->documents as $doc)
                        <li>
                            {{ $doc->file_name }} — 
                            <a href="{{ asset($doc->file_path) }}" target="_blank">Voir</a>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-muted">Aucun document joint.</p>
            @endif
        </div>
    </div>
</div>
@endsection
