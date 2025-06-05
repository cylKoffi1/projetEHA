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
<script>



let caracteristiquesAjoutees = [];

function afficherTableauCaracteristiques() {
    const tbody = document.querySelector('#caracteristiquesTable tbody');
    tbody.innerHTML = '';

    caracteristiquesAjoutees.forEach((carac, index) => {
        const valeurs = Array.isArray(carac.valeurs_possibles) ? carac.valeurs_possibles.join(', ') : (carac.valeurs_possibles || '-');
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${carac.libelle}</td>
            <td>${carac.type_label}</td>
            <td>${valeurs}</td>
            <td>${carac.unite_libelle || '-'}</td>
            <td>${carac.unite_symbole || '-'}</td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-warning btn-action"  onclick="modifierCaracteristique(${index})"><i class="bi bi-pencil-square"></i></button>
                <button type="button" class="btn btn-sm btn-outline-danger btn-action"  onclick="supprimerCaracteristique(${index})"><i class="bi bi-trash"></i></button>
            </td>
        `;

        tbody.appendChild(tr);
    });

    document.getElementById('caracteristiques_json').value = JSON.stringify(caracteristiquesAjoutees);
}

function supprimerCaracteristique(index) {
    const carac = caracteristiquesAjoutees[index];

    if (carac.id) {
        fetch(`{{ url('/')}}/famille/caracteristique/${document.getElementById('idFamilleHidden').value}/${carac.id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                caracteristiquesAjoutees.splice(index, 1);
                afficherTableauCaracteristiques();
            } else {
                alert(data.message || "Échec de la suppression.", 'error');
            }
        });
    } else {
        caracteristiquesAjoutees.splice(index, 1);
        afficherTableauCaracteristiques();
    }
}

function modifierCaracteristique(index) {
    const carac = caracteristiquesAjoutees[index];

    document.getElementById('libelleCaracteristique').value = carac.libelle;
    document.getElementById('typeCaracteristique').value = carac.type_id;
    document.getElementById('valeursPossibles').value = carac.valeurs_possibles;
    document.getElementById('libelleUnite').value = carac.unite_libelle;
    document.getElementById('symboleUnite').value = carac.unite_symbole;
    
    
    document.getElementById('typeCaracteristique').dispatchEvent(new Event('change'));

    // Supprimer temporairement l'ancienne version (sera ré-enregistrée à la prochaine soumission)
    supprimerCaracteristique(index);
}
function ajouterCaracteristique() {
        const libelle = document.getElementById('libelleCaracteristique').value.trim();
        const typeId = document.getElementById('typeCaracteristique').value;
        const typeLabel = document.getElementById('typeCaracteristique').selectedOptions[0].text;
        const valeurs = document.getElementById('valeursPossibles').value.trim();

        if (!libelle) {
            alert('Veuillez entrer un libellé de caractéristique.','warning');
            return;
        }

        caracteristiquesAjoutees.push({
            libelle: libelle,
            type_id: typeId,
            type_label: typeLabel,
            valeurs_possibles: valeurs,
            unite_libelle: document.getElementById('libelleUnite').value.trim(),
            unite_symbole: document.getElementById('symboleUnite').value.trim()
        });


        afficherTableauCaracteristiques();
        document.getElementById('libelleCaracteristique').value = '';
        document.getElementById('valeursPossibles').value = '';
        document.getElementById('libelleUnite').value = '';
        document.getElementById('symboleUnite').value = '';

    }

</script>
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
                    <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Gestion des infrastructures </h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="">Familles d'infrastructures</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Liste des familles</li>

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
                <div class="card-header" style="display: flex; width: 100%; justify-content: space-between; align-items: center;">
                    <h5 class="card-title"> Liste des familles d'infrastructures</h5>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <form id="familleForm" class="form" method="POST" action="{{ route('familleinfrastructure.store') }}" data-parsley-validate>
                            @csrf
                            <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}"  name="ecran_id" required>
                            <input type="hidden" id="famille_id_hidden" name="id">

                            <div class="row">
                                <div class="col-md-3">
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
                                <div class="col-md-3">
                                    <div class="form-group mandatory">
                                        <label class="form-label" for="code">Sous domaine :</label>
                                        <select class="form-control" id="SDomaine" name="SDomaine"  required>
                                            <option value="">Selectionner le sous domaine</option>
                                            
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mandatory">
                                        <label class="form-label" for="libelle">code famille:</label>
                                        <input type="text" class="form-control" id="code" name="code" placeholder="Code famille" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
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
            <div class="card" id="caracteristique-form-section" style="display:none;">
                <div class="card-header">
                    <h5>Définir les caractéristiques de la famille</h5>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <input type="hidden" name="idFamille" id="famille_id_caracteristique">
                        <div class="row">
                            <div class="col-md-2">
                                <label>Type de caractéristique</label>
                                <select id="typeCaracteristique" class="form-control">
                                    @foreach($typesCaracteristique as $type)
                                        <option value="{{ $type->idTypeCaracteristique }}">{{ $type->libelleTypeCaracteristique }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label>Libellé de la caractéristique</label>
                                <input type="text" id="libelleCaracteristique" class="form-control" placeholder="Ex: Nombre de pièces">
                            </div>

                            <div class="col-md-6" id="valeursPossiblesContainer" style="display: none;">
                                <label>Valeurs possibles <span style="color: red;">(séparées par des virgules)</span></label>
                                <input type="text" id="valeursPossibles" class="form-control" placeholder="Ex: Tôle, Tuile, Béton">
                            </div>

                            <div class="col-md-3 uniteFields" style="display: none;">
                                <label>Libellé de l’unité</label>
                                <input type="text" id="libelleUnite" class="form-control" placeholder="Ex: Mètre">
                            </div>
                            <div class="col-md-3 uniteFields" style="display: none;">
                                <label>Symbole</label>
                                <input type="text" id="symboleUnite" class="form-control" placeholder="Ex: m">
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col text-end">
                                <button type="button" class="btn btn-sm btn-secondary" onclick="ajouterCaracteristique()">Ajouter</button>
                            </div>
                        </div>

                        <hr>

                        <h5>Caractéristiques définies</h5>
                        <table  class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="caracteristiquesTable">
                            <thead>
                                <tr>
                                    <th>Libellé</th>
                                    <th>Type</th>
                                    <th>Valeurs possibles</th>
                                    <th>Unité</th>
                                    <th>Symbole</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>

                        <form method="POST" id="form-caracteristiques" action="{{ route('familleinfrastructure.caracteristiques.store') }}">
                            @csrf
                            <input type="hidden" name="idFamille" id="idFamilleHidden">

                            <input type="hidden" name="caracteristiques_json" id="caracteristiques_json">

                            <div class="d-flex justify-content-end mt-3">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle me-1"></i> Enregistrer les caractéristiques
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card-content">
                <div class="card-body">


                    <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="table1">
                        <thead>
                            <tr>
                                <th>Domaine</th>
                                <th>Sous domaine </th>
                                <th>Code</th>
                                <th>Libelle</th>
                                <th>action</th>
                            </tr>
                        </thead>

                        <tbody>
                        @foreach ($familleinfrastructure as $famille)
                        <tr>
                            <td>{{ $famille->domaine?->libelle }}</td>
                            <td>{{ $famille->sousdomaine?->lib_sous_domaine }}</td>
                            <td>{{ $famille->code_famille }}</td>
                            <td>{{ $famille->libelleFamille }}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary btn-action" 
                                    onclick="editFamille(
                                        '{{ $famille->idFamille }}',
                                        '{{ $famille->code_sdomaine }}',
                                        '{{ $famille->code_famille }}',
                                        '{{ $famille->libelleFamille }}',
                                        '{{ $famille->code_domaine }}'
                                    )" 
                                    title="Modifier">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                
                                <form method="POST" action="{{ route('familleinfrastructure.delete', $famille->idFamille) }}" style="display: inline;" onsubmit="return confirm('Confirmer la suppression ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger btn-action" title="Supprimer">
                                        <i class="bi bi-trash"></i>
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
</section>

<script>
    if (!libelle) {
        alert('Veuillez entrer un libellé.', 'warning');
        return;
    }

    if (typeLabel === 'nombre') {
        if (!document.getElementById('libelleUnite').value || !document.getElementById('symboleUnite').value) {
            alert('Veuillez renseigner l’unité (libellé et symbole).', 'warning');
            return;
        }
    }










</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const typeSelect = document.getElementById('typeCaracteristique');
    const valeursDiv = document.getElementById('valeursPossiblesContainer');
    const uniteFields = document.querySelectorAll('.uniteFields');

    typeSelect.addEventListener('change', function () {
        const selectedLabel = this.options[this.selectedIndex].text.toLowerCase();

        // Affiche les valeurs possibles si "liste"
        if (selectedLabel === 'liste') {
            valeursDiv.style.display = 'block';
        } else {
            valeursDiv.style.display = 'none';
        }

        // Affiche les unités si "nombre"
        if (selectedLabel === 'nombre') {
            uniteFields.forEach(el => el.style.display = 'block'); // ou 'block' si flex ne fonctionne pas
        } else {
            uniteFields.forEach(el => el.style.display = 'none');
        }
    });

    // Déclenche le comportement initial basé sur la valeur actuelle (utile en cas de modification)
    const initialChangeEvent = new Event('change');
    typeSelect.dispatchEvent(initialChangeEvent);
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('familleForm');

    form.addEventListener('submit', function (e) {
        e.preventDefault(); // empêcher la soumission normale

        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                // Afficher le second formulaire
                document.getElementById('famille_id_caracteristique').value = data.idFamille;
                document.getElementById('idFamilleHidden').value = data.idFamille;
                document.getElementById('caracteristique-form-section').style.display = 'block';
            } else {
                alert(data.message || 'Une erreur est survenue.', 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur inattendue lors de la soumission.', 'error');
        });
    });
});
    document.addEventListener('DOMContentLoaded', function () {
        const formcaracteristiques = document.getElementById('form-caracteristiques');

        formcaracteristiques.addEventListener('submit', function (e) {
            e.preventDefault(); // empêcher la soumission normale

            const formData = new FormData(formcaracteristiques);
            

            fetch(formcaracteristiques.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);

                if (data.status === 'success') {
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error(error);
                alert('Une erreur réseau est survenue.', 'error');
            });
        });
    });
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const domaineSelect = document.getElementById('domaine');
    const sousDomaineSelect = document.getElementById('SDomaine');

    domaineSelect.addEventListener('change', function () {
        const codeDomaine = this.value;

        // Réinitialiser la liste des sous-domaines
        sousDomaineSelect.innerHTML = '<option value="">Chargement...</option>';

        fetch(`{{ url('/')}}/get-sous-domaines/${codeDomaine}`)
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
function editFamille(id, codeSousDomaine, codeFamille, libelleFamille, codeDomaine) {
    const libelleInput = document.getElementById('libelle');
    const codeInput = document.getElementById('code');
    const domaineSelect = document.getElementById('domaine');
    const sousDomaineSelect = document.getElementById('SDomaine');
    const familleIdHidden = document.getElementById('famille_id_hidden');

    // Remplir les champs de base
    if (libelleInput) libelleInput.value = libelleFamille;
    if (codeInput) codeInput.value = codeFamille;
    if (domaineSelect) domaineSelect.value = codeDomaine;
    if (familleIdHidden) familleIdHidden.value = id;

    // Charger les sous-domaines liés au domaine sélectionné
    fetch(`{{ url('/') }}/get-sous-domaines/${codeDomaine}`)
        .then(response => response.json())
        .then(data => {
            // Nettoyer les options
            sousDomaineSelect.innerHTML = '<option value="">Sélectionner le sous-domaine</option>';

            // Ajouter les nouvelles options
            data.forEach(sd => {
                const option = document.createElement('option');
                option.value = sd.code_sous_domaine;
                option.text = sd.lib_sous_domaine;

                if (sd.code_sous_domaine === codeSousDomaine) {
                    option.selected = true; // Sélectionner automatiquement
                }

                sousDomaineSelect.appendChild(option);
            });
        })
        .catch(() => {
            sousDomaineSelect.innerHTML = '<option value="">Erreur lors du chargement</option>';
        });

    // Afficher le formulaire des caractéristiques
    fetch(`{{ url('/')}}/famille/${id}/caracteristiques`)
        .then(res => res.json())
        .then(data => {
            caracteristiquesAjoutees = data;
            afficherTableauCaracteristiques();

            document.getElementById('caracteristique-form-section').style.display = 'block';
            document.getElementById('famille_id_caracteristique').value = id;
            document.getElementById('idFamilleHidden').value = id;
        })
        .catch(error => {
            console.error('Erreur lors du chargement des caractéristiques :', error);
            alert("Impossible de charger les caractéristiques associées.",'error');
        });

    // Mettre à jour l'action du formulaire
    const form = document.getElementById('familleForm');
    if (form) {
        form.action = "{{ route('familleinfrastructure.store') }}";
    }
}
</script>

<!-- Your custom JavaScript -->
<script>
    $(document).ready(function() {
        initDataTable('{{ auth()->user()->acteur?->lieblle_court }} {{ auth()->user()->acteur?->libelle_long }}', 'table1', 'Liste des familles d\'infrastructures');
        initDataTable('{{ auth()->user()->acteur?->libelle_court }} {{ auth()->user()->acteur?->libelle_long }}', 'caracteristiquesTable', 'Liste des caractéristiques');
    });
</script>
@endsection
