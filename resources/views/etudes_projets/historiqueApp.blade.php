@extends('layouts.app')

@section('content')

@if (session('success'))
<script>
    $('#alertMessage').text("{{ session('success') }}");
    $('#alertModal').modal('show');
</script>
@endif
<section id="multiple-column-form">
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-sm-12">
                    <li class="breadcrumb-item" style="list-style: none; text-align: right; padding: 5px; font-family: Arial, Helvetica, sans-serif;"><span id="date-now" style="color: #34495E; font-family: Verdana, Geneva, Tahoma, sans-serif; margin-left: 15px;"></span></li>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Etudes projets </h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="">Approbation</a></li>
                            <li class="breadcrumb-item active" aria-current="page">historique des approbations</li>
                        </ol>
                        <div class="row">
                            <script>
                                setInterval(function() {
                                    document.getElementById('date-now').textContent = getCurrentDate();
                                }, 1000);

                                function getCurrentDate() {
                                    var currentDate = new Date();
                                    return currentDate.toLocaleString();
                                }
                            </script>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="bi bi-clock-history me-2"></i>Historique des approbations</h5>
            
            </div>

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            <div class="card-content">
                <div class="col-12 mt-3">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Liste des projets approuvés ou refusés</h5>
                        </div>

                        <div class="card-body">
                            @if (session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif

                            @if (session('error'))
                                <div class="alert alert-danger">{{ session('error') }}</div>
                            @endif

                            <div class="table">
                                @can("consulter_ecran_" . $ecran->id)
                                <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="table1">
                                    <thead >
                                        <tr>
                                            <th>Projet</th>
                                            <th>Nature des travaux</th>
                                            <th>Approbateur</th>
                                            <th>Statut</th>
                                            <th>Date d'action</th>
                                            <th>Commentaire</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($approvalHistory as $approval)
                                            <tr>
                                                <td>{{ $approval->etude->projet->code_projet ?? '—' }}</td>
                                                <td>{{ $approval->etude?->projet?->projetNaturesTravaux?->natureTravaux?->libelle ?? '—' }}</td>
                                                <td>{{ $approval->approbateur->acteur->libelle_court ?? '—' }} {{ $approval->approbateur->acteur->libelle_long ?? '—' }}</td>
                                                <td>
                                                    <span class="badge 
                                                        @if($approval->statut_validation_id == 2) bg-success
                                                        @elseif($approval->statut_validation_id == 3) bg-danger
                                                        @else bg-secondary @endif">
                                                        {{ $approval->statutValidation->libelle ?? '—' }}
                                                    </span>
                                                </td>
                                                <td>{{ \Carbon\Carbon::parse($approval->approved_at)->format('d/m/Y H:i') }}</td>
                                                <td class="text-muted">{{ $approval->commentaire_refus ?? '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                             @endcan
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</section>

<script>
        $(document).ready(function() {
            initDataTable('{{ auth()->user()->acteur->libelle_court }} {{ auth()->user()->acteur->libelle_long }}', 'table1', 'Suivit des projets approuvés');
        });
</script>
@endsection
