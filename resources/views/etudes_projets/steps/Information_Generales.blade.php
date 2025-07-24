<div class="step active" id="step-1">
    <h5 class="text-secondary">📋 Informations Générales</h5>
    <div class="row">
        <div class="col-4">
            <label>Nature des travaux *</label>

            <select name="natureTraveaux" id="natureTraveaux" class="form-control">
                <option>Sélectionner une nature</option>
                @foreach ($NaturesTravaux as $NaturesTravau)
                    <option value="{{ $NaturesTravau->code }}">{{ $NaturesTravau->libelle }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-4">
        <label>Groupe de Projet *</label>
        <select class="form-control" name="groupe_projet" disabled>
            <option value="">Sélectionner un groupe</option>
            @foreach ($GroupeProjets as $groupe)
                <option value="{{ $groupe->code }}"
                    {{ $groupeSelectionne == $groupe->code ? 'selected' : '' }}>
                    {{ $groupe->libelle }}
                </option>
            @endforeach
        </select>

        </div>

        <div class="col-4">
            <label>Nom du Projet *</label>
            <input type="text" class="form-control" id="nomProjet" name="nomProjet" placeholder="Nom du projet" required>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <label for="Domaine">Domaine *</label>
            <select name="domaine" id="domaineSelect" class="form-control">
                <option value="">Sélectionner domaine</option>
                @foreach ($Domaines as $domaine)
                    <option value="{{ $domaine->code }}">{{ $domaine->libelle }}</option>
                @endforeach
            </select>
        </div>

        <div class="col">
            <label for="SousDomaine">Sous-Domaine *</label>
            <select name="SousDomaine" id="sousDomaineSelect" class="form-control">
                <option value="">Sélectionner sous domaine</option>
            </select>
        </div>


        <div class="col">
            <label for="SousDomaine">Date Début prévisionnelle *</label>
            <input type="date" class="form-control" id="dateDemarragePrev">
        </div>
        <div class="col">
            <label for="SousDomaine">Date Fin prévisionnelle *</label>
            <input type="date" class="form-control" id="dateFinPrev">
        </div>

    </div>
    <div class="row">
        <div class="col-md-3">
            <label>Coût du projet</label>
            <input type="text" name="coutProjet" id="coutProjet" class="form-control text-end" oninput="formatNumber(this)">
        </div>
        <div class="col-md-2">
            <label>Devise du coût</label>
            <input type="text" name="code_devise" id="deviseCout" class="form-control" value="{{ $deviseCouts->code_long }}" readonly>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <label for="">Commentaire</label>
            <textarea class="form-control" name="commentaireProjet" id="commentaireProjet"></textarea>
        </div>
    </div>
    <br>


    <div class="row">
        <div class="col text-end">
            <button type="button" class="btn btn-primary" onclick="saveStep1(nextStep)">Suivant <i class="fas fa-arrow-right"></i> </button>
        </div>
    </div>
</div>
<!--Sauvegarde temporaire -->
<script>
    function saveStep1(callback = null) {
        const data = {
            _token: '{{ csrf_token() }}',
            libelle_projet: $('#nomProjet').val(),
            code_sous_domaine: $('#sousDomaineSelect').val(),
            date_demarrage_prevue: $('#dateDemarragePrev').val(),
            date_fin_prevue: $('#dateFinPrev').val(),
            cout_projet: $('#coutProjet').val().replace(/\s/g, ''), // nettoie les espaces
            code_devise: $('#deviseCout').val(),
            commentaire: $('#commentaireProjet').val(),
            code_nature: $('#natureTraveaux').val(),
            code_pays: '{{ session('pays_selectionne') }}', // ajustable selon ton contexte
        };

        $.ajax({
            url: '{{ route("projets.temp.save.step1") }}',
            method: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    console.log("Step 1 sauvegardé temporairement.");
                    localStorage.setItem("code_projet_temp", response.code_projet);
                    if (typeof callback === 'function') callback();
                    updateInfrastructureField();
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

                alert(message);
                console.error("Détail complet :", xhr.responseText);
            }

        });
    }


</script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        
        const dateDebutInput = document.getElementById("dateDemarragePrev");
        const dateFinInput = document.getElementById("dateFinPrev");

        function validerDates() {
            const dateDebut = new Date(dateDebutInput.value);
            const dateFin = new Date(dateFinInput.value);

            if (dateDebutInput.value && dateFinInput.value && dateDebut > dateFin) {
                alert("La date de début ne peut pas être postérieure à la date de fin.",'error');
                // Réinitialise la valeur incorrecte (ici on vide les deux pour laisser le choix)
                dateDebutInput.value = "";
                dateFinInput.value = "";
            }
        }

        dateDebutInput.addEventListener("change", validerDates);
        dateFinInput.addEventListener("change", validerDates);

        // domaine sous domaine
        let domaineSelect = document.getElementById("domaineSelect");
        let sousDomaineSelect = document.getElementById("sousDomaineSelect");

        domaineSelect.addEventListener("change", function () {
            let domaineCode = this.value;

            // Réinitialiser la liste des sous-domaines
            sousDomaineSelect.innerHTML = '<option value="">Sélectionner sous domaine</option>';

            if (domaineCode) {
                fetch(`{{ url('/') }}/get-sous-domaines/${domaineCode}`)
                .then(response => response.json())
                    .then(data => {
                        data.forEach(sousDomaine => {
                            let option = document.createElement("option");
                            option.value = sousDomaine.code_sous_domaine;
                            option.textContent = sousDomaine.lib_sous_domaine;
                            sousDomaineSelect.appendChild(option);
                        });
                        sousDomaineSelect.disabled = false;
                    })
                    .catch(error => console.error("Erreur lors du chargement des sous-domaines :", error));
            } else {
                sousDomaineSelect.disabled = true;
            }
        });
    });
</script>