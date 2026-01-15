{{-- STEP 1 — Informations générales (ETUDE) --}}
<div class="step active" id="step-1">
@isset($ecran)
@can("consulter_ecran_" . $ecran->id)

@php
  // Si dans le contrôleur tu as: $NaturesTravaux = NatureTravaux::orderBy('libelle')->get();
  // on sécurise un "par défaut" (le premier).
  $nature = ($NaturesTravaux instanceof \Illuminate\Support\Collection) ? $NaturesTravaux->first() : $NaturesTravaux;
@endphp

<h5 class="text-secondary">📋 Informations générales de l’étude</h5>

<div class="row mb-3">
  <div class="col-4">
    <label>Nature des travaux *</label>
    <input type="hidden" name="natureTraveaux" id="natureTraveaux" value="{{ $nature->code ?? '' }}">
    <input type="text"  class="form-control" id="natureTraveauxLibelle"
           value="{{ $nature->libelle ?? '' }}" readonly>
  </div>

  <div class="col-4">
    <label>Groupe de Projet *</label>
    <select class="form-control" name="groupe_projet" disabled>
      <option value="">Sélectionner un groupe</option>
      @foreach ($GroupeProjets as $groupe)
        <option value="{{ $groupe->code }}" {{ ($groupeSelectionne ?? '') == $groupe->code ? 'selected' : '' }}>
          {{ $groupe->libelle }}
        </option>
      @endforeach
    </select>
  </div>

  <div class="col-4">
    <label>Intitulé de l’étude *</label>
    <input type="text" class="form-control" id="nomProjet" name="nomProjet" placeholder="Intitulé" required>
  </div>
</div>

<div class="row mb-3">
  <div class="col">
    <label>Domaine *</label>
    <select name="domaine" id="domaineSelect" class="form-control">
      <option value="">Sélectionner domaine</option>
      @foreach ($Domaines as $domaine)
        <option value="{{ $domaine->code }}">{{ $domaine->libelle }}</option>
      @endforeach
    </select>
  </div>

  <div class="col">
    <label>Sous-domaine *</label>
    <select name="SousDomaine" id="sousDomaineSelect" class="form-control" disabled>
      <option value="">Sélectionner sous-domaine</option>
    </select>
  </div>

  <div class="col">
    <label>Date début prévisionnelle *</label>
    <input type="date" class="form-control" id="dateDemarragePrev">
  </div>
  <div class="col">
    <label>Date fin prévisionnelle *</label>
    <input type="date" class="form-control" id="dateFinPrev">
  </div>
</div>

<div class="row mb-3">
  <div class="col-md-3">
    <label>Budget prévisionnel</label>
    <input type="text" id="coutProjet" class="form-control text-end" oninput="formatNumber(this)">
  </div>
  <div class="col-md-2">
    <label>Devise</label>
    <input type="text" id="deviseCout" class="form-control" value="{{ $deviseCouts->code_long ?? 'XOF' }}" readonly>
  </div>
  <div class="col-md-3">
    <label>Type d’étude *</label>
    <select id="codeTypeEtude" class="form-control">
      <option value="">Sélectionner…</option>
      @foreach (($EtudeTypes ?? []) as $t)
        <option value="{{ $t->code }}">{{ $t->libelle }}</option>
      @endforeach
    </select>
  </div>
</div>

<div class="mb-3">
  <label>Commentaire / Description</label>
  <textarea class="form-control" id="commentaireProjet" rows="3" placeholder="Objectif, contexte…"></textarea>
</div>

<hr class="my-3">

{{-- Livrables attendus (sans dates ni statut) --}}
<div class="card">
  <div class="card-body">
    <div class="row g-3 align-items-end mb-2">
      <div class="col-12 col-md-3">
        <label>📦 Livrable</label>
        <select id="livrableSelect" class="form-control">
          <option value="">Sélectionner un livrable</option>
          @foreach(($Livrables ?? []) as $liv)
            <option value="{{ $liv->id }}">{{ $liv->libelle }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-12 col-md-6">
        <label>Précisions (optionnel)</label>
        <textarea id="livrablesCommentaires" class="form-control" rows="2"
          placeholder="Ex: maquette BIM, fiche de synthèse, etc."></textarea>
      </div>
      <div class="col-12 col-md-3 text-end">
        <button type="button" class="btn btn-outline-primary" id="btnAddLivrable">
          <i class="bi bi-plus-circle"></i> Ajouter
        </button>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered align-middle" id="tableLivrables">
        <thead class="table-light">
          <tr>
            <th hidden style="width:25%">ID</th>
            <th style="width:45%">Libellé</th>
            <th style="width:25%">Commentaire</th>
            <th style="width:5%" class="text-center">Action</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>

  </div>
</div>

<br>

<div class="row">
  <div class="col text-end">
    @can("ajouter_ecran_" . $ecran->id)
    <button type="button" class="btn btn-primary" onclick="saveEtudeStep1(nextStep)">
      Suivant <i class="fas fa-arrow-right"></i>
    </button>
    @endcan
  </div>
</div>

@endcan
@endisset
</div>

<script>
(function () {
  // -- Livrables en mémoire: { livrable_id, libelle, commentaire }
  const livrables = [];

  // Domaines -> Sous-domaines
  document.addEventListener("DOMContentLoaded", function () {
    const dom = document.getElementById('domaineSelect');
    const sd  = document.getElementById('sousDomaineSelect');
    const d1  = document.getElementById("dateDemarragePrev");
    const d2  = document.getElementById("dateFinPrev");

    dom?.addEventListener('change', function () {
      const code = this.value;
      sd.innerHTML = '<option value="">Chargement…</option>';
      sd.disabled = true;

      if (!code) { sd.innerHTML = '<option value="">Sélectionner sous-domaine</option>'; return; }

      fetch(`{{ url('/') }}/get-sous-domaines/${code}`)
        .then(r => r.json())
        .then(rows => {
          sd.innerHTML = '<option value="">Sélectionner sous-domaine</option>';
          (rows || []).forEach(x => {
            sd.add(new Option(x.lib_sous_domaine, x.code_sous_domaine));
          });
          sd.disabled = false;
        })
        .catch(() => { sd.innerHTML = '<option value="">Erreur</option>'; });
    });

    function validDates() {
      if (!d1.value || !d2.value) return;
      const a = new Date(d1.value), b = new Date(d2.value);
      if (a > b) { alert("La date de début ne peut pas être postérieure à la date de fin."); d2.value = ""; }
    }
    d1?.addEventListener('change', validDates);
    d2?.addEventListener('change', validDates);
  });

  // -- Table Livrables
  const tbody = () => document.querySelector('#tableLivrables tbody');

  function renderLivrables() {
    const t = tbody();
    t.innerHTML = '';
    livrables.forEach((L, i) => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td hidden><code>${L.livrable_id}</code></td>
        <td>${L.libelle || ''}</td>
        <td>${L.commentaire || ''}</td>
        <td class="text-center">
          <button type="button" data-idx="${i}" class="btn btn-sm btn-outline-danger btn-remove-livrable">
            <i class="bi bi-trash"></i>
          </button>
        </td>`;
      t.appendChild(tr);
    });

    t.querySelectorAll('.btn-remove-livrable').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const idx = +e.currentTarget.dataset.idx;
        livrables.splice(idx, 1);
        renderLivrables();
      });
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    const btnAdd = document.getElementById('btnAddLivrable');
    btnAdd?.addEventListener('click', () => {
      const sel = document.getElementById('livrableSelect');
      const id  = sel.value;
      const lib = sel.options[sel.selectedIndex]?.text || '';
      const cmt = document.getElementById('livrablesCommentaires').value || '';

      if (!id) { alert('Choisir un livrable.'); return; }
      if (livrables.some(x => String(x.livrable_id) === String(id))) {
        alert('Ce livrable est déjà ajouté.');
        return;
      }

      livrables.push({
        livrable_id: id,
        libelle: lib,
        commentaire: cmt
      });

      // reset champ commentaire
      document.getElementById('livrablesCommentaires').value = '';
      renderLivrables();
    });
  });

  // -- SAVE
  window.saveEtudeStep1 = function (callback = null) {
    const payload = {
      _token: '{{ csrf_token() }}',
      libelle_projet: document.getElementById('nomProjet').value.trim(),
      code_domaine:   document.getElementById('domaineSelect').value,
      code_sous_domaine: document.getElementById('sousDomaineSelect').value,
      date_demarrage_prevue: document.getElementById('dateDemarragePrev').value,
      date_fin_prevue:       document.getElementById('dateFinPrev').value,
      cout_projet:   (document.getElementById('coutProjet').value || '').replace(/\s/g,''),
      code_devise:   document.getElementById('deviseCout').value,
      code_nature:   document.getElementById('natureTraveaux').value,
      code_pays:     '{{ session("pays_selectionne") }}',
      commentaire:   document.getElementById('commentaireProjet').value || null,

      // Nouveaux champs Étude
      type_etude_code: document.getElementById('codeTypeEtude').value,

      // ⚠️ Sans dates ni statut — on envoie seulement l’ID + commentaire
      livrables_attendus: livrables.map(L => Number(L.livrable_id)),
      livrables_commentaires: null
    };

    fetch(`{{ route('projet.etude.temp.save.step1') }}`, {
      method: 'POST',
      headers: { 'Content-Type':'application/json', 'Accept':'application/json' },
      body: JSON.stringify(payload)
    })
    .then(async r => {
      const data = await r.json().catch(()=> ({}));
      if (!r.ok) throw new Error(data?.message || 'Erreur de sauvegarde (Step 1).');
      if (data?.success) {
        if (typeof callback === 'function') callback(); else nextStep();
      } else {
        throw new Error(data?.message || 'Erreur Step 1.');
      }
    })
    .catch(err => alert(err.message));
  };
})();
</script>
