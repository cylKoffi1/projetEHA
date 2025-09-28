@extends('layouts.app')

@section('content')
<div class="container py-3">
  <div class="d-flex align-items-center mb-3">
    <h3 class="me-auto">{{ isset($workflowId) ? 'Éditer' : 'Créer' }} un workflow</h3>
  </div>

  {{-- Bloc workflow (métadonnées) --}}
  <div class="card mb-3">
    <div class="card-body">
      <div class="row g-3">
        @if(isset($workflowId) && isset($prefill['workflow']['code']))
          <div class="col-12">
            <label class="form-label d-block">Code</label>
            <span class="badge bg-secondary" id="wf-code-badge">{{ $prefill['workflow']['code'] }}</span>
          </div>
        @endif

        <div class="col-md-6">
          <label class="form-label">Libelle <span class="text-danger">*</span></label>
          <input id="wf-nom" class="form-control" placeholder="Validation Étude de Projet" required>
        </div>

        <div class="col-md-3">
          <label class="form-label">Pays <span class="text-danger" >*</span></label>
          <select id="wf-pays" class="form-select" required disabled>
            <option value="" disabled selected>-- Sélectionner --</option>
            @foreach(($ctx['pays_options'] ?? []) as $p)
              <option value="{{ $p['alpha3'] }}">{{ $p['nom_fr_fr'] }} ({{ $p['alpha3'] }})</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-3">
          <label class="form-label">Groupe projet (optionnel)</label>
          <select id="wf-groupe" class="form-select" disabled>
            <option value="">-- Aucun --</option>
            @foreach(($ctx['groupe_options'] ?? []) as $g)
              <option value="{{ $g['code'] }}">{{ $g['libelle'] }} ({{ $g['code'] }})</option>
            @endforeach
          </select>
        </div>
      </div>
    </div>
  </div>

  {{-- Bloc version --}}
  <div class="card mb-3">
    <div class="card-body">
      <h5 class="mb-3">Version</h5>
      <div class="row g-3">
        <div class="col-md-3">
          <label class="form-label">Numéro de version</label>
          <input id="v-num" type="number" min="1" class="form-control" placeholder="1">
        </div>
        <div class="col-md-5">
            <label class="form-label">Politique de changement</label>
            <select id="v-policy" class="form-select">
                <option value="NE_JAMAIS_REAPPROUVER">Aucune ré-approbation</option>
                <option value="TOUJOURS_REAPPROUVER">Toujours ré-approbation</option>
                <option value="REAPPROUVER_SUR_RISQUE" selected>Ré-approbation si risque</option>
            </select>
            <small class="text-muted d-block mt-1">
                Détermine si les validations doivent être relancées quand un objet déjà approuvé est modifié.
            </small>
            </div>

        <div class="col-md-2 d-flex align-items-center">
          <div class="form-check mt-4">
            <input id="v-publie" type="checkbox" class="form-check-input">
            <label class="form-check-label ms-1">Publier ?</label>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Bloc étapes --}}
  <div class="card mb-3">
    <div class="card-body">
      <div class="d-flex mb-2 align-items-center">
        <h5 class="me-auto mb-0">Étapes</h5>
        <button class="btn btn-sm btn-outline-primary" id="btn-add-step" type="button">+ Étape</button>
      </div>
      <div id="steps"></div>
    </div>
  </div>

  {{-- Actions --}}
    <div class="d-flex gap-2">
        @if(!isset($workflowId))
            <button id="btn-save-create" class="btn btn-primary" type="button">
            Créer le workflow
            </button>
        @else
            <button id="btn-save-update" class="btn btn-primary" type="button" data-mode="update_existing">
            Mettre à jour cette version
            </button>
            <button id="btn-save-new" class="btn btn-outline-primary" type="button" data-mode="new_version">
            Créer une nouvelle version
            </button>
            <button id="btn-publish" class="btn btn-success" type="button">
            Publier la version
            </button>
        @endif
    </div>

</div>

{{-- ======= Templates ======= --}}
<template id="tpl-step">
  <div class="border rounded p-3 mb-3 step-item">
    <div class="d-flex gap-2 align-items-center">
      <strong>Étape</strong>
      <input class="form-control form-control-sm step-position" type="number" min="1" style="width:100px" placeholder="Position" required>
      <select class="form-select form-select-sm step-mode" style="width:160px">
        <option value="SERIAL">Série</option>
        <option value="PARALLEL">Parallèle</option>
      </select>
      <input class="form-control form-control-sm step-quorum" type="number" min="1" style="width:120px" placeholder="Quorum" value="1">
      <input class="form-control form-control-sm step-sla" type="number" min="1" style="width:140px" placeholder="SLA (heures)">
      <div class="form-check ms-2">
        <input class="form-check-input step-deleg" type="checkbox" checked>
        <label class="form-check-label">Délégation OK</label>
      </div>
      <div class="form-check ms-2">
        <input class="form-check-input step-skip" type="checkbox" checked>
        <label class="form-check-label">Sauter si vide</label>
      </div>
      <button class="btn btn-sm btn-outline-danger ms-auto btn-del-step" type="button">Suppr</button>
    </div>

    <div class="mt-2">
      <label class="form-label">Politique de ré-approbation (JSON)</label>
      <input class="form-control form-control-sm step-reapprove" placeholder='{"montant_delta_pct":10}'>
    </div>

    <div class="row mt-3 g-2">
      <div class="col-md-6">
        <div class="d-flex align-items-center mb-2">
          <strong class="me-auto">Approbateurs</strong>
          <button class="btn btn-sm btn-light btn-add-approver" type="button">+ Ajouter</button>
        </div>
        <div class="approvers"></div>
      </div>
      <div class="col-md-6">
        <div class="d-flex align-items-center mb-2">
          <strong class="me-auto">Règles</strong>
          <button class="btn btn-sm btn-light btn-add-rule" type="button">+ Ajouter</button>
        </div>
        <div class="rules"></div>
      </div>
    </div>
  </div>
</template>

<template id="tpl-approver">
  <div class="input-group input-group-sm mb-1 approver">
    <select class="form-select ap-type" style="max-width:160px">
      <option value="ACTEUR" selected>Utilisateur</option>
      <option value="ROLE">Rôle</option>
      <option value="GROUPE">Groupe</option>
    </select>

    {{-- Select pour ACTEUR (peuplé serveur) --}}
    <select class="form-select ap-ref-select" data-mode="ACTEUR" style="min-width:300px">
      <option value="">— Sélectionner un utilisateur —</option>
      @isset($approverUsers)
        @foreach($approverUsers as $u)
          <option value="{{ $u->acteur_id }}">
            {{ $u->acteur?->libelle_long ?? $u->login }} — {{ $u->email }}
          </option>
        @endforeach
      @endisset
    </select>

    {{-- Input texte pour ROLE/GROUPE (masqué par défaut) --}}
    <input class="form-control ap-ref-input d-none" placeholder="code_role / code_groupe">

    <div class="input-group-text">
      <input class="form-check-input mt-0 ap-required" type="checkbox"> Obligatoire
    </div>
    <button class="btn btn-outline-danger btn-del-approver" type="button">×</button>
  </div>
</template>

<template id="tpl-rule">
  <div class="input-group input-group-sm mb-1 rule">
    <input class="form-control r-field" placeholder="champ (ex: montant)">
    <select class="form-select r-op" style="max-width:220px">
        <option value="EQ">= (Égal à)</option>
        <option value="NE">≠ (Différent de)</option>
        <option value="GT">< (Supérieur à)</option>
        <option value="GTE"><= (Supérieur ou égal à)</option>
        <option value="LT">> (Inférieur à)</option>
        <option value="LTE">>= (Inférieur ou égal à)</option>
        <option value="IN">∈ (Dans la liste)</option>
        <option value="NOT_IN">∉ (Pas dans la liste)</option>
        <option value="BETWEEN">↔ (Entre min et max)</option>
    </select>
    <input class="form-control r-val" placeholder='valeur JSON (ex: 1000000 ou ["A","B"])'>
    <button class="btn btn-outline-danger btn-del-rule" type="button">×</button>
  </div>
</template>

{{-- ======= Script ======= --}}
<script>
const WF_ID = {!! isset($workflowId) ? (int)$workflowId : 'null' !!};

/* ========== Helpers UI ========== */
function addStep(prefill = {}) {
  const tpl = document.getElementById('tpl-step').content.cloneNode(true);
  const root = tpl.querySelector('.step-item');

  root.querySelector('.step-position').value = prefill.position ?? '';
  root.querySelector('.step-mode').value     = prefill.mode_code ?? 'SERIAL';
  root.querySelector('.step-quorum').value   = prefill.quorum ?? 1;
  root.querySelector('.step-sla').value      = prefill.sla_heures ?? '';
  root.querySelector('.step-deleg').checked  = !!(prefill.delegation_autorisee ?? 1);
  root.querySelector('.step-skip').checked   = !!(prefill.sauter_si_vide ?? 1);
  root.querySelector('.step-reapprove').value = prefill.politique_reapprobation ? JSON.stringify(prefill.politique_reapprobation) : '';

  root.querySelector('.btn-del-step').onclick  = () => root.remove();
  root.querySelector('.btn-add-approver').onclick = () => addApprover(root);
  root.querySelector('.btn-add-rule').onclick  = () => addRule(root);

  document.getElementById('steps').appendChild(root);

  (prefill.approbateurs || []).forEach(a => addApprover(root, a));
  (prefill.regles || []).forEach(r => addRule(root, r));
}

function addApprover(stepRoot, prefill = {}) {
  const tpl = document.getElementById('tpl-approver').content.cloneNode(true);
  const row = tpl.querySelector('.approver');

  const $type = row.querySelector('.ap-type');
  const $sel  = row.querySelector('.ap-ref-select');
  const $txt  = row.querySelector('.ap-ref-input');

  // valeurs par défaut
  $type.value = prefill.type_approbateur ?? 'ACTEUR';
  row.querySelector('.ap-required').checked = !!(prefill.obligatoire ?? 0);
  row.querySelector('.btn-del-approver').onclick = () => row.remove();

  // Pré-sélection si ACTEUR
  if ($type.value === 'ACTEUR') {
    $sel.classList.remove('d-none');
    $txt.classList.add('d-none');
    if (prefill.reference_approbateur) {
      $sel.value = prefill.reference_approbateur;
    }
  } else {
    // ROLE / GROUPE -> input texte
    $sel.classList.add('d-none');
    $txt.classList.remove('d-none');
    $txt.value = prefill.reference_approbateur ?? '';
  }

  // Toggle selon le type
  $type.addEventListener('change', () => {
    if ($type.value === 'ACTEUR') {
      $sel.classList.remove('d-none');
      $txt.classList.add('d-none');
    } else {
      $sel.classList.add('d-none');
      $txt.classList.remove('d-none');
    }
  });

  stepRoot.querySelector('.approvers').appendChild(row);
}


function addRule(stepRoot, prefill = {}) {
  const tpl = document.getElementById('tpl-rule').content.cloneNode(true);
  const row = tpl.querySelector('.rule');

  row.querySelector('.r-field').value = prefill.champ ?? '';
  row.querySelector('.r-op').value    = prefill.operateur_code ?? 'EQ';
  row.querySelector('.r-val').value   = prefill.valeur ? JSON.stringify(prefill.valeur) : '';
  row.querySelector('.btn-del-rule').onclick = () => row.remove();

  stepRoot.querySelector('.rules').appendChild(row);
}

function collectPayload() {
  const steps = [];
  document.querySelectorAll('#steps .step-item').forEach(s => {
    const approbateurs = [];
    s.querySelectorAll('.approver').forEach(a => {
    const type = a.querySelector('.ap-type').value;
    let ref;
    if (type === 'ACTEUR') {
        ref = a.querySelector('.ap-ref-select').value || '';
    } else {
        ref = a.querySelector('.ap-ref-input').value.trim();
    }

    approbateurs.push({
        type_approbateur: type,
        reference_approbateur: ref,
        obligatoire: a.querySelector('.ap-required').checked
    });
    });


    const regles = [];
    s.querySelectorAll('.rule').forEach(r => {
      let val;
      const raw = r.querySelector('.r-val').value;
      if (raw && raw.trim() !== '') {
        try { val = JSON.parse(raw); }
        catch(e) { val = raw.trim(); }
      } else { val = null; }

      regles.push({
        champ: r.querySelector('.r-field').value.trim(),
        operateur_code: r.querySelector('.r-op').value,
        valeur: val
      });
    });

    let reapp = null;
    const txt = s.querySelector('.step-reapprove').value.trim();
    if (txt) { try { reapp = JSON.parse(txt); } catch(e) { reapp = null; } }

    steps.push({
      position: parseInt(s.querySelector('.step-position').value || '0', 10),
      mode_code: s.querySelector('.step-mode').value,
      quorum: parseInt(s.querySelector('.step-quorum').value || '1', 10),
      sla_heures: s.querySelector('.step-sla').value ? parseInt(s.querySelector('.step-sla').value, 10) : null,
      delegation_autorisee: s.querySelector('.step-deleg').checked,
      sauter_si_vide: s.querySelector('.step-skip').checked,
      politique_reapprobation: reapp,
      approbateurs, regles
    });
  });

  return {
    // PAS de 'code' : il est généré côté serveur
    nom:  document.getElementById('wf-nom').value.trim(),
    code_pays: document.getElementById('wf-pays').value,
    groupe_projet_id: (document.getElementById('wf-groupe').value || null),
    mode_version: window.__mode_version || 'new_version',  // <— ICI
    version: {
      numero_version: parseInt(document.getElementById('v-num').value || '1', 10),
      politique_changement: document.getElementById('v-policy').value.trim() || 'REAPPROUVER_SUR_RISQUE',
      publie: document.getElementById('v-publie').checked,
      etapes: steps
    }
  };
}
@if(!isset($workflowId))
document.getElementById('btn-save-create').addEventListener('click', () => {
  window.__mode_version = 'new_version';
  savePayload(); // appelle la fonction commune ci-dessous
});
@else
document.getElementById('btn-save-update').addEventListener('click', () => {
  window.__mode_version = 'update_existing';
  savePayload();
});
document.getElementById('btn-save-new').addEventListener('click', () => {
  window.__mode_version = 'new_version';
  savePayload();
});
@endif

async function savePayload() {
  const payload = collectPayload();
  const url = WF_ID
    ? `{{ route('workflows.update', ['id' => '__ID__']) }}`.replace('__ID__', WF_ID)
    : `{{ route('workflows.store') }}`;
  const method = WF_ID ? 'PUT' : 'POST';

  const res = await fetch(url, {
    method,
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': '{{ csrf_token() }}',
      'Accept': 'application/json'
    },
    body: JSON.stringify(payload)
  });

  const data = await res.json();
  if (!res.ok) {
    alert((data.message || data.error || 'Erreur') + '\n' + JSON.stringify(data));
    return;
  }

  alert(data.message || 'OK');
  window.location = '{{ route('workflows.index') }}';
}
/* ========== Actions ========== */
document.getElementById('btn-add-step').addEventListener('click', () => addStep());

document.getElementById('btn-save').addEventListener('click', async function () {
  // petite validation rapide côté client
  if (!document.getElementById('wf-nom').value.trim()) {
    alert('Le nom est requis.');
    return;
  }
  if (!document.getElementById('wf-pays').value) {
    alert('Le pays est obligatoire.');
    return;
  }

  const payload = collectPayload();

  const url = WF_ID
    ? `{{ route('workflows.update', ['id' => '__ID__']) }}`.replace('__ID__', WF_ID)
    : `{{ route('workflows.store') }}`;

  const method = WF_ID ? 'PUT' : 'POST';

  const res = await fetch(url, {
    method,
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': '{{ csrf_token() }}',
      'Accept': 'application/json'
    },
    body: JSON.stringify(payload)
  });

  const data = await res.json();
  if (!res.ok) {
    alert((data.message || data.error || 'Erreur') + '\n' + JSON.stringify(data));
    return;
  }

  alert('Enregistré.');
  window.location = '{{ route('workflows.index') }}';
});

@if(isset($workflowId))
document.getElementById('btn-publish').addEventListener('click', async function () {
  const num = parseInt(document.getElementById('v-num').value || '1', 10);
  const url = `{{ route('workflows.publish', ['id' => '__ID__']) }}`.replace('__ID__', WF_ID);

  const res = await fetch(url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': '{{ csrf_token() }}',
      'Accept': 'application/json'
    },
    body: JSON.stringify({ numero_version: num })
  });

  const data = await res.json();
  if (!res.ok) {
    alert((data.message || data.error || 'Erreur') + '\n' + JSON.stringify(data));
    return;
  }

  alert('Version publiée.');
});
@endif

/* ========== Pré-remplissage contrôleur ========== */
(function preload(){
  const wf  = @json($prefill['workflow'] ?? []);
  const ver = @json($prefill['version'] ?? []);
  const ctx = @json($ctx ?? []);

  // nom
  document.getElementById('wf-nom').value = wf.nom || '';

  // pays : priorité à prefill -> sinon contexte sélectionné -> sinon placeholder
  const paysSelect = document.getElementById('wf-pays');
  const selectedPays = (wf.code_pays || ctx.pays_selected || '');
  if (selectedPays) {
    const opt = Array.from(paysSelect.options).find(o => o.value === selectedPays);
    if (opt) opt.selected = true;
  }

  // groupe projet : priorité à prefill -> sinon contexte sélectionné
  const gpSelect = document.getElementById('wf-groupe');
  const selectedGp = (wf.groupe_projet_id || ctx.projet_selected || '');
  if (selectedGp) {
    const opt = Array.from(gpSelect.options).find(o => o.value === selectedGp);
    if (opt) opt.selected = true;
  }

  // version
  document.getElementById('v-num').value    = ver.numero_version || 1;
  document.getElementById('v-policy').value = ver.politique_changement || 'REAPPROUVER_SUR_RISQUE';
  document.getElementById('v-publie').checked = !!ver.publie;

  // étapes
  (ver.etapes || []).forEach(e => addStep({
    position: e.position,
    mode_code: e.mode, // converti côté contrôleur
    quorum: e.quorum,
    sla_heures: e.sla_heures,
    delegation_autorisee: e.delegation_autorisee,
    sauter_si_vide: e.sauter_si_vide,
    politique_reapprobation: e.politique_reapprobation,
    approbateurs: e.approbateurs,
    regles: e.regles
  }));
})();
</script>
@endsection
