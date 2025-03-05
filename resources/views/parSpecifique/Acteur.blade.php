@extends('layouts.app')

@section('content')
<style>
    .photo-preview {
    width: 150px;
    height: 150px;
    border: 2px dashed #ccc;
    border-radius: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background-color: #f9f9f9;
    margin-top: 10px;
}

.photo-preview img {
    max-width: 100%;
    max-height: 100%;
    object-fit: cover;
}

</style>
@if (session('success'))
<script>
    alert("{{ session('success') }}");
</script>
@endif

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
                            <li class="breadcrumb-item"><a href="">Paramètre spécifique</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Acteurs</li>

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

<section class="section">
    <div class="card">
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @elseif (session('success'))
        <div class="alert alert-success">
            <ul>
                <li>{{ session('success') }}</li>
            </ul>
        </div>
        @endif



        <div class="card-header">
            <h5> Acteurs</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('acteurs.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="method" name="_method" value="POST">
                <input type="hidden" id="acteur-id" name="id">

                <div class="row">
                    <div class="row ">
                        <!-- Pays -->
                        <div class="form-group col-md-4">
                            <label for="code_pays">Pays</label>
                            <input type="hidden" name="code_pays" id="code_pays" value="{{ $pays->alpha3 }}">
                            <input type="text" name="pays" class="form-control" id="pays" value="{{ $pays->nom_fr_fr }}" readonly>
                        </div>
                        <div class="col-6"></div>
                        <div class="form-group col-2 text-end">
                            <label>Photo / Logo :</label>
                            <div class="photo-preview">
                                <img id="photo-preview" src="#" alt="Aperçu de la photo" style="display: none;">

                            </div>
                            <input type="file"  id="photo" name="photo" class="form-control" accept="image/*" style="width:100%">
                        </div>
                    </div>

                    <div class="form-group col-md-4">
                        <label>Acteur *</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="type_personne" id="personnePhysique" value="physique" onchange="togglePersonneFields()">
                            <label class="form-check-label" for="personnePhysique">Personne Physique</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="type_personne" id="personneMorale" value="morale" onchange="togglePersonneFields()">
                            <label class="form-check-label" for="personneMorale">Personne Morale (Entreprise)</label>
                        </div>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Status *</label>
                        <select class="form-control" name="type_financement" required>
                            <option value="">Sélectionner le statut</option>
                            @foreach($typeFinancements as $typeFin)
                                <option value="{{ $typeFin->code_type_financement }}"> {{ $typeFin->libelle }} </option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Type d'acteur -->
                    <div class="form-group col-md-4">
                        <label for="type_acteur">Type d'acteur</label>
                        <select class="form-control" id="type_acteur" name="type_acteur" required >
                            <option value="">Sélectionner le type d'acteur</option>
                            @foreach ($TypeActeurs as $TypeActeur)
                                <option value="{{ $TypeActeur->cd_type_acteur }}">{{ $TypeActeur->libelle_type_acteur }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- MOE Entreprise Fields -->
                    <div class="row mt-3 d-none" id="entrepriseFields">
                        <hr>
                        <h6>Détails pour l’Entreprise</h6>
                        <div class="col-12">
                            <ul class="nav nav-tabs" id="entrepriseTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="entreprise-general-tab" data-bs-toggle="tab" data-bs-target="#entreprise-general" type="button" role="tab" aria-controls="entreprise-general" aria-selected="true">Informations Générales</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="entreprise-legal-tab" data-bs-toggle="tab" data-bs-target="#entreprise-legal" type="button" role="tab" aria-controls="entreprise-legal" aria-selected="false">Informations Juridiques</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="entreprise-contact-tab" data-bs-toggle="tab" data-bs-target="#entreprise-contact" type="button" role="tab" aria-controls="entreprise-contact" aria-selected="false">Informations de Contact</button>
                                </li>
                            </ul>
                            <div class="tab-content mt-3" id="entrepriseTabsContent">
                                <!-- Tab 1: Informations Générales -->
                                <div class="tab-pane fade show active" id="entreprise-general" role="tabpanel" aria-labelledby="entreprise-general-tab">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label>Nom complet (Raison sociale) *</label>
                                            <input type="text" class="form-control" name="libelle_long" placeholder="Nom complet de l'entreprise" >
                                        </div>
                                        <div class="col-md-6">
                                            <label>Nom abrégé *</label>
                                            <input type="text" class="form-control" name="libelle_court" placeholder="Nom abrégé de l'entreprise" >
                                        </div>

                                        <div class="col-md-6">
                                            <label>Date de création * </label>
                                            <input type="date" class="form-control" name="date_creation" placeholder="Adresse complète">
                                        </div>
                                        <div class="col-md-6 ">
                                            <label>Forme Juridique *</label>
                                            <select name="FormeJuridique" id="FormeJuridique" class="form-control">
                                                <option value="">Sélectionnez...</option>
                                                @foreach ($formeJuridiques as $formeJuridique)
                                                    <option value="{{ $formeJuridique->id }}">{{ $formeJuridique->forme }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tab 2: Informations Juridiques -->
                                <div class="tab-pane fade" id="entreprise-legal" role="tabpanel" aria-labelledby="entreprise-legal-tab">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label>Numéro d’Immatriculation *:</label>
                                            <input type="text" class="form-control" name="NumeroImmatriculation" placeholder="Numéro RCCM">
                                        </div>
                                        <div class="col-md-4">
                                            <label>Numéro d’Identification Fiscale (NIF) :</label>
                                            <input type="text" class="form-control" name="nif" placeholder="Numéro fiscal">
                                        </div>
                                        <div class="col-md-4">
                                            <label>Registre du commerce (RCCM) :</label>
                                            <input type="text" class="form-control" name="rccm" placeholder="Numéro fiscal">
                                        </div>
                                        <div class="col-md-6 mt-2">
                                            <label>Capital Social :</label>
                                            <input type="number" class="form-control" name="CapitalSocial" placeholder="Capital social de l’entreprise">
                                        </div>
                                        <div class="col-md-6 mt-2">
                                            <label>Numéro d'agrément :</label>
                                            <input type="text" name="Numéroagrement" id="Numéroagrement" class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <!-- Tab 3: Informations de Contact -->
                                <div class="tab-pane fade" id="entreprise-contact" role="tabpanel" aria-labelledby="entreprise-contact-tab">
                                    <div class="row">
                                        <div class="col-4">
                                            <label>Code postale</label>
                                            <input type="text" class="form-control" name="CodePostaleEntreprise" placeholder="Code postale">
                                        </div>
                                        <div class="col-4">
                                            <label>Adresse postale</label>
                                            <input type="text" class="form-control" name="AdressePostaleEntreprise" placeholder="Code postale">
                                        </div>
                                        <div class="col-4">
                                            <label>Adresse Siège</label>
                                            <input type="text" class="form-control" name="AdresseSiègeEntreprise" placeholder="Code postale">
                                        </div>
                                        <hr>
                                        <div class="col-md-3">
                                            <label>Représentant Légal *</label>
                                            <lookup-select name="nomRL" id="nomRL">
                                                @foreach ($acteurRepres as $acteurRepre)
                                                    <option value="{{ $acteurRepre->code_acteur }}">{{ $acteurRepre->libelle_court }} {{ $acteurRepre->libelle_long }}</option>
                                                @endforeach
                                            </lookup-select>

                                        </div>
                                        <div class="col-md-3">
                                            <label>Email *</label>
                                            <input type="email" name="emailRL" class="form-control" placeholder="Email du représentant légal" >
                                        </div>
                                        <div class="col-md-3">
                                            <label>Téléphone 1 *</label>
                                            <input type="text" name="telephone1RL" class="form-control" placeholder="Téléphone 1 du représentant légal">
                                        </div>
                                        <div class="col-md-3">
                                            <label>Téléphone 2 *</label>
                                            <input type="text" name="telephone2RL" class="form-control" placeholder="Téléphone 2 du représentant légal">
                                        </div>
                                        <hr>

                                    </div>
                                    <div class="row align-items-end">
                                        <!-- Lookup-Multiselect -->
                                        <div class="col-md-3">
                                            <label>Personne de Contact</label>
                                            <lookup-multiselect name="nomPC" id="nomPC">
                                                @foreach ($acteurRepres as $acteurRepre)
                                                    <option value="{{ $acteurRepre->code_acteur }}">{{ $acteurRepre->libelle_court }} {{ $acteurRepre->libelle_long }}</option>
                                                @endforeach
                                            </lookup-multiselect>
                                        </div>

                                        <!-- Conteneur pour afficher dynamiquement les champs -->
                                        <div class="col-md-9 d-flex flex-wrap" id="contactContainer"></div>
                                    </div>
                                    <hr>

                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MOE Individu Fields -->
                    <div class="row mt-3 d-none" id="individuFields">
                        <hr>
                        <h6>Détails pour l’Individu</h6>
                        <div class="col-12">
                            <ul class="nav nav-tabs" id="individuTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="individu-general-tab" data-bs-toggle="tab" data-bs-target="#individu-general" type="button" role="tab" aria-controls="individu-general" aria-selected="true">Informations Personnelles</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="individu-contact-tab" data-bs-toggle="tab" data-bs-target="#individu-contact" type="button" role="tab" aria-controls="individu-contact" aria-selected="false">Informations de Contact</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="individu-admin-tab" data-bs-toggle="tab" data-bs-target="#individu-admin" type="button" role="tab" aria-controls="individu-admin" aria-selected="false">Informations Administratives</button>
                                </li>
                            </ul>
                            <div class="tab-content mt-3" id="individuTabsContent">
                                <!-- Tab 1: Informations Personnelles -->
                                <div class="tab-pane fade show active" id="individu-general" role="tabpanel" aria-labelledby="individu-general-tab">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label>Nom *</label>
                                            <input type="text" name="nom" class="form-control" placeholder="Nom" >
                                        </div>
                                        <div class="col-md-4">
                                            <label>Prénom *</label>
                                            <input type="text" name="prenom" class="form-control" placeholder="Prénom" >
                                        </div>
                                        <div class="col-md-4">
                                            <label>Date de Naissance </label>
                                            <input type="date" name="date_naissance" id="date_naissance" class="form-control">
                                        </div>
                                        <div class="col-md-4">
                                            <label>Genre</label>
                                            <select name="genre" id="genre" class="form-control">
                                                <option value="">Sélectionnez...</option>
                                                @foreach ($genres as $genre)
                                                <option value="{{ $genre->code_genre }}">{{ $genre->libelle_genre }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4 ">
                                            <label>Situation Matrimoniale :</label>
                                            <select class="form-control" name="situationMatrimoniale" id="situationMatrimoniale">
                                                <option value="">Sélectionnez...</option>
                                                @foreach ($SituationMatrimoniales as $SituationMatrimoniale)
                                                    <option value="{{ $SituationMatrimoniale->id }}">{{ $SituationMatrimoniale->libelle }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label>Pays d'origine *</label>
                                            <lookup-select name="nationnalite" id="nationnalite">
                                                <option value="">Sélectionnez...</option>
                                                @foreach ($tousPays as $tousPay)
                                                    <option value="{{ $tousPay->id }}">{{ $tousPay->nom_fr_fr }}</option>
                                                @endforeach
                                            </lookup-select>
                                        </div>

                                    </div>
                                </div>

                                <!-- Tab 2: Informations de Contact -->
                                <div class="tab-pane fade" id="individu-contact" role="tabpanel" aria-labelledby="individu-contact-tab">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label>Email *</label>
                                            <input type="email" name="emailI" class="form-control" placeholder="Email">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="codePostal">Code postal</label>
                                            <input type="text" name="CodePostalI" id="CodePostal" class="form-control">
                                        </div>
                                        <div class="col-md-4">
                                            <label>Adresse postale</label>
                                            <input type="text" name="AdressePostaleIndividu" class="form-control" placeholder="Adresse">
                                        </div>
                                        <div class="col-md-4">
                                            <label>Adresse siège *</label>
                                            <input type="text" name="adresseSiegeIndividu" class="form-control" placeholder="Adresse">
                                        </div>
                                        <div class="col-md-4">
                                            <label>Téléphone Bureau *</label>
                                            <input type="text" name="telephoneBureauIndividu" class="form-control" placeholder="Téléphone">
                                        </div>
                                        <div class="col-md-4">
                                            <label>Téléphone mobile *</label>
                                            <input type="text" name="telephoneMobileIndividu" class="form-control" placeholder="Téléphone">
                                        </div>
                                    </div>
                                </div>

                                <!-- Tab 3: Informations Administratives -->
                                <div class="tab-pane fade" id="individu-admin" role="tabpanel" aria-labelledby="individu-admin-tab">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label>Pièce d’Identité :</label>
                                            <select class="form-control" name="piece_identite" id="piece_identite">
                                                <option value="">Sélectionner une pièce d'identité</option>
                                                @foreach($Pieceidentite as $Pieceidentit)
                                                <option value="{{ $Pieceidentit->idPieceIdent }}">{{ $Pieceidentit->libelle_long }}</option>
                                                @endforeach

                                            </select>

                                        </div>
                                        <div class="col-md-4">
                                            <label>Numéro Pièce:</label>
                                            <input type="text" class="form-control" name="numeroPiece" placeholder="Numéro de CNI">
                                        </div>
                                        <div class="col-md-4">
                                            <label>Date de etablissement:</label>
                                            <input type="date" class="form-control" name="dateEtablissement" placeholder="Numéro de CNI">
                                        </div>

                                        <div class="col-md-6">
                                            <label>Date de expiration:</label>
                                            <input type="date" class="form-control" name="dateExpiration" placeholder="Numéro de CNI">
                                        </div>
                                        <div class="col-md-6">
                                            <label>Numéro Fiscal </label>
                                            <input type="text" class="form-control" name="numeroFiscal" placeholder="Numéro fiscal">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="row mt-3">
                    <!-- Bouton d'envoi -->
                    <div class="col-md-12 text-end">
                        <button type="submit" class="btn btn-primary mt-2" id="submit-button">Enregistrer</button>
                    </div>
                </div>
            </form>

        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h5>Liste des Acteurs</h5>
        </div>
        <div class="card-body">


            <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="table1">
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Nom complet</th>
                        <th>Nom court</th>
                        <th>Type acteur</th>
                        <th>Pays</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($acteurs as $acteur)
                    <tr>
                        <td>
                            @if ($acteur->Photo)
                            <img src="{{ asset($acteur->Photo) }}"
                            alt="Photo de {{ $acteur->libelle_long }}"
                            style="width: 50px; height: 50px; object-fit: cover;">

                            @else
                                <span>Pas de photo</span>
                            @endif
                        </td>
                        <td class="col-2">{{ $acteur->libelle_long }}</td>
                        <td class="col-2">{{ $acteur->libelle_court }}</td>
                        <td class="col-2">{{ $acteur->type ? $acteur->type->libelle_type_acteur : 'Type non défini' }}</td>
                        <td class="col-2">{{ $acteur->pays ? $acteur->pays->nom_fr_fr : 'Pays non défini' }}</td>
                        <td >{{ $acteur->email }}</td>
                        <td >{{ $acteur->telephone }}</td>
                        <td>@if ($acteur->is_active)
                                <span class="badge bg-success">Actif</span>
                            @else
                                <span class="badge bg-danger">Inactif</span>
                            @endif</td>
                        <td class="col-2">

                            @if ($acteur->is_active)
                                <!-- Désactivation -->
                                <a href="#" class="delete-button" data-id="{{ $acteur->code_acteur }}" title="Supprimer">
                                    <i class="bi bi-x-circle" style="font-size: 1.2rem; color: red; cursor: pointer;"></i>
                                </a>
                            @else
                                <!-- Réactivation -->
                                <a href="#" class="restore-button" data-id="{{ $acteur->code_acteur }}" data-ecran-id="{{ $ecran->id }}" title="Réactiver">
                                    <i class="bi bi-check-circle" style="font-size: 1.2rem; color: green; cursor: pointer;"></i>
                                </a>


                            @endif

                            <!-- Modification -->
                            <a href="#" class="edit-button"
                                data-id="{{ $acteur->code_acteur }}"
                                data-libelle-long="{{ $acteur->libelle_long }}"
                                data-libelle-court="{{ $acteur->libelle_court }}"
                                data-email="{{ $acteur->email }}"
                                data-telephone="{{ $acteur->telephone }}"
                                data-adresse="{{ $acteur->adresse }}"
                                data-type-acteur="{{ $acteur->type_acteur }}"
                                data-pays-nom="{{ $acteur->pays ? $acteur->pays->nom_fr_fr : 'Pays non défini' }}"
                                title="Modifier">
                                <i class="bi bi-pencil-square" style="font-size: 1.2rem; cursor: pointer;"></i>
                            </a>

                            <!-- Formulaire de désactivation -->
                            <form id="delete-form-{{ $acteur->code_acteur }}" action="{{ route('acteurs.destroy', $acteur->code_acteur) }}" method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                            <!-- Formulaire d'activation  -->
                            <form id="restore-form-{{ $acteur->code_acteur }}" action="{{ route('acteurs.restore', ['id' => $acteur->code_acteur, 'ecran_id' => $ecran->id]) }}" method="POST" style="display: none;">
                                @csrf
                                @method('PATCH')
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>

<script>
    $(document).ready(function() {
        initDataTable('{{ auth()->user()?->acteur?->libelle_court }} {{ auth()->user()?->acteur?->libelle_long }}', 'table1', 'Liste des acteurs')
    });
    document.addEventListener('DOMContentLoaded', function () {
        // Gestion de la modification
        document.querySelectorAll('.edit-button').forEach(button => {
            button.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const libelleLong = this.getAttribute('data-libelle-long');
                const libelleCourt = this.getAttribute('data-libelle-court');
                const email = this.getAttribute('data-email');
                const telephone = this.getAttribute('data-telephone');
                const adresse = this.getAttribute('data-adresse');
                const typeActeur = this.getAttribute('data-type-acteur');
                const paysNom = this.getAttribute('data-pays-nom'); // Récupérer le nom du pays

                // Mise à jour des valeurs du formulaire
                document.getElementById('acteur-id').value = id;
                document.getElementById('libelle_long').value = libelleLong;
                document.getElementById('libelle_court').value = libelleCourt;
                document.getElementById('email').value = email;
                document.getElementById('telephone').value = telephone;
                document.getElementById('adresse').value = adresse;
                document.getElementById('type_acteur').value = typeActeur;
                document.getElementById('pays').value = paysNom; // Afficher le nom du pays

                // Mise à jour de l'action du formulaire pour modification
                const form = document.getElementById('acteur-form');
                form.action = `{{ url('/acteurs') }}/${id}`;
                document.getElementById('method').value = 'PUT';

                // Changer le texte du bouton
                document.getElementById('submit-button').textContent = 'Modifier';
            });
        });

        document.querySelectorAll('.restore-button').forEach(button => {
            button.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const ecranId = this.getAttribute('data-ecran-id');

                if (confirm('Êtes-vous sûr de vouloir réactiver cet acteur ?')) {
                    const form = document.createElement('form');
                    form.method = 'POST';

                    form.action = `{{ url('/acteurs')}}/${id}/restore?ecran_id=${ecranId}`;
                    form.innerHTML = `
                        @csrf
                        <input type="hidden" name="_method" value="PATCH">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });


        // Gestion de la suppression
        document.querySelectorAll('.delete-button').forEach(button => {
            button.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                if (confirm('Êtes-vous sûr de vouloir supprimer cet acteur ?')) {
                    document.getElementById(`delete-form-${id}`).submit();
                }
            });
        });



        // Réinitialisation du formulaire pour une nouvelle création
        const form = document.getElementById('acteur-form');
        form.addEventListener('submit', function () {
            if (document.getElementById('method').value === 'POST') {
                form.action = '{{ route("acteurs.store") }}';
            }
        });
    });


</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    // Sélectionner l'input de date de naissance
    var dateNaissanceInput = document.getElementById("date_naissance");

    if (dateNaissanceInput) {
        // Calculer la date minimum autorisée (18 ans en arrière)
        var today = new Date();
        var minDate = new Date(today.getFullYear() - 18, today.getMonth(), today.getDate());

        // Formater la date en YYYY-MM-DD pour l'attribut max
        var formattedDate = minDate.toISOString().split("T")[0];

        // Définir la date max sur l'input
        dateNaissanceInput.setAttribute("max", formattedDate);

        // Vérification au changement de valeur
        dateNaissanceInput.addEventListener("change", function () {
            var selectedDate = new Date(this.value);

            if (selectedDate > minDate) {
                alert("Vous devez avoir au moins 18 ans.");
                this.value = ""; // Réinitialise le champ
            }
        });
    }
});
</script>

<script>
    document.getElementById('photo').addEventListener('change', function(event) {
        const file = event.target.files[0];

        if (file) {
            const reader = new FileReader();

            reader.onload = function(e) {
                const preview = document.getElementById('photo-preview');
                preview.src = e.target.result;
                preview.style.display = 'block';
            };

            reader.readAsDataURL(file); // Lire le fichier comme une URL de données
        }
    });

    document.addEventListener("DOMContentLoaded", function () {
        function togglePersonneFields() {
            const personnePhysique = document.getElementById("personnePhysique");
            const personneMorale = document.getElementById("personneMorale");
            const entrepriseFields = document.getElementById("entrepriseFields");
            const individuFields = document.getElementById("individuFields");

            if (personnePhysique.checked) {
                individuFields.classList.remove("d-none");
                entrepriseFields.classList.add("d-none");
            } else if (personneMorale.checked) {
                entrepriseFields.classList.remove("d-none");
                individuFields.classList.add("d-none");
            }
        }

        document.getElementById("personnePhysique").addEventListener("change", togglePersonneFields);
        document.getElementById("personneMorale").addEventListener("change", togglePersonneFields);
    });

</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const lookup = document.getElementById("nomPC"); // Sélection du lookup-multiselect
        const contactContainer = document.getElementById("contactContainer");
        const acteurs = @json($acteurRepres); // Récupération des contacts depuis Laravel

        function updateContacts() {
            contactContainer.innerHTML = ""; // Vider le contenu

            let selectedValues = lookup.value; // Récupère les valeurs sélectionnées

            if (selectedValues.length === 0) {
                return; // Si aucune sélection, ne rien afficher
            }

            selectedValues.forEach(code => {
                let acteur = acteurs.find(a => a.code_acteur == code);
            // console.log('acteur :',acteur);
                if (acteur) {
                    let row = document.createElement("div");
                    row.classList.add("d-flex", "align-items-center", "me-3");

                    row.innerHTML = `
                        <div class="me-3">
                            <label>Nom</label>
                            <input type="text" class="form-control" value="${acteur.libelle_court} ${acteur.libelle_long}" readonly>
                        </div>
                        <div class="me-3">
                            <label>Email</label>
                            <input type="email" class="form-control" name="emailPC" value="${acteur.email || ''}">
                        </div>
                        <div class="me-3">
                            <label>Téléphone 1</label>
                            <input type="text" class="form-control" name="Tel1Pc" value="${acteur.telephone_mobile || ''}">
                        </div>
                        <div class="me-3">
                            <label>Téléphone 2</label>
                            <input type="text" class="form-control" name="Tel2PC" value="${acteur.telephone_bureau || ''}">
                        </div>
                    `;

                    contactContainer.appendChild(row);
                }
            });
        }

        // Écouter le changement de sélection sur `lookup-multiselect`
        lookup.addEventListener("change", updateContacts);

        // Optionnel : Afficher les données au chargement si des valeurs sont déjà sélectionnées
        setTimeout(updateContacts, 500);
    });

    document.addEventListener("DOMContentLoaded", function () {
        const lookupRL = document.getElementById("nomRL"); // Sélecteur du lookup-select
        const emailRL = document.querySelector("input[name='emailRL']");
        const telephone1RL = document.querySelector("input[name='telephone1RL']");
        const telephone2RL = document.querySelector("input[name='telephone2RL']");

        const acteurs = @json($acteurRepres); // Récupération des acteurs depuis Laravel Blade

        function updateRepresentantLegal() {
            let selectedValue = lookupRL.value; // Récupérer l'ID sélectionné

            // Trouver les données du représentant légal
            let acteur = acteurs.find(a => a.code_acteur == selectedValue);

            if (acteur) {
                emailRL.value = acteur.email || ""; // Mettre à jour l'email
                telephone1RL.value = acteur.telephone_mobile || ""; // Mettre à jour Téléphone 1
                telephone2RL.value = acteur.telephone_bureau || ""; // Mettre à jour Téléphone 2
            } else {
                emailRL.value = ""; // Vider si aucun représentant légal trouvé
                telephone1RL.value = "";
                telephone2RL.value = "";
            }
        }

        // Écouter les changements sur le `lookup-select`
        lookupRL.addEventListener("change", updateRepresentantLegal);

        // Optionnel : Remplir les champs au chargement si une valeur est déjà sélectionnée
        setTimeout(updateRepresentantLegal, 500);

        // Ajouter les champs cachés dynamiques pour conserver les modifications lors du submit
        const form = document.querySelector("form");
        form.addEventListener("submit", function () {
            // Ajouter des champs cachés pour les valeurs modifiées
            let hiddenEmail = document.createElement("input");
            hiddenEmail.type = "hidden";
            hiddenEmail.name = "emailRL_modified";
            hiddenEmail.value = emailRL.value;
            form.appendChild(hiddenEmail);

            let hiddenTel1 = document.createElement("input");
            hiddenTel1.type = "hidden";
            hiddenTel1.name = "telephone1RL_modified";
            hiddenTel1.value = telephone1RL.value;
            form.appendChild(hiddenTel1);

            let hiddenTel2 = document.createElement("input");
            hiddenTel2.type = "hidden";
            hiddenTel2.name = "telephone2RL_modified";
            hiddenTel2.value = telephone2RL.value;
            form.appendChild(hiddenTel2);
        });
    });
</script>


@endsection
