@extends('layouts.app')

@section('content')
<section class="section">
    <div class="page-heading">
        <h3><i class="bi bi-clock-history me-2"></i>Historique des statuts - {{ $infraProjet->infra?->libelle }}</h3>
    </div>

    <div class="card">
        <div class="card-header">
            Détails du projet : {{ $infraProjet->projet->libelle ?? $infraProjet->code_projet }}
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Statut</th>
                        <th>Description</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($infraProjet->statuts as $statut)
                        <tr>
                            <td><i class="bi bi-info-circle text-primary me-1"></i>{{ $statut->statut->libelle }}</td>
                            <td>{{ $statut->statut->description }}</td>
                            <td>{{ \Carbon\Carbon::parse($statut->date_statut)->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">Aucun statut enregistré pour ce projet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
