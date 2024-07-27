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

    .vertical-separator {
        border-left: 2px solid #000; /* Couleur et largeur de la barre */
        height: 100%; /* Hauteur de la barre */
        margin: 0 15px; /* Espacement horizontal */
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
                    <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Projet </h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="">Gestions des exceptions</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Réattribution de projet</li>
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
                                        @if(session('error'))
                            <div class="alert alert-danger" role="alert">
                                {{ session('error') }}
                            </div>
                        @endif

                        <!-- Affichage des messages de succès -->
                        @if(session('success'))
                            <div class="alert alert-success" role="alert">
                                {{ session('success') }}
                            </div>
                        @endif
                                <div class="card-header">
                                    <div style="display: flex; width: 100%; justify-content: space-between; align-items: center;">
                                        <h5 class="card-title">
                                            Réattribution
                                        </h5>

                                    </div>
                                </div>


                            <div class="card-content">
                                <div class="card-body">
                                <form class="form" method="POST" action="{{ route('reattribution.store') }}" data-parsley-validate>
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}" name="ecran_id" required>
                                            <div class="row justify-content-center">
                                                <div class="col">
                                                    <div class="row">
                                                        <div class="col-7">
                                                            <label class="form-label" for="utilisateur">Code projet :</label>
                                                            <select class="form-select" name="code_projet" id="code_projet">
                                                            <option value="">Selectionner le code projet</option>
                                                                @foreach ($projets as $projet)
                                                                    <option value="{{ $projet->CodeProjet }}">{{ $projet->CodeProjet }}</option>
                                                                @endforeach
                                                        </select>

                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-7">
                                                            <label class="form-label">Maître d'oeuvre</label>
                                                            <select class="form-select" name="maitre" id="maitre">
                                                                <option value="">Selectionner</option>
                                                                @foreach ($agenceExe->sortByDesc('date') as $agence)
                                                                    <option value="{{ $agence->code_agence_execution }}">{{ $agence->nom_agence }}</option>
                                                                @endforeach
                                                            </select>

                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-7">
                                                            <label class="form-label">Chef de projet</label>
                                                            <select class="form-select" name="chef" id="chef">
                                                                <option value="">Selectionner </option>
                                                                @foreach ($personnel->sortByDesc('date') as $personnel)
                                                                    @if ($personnel->personnel)
                                                                        <option value="{{ $personnel->code_personnel }}">{{ $personnel->personnel->nom }} {{ $personnel->personnel->prenom }}</option>
                                                                    @endif
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-7">
                                                            <label for="date">Date de changement</label>
                                                            <input type="date" class="form-control" name="changement" id="changement">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col">

                                                    <div class="row">
                                                        <div class="col">
                                                            <label class="form-label">Type de réattribution :</label>
                                                            <div>
                                                                <input type="radio" id="chef_projet" name="type_reattribution" value="chef_projet" onclick="toggleMotifs()">
                                                                <label for="chef_projet">Chef de projet</label>
                                                                <input type="radio" id="maitre_oeuvre" name="type_reattribution" value="maitre_oeuvre" onclick="toggleMotifs()">
                                                                <label for="maitre_oeuvre">Maître d'œuvre</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <div class="row" id="motifs_chef_projet" style="display: none;">
                                                        <div class="col">
                                                            <label class="form-label">Motifs pour changer le Chef de projet :</label>
                                                            <div>
                                                                @foreach ($changerChef as $chefsCha)
                                                                <input class="form-check-input" type="checkbox" name="motifs[]" value="{{ $chefsCha->id}}"> {{ $chefsCha->libelle}}<br>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row" id="motifs_maitre_oeuvre" style="display: none;">
                                                        <div class="col">
                                                            <label class="form-label">Motifs pour changer le Maître d'œuvre :</label>
                                                            <div>
                                                                @foreach ($changerMaitre as $maitress)
                                                                <input class="form-check-input" type="checkbox" name="motifs[]" value="{{ $maitress->id}}"> {{ $maitress->libelle}}<br>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <div class="row">
                                                        <div class="col-9">
                                                            <label class="form-label" for="réattribution">Autre Motif de la réattribution</label>
                                                            <textarea class="form-control" name="motif" id="motif" rows="2"></textarea>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <br>
                                            <div class="row">
                                                <div class="col-12 d-flex justify-content-end mt-2">
                                                    <input type="submit" class="btn btn-primary me-1 mb-1" value="Enregistrer" id="enregistrer">
                                                    <button type="reset" class="btn btn-light-secondary me-1 mb-1">Annuler</button>
                                                </div>
                                            </div>

                                        </form>

                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <div style="display: flex; width: 100%; justify-content: space-between; align-items: center;">
                                    <h5 class="card-title">
                                        Liste des réattributions
                                    </h5>
                                </div>
                            </div>
                            <div class="card-content">
                                <div class="card-body">
                                    <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="table1">
                                        <thead>

                                            <tr>
                                                <th>Code projet</th>
                                                <th>Date de changement</th>
                                                <th>Ancien</th>
                                                <th>Nouveau</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($reattributions as $reattribution)


                                                <tr>
                                                    <td>{{ $reattribution->code_projet }}</td>
                                                    <td>{{ $reattribution->changement }}</td>
                                                    
                                                    <td>{{ $reattribution->code_projet }}</td>
                                                    <td>

                                                    </td>
                                                    <td>
                                                        <div class="dropdown">
                                                            <a href="#" class="btn btn-link dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown">
                                                                <span style="color: white"></span>
                                                            </a>
                                                            <ul class="dropdown-menu z-3" aria-labelledby="userDropdown">
                                                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#voirPlusModal"><i class="bi bi-eye me-3"></i> Voir plus</a></li>
                                                                <li><a class="dropdown-item" href="#" ><i class="bi bi-pencil-square me-3"></i> Modifier</a></li>
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
                        </div>
                    </div>
                </div>
            </section>
            <div class="modal fade" id="voirPlusModal" tabindex="-1" aria-labelledby="voirPlusModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content" style="width: 141%">
                        <div class="modal-header">
                            <h5 class="modal-title" id="voirPlusModalLabel">Détails supplémentaires</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="alertMessage">
                            <div class="row">
                                <div class="col">
                                    <label for="code_projet">Code projet</label>
                                    <input type="text" readonly class="form-control" name="code_projet_plus" id="code_projet_plus">
                                </div>
                                <div class="col">
                                    <label for="maitre">Maître d'œuvre</label>
                                    <input type="text" readonly class="form-control" name="maitre_plus" id="maitre_plus">
                                </div>
                                <div class="col">
                                    <label for="chef">Chef projet</label>
                                    <input type="text" readonly class="form-control" name="chef_plus" id="chef_plus">
                                </div>
                                <div class="col">
                                    <label for="chef">Date de réattribution</label>
                                    <input type="date" readonly class="form-control" name="chef_plus" id="chef_plus">
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col justify-content-center">
                                    <label for="chef">Motif de réattribution</label>
                                    <Textarea class="form-control" readonly name="motif_plus" id="motif_plus"></Textarea>
                                </div>

                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    $(document).ready(function() {
        initDataTable('{{ auth()->user()->personnel->nom }} {{ auth()->user()->personnel->prenom }}', 'table1', 'Liste des réattributions');
    });
    function toggleMotifs() {
        var chefProjetChecked = document.getElementById('chef_projet').checked;
        var maitreOeuvreChecked = document.getElementById('maitre_oeuvre').checked;

        document.getElementById('motifs_chef_projet').style.display = chefProjetChecked ? 'block' : 'none';
        document.getElementById('motifs_maitre_oeuvre').style.display = maitreOeuvreChecked ? 'block' : 'none';
    }

    document.getElementById('code_projet').addEventListener('change', function() {
    var codeProjet = this.value;

    document.getElementById('code_projet').addEventListener('change', function() {
    var codeProjet = this.value;

    if (codeProjet) {
        fetch('/getProjectDetails/' + codeProjet)
            .then(response => response.json())
            .then(data => {
                var chefSelect = document.getElementById('chef');
                var maitreSelect = document.getElementById('maitre');

                // Fonction pour gérer l'ajout des options
                function updateSelect(selectElement, items, formatOption) {
                    // Supprimer l'option ajoutée précédemment
                    if (selectElement.options.length > 0 && selectElement.options[0].classList.contains('new-option')) {
                        selectElement.remove(0);
                    }

                    if (items && items.length > 0) {
                        var newOption = formatOption(items[0]);
                        newOption.classList.add('new-option'); // Ajouter une classe pour l'identifier
                        selectElement.insertBefore(newOption, selectElement.firstChild);
                        selectElement.selectedIndex = 0; // Sélectionner la nouvelle option
                    }
                }

                // Mettre à jour les selects
                updateSelect(chefSelect, data.chef, function(chef) {
                    return new Option(chef.nom + ' ' + chef.prenom, chef.code_personnel);
                });

                updateSelect(maitreSelect, data.maitre, function(maitre) {
                    return new Option(maitre.nom_agence, maitre.code_agence_execution);
                });
            })
            .catch(error => console.error('Error:', error));
    } else {
        // Si aucun code de projet n'est sélectionné, supprimer les nouvelles options ajoutées précédemment
        var chefSelect = document.getElementById('chef');
        var maitreSelect = document.getElementById('maitre');

        if (chefSelect.options.length > 0 && chefSelect.options[0].classList.contains('new-option')) {
            chefSelect.remove(0);
        }
        if (maitreSelect.options.length > 0 && maitreSelect.options[0].classList.contains('new-option')) {
            maitreSelect.remove(0);
        }
    }
});

});
</script>
@endsection
