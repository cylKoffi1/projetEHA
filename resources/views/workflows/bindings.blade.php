{{-- resources/views/workflows/bindings.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container py-3">
  <div class="d-flex align-items-center mb-3">
    <h3 class="me-auto">Liaisons du workflow #{{ $workflowId }}</h3>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-3">
          <label class="form-label">Version publiée <span class="text-danger">*</span></label>
          <select id="b-num" class="form-select">
            <option value="">— Sélectionner —</option>
            @forelse($publishedVersions as $v)
              <option value="{{ $v->numero_version }}">v{{ $v->numero_version }}</option>
            @empty
              <option value="">(Aucune version publiée)</option>
            @endforelse
          </select>
        </div>

        <div class="col-md-9">
          <label class="form-label">Liaisons existantes</label>
          <select id="b-existing" class="form-select">
            <option value="">— Sélectionner une liaison —</option>
            @foreach($bindings as $b)
              @php
                $ver = optional($b->version)->numero_version;
                $label = ($ver ? "v$ver" : "ver#{$b->version_workflow_id}")
                       ." • {$b->module_code} • {$b->type_cible}"
                       .($b->id_cible ? " • ID:{$b->id_cible}" : " • (par type)")
                       .($b->par_defaut ? " • défaut" : "");
              @endphp
              <option
                value="{{ $b->id }}"
                data-num="{{ $ver }}"
                data-module="{{ $b->module_code }}"
                data-type="{{ $b->type_cible }}"
                data-idc="{{ $b->id_cible }}"
                data-default="{{ (int)$b->par_defaut }}"
                data-module-type-id="{{ $b->module_type_id ?? '' }}"
                data-code-pays="{{ $b->code_pays ?? '' }}"
                data-groupe-projet="{{ $b->groupe_projet_id ?? '' }}"
              >{{ $label }}</option>
            @endforeach
          </select>
          <div class="form-text">Choisir une liaison remplit le formulaire ci-dessous.</div>
        </div>
      </div>

      <div class="row g-3 mt-2">
        <div class="col-md-5">
          <label class="form-label">Module / Type (sélection à partir du registre)</label>
          <select id="b-module-type" class="form-select">
            <option value="">Chargement des modules…</option>
          </select>
          <div class="form-text">Module + type proviennent du registre <code>modules_workflow_disponibles</code>.</div>
        </div>

        <div class="col-md-3">
          <label class="form-label">Champ identifiant (utilisé pour l'autocomplete)</label>
          <select id="b-champ-identifiant" class="form-select">
            <option value="">— Choisir —</option>
          </select>
          <div class="form-text small text-muted">Proposé depuis $fillable si disponible, sinon colonnes DB.</div>
        </div>

        <div class="col-md-2">
          <label class="form-label">ID objet (optionnel)</label>
          <input id="b-id" class="form-control" placeholder="ex : PROJ2025001">
          <div class="form-text">Laisser vide pour appliquer "par type".</div>
        </div>

        <div class="col-md-1">
          {{--<label class="form-label">Pays</label>--}}
          <input type="hidden" id="b-pays" class="form-control" >
        </div>

        <div class="col-md-1">
          {{--<label class="form-label">Groupe</label>--}}
          <input type="hidden" id="b-groupe" class="form-control" >
        </div>
      </div>

      <div class="d-flex align-items-center mt-3">
        <div class="form-check">
          <input id="b-default" type="checkbox" class="form-check-input">
          <label for="b-default" class="form-check-label">Définir comme “par défaut”</label>
        </div>

        <button id="btn-bind" class="btn btn-primary ms-auto">Enregistrer la liaison</button>

        <!-- Bouton optionnel pour persister le champ_identifiant dans le registre module (facultatif) -->
        <button id="btn-save-module-config" class="btn btn-outline-secondary ms-2" title="Enregistrer le champ identifiant comme défaut pour ce module" style="display:none;">
          Enregistrer config module
        </button>

        <button id="btn-delete" class="btn btn-outline-danger ms-2" style="display:none;">Supprimer la liaison</button>
      </div>
    </div>
  </div>

  {{-- Tableau liste --}}
  <div class="card">
    <div class="card-body">
      <h5 class="mb-2">Liaisons existantes</h5>
      <div class="table-responsive">
        <table class="table table-sm align-middle" id="bindings-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Version</th>
              <th>Module</th>
              <th>Type</th>
              <th>ID cible</th>
              <th>Pays</th>
              <th>Groupe projet</th>
              <th>Par défaut</th>
              <th>Créée le</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($bindings as $idx => $b)
              <tr data-binding-id="{{ $b->id }}">
                <td>{{ $idx+1 }}</td>
                <td>{{ optional($b->version)->numero_version ? 'v'.optional($b->version)->numero_version : '#'.$b->version_workflow_id }}</td>
                <td>{{ $b->module_code }}</td>
                <td>{{ $b->type_cible }}</td>
                <td>{{ $b->id_cible }}</td>
                <td>{{ $b->code_pays }}</td>
                <td>{{ $b->groupe_projet_id }}</td>
                <td>{{ $b->par_defaut ? '✅' : '' }}</td>
                <td>{{ $b->created_at }}</td>
                <td>
                  <button class="btn btn-sm btn-link edit-binding">Modifier</button>
                  <button class="btn btn-sm btn-link text-danger delete-binding">Supprimer</button>
                </td>
              </tr>
            @empty
              <tr><td colspan="10" class="text-muted text-center">Aucune liaison</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="small text-muted">Priorité : liaison par ID &gt; liaison par type (par défaut).</div>
    </div>
  </div>
</div>

{{-- modal feedback --}}
<div class="modal fade" id="feedbackModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title" id="feedbackTitle">Information</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
    </div>
    <div class="modal-body" id="feedbackBody">...</div>
    <div class="modal-footer">
      <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
    </div>
  </div></div>
</div>

<script>
(function(){
  const CSRF = '{{ csrf_token() }}';
  const bindDynamicUrl = @json(route('workflows.bindDynamic', ['id' => $workflowId]));
  const modulesUrl = @json(route('workflows.modules'));
  const moduleCandidatesUrl = @json(route('workflows.modelCandidates'));
  const modelFieldsUrl = @json(route('workflows.modelFields')) + '?class=';
  const moduleInstancesBase = @json(route('workflows.module.instances', ['id' => '__ID__'])).replace('__ID__',''); // on suffixe /{id}/instances
  const deleteBindingUrl = (bindingId) => @json(route('workflows.bindings.destroy', ['workflow' => $workflowId, 'binding' => '__B__'])).replace('__B__', String(bindingId));
  const saveModuleConfigUrl = @json(route('workflows.modules.save'));
  
  const $existing     = document.getElementById('b-existing');
  const $vers         = document.getElementById('b-num');
  const $moduleType   = document.getElementById('b-module-type');
  const $champIdent   = document.getElementById('b-champ-identifiant');
  const $idc          = document.getElementById('b-id');
  const $pays         = document.getElementById('b-pays');
  const $groupe       = document.getElementById('b-groupe');
  const $def          = document.getElementById('b-default');
  const $btnBind      = document.getElementById('btn-bind');
  const $btnDelete    = document.getElementById('btn-delete');
  const $btnSaveMod   = document.getElementById('btn-save-module-config');
  const $table        = document.getElementById('bindings-table').querySelector('tbody');

  let modules = [];
  let selectedBindingId = null;
  let currentModuleRecord = null; // objet module sélectionné (avec classe_modele)

  async function loadModules() {
    try {
      const res = await fetch(`${modulesUrl}?only_with_model=0`);
      modules = await res.json();
      $moduleType.innerHTML = '<option value="">— Sélectionnez module / type —</option>';
      modules.forEach(m => {
        const label = `${m.libelle_module || ''}`;
        const opt = document.createElement('option');
        opt.value = m.id;
        opt.text = label;
        opt.dataset.code = m.code_module;
        opt.dataset.type = m.type_cible;
        opt.dataset.model = m.classe_modele || '';
        opt.dataset.champ = m.champ_identifiant || '';
        $moduleType.appendChild(opt);
      });
    } catch (e) {
      console.error('Erreur modules', e);
      alert('Impossible de charger la liste des modules (vérifie /modules-workflow)', 'error');
    }
  }

  // quand on change de module-type : charger modelFields si model présent
  $moduleType?.addEventListener('change', async function(){
    const id = this.value;
    currentModuleRecord = null;
    // trouver le record modules[] correspondant
    const rec = modules.find(m => String(m.id) === String(id));
    if (!rec) {
      $champIdent.innerHTML = '<option value="">— Choisir —</option>';
      $btnSaveMod.style.display = 'none';
      return;
    }
    currentModuleRecord = rec;
    const modelClass = rec.classe_modele || this.selectedOptions[0].dataset.model;
    // si la config du module contient déjà un champ_identifiant, pré-remplir
    if (rec.champ_identifiant) {
      $champIdent.innerHTML = `<option value="${rec.champ_identifiant}">${rec.champ_identifiant} (depuis module)</option>`;
      $btnSaveMod.style.display = 'inline-block';
    } else {
      $champIdent.innerHTML = '<option>Chargement…</option>';
      $btnSaveMod.style.display = 'inline-block';
    }

    if (modelClass) {
      try {
        const res = await fetch(modelFieldsUrl + encodeURIComponent(modelClass));
        if (!res.ok) throw new Error('Model fields fetch failed');
        const payload = await res.json();

        // 1) remplir champ_identifiant : priorité fillable, sinon columns
        const fillable = payload.fillable && payload.fillable.length ? payload.fillable : [];
        const columns  = (payload.columns || []).map(c => c.column);
        const picks = fillable.length ? fillable.concat(columns.filter(c=>!fillable.includes(c))) : columns;

        $champIdent.innerHTML = '<option value="">— Choisir —</option>';
        picks.forEach(c => {
          const opt = document.createElement('option');
          opt.value = c;
          opt.text = c + (fillable.includes(c) ? ' (fillable)' : '');
          $champIdent.appendChild(opt);
        });

        // si le module record a champ_identifiant, sélectionner
        if (rec.champ_identifiant) {
          const found = Array.from($champIdent.options).find(o => o.value === rec.champ_identifiant);
          if (found) found.selected = true;
        }

        // 2) remplir suggestions des champs pour rule-builder (select.r-field-suggest)
        document.querySelectorAll('.r-field-suggest').forEach(sel => {
          sel.innerHTML = '<option value="">— choisir —</option>';
          // proposer d'abord les picks courts (libelle, code, id)
          picks.slice(0, 12).forEach(c => {
            const op = document.createElement('option');
            op.value = c; op.text = c; sel.appendChild(op);
          });
        });

        // 3) afficher sample en console (ou ajouter UI si tu veux)
        console.log('model fields sample', payload.sample);

      } catch (e) {
        console.error('Erreur modelFields', e);
        alert('Impossible de récupérer les champs du modèle', 'error');
      }
    } else {
      // pas de classe modèle
      $champIdent.innerHTML = '<option value="">(Aucun modèle rattaché)</option>';
      document.querySelectorAll('.r-field-suggest').forEach(sel => sel.innerHTML = '<option value="">— choisir —</option>');
      $btnSaveMod.style.display = 'none';
    }
  });

  // Pré-remplissage depuis "Liaisons existantes"
  $existing?.addEventListener('change', function(){
    const opt = this.selectedOptions[0];
    if (!opt) return resetForm();
    const num = opt.dataset.num || '';
    const moduleTypeId = opt.dataset.moduleTypeId || '';
    if (moduleTypeId) {
      $moduleType.value = moduleTypeId;
      // déclencher change pour charger model-fields
      $moduleType.dispatchEvent(new Event('change'));
    } else {
      const mod = opt.dataset.module || '';
      const typ = opt.dataset.type || '';
      const found = Array.from($moduleType.options).find(o => o.dataset.code === mod && o.dataset.type === typ);
      $moduleType.value = found ? found.value : '';
      if (found) $moduleType.dispatchEvent(new Event('change'));
    }

    $vers.value = num ? String(num) : '';
    $idc.value  = opt.dataset.idc || '';
    $pays.value = opt.dataset.codePays || '';
    $groupe.value = opt.dataset.groupeProjet || '';
    $def.checked = opt.dataset.default === '1';
    selectedBindingId = opt.value;
    $btnDelete.style.display = 'inline-block';
  });

  // tableau click: edit / delete
  $table?.addEventListener('click', function(e){
    const tr = e.target.closest('tr[data-binding-id]');
    if (!tr) return;
    const id = tr.dataset.bindingId;
    if (e.target.classList.contains('edit-binding')) {
      const cells = tr.children;
      $vers.value = cells[1].innerText.trim().replace(/^v/,'') || '';
      const moduleCode = cells[2].innerText.trim();
      const typeCode   = cells[3].innerText.trim();
      const found = Array.from($moduleType.options).find(o => o.dataset.code === moduleCode && o.dataset.type === typeCode);
      if (found) {
        $moduleType.value = found.value;
        $moduleType.dispatchEvent(new Event('change'));
      }
      $idc.value = cells[4].innerText.trim();
      $pays.value = cells[5].innerText.trim();
      $groupe.value = cells[6].innerText.trim();
      $def.checked = cells[7].innerText.trim() === '✅';
      selectedBindingId = id;
      $btnDelete.style.display = 'inline-block';
      window.scrollTo({top:0, behavior:'smooth'});
    } else if (e.target.classList.contains('delete-binding')) {
      if (!confirm('Supprimer cette liaison ?')) return;
      doDeleteBinding(id, tr);
    }
  });

  async function doDeleteBinding(bindingId, trEl) {
    try {
      const res = await fetch(deleteBindingUrl(bindingId), {
        method: 'DELETE',
        headers: {'X-CSRF-TOKEN': CSRF, 'Accept':'application/json'}
      });
      if (!res.ok) {
        const payload = await res.json().catch(()=>({message:'Erreur suppression'}));
        throw new Error(payload.message || 'Erreur');
      }
      if (trEl) trEl.remove();
      alert('Liaison supprimée');
      if (String(selectedBindingId) === String(bindingId)) resetForm();
    } catch (e) {
      console.error(e);
      alert( e.message || 'Erreur lors de la suppression', 'error');
    }
  }

  function resetForm() {
    $existing.value = '';
    $vers.value = '';
    $moduleType.value = '';
    $champIdent.innerHTML = '<option value="">— Choisir —</option>';
    $idc.value = '';
    $pays.value = '';
    $groupe.value = '';
    $def.checked = false;
    selectedBindingId = null;
    $btnDelete.style.display = 'none';
    $btnSaveMod.style.display = 'none';
  }

  // autocomplete sur ID (utilise endpoint moduleInstances)
  let acTimer;
  $idc.addEventListener('input', function(ev){
    clearTimeout(acTimer);
    acTimer = setTimeout(async () => {
      const q = ev.target.value;
      const moduleId = $moduleType.value;
      const champ = $champIdent.value || '';
      if (!moduleId || q.length < 2) return;
      try {
        const res = await fetch(`${moduleInstancesBase}/${moduleId}/instances?q=${encodeURIComponent(q)}&pays_code=${encodeURIComponent($pays.value||'')}&groupe_projet_id=${encodeURIComponent($groupe.value||'')}`);
        const list = await res.json();
        // TODO: remplacer console.log par dropdown UI (TomSelect/Alpine etc.)
        console.log('suggestions', list, 'display_field:', champ);
        // si champ présent, essayer de remplacer input par label (ex: "ID — Libellé")
        // on laisse cela pour que tu puisses brancher la lib d'autocomplete de ton choix
      } catch (e) {
        console.error('autocomplete error', e);
      }
    }, 250);
  });

  // Enregistrer liaison
  $btnBind.addEventListener('click', async () => {
    const payload = {
      module_type_id: $moduleType.value || null,
      numero_version: parseInt($vers.value || 0, 10),
      id_cible: ($idc.value || '').trim() || null,
      par_defaut: $def.checked ? 1 : 0,
      code_pays: ($pays.value || '').trim() || null,
      groupe_projet_id: ($groupe.value || '').trim() || null
    };

    if (!payload.module_type_id) return alert('Choisissez un module/type.', 'warning');
    if (!payload.numero_version)    return alert('Choisissez une version publiée.', 'warning');
    if (payload.id_cible && payload.par_defaut) return alert('Impossible : "par défaut" s\'applique uniquement pour liaisons par type (ID vide).', 'warning');

    $btnBind.disabled = true;
    try {
      const res = await fetch(bindDynamicUrl, {
        method: 'POST',
        headers:{ 'Content-Type':'application/json','X-CSRF-TOKEN': CSRF, 'Accept':'application/json' },
        body: JSON.stringify(payload)
      });
      const body = await res.json().catch(()=>({}));
      if (!res.ok) throw new Error(body.message || body.error || 'Erreur lors de l\'enregistrement');
      alert((body.message || 'Liaison enregistrée') + '<br><small>Rafraîchis la page pour voir la liste à jour.</small>');
      setTimeout(()=> location.reload(), 900);
    } catch (e) {
      console.error(e);
      alert( e.message || 'error');
    } finally {
      $btnBind.disabled = false;
    }
  });

  // bouton: sauvegarder config module (champ_identifiant) -> POST /workflow/modules
  $btnSaveMod.addEventListener('click', async () => {
    if (!currentModuleRecord) return;
    if (!$champIdent.value) return alert('Choisis un champ identifiant à sauvegarder.', 'warning');
    const payload = {
      id: currentModuleRecord.id,
      code_module: currentModuleRecord.code_module,
      type_cible: currentModuleRecord.type_cible,
      classe_modele: currentModuleRecord.classe_modele || null,
      champ_identifiant: $champIdent.value
    };
    try {
      const res = await fetch(saveModuleConfigUrl, {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN': CSRF, 'Accept':'application/json'},
        body: JSON.stringify(payload)
      });
      const body = await res.json();
      if (!res.ok) throw new Error(body.message || 'Erreur');
      alert('Configuration du module sauvegardée.');
      // mettre à jour modules[] local
      const idx = modules.findIndex(m => m.id == currentModuleRecord.id);
      if (idx !== -1) modules[idx].champ_identifiant = $champIdent.value;
    } catch (e) {
      console.error(e);
      alert( e.message || 'Impossible de sauvegarder la configuration', 'error');
    }
  });

  $btnDelete.addEventListener('click', function(){
    if (!selectedBindingId) return;
    if (!confirm('Supprimer la liaison sélectionnée ?')) return;
    const tr = document.querySelector(`tr[data-binding-id="${selectedBindingId}"]`);
    doDeleteBinding(selectedBindingId, tr);
  });

  loadModules();
})();
</script>
@endsection
