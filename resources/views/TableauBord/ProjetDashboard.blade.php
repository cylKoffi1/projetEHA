@extends('layouts.app')

@section('content')
<div class="container-fluid">

    {{-- ============================================================
         TITRE + NAVIGATION (optionnel selon ton layout)
    ============================================================ --}}
    <h4 class="mb-3">Tableau de bord — Projets</h4>


    {{-- ============================================================
         BARRE DE NAVIGATION ENTRE LES MODES
         - Nombre
         - Finance
         - Graphique (représentation)
    ============================================================ --}}
    <div class="btn-group mb-3" role="group">
        <a href="{{ route('stat.projet.dashboard', ['vue'=>'nombre','mode'=>'table']) }}"
           class="btn btn-sm {{ $typeVue=='nombre' && $mode=='table' ? 'btn-primary':'btn-outline-primary' }}">
            Nombre
        </a>

        <a href="{{ route('stat.projet.dashboard', ['vue'=>'finance','mode'=>'table']) }}"
           class="btn btn-sm {{ $typeVue=='finance' && $mode=='table' ? 'btn-primary':'btn-outline-primary' }}">
            Montants
        </a>

        <a href="{{ route('stat.projet.dashboard', ['vue'=>$typeVue,'mode'=>'graph']) }}"
           class="btn btn-sm {{ $mode=='graph' ? 'btn-primary':'btn-outline-primary' }}">
            Représentation graphique
        </a>
    </div>


    {{-- ============================================================
         MODE "GRAPH" = KPI + GRAPHIQUES
         (exactement comme dans ton ancien statProjet.blade.php)
    ============================================================ --}}
    @if($mode == 'graph')

        {{-- ==== KPIs ==== --}}
        <div class="row">
            <div class="col-md-3">
                <div class="card shadow-sm kpi-card" style="background: linear-gradient(to right, rgba(104,155,225,.9), #e7f1ff);">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Projets Totaux</h6>
                            <h6 class="mb-0">{{ number_format(array_sum($national),0,',',' ') }}</h6>
                        </div>
                        <i class="fas fa-layer-group fa-2x"></i>
                    </div>
                </div>
            </div>

            {{-- ---- Un KPI par statut ---- --}}
            @foreach($projectStatusCounts as $label => $value)
                <div class="col-md-3 mt-3">
                    <div class="card shadow-sm kpi-card" style="background: linear-gradient(to right, rgba(104,155,225,.9), #e7f1ff);">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">{{ $label }}</h6>
                                <h6 class="mb-0">{{ number_format($value,0,',',' ') }}</h6>
                            </div>
                            <i class="fas fa-chart-pie fa-2x"></i>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>


        {{-- ==== Graphiques globaux ==== --}}
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card shadow-sm p-3">
                    <h6 class="text-center">Répartition des Projets par Statut</h6>
                    <canvas id="projectsStatusChart"></canvas>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-sm p-3">
                    <h6 class="text-center">Répartition en nombre d'Acteurs</h6>
                    <canvas id="actorsChart"></canvas>
                </div>
            </div>
        </div>


        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card shadow-sm p-3">
                    <h6 class="text-center">Répartition par Financement</h6>
                    <canvas id="financementChart"></canvas>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-sm p-3">
                    <h6 class="text-center">Nombre de projets par Année</h6>
                    <canvas id="anneeChart"></canvas>
                </div>
            </div>
        </div>


        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card shadow-sm p-3">
                    <h6 class="text-center">Évolution du Budget Annuel (en milliards)</h6>
                    <canvas id="budgetChart"></canvas>
                </div>
            </div>
        </div>


    @else
    {{-- ============================================================
         MODE TABLEAU DYNAMIQUE (Nombre ou Finance)
         EXACTEMENT COMME DANS ÉTUDE DE PROJET
    ============================================================ --}}

    <div class="row">
        <div class="col-md-12">

            {{-- ======================= CARD PRINCIPALE ======================= --}}
            <div class="card shadow-sm">

                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        @if($typeVue=='nombre')
                            Nombre de projets par statut
                        @else
                            Montants par statut (CFA)
                        @endif
                    </h6>
                </div>

                <div class="card-body">

                    {{-- ##############################################################
                         FILTRES AVANCÉS (checkbox)
                         identiques à ÉtudeProjet
                    ############################################################## --}}
                    <div class="mb-3">

                        {{-- Filtre statuts --}}
                        <div class="d-flex flex-wrap gap-3 mb-3">
                            <strong class="me-2">Statuts :</strong>
                            @foreach($statusOrder as $st)
                                <label class="me-3">
                                    <input type="checkbox" class="filter-statut"
                                           data-stat="{{ $st }}" checked>
                                    {{ $statusTitles[$st] }}
                                </label>
                            @endforeach
                        </div>

                        {{-- Filtre public / privé --}}
                        <div class="d-flex flex-wrap gap-3 mb-3">
                            <strong class="me-2">Type :</strong>
                            <label><input type="checkbox" class="filter-segment" data-seg="public" checked> Public</label>
                            <label><input type="checkbox" class="filter-segment" data-seg="prive"  checked> Privé</label>
                        </div>

                        {{-- Filtre acteurs --}}
                        <div class="d-flex flex-wrap gap-3 mb-3">
                            <strong class="me-2">Mes rôles :</strong>
                            @if(in_array('chef_projet',$roles))
                                <label><input type="checkbox" class="filter-role" data-role="chef_projet" checked> Chef Projet</label>
                            @endif
                            @if(in_array('mo',$roles))
                                <label><input type="checkbox" class="filter-role" data-role="mo" checked> MO</label>
                            @endif
                            @if(in_array('moe',$roles))
                                <label><input type="checkbox" class="filter-role" data-role="moe" checked> MOE</label>
                            @endif
                            @if(in_array('bailleur',$roles))
                                <label><input type="checkbox" class="filter-role" data-role="bailleur" checked> Bailleur</label>
                            @endif
                        </div>

                    </div>


                    {{-- ##############################################################
                         TABLEAU COMPLET (avec colonnes dynamiques)
                         NATIONAL + ACTEURS + RATIO
                    ############################################################## --}}
                    <div class="table-responsive">
                        <table id="tbProjet" class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Ligne</th>

                                    @foreach($statusOrder as $st)
                                        <th class="col-{{ $st }}" style="min-width:130px; text-align:center">
                                            {{ $statusTitles[$st] }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>

                            <tbody>

                                {{-- #######################
                                     LIGNE NATIONAL
                                ######################## --}}
                                <tr class="row-national fw-bold">
                                    <td>National</td>

                                    @foreach($statusOrder as $st)
                                        @php
                                            $val = $national["total_$st"] ?? 0;
                                            $segment = $typeVue=='nombre' ? 'total' : 'total';
                                        @endphp

                                        <td class="text-center">
                                            <a href="{{ route('stat.projet.'.($typeVue=='nombre'?'nombre':'finance').'.data', [
                                                'statut'=>$st,
                                                'segment'=>'total',
                                                'role'=>null,
                                                'type'=>'national'
                                            ]) }}" class="text-decoration-none fw-bold">
                                                {{ number_format($val,0,',',' ') }}
                                            </a>
                                        </td>
                                    @endforeach
                                </tr>


                                {{-- #######################
                                     LIGNES ACTEURS
                                ######################## --}}
                                @foreach($roles as $role)
                                    @php
                                        $bag = $statsMoi[$role];
                                        $roleLabel = match($role) {
                                            'chef_projet'=>'Chef Projet',
                                            'mo'=>'Maître d’Ouvrage',
                                            'moe'=>'Maître d’Œuvre',
                                            'bailleur'=>'Bailleur',
                                            default=>'Acteur',
                                        };
                                    @endphp

                                    <tr class="row-role" data-role="{{ $role }}">
                                        <td>{{ $roleLabel }}</td>

                                        @foreach($statusOrder as $st)
                                            @php
                                                $val = $bag["total_$st"] ?? 0;
                                            @endphp
                                            <td class="text-center col-role-{{ $role }}">
                                                <a href="{{ route('stat.projet.'.($typeVue=='nombre'?'nombre':'finance').'.data', [
                                                    'statut'=>$st,
                                                    'segment'=>'total',
                                                    'role'=>$role,
                                                    'type'=>'personnel'
                                                ]) }}" class="text-decoration-none">
                                                    {{ number_format($val,0,',',' ') }}
                                                </a>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach


                                {{-- #######################
                                     LIGNE RATIO (%)
                                ######################## --}}
                                <tr class="table-info">
                                    <td>Ratio (%)</td>
                                    @foreach($statusOrder as $st)
                                        <td class="text-center">
                                            {{ $ratio["total_$st"] ?? 0 }} %
                                        </td>
                                    @endforeach
                                </tr>

                            </tbody>
                        </table>
                    </div>

                </div> {{-- card-body --}}
            </div> {{-- card --}}
        </div>
    </div>

    @endif {{-- end mode tableau / graph --}}

</div> {{-- container --}}



{{-- ============================================================== --}}
{{--                      SCRIPTS CHARTJS                         --}}
{{-- ============================================================== --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {

    // ===========================
    // Graph : Statuts
    // ===========================
    new Chart(document.getElementById('projectsStatusChart'), {
        type: 'pie',
        data: {
            labels: {!! json_encode(array_keys($projectStatusCounts)) !!},
            datasets: [{
                data: {!! json_encode(array_values($projectStatusCounts)) !!}
            }]
        }
    });

    // ===========================
    // Graph : Acteurs
    // ===========================
    new Chart(document.getElementById('actorsChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_keys($actorsCounts)) !!},
            datasets: [{
                data: {!! json_encode(array_values($actorsCounts)) !!}
            }]
        },
        options: { scales: { y: { beginAtZero:true } } }
    });

    // ===========================
    // Graph : Financement
    // ===========================
    new Chart(document.getElementById('financementChart'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($financements->pluck('libelle')->toArray()) !!},
            datasets: [{
                data: {!! json_encode($financements->pluck('total_projets')->toArray()) !!}
            }]
        }
    });

    // ===========================
    // Graph : Projets par année
    // ===========================
    new Chart(document.getElementById('anneeChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode(array_keys($projectsParAnnee)) !!},
            datasets: [{
                data: {!! json_encode(array_values($projectsParAnnee)) !!},
                fill:false,
                tension:0.3
            }]
        }
    });

    // ===========================
    // Graph : Budgets par année
    // ===========================
    new Chart(document.getElementById('budgetChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_keys($budgetsParAnnee)) !!},
            datasets: [{
                data: {!! json_encode(array_values($budgetsParAnnee)) !!}
            }]
        },
        options: { scales: { y:{ beginAtZero:true } } }
    });

});
</script>


{{-- ============================================================== --}}
{{--                      STYLES                                   --}}
{{-- ============================================================== --}}
<style>
    .kpi-card { transition: .2s; }
    .kpi-card:hover { transform: translateY(-5px); }
</style>

@endsection
