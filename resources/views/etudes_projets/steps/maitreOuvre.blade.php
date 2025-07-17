<div class="step" id="step-5">
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
            <button type="button" class="btn btn-secondary" id="addMoeuvreBtn" style="heght: 34px">
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
                populateInfrastructureSelect();
                if (typeof callback === "function") {
                    callback();
                } else {
                    nextStep(); // Assure l'affichage du step suivant
                }
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
