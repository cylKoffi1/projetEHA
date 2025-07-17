<div class="step" id="step-3">
    <h5 class="text-secondary">🌍 Infrastructures</h5>
    <div class="row">
        <br>
        <div style="width: 100%;">
            <fieldset class="border p-2 mt-5">
                <legend class="w-auto">Actions</legend>
                <div class="row">
                    <div class="col-1" style="width: 10%;">
                        <p for="action">N ordre:</p>
                        <input type="number" name="nordre" id="nordre" value="1" readonly class="form-control">
                    </div>
                    <div class="col-2" style="width: 25%;">
                        <p for="action">Action à mener:</p>
                        <select id="action" class="form-select" name="actionMener">
                            <option value="">Sélectionner </option>
                            @foreach ($actionMener as $action)
                            <option value="{{ $action->code }}">{{ $action->libelle }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-2" style="width: 11%;">
                        <p for="quantite">Quantité:</p>
                        <input type="number" class="form-control"  min="0" id="quantite" name="quantite" style="width: 88%; text-align: right; justify-content: right;" readonly>
                    </div>
                    <div class="col-2" style="width: 22%;">
                        <p for="infrastructure">Infrastructure:</p>
                        <select name="infrastructure" id="insfrastructureSelect" class="form-select">
                            <option value="">Sélectionner l'infrastructure</option>
                        </select>

                    </div>
                    <div class="col-2" style="margin-top: 7px; width: 17%;">
                        <a href="#"  id="toggleBeneficiaire">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle-fill" viewBox="0 0 16 16">
                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3v-3z"></path>
                            </svg>
                            Bénéficiaire
                        </a>




                            <button type="button" style="margin-top: 7px; float: right;" class="btn btn-secondary" id="addAction">
                                <i class="fas fa-plus"></i>
                                Action
                            </button>
                    </div>
            </fieldset>
            <div class="row mt-3 d-none" id="infrastructureField">
                <div class="row">
                    <div class="row">
                        <label for="structure_ratache">Bénéficiaire :</label>
                        <input type="hidden" name="CodeProjetBene" id="CodeProjetBene">
                        <input type="hidden" name="numOrdreBene" id="numOrdreBene">

                        <div class="col">
                            <label for="age">Localité :</label>
                            <input type="radio" name="beneficiaire_type[]" value="localite" id="age" checked="true" onclick="afficheSelect('localite')" style="margin-right: 15px;">
                        </div>
                        <div class="col">
                            <label for="sousp">Acteur :</label>
                            <input type="radio" name="beneficiaire_type[]" value="acteur" id="sousp" onclick="afficheSelect('acteur')" style="margin-right: 15px;">
                        </div>
                        <div class="col" >
                            <label for="min">infrastructure :</label>
                            <input type="radio" name="beneficiaire_type[]" value="infrastructure" id="dep" onclick="afficheSelect('infrastructure')" style="margin-right: 15px;">

                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <lookup-select name="beneficiaire_code[]" id="localite" style="display: none;">
                                <option value="">Sélectionner la localité</option>
                                
                            </lookup-select>
                            <lookup-select name="beneficiaire_code[]" id="acteur" style="display: none;">
                                <option value="">Sélectionner l'acteur</option>
                                @foreach ($acteurs as $acteur)
                                    <option value="{{ $acteur?->code_acteur }}">{{ $acteur?->libelle_court }} {{ $acteur?->libelle_long }} </option>
                                @endforeach
                            </lookup-select>
                            <lookup-select name="beneficiaire_code[]" id="infrastructure" style="display: none;">
                                <option value="">Sélectionner l'infrastructure</option>
                                @foreach ($infrastructures as $infrastructure)
                                    <option value="{{ $infrastructure->id }}">{{ $infrastructure->libelle }}</option>
                                @endforeach
                            </lookup-select>
                        </div>

                        <div class="col">
                            <button type="button" class="btn btn-secondary" id="addBtnBene">
                                <i class="fas fa-plus"></i>
                                Ajouter
                            </button>
                        </div>
                        <div class="col">
                            <button type="button" class="btn btn-danger" style="width: 121px" id="deleteBtn">
                                <i class="fas fa-trash"></i>
                                Supprimer
                            </button>
                        </div>
                    </div>
                    <br><br>
                </div>
                <br>
                <div class="row" style="align-items: center;">
                    <div class="col">
                        <div class="table-container">
                            <table id="beneficiaireTable">
                                <thead>
                                    <tr>
                                        <th class="etablissement"><input type="checkbox"></th>
                                        <th class="etablissement">Code</th>
                                        <th class="etablissement">Libellé</th>
                                        <th class="etablissement">Type</th>

                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
        <div>

        <div class="table table-bordered" >
            <table id="tableActionMener" style="width: 100%">
                <thead>
                    <tr>
                        <th>N° d'ordre</th>
                        <th>Action</th>
                        <th hidden>ActionCode </th>
                        <th>Quantité</th>
                        <th>Infrastructure</th>                                                    
                        <th hidden>InfrastructureCode</th>
                        <th>libelle Bénéficiaires</th>
                        <th hidden>Code bénéficiaire</th>
                        <th>type bénéficiaire</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="beneficiaire-table-body">
                    <!-- Le corps du tableau sera géré dynamiquement via JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
    <br>
    <div class="row mt-3">
        <div class="col">
            <button type="button" class="btn btn-secondary" onclick="prevStep()"><i class="fas fa-arrow-left"></i> Précédent</button>
        </div>
        <div class="col text-end">
            <button type="button" class="btn btn-primary" onclick="saveStep3(nextStep)">Suivant <i class="fas fa-arrow-right"></i> </button>
        </div>
        
    </div>
</div>
<!--Sauvegarde temporaire -->
<script>
    function saveStep3(callback = null) {
        const codeProjet = localStorage.getItem("code_projet_temp");
        if (!codeProjet) return alert("Aucun projet temporaire trouvé.", 'error');

        const actions = [];

        $("#tableActionMener tbody tr").each(function () {
            const $row = $(this);

            const ordre = $row.find("td:eq(0)").text().trim();
            const actionText = $row.find("td:eq(1)").text().trim();
            const actionCode = $row.find("td:eq(2)").text().trim();
            const quantite = $row.find("td:eq(3)").text().trim();
            const infraLabel = $row.find("td:eq(4)").text().trim();
            const infraCode = $row.find("td:eq(5)").text().trim();
            const libelleBenef = $row.find("td:eq(6)").text().trim();
            const codeB = $row.find("td:eq(7)").text().trim();
            const typeB = $row.find("td:eq(8)").text().trim();
            const codePays = $row.find("td:eq(9)").text().trim();
            const codeRattachement = $row.find("td:eq(10)").text().trim();

            // Vérifie si une action avec le même ordre existe déjà (plusieurs bénéficiaires pour la même action)
            let existingAction = actions.find(a => a.ordre === ordre && a.action_code === actionCode && a.infrastructure_code === infraCode);

            const beneficiaire = {
                code: codeB,
                libelle: libelleBenef,
                type: typeB,
                codePays: codePays,
                codeRattachement: codeRattachement
            };

            if (existingAction) {
                existingAction.beneficiaires.push(beneficiaire);
            } else {
                actions.push({
                    ordre: ordre,
                    action_code: actionCode,
                    action_label: actionText,
                    quantite: quantite,
                    infrastructure_code: infraCode,
                    infrastructure_label: infraLabel,
                    beneficiaires: [beneficiaire]
                });
            }
        });


        $.ajax({
            url: '{{ route("projets.temp.save.step3") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                code_projet: codeProjet,
                actions: actions
            },
            success: function (response) {
                //alert(response.message || "Étape 3 sauvegardée.");
                if (response.success) {
                    console.log("Step 1 sauvegardé temporairement.");
                    localStorage.setItem("code_projet_temp", response.code_projet);
                    //if (typeof callback === 'function') callback();
                    nextStep();
                }

            },
            error: function (xhr) {
                let message = "Une erreur est survenue.";

                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.message) {
                        message = response.message;
                    }
                } catch (e) {
                    console.error("Erreur parsing JSON :", e);
                    console.warn("Réponse brute :", xhr.responseText);
                }

                alert(message,'error');
                console.error("Détail complet :", xhr.responseText);
            }
        });
    }

</script>
<script>
    $(document).ready(function () {
        const paysCode = $("#paysSelect").val();

        if (paysCode) {
            $.ajax({
                url: '{{ url("/") }}/get-localites/' + paysCode,
                type: "GET",
                success: function (data) {
                    const $localite = $("#localite");
                    $localite.empty().append('<option value="">Sélectionnez une localité</option>');

                    // Normalisation et tri optionnel
                    const libelleCount = {};
                    data.forEach(localite => {
                        const libelleNormalized = localite.libelle.trim().toLowerCase();
                        libelleCount[libelleNormalized] = (libelleCount[libelleNormalized] || 0) + 1;
                    });

                    data.forEach(localite => {
                        const libelle = localite.libelle.trim();
                        const libelleNormalized = libelle.toLowerCase();
                        const isDuplicate = libelleCount[libelleNormalized] > 1;

                        const label = isDuplicate && localite.libelle_decoupage
                            ? `${libelle} (${localite.libelle_decoupage})`
                            : libelle;

                        $localite.append(`
                            <option 
                                value="${localite.id}" 
                                data-code-pays="${localite.id_pays}" 
                                data-code-rattachement="${localite.code_rattachement}">${label}</option>
                        `);
                    });

                    // 🔄 Rechargement visuel du <lookup-select> personnalisé
                    const lookup = document.getElementById("localite");
                    if (lookup && typeof lookup.loadOptionsFromDOM === "function") {
                        lookup.loadOptionsFromDOM();
                    }
                },
                error: function () {
                    console.error("Erreur lors du chargement des localités.");
                }
            });
        }

        // Pré-sélection "localité"
        $("#age").prop("checked", true);
        afficheSelect('localite');
    });

    // Toggle formulaire de bénéficiaire
    document.addEventListener("DOMContentLoaded", function () {
        const beneficiaireBtn = document.getElementById("toggleBeneficiaire");
        const infrastructureField = document.getElementById("infrastructureField");

        if (beneficiaireBtn && infrastructureField) {
            beneficiaireBtn.addEventListener("click", function (event) {
                event.preventDefault();
                infrastructureField.classList.toggle("d-none");
            });
        }
    });

    function afficheSelect(selectId) {
        $('#localite, #infrastructure, #acteur').hide();
        $('#' + selectId).show();
    }

    let actionCounter = 1;

    const typeToSelectId = {
        localite: "localite",
        acteur: "acteur",
        infrastructure: "infrastructure"
    };

    // 🔹 Ajouter un bénéficiaire
    $("#addBtnBene").on("click", function () {
        const selectedType = $("input[name='beneficiaire_type[]']:checked").val();
        const selectId = typeToSelectId[selectedType];
        const selectedLookup = document.getElementById(selectId);
        const selectedOption = selectedLookup?.getSelected();

        if (selectedOption && selectedOption.value) {
            const code = selectedOption.value;
            const libelle = selectedOption.text;
            const type = selectId;

            const row = `
                <tr>
                    <td><input type="checkbox"></td>
                    <td>${code}</td>
                    <td>${libelle}</td>
                    <td>${type}</td>
                </tr>
            `;
            $("#beneficiaireTable tbody").append(row);
        } else {
            alert("Veuillez sélectionner un bénéficiaire.");
        }
    });

    // 🔹 Supprimer bénéficiaires sélectionnés
    $("#deleteBtn").on("click", function () {
        $("#beneficiaireTable tbody input[type='checkbox']:checked").closest("tr").remove();
    });

    // 🔹 Ajouter une action avec ses bénéficiaires
    $("#addAction").on("click", function () {
        const action = document.getElementById('action');
        const quantite = $("#quantite").val();
        const infrastructureSelect = document.getElementById("insfrastructureSelect");
        const infrastructureOption = infrastructureSelect.options[infrastructureSelect.selectedIndex];
        if ( !action.value ) {
            alert("Veuillez sélectionner l'action à mener.",'warning');
            return;
        }
        if ( !quantite ) {
            alert("Veuillez sélectionner la quantité.",'warning');
            return;
        }
        if ( !infrastructureOption || !infrastructureOption.value) {
            alert("Veuillez sélectionner l'infrastructure.",'warning');
            return;
        }
        const infrastructureLibelle = infrastructureOption.textContent;
        const infrastructureCode = infrastructureOption.value;

        const $beneficiaires = $("#beneficiaireTable tbody tr");

        if ($beneficiaires.length === 0) {
            alert("Veuillez ajouter au moins un bénéficiaire.",'warning');
            return;
        }

        $beneficiaires.each(function () {
            const codeB = $(this).find("td:eq(1)").text();
            const libelleB = $(this).find("td:eq(2)").text();
            const typeB = $(this).find("td:eq(3)").text();

            let extraData = {
                codePays: "",
                codeRattachement: ""
            };

            if (typeB === "localite") {
                const selected = $("#localite option:selected");
                extraData.codePays = selected.data("code-pays") || "";
                extraData.codeRattachement = selected.data("code-rattachement") || "";
            }

            const newRow = `
                <tr>
                    <td>${actionCounter}</td>
                    <td>${action.selectedOptions[0]?.textContent ?? ''}</td>
                    <td hidden>${action.value}</td>
                    <td>${quantite}</td>
                    <td>${infrastructureLibelle}</td>
                    <td hidden>${infrastructureCode}</td>
                    <td>${libelleB}</td>
                    <td hidden>${codeB}</td>
                    <td>${typeB}</td>
                    <td hidden>${extraData.codePays}</td>
                    <td hidden>${extraData.codeRattachement}</td>
                    <td>
                        <button class="btn btn-danger btn-sm delete-row">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;

            $("#beneficiaire-table-body").append(newRow);
        });

        // Incrémentation et nettoyage
        actionCounter++;
        $("#nordre").val(actionCounter);

        $("#quantite").val('');
        $("#action").val('');
        $("#insfrastructureSelect").val('');
        $("#beneficiaireTable tbody").empty();
        $("#infrastructureField").addClass("d-none");
    });

    // 🔹 Suppression ligne dans tableau final
    $(document).on("click", ".delete-row", function () {
        $(this).closest("tr").remove();
    });

    // Fonction pour peupler le select avec les infrastructures
    function populateInfrastructureSelect() {
        const selectInfra = document.getElementById('insfrastructureSelect');
        selectInfra.innerHTML = '<option value="">Sélectionner l\'infrastructure</option>'; // Clear

        infrastructuresAction.forEach((infra, index) => {
            const option = document.createElement('option');
            option.value = index;
            option.textContent = infra.libelle;
            option.dataset.quantite = infra.quantite;
            option.dataset.localisation = infra.localisation_id;
            selectInfra.appendChild(option);
        });
    }

    document.addEventListener("DOMContentLoaded", function () {
        const selectInfra = document.getElementById('insfrastructureSelect');

        // Gestion du changement d'infrastructure
        selectInfra.addEventListener('change', function () {
            const selected = this.options[this.selectedIndex];
            const quantite = selected.dataset.quantite;
            document.getElementById('quantite').value = quantite;
        });
    });
</script>
