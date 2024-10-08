@extends('layouts.app')

@section('content')
<style>
    /* Style global pour centrer le contenu de la page */
    body {
        justify-content: center;
        align-items: center;
    }

    /* Style pour le conteneur principal */
    .container {
        width: 80%;
        max-width: 1200px; /* Largeur maximale du conteneur */
    }

    /* Style pour le fond du modal */
    .modal-content {
        background-color: #EAF2F8;
    }

    .modal-header,
    .modal-footer {
        background-color: #EAF2F8;
    }

    .modal-header .btn-close {
        filter: invert(1); /* Assure que le bouton de fermeture est visible */
    }

    #liste-approbateurs-modal span{
        color: #666;
    }

    /* Style pour le contenu de la page */
    #multiple-column-forms {
        display: flex;
        justify-content: center;
        flex-direction: column;
        align-items: center;
    }

    .table-container {
        width: 100%;
    }

</style>
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
                    <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Projet </h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="">Etudes projets</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Naissance / modelisation</li>
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
    <section id="multiple-column-forms"  style="justify-content: center;">

        <div class="col-10">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col">
                            <h5 class="card-title">Naissance / Modélisation de Projet</h5>
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
                        <div class="col d-flex justify-content-end" style="line-height: inherit;">
                            <h6><a href="#" id="voir-liste-link" data-bs-toggle="modal" data-bs-target="#liste-approbateurs-modal">Voir la liste des approbateurs</a></h6>
                        </div>
                    </div>
                </div>
                <div class="card-content">
                    <fieldset class="border p-3 mt-5 rounded">
                        <div class="row align-items-center">

                            <div class="col-4">
                                <label for="user" class="form-label">Utilisateur:</label>
                                <select id="user" class="form-select" name="userapp">
                                    <option value="">Sélectionner les approbateurs</option>
                                    @foreach($personne as $personnes)
                                        <option value="{{ $personnes->code_personnel }}">{{ $personnes->nom }} {{ $personnes->prenom }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-4">
                                <label for="structure">Structure:</label>
                                <input type="text" id="structure" name="structure" class="form-control" readonly>
                            </div>
                            <div class="col-2 ms-auto">
                                <label for="nordre" class="form-label">Niveau :</label>
                                <input type="number" name="Nordre" id="nordre" value="{{ $nextOrder }}"  readonly class="form-control">
                            </div>
                            <div class="col-12 mt-3">
                                <button type="button" class="btn btn-primary" id="addAction">
                                    <i class="fa fa-plus"></i> Ajouter
                                </button>
                                <form id="approveForm" method="POST" action="{{ route('approbateur.store') }}">
                                    @csrf
                                    <input type="hidden" name="approbateurs" id="approbateursInput">
                                    <button type="submit" class="btn btn-primary float-end">
                                        <i class="fa fa-save"></i> Enregistrer
                                    </button>
                                </form>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>

            <hr>
            <div class="card">
                <div class="card-body">
                    <div class="table-container">
                        <table id="tableActionMener" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Niveau d'approbation</th>
                                    <th>Nom</th>
                                    <th>Structure </th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Les lignes seront ajoutées ici dynamiquement -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- Button to open the modal -->
    <div class="row">
        </div>

    <!-- Modal for the list of approvers -->
    <div class="modal fade" id="liste-approbateurs-modal" tabindex="-1" aria-labelledby="listeApprobatuerModalLabel" aria-hidden="true" style="background-color: #EAF2F8;">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="listeApprobatuerModalLabel">Liste des approbateurs</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="liste-approbateur-table">
                        <thead>
                            <tr>

                                <th>Nom </th>
                                <th>Prénoms</th>
                                <th>Structure</th>
                                <th>Niveau approbation</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($approbateurs as $approbateur)
                            <tr>

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
                                <td>{{ $approbateur->numOrdre }}</td>
                                <td>
                                    <div class="dropdown">
                                        <a href="#" class="btn btn-link dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown">
                                            <span style="color: white"></span>
                                        </a>
                                        <ul class="dropdown-menu z-3" aria-labelledby="userDropdown">
                                            <li><a class="dropdown-item" href="#" onclick="editApprobateur('{{ $approbateur->numOrdre }}', '{{ $approbateur->personnel->nom }} {{ $approbateur->personnel->prenom }}', '{{ $approbateur->personnel->code_personnel }}')"> <i class="bi bi-pencil-fill me-3"></i> Modifier</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="deleteApprobateur('{{ $approbateur->codeAppro }}')"> <i class="bi bi-trash3-fill me-3"></i> Supprimer</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <!--Modale Modifier approbateur-->
    <div class="modal fade" id="editApprobateurModal" tabindex="-1" aria-labelledby="editApprobateurModalLabel" aria-hidden="true" style="background-color: #EAF2F8;">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editApprobateurModalLabel">Modifier Approbateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editApproveForm" method="POST" action="{{ route('approbateur.update') }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="numOrdreId" id="numOrdreId">
                        <fieldset class="border p-3 mt-5 rounded">
                            <div class="row align-items-center">
                                <div class="col-2">
                                    <label for="editNordre" class="form-label">Niveau approbation:</label>
                                    <input type="number" name="editNordre" id="editNordre" readonly class="form-control">
                                </div>
                                <div class="col-5">
                                    <label for="editUser" class="form-label">Utilisateur:</label>
                                    <select id="editUser" class="form-select" name="editUserapp">
                                        <option value="">Sélectionner l'utilisateur</option>
                                        @foreach($personne as $personnes)
                                        <option value="{{ $personnes->code_personnel }}">{{ $personnes->nom }} {{ $personnes->prenom }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 mt-3">
                                    <button type="submit" class="btn btn-primary float-end">
                                        <i class="fa fa-save"></i> Enregistrer
                                    </button>
                                </div>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Message d'alerte -->
    <div id="alertModal" class="modal fade" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="alertModalLabel" style="color: red;">Alerte</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="alertMessage"></p>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
    $(document).ready(function() {
        // Initialize DataTable if needed
        initDataTable('{{ auth()->user()->personnel->nom }} {{ auth()->user()->personnel->prenom }}', 'liste-approbateur-table', 'Liste des approbateurs');
        $('#addAction').on('click', function() {
            // Récupérer les valeurs des champs
            var nordre = parseInt($('#nordre').val());
            var userCode = $('#user').val();
            var userText = $('#user option:selected').text();
            var structure = $('#structure').val();

            // Vérifier si toutes les données sont sélectionnées ou saisies
            if (userCode) {
                // Vérifier si les données existent déjà dans le tableau #tableActionMener
                var existeDeja = false;
                $('#tableActionMener tbody tr').each(function() {
                    var existingUser = $(this).find('td:eq(1)').text();
                    if (existingUser === userText) {
                        existeDeja = true;
                        return false; // Sortir de la boucle each
                    }
                });

                if (!existeDeja) {
                    // Ajouter les données récupérées au tableau #tableActionMener
                    var tableActionMener = $('#tableActionMener tbody');
                    tableActionMener.append(
                        '<tr><td>' + nordre + '</td><td>' + userText + '</td><td>'+structure+'</td><td hidden>' + userCode + '</td><td><button type="button" class="btn btn-danger btn-sm delete-action">Supprimer</button></td></tr>'
                    );

                    // Incrémenter le champ #nordre
                    $('#nordre').val(nordre + 1);

                    // Réinitialiser le champ #user après l'ajout
                    $('#user').val('');
                } else {
                    $('#alertMessage').text("Cet utilisateur est déjà dans le tableau.");
                    $('#alertModal').modal('show');
                }
            } else {
                $('#alertMessage').text("Veuillez sélectionner un utilisateur avant d'ajouter.");
                $('#alertModal').modal('show');
            }
        });

        // Submit form with approbateurs data
        $('#approveForm').on('submit', function(e) {
            e.preventDefault();
            var approbateurs = [];
            $('#tableActionMener tbody tr').each(function() {
                var nordre = $(this).find('td:eq(0)').text();
                var userText = $(this).find('td:eq(1)').text();
                var userCode = $(this).find('td:eq(2)').text();
                approbateurs.push({
                    nordre: nordre,
                    userText: userText,
                    userCode: userCode
                });
            });
            $('#approbateursInput').val(JSON.stringify(approbateurs));
            console.log('Approbateurs:', approbateurs); // Debugging line
            this.submit();
        });


        // Gérer le clic sur les boutons de suppression
        $('#tableActionMener').on('click', '.delete-action', function() {
            // Supprimer la ligne correspondante
            $(this).closest('tr').remove();

            // Recalculer les numéros d'ordre et mettre à jour le champ nordre
            var currentOrder = {{ $nextOrder }};

            $('#tableActionMener tbody tr').each(function(index) {
                $(this).find('td:eq(0)').text(currentOrder + index);
            });

            // Mettre à jour le numéro d'ordre pour la prochaine entrée
            $('#nordre').val(currentOrder + $('#tableActionMener tbody tr').length);
        });

    });

    function deleteApprobateur(id) {
        if (confirm("Êtes-vous sûr de vouloir supprimer cet approbateur ?")) {
            $.ajax({
                url: '/approbation/' + id,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(result) {
                    $('#alertMessage').text("Approbateur supprimé avec succès.");
                    $('#alertModal').modal('show');
                    window.location.reload(true);
                },
                error: function(xhr, status, error) {
                    $('#alertMessage').text('Erreur lors de la suppression de l\'approbateur : ' + error);
                    $('#alertModal').modal('show');
                }
            });
        }
    }
    function editApprobateur(numOrdre, nomPrenom, userCode) {
        // Remplir les champs du modal avec les données existantes
        $('#editNordre').val(numOrdre);
        $('#editUser').val(userCode);
        $('#numOrdreId').val(numOrdre); // Enregistrer l'ID de l'approbateur pour la modification

        // Ouvrir le modal
        $('#editApprobateurModal').modal('show');
    }

    $('#editApproveForm').on('submit', function(e) {
        e.preventDefault();

        // Effectuer les modifications nécessaires ici ou soumettre le formulaire
        this.submit();
    });

</script>
<script>
    document.getElementById('user').addEventListener('change', function () {
        var codePersonnel = this.value;
        var structureInput = document.getElementById('structure');

        if (codePersonnel) {
            fetch(`/get-structure/${codePersonnel}`)
                .then(response => response.json())
                .then(data => {
                    if (data.libelle) {
                        structureInput.value = data.libelle; // Affiche le libellé de la structure
                    } else {
                        structureInput.value = 'Aucune structure trouvée.';
                    }
                })
                .catch(error => console.error('Erreur:', error));
        } else {
            structureInput.value = '';
        }
    });
</script>
@endsection
<?php
    public function filterAnnexe(Request $request)
    {
        try {
            // 1. Récupérer le sous-domaine, l'année et l'écran sélectionnés
            $selectedSousDomaineCode = $request->input('sous_domaine');
            $selectedYear = $request->input('year');
            $ecran_id = $request->input('ecran_id');

            // Vérifier que les paramètres nécessaires sont présents
            if (!$selectedSousDomaineCode || !$selectedYear || !$ecran_id) {
                return back()->withErrors(['error' => 'Paramètres manquants.']);
            }

            // 2. Extraire les années et les codes sous-domaines depuis les projets
            $projets = ProjetEha2::all();

            // Filtrer les projets en fonction de l'année et du sous-domaine sélectionnés
            $projetsFiltres = $projets->filter(function ($projet) use ($selectedSousDomaineCode, $selectedYear) {
                // Extraire le code sous-domaine de la position 12 à 15 du CodeProjet
                $codeSousDomaine = substr($projet->CodeProjet, 12, 4);
                // Extraire l'année de la position 17 à 21
                $year = substr($projet->CodeProjet, 17, 4);

                // Vérifier que le projet correspond au sous-domaine et à l'année sélectionnés
                return $codeSousDomaine == $selectedSousDomaineCode && $year == $selectedYear;
            });

            // 3. Vérifier si les projets existent dans la table `Caractéristique`
            $caracteristiques = Caracteristique::whereIn('CodeProjet', $projetsFiltres->pluck('CodeProjet'))->get();

            // Trouver les intersections entre les projets dans `ProjetEha2` et `Caractéristique`
            $intersections = $projetsFiltres->filter(function ($projet) use ($caracteristiques) {
                return $caracteristiques->contains('CodeProjet', $projet->CodeProjet);
            });

            // Obtenir les CodeCaractFamille correspondants
            $codeCaractFamilles = $caracteristiques->pluck('CodeCaractFamille');

            // Récupérer les tables associées au sous-domaine sélectionné
            $caracts = SousDomaineTypeCaract::where('CodeSousDomaine', $selectedSousDomaineCode)->get();

            // Vérifier s'il y a des caractéristiques associées
            if ($caracts->isEmpty()) {
                return back()->withErrors(['error' => 'Aucun type de table trouvé pour ce sous-domaine.']);
            }

            // Préparer les colonnes et les données des tables associées
            $resultats = [];
            $headerConfig = []; // Pour stocker les configurations des en-têtes

            foreach ($caracts as $caract) {
                $tableName = $caract->CaractTypeTable;

                // Charger dynamiquement le modèle basé sur le nom de la table
                $modelClass = "App\\Models\\" . ucfirst($tableName);
                if (!class_exists($modelClass)) {
                    return back()->withErrors(['error' => "Le modèle pour la table $tableName n'existe pas."]);
                }

                $model = app($modelClass);
                // Filtrer les données en fonction des CodeCaractFamille
                $data = $model::whereIn('CodeCaractFamille', $codeCaractFamilles)->get();

                // Remplacer les codes de natureTravaux par leur libellé
                foreach ($data as $row) {
                    // Remplacement de la valeur de natureTravaux par son libellé
                    if (isset($row->natureTravaux)) {
                        $libelleNatureTravaux = NatureTravaux::getLibelleByCode($row->natureTravaux);
                        $row->natureTravaux = $libelleNatureTravaux ?: $row->natureTravaux; // Si pas de libellé trouvé, garde le code
                    }

                    // Remplacement de la valeur de typeCaptage par son libellé
                    if (isset($row->typeCaptage)) {
                        $libelleTypeCaptage = TypeCaptage::getLibelleByCode($row->typeCaptage);
                        $row->typeCaptage = $libelleTypeCaptage ?: $row->typeCaptage; // Si pas de libellé trouvé, garde le code
                    }
                }

                // Récupérer les colonnes de la table actuelle
                $columns = \Schema::getColumnListing($model->getTable());

                // Configuration dynamique des en-têtes
                $headerName = $this->formatHeaderName($tableName); // Formater le nom de l'en-tête
                $headerConfig[] = [
                    'name' => $headerName,
                    'colspan' => count($columns), // Mettez à jour avec le nombre de colonnes
                ];

                // Stocker les résultats sous forme de table
                $resultats[$headerName] = [
                    'data' => $data,
                    'columns' => $columns,
                ];
            }

            // Passer les résultats, l'année, et le sous-domaine à la vue
            return view('partials.result_table', compact('headerConfig', 'resultats', 'selectedSousDomaineCode', 'selectedYear'));

        } catch (\Exception $e) {
            // Afficher l'erreur dans les logs
            \Log::error('Erreur lors de l\'exécution de filterAnnexe: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Une erreur est survenue lors du traitement.']);
        }
    }
