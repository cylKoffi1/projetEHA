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
                            <li class="breadcrumb-item active" aria-current="page">Reattribution de projet</li>

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
        <div class="row match-height">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3>Réattribution du Maître d’œuvre</h3>
                    </div>
                    <div class="card-body">
                        <form id="moForm" method="POST" action="{{ route('maitre_ouvrage.store') }}">
                            @csrf
                            <input type="hidden" name="_method" value="POST">
                            <input type="hidden" name="execution_id" id="execution_id">

                            <div class="row">
                                <div class="col-4">
                                    <label for="projet_id">Projet</label>
                                    <select name="projet_id" class="form-control" required>
                                        <option value="">-- Sélectionnez --</option>
                                        @foreach($projets as $projet)
                                            <option value="{{ $projet->code_projet }}">{{ $projet->code_projet }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- Affichage des infos du MO actuel -->
                                <div id="moeInfosCard" class="col-7 card " style="display: none; height: 49px; top: 12px; width: 65%; border: none">
                                   <div class="card-body">                                        
                                        <p><strong>Type :</strong> <span id="moe-type">  </span>
                                        <strong>  ||   Maître d'oeuvre actuel :</strong> <span id="moe-acteur">  <span id="moe-secteur"></span></span></p>                                      
                                    </div>
                                </div>


                               
                            </div>

                            

                            <!-- Sélection d’acteur -->
                            <div class="row mt-3">
                                <div class="col-4">
                                    <label>Type de Maître d’œuvre *</label><br>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input type_ouvrage" type="radio" name="type_ouvrage" id="moePublic" value="Public">
                                        <label class="form-check-label" for="moePublic">Public</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input type_ouvrage" type="radio" name="type_ouvrage" id="moePrive" value="Privé">
                                        <label class="form-check-label" for="moePrive">Privé</label>
                                    </div>
                                </div>
                                <!-- Options Privé -->
                                <div class="col-3 d-none" id="optionsMoePrive">
                                    <label>Type de Privé *</label><br>
                                    <div class="col form-check ">
                                        <input class="form-check-input type_prive" type="radio" name="priveMoeType" id="moeEntreprise" value="Entreprise">
                                        <label class="form-check-label" for="moeEntreprise">Entreprise</label>
                                    </div>
                                    <div class="col form-check">
                                        <input class="form-check-input type_prive" type="radio" name="priveMoeType" id="moeIndividu" value="Individu">
                                        <label class="form-check-label" for="moeIndividu">Individu</label>
                                    </div>
                                </div>
                                <div class="col">
                                    <label for="acteur_id">Nouveau maître d'oeuvre *</label>
                                    <select name="acteur_id" id="acteurMoeSelect" class="form-control" required>
                                        <option value="">Sélectionnez un acteur</option>
                                        @foreach($acteurs as $acteur)
                                            <option value="{{ $acteur->code_acteur }}">{{ $acteur->libelle_court }} {{ $acteur->libelle_long }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Entité qui assure le rôle de Maître d’œuvre</small>
                                </div>

                                <div class="col" id="secteurContainer" style="display: none;">
                                    <label for="secteur_id">Secteur d’activité</label>
                                    <select name="secteur_id" id="sectActivEntMoe" class="form-control">
                                        <option value="">Sélectionnez...</option>
                                        @foreach ($SecteurActivites as $secteur)
                                            <option value="{{ $secteur->code }}">{{ $secteur->libelle }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-9">
                                    <label for="motif">Motif *</label>
                                    <textarea name="motif" id="motif" class="form-control" rows="2" required placeholder="Expliquer la raison de la réattribution."></textarea>
                                </div>
                                <div class="col text-end">
                                    <button type="submit" class="btn btn-primary mt-3" id="formButton">Enregistrer</button>
                                </div>
                            </div>
                            
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <hr>

    <h5 class="mt-4">Maîtres d’œuvre existants</h5>
    <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="table1">
        <thead>
            <tr>
                <th>Code Projet</th>
                <th>Maître d’œuvre</th>
                <th>Type M.Œuvre</th>
                <th>Secteur</th>
                <th>Motif</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($executions as $execution)
                <tr>
                    <td>{{ $execution->code_projet }}</td>
                    <td>{{ $execution->acteur->libelle_court ?? '' }} {{ $execution->acteur->libelle_long ?? '' }}</td>

                    {{-- Type Maître d’œuvre --}}
                    <td>
                        @if(in_array($execution->acteur->type_acteur, ['eta', 'clt']))
                            <span class="badge bg-success">Public</span>
                        @else
                            <span class="badge bg-secondary">Privé</span>
                        @endif
                    </td>

                    <td>{{ $execution->secteur_id ?? '-' }}</td>
                    <td>{{ $execution->motif ?? '-' }}</td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="dropdownMenu{{ $execution->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                Actions
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenu{{ $execution->id }}">
                                <li>
                                    <button class="dropdown-item text-warning" type="button" onclick="editMO(@js([
                                        'id' => $execution->id,
                                        'code_projet' => $execution->code_projet,
                                        'code_acteur' => $execution->code_acteur,
                                        'secteur_id' => $execution->secteur_id,
                                        'motif' => $execution->motif,
                                        'acteur_type' => $execution->acteur->type_acteur
                                    ]))">
                                        <i class="bi bi-pencil-square"></i> Modifier
                                    </button>
                                </li>
                                <li>
                                    <button class="dropdown-item text-danger" type="button" onclick="deleteMO({{ $execution->id }})">
                                        <i class="bi bi-trash"></i> Supprimer
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>


</div>

<!-- SCRIPT -->
<script>
    $(document).ready(function() {
        initDataTable('{{ auth()->user()->acteur?->libelle_court }} {{ auth()->user()->acteur?->libelle_long }}', 'table1', "Liste des maitres d'oeuvre")
    });
    
    function fetchActeurs() {
        const acteurSelect = document.getElementById("acteurMoeSelect");
        const typeOuvrage = document.querySelector('input[name="type_ouvrage"]:checked')?.value;
        const priveType = document.querySelector('input[name="priveMoeType"]:checked')?.value;

        if (!typeOuvrage) return Promise.resolve();

        let url = `{{ url('/') }}/get-acteurs?type_ouvrage=${typeOuvrage}`;
        if (typeOuvrage === 'Privé' && priveType) {
            url += `&priveMoeType=${priveType}`;
        }

        return fetch(url)
            .then(response => response.json())
            .then(data => {
                acteurSelect.innerHTML = '<option value="">Sélectionnez un acteur</option>';
                data.forEach(acteur => {
                    const option = document.createElement('option');
                    option.value = acteur.code_acteur;
                    option.textContent = acteur.libelle_long;
                    acteurSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Erreur lors du chargement des acteurs :', error);
            });
    }

    $('select[name="projet_id"]').on('change', function () {
        const selectedProjet = $(this).val();
        if (!selectedProjet) return;

        fetch(`{{ url("/")}}/get-execution-by-projet/${selectedProjet}`)
            .then(response => response.json())
            .then(data => {
                if (!data) {
                    // Réinitialise le formulaire
                    $('#execution_id').val('');
                    $('#motif').val('');
                    $('#acteurMoeSelect').val('');
                    $('input[name="secteur_id"]').val('');
                    $('input[name="type_ouvrage"]').prop('checked', false);
                    $('input[name="priveMoeType"]').prop('checked', false);
                    $('#optionsMoePrive').addClass('d-none');
                    return;
                }
                console.log(data);
                editMO(data); 
            })
            .catch(err => {
                console.error('Erreur chargement exécution:', err);
            });

    });

    document.addEventListener("DOMContentLoaded", function () {
        const acteurSelect = document.getElementById("acteurMoeSelect");
        const secteurContainer = document.getElementById("secteurContainer");



        document.querySelectorAll('.type_ouvrage').forEach(input => {
            input.addEventListener('change', () => {
                document.getElementById('optionsMoePrive').classList.toggle('d-none', input.value !== 'Privé');
                fetchActeurs();
            });
        });

        document.querySelectorAll('.type_prive').forEach(input => {
            input.addEventListener('change', fetchActeurs);
        });

        acteurSelect.addEventListener('change', function () {
            secteurContainer.style.display = (this.value === '5689') ? 'block' : 'none';
        });
    });

    $('#moForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const url = form.attr('action');
        const method = form.find('input[name="_method"]').val() || 'POST';

        $.ajax({
            url: url,
            type: method,
            data: form.serialize(),
            success: function(response) {
                alert(response.success);
                location.reload();
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.error || 'Erreur serveur.');
            }
        });
    });
    function editMO(data) {
        $('#moeInfosCard').show();
        $('#moe-projet-code').text(data.code_projet);
        //$('#moe-acteur').text(data.code_acteur); 
        $('#moe-acteur').text(data.acteur_nom || data.code_acteur);
        $('#moe-type').text(['eta', 'clt'].includes(data.acteur_type) ? 'Public' : 'Privé');
        if(data.secteur_id){
            $('#moe-secteur').text(data.secteur_id || '-');
        };
        $('#moe-motif').text(data.motif || '-');

        // Réinitialiser le formulaire
        $('#moForm')[0].reset();
        $('#execution_id').val(data.id);

        // Réinitialiser visuellement
        $('#secteurContainer').hide();
        $('#optionsMoePrive').addClass('d-none');
        $('#moForm').attr('action', '{{ route("maitre_ouvrage.store") }}');
        $('input[name="_method"]').val('POST');
        $('#formButton').text('Enregistrer');
    }



    function deleteMO(id) {
        if (!confirm('Confirmer la suppression ?')) return;

        $.ajax({
            url: `{{ url('/')}}/reatributionProjet/${id}`,
            type: 'DELETE',
            data: {_token: '{{ csrf_token() }}'},
            success: function(response) {
                alert(response.success);
                location.reload();
            },
            error: function(xhr) {
                alert('Erreur lors de la suppression.');
            }
        });
    }
</script>
@endsection
