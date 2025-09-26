@extends('layouts.app')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.6.2/js/bootstrap.min.js"></script>

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
                            <li class="breadcrumb-item active" aria-current="page">Édition des projets</li>

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
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Édition des projets</h6>
            <div class="dropdown no-arrow">
                @can("consulter_ecran_" . $ecran->id)
                <button class="btn btn-primary dropdown-toggle" type="button" id="exportDropdown" 
                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-download fa-sm"></i> Exporter
                </button>
                @endcan
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" 
                     aria-labelledby="exportDropdown">
                    <form id="exportForm" action="{{ route('pdf.export.multiple') }}" method="POST">
                        @csrf
                        <input type="hidden" name="ecran_id" value="{{ $ecran->id }}">
                        <input type="hidden" name="type" id="exportType" value="projet">
                        <input type="hidden" name="projets" id="selectedProjects">
                        <button class="dropdown-item" type="button" onclick="prepareExport('projet')">
                            <i class="fas fa-file-pdf text-danger"></i> Fiches Projet
                        </button>
                        <button class="dropdown-item" type="button" onclick="prepareExport('contrat')">
                            <i class="fas fa-file-contract text-info"></i> Fiches Contrat
                        </button>
                        <button class="dropdown-item" type="button" onclick="prepareExport('acteur')">
                            <i class="fas fa-users text-warning"></i> Fiches Acteurs
                        </button>
                        <button class="dropdown-item" type="button" onclick="prepareExport('infrastructure')">
                            <i class="fas fa-building text-success"></i> Fiches Infrastructure
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered" id="projectsTables" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th width="40px">
                                <input type="checkbox" id="selectAll">
                            </th>
                            <th>Code Projet</th>
                            <th>Intitulé</th>
                            <th>Maître d'ouvrage</th>
                            <th>Date Début</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($projets as $projet)
                        <tr>
                            <td>
                                <input type="checkbox" class="project-checkbox" 
                                       value="{{ $projet->code_projet }}">
                            </td>
                            <td>{{ $projet->code_projet }}</td>
                            <td>{{ $projet->libelle_projet }}</td>
                            <td>{{ $projet->maitreOuvrage->acteur->libelle_court ?? 'Non défini' }}</td>
                            <td>{{ \Carbon\Carbon::parse($projet->date_demarrage_prevue)->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge badge-{{ $projet->statuts->statut->couleur ?? 'secondary' }}">
                                    {{ $projet->statuts->statut->libelle ?? 'Non défini' }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    @can("consulter_ecran_" . $ecran->id)
                                    <a href="{{ route('pdf.projet', $projet->code_projet) }}" 
                                       class="btn btn-primary" title="Exporter PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                    @endcan
                                    @can("consulter_ecran_" . $ecran->id)
                                    <a href="{{ route('projets.show', $projet->code_projet) }}" 
                                       class="btn btn-info" title="Voir détails">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        initDataTable('{{ auth()->user()->acteur?->libelle_court }} {{ auth()->user()->acteur?->libelle_long }}', 'projectsTables', 'Liste de projets')
    });

</script>
<script>
    // Sélection/désélection globale
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.project-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // Préparation de l'export
    function prepareExport(type) {
        const selectedProjects = Array.from(document.querySelectorAll('.project-checkbox:checked'))
                                    .map(checkbox => checkbox.value);
                                    console.log(selectedProjects);
        if (selectedProjects.length === 0) {
             alert("Veuillez sélectionner au moins un projet à exporter", "warning");

        }

        document.getElementById('exportType').value = type;
        document.getElementById('selectedProjects').value = JSON.stringify(selectedProjects);
        document.getElementById('exportForm').submit();
    }
</script>

@endsection