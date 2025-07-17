<div class="step" id="step-6">
    <h5 class="text-secondary">💰 Ressources Financières</h5>

    <div class="col-2 mb-3">
        <label for="typeFinancement">Type de financement</label>
        <select id="typeFinancement" name="type_financement" class="form-control">
            <option value="">Slectionner le type</option>
            @foreach ($typeFinancements as $typeFinancement)
                <option value="{{ $typeFinancement->code_type_financement }}">{{ $typeFinancement->libelle }}</option>
            @endforeach
        </select>
    </div>

    <div class="row">
        <div class="col-1">
            <label>Local</label><br>
            <div class="form-check form-check-inline">
                <input type="radio" id="BailOui" name="BaillOui" value="1" class="form-check-input">
                <label for="BailOui" class="form-check-label">Oui</label>
            </div>
            <div class="form-check form-check-inline">
                <input type="radio" id="BailNon" name="BaillOui" value="0" class="form-check-input">
                <label for="BailNon" class="form-check-label">Non</label>
            </div>
        </div>

        <div class="col">
            <label for="bailleur">Bailleur</label>
            <lookup-select name="bailleur" id="bailleur">
                <option value="">Sélectionner le bailleur</option>
                @foreach ($bailleurActeurs as $bailleurActeur)
                    <option value="{{ $bailleurActeur->code_acteur }}">{{ $bailleurActeur->libelle_court }} {{ $bailleurActeur->libelle_long }}</option>
                @endforeach
            </lookup-select>
        </div>

        <div class="col-md-2">
            <label for="montant">Montant</label>
            <input type="number" id="montant" class="form-control" placeholder="Montant">
        </div>

        <div class="col-md-1">
            <label for="deviseBailleur">Devise</label>
            <input type="text" id="deviseBailleur" class="form-control" value="{{ $Devises[0]->code_devise ?? 'XOF' }}" readonly>
        </div>

        <div class="col-md-3">
            <label for="commentaire">Commentaire</label>
            <input type="text" id="commentaire" class="form-control" placeholder="Commentaire">
        </div>

        <div class="col text-end">
            <button type="button" class="btn btn-secondary" id="addFinancementBtn">Ajouter</button>
        </div>
    </div>

    <div class="mt-4">
        <table class="table table-bordered" id="tableFinancements">
            <thead>
                <tr>
                    <th>Bailleur</th>
                    <th>Montant</th>
                    <th>Devise</th>
                    <th>Local</th>
                    <th>Commentaire</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="row">
        <div class="col">
            <button type="button" class="btn btn-secondary" onclick="prevStep()"><i class="fas fa-arrow-left"></i> Précédent</button>
        </div>
        <div class="col text-end">
            <button type="button" class="btn btn-primary" onclick="saveStep6(nextStep)">Suivant <i class="fas fa-arrow-right"></i> </button>
        </div>
    </div>
</div>

<script>
    let financementIndex = 0;

    document.getElementById('addFinancementBtn').addEventListener('click', function () {
        const bailleurLookup = document.getElementById('bailleur');
        const selected = bailleurLookup?.getSelected?.();

        if (!selected || !selected.value) {
            alert("Veuillez sélectionner un bailleur.");
            return;
        }

        const bailleurText = selected.text;
        const bailleurValue = selected.value;
        const montant = document.getElementById('montant').value;
        const devise = document.getElementById('deviseBailleur').value;
        const commentaire = document.getElementById('commentaire').value;

        const localRadio = document.querySelector('input[name="BaillOui"]:checked');
        const localValue = localRadio ? localRadio.value : '';

        if (!montant || !devise || localValue === '') {
            alert("Veuillez remplir tous les champs obligatoires.");
            return;
        }

        const newRow = `
            <tr>
                <td>
                    ${bailleurText}
                    <input type="hidden" name="financements[${financementIndex}][bailleur]" value="${bailleurValue}">
                </td>
                <td>
                    ${montant}
                    <input type="hidden" name="financements[${financementIndex}][montant]" value="${montant}">
                </td>
                <td>
                    ${devise}
                    <input type="hidden" name="financements[${financementIndex}][devise]" value="${devise}">
                </td>
                <td>
                    ${localValue == 1 ? 'Oui' : 'Non'}
                    <input type="hidden" name="financements[${financementIndex}][local]" value="${localValue}">
                </td>
                <td>
                    ${commentaire}
                    <input type="hidden" name="financements[${financementIndex}][commentaire]" value="${commentaire}">
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm removeRow"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `;

        document.querySelector("#tableFinancements tbody").insertAdjacentHTML("beforeend", newRow);
        financementIndex++;

        // Reset
        document.getElementById('montant').value = '';
        document.getElementById('commentaire').value = '';
        document.getElementById('BailOui').checked = false;
        document.getElementById('BailNon').checked = false;

        if (bailleurLookup && bailleurLookup.shadowRoot) {
            bailleurLookup.value = null;
            bailleurLookup.shadowRoot.querySelector("input").value = '';
        }
    });

    // Suppression de ligne
    document.getElementById('tableFinancements').addEventListener('click', function (e) {
        if (e.target.closest('.removeRow')) {
            e.target.closest('tr').remove();
        }
    });

    // Sauvegarde
    function saveStep6(callback = null) {
        const codeProjet = localStorage.getItem("code_projet_temp");
        if (!codeProjet) return alert("Projet non trouvé.");

        const typeFinancement = document.getElementById("typeFinancement").value;
        localStorage.setItem("type_financement", typeFinancement);

        const financements = [];

        document.querySelectorAll("#tableFinancements tbody tr").forEach(row => {
            const bailleur = row.querySelector('input[name$="[bailleur]"]').value;
            const montant = row.querySelector('input[name$="[montant]"]').value;
            const devise = row.querySelector('input[name$="[devise]"]').value;
            const local = row.querySelector('input[name$="[local]"]').value;
            const commentaire = row.querySelector('input[name$="[commentaire]"]').value;

            financements.push({ bailleur, montant, devise, local, commentaire });
        });

        if (financements.length === 0) {
            alert("Aucun financement ajouté.");
            return;
        }

        $.ajax({
            url: '{{ route("projets.temp.save.step6") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                code_projet: codeProjet,
                type_financement: typeFinancement,
                financements: financements
            },
            success: function (res) {
                nextStep();
                if (typeof callback === 'function') callback();
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