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
            <form id="acteur-form" action="{{ route('acteurs.store') }}" method="POST" enctype="multipart/form-data">
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


                    <!-- Conteneur pour l'aperçu de la photo -->

                    <!-- Libellé court -->
                    <div class="form-group col-md-4">
                        <label for="libelle_court">Libellé court / Nom</label>
                        <input type="text" class="form-control" id="libelle_court" name="libelle_court" required>
                    </div>

                    <!-- Libellé long -->
                    <div class="form-group col-md-4">
                        <label for="libelle_long">Libellé long / Prénoms</label>
                        <input type="text" class="form-control" id="libelle_long" name="libelle_long"  required>
                    </div>

                    <!-- Type d'acteur -->
                    <div class="form-group col-md-4">
                        <label for="type_acteur">Type d'acteur</label>
                        <select class="form-control" id="type_acteur" name="type_acteur" >
                            @foreach ($TypeActeurs as $TypeActeur)
                                <option value="{{ $TypeActeur->cd_type_acteur }}">{{ $TypeActeur->libelle_type_acteur }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mt-3">
                    <!-- Email -->
                    <div class="form-group col-md-4">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                    </div>

                    <!-- Téléphone -->
                    <div class="form-group col-md-4">
                        <label for="telephone">Téléphone</label>
                        <input type="text" class="form-control" id="telephone" name="telephone" placeholder="Téléphone">
                    </div>

                    <!-- Adresse -->
                    <div class="form-group col-md-4">
                        <label for="adresse">Adresse</label>
                        <input type="text" class="form-control" id="adresse" name="adresse" placeholder="Adresse">
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
        initDataTable('{{ auth()->user()->acteur->libelle_court }} {{ auth()->user()->acteur->libelle_long }}', 'table1', 'Liste des acteurs')
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
</script>

@endsection
