@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h3>Suspension d’un projet</h3>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('projets.suspension.store') }}">
                @csrf
                <div class="row">
                    <div class="col-md-3">
                        <label for="code_projet">Projet à suspendre *</label>
                        <select name="code_projet" id="code_projet_suspendre" class="form-control" required>
                            <option value="">-- Sélectionnez un projet --</option>
                            @foreach($projets as $projet)
                                <option value="{{ $projet->code_projet }}">{{ $projet->code_projet }}</option>
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
                </div>
                <div class="row">
                    <div class="col-9">
                        <label for="motif">Motif de la suspension *</label>
                        <textarea name="motif" class="form-control" rows="2" required placeholder="Expliquez la raison de la suspension..."></textarea>
                    </div>
                    <div class="col-3 text-end" style="top: 23px">
                        <button type="submit" class="btn btn-warning mt-3">Suspendre le projet</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <hr>

    <div class="card mt-4">
        <div class="card-header">
            <h5>📋 Projets suspendus</h5>
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%"  id="tableSuspendus">
                <thead>
                    <tr>

                        <th>Code</th>
                        <th class="col-3">Libellé</th>
                        <th class="col-1">Date suspension</th>
                        <th >Motif de suspension</th>
                        <th>Date redemarrage</th>
                        <th>Actions</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($projetsSuspendus as $projet)
                        <tr>quantite_prevue
                            <td>{{ $projet->code_projet }}</td>
                            <td class="col-3">{{ $projet->libelle_projet }}</td>
                            <td class="col-1"> 
                                @if( $projet->statuts->type_statut == 5 ) 
                                    {{ $projet->statuts->date_statut ?? '-' }}
                                @endif
                            </td>
                            <td >
                                @if($projet->dernierStatut->type_statut == 5) 
                                    {{ $projet->dernierStatut->motif ?? 'Aucun motif' }}
                                @endif                               
                            
                            </td>
                            <form method="POST" class="form-redemarrage"   action="{{ route('projets.redemarrer') }}">
                                 @csrf
                                 <input type="hidden" name="code_projet" value="{{ $projet->code_projet }}">
                                <td>
                                    @if($projet->dernierStatut->type_statut == 6) 
                                        {{ $projet->dernierStatut->date_statut ?? '-' }}
                                    @else 
                                        <input type="date" name="dateRedemarrage" id="dateRedemarrage" class="form-control">
                                    @endif
                                </td>
                                <td>
                                    @if( $projet->dernierStatut->type_statut == 5 ) 
                                        <button type="submit" class="btn btn-success" style="font-size: 12px;">Redemarer</button>
                                    @endif
                                </td>
                                <td> {{ $projet->dernierStatut->statut->libelle }}</td>
                               
                            </form>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>

    $(document).ready(function() {
        initDataTable('{{ auth()->user()->acteur?->libelle_court }} {{ auth()->user()->acteur?->libelle_long }}', 'tableSuspendus', "Liste des projets suspendus");
    });
    $('#code_projet_suspendre').on('change', function () {
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
<script>
$(document).ready(function () {
    $('.form-redemarrage').on('submit', function (e) {
        e.preventDefault(); // Empêche l'envoi normal

        const form = $(this);
        const url = form.attr('action');
        const formData = form.serialize();

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            success: function (res) {
                if (res.success) {
                    alert(res.success);
                    window.location.href = '{{ route("projets.suspension.form") }}';
                } else if (res.error) {
                    alert(res.error, 'warning');
                }
            },
            error: function (xhr) {
                const msg = xhr.responseJSON?.error || "Une erreur est survenue.";
                alert(msg, 'error');
            }
        });
    });
});
</script>

@endsection
