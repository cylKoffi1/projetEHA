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
                <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Gestion financiaire </h3>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Gestion financiaire</a></li>

                        <li class="breadcrumb-item active" aria-current="page">PIB</li>

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
<div class="row mt-3">
    <div class="col-md-12">
        <div class="card shadow-sm" style="background-color: rgba(250, 250, 250, 0.9);">
            <div class="card-body p-3">
                <h5 class="card-title">Représentation du PIB par année</h5>
            </div>
            <canvas id="pibChart" style="max-height: 200px;"></canvas>
        </div>
    </div>
</div>

<section>
    <div class="card">
        <div class="card-header">
            <div style="display: flex; width: 100%; justify-content: space-between; align-items: center;">

            <h5 class="card-title">
                    Ajout d'un pib
                    {{-- @can("ajouter_ecran_" . $ecran->id)--}}
                    <a  href="#" data-toggle="modal" data-target="#localite-modal" style="margin-left: 15px;"><i class="bi bi-plus-circle me-1"></i></a>
                    {{--@endcan--}}
                </h5>
                        <div class="card-title text-end">
                        @if (session('success'))
                            <div class="alert alert-success">
                                {{session('success')}}
                            </div>
                        @elseif (session('error'))
                            <div class="alert alert-danger">
                                {{session('error')}}
                            </div>
                        @endif
                        </div>
                @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>
            <div style="text-align: center;">
                <h5 class="card-title"> Liste des pibs</h5>
            </div>
        </div>
        <div class="card-body">
        <form class="form" method="POST" action="{{ route('pib.store') }}">
            @csrf
            <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}" name="ecran_id" required>
            <div class="row">
                <div class="col-md-4 col-12">
                    <label>Année :</label>
                    <input type="number" class="form-control" id="annee" name="annee" required>
                </div>
                <div class="col-md-4 col-12">
                    <label>Montant :</label>
                    <input type="number" class="form-control" name="montant" id="montant" required>
                </div>
                <div class="col-md-4 col-12">
                    <label>Devise :</label>
                    <select name="devise" id="devise" class="form-select">
                        @foreach ($devises as $devise)
                        <option value="{{ $devise->code }}">{{ $devise->code_long }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col text-end"><button type="submit" class="btn btn-primary mt-2 text-end">Enregistrer</button></div>

        </form>

        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="table1">
                <thead>
                    <tr>
                        {{--<th>Code</th>--}}
                        <th>Année</th>
                        <th>Montant</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($pibs as $pib)
                    <tr>
                        {{--<td>{{ $pib->code }}</td>--}}
                        <td class="col-2">{{ $pib->annee }}</td>
                        <td class="col-3 text-end">{{ number_format($pib->montant_pib, 0, ',', ' ') }}</td>
                        <td class="col-2">
                            <div class="dropdown">
                                <a href="#" class="btn btn-link dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown">
                                    <span style="color: white"></span>
                                </a>
                                <ul class="dropdown-menu z-3" aria-labelledby="userDropdown">
                                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#edit-modal-{{ $pib->code }}"><i class="bi bi-pencil-square me-3"></i> Modifier</a></li>
                                    <li><a class="dropdown-item" href="#"> <i class="bi bi-trash3-fill me-3"></i> Supprimer</a></li>

                                </ul>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>
<!-- Modal -->
<div class="modal fade" id="edit-modal-{{ $pib->code }}" tabindex="-1" style="background: transparent">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('pib.update', $pib->code) }}" >
            @csrf
            @method('PUT')
            <div class="modal-content" style="background: white">
                <div class="modal-header">
                    <h5 class="modal-title">Modifier PIB</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label>Année :</label>
                    <input type="number" name="annee" value="{{ $pib->annee }}" class="form-control" required>
                    <label>Montant :</label>
                    <input type="number" name="montant" value="{{ $pib->montant_pib }}" class="form-control" required>
                    <label>Devise :</label>
                    <select name="devise" class="form-select">
                        @foreach ($devises as $devise)
                        <option value="{{ $devise->code }}" {{ $devise->code == $pib->devise ? 'selected' : '' }}>{{ $devise->code_long }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </div>
        </form>
    </div>
</div>
<form method="POST" action="{{ route('pib.destroy', $pib->code) }}">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger">Supprimer</button>
</form>

<script>
    $(document).ready(function() {
        initDataTable('{{ auth()->user()->acteur?->libelle_court }} {{ auth()->user()->acteur?->libelle_long }}', 'table1', 'Liste des pibs')
    });

</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('pibChart').getContext('2d');

    // Données pour le graphique (transmises depuis le backend)
    const pibData = @json($pibs->map(function ($pib) {
        return ['année' => $pib->annee, 'montant' => $pib->montant_pib /1000000000];
    }));

    // Extraction des années et montants pour le graphique
    const labels = pibData.map(pib => pib.année);
    const data = pibData.map(pib => pib.montant);

    new Chart(ctx, {
        type: 'line', // Utilisez 'bar' ou 'line' selon vos besoins
        data: {
            labels: labels,
            datasets: [{
                label: 'PIB (en milliard de dollar)',
                data: data,
                backgroundColor: 'rgba(255, 255, 255, 0)',
                borderColor: 'rgba(104, 155, 225, 1)',
                borderWidth: 2,
                fill: true
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
@endsection
