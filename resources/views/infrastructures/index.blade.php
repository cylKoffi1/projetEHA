@extends('layouts.app')

@section('content')
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
                    <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Gestion des infrastructures </h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#">Infrastructures</a></li>

                            <li class="breadcrumb-item active" aria-current="page">Nouvelle infrastructure</li>

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
    
    <div class="row match-height">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h4 class="card-title">Liste des Infrastructures</h4>
                        <a href="{{ route('infrastructures.create') }}" class="btn btn-primary">+ Nouvelle Infrastructure</a>
                    </div>
                    <div class="card-body">
                    <form id="filter-form" class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label>Domaine</label>
                            <select id="filter-domaine" class="form-control">
                                <option value="">Tous</option>
                                @foreach ($domaines as $domaine)
                                    <option value="{{ $domaine->code }}">{{ $domaine->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Famille</label>
                            <select id="filter-famille" class="form-control">
                                <option value="">Toutes</option>
                                @foreach ($familles as $famille)
                                    <option value="{{ $famille->code_famille }}">{{ $famille->libelleFamille }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Niveau</label>
                            <select id="filter-niveau" class="form-control">
                                <option value="">Tous</option>
                                @foreach ($niveaux as $idNiveau => $libelle)
                                    <option value="{{ $idNiveau }}">{{ $libelle }}</option>
                                @endforeach

                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end justify-content-between">
                            <button type="button" class="btn btn-primary" onclick="applyFilters()">Filtrer</button>
                            <form method="GET" action="{{ route('infrastructures.imprimer') }}" target="_blank" id="print-form">
                                <input type="hidden" name="domaine" id="print-domaine">
                                <input type="hidden" name="famille" id="print-famille">
                                <input type="hidden" name="niveau" id="print-niveau">
                                <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-printer"></i> Imprimer</button>
                            </form>
                        </div>
                    </form>

                </div>
                <div class="card-content">
                    <div class="card-body">
                        @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                        @endif
                        <script>
                            const familleToDomaine = @json($mappingFamilleDomaine);
                        </script>

                        <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="table1">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Nom</th>
                                    <th>Famille</th>
                                    <th>Localisation</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($infrastructures as $infra)
                                <tr 
                                    data-famille="{{ $infra->code_famille_infrastructure }}"
                                    data-domaine="{{ $mappingFamilleDomaine[$infra->code_famille_infrastructure] ?? '' }}"
                                    data-niveau="{{ $infra->localisation->id_niveau ?? '' }}">

                                    <td>{{ $infra->code }}</td>
                                    <td>{{ $infra->libelle }}</td>
                                    <td>{{ $infra->familleInfrastructure->libelleFamille ?? '' }}</td>
                                    <td>{{ $infra->localisation->libelle ?? '' }}</td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('infrastructures.show', $infra->id) }}">
                                                        <i class="bi bi-eye me-1 text-primary"></i> Caractéristiques
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('infrastructures.historique', $infra->id) }}">
                                                        <i class="bi bi-clock-history me-1"></i> Historique
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('infrastructures.edit', $infra->id) }}">
                                                        <i class="bi bi-pencil-square me-1 text-warning"></i> Modifier
                                                    </a>
                                                </li>
                                                <li>
                                                    <form action="{{ route('infrastructures.destroy', $infra->id) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr ?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            <i class="bi bi-trash3 me-1"></i> Supprimer
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
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
    </div>
</section>

<script>
 $(document).ready(function() {
        initDataTable('{{ auth()->user()->acteur?->libelle_court }} {{ auth()->user()->acteur?->libelle_long }}', 'table1', 'Liste des infrastructures')
    });
    function applyFilters() {
    const domaine = $('#filter-domaine').val();
    const famille = $('#filter-famille').val();
    const niveau = $('#filter-niveau').val();

    // Mettre à jour les champs du formulaire d'impression
    $('#print-domaine').val(domaine);
    $('#print-famille').val(famille);
    $('#print-niveau').val(niveau);

    // Filtrage des lignes du tableau
    $('#table1 tbody tr').hide().filter(function () {
        const rowDomaine = $(this).data('domaine');
        const rowFamille = $(this).data('famille');
        const rowNiveau = $(this).data('niveau');

        const matchDomaine = !domaine || rowDomaine == domaine;
        const matchFamille = !famille || rowFamille == famille;
        const matchNiveau = !niveau || rowNiveau == niveau;

        return matchDomaine && matchFamille && matchNiveau;
    }).show();
}


$('#filter-domaine').on('change', function () {
    const domaineCode = $(this).val();
    const familleSelect = $('#filter-famille');

    familleSelect.empty().append('<option value="">Toutes</option>');

    if (!domaineCode) return;

    $.get(`{{ url('/') }}/familles/${domaineCode}`, function (data) {
        data.forEach(famille => {
            familleSelect.append(`<option value="${famille.code_famille}">${famille.libelleFamille}</option>`);
        });
    });
});


</script>
@endsection