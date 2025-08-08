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

    .offcanvas.offcanvas-end {
        top: 87px !important;
        width: 90% !important; 
        height: calc(100vh - 90px) !important;
    }
    </style>
<meta name="csrf-token" content="{{ csrf_token() }}">

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
                                    
                                    <div class="col-md-2">
                                        <div class="form-group mandatory">
                                            <label class="form-label" for="libelle">code famille:</label>
                                            <input type="text" class="form-control" id="code" name="code" placeholder="Code famille" required value="{{ $codeFamilleGenere ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-2"></div>
                                    <div class="col-md-8">
                                        <div class="form-group mandatory">
                                            <label class="form-label" for="libelle">Libelle famille:</label>
                                            <input type="text" class="form-control" id="libelle" name="libelle" placeholder="Libelle" required>
                                        </div>
                                    </div>
                                </div>

                                <input type="hidden" name="domaine_mapping" id="domaine_mapping">
                                <div class="modal-footer">
                                    @can('ajouter_ecran_' . $ecran->id)
                                    <input type="submit" class="btn btn-primary" value="Enregistrer" id="enregistrerFamilleinfrastructure">
                                    @endcan
                                </div>
                                
                                <div id="btnCaracteristiqueWrapper" class="mt-3 btn-caracteristiques" style="display: none;">
                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary"
                                        id="btnOpenCaracteristiques"
                                        data-famille-id=""
                                    >
                                        <i class="bi bi-diagram-3"></i> Caractéristiques
                                    </button>
                                </div>


                            </form>
                        </div>
                    </div>                                
                </div>
               
                <!-- ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: -->
                <div class="offcanvas offcanvas-end" tabindex="-1" id="caracteristiqueDrawer" style="background-color: #DBECF8 !important; width: 90%;">
                    <div class="offcanvas-header" style="background-color: #93ceff !important;">
                        <h5 class="offcanvas-title">Caractéristiques de la famille</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
                    </div>
                    <div class="offcanvas-body" id="drawerContent" style="background-color: #DBECF8;">
                        <!-- Messages d'alerte -->
                        <div id="alertContainer"></div>
                        
                        <div class="container">
                            <!-- Panel de création -->
                            <div class="panel">
                                <div class="panel-header">
                                    <h2><i class="fas fa-plus-circle"></i> Créateur de Caractéristiques</h2>
                                    <p>Définissez votre structure hiérarchique</p>
                                </div>
                                <div class="panel-content">
                                    <form id="characteristicForm">
                                        <div class="row">
                                            <div class="col-8">
                                                <div class="form-group">
                                                    <label for="charName">Nom de la caractéristique *</label>
                                                    <input type="text" id="charName" class="form-control" placeholder="Ex: Surface totale" required>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="form-group">
                                                    <label for="charParent">Parent</label>
                                                    <select id="charParent" class="form-select">
                                                        <option value="">🏠 Racine</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label for="charType">Type *</label>
                                                    <select id="charType" class="form-select" required>
                                                        @foreach ($typesCaracteristique as $type)
                                                            <option value="{{ $type->idTypeCaracteristique }}"  data-type="{{ strtolower($type->libelleTypeCaracteristique) }}"> 
                                                                {{ $type->libelleTypeCaracteristique }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="form-group" id="unitSection" style="display: none;">
                                                    <label for="charUnit">Unité</label>
                                                    <select id="charUnit" class="form-select">
                                                        @foreach($unites as $unite)
                                                            <option value="{{ $unite->idUnite }}">{{ $unite->libelleUnite }} ({{ $unite->symbole }})</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="form-group">
                                                    <label for="charOrder">Ordre</label>
                                                    <input type="number" id="charOrder" class="form-control" value="1" min="1">
                                                </div>
                                            </div>
                                        </div>

                                        <div id="selectOptions" class="form-group" style="display: none;">
                                            <label for="charOptions">Options (séparées par des virgules) *</label>
                                            <input type="text" id="charOptions" class="form-control" placeholder="Ex: Option1, Option2, Option3">
                                        </div>

                                        <div class="form-group">
                                            <label for="charDescription">Description</label>
                                            <textarea id="charDescription" class="form-control" rows="3" placeholder="Description détaillée..."></textarea>
                                        </div>

                                        <div class="form-actions">
                                            <button type="submit" class="btn btn-primary" id="mainCharBtn">
                                                <i class="fas fa-plus"></i> Ajouter
                                            </button>
                                            <button type="button" class="btn btn-secondary" onclick="resetForm()">
                                                <i class="fas fa-undo"></i> Réinitialiser
                                            </button>
                                        </div>

                                        <div class="row mt-4">
                                            <div class="col-6">
                                                <button type="button" id="btnSaveStructure" class="btn btn-outline-success w-100">
                                                    💾 Enregistrer
                                                </button>
                                            </div>
                                            <div class="col-6">
                                                <button type="button" id="btnDeleteStructure" class="btn btn-outline-danger w-100">
                                                    🗑 Supprimer tout
                                                </button>
                                            </div>
                                        </div>
                                    </form>

                                    <div class="export-section">
                                        <h4><i class="fas fa-download"></i> Export/Import</h4>
                                        <div class="form-actions">
                                            <button class="btn btn-success" onclick="exportStructure()">
                                                <i class="fas fa-file-export"></i> Exporter JSON
                                            </button>
                                            <input type="file" id="importFile" accept=".json" style="display: none;">
                                            <button class="btn btn-secondary" onclick="document.getElementById('importFile').click()">
                                                <i class="fas fa-file-import"></i> Importer JSON
                                            </button>
                                            <button class="btn btn-danger" onclick="clearAll()">
                                                <i class="fas fa-trash"></i> Tout effacer
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Panel de visualisation -->
                            <div class="panel">
                                <div class="panel-header">
                                    <h2><i class="fas fa-sitemap"></i> Structure Hiérarchique</h2>
                                    <p>Votre arbre de caractéristiques</p>
                                </div>
                                <div class="panel-content">
                                    <div id="treeContainer" class="tree-container">
                                        <div id="emptyState">
                                            <i class="fas fa-tree"></i>
                                            <p>Commencez par ajouter votre première caractéristique</p>
                                        </div>
                                    </div>

                                    <div class="export-section" style="display: none;">
                                        <h4><i class="fas fa-code"></i> Prévisualisation JSON</h4>
                                        <div id="jsonPreview" class="json-display">
                                            {
                                            "message": "Ajoutez des caractéristiques pour voir la structure"
                                            }
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                   

                <!-- ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: -->
                
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
                                    <td>{{ $famille->code_Ssys }}</td>
                                    <td>{{ $famille->libelleFamille }}</td>
                                    <td>
                                        

                                        <button
                                            class="btn btn-sm btn-outline-primary btn-action"
                                            data-id="{{ $famille->idFamille }}"
                                            data-sdomaine='@json($famille->familleDomaine->pluck("code_sdomaine"))'
                                            data-domaine='@json($famille->familleDomaine->pluck("code_domaine"))'
                                            data-groupe='@json($famille->familleDomaine->pluck("code_groupe_projet"))'
                                            data-code="{{ e($famille->code_Ssys) }}"
                                            data-libelle="{{ e($famille->libelleFamille) }}"
                                            onclick="handleEditFamille(this)"
                                            title="Modifier"
                                        >
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
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('mainCharBtn').addEventListener('click', handleCharacteristic);
    });

    document.addEventListener('DOMContentLoaded', function () {
        const btn = document.getElementById('btnOpenCaracteristiques');
        const wrapper = document.getElementById('btnCaracteristiqueWrapper');

        // Attacher un seul listener
        if (btn) {
            btn.addEventListener('click', function () {
                const familleId = this.dataset.familleId;
                if (!familleId) {
                    alert("Erreur au niveau de la famille associée.", 'error');
                    return;
                }

                // Appel de l'API pour récupérer la structure
                fetch(`{{ url('/')}}/famille-infrastructure/${familleId}/structure/data`)
                    .then(response => {
                        if (!response.ok) throw new Error("Erreur lors du chargement des caractéristiques.");
                        return response.json();
                    })
                    .then(data => {
                        console.log('Données complètes reçues:', data);
                        
                        if (data.status === 'success' && Array.isArray(data.data)) {
                            // Nouvelle méthode de traitement
                            characteristics = {};
                            
                            // Fonction récursive pour traiter la hiérarchie
                            function processNode(node) {
                                characteristics[node.id] = node;
                                if (node.children && node.children.length > 0) {
                                    node.children.forEach(child => processNode(child));
                                }
                            }
                            
                            data.data.forEach(rootNode => processNode(rootNode));
                            
                            console.log('Structure transformée:', characteristics);
                            updateTree();
                            updateJsonPreview();
                            updateParentSelect();
                        } else {
                            throw new Error("Structure JSON inattendue");
                        }
                    })
                    .catch(error => {
                        console.error("Erreur détaillée:", error);
                        alert("Erreur : " + error.message);
                    });
            });
        }

        // Fonction à appeler après création ou édition de la famille
        window.showCaracteristiqueButton = function (familleId) {
            if (wrapper && btn) {
                wrapper.style.display = 'block';
                btn.dataset.familleId = familleId;
            }
        };

        $('.select2-multiple').select2({
            placeholder: "Sélectionner une ou plusieurs options",
            width: '100%',
            allowClear: true,
            dropdownAutoWidth: true
        });
        const groupeProjet = document.getElementById('groupeProjet');
        const domaineSelect = document.getElementById('domaine');
        const sousDomaineSelect = document.getElementById('SDomaine');
        const domaineToGroupeProjetMap = new Map();

        $('#groupeProjet').on('change', function () {
            const codeGroupeProjets = $(this).val(); // Tableau de codes
            const joinedCodes = codeGroupeProjets ? codeGroupeProjets.join(',') : '';

            $('#domaine').empty().append('<option value="">Sélectionner le domaine</option>');
            $('#SDomaine').empty().append('<option value="">Sélectionner le sous domaine</option>');
            domaineToGroupeProjetMap.clear();

            if (!joinedCodes) {
                console.warn("⚠️ Aucun groupe projet sélectionné.");
                return;
            }

            console.log(`📡 Appel fetch domaines pour : ${joinedCodes}`);
            fetch(`{{ url('/') }}/getDomaineByGroupeProjet/${joinedCodes}`)
                .then(response => {
                      return response.json();
                })
                .then(data => {
                  
                    if (data.error) {
                        console.warn("⚠️ Erreur côté serveur :", data.error);
                        $('#domaine').append(`<option value="">${data.error}</option>`);
                        return;
                    }

                    data.forEach(d => {
                        domaineToGroupeProjetMap.set(d.code, d.groupe_projet_code);
                        $('#domaine').append(`<option value="${d.code}">${d.libelle}</option>`);
                       });

                    $('#domaine').trigger('change');
                })
                .catch(err => {
                    console.error("❌ Erreur fetch domaines :", err);
                    $('#domaine').append('<option value="">Erreur de chargement</option>');
                });
        });

        $('#domaine').on('change', function () {
            const selectedDomaines = $(this).val(); // Tableau
          
            $('#SDomaine').empty().append('<option value="">Sélectionner le sous domaine</option>');

            if (!selectedDomaines || selectedDomaines.length === 0) {
                console.warn("⚠️ Aucun domaine sélectionné.");
                return;
            }

            selectedDomaines.forEach(codeDomaine => {
                const groupeCode = domaineToGroupeProjetMap.get(codeDomaine);
               
                if (!groupeCode) {
                    console.warn(`⚠️ Aucun groupe projet trouvé pour domaine ${codeDomaine}`);
                    return;
                }

                console.log(`📡 Appel fetch sous-domaines pour ${codeDomaine} / ${groupeCode}`);
                fetch(`{{ url('/') }}/get-sous-domaines/${codeDomaine}/${groupeCode}`)
                    .then(res => res.json())
                    .then(data => {
                        data.forEach(sd => {
                            $('#SDomaine').append(`<option value="${sd.code_sous_domaine}">${sd.lib_sous_domaine}</option>`);
                          });

                        $('#SDomaine').trigger('change');
                    })
                    .catch(e => {
                        console.error(`❌ Erreur sous-domaines pour ${codeDomaine} :`, e);
                        $('#SDomaine').append('<option value="">Erreur de chargement</option>');
                    });
            });
        });

    });
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Gestion du formulaire famille
    const form = document.getElementById('familleForm');

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        console.log("📤 Soumission du formulaire famille...");

        // Récupération des champs multi-select
        const domaines = $('#domaine').val() || [];
        const sousDomaines = $('#SDomaine').val() || [];
        const groupes = $('#groupeProjet').val() || [];
        const domaineToGroupeProjetMap = new Map();

        console.log("📋 Domaines sélectionnés :", domaines);
        console.log("📋 Sous-domaines sélectionnés :", sousDomaines);
        console.log("📋 Groupes projet sélectionnés :", groupes);

        const mapping = [];

        domaines.forEach(codeDomaine => {
            groupes.forEach(gp => {
                const sdFiltres = sousDomaines.filter(sd => sd.startsWith(codeDomaine));
                console.log(`🔍 Sous-domaines pour ${codeDomaine} :`, sdFiltres);

                if (sdFiltres.length === 0) {
                    mapping.push({
                        domaine: codeDomaine,
                        sdomaine: null,
                        groupeProjet: gp
                    });
                    console.log("➕ Mapping (sans sous-domaine) :", { domaine: codeDomaine, groupeProjet: gp });
                } else {
                    sdFiltres.forEach(sd => {
                        mapping.push({
                            domaine: codeDomaine,
                            sdomaine: sd,
                            groupeProjet: gp
                        });
                        console.log("➕ Mapping :", { domaine: codeDomaine, sdomaine: sd, groupeProjet: gp });
                    });
                }
            });
        });



        // Injecter dans le champ caché
        const mappingField = document.getElementById('domaine_mapping');
        mappingField.value = JSON.stringify(mapping);

        console.log("🧪 Mapping JSON final injecté :", mappingField.value);

        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(async res => {
            const contentType = res.headers.get("content-type");
            if (contentType && contentType.includes("application/json")) {
                return res.json();
            } else {
                const text = await res.text();
                throw new Error("Réponse non JSON :\n" + text);
            }
        })
        .then(data => {
            console.log("✅ Réponse JSON reçue :", data);
            alert(data.message || 'Réponse du serveur sans message.');

            if (data.status === 'success') {

            }
        })
        .catch(error => {
            console.error('❌ Erreur fetch / traitement :', error);
            alert(error.message || 'Une erreur est survenue.', 'error');
        });
    });
});

</script>

<script>
    function handleEditFamille(button) {
        const id = button.dataset.id;
        const code = button.dataset.code;
        const libelle = button.dataset.libelle;
        const sousDomaines = JSON.parse(button.dataset.sdomaine || '[]');
        const domaines = JSON.parse(button.dataset.domaine || '[]');
        const groupes = JSON.parse(button.dataset.groupe || '[]');

        editFamille(id, sousDomaines, code, libelle, domaines, groupes);
    }
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

        showCaracteristiqueButton(id);
        document.getElementById('familleForm').action = `{{ route('familleinfrastructure.update', ['id' => '___ID___']) }}`.replace('___ID___', id);
        document.getElementById('enregistrerFamilleinfrastructure').value = "Modifier";


    }
    function openDrawer() {
        document.getElementById('drawer-caracteristiques').classList.add('open');
    }
   

</script>

<!-- Initialisation DataTables -->
<script>
        $(document).ready(function() {
            initDataTable('{{ auth()->user()->acteur?->lieblle_court }} {{ auth()->user()->acteur?->libelle_long }}', 'table1', 'Liste des familles d\'infrastructures');
            
        });
</script>













<!---------------------- DRAWER CARACERISTIQUES ------------------------>
<!------------------------------------------------------------------------->

<style>

    .container {
        padding: 20px;
        background-color: #DBECF8;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
        min-height: calc(100vh - 40px);
    }

    .panel {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        max-height: 90vh;
    }

    .panel-header {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        padding: 20px;
        text-align: center;
    }

    .panel-header h2 {
        font-size: 1.5rem;
        margin-bottom: 5px;
    }

    .panel-content {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #2c3e50;
    }

    .form-control, .form-select {
        border-radius: 8px;
        border: 2px solid #e9ecef;
        padding: 10px;
        transition: border-color 0.3s;
    }

    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    .form-actions {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }

    .btn {
        border-radius: 8px;
        padding: 10px 20px;
        font-weight: 500;
        transition: all 0.3s;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea, #764ba2);
        border: none;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }

    .tree-container {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 15px;
        background: #f8f9fa;
        max-height: 60vh;
        overflow-y: auto;
    }

    .tree-node {
        margin-bottom: 10px;
    }

    .node-item {
        background: white;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }

    .node-item:hover {
        border-color: #667eea;
        transform: translateX(5px);
    }

    .node-item.selected {
        border-color: #667eea;
        background: #e3f2fd;
    }

    .node-header {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .node-icon {
        width: 35px;
        height: 35px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        color: white;
        background: linear-gradient(135deg, #667eea, #764ba2);
    }

    .node-info {
        flex: 1;
    }

    .node-title {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 4px;
    }

    .node-meta {
        font-size: 12px;
        color: #6c757d;
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }

    .node-actions {
        display: flex;
        gap: 5px;
    }

    .node-btn {
        width: 30px;
        height: 30px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        transition: all 0.2s;
    }

    .node-btn:hover {
        transform: scale(1.1);
    }

    .node-btn.add {
        background: #28a745;
        color: white;
    }

    .node-btn.edit {
        background: #ffc107;
        color: #212529;
    }

    .node-btn.delete {
        background: #dc3545;
        color: white;
    }

    .node-children {
        margin-left: 30px;
        margin-top: 10px;
        padding-left: 20px;
        border-left: 2px solid #dee2e6;
    }

    .type-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        background: #e9ecef;
        color: #495057;
    }

    .export-section {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        margin-top: 20px;
    }

    .json-display {
        background: #2d3748;
        color: #e2e8f0;
        padding: 15px;
        border-radius: 8px;
        font-family: 'Courier New', monospace;
        font-size: 12px;
        max-height: 300px;
        overflow-y: auto;
    }

    .alert {
        padding: 10px 15px;
        border-radius: 8px;
        margin-bottom: 15px;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert-danger {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .alert-warning {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }

    #emptyState {
        text-align: center;
        color: #6c757d;
        padding: 40px;
    }

    #emptyState i {
        font-size: 48px;
        margin-bottom: 20px;
        color: #dee2e6;
    }

    .btn-caracteristiques {
        margin-top: 20px;
    }

    @media (max-width: 1200px) {
        .container {
            grid-template-columns: 1fr;
            height: auto;
        }
    }

    .loading {
        opacity: 0.6;
        pointer-events: none;
    }

    .spinner {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #667eea;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Structure de données globale
        let characteristics = {};
        let selectedNode = null;
        let editingId = null;
        let currentFamilleId = null;

        // Types d'icônes
        const typeIcons = {
            'texte': { icon: '📝', color: '#1976d2' },
            'nombre': { icon: '🔢', color: '#7b1fa2' },
            'liste': { icon: '📋', color: '#388e3c' },
            'boolean': { icon: '☑️', color: '#f57c00' },
            'date': { icon: '📅', color: '#c2185b' }
        };

       

        // Génération d'ID unique
        function generateId() {
            return 'char_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        }

        // Validation des données
        function validateForm() {
            const name = document.getElementById('charName').value.trim();
            const type = document.getElementById('charType').value;
            const options = document.getElementById('charOptions').value.trim();
            
            if (!name) {
                alert('Le nom de la caractéristique est obligatoire.', 'warning');
                return false;
            }
            
            if (!type) {
                alert('Le type est obligatoire.', 'warning');
                return false;
            }
            
            // Vérification pour les listes
            const selectedOption = document.getElementById('charType').options[document.getElementById('charType').selectedIndex];
            const typeLabel = selectedOption.dataset.type;
            
            if (typeLabel === 'liste' && !options) {
                alert('Les options sont obligatoires pour le type "Liste".', 'warning');
                return false;
            }
            
            if (typeLabel === 'liste') {
                if (!options.includes(',')) {
                    alert('Veuillez entrer au moins deux options séparées par des virgules.', 'warning');
                    return false;
                }

                const invalidCharsRegex = /[<>;'"{}()=]`/; 

                const optionArray = options.split(',').map(opt => opt.trim());

                for (let opt of optionArray) {
                    if (invalidCharsRegex.test(opt) || opt.toLowerCase().includes('script') ) {
                        alert(`L'option "${opt}" contient des caractères interdits.`, 'danger');
                        return false;
                    }
                }
            }

            
            return true;
        }

        // Ajouter/Modifier une caractéristique
        function handleCharacteristic(event) {
            event.preventDefault();
            
            if (!validateForm()) return;
            
            const id = editingId || generateId();
            const name = document.getElementById('charName').value.trim();
            const parentId = document.getElementById('charParent').value || null;
            const typeSelect = document.getElementById('charType');
            const selectedOption = typeSelect.options[typeSelect.selectedIndex];
            const typeLabel = selectedOption.dataset.type;
            const unit = document.getElementById('charUnit').value || null;
            const order = parseInt(document.getElementById('charOrder').value) || 1;
            const description = document.getElementById('charDescription').value.trim() || null;
            
            // Traitement des options
            const optionsValue = document.getElementById('charOptions').value.trim();
            const options = optionsValue ? optionsValue.split(',').map(opt => opt.trim()).filter(opt => opt !== '') : null;
            
            // Créer/Mettre à jour la caractéristique
            characteristics[id] = {
                id: id,
                name: name,
                type: typeSelect.value,
                typeLabel: typeLabel,
                parentId: parentId,
                options: options,
                unit: unit,
                order: order,
                description: description,
                children: characteristics[id]?.children || []
            };
            
            // Mise à jour de l'interface
            updateParentSelect();
            updateTree();
            updateJsonPreview();
            
            // Message de succès
            alert(editingId ? 'Caractéristique modifiée avec succès!' : 'Caractéristique ajoutée avec succès!', 'success');
            
            // Réinitialiser le formulaire
            resetForm();
        }
        function normalizeTree(node) {
                node.children = Array.isArray(node.children) ? node.children : [];
                node.options = Array.isArray(node.options) ? node.options : [];

                node.children.forEach(normalizeTree);
            }

        // Réinitialiser le formulaire
        function resetForm() {
            document.getElementById('characteristicForm').reset();
            document.getElementById('charOrder').value = 1;
            document.getElementById('selectOptions').style.display = 'none';
            document.getElementById('unitSection').style.display = 'none';
            
            const btn = document.getElementById('mainCharBtn');
            btn.innerHTML = '<i class="fas fa-plus"></i> Ajouter';
            
            editingId = null;
            selectedNode = null;
            
            // Mettre à jour l'ordre automatiquement
            updateOrderForParent();
        }

        // Mettre à jour l'ordre automatiquement
        function updateOrderForParent() {
            const parentId = document.getElementById('charParent').value;
            
            if (!parentId) {
                // Racine : compter les enfants racines
                const rootCount = Object.values(characteristics).filter(c => !c.parentId).length;
                document.getElementById('charOrder').value = rootCount + 1;
            } else {
                // Enfant : compter les enfants du parent
                const childCount = Object.values(characteristics).filter(c => c.parentId === parentId).length;
                document.getElementById('charOrder').value = childCount + 1;
            }
        }

        // Mettre à jour la liste des parents
        function updateParentSelect() {
            const select = document.getElementById('charParent');
            const currentValue = select.value;
            
            select.innerHTML = '<option value="">🏠 Racine</option>';
            
            Object.values(characteristics).forEach(char => {
                if (char.id !== editingId) {
                    const option = document.createElement('option');
                    option.value = char.id;
                    option.textContent = `${char.name}`;
                    select.appendChild(option);
                }
            });
            
            select.value = currentValue;
        }

        // Construire la hiérarchie
        function buildHierarchy() {
            const roots = [];
            const childrenMap = {};
            
            // Initialiser la map des enfants
            Object.values(characteristics).forEach(char => {
                childrenMap[char.id] = [];
            });
            
            // Construire les relations parent-enfant
            Object.values(characteristics).forEach(char => {
                if (char.parentId && characteristics[char.parentId]) {
                    childrenMap[char.parentId].push(char);
                } else {
                    roots.push(char);
                }
            });
            
            // Trier les enfants par ordre
            Object.keys(childrenMap).forEach(parentId => {
                childrenMap[parentId].sort((a, b) => a.order - b.order);
            });
            
            // Attacher les enfants triés
            Object.values(characteristics).forEach(char => {
                char.children = childrenMap[char.id] || [];
            });
            
            return roots.sort((a, b) => a.order - b.order);
        }

        // Mettre à jour l'arbre
        function updateTree() {
            const container = document.getElementById('treeContainer');
            const emptyState = document.getElementById('emptyState');

            if (Object.keys(characteristics).length === 0) {
                if (emptyState) {
                    emptyState.style.display = 'block';
                } else {
                    container.innerHTML = '<div id="emptyState"><i class="fas fa-tree"></i><p>Commencez par ajouter votre première caractéristique</p></div>';
                }
                return;
            }

            if (emptyState) emptyState.style.display = 'none';
            container.innerHTML = '';

            const hierarchy = buildHierarchy();
            hierarchy.forEach(root => {
                container.appendChild(createNodeElement(root));
            });
        }


        // Créer un élément de nœud
        function createNodeElement(char, level = 0) {
            const nodeDiv = document.createElement('div');
            nodeDiv.className = 'tree-node';
            
            const typeInfo = typeIcons[char.typeLabel] || { icon: '❓', color: '#6c757d' };
          
            nodeDiv.innerHTML = `
                <div class="node-item ${selectedNode === char.id ? 'selected' : ''}" onclick="selectNode('${char.id}')">
                    <div class="node-header">
                        <div class="node-icon">
                            ${typeInfo.icon}
                        </div>
                        <div class="node-info">
                            <div class="node-title">${char.name}</div>
                            <div class="node-meta">
                                <span class="type-badge">Ordre: ${char.order}</span>
                                <span class="type-badge">Type: ${char.typeLabel}</span>
                                ${char.options ? `<span class="type-badge">Options: ${char.options.length}</span>` : ''}
                            </div>
                        </div>
                        <div class="node-actions">
                            <button class="node-btn add" onclick="event.stopPropagation(); addChildTo('${char.id}')" title="Ajouter enfant">
                                <i class="fas fa-plus"></i>
                            </button>
                            <button class="node-btn edit" onclick="event.stopPropagation(); editNode('${char.id}')" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="node-btn delete" onclick="event.stopPropagation(); deleteNode('${char.id}')" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    ${char.description ? `<div style="margin-top: 8px; font-size: 12px; color: #6c757d; font-style: italic;">${char.description}</div>` : ''}
                </div>
            `;
            
            // Ajouter les enfants
            if (char.children && char.children.length > 0) {
                const childrenDiv = document.createElement('div');
                childrenDiv.className = 'node-children';
                
                char.children.forEach(child => {
                    childrenDiv.appendChild(createNodeElement(child, level + 1));
                });
                
                nodeDiv.appendChild(childrenDiv);
            }
            
            return nodeDiv;
        }

        // Sélectionner un nœud
        function selectNode(id) {
            selectedNode = selectedNode === id ? null : id;
            updateTree();
        }

        // Ajouter un enfant à un nœud
        function addChildTo(parentId) {
            document.getElementById('charParent').value = parentId;
            updateOrderForParent();
            document.getElementById('charName').focus();
        }

        // Éditer un nœud
        function editNode(id) {
            const node = characteristics[id];
            if (!node) {
                alert('Caractéristique non trouvée', 'danger');
                return;
            }
            
            editingId = id;
            
            document.getElementById('charName').value = node.name;
            document.getElementById('charType').value = node.type;
            document.getElementById('charParent').value = node.parentId || '';
            document.getElementById('charOptions').value = node.options ? node.options.join(', ') : '';
            document.getElementById('charUnit').value = node.unit || '';
            document.getElementById('charOrder').value = node.order || 1;
            document.getElementById('charDescription').value = node.description || '';
            
            // Déclencher l'affichage conditionnel
            document.getElementById('charType').dispatchEvent(new Event('change'));
            
            // Changer le texte du bouton
            const btn = document.getElementById('mainCharBtn');
            btn.innerHTML = '<i class="fas fa-save"></i> Modifier';
            
            // Faire défiler vers le formulaire
            document.getElementById('charName').focus();
        }

        // Supprimer un nœud
        function deleteNode(id) {
            const node = characteristics[id];
            if (!node) return;

            Swal.fire({
                title: 'Supprimer la caractéristique ?',
                text: 'Cette action supprimera aussi tous les enfants liés.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler',
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6'
            }).then((result) => {
                if (result.isConfirmed) {
                    function deleteRecursive(nodeId) {
                        const current = characteristics[nodeId];
                        if (current && Array.isArray(current.children)) {
                            current.children.forEach(child => {
                                deleteRecursive(child.id);
                            });
                        }
                        delete characteristics[nodeId];
                    }

                    deleteRecursive(id);

                    updateParentSelect();
                    updateTree();
                    updateJsonPreview();

                    if (editingId === id) {
                        resetForm();
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Supprimé',
                        text: 'Caractéristique supprimée avec succès.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });
        }


        // Mettre à jour l'aperçu JSON
        function updateJsonPreview() {
            const hierarchy = buildHierarchy();
            const jsonPreview = document.getElementById('jsonPreview');
            
            if (hierarchy.length === 0) {
                jsonPreview.textContent = '{\n  "message": "Ajoutez des caractéristiques pour voir la structure"\n}';
            } else {
                jsonPreview.textContent = JSON.stringify(hierarchy, null, 2);
            }
        }

        // Exporter la structure au format JSON
        function exportStructure() {
            const hierarchy = buildHierarchy();
            const json = JSON.stringify(hierarchy, null, 2);
            const blob = new Blob([json], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = 'structure_caracteristiques.json';
            link.click();
            URL.revokeObjectURL(url);
        }

        // Importer une structure JSON
        document.getElementById('importFile').addEventListener('change', function (event) {
            const file = event.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function (e) {
                try {
                    const data = JSON.parse(e.target.result);
                    flattenStructure(data);
                    updateParentSelect();
                    updateTree();
                    updateJsonPreview();
                    alert('Structure importée avec succès !', 'success');
                } catch (err) {
                    alert('Erreur lors de l’importation du fichier JSON.', 'danger');
                    console.error(err);
                }
            };
            reader.readAsText(file);
        });

        // Aplatir la structure hiérarchique en dictionnaire
        function flattenStructure(nodes, parentId = null) {
            nodes.forEach(node => {
                const id = generateId();
                const flattenedNode = {
                    id: id,
                    name: node.name,
                    type: node.type,
                    typeLabel: node.typeLabel,
                    parentId: parentId,
                    options: node.options || null,
                    unit: node.unit || null,
                    order: node.order || 1,
                    description: node.description || '',
                    children: []
                };
                characteristics[id] = flattenedNode;

                if (node.children && node.children.length > 0) {
                    flattenStructure(node.children, id);
                }
            });
        }

        // Tout effacer
        function clearAll() {
            Swal.fire({
                title: 'Réinitialiser la structure ?',
                text: 'Toutes les caractéristiques seront supprimées de la vue (non enregistrées).',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Oui, tout effacer',
                cancelButtonText: 'Annuler',
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6'
            }).then((result) => {
                if (result.isConfirmed) {
                    characteristics = {};
                    editingId = null;
                    selectedNode = null;
                    updateParentSelect();
                    updateTree();
                    updateJsonPreview();

                    Swal.fire({
                        icon: 'success',
                        title: 'Réinitialisé',
                        text: 'Structure entièrement réinitialisée.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });
        }


        // Sauvegarde (simulation - à adapter avec votre backend Laravel)
        document.getElementById('btnSaveStructure').addEventListener('click', function () {
            const structure = buildHierarchy();
            // ✅ Normalisation ici pour éviter children=null ou options=null
            structure.forEach(normalizeTree);
            const familleId = document.getElementById('famille_id_hidden').value;
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch(`{{ url('/') }}/famille-infrastructure/${familleId}/structure/save`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ structure: structure })
            })
                .then(res => res.json())
                .then(data => {
                    alert(data.message || 'Structure enregistrée avec succès !', 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500); // attends 1.5 seconde avant de recharger
                })
                .catch(err => {
                    alert('Erreur lors de l’enregistrement.', 'danger');
                    console.error(err);
                });
        });

        // Supprimer toute la structure (exemple Laravel)
        document.getElementById('btnDeleteStructure').addEventListener('click', function () {
            const familleId = document.getElementById('famille_id_hidden').value;

            const deleteUrl = `{{ url('/')}}/famille-infrastructure/${familleId}/structure/delete`;

            confirmDelete(deleteUrl, function () {
                clearAll(); // vider l’arbre localement
                window.location.reload(); // recharge après suppression
            }, {
                title: "Supprimer toutes les caractéristiques ?",
                text: "Cette action supprimera toute la structure actuelle.",
                confirmButtonText: "Oui, tout supprimer",
                cancelButtonText: "Annuler",
                successMessage: "Toutes les caractéristiques ont été supprimées avec succès.",
                errorMessage: "Erreur lors de la suppression des caractéristiques."
            });
        });


        // Gestion du type de champ (afficher unité ou options)
        document.getElementById('charType').addEventListener('change', function () {
            const type = this.options[this.selectedIndex].dataset.type;
            const showOptions = (type === 'liste');
            const showUnit = (type === 'nombre');

            document.getElementById('selectOptions').style.display = showOptions ? 'block' : 'none';
            document.getElementById('unitSection').style.display = showUnit ? 'block' : 'none';
        });

        // Lier l’événement au bouton "Ajouter" du formulaire
        document.getElementById('characteristicForm').addEventListener('submit', handleCharacteristic);

        // Ouvrir le drawer
        document.getElementById('btnOpenCaracteristiques').addEventListener('click', function () {
            const drawer = new bootstrap.Offcanvas(document.getElementById('caracteristiqueDrawer'));
            drawer.show();

            currentFamilleId = this.dataset.familleId || document.getElementById('famille_id_hidden').value;
        });

        // Initialisation de l'ordre quand on change de parent
        document.getElementById('charParent').addEventListener('change', updateOrderForParent);

        // Initialiser vue au chargement
        document.addEventListener('DOMContentLoaded', function () {
            updateParentSelect();
            updateTree();
            updateJsonPreview();
        });

    </script>
@endsection