<!-- Étape : Informations sur le Maître d’Œuvre -->
<div class="step" id="step-4">
    <h5 class="text-secondary">👷 Informations / Maître d’ouvrage</h5>

    <div class="row">
        <label>Type de Maître d’ouvrage *</label>
        <div class="col">
            <div class="form-check">
                <input type="checkbox" id="moePublic" class="form-check-input" name="type_ouvrage" value="Public" onchange="toggleTypeMoe()">
                <label class="form-check-label" for="moePublic">Public</label>
            </div>
            <div class="form-check">
                <input type="checkbox" id="moePrive" class="form-check-input" name="type_ouvrage" value="Privé" onchange="toggleTypeMoe()">
                <label class="form-check-label" for="moePrive">Privé</label>
            </div>

        </div>
        <!-- Options spécifiques pour le type privé -->
        <div class="col mt-3 d-none" id="optionsMoePrive">
            <label>Type de Privé *</label>
            <div class="col">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="priveMoeType" id="moeEntreprise" value="Entreprise" onchange="toggleMoeFields()">
                    <label class="form-check-label" for="moeEntreprise">Entreprise</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="priveMoeType" id="moeIndividu" value="Individu" onchange="toggleMoeFields()">
                    <label class="form-check-label" for="moeIndividu">Individu</label>
                </div>
            </div>
        </div>
        <div class="col position-relative">
            <label>Nom acteur *</label>
            <select class="form-control required" name="acteurMoeSelect" id="acteurMoeSelect">
                <option value="">Sélectionnez un acteur</option>
            </select>
            <small class="text-muted">Sélectionnez l’entité qui assure le rôle de Maître d’œuvre.</small>
        </div>
        <div class="col">
            <label>De :</label>
            <select name="sectActivEntMoe" id="sectActivEntMoe" class="form-control" >
                <option value="">Sélectionnez...</option>
                @foreach ($SecteurActivites as $SecteurActivite)
                    <option value="{{ $SecteurActivite->code }}">{{ $SecteurActivite->libelle }}</option>
                @endforeach
            </select>
        </div>

    </div>
    <hr>
    <div class="row mt-3">
        <div class="col-8">
            <label>Description / Observations</label>
            <textarea class="form-control" id="descriptionMoe" rows="3" placeholder="Ajoutez des précisions sur le Maître d’œuvre"></textarea>
        </div>
        <div class="col-4  mt-4">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="isAssistantMoe">
                <label class="form-check-label" for="isAssistantMoe">Assistant Maître d’Ouvrage</label>
            </div>
            <button type="button" class="btn btn-secondary" id="addMoeBtn" style="heght: 34px">
                <i class="fas fa-plus"></i> Ajouter 
            </button>
        </div>
    </div>
    <div class="row mt-3">
        <table class="table table-bordered" id="moeTable">
            <thead>
                <tr>
                    <th>Nom / Libellé court</th>
                    <th>Prénom / Libellé long</th>
                    <th>Secteur</th>
                    <th>Rôle</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- Ligne ajoutée dynamiquement -->
            </tbody>
        </table>
    </div>
    <br>
    <div class="row">

        <div class="col">
            <button type="button" class="btn btn-secondary" onclick="prevStep()"><i class="fas fa-arrow-left"></i> Précédent</button>
        </div>
        <div class="col text-end">
            <button type="button" class="btn btn-primary " onclick="saveStep4(nextStep)">Suivant <i class="fas fa-arrow-right"></i> </button>
        </div>
    </div>

</div>
<!--Sauvegarde temporaire -->
<script>
    function saveStep4(callback = null) {
        const codeProjet = localStorage.getItem('code_projet_temp');
        if (!codeProjet) return alert("Projet non trouvé.");

        const acteurs = [];

        // Lecture de chaque ligne du tableau des maîtres d’ouvrage
        $("#moeTable tbody tr").each(function () {
            const tds = $(this).find("td");
            const codeActeur = $(this).find("input[name='code_acteur_moe']").val();
            const role = $(this).find("input[name='role_moe']").val(); // 'moe' ou 'amo'
            const secteurCode = $(this).find("td:eq(4)").text(); // Code secteur (si ministère)

            if (codeActeur && role) {
                acteurs.push({
                    code_acteur: codeActeur,
                    is_assistant: role === 'amo', // booléen pour le backend
                    secteur_code: secteurCode || null
                });
            }
        });

        if (acteurs.length === 0) {
            alert("Veuillez ajouter au moins un Maître d’Ouvrage ou un Assistant.");
            return;
        }

        // Champs globaux (type MOE, description, etc.)
        const typeOuvrage = $('input[name="type_ouvrage"]:checked').val() || null;
        const priveMoeType = $('input[name="priveMoeType"]:checked').val() || null;
        const description = $('#descriptionMoe').val();

        $.ajax({
            url: '{{ route("projets.temp.save.step4") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                code_projet: codeProjet,
                type_ouvrage: typeOuvrage,
                priveMoeType: priveMoeType,
                descriptionMoe: description,
                acteurs: acteurs // tableau [{code_acteur, is_assistant, secteur_code}]
            },
            success: function (response) {
                console.log("[STEP4] Sauvegarde réussie :", response);
                if (typeof callback === "function") callback();
                else nextStep();
            },
            error: function (xhr) {
                let message = "Une erreur est survenue.";
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.message) message = response.message;
                } catch (e) {
                    console.error("Erreur parsing JSON :", e);
                    console.warn("Réponse brute :", xhr.responseText);
                }

                alert(message);
                console.error("Détail complet :", xhr.responseText);
            }
        });
    }
</script>

<script>
     document.addEventListener("DOMContentLoaded", function() {
        
        const acteurSelect = document.getElementById('acteurMoeSelect');
        const secteurActiviteContainer = document.getElementById('sectActivEntMoe').parentElement;
        if (!acteurSelect || !secteurActiviteContainer ) {
            console.error("Les éléments HTML avec les identifiants 'acteurMoeSelect' ou 'sectActivEntMoe' n'ont pas été trouvés.");
            return;
        }
        acteurSelect.addEventListener('change', function () {
            const selectedValue = acteurSelect.value;
            console.log("Valeur sélectionnée :", selectedValue);


            if (selectedValue === '5689') {
                // Afficher le secteur d'activité
                secteurActiviteContainer.style.display = 'block';
            } else {
                // Masquer le secteur d'activité
                secteurActiviteContainer.style.display = 'none';
            }
        });
        if (acteurSelect.value === '5689') {
            secteurActiviteContainer.style.display = 'block';
        } else {
            secteurActiviteContainer.style.display = 'none';
        }



        const typeSelectionInputs = document.querySelectorAll(".type_ouvrage");

        const acteurMoeSelect = document.getElementById("acteurMoeSelect");

        typeSelectionInputs.forEach(input => {
            input.addEventListener("change", function() {
                const selectionType = this.value;

                fetch(`{{ url("/") }}/get-acteurs?type_selection=${selectionType}`)
                    .then(response => response.json())
                    .then(data => {
                        // Réinitialiser les options
                        acteurMoeSelect.innerHTML = '<option value="">Sélectionnez un acteur</option>';

                        // Ajouter les nouvelles options
                        data.forEach(acteur => {
                            const option = document.createElement("option");
                            option.value = acteur.code_acteur;
                            option.textContent = acteur.libelle_long;
                            acteurMoeSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error("Erreur lors du chargement des acteurs:", error));
            });
        });
    
        // Empêcher la sélection de plusieurs options pour type_ouvrage
        const type_ouvrages = document.querySelectorAll('input[name="type_ouvrage"]');
        type_ouvrages.forEach((checkbox) => {
            checkbox.addEventListener("change", function () {
                if (this.checked) {
                    type_ouvrages.forEach((cb) => {
                        if (cb !== this) cb.checked = false;
                    });
                }
            });
        });

        // Gestion du Maître d’Ouvrage
        function toggleTypeMoe() {
            const publicRadio = document.getElementById('moePublic');
            const priveRadio = document.getElementById('moePrive');
            const optionsMoePrive = document.getElementById('optionsMoePrive');
            const acteurMoeSelect = document.getElementById('acteurMoeSelect');

            if (publicRadio.checked) {
                optionsMoePrive.classList.add('d-none');
                fetchMoeActeurs('Public');
            } else if (priveRadio.checked) {
                optionsMoePrive.classList.remove('d-none');
                acteurMoeSelect.innerHTML = '<option value="">Sélectionnez un acteur</option>';

                const entrepriseRadio = document.getElementById('moeEntreprise');
                const individuRadio = document.getElementById('moeIndividu');

            } else {
                optionsMoePrive.classList.add('d-none');
                acteurMoeSelect.innerHTML = '<option value="">Sélectionnez un acteur</option>';
            }
        }

        function toggleMoeFields() {
            const entrepriseRadio = document.getElementById('moeEntreprise');
            const individuRadio = document.getElementById('moeIndividu');
            const typeOuvrage = document.querySelector('input[name="type_ouvrage"]:checked')?.value;

            if (entrepriseRadio.checked) {
                fetchMoeActeurs(typeOuvrage, 'Entreprise');
            } else if (individuRadio.checked) {
                fetchMoeActeurs(typeOuvrage, 'Individu');
            }
        }

        function fetchMoeActeurs(typeOuvrage, priveType = null) {
            const acteurMoeSelect = document.getElementById('acteurMoeSelect');
            let url = `{{ url("/") }}/get-acteurs?type_ouvrage=${encodeURIComponent(typeOuvrage)}`;

            if (priveType) {
                url += `&priveMoeType=${encodeURIComponent(priveType)}`;
            }

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    acteurMoeSelect.innerHTML = '<option value="">Sélectionnez un acteur</option>';
                    data.forEach(acteur => {
                        const option = document.createElement('option');
                        option.value = acteur.code_acteur;
                        option.textContent = acteur.libelle_long;
                        acteurMoeSelect.appendChild(option);
                    });
                })
                .catch(error => console.error("Erreur lors du chargement des acteurs :", error));
        }

        document.getElementById('moePublic').addEventListener('change', toggleTypeMoe);
        document.getElementById('moePrive').addEventListener('change', toggleTypeMoe);
        document.getElementById('moeEntreprise').addEventListener('change', toggleMoeFields);
        document.getElementById('moeIndividu').addEventListener('change', toggleMoeFields);
    });

    $("#addMoeBtn").on("click", function () {
        const selected = $("#acteurMoeSelect option:selected");

        if (!selected.val()) {
            alert("Veuillez sélectionner un acteur.");
            return;
        }

        const isAssistant = $("#isAssistantMoe").is(":checked");
        const codeActeur = selected.val();
        const libelleCourt = selected.data("libelle-court") || selected.text().split(" ")[0];
        const libelleLong = selected.data("libelle-long") || selected.text().split(" ").slice(1).join(" ");
        const secteur = $("#sectActivEntMoe option:selected").text();
        const secteurCode = $("#sectActivEntMoe").val();
        const tableBody = $("#moeTable tbody");

        const role = isAssistant ? "Assistant Maître d’Ouvrage" : "Maître d’Ouvrage";

        // Un seul maître d’ouvrage
        if (!isAssistant) {
            if (tableBody.find("input[name='role_moe'][value='moe']").length > 0) {
                alert("Un seul Maître d’Ouvrage peut être sélectionné.");
                return;
            }
        }
        const isMinistere = libelleCourt?.toLowerCase().includes("minist");
        const row = `
            <tr>
                <td>${libelleCourt}</td>
                <td>${libelleLong}</td>
                <td>${isMinistere ? secteur : "-"}</td>
                <td>${role}</td>
                 <input type="hidden" name="role_moe" value="${isAssistant ? 'amo' : 'moe'}">
                <td hidden>${isMinistere ? secteurCode : ""}</td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-moe">
                        <i class="fas fa-trash"></i>
                    </button>
                    <input type="hidden" name="code_acteur_moe" value="${codeActeur}">
                </td>
            </tr>
        `;
        tableBody.append(row);
    });


    // Suppression de la ligne
    $(document).on("click", ".remove-moe", function () {
        $(this).closest("tr").remove();
    });
</script>