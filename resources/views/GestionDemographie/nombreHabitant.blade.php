@extends('layouts.app')
<style>
    #cascades {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem; /* espace entre les blocs */
    }
    @media (max-width: 991.98px) { /* < lg */
        #cascades { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 575.98px) { /* < sm */
        #cascades { grid-template-columns: 1fr; }
    }
    #cascades > * { height: 100%; }
</style>
@section('content')
<div class="page-heading">
  <div class="page-title">
    <div class="row">
      <div class="col-sm-12">
        <li class="breadcrumb-item" style="list-style:none;text-align:right;padding:5px;">
          <span id="date-now"></span>
        </li>
      </div>
    </div>
    <div class="row">
      <div class="col-12 col-md-6 order-md-1 order-last">
        <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i> Gestion de la démographie</h3>
      </div>
      <div class="col-12 col-md-6 order-md-2 order-first">
        <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Gestion de la démographie</a></li>
            <li class="breadcrumb-item active" aria-current="page">Nombre d'habitants</li>
          </ol>
        </nav>
      </div>
    </div>
  </div>
</div>

<section class="section">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0">Ajout de nombre d'habitants</h5>
      @if ($errors->any())
        <div class="alert alert-danger mb-0">
          <ul class="mb-0">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif
    </div>

    <div class="card-body">
      <section id="multiple-column-form">
        <div class="row match-height">
          <div class="col-12">
            <div class="card">
              <div class="card-content">
                <div class="card-body">
                  <form class="form" id="demographieForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="ecran_id" value="{{ $ecran->id ?? '' }}" name="ecran_id">

                    <div class="row g-3" id="cascades"></div>

                    <div class="row mt-3">
                      <div class="col-md-3">
                        <label>Année</label>
                        <select name="annee" class="form-select">
                          @foreach($annees as $an)
                            <option value="{{ $an }}">{{ $an }}</option>
                          @endforeach
                        </select>
                      </div>
                      <div class="col-md-3">
                        <label>Population totale *</label>
                        <input type="number" min="0" name="population_totale" class="form-control" required>
                      </div>
                      <div class="col-md-3">
                        <label>Hommes</label>
                        <input type="number" min="0" name="population_homme" class="form-control">
                      </div>
                      <div class="col-md-3">
                        <label>Femmes</label>
                        <input type="number" min="0" name="population_femme" class="form-control">
                      </div>
                    </div>

                    <input type="hidden" name="localite_id" id="localite_id">

                    <div class="text-end mt-3">
                      <button class="btn btn-primary" type="submit">Enregistrer</button>
                    </div>
                  </form>

                  <hr>
                  <h5>Dernières saisies</h5>
                  <div id="dernieresSaisies" class="text-muted">—</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <div class="text-center mt-4">
        <h5 class="card-title">Statistiques par niveau (agrégées)</h5>
      </div>

        <div class="table-responsive">
        <table class="table table-striped table-bordered" cellspacing="0" style="width:100%" id="table1">
            <thead>
            <tr>
                <th>Niveau</th>
                <th>Type</th>
                <th>Libellé</th>
                <th>Nb enregistrements</th>
                <th>Cumul</th>
            </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
            <tr>
                <th colspan="3" class="text-end">Total</th>
                <th id="total_global">0</th>
                <th></th>
            </tr>
            </tfoot>
        </table>
        </div>

      <div class="table-responsive">
        <table class="table table-striped table-bordered" id="table1"><thead><tr></tr></thead><tbody></tbody></table>
      </div>
    </div>
  </div>
</section>

<style>
  #date-now{color:#34495E}
</style>
<div class="text-center mt-4">
  <h5 class="card-title">Enregistrements (détail)</h5>
</div>
<div class="table-responsive">
  <table class="table table-striped table-bordered" id="table2" style="width:100%">
    <thead>
      <tr>
        <th>Date maj</th>
        <th>Année</th>
        <th>Niveau</th>
        <th>Type</th>
        <th>Localité</th>
        <th>Code rattachement</th>
        <th>Total</th>
        <th>Hommes</th>
        <th>Femmes</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
  const res = await fetch('{{ route("habitants.entries") }}');
  const rows = await res.json();

  const tbody2 = document.querySelector('#table2 tbody');
  tbody2.innerHTML = '';
  rows.forEach(r => {
    tbody2.insertAdjacentHTML('beforeend', `
      <tr>
        <td>${r.updated_at ?? ''}</td>
        <td>${r.annee}</td>
        <td>${r.niveau}</td>
        <td>${r.type_niveau ?? r.code_decoupage}</td>
        <td>${r.localite}</td>
        <td>${r.code_rattachement}</td>
        <td>${(r.population_totale ?? 0).toLocaleString()}</td>
        <td>${(r.population_homme ?? 0).toLocaleString()}</td>
        <td>${(r.population_femme ?? 0).toLocaleString()}</td>
      </tr>
    `);
  });

  $(document).ready(function() {
    initDataTable(
      '{{ auth()->user()?->acteur?->code_acteur }} {{ auth()->user()->acteur?->libelle_long }}',
      'table2',
      "Enregistrements de population (détail)"
    );
  });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  // Récup stats
  const res = await fetch('{{ route("habitants.stats") }}');
  const json = await res.json();

  // Remplit le tbody
  const tbody = document.querySelector('#table1 tbody');
  tbody.innerHTML = '';
  json.rows.forEach(r => {
    tbody.insertAdjacentHTML('beforeend', `
      <tr>
        <td>${r.niveau}</td>
        <td>${r.code_decoupage}</td>
        <td>${r.libelle}</td>
        <td>${r.nb_enregistrements.toLocaleString()}</td>
        <td>${r.cumul.toLocaleString()}</td>
      </tr>
    `);
  });

  // Total global
  document.getElementById('total_global').textContent = (json.total_global || 0).toLocaleString();

  // Active DataTable
  $(document).ready(function() {
    initDataTable(
      '{{ auth()->user()?->acteur?->code_acteur }} {{ auth()->user()->acteur?->libelle_long }}',
      'table1',
      "Liste des nombres d'habitants (agrégats par niveau)"
    );
  });
});
</script>

<script>
/* Horloge */
setInterval(function() {
  document.getElementById('date-now').textContent = new Date().toLocaleString();
}, 1000);

function goBack(){ window.history.back(); }

/** =========== JS DYNAMIQUE POUR LES NIVEAUX =========== */
document.addEventListener('DOMContentLoaded', async () => {
  const cascades = document.getElementById('cascades');

  // Récupère le schéma
  const schemaResp = await fetch('{{ url("admin/demographie/schema") }}');
  if (!schemaResp.ok) {
    cascades.innerHTML = '<div class="alert alert-danger">Schéma introuvable pour le pays sélectionné.</div>';
    return;
  }
  const meta = await schemaResp.json();
  const niveaux = Object.keys(meta.schema || {}).map(n => parseInt(n)).sort((a,b)=>a-b);

  if (!niveaux.length) {
    cascades.innerHTML = '<div class="alert alert-warning">Aucun schéma de découpage pour ce pays.</div>';
    return;
  }

  // Construit un bloc par niveau (tabs si plusieurs types)
  for (const niveau of niveaux) {
    const types = meta.schema[niveau]; // [{code_decoupage, libelle}, ...]
    const col = document.createElement('div');
    col.className = 'col-12 col-md-4';
    
    const block = document.createElement('div');
    block.className = 'mb-3 p-3 border rounded h-100';
    block.dataset.niveau = niveau;

    const tabId = `lvl${niveau}`;
    const tabs = [];
    const panes = [];

    types.forEach((t, idx) => {
      const active = idx === 0 ? 'active' : '';
      tabs.push(`
        <li class="nav-item">
          <button class="nav-link ${active}" data-bs-toggle="tab" data-bs-target="#${tabId}-${t.code_decoupage}">
            ${t.libelle} <small class="text-muted">(${t.code_decoupage})</small>
          </button>
        </li>
      `);
      panes.push(`
        <div class="tab-pane fade ${active ? 'show active' : ''}" id="${tabId}-${t.code_decoupage}">
          <label class="mt-2">Sélection ${t.libelle}</label>
          <select class="form-select locality-select"
                  data-niveau="${niveau}"
                  data-type="${t.code_decoupage}">
            <option value="">— Choisir —</option>
          </select>
        </div>
      `);
    });

    block.innerHTML = `
      <div class="d-flex justify-content-between align-items-center mb-2">
        <small class="text-muted">Sélectionnez un ${types.map(t=>t.libelle).join(' / ')}</small>
      </div>
      <ul class="nav nav-tabs">${tabs.join('')}</ul>
      <div class="tab-content p-2 border border-top-0 rounded-bottom">
        ${panes.join('')}
      </div>
    `;
    col.appendChild(block);
    cascades.appendChild(block);
  }

  // Charge le niveau racine (niveau min)
  await loadLevel(niveaux[0]);

  // Sur changement d’un select d’un niveau → charge le suivant
    cascades.addEventListener('change', async (e) => {
        if (!e.target.classList.contains('locality-select')) return;

        const niveau = parseInt(e.target.dataset.niveau, 10);
        const value  = e.target.value; // "id|code_rattachement" ou ""
        const [locId, codeRat] = value ? value.split('|') : [null, null];

        // Reset niveaux inférieurs
        niveaux.filter(n => n > niveau).forEach(n => {
            cascades.querySelectorAll(`[data-niveau="${n}"] .locality-select`).forEach(sel => {
            sel.innerHTML = '<option value="">— Choisir —</option>';
            });
        });

        const isLast = (niveau === niveaux[niveaux.length - 1]);
        document.getElementById('localite_id').value = isLast ? (locId || '') : '';

        const next = niveaux.find(n => n === niveau + 1);
        if (next && codeRat) {
            await loadLevel(next, { parent_code: codeRat });
        }
    });


  // Charge les options d’un niveau (pour chaque type de ce niveau)
    async function loadLevel(niveau, opts = {}) {
        const block = cascades.querySelector(`[data-niveau="${niveau}"]`);
        if (!block) return;

        const selects = block.querySelectorAll('.locality-select');
        for (const sel of selects) {
            const type = sel.dataset.type;
            const url = new URL('{{ url("admin/demographie/localites") }}', window.location.origin);
            url.searchParams.set('niveau', niveau);
            url.searchParams.set('code_decoupage', type);
            if (opts.parent_code) url.searchParams.set('parent_code', opts.parent_code);

            sel.innerHTML = '<option>Chargement…</option>';

            console.log('[LOAD]', {
            niveau, type, parent_code: opts.parent_code || null, url: url.toString()
            });

            const res = await fetch(url);
            const rows = await res.json();

            console.log('[RESP]', { niveau, type, count: rows.length, sample: rows.slice(0,3) });

            sel.innerHTML = '<option value="">— Choisir —</option>';
            rows.forEach(r => {
            // IMPORTANT : value = "id|code_rattachement"
            sel.insertAdjacentHTML('beforeend',
                `<option value="${r.id}|${r.code_rattachement}">${r.libelle}</option>`);
            });
        }
    }


  // Envoi du formulaire
  document.getElementById('demographieForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);

    if (!fd.get('localite_id')) {
      alert("Merci de sélectionner une localité au dernier niveau.");
      return;
    }

    const res = await fetch('{{ route("habitants.store") }}', {
      method: 'POST',
      headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
      body: fd
    });

    const out = await res.json();
    if (!res.ok || !out.success) {
      alert(out.message || 'Erreur lors de l’enregistrement.');
      return;
    }

    alert('Enregistré !');
    e.target.reset();
    document.getElementById('localite_id').value = '';
  });
});
</script>
@endsection
