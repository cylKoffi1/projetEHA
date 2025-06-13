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
        }

    /* --- Harmonisation select2 avec Bootstrap --- */
    .select2-container--default .select2-selection--multiple {
        background-color: #fff;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        min-height: 38px;
        height: auto;
        padding: 0.25rem 0.5rem;
        font-size: 13px;
        font-family: inherit;
        box-shadow: none;
        line-height: 1.5;
    }

    /* Evite l'affichage des mini "cards" trop grossiers */
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #dee2e6;
        color: #212529;
        border: none;
        border-radius: 0.25rem;
        padding: 2px 6px;
        margin: 3px 3px 0 0;
        font-size: 0.85rem;
    }

    /* Focus harmonieux */
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.2rem rgba(13,110,253,.25);
    }

    /* Supprime croix trop imposante */
    .select2-container--default .select2-selection__choice__remove {
        color: #6c757d;
        margin-right: 3px;
        font-size: 0.85rem;
    }

    /* Placeholder style */
    .select2-container--default .select2-search--inline .select2-search__field {
        font-size: 13px;
        font-family: inherit;
        margin-top: 0.25rem;
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
                                    var currentDate = new Date();
                                    return currentDate.toLocaleString();
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
                                <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}" name="ecran_id" required>
                                <input type="hidden" id="famille_id_hidden" name="id">
                                <div class="row align-items-end">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="groupeProjet">Groupe projet</label>
                                            <select class="form-control select2-multiple" name="groupeProjet[]" id="groupeProjet" multiple required>
                                            @foreach ($groupeProjets as $groupeProjet)
                                                <option value="{{ $groupeProjet->code }}">{{ $groupeProjet->libelle }}</option>
                                            @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="domaine">Domaine</label>
                                            <select class="form-control select2-multiple" name="domaine[]" id="domaine" multiple required></select>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="SDomaine">Sous domaine</label>
                                            <select class="form-control select2-multiple" name="SDomaine[]" id="SDomaine" multiple ></select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    
                                    <div class="col-md-6">
                                        <div class="form-group mandatory">
                                            <label class="form-label" for="libelle">code famille:</label>
                                            <input type="text" class="form-control" id="code" name="code" placeholder="Code famille" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
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
                <div class="card mb-4" id="caracteristique-form-section" style="display: none;">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Ajouter des caractéristiques</h5>
                        <small><i class="bi bi-tools"></i> Étape 2</small>
                    </div>
                    <div class="card-body">
                        <input type="hidden" name="idFamille" id="famille_id_caracteristique">
                        <input type="hidden" id="indexEdition" value="">

                        <!-- Ligne des champs -->
                        <div class="row g-3 align-items-end">
                            <div class="col-md-2">
                                <label class="form-label">Type</label>
                                <select id="typeCaracteristique" class="form-select">
                                    @foreach($typesCaracteristique as $type)
                                        <option value="{{ $type->idTypeCaracteristique }}">{{ $type->libelleTypeCaracteristique }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Libellé</label>
                                <input type="text" id="libelleCaracteristique" class="form-control" placeholder="Ex: Surface">
                            </div>

                            <div class="col-md-5" id="valeursPossiblesContainer" style="display: none;">
                                <label class="form-label">Valeurs possibles (séparées par des virgules)</label>
                                <input type="text" id="valeursPossibles" class="form-control" placeholder="Ex: Tôle, Tuile, Béton">
                            </div>

                            <div class="col-md-4 uniteFields" style="display: none;">
                                <label class="form-label">Unité</label>
                                <select id="selectUnite" class="form-select">
                                    @foreach($unites as $unite)
                                        <option value="{{ $unite->idUnite }}">{{ $unite->libelleUnite }} ({{ $unite->symbole }})</option>
                                    @endforeach
                                    <option value="autre">Autre...</option>
                                </select>
                            </div>

                            <div class="col-md-4" id="autreUniteFields" style="display: none;">
                                <label class="form-label">Nouvelle unité</label>
                                <div class="input-group">
                                    <input type="text" id="libelleUnite" class="form-control" placeholder="Libellé">
                                    <input type="text" id="symboleUnite" class="form-control" placeholder="Symbole">
                                </div>
                            </div>

                            <div class="col-md-2 text-end">
                                <button type="button" class="btn btn-outline-secondary w-100" onclick="ajouterCaracteristique()">Ajouter</button>
                            </div>
                        </div>

                        <hr>

                        <h5>Caractéristiques définies</h5>
                        <table class="table table-hover table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Libellé</th>
                                    <th>Type</th>
                                    <th>Valeurs possibles</th>
                                    <th>Unité</th>
                                    <th>Symbole</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody id="caracteristiquesTableBody"></tbody>
                        </table>

                        <!-- Formulaire de soumission -->
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

                <div class="card-content">
                    <div class="card-body">
                        <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="table1">
                            <thead>
                                <tr>
                                    <th>Groupe projet</th>
                                    <th>Domaine</th>
                                    <th>Sous domaine</th>
                                    <th>Code</th>
                                    <th>Libelle</th>
                                    <th>action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($familleinfrastructure as $famille)
                                <tr>
                                    <td>
                                        @foreach ($famille->familleDomaine as $fd)
                                            <span class="badge bg-info">{{ $fd->groupeProjet?->libelle ?? '-' }}</span>
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach ($famille->familleDomaine as $fd)
                                            <span class="badge bg-primary">{{ $fd->domaine?->libelle ?? $fd->code_domaine }}</span>
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach ($famille->familleDomaine as $fd)
                                            <span class="badge bg-secondary">{{ $fd->sousdomaine?->lib_sous_domaine ?? $fd->code_sdomaine }}</span>
                                        @endforeach
                                    </td>
                                    <td>{{ $famille->code_famille }}</td>
                                    <td>{{ $famille->libelleFamille }}</td>
                                    <td>
                                        

                                        <button class="btn btn-sm btn-outline-primary btn-action" 
                                            onclick="editFamille(
                                                '{{ $famille->idFamille }}',
                                                @json($famille->familleDomaine->pluck('code_sdomaine')),
                                                '{{ e($famille->code_famille) }}',
                                                '{{ e($famille->libelleFamille) }}',
                                                @json($famille->familleDomaine->pluck('code_domaine')),
                                                @json($famille->familleDomaine->pluck('code_groupe_projet'))
                                            )" 
                                            title="Modifier">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>

                                        <button 
                                            type="button"
                                            class="btn btn-sm btn-outline-danger btn-action" 
                                            title="Supprimer"
                                            onclick="confirmDelete('{{ route('familleinfrastructure.delete', $famille->idFamille) }}', () => window.location.reload())">
                                            <i class="bi bi-trash"></i>
                                        </button>
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
    let caracteristiquesAjoutees = [];

        function afficherTableauCaracteristiques() {
            const tbody = document.getElementById('caracteristiquesTableBody');
            tbody.innerHTML = '';

            caracteristiquesAjoutees.forEach((carac, index) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${carac.libelle}</td>
                    <td>${carac.type_label}</td>
                    <td>${carac.valeurs_possibles || '-'}</td>
                    <td>${carac.unite_libelle || '-'}</td>
                    <td>${carac.unite_symbole || '-'}</td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-outline-warning" onclick="modifierCaracteristique(${index})">Modifier</button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(null, () => supprimerCaracteristique(${index}))">Supprimer</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            document.getElementById('caracteristiques_json').value = JSON.stringify(caracteristiquesAjoutees);
        }


        function supprimerCaracteristique(index) {
            caracteristiquesAjoutees.splice(index, 1);
            afficherTableauCaracteristiques();
        }

        function modifierCaracteristique(index) {
            const carac = caracteristiquesAjoutees[index];

            document.getElementById('libelleCaracteristique').value = carac.libelle;
            document.getElementById('typeCaracteristique').value = carac.type_id;
            document.getElementById('valeursPossibles').value = carac.valeurs_possibles || '';
            document.getElementById('selectUnite').value = carac.unite_id || '';
            document.getElementById('libelleUnite').value = carac.unite_libelle || '';
            document.getElementById('symboleUnite').value = carac.unite_symbole || '';
            document.getElementById('indexEdition').value = index;

            // Affichage conditionnel
            document.getElementById('typeCaracteristique').dispatchEvent(new Event('change'));
            if (carac.unite_id === 'autre') {
                document.getElementById('autreUniteFields').style.display = 'flex';
            }
        }


        function ajouterCaracteristique() {
            const libelle = document.getElementById('libelleCaracteristique').value.trim();
            const typeSelect = document.getElementById('typeCaracteristique');
            const typeId = typeSelect.value;
            const typeLabel = typeSelect.options[typeSelect.selectedIndex].text;

            const carac = {
                libelle: libelle,
                type_id: typeId,
                type_label: typeLabel,
                valeurs_possibles: document.getElementById('valeursPossibles').value.trim(),
                unite_id: document.getElementById('selectUnite').value,
                unite_libelle: document.getElementById('libelleUnite').value.trim(),
                unite_symbole: document.getElementById('symboleUnite').value.trim()
            };

            const editIndex = document.getElementById('indexEdition').value;

            if (editIndex !== '') {
                caracteristiquesAjoutees[editIndex] = carac; // Remplacer
                document.getElementById('indexEdition').value = ''; // Reset
            } else {
                caracteristiquesAjoutees.push(carac); // Ajouter
            }

            afficherTableauCaracteristiques();

            // Réinitialisation
            document.getElementById('libelleCaracteristique').value = '';
            document.getElementById('valeursPossibles').value = '';
            document.getElementById('libelleUnite').value = '';
            document.getElementById('symboleUnite').value = '';
            document.getElementById('selectUnite').value = '';
            document.getElementById('autreUniteFields').style.display = 'none';
        }

    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Initialiser Select2 pour les selects multiples
        $('.select2-multiple').select2({
            placeholder: "Sélectionner une ou plusieurs options",
            width: '100%',
            allowClear: true,
            dropdownAutoWidth: true
        });


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
                document.querySelectorAll('.uniteFields').forEach(el => el.style.display = 'block');
            } else {
                document.querySelectorAll('.uniteFields').forEach(el => el.style.display = 'none');
                document.getElementById('selectUnite').value = '';
                document.getElementById('autreUniteFields').style.display = 'none';
                document.getElementById('libelleUnite').value = '';
                document.getElementById('symboleUnite').value = '';
            }
        });

        // Déclenche le comportement initial
        const initialChangeEvent = new Event('change');
        typeSelect.dispatchEvent(initialChangeEvent);

        // Gestion du formulaire famille
        const form = document.getElementById('familleForm');

        form.addEventListener('submit', function (e) {
            e.preventDefault();

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

        // Gestion du formulaire caractéristiques
        const formcaracteristiques = document.getElementById('form-caracteristiques');

        formcaracteristiques.addEventListener('submit', function (e) {
            e.preventDefault();

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

        // Gestion des dépendances entre selects
        const groupeProjet = document.getElementById('groupeProjet');
        const domaineSelect = document.getElementById('domaine');
        const sousDomaineSelect = document.getElementById('SDomaine');

        const domaineToGroupeProjetMap = new Map();

        // Quand groupe projet change
        $('#groupeProjet').on('change', function() {
            const codeGroupeProjet = $(this).val().join(',');
            //console.log("✅ Groupe projet sélectionné:", codeGroupeProjet);

            // Réinitialiser les selects dépendants
            $('#domaine').empty().append('<option value="">Selectionner le domaine</option>');
            $('#SDomaine').empty().append('<option value="">Selectionner le sous domaine</option>');
            domaineToGroupeProjetMap.clear();

            if (!codeGroupeProjet) return;

            fetch(`{{ url('/') }}/getDomaineByGroupeProjet/${codeGroupeProjet}`)
                .then(response => response.json())
                .then(data => {
                    //console.log("✅ Données reçues pour domaine:", data);

                    if (data.error) {
                        $('#domaine').append(`<option value="">${data.error}</option>`);
                        return;
                    }

                    // Ajouter les options au select domaine
                    data.forEach(d => {
                        domaineToGroupeProjetMap.set(d.code, d.groupe_projet_code);
                        $('#domaine').append(`<option value="${d.code}">${d.libelle}</option>`);
                    });
                })
                .catch(err => {
                    console.error("❌ Erreur fetch domaines:", err);
                    $('#domaine').append('<option value="">Erreur lors du chargement</option>');
                });
        });

        // Quand domaine change
        $('#domaine').on('change', function() {
            const selectedDomains = $(this).val();
            //console.log("📌 Domaines sélectionnés:", selectedDomains);

            // Réinitialiser le select sous-domaine
            $('#SDomaine').empty().append('<option value="">Selectionner le sous domaine</option>');

            if (!selectedDomains || selectedDomains.length === 0) return;

            // Pour chaque domaine sélectionné, charger les sous-domaines
            selectedDomains.forEach(codeDomaine => {
                const groupeProjetCode = domaineToGroupeProjetMap.get(codeDomaine);
                
                if (!codeDomaine || !groupeProjetCode) return;

                //console.log(`🔍 Chargement SD pour domaine ${codeDomaine}, groupe projet ${groupeProjetCode}`);

                fetch(`{{ url('/') }}/get-sous-domaines/${codeDomaine}/${groupeProjetCode}`)
                    .then(res => res.json())
                    .then(data => {
                        //console.log(`✅ Sous-domaines reçus pour ${codeDomaine}:`, data);
                        // Ajouter les options au select sous-domaine
                        data.forEach(sd => {
                            $('#SDomaine').append(`<option value="${sd.code_sous_domaine}">${sd.lib_sous_domaine}</option>`);
                        });
                    })
                    .catch(e => {
                        console.error(`❌ Erreur lors du chargement des sous-domaines pour ${codeDomaine}`, e);
                    });
            });
        });
    });
    </script>

    <script>
    function editFamille(id, codesSousDomaine, codeFamille, libelleFamille, codesDomaine, groupesProjetCode) {
        // Remplir les champs
        document.getElementById('libelle').value = libelleFamille;
        document.getElementById('code').value = codeFamille;
        document.getElementById('famille_id_hidden').value = id;

        // 🎯 Sélectionner les groupes projet
        const groupeProjetSelect = $('#groupeProjet');
        groupeProjetSelect.val(groupesProjetCode).trigger('change');

        // 🔁 Charger les domaines correspondants
        const codeGroupeProjet = groupesProjetCode.join(',');
        fetch(`{{ url('/') }}/getDomaineByGroupeProjet/${codeGroupeProjet}`)
            .then(response => response.json())
            .then(domaines => {
                $('#domaine').empty();

                domaines.forEach(d => {
                    const selected = codesDomaine.includes(d.code) ? 'selected' : '';
                    $('#domaine').append(`<option value="${d.code}" ${selected}>${d.libelle}</option>`);
                });

                return Promise.all(
                    codesDomaine.map(code => {
                        const gpCode = groupesProjetCode[0]; // ou autre logique si plusieurs GP
                        return fetch(`{{ url('/') }}/get-sous-domaines/${code}/${gpCode}`).then(res => res.json());
                    })
                );
            })
            .then(results => {
                $('#SDomaine').empty();

                results.flat().forEach(sd => {
                    const selected = codesSousDomaine.includes(sd.code_sous_domaine) ? 'selected' : '';
                    $('#SDomaine').append(`<option value="${sd.code_sous_domaine}" ${selected}>${sd.lib_sous_domaine}</option>`);
                });

                $('#SDomaine').trigger('change');
            });

        // Action du formulaire
        document.getElementById('familleForm').action = `{{ url('/') }}/familleinfrastructure/${id}/update`;
        document.getElementById('enregistrerFamilleinfrastructure').value = "Modifier";

        // Afficher le formulaire 2 si des caractéristiques sont déjà là
        fetch(`{{ url('/') }}/famille/${id}/caracteristiques`)
            .then(res => res.json())
            .then(data => {
                caracteristiquesAjoutees = data;
                afficherTableauCaracteristiques();
                document.getElementById('caracteristique-form-section').style.display = 'block';
                document.getElementById('famille_id_caracteristique').value = id;
                document.getElementById('idFamilleHidden').value = id;
                document.getElementById('form-caracteristiques').action = `{{url('/')}}/familleinfrastructure/${id}/caracteristiques/update`;
            })
            .catch(err => {
                console.error("Erreur chargement caractéristiques:", err);
            });
    }

    document.getElementById('selectUnite').addEventListener('change', function () {
        if (this.value === 'autre') {
            document.getElementById('autreUniteFields').style.display = 'flex';
        } else {
            document.getElementById('autreUniteFields').style.display = 'none';
            document.getElementById('libelleUnite').value = '';
            document.getElementById('symboleUnite').value = '';
        }
    });

    </script>

    <!-- Initialisation DataTables -->
    <script>
        $(document).ready(function() {
            initDataTable('{{ auth()->user()->acteur?->lieblle_court }} {{ auth()->user()->acteur?->libelle_long }}', 'table1', 'Liste des familles d\'infrastructures');
            //initDataTable('{{ auth()->user()->acteur?->libelle_court }} {{ auth()->user()->acteur?->libelle_long }}', 'caracteristiquesTableBody', 'Liste des caractéristiques');
        });
    </script>
    @endsection