{{-- resources/views/GestionDemographie/localitePays.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="page-heading">
  <div class="row align-items-center">
    <div class="col-12 col-md-6">
      <h3>Gestion des localités</h3>
      @isset($ecran)
        <small class="text-muted">Écran : {{ $ecran->libelle ?? $ecran->id }}</small>
      @endisset
    </div>
    <div class="col-12 col-md-6 text-md-end">
      <span id="date-now" class="text-muted"></span>
    </div>
  </div>
</div>

<section class="section">
  <div class="card">
    <div class="card-header">
      <ul class="nav nav-tabs" id="locTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="tab-saisie-tab" data-bs-toggle="tab"
                  data-bs-target="#tab-saisie" type="button" role="tab">
            Saisie unitaire
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="tab-import-tab" data-bs-toggle="tab"
                  data-bs-target="#tab-import" type="button" role="tab">
            Import XLS
          </button>
        </li>
      </ul>
    </div>

    <div class="card-body tab-content">
      {{-- ===================== SAISIE ===================== --}}
      <div class="tab-pane fade show active" id="tab-saisie" role="tabpanel" aria-labelledby="tab-saisie-tab">

        <div class="alert alert-light border d-flex align-items-center gap-2">
          <i class="bi bi-info-circle me-2"></i>
          <div>
            Sélectionne un parent dans les blocs ci-dessous (de gauche à droite).<br>
            Si aucun parent n’est sélectionné, la localité sera créée au <strong>niveau 1</strong>.
          </div>
          <button class="btn btn-sm btn-outline-secondary ms-auto" id="btn-clear-selection">
            Effacer la sélection
          </button>
        </div>

        {{-- Blocs en cascade (générés dynamiquement depuis /admin/localites/schema) --}}
        <div class="row g-3" id="cascades"></div>

        <hr class="my-4">

        {{-- Formulaire de création --}}
        <form id="formLocalite" class="row g-3">@csrf
          {{-- hidden auto-renseignés au submit --}}
          <input type="hidden" name="id_niveau" value="">
          <input type="hidden" name="code_decoupage" value="">
          <input type="hidden" name="parent_code" value="">

          <div class="col-12">
            <div class="p-3 border rounded bg-light">
              <div class="row g-3 align-items-end">
                <div class="col-md-4">
                  <label class="form-label mb-1">Parent sélectionné</label>
                  <div id="parentBadge" class="small text-muted">— aucun —</div>
                </div>

                <div class="col-md-4">
                  <label class="form-label">Type du nouveau niveau (code découpage)</label>
                  <select class="form-select" id="typeNouveauNiveau">
                    <option value="">— auto —</option>
                  </select>
                  <div class="form-text">
                    Par défaut, le premier type disponible au niveau à créer est utilisé.
                  </div>
                </div>

                <div class="col-md-4">
                  <label class="form-label">Niveau calculé</label>
                  <div id="niveauCalc" class="form-control bg-body-tertiary">—</div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Libellé *</label>
            <input type="text" name="libelle" class="form-control" required>
          </div>

          <div class="col-md-3">
            <div class="form-check mt-4 pt-2">
              <input class="form-check-input" type="checkbox" name="auto_code" id="auto_code" checked>
              <label class="form-check-label" for="auto_code">Générer le code automatiquement</label>
            </div>
          </div>

          <div class="col-md-3">
            <label class="form-label">Code (manuel)</label>
            <input type="text" name="code_rattachement" class="form-control" placeholder="ex: 01010101" disabled>
            <div class="form-text" id="codeHelp">Longueur attendue : —</div>
          </div>

          <div class="col-12 text-end">
            <button class="btn btn-primary" type="submit">
              <i class="bi bi-save me-1"></i> Enregistrer
            </button>
          </div>
        </form>

        {{-- Messages succès/erreur --}}
        <div id="saveMsg" class="mt-3"></div>
      </div>

      {{-- ===================== IMPORT XLS ===================== --}}
      <div class="tab-pane fade" id="tab-import" role="tabpanel" aria-labelledby="tab-import-tab">
        <div class="row g-3">
          <div class="col-md-8">
            <div class="alert alert-secondary">
              <strong>Modèle attendu</strong> : colonnes
              <code>id_pays, id_niveau, libelle, code_rattachement, code_decoupage</code><br>
              <small>NB : <em>id_pays</em> présent dans le fichier est <u>ignoré</u> et remplacé par le pays (alpha3) en session.</small>
            </div>

            <form id="formImport" enctype="multipart/form-data">@csrf
              <div class="mb-2">
                <a href="{{ route('localites.template') }}" class="btn btn-outline-secondary btn-sm">
                  <i class="bi bi-download"></i> Télécharger le modèle XLS
                </a>
              </div>

              <input type="file" name="fichier" class="form-control" accept=".xlsx, .xls" required>

              <div class="text-end mt-3">
                <button class="btn btn-success">
                  <i class="bi bi-upload"></i> Importer
                </button>
              </div>
            </form>

            <div id="importResult" class="mt-3 small text-muted">—</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- Styles rapides --}}
<style>
  #cascades .box { border:1px solid #e5e7eb; border-radius:.5rem; }
  #cascades .box .tab-content { background:#fff; }
  #parentBadge .code { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; }
</style>

{{-- Scripts --}}
<script>
/* Horloge */
setInterval(()=>document.getElementById('date-now').textContent=new Date().toLocaleString(),1000);

document.addEventListener('DOMContentLoaded', async () => {
  const cascades    = document.getElementById('cascades');
  const form        = document.getElementById('formLocalite');
  const autoCode    = document.getElementById('auto_code');
  const codeInput   = form.querySelector('input[name="code_rattachement"]');
  const codeHelp    = document.getElementById('codeHelp');
  const typeSelect  = document.getElementById('typeNouveauNiveau');
  const parentBadge = document.getElementById('parentBadge');
  const niveauCalc  = document.getElementById('niveauCalc');
  const btnClear    = document.getElementById('btn-clear-selection');
  const saveMsg     = document.getElementById('saveMsg');

  let schema = {};              // {1:[{code_decoupage,libelle},...], 2:[...]}
  let niveaux = [];             // [1,2,3,...]
  let lastSelection = { niveau: 0, code: '', libelle: '' };

  /* Helpers messages */
  function showMsg(html, kind='info'){
    saveMsg.innerHTML = `<div class="alert alert-${kind} alert-dismissible fade show" role="alert">
        ${html}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>`;
  }
  function clearMsg(){ saveMsg.innerHTML = ''; }

  /* Charge schéma */
  const metaResp = await fetch('{{ url("admin/localites/schema") }}');
  if (!metaResp.ok){
    cascades.innerHTML = '<div class="alert alert-danger">Schéma introuvable pour le pays actif.</div>';
    return;
  }
  const meta = await metaResp.json();
  schema  = meta.schema || {};
  niveaux = Object.keys(schema).map(n => parseInt(n)).sort((a,b)=>a-b);

  if (!niveaux.length) {
    cascades.innerHTML = '<div class="alert alert-warning">Aucun schéma de découpage pour le pays actif.</div>';
    return;
  }

  /* Construit les blocs (sélecteurs par niveau) */
  niveaux.forEach((niveau) => {
    const types = schema[niveau] || [];
    const col = document.createElement('div'); col.className='col-12 col-lg-4';
    const box = document.createElement('div'); box.className='box p-3 rounded h-100'; box.dataset.niveau = niveau;

    const tabId = `lvl${niveau}`;
    let tabs='', panes='';
    types.forEach((t, i) => {
      const active = (i===0) ? 'active' : '';
      tabs += `<li class="nav-item">
                 <button class="nav-link ${active}" data-bs-toggle="tab" data-bs-target="#${tabId}-${t.code_decoupage}">
                   ${t.libelle} <small class="text-muted">(${t.code_decoupage})</small>
                 </button>
               </li>`;
      panes += `<div class="tab-pane fade ${active?'show active':''}" id="${tabId}-${t.code_decoupage}">
                  <label class="mt-2">Sélection ${t.libelle}</label>
                  <select class="form-select locality-select" data-niveau="${niveau}" data-type="${t.code_decoupage}">
                    <option value="">— Choisir —</option>
                  </select>
                </div>`;
    });

    box.innerHTML = `
      <div class="d-flex justify-content-between align-items-center mb-2">
        <strong>Niveau ${niveau}</strong>
      </div>
      <ul class="nav nav-tabs">${tabs}</ul>
      <div class="tab-content p-2 border border-top-0 rounded-bottom">${panes}</div>
    `;
    col.appendChild(box);
    cascades.appendChild(col);
  });

  /* UI: remplit le sélecteur de type pour le niveau à créer */
  function fillTypeSelect(nivCible) {
    typeSelect.innerHTML = '';
    const arr = schema[nivCible] || [];
    if (!arr.length) {
      typeSelect.innerHTML = '<option value="">—</option>';
      niveauCalc.textContent = '—';
      codeHelp.textContent = 'Longueur attendue : —';
      return;
    }
    arr.forEach((t,i)=>{
      const opt = document.createElement('option');
      opt.value = t.code_decoupage;
      opt.text  = `${t.libelle} (${t.code_decoupage})`;
      if (i===0) opt.selected = true;
      typeSelect.appendChild(opt);
    });
    niveauCalc.textContent = nivCible;
    codeHelp.textContent   = `Longueur attendue : ${2 * nivCible} caractères`;
    codeInput.placeholder  = (lastSelection.code || '').concat(''.padStart(2,'?'));
  }

  /* UI: mise à jour parent + niveau cible (création) */
  function updateParentUI() {
    const has = !!lastSelection.code;
    parentBadge.innerHTML = has
      ? `<span class="badge bg-secondary">Parent</span> <span class="ms-2">${lastSelection.libelle}</span> <span class="ms-2 code">${lastSelection.code}</span>`
      : '— aucun —';

    const nivCible = has ? lastSelection.niveau + 1 : 1; // si pas de parent → on crée du niveau 1
    fillTypeSelect(nivCible);

    // champ code manuel activé/désactivé
    codeInput.disabled = autoCode.checked;
  }

  /* Charge options d’un niveau (selon type actif) */
  async function loadLevel(niveau, opts={}) {
    const block = cascades.querySelector(`[data-niveau="${niveau}"]`);
    if (!block) return;

    for (const sel of block.querySelectorAll('.locality-select')) {
      const type = sel.dataset.type;
      const url  = new URL('{{ url("admin/localites/children") }}', window.location.origin);
      url.searchParams.set('niveau', niveau);
      url.searchParams.set('code_decoupage', type);
      if (opts.parent_code) url.searchParams.set('parent_code', opts.parent_code);

      sel.innerHTML = '<option>Chargement…</option>';
      const res = await fetch(url);
      const rows = await res.json();
      sel.innerHTML = '<option value="">— Choisir —</option>';
      rows.forEach(r => sel.insertAdjacentHTML('beforeend',
        `<option value="${r.id}|${r.code_rattachement}">${r.libelle}</option>`));
    }
  }

  /* Initialisation : on peut créer du NIVEAU 1 immédiatement */
  updateParentUI();                // ← remplit le typeSelect pour niveau 1 + aides
  await loadLevel(niveaux[0]);     // charge les localités de niveau 1 (pour sélectionner un parent éventuel)

  /* Sélection d’un parent → calcule le niveau cible et charge le niveau suivant */
  cascades.addEventListener('change', async (e) => {
    if (!e.target.classList.contains('locality-select')) return;

    const niv = parseInt(e.target.dataset.niveau,10);
    const txt = e.target.options[e.target.selectedIndex]?.text || '';
    const [locId, code] = (e.target.value||'').split('|');

    // Réinitialise niveaux inférieurs
    niveaux.filter(n => n > niv).forEach(n => {
      cascades.querySelectorAll(`[data-niveau="${n}"] .locality-select`)
        .forEach(sel => sel.innerHTML = '<option value="">— Choisir —</option>');
    });

    lastSelection = { niveau: niv, code: code || '', libelle: txt || '' };
    updateParentUI();

    const next = niveaux.find(n => n === niv + 1);
    if (next && code) await loadLevel(next, { parent_code: code });
  });

  /* Effacer sélection parent */
  btnClear.addEventListener('click', async () => {
    lastSelection = { niveau: 0, code: '', libelle: '' };
    updateParentUI();
    cascades.querySelectorAll('.locality-select').forEach(sel => sel.selectedIndex = 0);
    await loadLevel(niveaux[0]);
    clearMsg();
  });

  /* Auto-code ↔ manuel */
  autoCode.addEventListener('change', () => {
    codeInput.disabled = autoCode.checked;
  });

  /* Soumission du formulaire */
  form.addEventListener('submit', async (e) => {
    e.preventDefault(); clearMsg();

    // Calcule le niveau à créer (1 si pas de parent)
    const idNiv  = lastSelection.code ? (lastSelection.niveau + 1) : 1;
    const typesN = schema[idNiv] || [];
    const chosenType = typeSelect.value || (typesN[0] ? typesN[0].code_decoupage : '');

    // Remplit les hidden
    form.querySelector('input[name="id_niveau"]').value      = idNiv;
    form.querySelector('input[name="code_decoupage"]').value = chosenType;
    form.querySelector('input[name="parent_code"]').value    = lastSelection.code || '';

    // Validation front si code manuel
    const expectedLen = 2 * idNiv;
    if (!autoCode.checked) {
      const val = (codeInput.value || '').trim();
      const digits = val.replace(/\D+/g,'');
      if (digits.length !== expectedLen) {
        showMsg(`Le code saisi doit contenir exactement <strong>${expectedLen}</strong> chiffres.`, 'danger');
        return;
      }
      // si parent, vérifie le préfixe
      if (lastSelection.code && !digits.startsWith(lastSelection.code)) {
        showMsg(`Le code doit commencer par le préfixe parent <strong>${lastSelection.code}</strong>.`, 'danger');
        return;
      }
    }

    // Bouton → état "en cours"
    const btn = form.querySelector('button[type="submit"]');
    const oldHtml = btn.innerHTML;
    btn.disabled = true; btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Enregistrement…`;

    try {
      const fd = new FormData(form);
      const res = await fetch('{{ route("localites.store") }}', {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
        body: fd
      });
      const out = await res.json();

      if (!res.ok || !out.success) {
        showMsg(out.message || 'Erreur lors de l’enregistrement.', 'danger');
      } else {
        showMsg(`Localité enregistrée avec le code <strong>${out.code || '(non retourné)'} </strong>.`, 'success');

        // refresh la liste du niveau créé
        if (lastSelection.code) {
          await loadLevel(idNiv, { parent_code: lastSelection.code });
        } else {
          await loadLevel(1);
        }

        form.reset();
        updateParentUI(); // garde la cible (niveau 1 si pas de parent)
      }
    } catch (err) {
      showMsg('Erreur réseau / serveur.', 'danger');
    } finally {
      btn.disabled = false; btn.innerHTML = oldHtml;
    }
  });

  /* Import XLS */
  document.getElementById('formImport').addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    const res = await fetch('{{ route("localites.import") }}', {
      method: 'POST',
      headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
      body: fd
    });
    const out = await res.json();
    const target = document.getElementById('importResult');
    if (!res.ok || !out.success) {
      target.innerHTML = `<div class="alert alert-danger">Import échoué : ${out.message || 'Erreur'}</div>`;
      return;
    }
    target.innerHTML = `<div class="alert alert-info">Insérés: ${out.insertes} — Échoués: ${out.echoues}<br>${(out.erreurs||[]).join('<br>')}</div>`;
  });
});
</script>
@endsection
