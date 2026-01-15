{{-- STEP 3 --}}
<div class="step" id="step-3">
@isset($ecran)
@can("consulter_ecran_" . $ecran->id)

<h5 class="text-secondary">👥 Liste des bénéficiaires du projet</h5>

<div class="alert alert-info" id="projet-info" style="display:none;">
    <strong>Projet :</strong> <span id="projet-code"></span> — <span id="projet-libelle"></span>
</div>

<div class="card mt-3">
  <div class="card-body">
    <h6 class="mb-3">Bénéficiaires associés au projet</h6>

    <div class="table-responsive">
      <table class="table table-bordered align-middle" id="tableBeneficiaires">
        <thead class="table-light">
          <tr>
            <th style="width:15%">Code</th>
            <th style="width:35%">Libellé</th>
            <th style="width:15%">Type</th>
            <th style="width:15%">Pays</th>
            <th style="width:20%">Informations supplémentaires</th>
          </tr>
        </thead>
        <tbody>
          <tr><td colspan="5" class="text-center text-muted">Chargement en cours...</td></tr>
        </tbody>
      </table>
    </div>

    <div id="noBenefMessage" class="text-muted small mt-2" style="display:none;">
      Aucun bénéficiaire n’est enregistré pour ce projet.
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
    <button type="button" class="btn btn-primary" onclick="nextStep()">
      Suivant <i class="fas fa-arrow-right"></i>
    </button>
  </div>
</div>

@endcan
@endisset
</div>

<script>
(function() {
    const codeProjet = localStorage.getItem('code_projet_temp');
    const libelleProjet = localStorage.getItem('libelle_projet_temp');

    const projetInfo = document.getElementById('projet-info');
    const projetCode = document.getElementById('projet-code');
    const projetLibelle = document.getElementById('projet-libelle');
    const tbody = document.querySelector('#tableBeneficiaires tbody');
    const noBenefMessage = document.getElementById('noBenefMessage');

    if (codeProjet) {
        projetInfo.style.display = 'block';
        projetCode.textContent = codeProjet;
        projetLibelle.textContent = libelleProjet ?? '';
    }

    // Charger les bénéficiaires du projet
    function loadBeneficiaires() {
        if (!codeProjet) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Aucun projet sélectionné (retournez à l’étape 2)</td></tr>';
            return;
        }

        fetch(`{{ url('/') }}/projets/${encodeURIComponent(codeProjet)}/beneficiaires`)
            .then(res => res.json())
            .then(data => {
                const beneficiaires = data.beneficiaires || [];
                renderBeneficiaires(beneficiaires);
            })
            .catch(err => {
                console.error('[STEP3] Erreur de chargement :', err);
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Erreur de chargement des bénéficiaires</td></tr>';
            });
    }

    function renderBeneficiaires(list) {
        tbody.innerHTML = '';
        if (!list.length) {
            noBenefMessage.style.display = 'block';
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Aucun bénéficiaire trouvé</td></tr>';
            return;
        }

        noBenefMessage.style.display = 'none';
        list.forEach(b => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${b.code ?? ''}</td>
                <td>${b.libelle ?? ''}</td>
                <td>${b.type ?? ''}</td>
                <td>${b.code_pays ?? ''}</td>
                <td>${b.extra ?? ''}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    loadBeneficiaires();
})();
</script>
