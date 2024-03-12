<!-- resources/views/users/create.blade.php -->

@extends('layouts.app')

@section('content')
@if (session('success'))
<script>
    alert("{{ session('success') }}");

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
                            <li class="breadcrumb-item"><a href="">Gestion des habilitations</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Sous-menus</li>

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
                    <div style="display: flex; width: 100%; justify-content: space-between; align-items: center;">
                        @can("ajouter_ecran_" . $ecran->id)
                        <h5 class="card-title">
                            Ajout d'un sous-menu
                            <a href="#" data-toggle="modal" data-target="#localite-modal" style="margin-left: 15px;"><i class="bi bi-plus-circle me-1"></i></a>
                        </h5>
                         @endcan

                       
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
                        <h5 class="card-title"> Liste des sous-menus</h5>
                    </div>
                </div>
                <div class="card-content">
                    <div class="card-body">


                        <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="table1">
                            <thead>
                                <tr>
                                    <th>Code </th>
                                    <th>Rubrique</th>
                                    <th>Sous-menu parent</th>
                                    <th>Sous-menu</th>
                                    <th>Ordre</th>
                                    <th>Niveau</th>
                                    <th>action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($sous_menus as $sous_menu)
                                <tr>
                                    <td># {{ $sous_menu->code }}</td>
                                    <td>{{ $sous_menu->rubrique->libelle ?? '' }}</td>
                                    <td>{{ $sous_menu->sm_parent->libelle ?? "" }}</td>
                                    <td>{{ $sous_menu->libelle }}</td>
                                    <td>{{ $sous_menu->ordre }}</td>
                                    <td>{{ $sous_menu->niveau }}</td>
                                    <td>
                                        <div class="dropdown">
                                            <a href="#" class="btn btn-link dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown">
                                                <span style="color: white"></span>
                                            </a>
                                            <ul class="dropdown-menu z-3" aria-labelledby="userDropdown">
                                                @can("modifier_ecran_" . $ecran->id)
                                                <li><a class="dropdown-item" onclick="showEditRubrique('{{ $sous_menu->code }}')" href="#"><i class="bi bi-pencil-square me-3"></i> Modifier</a></li>
                                                @endcan
                                                @can("supprimer_ecran_" . $ecran->id)
                                                <li><a class="dropdown-item" onclick="deleteRubrique('{{ $sous_menu->code }}')" href="#"> <i class="bi bi-trash3-fill me-3"></i> Supprimer</a></li>
                                                @endcan
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



    <!-- Modal -->
    <div class="modal fade" id="localite-modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Enregistrement d'une rubrique</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form class="form" method="POST" action="{{ route('sous_menu.store') }}" data-parsley-validate>
                        @csrf
                        <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}"  name="ecran_id" required>
                        <div class="row">
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="edit_libelle">Rubriques :</label>
                                    <div class="mb-3">
                                        <select class="form-select" id="code_rubrique" name="code_rubrique">
                                            <option value="">Sélection une rubrique</option>
                                            @foreach($rubriques as $rubrique)
                                            <option value="{{ $rubrique->code }}">{{ $rubrique->libelle }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="edit_ordre">Niveau :</label>
                                    <input type="number" class="form-control" id="niveau" name="niveau" placeholder="Niveaux" min="1" max="3" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="sous_menu_parent">Parent :</label>
                                    <div class="mb-3">
                                        <select class="form-select" id="sous_menu_parent" name="sous_menu_parent">
                                            <option value="">Sélection un sous menu</option>
                                            @foreach($sous_menus as $sous_menu)
                                            <option value="{{ $sous_menu->code }}">{{ $sous_menu->libelle }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="libelle">Libellé :</label>
                                    <input type="text" class="form-control" id="libelle" name="libelle" placeholder="Libellé" required>
                                </div>
                            </div>
                            <div class="col-md-4 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="libelle">Ordre :</label>
                                    <input type="number" class="form-control" id="ordre" name="ordre" value="{{  $smPlusGrandOrdre->ordre + 1 }}" placeholder="Ordre" min="1" required>
                                </div>
                            </div>
                        </div>
                        @can("ajouter_ecran_" . $ecran->id)
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                            <input type="submit" class="btn btn-primary" value="Enregistrer" id="enregistrerLocalite">
                        </div>
                         @endcan
                        
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="edit-localite-modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Modification de rubrique</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form class="form" method="POST" action="{{ route('sous_menu.update') }}" data-parsley-validate>
                        @csrf
                        <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}"  name="ecran_id" required>
                        <input type="hidden" class="form-control" id="edit_code" name="edit_code" required>
                        <div class="row">
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="edit_libelle">Rubriques :</label>
                                    <div class="mb-3">
                                        <select class="form-select" id="edit_code_rubrique" name="edit_code_rubrique">
                                            <option value="">Sélection une rubrique</option>
                                            @foreach($rubriques as $rubrique)
                                            <option value="{{ $rubrique->code }}">{{ $rubrique->libelle }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="edit_ordre">Niveau :</label>
                                    <input type="number" class="form-control" id="edit_niveau" name="edit_niveau" placeholder="Ordre" min="1" max="3" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="edit_sous_menu_parent">Parent :</label>
                                    <div class="mb-3">
                                        <select class="form-select" id="edit_sous_menu_parent" name="edit_sous_menu_parent">
                                            <option value="">Sélection un sous menu</option>
                                            @foreach($sous_menus as $sous_menu)
                                            <option value="{{ $sous_menu->code }}">{{ $sous_menu->libelle }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="edit_libelle">Libellé :</label>
                                    <input type="text" class="form-control" id="edit_libelle" name="edit_libelle" placeholder="Libellé" required>
                                </div>
                            </div>
                            <div class="col-md-4 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="edit_ordre">Ordre :</label>
                                    <input type="number" class="form-control" id="edit_ordre" name="edit_ordre" placeholder="Ordre" min="1" required>
                                </div>
                            </div>
                        </div>
                        @can("modifier_ecran_" . $ecran->id)
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                            <input type="submit" class="btn btn-primary" value="Enregistrer" id="edit_enregistrerLocalite">
                        </div>
                         @endcan
                        
                    </form>
                </div>
            </div>
        </div>
    </div>

</section>



<script>
    /* CODE JAVASCRIPT ICI */


    $(document).ready(function() {
        initDataTable('{{ auth()->user()->personnel->nom }} {{ auth()->user()->personnel->prenom }}', 'table1', 'Liste des sous-menus')
    });

    // Lorsque l'utilisateur clique sur un bouton "Modifier"
    function showEditRubrique(code) {
        $('#edit-localite-modal').modal('show');
        $.ajax({
            type: 'GET'
            , url: '/admin/sous_menu/get-sous_menu/' + code
            , success: function(data) {
                console.log(data);
                // Remplir le formulaire modal avec les données du district
                $('#edit_code').val(data.code); // Utilisez l'ID du champ d'édition
                $('#edit_libelle').val(data.libelle);
                $('#edit_ordre').val(data.ordre);
                $('#edit_niveau').val(data.niveau);
                $('#edit_code_rubrique').val(data.code_rubrique);
                $('#edit_sous_menu_parent').val(data.sous_menu_parent);
            }
        });
    }

    function deleteRubrique(code) {
        if (confirm("Êtes-vous sûr de vouloir supprimer ce sous-menu ?")) {
            $.ajax({
                url: '/admin/sous_menu/delete/' + code
                , method: 'DELETE', // Utilisez la méthode DELETE pour la suppression
                data: {
                    _token: '{{ csrf_token() }}' // Assurez-vous d'envoyer le jeton CSRF
                }
                , success: function(response) {
                    var message = "sous-menu supprimé avec succès.";
                    showPopup(message);
                    // Rechargez la page actuelle en ignorant le cache du navigateur
                    window.location.reload(true);

                }
                , error: function() {
                    // Gérer les erreurs de la requête AJAX
                    console.log('Erreur lors de la suppression du sous-menu.');
                }
            });
        }
    }

</script>
@endsection
