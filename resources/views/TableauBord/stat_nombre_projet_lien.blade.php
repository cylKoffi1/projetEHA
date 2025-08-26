@extends('layouts.app')

@section('content')
@if (session('success'))
<script>
    $('#alertMessage').text("{{ session('success') }}");
    $('#alertModal').modal('show');
</script>
@endif

<style>
    .invalid-feedback { display:block; width:100%; margin-top:6px; font-size:80%; color:#dc3545; }
</style>

<section id="multiple-column-form">
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-sm-12">
                    <li class="breadcrumb-item" style="list-style:none; text-align:right; padding:5px;">
                        <span id="date-now" style="color:#34495E;"></span>
                    </li>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i> Tableau de bord</h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="">Nombre de projet</a></li>
                        </ol>
                    </nav>
                    <script>
                        setInterval(function() {
                            document.getElementById('date-now').textContent = new Date().toLocaleString();
                        }, 1000);
                    </script>
                </div>
            </div>
        </div>
    </div>

    <div class="row match-height">
        <div class="col-12">
            <div class="card">
                <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
                    <h5 class="card-title"></h5>
                    @if ($errors->any())
                        <div class="alert alert-danger mb-0">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div style="text-align:center; width:100%;">
                        <h5 class="card-title">Tableau de bord en nombre de projets</h5>
                    </div>
                </div>

                <div class="card-content">
                    <div class="card-body">
                        <table class="table table-striped table-bordered" cellspacing="0" style="width:100%" id="table1">
                            <thead>
                                <tr>
                                    <th style="width:10%">Code</th>
                                    <th style="width:12%">Statut</th>
                                    <th style="width:20%">Sous-domaine</th>
                                    <th style="width:25%">Localités</th>
                                    <th style="width:11%">Début prévue</th>
                                    <th style="width:11%">Fin prévue</th>
                                    <th style="width:9%">Coût</th>
                                    <th style="width:6%">Devise</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($statutsProjets as $projet)
                                    @php
                                        // Dernier statut libellé
                                        $statutLib = $projet->dernierStatut?->statut?->libelle ?? '—';

                                        // Sous-domaine libellé
                                        $sousDom = $projet->sousDomaine?->lib_sous_domaine ?? '—';

                                        // Liste des localités distinctes
                                        $localites = $projet->localisations
                                            ->map(fn($pl) => optional($pl->localite)->libelle)
                                            ->filter()
                                            ->unique()
                                            ->implode(', ');
                                        $localites = $localites !== '' ? $localites : '—';

                                        // Dates
                                        $dd = $projet->date_demarrage_prevue ? \Carbon\Carbon::parse($projet->date_demarrage_prevue)->format('d-m-Y') : '—';
                                        $df = $projet->date_fin_prevue ? \Carbon\Carbon::parse($projet->date_fin_prevue)->format('d-m-Y') : '—';

                                        // Coût
                                        $cout = is_null($projet->cout_projet) ? '—' : number_format((float)$projet->cout_projet, 0, ',', ' ');

                                        // Devise
                                        $devise = $projet->devise?->code_long ?? '';
                                    @endphp
                                    <tr>
                                        <td>{{ $projet->code_projet }}</td>
                                        <td>{{ $statutLib }}</td>
                                        <td>{{ $sousDom }}</td>
                                        <td>{{ $localites }}</td>
                                        <td>{{ $dd }}</td>
                                        <td>{{ $df }}</td>
                                        <td style="text-align:right">{{ $cout }}</td>
                                        <td>{{ $devise }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">Aucune donnée disponible.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

</section>

<script>
    $(document).ready(function() {
        initDataTable('{{ auth()->user()->acteur?->libelle_court }} {{ auth()->user()->acteur?->libelle_long }}', 'table1', 'Liste des nombres de projet')
    });

</script>
@endsection
