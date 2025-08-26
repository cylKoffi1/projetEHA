<div class="step" id="step-2">
    @isset($ecran)
    @can("consulter_ecran_" . $ecran->id)
    <div class="tab-content mt-3" id="tabContent">
        <!-- Infrastructure Form -->
        <div class="tab-pane fade show active" id="infrastructures" role="tabpanel">
            <h5 class="text-secondary">🏗️ Infrastructures</h5>

            <!-- Formulaire principal -->
            <div id="infrastructureForm">
                <div class="row">
                    <div class="col-4">
                        <label>Pays *</label>
                        @foreach ($Pays as $alpha3 => $nom_fr_fr)
                            <input type="text" value="{{ $nom_fr_fr }}" id="paysSelect1" class="form-control" readonly>
                            <input type="hidden" value="{{ $alpha3 }}" id="paysSelect" class="form-control" readonly>
                        @endforeach
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-3">
                        <label id="niveau1Label">Localité *</label>
                        <lookup-select id="niveau1Select">
                            <option value="">Sélectionnez une localité</option>
                        </lookup-select>
                    </div>
                    <div class="col-md-3">
                        <label id="niveau2Label">Niveau</label>
                        <select class="form-control" id="niveau2Select" disabled>
                            <option value="">Sélectionnez un niveau</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label id="niveau3Label">Découpage</label>
                        <select class="form-control" id="niveau3Select" disabled>
                            <option value="">Sélectionnez un niveau</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <label>Famille d'Infrastructure *</label>
                        <select class="form-control" id="FamilleInfrastruc">
                            <option value="">Sélectionnez une famille</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Quantité *</label>
                        <input type="number" class="form-control" id="quantiteInfras" min="1" value="1">
                    </div>
                    <div class="col-md-4" id="infrastructureNameContainer">
                        <label>Nom de l'infrastructure *</label>
                        <input type="text" class="form-control" id="infrastructureName" placeholder="Nom de l'infrastructure">
                    </div>


                    <div class="col-md-3">
                        <div class="d-flex align-items-end h-100">
                            @can("ajouter_ecran_" . $ecran->id)
                            <button type="button" class="btn btn-outline-success me-2" id="addInfrastructureBtn">
                                <i class="fas fa-plus"></i> Ajouter Infrastructure
                            </button>
                            @endcan
                        </div>
                    </div>
                    
                </div>
                <br>
                <div class="row">
                    <div class="col-9"></div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-end h-100">
                            <button type="button" class="btn btn-outline-secondary" id="resetFormBtn">
                                <i class="fas fa-refresh"></i> Réinitialiser
                            </button>
                        </div>
                    </div>
                </div>
                <!-- Ajout de caractéristiques -->
                <div id="caracteristiquesContainer" class="row mt-3" style="display: none;">
                    <div class="col-md-12">
                        <label class="form-label">Caractéristiques de la famille</label>
                        <div id="caracteristiquesFields" class="row g-2"></div>
                    </div>
                </div>

                
            </div>

            <hr class="mt-4">

            <!-- Liste finale des infrastructures -->
            <div class="row">
                <div class="col">
                    <h6>Infrastructures ajoutées :</h6>
                    <div class="table-container">
                        @can("consulter_ecran_" . $ecran->id)
                        <table class="table table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th hidden>ID Localité</th>
                                    <th hidden>Code Rattachement</th>
                                    <th>Localité</th>                                                                
                                    <th>Découpage</th>
                                    <th hidden>Code Découpage</th>                                                                
                                    <th>Quantité</th>
                                    <th hidden>InfrastructureCode</th>
                                    <th>Infrastructure</th>
                                    <th hidden>Code Famille</th>
                                    <th>Famille</th>
                                    <th>Caractéristiques</th>
                                    <th>Actions</th>
                                    <th hidden>Niveau</th>
                                </tr>
                            </thead>
                            <tbody id="tableInfrastructures">
                                <!-- Dynamically added rows -->
                            </tbody>
                        </table>
                        @endcan
                    </div>
                    <div id="emptyTableMessage" class="text-center text-muted p-3">
                        Aucune infrastructure ajoutée pour le moment.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col">
            <button type="button" class="btn btn-secondary" onclick="prevStep()">
                <i class="fas fa-arrow-left"></i> Précédent
            </button>
        </div>
        <div class="col text-end">
            @can("ajouter_ecran_" . $ecran->id)
            <button type="button" class="btn btn-primary" onclick="saveStep2(nextStep)">
                Suivant <i class="fas fa-arrow-right"></i>
            </button>
            @endcan
        </div>
    </div>
</div>
@endcan
    @endisset
<!--Sauvegarde temporaire -->
<script>
function saveStep2(callback = null) {
    const codeProjet = localStorage.getItem("code_projet_temp");
    if (!codeProjet) {
        alert("Aucun projet n'a encore été créé.");
        return;
    }

    const infrastructures = [];
    const localiteSet = new Set();

    $("#tableInfrastructures tr").each(function () {
        const tds = $(this).find("td");

        if (tds.length > 0) {
            const localiteId = tds.eq(0).text().trim();
            const codeRattachement = tds.eq(1).text().trim();
            const localiteLibelle = tds.eq(2).text().trim();
            const libelleDecoupage = tds.eq(3).text().trim(); 
            const codeDecoupage = tds.eq(4).text().trim();   
            const quantite = parseInt(tds.eq(5).text().trim()) || 1;
            const infrastructureCode = tds.eq(6).text().trim();
            const infrastructureName = tds.eq(7).text().trim();
            const familleCode = tds.eq(8).text().trim();
            const familleLibelle = tds.eq(9).text().trim();

            // 🔁 Ajouter la localité (en string JSON) dans le Set pour éliminer les doublons
            localiteSet.add(JSON.stringify({
                id: localiteId,
                code_rattachement: codeRattachement,
                libelle: localiteLibelle,
                code_decoupage: codeDecoupage,
                libelle_decoupage: libelleDecoupage,
                niveau: tds.eq(12).text().trim()
            }));

            // 🔁 Récupération des caractéristiques
            const caracts = [];
            tds.eq(10).find('input[type="hidden"]').each(function () {
                const parts = $(this).val().split('|');

                if (parts.length === 3) {
                    const [id, unite_id, valeur] = parts;
                    caracts.push({
                        id: id,
                        unite_id: unite_id,
                        valeur: valeur
                    });
                }
            });

            infrastructures.push({
                libelle: infrastructureName,
                famille_code: familleCode,
                localisation_id: localiteId,
                quantite: quantite,
                caracteristiques: caracts
            });
        }
    });

    const localitesArray = Array.from(localiteSet).map(item => JSON.parse(item));
    // 🔁 Stocker le premier code de rattachement comme code_localisation
    const firstCodeRattachement = localitesArray[0]?.code_rattachement ?? null;
    if (firstCodeRattachement) {
        localStorage.setItem('code_localisation', firstCodeRattachement);
        console.log("✅ Code localisation sauvegardé :", firstCodeRattachement);
    } else {
        console.warn("⚠️ Aucun code de rattachement trouvé pour stocker le code_localisation");
    }

    // ✅ Vérification des données
    if (infrastructures.length === 0) {
        alert("Veuillez ajouter au moins une infrastructure.");
        return;
    }

    if (localitesArray.length === 0) {
        alert("Aucune localité détectée.");
        return;
    }

    // 📨 Envoi AJAX
    $.ajax({
        url: '{{ route("projets.temp.save.step2") }}',
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            code_projet: codeProjet,
            localites: localitesArray,
            infrastructures: infrastructures
        },
        success: function (response) {
            if (response.success) {
                console.log("[✅ Sauvegarde] Étape 2 enregistrée :", response);

                populateInfrastructureSelect();
                if (typeof callback === "function") {
                    callback();
                } else {
                    nextStep();
                }
            } else {
                alert(response.message || "Erreur lors de la sauvegarde.");
            }
        },
        error: function (xhr) {
            let message = "Une erreur est survenue lors de la sauvegarde.";

            try {
                const response = JSON.parse(xhr.responseText);
                if (response.message) {
                    message = response.message;
                }
            } catch (e) {
                console.warn("⚠️ Réponse brute (non JSON) :", xhr.responseText);
            }

            alert(message);
            console.error("[❌ Erreur] Détail :", xhr.responseText);
        }
    });
}
</script>
<script id="unitesSIData" type="application/json">{!! json_encode(\App\Models\Unite::all()) !!}</script>
<script id="unitesDeriveesData" type="application/json">{!! json_encode($unitesDerivees) !!}</script>
<script>
    window.unitesSI = JSON.parse(document.getElementById('unitesSIData')?.textContent || '[]');
    window.unitesDerivees = JSON.parse(document.getElementById('unitesDeriveesData')?.textContent || '[]');
</script>


<script>
        // Fonction de mise à jour dynamique
    function updateInfrastructureField() {
        const natureTravaux = $("#natureTraveaux").val();
        const quantite = parseInt($("#quantiteInfras").val() || "1");

        const container = $("#infrastructureNameContainer");
        container.empty();

        container.append('<label>Nom de l\'infrastructure *</label>');

        if (natureTravaux === "1") {
            // Cas "Construction" → input texte
            container.append('<input type="text" class="form-control" id="infrastructureName" placeholder="Nom de l\'infrastructure">');
        } else {
            // Cas autres que "Construction"
            if (quantite === 1) {
                const domaine = $('#domaineSelect').val();
                const sousDomaine = $('#sousDomaineSelect').val();
                const pays = $('#paysSelect').val();
                if (!domaine || !sousDomaine || !pays) {
                    console.warn("🔸 Domaine, sous-domaine ou pays manquant.");
                    container.append('<input type="text" class="form-control" id="infrastructureName" placeholder="Nom de l\'infrastructure">');
                    return;
                }

                fetch(`{{ url('/') }}/get-infrastructures/${domaine}/${sousDomaine}/${pays}`)
                    .then(res => {
                        if (!res.ok) {
                            throw new Error("Erreur HTTP : " + res.status);
                        }
                        return res.json();
                    })
                    .then(data => {
                        if (!data || data.length === 0) {
                            // ✅ Aucune infrastructure → input texte
                            container.append('<input type="text" class="form-control" id="infrastructureName" placeholder="Nom de l\'infrastructure">');
                        } else {
                            // ✅ Liste trouvée → select
                            const select = $('<select class="form-control" id="infrastructureName"></select>');
                            select.append('<option value="">Sélectionner une infrastructure</option>');
                            select.append(`<option value="__new__">➕ Créer une nouvelle infrastructure</option>`);
                            data.forEach(infra => {
                                select.append(`<option value="${infra.code}">${infra.libelle}</option>`);
                            });
                            container.append(select);

                            select.on('change', function () {
                                const selectedValue = this.value;
                                if (selectedValue === '__new__') {
                                    // Remplacer le <select> par un <input>
                                    const container = $("#infrastructureNameContainer");
                                    container.empty();
                                    container.append('<label>Nom de l\'infrastructure *</label>');
                                    container.append('<input type="text" class="form-control" id="infrastructureName" placeholder="Nom de l\'infrastructure">');
                                } else {
                                    // 🔁 Si besoin : charger la localité liée à l’infrastructure existante
                                    const infraCode = this.value;
                                    if (!infraCode) return;

                                    fetch(`{{ url('/') }}/get-infrastructure-localite/${infraCode}`)
                                        .then(res => res.json())
                                        .then(data => {
                                            if (!data || !data.id) return;

                                            $("#niveau1Select").val(data.id).trigger('change');
                                            $("#niveau2Select").html(`<option value="${data.niveau}">${data.niveau}</option>`).prop("disabled", false);
                                            $("#niveau3Select").html(`<option value="${data.code_decoupage}">${data.libelle_decoupage}</option>`).prop("disabled", false);

                                            selectedLocalite = {
                                                id: data.id,
                                                code_rattachement: data.code_rattachement,
                                                libelle: data.libelle,
                                                niveau: data.niveau,
                                                code_decoupage: data.code_decoupage,
                                                libelle_decoupage: data.libelle_decoupage
                                            };
                                        });
                                }
                            });
                        }
                    })
                    .catch(err => {
                        console.error("Erreur lors du chargement des infrastructures :", err);
                        container.append('<input type="text" class="form-control" id="infrastructureName" placeholder="Nom de l\'infrastructure">');
                    });

            } else {
                // Quantité > 1 → input texte
                container.append('<input type="text" class="form-control" id="infrastructureName" placeholder="Nom de l\'infrastructure">');
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const selectFamille = document.getElementById('FamilleInfrastruc');
        const blockContainer = document.getElementById('caracteristiquesContainer');
        const fieldsContainer = document.getElementById('caracteristiquesFields');

        document.getElementById('domaineSelect').addEventListener('change', function() {
            let codeSousDomaine = this.value;

            fetch('{{ url("/") }}/get-familles/' + codeSousDomaine)
                .then(response => response.json())
                .then(data => {
                    console.log(data);
                    let select = document.getElementById('FamilleInfrastruc');
                    select.innerHTML = '<option value="">Sélectionnez</option>';

                    data.forEach(function(famille) {
                        let option = document.createElement('option');
                        option.value = famille.code_Ssys;
                        option.text = famille.libelleFamille;
                        select.appendChild(option);
                    });
                });
        });

        // const infrastructureList = JSON.parse(document.getElementById('infraListData')?.textContent || '[]'); // Liste des infrastructures disponibles




        // Écouteurs d'événements
        $("#natureTraveaux").on("change", updateInfrastructureField);
        $("#quantiteInfras").on("input", updateInfrastructureField);

        // Initialisation à l'ouverture de la page
        updateInfrastructureField();


        let selectedLocalite = {
            id: null,
            code_rattachement: null,
            libelle: null,
            niveau: null,
            code_decoupage: null,
            libelle_decoupage: null
        };
        let familleData = null;
        let infrastructureCounter = 1;

        // Initialisation
        loadLocalites();
        updateEmptyTableMessage();


        // Chargement des localités
        function loadLocalites() {
            const paysCode = $("#paysSelect").val();
            if (!paysCode) return;

            $.ajax({
                url: '{{ url("/") }}/get-localites/' + paysCode,
                type: "GET",
                dataType: "json",
                success: function (data) {
                    const libelleCount = {};
                    data.forEach(localite => {
                        const libelleNormalized = localite.libelle.trim().toLowerCase();
                        libelleCount[libelleNormalized] = (libelleCount[libelleNormalized] || 0) + 1;
                    });

                    const $select = $("#niveau1Select");
                    $select.empty().append('<option value="">Sélectionnez une localité</option>');

                    data.forEach(localite => {
                        const libelle = localite.libelle.trim();
                        const libelleNormalized = libelle.toLowerCase();
                        const isDuplicate = libelleCount[libelleNormalized] > 1;

                        const label = isDuplicate && localite.libelle_decoupage
                            ? `${libelle} (${localite.libelle_decoupage})`
                            : libelle;

                        $select.append(
                            `<option value="${localite.id}" data-code="${localite.code_rattachement}">${label}</option>`
                        );
                    });
                },
                error: function () {
                    console.error("Erreur lors du chargement des localités.");
                }
            });
        }

        // Gestion du changement de famille
        selectFamille.addEventListener('change', function () {
            const familleCode = this.value;
            fieldsContainer.innerHTML = '';

            if (!familleCode) {
                blockContainer.style.display = 'none';
                return;
            }

            fetch(`{{ url('/')}}/famillesCaracteristiquess/${familleCode}/`)
                .then(res => res.json())
                .then(caracs => {
                    familleData = caracs;
                    console.log(familleData)
                    blockContainer.style.display = 'block';
                    renderCaracteristiques(caracs);
                })
                .catch(err => {
                    console.error('Erreur lors du chargement des caractéristiques :', err);
                    blockContainer.style.display = 'block';
                    fieldsContainer.innerHTML = '<div class="text-danger">Erreur de chargement.</div>';
                });
        });
        // window.unitesSI est injecté depuis Laravel
        const uniteMap = {};
        if (window.unitesSI) {
            window.unitesSI.forEach(unite => {
                uniteMap[unite.idUnite] = unite;
            });
        }

        // Rendu des caractéristiques
        function renderCaracteristiques(caracs) {
            fieldsContainer.innerHTML = '';

            // Mapping des unités SI injectées depuis Blade
            const uniteMap = {};
            if (window.unitesSI) {
                window.unitesSI.forEach(unite => {
                    uniteMap[unite.idUnite] = unite;
                });
            }

            function renderCarac(carac) {
                const col = document.createElement('div');
                col.className = 'col-md-3'; // Taille ajustée pour contenir 4 par ligne
                col.style.marginBottom = '8px';
                col.setAttribute('data-idcarac', carac.idCaracteristique);
                col.setAttribute('data-libelle', carac.libelleCaracteristique);

                const label = document.createElement('label');
                label.textContent = carac.libelleCaracteristique + ' *';

                const name = `caracteristiques[${carac.idCaracteristique}]`;
                const type = (carac.libelleTypeCaracteristique || carac.type?.libelleTypeCaracteristique || '').toLowerCase();
            
                let inputElement = null;

                if (type === 'nombre') {
                    const inputGroup = document.createElement('div');
                    inputGroup.className = 'input-group input-group-sm';

                    const input = document.createElement('input');
                    input.type = 'number';
                    input.step = 'any';
                    input.name = name;
                    input.className = 'form-control';
                    input.style.maxWidth = '80px';
                    input.maxLength = 10;
                    inputElement = input;

                    inputGroup.appendChild(input);

                    const unite = uniteMap[carac.idUnite];
                    if (unite) {
                        const span = document.createElement('span');
                        span.className = 'input-group-text';
                        span.textContent = unite.symbole || unite.libelleUnite || '?';
                        inputGroup.appendChild(span);
                    }

                    col.appendChild(label);
                    col.appendChild(inputGroup);

                } else if (type === 'liste') {
                    const select = document.createElement('select');
                    select.className = 'form-select form-select-sm';
                    select.name = name;
                    select.innerHTML = `<option value="">-- Choisir --</option>`;
                    (carac.valeurs_possibles || []).forEach(val => {
                        select.innerHTML += `<option value="${val.id}">${val.valeur}</option>`;
                    });

                    inputElement = select;
                    col.appendChild(label);
                    col.appendChild(select);

                } else if (type === 'boolean') {
                    const row = document.createElement('div');
                    row.className = 'd-flex align-items-center gap-2';

                    const labelNon = document.createElement('span');
                    labelNon.textContent = 'Non';
                    labelNon.style.fontSize = '0.9rem';
                    labelNon.style.color = 'black'

                    const container = document.createElement('div');
                    container.className = 'form-check form-switch mb-0';

                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = name;
                    hidden.value = '0';

                    const checkbox = document.createElement('input');
                    checkbox.className = 'form-check-input';
                    checkbox.type = 'checkbox';
                    checkbox.name = name;
                    checkbox.value = '1';

                    container.appendChild(hidden);
                    container.appendChild(checkbox);

                    const labelOui = document.createElement('span');
                    labelOui.textContent = 'Oui';
                    labelOui.style.fontSize = '0.9rem';
                    labelOui.style.color = 'black'

                    row.appendChild(labelNon);
                    row.appendChild(container);
                    row.appendChild(labelOui);

                    col.appendChild(label);
                    col.appendChild(row);

                    inputElement = checkbox;
                }
                else {
                    const input = document.createElement('input');
                    input.type = 'text';
                    input.name = name;
                    input.className = 'form-control form-control-sm';
                    inputElement = input;

                    col.appendChild(label);
                    col.appendChild(input);
                }

                // Retourne DOM complet et référence à l'input
                return { col, inputElement };
            }

            function appendCaracAndChildren(carac, targetContainer) {
                const existing = targetContainer.querySelector(`[data-idcarac="${carac.idCaracteristique}"]`);
                if (existing) return; // ⚠️ Ne pas afficher à nouveau si déjà présent

                const { col, inputElement } = renderCarac(carac);
                col.dataset.idcarac = carac.idCaracteristique; // utile pour l'anti-duplication
                targetContainer.appendChild(col);

                const type = (carac.libelleTypeCaracteristique || carac.type?.libelleTypeCaracteristique || '').toLowerCase();

                function showChildrenOnce() {
                    if (carac._childrenShown) return;
                    carac._childrenShown = true;
                    carac.enfants.forEach(enfant => appendCaracAndChildren(enfant, targetContainer));
                }

                function hideChildren() {
                    if (!carac._childrenShown) return;
                    carac._childrenShown = false;
                    carac.enfants.forEach(enfant => {
                        const el = targetContainer.querySelector(`[data-idcarac="${enfant.idCaracteristique}"]`);
                        if (el) el.remove();
                    });
                }

                // Conditions d'affichage dynamique
                if (carac.enfants && carac.enfants.length > 0) {
                    if (type === 'boolean') {
                        inputElement.addEventListener('change', () => {
                            if (inputElement.checked) showChildrenOnce();
                            else hideChildren();
                        });
                    } else if (type === 'liste') {
                        inputElement.addEventListener('change', () => {
                            if (inputElement.value) showChildrenOnce();
                            else hideChildren();
                        });
                    } else if (type === 'nombre' || type === 'texte') {
                        inputElement.addEventListener('input', () => {
                            if (inputElement.value.trim() !== '') showChildrenOnce();
                            else hideChildren();
                        });
                    }
                }
            }


            caracs.forEach(carac => {
                appendCaracAndChildren(carac, fieldsContainer);
            });
        }

        // Gestion du changement de localité
        $("#niveau1Select").change(function () {
            const lookup = document.getElementById("niveau1Select");
            const localiteId = lookup.value;
            const selected = lookup.getSelected();
            const localiteText = selected ? selected.text : "";
            const codeRattachement = selected ? selected.code : null;

            selectedLocalite.libelle = localiteText;
            selectedLocalite.id = localiteId;
            selectedLocalite.code_rattachement = codeRattachement;

            if (localiteId) {
                $.ajax({
                    url: '{{ url("/") }}/get-decoupage-niveau/' + localiteId,
                    type: "GET",
                    success: function (data) {
                        $("#niveau2Select").empty()
                            .append('<option value="' + data.niveau + '">' + data.niveau + '</option>')
                            .prop("disabled", false);

                        $("#niveau3Select").empty()
                            .append('<option value="' + data.code_decoupage + '">' + data.libelle_decoupage + '</option>')
                            .prop("disabled", false);

                        selectedLocalite.niveau = data.niveau;
                        selectedLocalite.code_decoupage = data.code_decoupage;
                        selectedLocalite.libelle_decoupage = data.libelle_decoupage;
                        
                    }
                });
            }
        });



        // Ajout d'une infrastructure
        $("#addInfrastructureBtn").click(function () {
            if (!validateForm()) return;

            const infrastructure = document.getElementById('infrastructureName');
            let infrastructureCode = '';
            let infrastructureName = '';

            if (infrastructure.tagName === 'SELECT') {
                // c'est un <select>
                infrastructureCode = infrastructure.value;
                infrastructureName = infrastructure.selectedOptions[0]?.textContent ?? '';
            } else {
                // c'est un <input type="text">
                infrastructureCode = '';
                infrastructureName = infrastructure.value;
            }

            const quantite = parseInt(document.getElementById('quantiteInfras').value);
            const familleCode = selectFamille.value;
            const familleText = selectFamille.options[selectFamille.selectedIndex].text;

            // Récupérer les caractéristiques
            const caracteristiques = [];
            const caracInputs = fieldsContainer.querySelectorAll('input, select');
            caracInputs.forEach(input => {
                if (input.value && input.value !== '0' && input.name.includes('caracteristiques')) {
                    const caracId = input.name.match(/\[(\d+)\]/)[1];
                    let displayValue = input.value;
                    if (input.type === 'checkbox') {
                        displayValue = input.checked ? 'Oui' : 'Non';
                    } else if (input.tagName === 'SELECT') {
                        displayValue = input.options[input.selectedIndex].text;
                    }
                    const colParent = input.closest('[data-idcarac]');
                    const libelle = colParent ? colParent.getAttribute('data-libelle') : 'Caractéristique';


                    let uniteId = '';
                    if (input.type === 'number') {
                        const span = input.closest('.input-group')?.querySelector('.input-group-text');
                        if (span) {
                            const symbole = span.textContent.trim();
                            const unite = Object.values(window.unitesSI).find(u => u.symbole === symbole);
                            uniteId = unite?.idUnite || '';
                        }
                    }

                    caracteristiques.push({
                        id: caracId,
                        unite_id: uniteId,
                        valeur: input.value,
                        libelle: libelle,
                        display: displayValue
                    });


                }
            });

            // Créer la ligne dans le tableau
            const newRow = createTableRow(
                selectedLocalite, 
                infrastructureCode,
                infrastructureName,
                familleCode,
                familleText,
                quantite,
                caracteristiques
            );

            $("#tableInfrastructures").append(newRow);
            infrastructuresAction.push({
                libelle: infrastructureName,
                famille_code: familleCode,
                localisation_id: selectedLocalite.id,
                quantite: quantite,
                caracteristiques: caracteristiques
            });
            console.log("💾 Infrastructure ajoutée :", infrastructuresAction);

            // Incrémenter le compteur et mettre à jour la quantité
            infrastructureCounter++;
            document.getElementById('quantiteInfras').value = infrastructureCounter;

            // Réinitialiser le formulaire partiellement
            resetForm(false);
            updateEmptyTableMessage();
        });

        // Validation du formulaire
        function validateForm() {
            const infrastructureName = document.getElementById('infrastructureName').value;
            const quantite = document.getElementById('quantiteInfras').value;

            if (!selectedLocalite.id) {
                alert('Veuillez sélectionner une localité.', 'warning');
                return false;
            }

            if (!infrastructureName.trim()) {
                alert('Veuillez saisir le nom de l\'infrastructure.', 'warning');
                return false;
            }

            if (!selectFamille.value) {
                alert('Veuillez sélectionner une famille d\'infrastructure.', 'warning');
                return false;
            }

            if (!quantite || quantite < 1) {
                alert('La quantité doit être au moins 1.', 'warning');
                return false;
            }

            // 🔁 Validation des caractéristiques obligatoires
            const inputs = fieldsContainer.querySelectorAll('input, select');
            for (let input of inputs) {
                const name = input.name || '';
                if (name.includes('caracteristiques')) {
                    const type = input.type;
                    const tag = input.tagName.toLowerCase();

                    if (tag === 'select' && input.value === '') {
                        alert('Veuillez sélectionner toutes les caractéristiques obligatoires.', 'warning');
                        return false;
                    }

                    if ((type === 'text' || type === 'number') && input.value.trim() === '') {
                        alert('Veuillez remplir toutes les caractéristiques obligatoires.', 'warning');
                        return false;
                    }

                    if (type === 'checkbox') {
                        const parent = input.closest('.form-check');
                        if (parent) {
                            const hiddenInput = parent.querySelector('input[type="hidden"]');
                            if (!hiddenInput) {
                                alert('Veuillez vérifier les cases à cocher obligatoires.', 'warning');
                                return false;
                            }
                        }
                    }
                }
            }

            return true;
        }


        // Création d'une ligne de tableau
        function createTableRow(localite, infrastructureCode, infrastructureName, familleCode, familleText, quantite, caracteristiques) {
            let caracDisplay = '';
            let caracHidden = '';

            caracteristiques.forEach(carac => {
                caracDisplay += `
                    <div class="carac-item mb-1">
                        <strong>${carac.libelle}</strong> : <span>${carac.display}</span>
                    </div>
                `;

                caracHidden += `
                    <input type="hidden" value="${carac.id}|${carac.unite_id || ''}|${carac.valeur}">
                `;
            });

            return `
                <tr>
                    <td hidden>${localite.id}</td>
                    <td hidden>${localite.code_rattachement}</td>
                    <td>${localite.libelle}</td>
                    <td hidden>${localite.code_decoupage}</td>
                    <td>${localite.libelle_decoupage}</td>                
                    <td>${quantite}</td>
                    <td hidden>${infrastructureCode}</td>
                    <td>${infrastructureName}</td>
                    <td hidden>${familleCode}</td>
                    <td>${familleText}</td>
                    <td>
                        ${caracDisplay}
                        ${caracHidden}
                    </td>
                    <td>
                        @can("supprmer_ecran_" . $ecran->id)
                        <button type="button" class="btn btn-danger btn-sm deleteRowBtn">
                            <i class="fas fa-trash"></i>
                        </button>
                        @endcan
                    </td>
                    <td hidden>${localite.niveau}</td>
                </tr>
            `;
        }


        // Réinitialisation du formulaire
        function resetForm(full = true) {
            document.getElementById('infrastructureName').value = '';
            fieldsContainer.innerHTML = '';
            blockContainer.style.display = 'none';

            if (full) {
                selectFamille.value = '';
                $("#niveau1Select").val('');
                $("#niveau2Select").empty().append('<option value="">Sélectionnez un niveau</option>').prop("disabled", true);
                $("#niveau3Select").empty().append('<option value="">Sélectionnez un niveau</option>').prop("disabled", true);
                selectedLocalite = {
                    id: null,
                    code_rattachement: null,
                    libelle: null,
                    niveau: null,
                    code_decoupage: null,
                    libelle_decoupage: null
                };
                infrastructureCounter = 1;
                document.getElementById('quantiteInfras').value = 1;
            }
        }

        // Gestion du bouton de réinitialisation
        $("#resetFormBtn").click(function () {
            resetForm(true);
        });

        // Suppression d'une ligne
        $(document).on("click", ".deleteRowBtn", function () {
            $(this).closest("tr").remove();
            updateEmptyTableMessage();
        });

        // Mise à jour du message de table vide
        function updateEmptyTableMessage() {
            const hasRows = $("#tableInfrastructures tr").length > 0;
            $("#emptyTableMessage").toggle(!hasRows);
        }

    });
</script>
