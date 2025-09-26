@extends('layouts.app')

@section('content')
<style>
  .table td, .table th { white-space: nowrap; }
  .card-form.sticky {
    position: sticky; top: 0; z-index: 5; background: #fff; /* si header fixed, adapte top */
    box-shadow: 0 2px 10px rgba(0,0,0,.04);
  }
  .btn-spinner {
    width: 1.25rem; height: 1.25rem; border: .15rem solid currentColor; border-right-color: transparent;
    border-radius: 50%; display: inline-block; vertical-align: -2px; animation: spin .7s linear infinite;
  }
   @keyframes spin { to { transform: rotate(360deg); } }
</style>

<div class="container-fluid">
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-sm-12">
                    <li class="breadcrumb-item" style="list-style:none; text-align:right; padding:5px;">
                        <span id="date-now" style="color:#34495E;"></span>
                    </li>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Gestion financière</h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="">Banques</a></li>
                        </ol>
                    </nav>
                    <script>
                        setInterval(() => {
                            document.getElementById('date-now').textContent = new Date().toLocaleString();
                        }, 1000);
                    </script>
                </div>
            </div>
        </div>
    </div>


  <!-- ===== Formulaire (création + modification) ===== -->
  <div class="card card-form mb-4">
    <div class="card-body">
      <div class="d-flex align-items-center justify-content-between">
        <h5 id="form-title" class="mb-3">Nouvelle banque</h5>
        <div class="d-flex gap-2">
          <button type="button" id="btn-cancel-edit" class="btn btn-sm btn-outline-secondary d-none">Annuler la modification</button>
        </div>
      </div>

      <form id="banque-form" autocomplete="off">
        @csrf
        <input type="hidden" name="id" id="banque-id">
        <input type="hidden" name="ecran_id" value="{{ $ecran->id }}">

        <div class="row g-3">
          
          <div class="col-md-3">
            <label class="form-label" for="code_swift">Code SWIFT</label>
            <input type="text" name="code_swift" id="code_swift" class="form-control" maxlength="11" placeholder="8 ou 11 caractères">
          </div>

          <div class="col-md-6">
            <label class="form-label" for="nom">Nom *</label>
            <input type="text" name="nom" id="nom" class="form-control" required>
          </div>

          <div class="col-md-3">
            <label class="form-label" for="sigle">Sigle</label>
            <input type="text" name="sigle" id="sigle" class="form-control">
          </div>


          <div class="col-md-3">
            <label class="form-label" for="telephone">Téléphone</label>
            <input type="text" name="telephone" id="telephone" class="form-control" placeholder="{{ $pays->codeTel }}">
          </div>

          <div class="col-md-3">
            <label class="form-label" for="email">Email</label>
            <input type="email" name="email" id="email" class="form-control">
          </div>

          <div class="col-md-3">
            <label class="form-label" for="site_web">Site web</label>
            <input type="url" name="site_web" id="site_web" class="form-control" placeholder="https://…">
          </div>

          <div class="col-md-3">
            <label class="form-label d-block">&nbsp;</label>
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" role="switch" id="est_internationale" name="est_internationale" value="1">
              <label class="form-check-label" for="est_internationale">Banque internationale</label>
            </div>
          </div>

          {{--<div class="col-md-3" id="grp-code-pays">
            <label class="form-label" for="code_pays">Pays</label>
            <select name="code_pays" id="code_pays" class="form-control">
            <option value="{{ $pays->alpha3 }}">{{ $pays->nom_fr_fr }}</option>
            
                @foreach ($pays as $pay)
                    <option value="{{ $pay->alpha3 }}">{{ $pay->nom_fr_fr }}</option>
                @endforeach
            </select>
           
          </div>--}}

          <div class="col-md-6">
            <label class="form-label" for="adresse">Adresse</label>
            <textarea name="adresse" id="adresse" class="form-control" rows="1"></textarea>
          </div>

          <div class="col-md-2 d-flex align-items-center">
            <div class="form-check mt-3">
              <input class="form-check-input" type="checkbox" id="actif" name="actif" value="1" checked>
              <label class="form-check-label" for="actif">Active</label>
            </div>
          </div>

            <div class="col-6 d-flex  gap-2 mt-2">
              
                <button type="button" class="btn btn-light" id="btn-reset">Réinitialiser</button>
            </div>
            <div class="col-6 d-flex justify-content-end gap-2 mt-2">
                <button type="submit" class="btn btn-primary" id="btn-save">
                    <span class="btn-label">Enregistrer</span>
                </button>
               
            </div>
        </div>
      </form>
    </div>
  </div>

  <!-- ===== Tableau ===== -->
  <div class="card">
    <div class="card-body">
      <div class="table-responsive">    
        <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%"  id="tbl-banques">
          <thead>
            <tr>
              <th>SWIFT</th>
              <th>Nom</th>
              <th>Sigle</th>
              <th>Téléphone</th>
              <th>Email</th>
              <th>Actif</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody><!-- rempli via JS --></tbody>
        </table>
      </div>
    </div>
  </div>

</div>

<script>
(() => {
  const routes = {
    list:    @json(route('banques.list')),
    store:   @json(route('banques.store')),
    update:  (id) => @json(route('banques.update', ['id' => '___ID___'])).replace('___ID___', id),
    destroy: (id) => @json(route('banques.destroy', ['id' => '___ID___'])).replace('___ID___', id),
  };
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

  /* ===== Eléments ===== */
  const tblBody = document.querySelector('#tbl-banques tbody');

  const formCard = document.querySelector('.card-form');
  const form    = document.getElementById('banque-form');
  const formTitle = document.getElementById('form-title');
  const btnCancelEdit = document.getElementById('btn-cancel-edit');

  const btnNew  = document.getElementById('btn-new');
  const saveBtn = document.getElementById('btn-save');
  const resetBtn= document.getElementById('btn-reset');

  const idInput = document.getElementById('banque-id');
  const nom = document.getElementById('nom');
  const sigle = document.getElementById('sigle');
  const estInternationale = document.getElementById('est_internationale');
  const codePays = document.getElementById('code_pays');
  const grpCodePays = document.getElementById('grp-code-pays');
  const codeSwift = document.getElementById('code_swift');
  const adresse = document.getElementById('adresse');
  const telephone = document.getElementById('telephone');
  const email = document.getElementById('email');
  const siteWeb = document.getElementById('site_web');
  const actif = document.getElementById('actif');

  const canCreate = @json(auth()->user()->can("ajouter_ecran_".$ecran->id));
  const canEdit   = @json(auth()->user()->can("modifier_ecran_".$ecran->id));
  const canDelete = @json(auth()->user()->can("supprimer_ecran_".$ecran->id));

  /* ===== Helpers ===== */
  function setMode(mode) { // 'create' | 'edit'
    if (mode === 'edit') {
      formTitle.textContent = 'Modifier la banque';
      btnCancelEdit.classList.remove('d-none');
      saveBtn.querySelector('.btn-label').textContent = 'Mettre à jour';
    } else {
      formTitle.textContent = 'Nouvelle banque';
      btnCancelEdit.classList.add('d-none');
      saveBtn.querySelector('.btn-label').textContent = 'Enregistrer';
    }
  }

  function setBusy(busy) {
    if (busy) {
      saveBtn.disabled = true;
      if (!saveBtn.querySelector('.btn-spinner')) {
        const sp = document.createElement('span');
        sp.className = 'btn-spinner me-2';
        saveBtn.prepend(sp);
      }
      saveBtn.querySelector('.btn-label').textContent =
        (idInput.value ? 'Mise à jour…' : 'Enregistrement…');
    } else {
      saveBtn.disabled = false;
      const sp = saveBtn.querySelector('.btn-spinner');
      if (sp) sp.remove();
      saveBtn.querySelector('.btn-label').textContent =
        (idInput.value ? 'Mettre à jour' : 'Enregistrer');
    }
  }

  function clearForm(){
    idInput.value = '';
    form.reset();
    actif.checked = true;
    estInternationale.checked = false;
    codePays.disabled = false;
    grpCodePays.style.opacity = '1';
    setMode('create');
  }

  function fillForm(b){
    idInput.value = b.id;
    nom.value = b.nom ?? '';
    sigle.value = b.sigle ?? '';
    estInternationale.checked = !!b.est_internationale;
    codePays.value = b.code_pays ?? '';
    codeSwift.value = b.code_swift ?? '';
    adresse.value = b.adresse ?? '';
    telephone.value = b.telephone ?? '';
    email.value = b.email ?? '';
    siteWeb.value = b.site_web ?? '';
    actif.checked = !!b.actif;
    togglePaysField();
    setMode('edit');
    // focus & scroll to form
    nom.focus();
    formCard.scrollIntoView({behavior:'smooth', block:'start'});
  }

  function togglePaysField(){
    const intl = estInternationale.checked;
    codePays.disabled = intl;
    grpCodePays.style.opacity = intl ? '.5' : '1';
    if (intl) codePays.value = '';
  }

  // Uppercase ISO3
  codePays.addEventListener('input', () => {
    codePays.value = (codePays.value || '').replace(/[^a-zA-Z]/g,'').toUpperCase().slice(0,3);
  });
  estInternationale.addEventListener('change', togglePaysField);

  function escapeHtml(s){
    return String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
  }

    function ligneHTML(b){
        const paysLib = b.est_internationale ? 'Internationale' : (b.pays?.nom_fr_fr || b.code_pays || '');
        const actifBadge = b.actif ? 'Oui' : 'Non';

        let actionsHtml = `<span class="text-muted">—</span>`;
        if (canEdit || canDelete) {
            actionsHtml = `<div class="btn-group btn-group-sm">`;
            if (canEdit) {
            actionsHtml += `
                <button class="btn btn-outline-primary btn-edit" title="Éditer">
                <i class="bi bi-pencil-square"></i>
                </button>`;
            }
            if (canDelete) {
            actionsHtml += `
                <button class="btn btn-outline-danger btn-del" title="Supprimer">
                <i class="bi bi-trash"></i>
                </button>`;
            }
            actionsHtml += `</div>`;
        }

        return `
            <tr data-id="${b.id}"
                data-code-pays="${escapeAttr(b.code_pays || '')}"
                data-est-internationale="${b.est_internationale ? '1' : '0'}">
            <td>${escapeHtml(b.code_swift||'')}</td>
            <td>${escapeHtml(b.nom||'')}</td>
            <td>${escapeHtml(b.sigle||'')}</td>
            <td>${escapeHtml(b.telephone||'')}</td>
            <td>${escapeHtml(b.email||'')}</td>
            <td>${actifBadge}</td>
            <td class="text-nowrap">${actionsHtml}</td>
            </tr>
        `;
    }

    function escapeAttr(s){
        return String(s).replace(/&/g,'&amp;').replace(/"/g,'&quot;')
                        .replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

  async function charger(){
    try{
      const res = await fetch(routes.list, {headers:{'Accept':'application/json'}});
      const j = await res.json();
      if(!j.ok) throw new Error(j.message || 'Erreur de chargement');
      tblBody.innerHTML = j.data.map(ligneHTML).join('');
    }catch(e){
      alert('Erreur: '+e.message);
    }
  }

  async function enregistrer(e){
    e.preventDefault();
    const id = idInput.value.trim();
    const isEdit = !!id;

    // Droits côté client (le serveur vérifiera aussi)
    if (isEdit && !canEdit) { alert("Vous n'avez pas les droits pour modifier."); return; }
    if (!isEdit && !canCreate) { alert("Vous n'avez pas les droits pour ajouter."); return; }

    const payload = new FormData(form);
    if(!estInternationale.checked) payload.set('est_internationale', '0');
    if(!actif.checked) payload.set('actif', '0');

    const url = isEdit ? routes.update(id) : routes.store;
    const method = isEdit ? 'PUT' : 'POST';

    try{
      setBusy(true);
      const res = await fetch(url, {
        method,
        headers: {'Accept':'application/json', 'X-CSRF-TOKEN': csrf},
        body: payload
      });
      const j = await res.json();
      if(!res.ok || !j.ok) throw new Error(j.message || 'Échec de sauvegarde');

      alert(j.message || (isEdit ? 'Banque mise à jour.' : 'Banque créée.'));
      await charger();
      clearForm();
    }catch(err){
      alert('Erreur: '+err.message);
    }finally{
      setBusy(false);
    }
  }

  async function supprimer(id){
    if(!confirm('Supprimer cette banque ?')) return;
    try{
      const res = await fetch(routes.destroy(id), {
        method: 'DELETE',
        headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': csrf},
        body: new URLSearchParams({ecran_id: '{{ $ecran->id }}'})
      });
      const j = await res.json();
      if(!res.ok || !j.ok) throw new Error(j.message || 'Échec de suppression');

      alert(j.message || 'Supprimé.');
      await charger();
      // si on supprimait l'élément en cours d'édition → reset form
      if (idInput.value && idInput.value === String(id)) clearForm();
    }catch(e){
      alert('Erreur: '+e.message);
    }
  }

  /* ===== Handlers ===== */
  @can("ajouter_ecran_".$ecran->id)
  btnNew?.addEventListener('click', () => { clearForm(); nom.focus(); formCard.scrollIntoView({behavior:'smooth'}); });
  @endcan

  btnCancelEdit.addEventListener('click', clearForm);
  resetBtn.addEventListener('click', () => { if (idInput.value) fillForm({ id: idInput.value, nom: nom.value, sigle: sigle.value, est_internationale: estInternationale.checked, code_pays: codePays.value, code_swift: codeSwift.value, adresse: adresse.value, telephone: telephone.value, email: email.value, site_web: siteWeb.value, actif: actif.checked }); else clearForm(); });
  form.addEventListener('submit', enregistrer);

    tblBody.addEventListener('click', (ev) => {
        const btn = ev.target.closest('.btn-edit, .btn-del');
        if (!btn) return;

        const tr = btn.closest('tr');
        const id = tr.dataset.id;

        if (btn.classList.contains('btn-edit')) {
            const cells = tr.children;
            fillForm({
            id,
            nom: cells[0].textContent.trim(),
            sigle: cells[1].textContent.trim(),
            // 🔑 on lit les data-* (code alpha3 + bool) et non le texte affiché
            est_internationale: tr.dataset.estInternationale === '1' || tr.dataset.estInternationale === 'true',
            code_pays: tr.dataset.codePays || '',
            code_swift: cells[3].textContent.trim(),
            telephone: cells[4].textContent.trim(),
            email: cells[5].textContent.trim(),
            actif: (cells[6].textContent.trim() === 'Oui')
            });
        }

        if (btn.classList.contains('btn-del')) {
            if (!canDelete) { alert("Vous n'avez pas les droits pour supprimer."); return; }
            supprimer(id);
        }
    });


  // Initial load
  charger();
})();

$(document).ready(function() {
    if (typeof initDataTable === 'function') {
        initDataTable('{{ auth()->user()->acteur?->libelle_court }} {{ auth()->user()->acteur?->libelle_long }}', 'tbl-banques', 'Liste des achats');
    }
});
</script>
@endsection
