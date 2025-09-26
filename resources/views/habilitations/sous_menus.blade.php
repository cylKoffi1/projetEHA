@extends('layouts.app')

@section('content')
{{-- ====== Messages non bloquants ====== --}}
@if (session('success'))
  <div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
  </div>
@endif

@if ($errors->any())
  <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
    <ul class="mb-0">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
  </div>
@endif

<style>
  .invalid-feedback{display:block;margin-top:6px;font-size:80%;color:#dc3545}
  .form-section-title{font-weight:700;color:#334}
  .table-actions .btn{padding:.1rem .35rem}
</style>

<section id="multiple-column-form">
  <div class="page-heading">
    <div class="page-title">
      <div class="row">
        <div class="col-sm-12">
          <li class="breadcrumb-item" style="list-style:none;text-align:right;padding:5px">
            <span id="date-now" style="color:#34495E"></span>
          </li>
        </div>
      </div>

      <div class="row align-items-center">
        <div class="col-12 col-md-6 order-md-1 order-last">
          <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i> Plateforme</h3>
        </div>
        <div class="col-12 col-md-6 order-md-2 order-first">
          <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Gestion des habilitations</a></li>
              <li class="breadcrumb-item active" aria-current="page">Sous-menus</li>
            </ol>
          </nav>
        </div>
      </div>
    </div>
  </div>

  {{-- =================== FORMULAIRE (création + édition) =================== --}}
  <div class="card shadow-sm mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h6 class="mb-0 form-section-title">
        <span id="form-title">Créer un sous-menu</span>
      </h6>
      <div>
        <button type="button" id="btn-cancel-edit" class="btn btn-sm btn-outline-secondary" style="display:none">
          Annuler la modification
        </button>
      </div>
    </div>

    <div class="card-body">
      <form id="sm-form" class="row g-3" method="POST" action="{{ route('sous_menu.store') }}">
        @csrf
        <input type="hidden" name="ecran_id" value="{{ $ecran->id }}">
        <input type="hidden" id="edit_code" name="" value=""> {{-- sera renommé en mode édition --}}

        <div class="col-md-4">
          <label for="code_rubrique" class="form-label">Rubrique</label>
          <select class="form-select" id="code_rubrique" name="code_rubrique" required>
            <option value="">Sélectionner…</option>
            @foreach($rubriques as $rubrique)
              <option value="{{ $rubrique->code }}">{{ $rubrique->libelle }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-2">
          <label for="niveau" class="form-label">Niveau</label>
          <input type="number" class="form-control" id="niveau" name="niveau" min="1" max="3" required>
        </div>

        <div class="col-md-6">
          <label for="sous_menu_parent" class="form-label">Sous-menu parent (optionnel)</label>
          <select class="form-select" id="sous_menu_parent" name="sous_menu_parent">
            <option value="">— Aucun —</option>
          </select>
          <div class="form-text">Le parent doit appartenir à la même rubrique et à un niveau inférieur.</div>
        </div>

        <div class="col-md-6">
          <label for="libelle" class="form-label">Libellé</label>
          <input type="text" class="form-control" id="libelle" name="libelle" required>
        </div>

        <div class="col-md-2">
          <label for="ordre" class="form-label">Ordre</label>
          <input type="number" class="form-control" id="ordre" name="ordre"
                 value="{{ optional($smPlusGrandOrdre)->ordre ? $smPlusGrandOrdre->ordre + 1 : 1 }}"
                 min="1" required>
        </div>

        <div class="col-md-4 d-flex align-items-end justify-content-end">
          @can("ajouter_ecran_" . $ecran->id)
          <button type="submit" id="submit-btn" class="btn btn-primary">
            Enregistrer
          </button>
          @endcan
        </div>
      </form>
    </div>
  </div>

  {{-- =================== TABLEAU =================== --}}
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0">Liste des sous-menus</h5>
      <div id="flash-area" style="min-width:280px"></div>
    </div>

    <div class="card-content">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped table-bordered align-middle" id="table1" style="width:100%">
            <thead>
              <tr>
                <th>Code</th>
                <th>Rubrique</th>
                <th>Parent</th>
                <th>Sous-menu</th>
                <th>Ordre</th>
                <th>Niveau</th>
                <th style="width:110px">Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($sous_menus as $sm)
                <tr data-code="{{ $sm->code }}">
                  <td># {{ $sm->code }}</td>
                  <td>{{ $sm->rubrique->libelle ?? '' }}</td>
                  <td>{{ $sm->sm_parent->libelle ?? '' }}</td>
                  <td>{{ $sm->libelle }}</td>
                  <td>{{ $sm->ordre }}</td>
                  <td>{{ $sm->niveau }}</td>
                  <td class="table-actions">
                    @can("modifier_ecran_" . $ecran->id)
                      <button type="button" class="btn btn-sm btn-outline-primary me-1 btn-edit">Modifier</button>
                    @endcan
                    @can("supprimer_ecran_" . $ecran->id)
                      <button type="button" class="btn btn-sm btn-outline-danger btn-delete">Suppr.</button>
                    @endcan
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div> {{-- /.table-responsive --}}
      </div>
    </div>
  </div>
</section>

<script>
  /* Horloge */
  setInterval(() => {
    const el = document.getElementById('date-now');
    if (el) el.textContent = new Date().toLocaleString();
  }, 1000);

  // =================== Références formulaire ===================
  const form           = document.getElementById('sm-form');
  const formTitle      = document.getElementById('form-title');
  const btnCancelEdit  = document.getElementById('btn-cancel-edit');
  const submitBtn      = document.getElementById('submit-btn');
  const inputEditCode  = document.getElementById('edit_code');

  const inputRubrique  = document.getElementById('code_rubrique');
  const inputNiveau    = document.getElementById('niveau');
  const inputParent    = document.getElementById('sous_menu_parent');
  const inputLibelle   = document.getElementById('libelle');
  const inputOrdre     = document.getElementById('ordre');

  const STORE_URL  = @json(route('sous_menu.store'));
  const UPDATE_URL = @json(route('sous_menu.update'));

  let mode = 'create'; // 'create' | 'edit'

  function setMode(newMode){
    mode = newMode;
    if (mode === 'create'){
      form.action = STORE_URL;
      formTitle.textContent = 'Créer un sous-menu';
      submitBtn.textContent = 'Enregistrer';
      btnCancelEdit.style.display = 'none';

      inputLibelle.name  = 'libelle';
      inputOrdre.name    = 'ordre';
      inputNiveau.name   = 'niveau';
      inputRubrique.name = 'code_rubrique';
      inputParent.name   = 'sous_menu_parent';
      inputEditCode.name = '';

      // Valeur par défaut de l'ordre si vide
      if (!inputOrdre.value) {
        inputOrdre.value = {{ optional($smPlusGrandOrdre)->ordre ? $smPlusGrandOrdre->ordre + 1 : 1 }};
      }
      // Réinitialise les options "Parent"
      inputParent.innerHTML = `<option value="">— Aucun —</option>`;

    } else {
      form.action = UPDATE_URL;
      formTitle.textContent = 'Modifier un sous-menu';
      submitBtn.textContent = 'Mettre à jour';
      btnCancelEdit.style.display = '';

      inputLibelle.name  = 'edit_libelle';
      inputOrdre.name    = 'edit_ordre';
      inputNiveau.name   = 'edit_niveau';
      inputRubrique.name = 'edit_code_rubrique';
      inputParent.name   = 'edit_sous_menu_parent';
      inputEditCode.name = 'edit_code';
    }
  }

  function resetForm(){
    form.reset();
    setMode('create');
  }
  btnCancelEdit.addEventListener('click', e => { e.preventDefault(); resetForm(); });

  // =================== API helpers ===================
  async function fetchSousMenu(code){
    const r = await fetch(`/admin/sous_menu/get-sous_menu/${code}`);
    if (!r.ok) throw new Error('Sous-menu introuvable');
    return await r.json();
  }

  async function fetchParents(rubriqueCode, niveau, excludeCode = null){
    const qs = new URLSearchParams({ rubrique: rubriqueCode, niveau: String(niveau) });
    if (excludeCode) qs.append('exclude', excludeCode);
    const r = await fetch(`/admin/sous_menu/parents?${qs.toString()}`);
    if (!r.ok) return [];
    return await r.json();
  }

  async function populateParents(rubriqueCode, niveau, excludeCode, selectedValue){
    const parents = await fetchParents(rubriqueCode, niveau, excludeCode);
    inputParent.innerHTML = `<option value="">— Aucun —</option>` +
      parents.map(p => `<option value="${p.code}">${p.libelle} (N${p.niveau})</option>`).join('');
    if (selectedValue) inputParent.value = selectedValue;
  }

  // Quand l'utilisateur change manuellement rubrique/niveau en mode création
  inputRubrique.addEventListener('change', () => {
    if (mode === 'create') populateParents(inputRubrique.value, parseInt(inputNiveau.value || '0', 10), null, '');
  });
  inputNiveau.addEventListener('input', () => {
    if (mode === 'create') populateParents(inputRubrique.value, parseInt(inputNiveau.value || '0', 10), null, '');
  });

  // =================== Table: edit & delete ===================
  document.addEventListener('DOMContentLoaded', () => {
    if (typeof initDataTable === 'function') {
      initDataTable('{{ auth()->user()?->acteur?->libelle_court }} {{ auth()->user()?->acteur?->libelle_long }}', 'table1', 'Liste des sous-menus');
    }

    const $table = $('#table1');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // EDIT
    $table.on('click', '.btn-edit', async function(){
      const tr = $(this).closest('tr').get(0);
      const code = tr.dataset.code;
      try{
        const sm = await fetchSousMenu(code);
        setMode('edit');
        inputEditCode.value = sm.code;

        inputLibelle.value  = sm.libelle ?? '';
        inputOrdre.value    = sm.ordre ?? '';
        inputNiveau.value   = sm.niveau ?? '';
        inputRubrique.value = sm.code_rubrique ?? '';

        await populateParents(sm.code_rubrique, sm.niveau, sm.code, sm.sous_menu_parent ?? '');

        // scroll vers le formulaire
        window.scrollTo({ top: document.querySelector('#sm-form').getBoundingClientRect().top + window.scrollY - 80, behavior: 'smooth' });
      }catch(e){
        console.error(e);
      }
    });

    // DELETE
    $table.on('click', '.btn-delete', function(){
      const tr = $(this).closest('tr');
      const code = tr.attr('data-code');
      if (!confirm('Supprimer ce sous-menu ?')) return;

      fetch(`/admin/sous_menu/delete/${code}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json' }
      })
      .then(r => r.ok ? r.json() : r.json().then(err => Promise.reject(err)))
      .then((res) => {
        tr.fadeOut(150, () => tr.remove());
        const area = document.getElementById('flash-area');
        if (area) {
          area.innerHTML = `
            <div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
              ${res?.success ?? "Sous-menu supprimé avec succès."}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
          `;
        }
      })
      .catch(err => {
        const area = document.getElementById('flash-area');
        if (area) {
          area.innerHTML = `
            <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
              ${err?.error ?? "Erreur lors de la suppression du sous-menu."}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
          `;
        }
      });
    });
  });

  // Init
  setMode('create');
</script>
@endsection
