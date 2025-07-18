<div class="step " id="step-5">
    <h5 class="text-secondary">🏗️ Informations / Maître d'œuvre</h5>

    <!-- ✅ Sélection du Type -->
    <div class="row">
        <label>Type de Maître d'œuvre  *</label>
        <div class="col">
            <div class="form-check">
                <input type="checkbox" id="public" class="form-check-input" name="type_mo" value="Public" onchange="toggleType()">
                <label class="form-check-label" for="public">Public</label>
            </div>
            <div class="form-check">
                <input type="checkbox" id="prive" class="form-check-input" name="type_mo" value="Privé" onchange="toggleType()">
                <label class="form-check-label" for="prive">Privé</label>
            </div>
            <small class="text-muted">Le maître d'œuvre peut être public (État), privé (Entreprise).</small>
        </div>
        <!-- ✅ Options spécifiques pour le type privé -->
        <div class="col mt-3 d-none" id="optionsPrive">
            <label>Type de Privé *</label>
            <div class="col">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="priveType" id="entreprise" value="Entreprise" onchange="togglePriveFields()">
                    <label class="form-check-label" for="entreprise">Entreprise</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="priveType" id="individu" value="Individu" onchange="togglePriveFields()">
                    <label class="form-check-label" for="individu">Individu</label>
                </div>
            </div>
        </div>
        <div class="col">
            <!-- ✅ Sélection de l’Acteur -->
            <label>Nom Acteur *</label>
            <select class="form-control required" name="acteurSelect" id="acteurSelect">
                <option value="">Sélectionnez un acteur</option>

            </select>
            <small class="text-muted">Sélectionnez l’entité qui assure le rôle de Maître d'œuvre.</small>
        </div>
        <div class="col">
            <label>De :</label>
            <select name="sectActivEnt" id="sectActivEnt" class="form-control" >
                <option value="">Sélectionnez...</option>
                @foreach ($SecteurActivites as $SecteurActivite)
                    <option value="{{ $SecteurActivite->code }}">{{ $SecteurActivite->libelle }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- ✅ Zone de description complémentaire -->
    <div class="row">
        <div class="col-10">                                            
            <label>Description / Observations</label>
            <textarea class="form-control" id="descriptionInd" rows="3" placeholder="Ajoutez des précisions sur le Maître d’Ouvrage (ex: Budget, contraintes, accords...)"></textarea>
        </div>
        <div class="col-2 mt-4">
            <button type="button" class="btn btn-secondary" id="addMoeuvreBtn" style="height: 34px">
                <i class="fas fa-plus"></i> Ajouter
            </button>

        </div>
    </div>
    <div class="row mt-3">
        <table class="table table-bordered" id="moeuvreTable">
            <thead>
                <tr>
                    <th>Nom / Libellé court</th>
                    <th>Prénom / Libellé long</th>
                    <th>Secteur</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- Rempli dynamiquement -->
            </tbody>
        </table>
    </div>

    <br>
    <div class="row">

        <div class="col">
            <button type="button" class="btn btn-secondary" onclick="prevStep()"><i class="fas fa-arrow-left"></i> Précédent</button>
        </div>
        <div class="col text-end">
            <button type="button" class="btn btn-primary " onclick="saveStep5(nextStep)">Suivant <i class="fas fa-arrow-right"></i> </button>
        </div>
    </div>

</div>
<!--Sauvegarde temporaire -->
<script>
    function saveStep5(callback = null) {
        const codeProjet = localStorage.getItem("code_projet_temp");
        if (!codeProjet) {
            alert("Aucun projet temporaire trouvé.");
            return;
        }

        const acteurs = [];

        $("#moeuvreTable tbody tr").each(function () {
            const codeActeur = $(this).find('input[name="code_acteur_moeuvre[]"]').val();
            const secteurId = $(this).find('input[name="secteur_id[]"]').val();

            acteurs.push({
                code_acteur: codeActeur,
                secteur_id: secteurId
            });
        });

        if (acteurs.length === 0) {
            alert("Veuillez ajouter au moins un maître d’œuvre.");
            return;
        }

        $.ajax({
            url: '{{ route("projets.temp.save.step5") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                code_projet: codeProjet,
                acteurs: acteurs
            },
            success: function (res) {
                //alert(res.message);
                nextStep();
                //if (typeof callback === "function") callback();
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

                alert(message);
                console.error("Détail complet :", xhr.responseText);
            }
        });
    }

</script>
<script>
    ///////////////INFORMATION / MAITRE OUVRAGE
    document.addEventListener("DOMContentLoaded", function () {
        // ✅ Vérification que seule UNE option (Public, Privé, Mixte) est sélectionnée
        const typeMOs = document.querySelectorAll('input[name="type_mo"]');
        typeMOs.forEach((checkbox) => {
            checkbox.addEventListener("change", function () {
                if (this.checked) {
                    typeMOs.forEach((cb) => {
                        if (cb !== this) cb.checked = false;
                    });
                }
            });
        });

        
    });

    // ➕ Ajouter un maître d’œuvre
    $("#addMoeuvreBtn").on("click", function () {
        const selected = $("#acteurSelect option:selected");

        if (!selected.val()) {
            alert("Veuillez sélectionner un acteur.");
            return;
        }

        const codeActeur = selected.val();
        const libelleCourt = selected.data("libelle-court") || selected.text().split(" ")[0];
        const libelleLong = selected.data("libelle-long") || selected.text().split(" ").slice(1).join(" ");
        const secteur = $("#sectActivEnt option:selected").text();
        const secteurCode = $("#sectActivEnt").val();
        const tableBody = $("#moeuvreTable tbody");

        // Vérifie si l'acteur est déjà dans la liste
        if (tableBody.find(`input[value="${codeActeur}"]`).length > 0) {
            alert("Ce maître d’œuvre est déjà ajouté.");
            return;
        }

        const isMinistere = libelleCourt?.toLowerCase().includes("minist");

        const row = `
            <tr>
                <td>${libelleCourt}</td>
                <td>${libelleLong}</td>
                <td>${isMinistere ? secteur : "-"}</td>
                <td hidden>${isMinistere ? secteurCode : ""}</td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-moeuvre">
                        <i class="fas fa-trash"></i>
                    </button>
                    <input type="hidden" name="code_acteur_moeuvre[]" value="${codeActeur}">
                    <input type="hidden" name="secteur_id[]" value="${isMinistere ? secteurCode : ''}">
                </td>
            </tr>
        `;

        tableBody.append(row);
    });

    // 🗑️ Supprimer un maître d’œuvre
    $(document).on("click", ".remove-moeuvre", function () {
        $(this).closest("tr").remove();
    });
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const typeSelectionInputs = document.querySelectorAll(".type_mo");

        const acteurSelect = document.getElementById("acteurSelect");

        typeSelectionInputs.forEach(input => {
            input.addEventListener("change", function() {
                const selectionType = this.value;

                fetch(`{{ url("/") }}/get-acteurs?type_selection=${selectionType}`)
                    .then(response => response.json())
                    .then(data => {
                        // Réinitialiser les options
                        acteurSelect.innerHTML = '<option value="">Sélectionnez un acteur</option>';

                        // Ajouter les nouvelles options
                        data.forEach(acteur => {
                            const option = document.createElement("option");
                            option.value = acteur.code_acteur;
                            option.textContent = acteur.libelle_long;
                            acteurSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error("Erreur lors du chargement des acteurs:", error));
            });
        });
    });
    function fetchActeurs(type_mo, priveType = null) {
        const acteurSelect = document.getElementById('acteurSelect'); // Select des acteurs
        let url = `{{ url("/") }}/get-acteurs?type_mo=${type_mo}`; // Construire l'URL API

        // Ajouter le sous-type (priveType) si présent
        if (priveType) {
            url += `&priveType=${priveType}`;
        }

        // Appeler l'API pour récupérer les acteurs
        fetch(url)
            .then(response => response.json())
            .then(data => {
                // Réinitialiser les options du select
                acteurSelect.innerHTML = '<option value="">Sélectionnez un acteur</option>';

                // Ajouter les options reçues
                data.forEach(acteur => {
                    const option = document.createElement('option');
                    option.value = acteur.code_acteur;
                    option.textContent = acteur.libelle_long;
                    acteurSelect.appendChild(option);
                });
            })
            .catch(error => console.error("Erreur lors du chargement des acteurs :", error));
    }
    function toggleType() {
        const publicRadio = document.getElementById('public'); 
        const priveRadio = document.getElementById('prive');   
        const optionsPrive = document.getElementById('optionsPrive'); 
        const acteurSelect = document.getElementById('acteurSelect');

        // Si "Public" est sélectionné
        if (publicRadio.checked) {
            optionsPrive.classList.add('d-none'); // Cacher les options pour "Privé"
            fetchActeurs('Public');
        }
        // Si "Privé" est sélectionné
        else if (priveRadio.checked) {
            optionsPrive.classList.remove('d-none'); // Afficher les options pour "Entreprise" ou "Individu"
            acteurSelect.innerHTML = '<option value="">Sélectionnez un acteur</option>';
            const entrepriseRadio = document.getElementById('entreprise');
            const individuRadio = document.getElementById('individu');

        }else{
            optionsPrive.classList.add('d-none');
            acteurSelect.innerHTML = '<option value="">Sélectionnez un acteur</option>';
        }
    }

    // Fonction pour basculer entre "Entreprise" et "Individu" lorsque "Privé" est sélectionné
    function togglePriveFields() {
        const entrepriseRadio = document.getElementById('entreprise'); // Radio "Entreprise"
        const individuRadio = document.getElementById('individu');     // Radio "Individu"
        const acteurSelect = document.getElementById('acteurSelect');

        // Si "Entreprise" est sélectionné
        if (entrepriseRadio.checked) {
            fetchActeurs('Privé', 'Entreprise');
        }
        // Si "Individu" est sélectionné
        else if (individuRadio.checked) {
            fetchActeurs('Privé', 'Individu');
        }
    }
    // Fonction pour récupérer les acteurs via API

    // Ajout des écouteurs d'événements sur les éléments pour assurer le bon fonctionnement
    document.addEventListener("DOMContentLoaded", function () {
        // Écouter les changements sur les checkboxes "Public" et "Privé"
        document.getElementById('public').addEventListener('change', toggleType);
        document.getElementById('prive').addEventListener('change', toggleType);

        // Écouter les changements sur les radios "Entreprise" et "Individu"
        document.getElementById('entreprise').addEventListener('change', togglePriveFields);
        document.getElementById('individu').addEventListener('change', togglePriveFields);
    });
    document.addEventListener('DOMContentLoaded', function () {
        const acteurSelect2 = document.getElementById('acteurSelect');
        const secteurActiviteContainer2 = document.getElementById('sectActivEnt').parentElement;
        
        if ( !acteurSelect2 || !secteurActiviteContainer2 ) {
            console.error("Les éléments HTML avec les identifiants 'acteurMoeSelect' ou 'sectActivEntMoe' n'ont pas été trouvés.");
            return;
        }
        
        acteurSelect2.addEventListener('change', function () {
            const selectedValue2 = acteurSelect2.value;
            console.log("Valeur sélectionnée :", selectedValue2);


            if (selectedValue2 === '5689') {
                // Afficher le secteur d'activité
                secteurActiviteContainer2.style.display = 'block';
            } else {
                // Masquer le secteur d'activité
                secteurActiviteContainer2.style.display = 'none';
            }
        });

        // Initialiser l'affichage en fonction de la sélection actuelle
        
        if (acteurSelect2.value === '5689') {
            secteurActiviteContainer2.style.display = 'block';
        } else {
            secteurActiviteContainer2.style.display = 'none';
        }
    });
</script>