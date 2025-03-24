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
                            <li class="breadcrumb-item"><a href="">Paramètre généraux</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Familles d'infrastructures</li>

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
                        <h5 class="card-title"> Liste des familles d'infrastructures</h5>
                    </div>
                    <section id="multiple-column-form">
                        <div class="row match-height">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-content">
                                        <div class="card-body">
                                            <form class="form" method="POST" action="{{ route('familleinfrastructure.store') }}" data-parsley-validate>
                                                @csrf
                                                <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}"  name="ecran_id" required>
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="form-group mandatory">
                                                            <label class="form-label" for="code">Domaine :</label>
                                                            <select class="form-control" id="domaine" name="domaine" placeholder="domaine" required>
                                                                <option value="">Selectionner le domaine</option>
                                                                @foreach ($domaine as $sous_domaine)
                                                                    <option value="{{ $sous_domaine->code }}">{{ $sous_domaine->libelle }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group mandatory">
                                                            <label class="form-label" for="code">Sous domaine :</label>
                                                            <select class="form-control" id="SDomaine" name="SDomaine"  required>
                                                                <option value="">Selectionner le sous domaine</option>
                                                               
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group mandatory">
                                                            <label class="form-label" for="libelle">Libelle famille:</label>
                                                            <input type="text" class="form-control" id="libelle" name="libelle" placeholder="Libelle" required>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="modal-footer">
                                                    <input type="submit" class="btn btn-primary" value="Enregistrer" id="enregistrerFamilleinfrastructure">
                                                </div>
                                            </form>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
                <div class="card-content">
                    <div class="card-body">


                        <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="table1">
                            <thead>
                                <tr>
                                    <th>Domaine</th>
                                    <th>Sous domaine </th>
                                    <th>Libelle</th>
                                    <th>action</th>
                                </tr>
                            </thead>

                            <tbody>
                            @foreach ($familleinfrastructure as $famille)
                            <tr>
                                <td>{{ $famille->domaine?->libelle }}</td>
                                <td>{{ $famille->sousdomaine?->lib_sous_domaine }}</td>
                                <td>{{ $famille->libelleFamille }}</td>
                                <td>
                                    <i class="bi bi-pencil-square text-primary" style="font-size: 1.2rem; cursor: pointer;" 
                                    onclick="editFamille(
                                        '{{ $famille->idFamille }}',
                                        '{{ $famille->code_sdomaine }}',
                                        '{{ $famille->libelleFamille }}',
                                        '{{ $famille->code_domaine }}'
                                    )" 
                                    title="Modifier"></i>

                                    <form method="POST" action="{{ route('familleinfrastructure.delete', $famille->idFamille) }}" style="display: inline;" onsubmit="return confirm('Confirmer la suppression ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="border: none; background: none; padding: 0; margin-left: 8px;">
                                            <i class="bi bi-x-circle" style="font-size: 1.2rem; color: red; cursor: pointer;" title="Supprimer"></i>
                                        </button>
                                    </form>
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
document.addEventListener('DOMContentLoaded', function () {
    const domaineSelect = document.getElementById('domaine');
    const sousDomaineSelect = document.getElementById('SDomaine');

    domaineSelect.addEventListener('change', function () {
        const codeDomaine = this.value;

        // Réinitialiser la liste des sous-domaines
        sousDomaineSelect.innerHTML = '<option value="">Chargement...</option>';

        fetch(`/get-sous-domaines/${codeDomaine}`)
            .then(response => response.json())
            .then(data => {
                sousDomaineSelect.innerHTML = '<option value="">Sélectionner le sous-domaine</option>';
                data.forEach(sd => {
                    sousDomaineSelect.innerHTML += `<option value="${sd.code_sous_domaine}">${sd.lib_sous_domaine}</option>`;
                });
            })
            .catch(() => {
                sousDomaineSelect.innerHTML = '<option value="">Erreur lors du chargement</option>';
            });
    });
});
</script>
<script>
function editFamille(id, codeSousDomaine, libelleFamille, codeDomaine) {
    document.getElementById('libelle').value = libelleFamille;
    document.getElementById('SDomaine').value = codeSousDomaine;
    document.getElementById('domaine').value = codeDomaine;

    // Changer l'action du formulaire
    const form = document.querySelector('form');
    form.action = "{{ route('familleinfrastructure.update') }}";

    // Ajouter un champ caché pour l'ID
    let hidden = document.getElementById('famille_id_hidden');
    if (!hidden) {
        hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'id';
        hidden.id = 'famille_id_hidden';
        form.appendChild(hidden);
    }
    hidden.value = id;
}
</script>

<!-- Your custom JavaScript -->
<script>
    $(document).ready(function() {
        initDataTable('{{ auth()->user()->acteur?->lieblle_court }} {{ auth()->user()->acteur?->libelle_long }}', 'table1', 'Liste des familles d\'infrastructures');
    });
</script>
@endsection
