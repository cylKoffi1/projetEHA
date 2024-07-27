@extends('layouts.app')

@section('content')
@if (session('success'))
<script>
    $('#alertMessage').text("{{ session('success') }}");
    $('#alertModal').modal('show');
</script>
@endif

<style>
    .invalid-feedback {
        display: block;
        width: 100%;
        margin-top: 6px;
        font-size: 80%;
        color: #dc3545;
        /* Couleur du texte pour les messages d'erreur */
    }
</style>

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
                    <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Plateforme </h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="">Paramètre généraux</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Approbateur</li>
                        </ol>
                    </nav>
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
                </div>
            </div>
        </div>
    </div>

    <div class="modal-content">
        <div class="modal-body">
            <section id="multiple-column-form">
                <div class="row match-height">
                    <div class="col-12">
                        <div class="card">
                            <div id="form-enregistrements">
                                <div class="card-header">
                                    <div style="display: flex; width: 100%; justify-content: space-between; align-items: center;">
                                        <h5 class="card-title">
                                            Enregistrement des approbateurs
                                        </h5>
                                        @if(session('error'))
                                        <script>
                                            document.addEventListener('DOMContentLoaded', function() {
                                                $('#alertMessage').text("{{ session('error') }}");
                                                $('#alertModal').modal('show');
                                            });
                                        </script>
                                        @endif
                                        @if(session('success'))
                                        <script>
                                            document.addEventListener('DOMContentLoaded', function() {
                                                $('#alertMessage').text("{{ session('success') }}");
                                                $('#alertModal').modal('show');
                                            });
                                        </script>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div id="form-modifications" style="display: none;">
                                <div class="card-header">
                                    <div style="display: flex; width: 100%; justify-content: space-between; align-items: center;">
                                        <h5 class="card-title">
                                            Modification des approbateurs
                                        </h5>
                                        @if(session('error'))
                                            <div class="alert alert-danger alert-dismissible fade show" style="color: red;" role="alert">
                                                {{ session('error') }}
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>
                                        @endif
                                        @if(session('success'))
                                            <div class="alert alert-success alert-dismissible fade show" style="color: red;" role="alert">
                                                {{ session('success') }}
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="card-content">
                                <div class="card-body">
                                    <div id="form-modification" style="display: none;">
                                        <form class="form" method="POST" action="{{ route('updateApprobation', '$approbateur') }}" data-parsley-validate id="form-modifier">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" class="form-control" id="ecran_id_mod" value="{{ $ecran->id }}" name="ecran_id" required>
                                            <div class="row">
                                                <div class="col">
                                                    <label class="form-label" for="utilisateur">Utilisateur :</label>
                                                    <select class="form-select" name="userapp_mod" id="userapp_mod">
                                                        @foreach($personne as $personnes)
                                                        <option value="{{ $personnes->code_personnel }}">{{ $personnes->nom }} {{ $personnes->prenom }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-2">
                                                    <label class="form-label">N° Ordre</label>
                                                    <select class="form-select" name="Nordre_mod" id="Nordre_mod">
                                                        @for ($i = 1; $i <= 10; $i++)
                                                        <option value="{{ $i }}">{{ $i }}</option>
                                                        @endfor
                                                    </select>
                                                </div>
                                                <div class="col">
                                                    <div class="d-flex justify-content-end">
                                                        <input type="submit" class="btn btn-primary" value="Modifier" id="modifierApprob">
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>

                                    <div id="form-enregistrement">
                                        <form class="form" method="POST" action="{{ route('storeApprobation') }}" data-parsley-validate>
                                            @csrf
                                            <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}" name="ecran_id" required>
                                            <div class="row">
                                                <div class="col">
                                                    <label class="form-label" for="utilisateur">Utilisateur :</label>
                                                    <select class="form-select" name="userapp" id="userapp" required>
                                                        <option value=""></option>
                                                        @foreach($personne as $personnes)
                                                        <option value="{{ $personnes->code_personnel }}">{{ $personnes->nom }} {{ $personnes->prenom }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-2">
                                                    <label class="form-label">N° Ordre</label>
                                                    <select class="form-select" name="Nordre" id="Nordre" required>
                                                        <option value=""></option>
                                                        @for ($i = 1; $i <= 10; $i++)
                                                        <option value="{{ $i }}">{{ $i }}</option>
                                                        @endfor
                                                    </select>
                                                </div>
                                                <div class="col">
                                                    <div class="d-flex justify-content-end">
                                                        <input type="submit" class="btn btn-primary" value="Enregistrer" id="enregistrerApprob">
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <div style="display: flex; width: 100%; justify-content: space-between; align-items: center;">
                                    <h5 class="card-title">
                                        Liste des approbateurs
                                    </h5>
                                </div>
                            </div>
                            <div class="card-content">
                                <div class="card-body">
                                    <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="table1">
                                        <thead>
                                            <tr>
                                                <th>N° ordre</th>
                                                <th>Nom</th>
                                                <th>Prénoms</th>
                                                <th>Structure</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($approbateurs as $approbateur)
                                            <tr>
                                                <td>{{ $approbateur->numOrdre }}</td>
                                                <td>{{ $approbateur->personnel->nom }}</td>
                                                <td>{{ $approbateur->personnel->prenom }}</td>
                                                <td>
                                                    @if($approbateur->structure)
                                                        @if($approbateur->structure->type_structure == 'agence')
                                                            {{ $approbateur->structure->agence->nom_agence }}
                                                        @elseif($approbateur->structure->type_structure == 'ministere')
                                                            {{ $approbateur->structure->ministere->libelle }}
                                                        @elseif($approbateur->structure->type_structure == 'bailleur')
                                                            {{ $approbateur->structure->bailleur->libelle_long }}
                                                        @endif
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="dropdown">
                                                        <a href="#" class="btn btn-link dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown">
                                                            <span style="color: white"></span>
                                                        </a>
                                                        <ul class="dropdown-menu z-3" aria-labelledby="userDropdown">
                                                            <li><a class="dropdown-item" href="#" onclick="showEditApprobateur('{{ $approbateur->codeAppro }}')"><i class="bi bi-pencil-square me-3"></i> Modifier</a></li>
                                                            <li><a class="dropdown-item" href="#" onclick="deleteApprobateur('{{ $approbateur->codeAppro }}')"> <i class="bi bi-trash3-fill me-3"></i> Supprimer</a></li>
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
        </div>
    </div>
</section>

<script>
    $(document).ready(function() {
        initDataTable('{{ auth()->user()->personnel->nom }} {{ auth()->user()->personnel->prenom }}', 'table1', 'Liste des approbateurs');
    });

    function showEditApprobateur(id) {
        $.ajax({
            url: '/approbateur/' + id + '/edit',
            type: 'GET',
            success: function(data) {
                $('#userapp_mod').val(data.code_personnel);
                $('#Nordre_mod').val(data.numOrdre);
                $('#form-modifier').attr('action', '/approbation/' + id);

                $('#form-enregistrement').hide();
                $('#form-modification').show();
                $('#form-enregistrements').hide();
                $('#form-modifications').show();
            },
            error: function(xhr) {
                console.error('Erreur lors de la récupération des données de l\'approbateur.');
            }
        });
    }


    function deleteApprobateur(id) {
        if (confirm("Êtes-vous sûr de vouloir supprimer cet approbateur ?")) {
            $.ajax({
                url: '/approbation/' + id,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(result) {
                    alert('Approbateur supprimé avec succès');
                    window.location.reload(true);
                },
                error: function(xhr, status, error) {
                    alert('Erreur lors de la suppression de l\'approbateur : ' + error);
                }
            });
        }
    }
</script>
@endsection
