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
                    <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Projet </h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="">Gestion des exceptions</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Annuler projet</li>

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
    

<div class="container">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
        <div class="row match-height">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3>Annuler projet</h3>
                    </div>
                    <div class="card-body">

                        <form method="POST" action="{{ route('projets.annulation.store') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="code_projet">Projet à annuler *</label>
                                    <select name="code_projet" id="code_projet_annuler" class="form-control" required>
                                        <option value="">-- Sélectionnez un projet --</option>
                                        @foreach($projets as $projet)
                                            <option value="{{ $projet->code_projet }}">{{ $projet->code_projet }} </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div id="infoCard" class="col-md-9 card" style="display: none; border: none; height: 135px">                                
                                    <div class="card shadow-sm border-primary mb-4">
                                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center" style="height: 57px;">
                                            <div>
                                                 <small class="d-block">Nature : <strong><span id="nature"></span></strong></small>
                                            </div>
                                            <div class="">
                                                <small class="d-block"><strong><span id="libelle_projet"></span></strong></small>
                                            </div>
                                            <div>
                                                <small class="d-block"><strong style="width: 10px;">Domaine</strong>       : <strong><span id="domaine"></span></strong></small>
                                                <small class="d-block"><strong style="width: 10px;">Sous domaine</strong> : <strong><span id="sousDomaine"></span></strong></small>
                                            </div>
                                        </div>
                                        <br>
                                        <div class="card-body">
                                            <div class="row g-4">
                                                <div class="col-md-8">
                                                    <d  iv class="d-flex align-items-start mb-3">
                                                    <i class="bi bi-calendar-check me-3 fs-4 text-primary"></i>
                                                    <div>
                                                        <h6 class="mb-1 fw-bold text-muted">Période</h6>
                                                        <p class="mb-0">Du <span id="date_demarrage_prevue"></span>
                                                        
                                                        Au <span id="date_fin_prevue"></span> </p>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="d-flex align-items-start mb-3">
                                                        <i class="bi bi-cash-coin me-3 fs-4 text-primary"></i>
                                                        <div>
                                                            <h6 class="mb-1 fw-bold text-muted">Budget</h6>
                                                            <p class="mb-0"><span id="cout"></span> <span id="devise"></span></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>  
                                    </div>
                                </div>
                            <div class="row">
                                <div class="col-9">
                                    <label for="motif">Motif de l’annulation *</label>
                                    <textarea name="motif" class="form-control" rows="2" required placeholder="Expliquez la raison de l’annulation..."></textarea>
                                </div>
                                <div class="col text-end" style="top: 23px">
                                    <button type="submit" class="btn btn-danger  mt-3">Annuler le projet</button>
                                </div>
                            </div>
                            
                        </form>

                    </div>
                </div>
            </div>
        </div>
    <h5>📋 Projets annulés</h5>
    <table  class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="tableAnnules">
        <thead>
            <tr>
                <th>Code</th>
                <th>Libellé</th>
                <th>Date annulation</th>
                <th>Statut</th>

            </tr>
        </thead>
        <tbody>
            @foreach($projetsAnnules as $projet)
                <tr>
                    <td>{{ $projet->code_projet }}</td>
                    <td>{{ $projet->libelle_projet }}</td>
                    <td>{{ $projet->statuts->date_statut ?? '-' }}</td>
                    <td>{{ $projet->statuts->statut->libelle ?? '-' }}</td>

                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
    $(document).ready(function() {
        initDataTable('{{ auth()->user()->acteur?->libelle_court }} {{ auth()->user()->acteur?->libelle_long }}', 'tableAnnules', "Liste des projets annulés")
    });
    $(document).ready(function() {
        $('#tableAnnules').DataTable();
    });


    $('#code_projet_annuler').on('change', function () {
        const selectedProjet = $(this).val();
        if (!selectedProjet) return;

        fetch(`{{ url("/")}}/getProjetADeleted/${selectedProjet}`)
            .then(response => response.json())
            .then(data => {
                if (!data) {
                    // Réinitialise le formulaire
                    $('#dateDemarrage').val('');
                    $('#libelleProjet').val('');
                    $('#dateFin').val('');
                    return;
                }
                console.log(data);
                editMO(data); 
            })
            .catch(err => {
                console.error('Erreur chargement exécution:', err);
            });

    });
    function editMO(data) {
        $('#infoCard').show();
        $('#libelle_projet').text(data.libelle_projet || 'Projet');
        $('#date_demarrage_prevue').text(data.date_demarrage_prevue || '-'); 
        $('#date_fin_prevue').text(data.date_fin_prevue || '-') ;
        $('#devise').text(data.devise || '-') ;
        $('#localite').text(data.localite || '-') ;
        $('#maitreOuvrage').text(data.maitreOuvrage || '-') ;
        $('#maitreOeuvre').text(data.maitreOeuvre || '-') ;
        $('#cout').text(data.cout || '-') ;
        $('#sousDomaine').text(data.sousDomaine || '-') ;
        $('#domaine').text(data.domaine || '-') ;
        $('#nature').text(data.nature || '-') ;
        $('#devise').text(data.devise || '-') ;
    }
</script>
@endsection
