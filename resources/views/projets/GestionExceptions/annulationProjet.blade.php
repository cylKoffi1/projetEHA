{{-- resources/views/projets/GestionExceptions/annulationProjet.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="page-heading">
  <div class="page-title">
    <div class="row">
      <div class="col-sm-12">
        <li class="breadcrumb-item" style="list-style:none;text-align:right;padding:5px;">
          <span id="date-now" style="color:#34495E;margin-left:15px;"></span>
        </li>
      </div>
    </div>
    <div class="row">
      <div class="col-12 col-md-6 order-md-1 order-last">
        <h3>
          <i class="bi bi-arrow-return-left return" onclick="goBack()"></i>
          Annulation de projet
        </h3>
      </div>
      <div class="col-12 col-md-6 order-md-2 order-first">
        <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Gestion des exceptions</a></li>
            <li class="breadcrumb-item active" aria-current="page">Annuler projet</li>
          </ol>
        </nav>
        <script>
          setInterval(()=>{ const el=document.getElementById('date-now'); if(el) el.textContent=new Date().toLocaleString(); },1000);
        </script>
      </div>
    </div>
  </div>
</div>

<div class="container">
  @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
  @if(session('error'))   <div class="alert alert-danger">{{ session('error') }}</div> @endif

  <div class="row match-height">
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex align-items-end justify-content-between">
          <div>
            <h4 class="card-title mb-0">Annuler projet</h4>
            <small class="text-muted">Choisissez d’abord le type pour charger la bonne liste.</small>
          </div>
        </div>

        <div class="card-body">
          {{-- Ligne "Type de projet" --}}
          <div class="row align-items-end mb-3">
            <div class="col-12 col-md-3">
              <label class="form-label">Type de projet</label>
              <select id="annul_type_projet" class="form-control">
                @can('projettype.select', 'INF')
                  <option value="PROJET">Projet d'infrastructure</option>
                @endcan
                @can('projettype.select', 'APP')
                  <option value="APPUI">Projet d'appui</option>
                @endcan
                @can('projettype.select', 'ETU')
                  <option value="ETUDE">Projet d'étude</option>
                @endcan
              </select>
            </div>
          </div>

          <form id="annulationForm" method="POST" action="{{ route('projets.annulation.store') }}">
            @csrf
            {{-- Conserve ecran_id sur post et redirection serveur --}}
            <input type="hidden" name="ecran_id" value="{{ request('ecran_id', $ecran->id ?? '') }}">

            <div class="row">
              <div class="col-md-3">
                <label for="code_projet_annuler">Projet à annuler *</label>
                <select name="code_projet" id="code_projet_annuler" class="form-control" required>
                  <option value="">-- Sélectionnez --</option>
                  {{-- options chargées par AJAX selon le type --}}
                </select>
              </div>

              {{-- Carte info projet --}}
              <div id="infoCard" class="col-md-9" style="display:none;">
                <div class="card shadow-sm border-primary mb-3">
                  <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center" style="min-height:57px;">
                    <div><small class="d-block">Nature : <strong><span id="nature"></span></strong></small></div>
                    <div><small class="d-block"><strong><span id="libelle_projet"></span></strong></small></div>
                    <div>
                      <small class="d-block"><strong>Domaine</strong> : <strong><span id="domaine"></span></strong></small>
                      <small class="d-block"><strong>Sous domaine</strong> : <strong><span id="sousDomaine"></span></strong></small>
                    </div>
                  </div>
                  <div class="card-body">
                    <div class="row g-4">
                      <div class="col-md-8">
                        <div class="d-flex align-items-start mb-3">
                          <i class="bi bi-calendar-check me-3 fs-4 text-primary"></i>
                          <div>
                            <h6 class="mb-1 fw-bold text-muted">Période</h6>
                            <p class="mb-0">
                              Du <span id="date_demarrage_prevue"></span>
                              au <span id="date_fin_prevue"></span>
                            </p>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="d-flex align-items-start mb-3">
                          <i class="bi bi-cash-coin me-3 fs-4 text-primary"></i>
                          <div>
                            <h6 class="mb-1 fw-bold text-muted">Budget</h6>
                            <p class="mb-0"><span id="cout"></span> <span id="devise"></span></p>
                          </div>
                        </div>
                      </div>
                    </div><!--/row-->
                  </div>
                </div>
              </div>
            </div>

            <div class="row mt-2">
              <div class="col-9">
                <label for="motif">Motif de l’annulation *</label>
                <textarea name="motif" class="form-control" rows="2" required placeholder="Expliquez la raison de l’annulation..."></textarea>
              </div>
              <div class="col text-end">
                @can("supprimer_ecran_" . ($ecran->id ?? 0))
                  <button type="submit" class="btn btn-danger mt-4">Annuler le projet</button>
                @endcan
              </div>
            </div>
          </form>
        </div><!--/card-body-->
      </div>
    </div>
  </div>

  <h5 class="mt-4">📋 Projets annulés</h5>
  <div class="card">
    <div class="card-body">
      <table class="table table-striped table-bordered" cellspacing="0" style="width:100%" id="tableAnnules">
        <thead>
          <tr>
            <th>Code</th>
            <th>Libellé</th>
            <th>Date annulation</th>
            <th>Statut</th>
          </tr>
        </thead>
        <tbody>
          @foreach($projetsAnnules as $p)
            <tr>
              <td>{{ $p?->code_projet }}</td>
              <td>{{ $p?->libelle_projet }}</td>
              <td>{{ $p?->date_statut ?? '-' }}</td>
              <td>{{ $p?->statut_libelle ?? '-' }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- JS --}}
<script>
  function goBack(){ window.history.back(); }

  // Détection TYPE depuis le code (comme la réattribution)
  const PREFIX_TO_TYPE = [
    { prefix: 'ET_',    type: 'ETUDE' },
    { prefix: 'APPUI_', type: 'APPUI' },
  ];
  
  function detectTypeFromCode(code) {
    if (!code) return 'PROJET';
    const hit = PREFIX_TO_TYPE.find(p => code.startsWith(p.prefix));
    return hit ? hit.type : 'PROJET';
  }

  const typeSelect   = document.getElementById('annul_type_projet');
  const projetSelect = document.getElementById('code_projet_annuler');

  async function reloadProjetOptions(type, preselect=null){
    projetSelect.innerHTML = '<option value="">Chargement...</option>';
    try{
      const url = @json(route('annulation.optionsProjets')) + '?type=' + encodeURIComponent(type || 'PROJET');
      const res = await fetch(url, { headers: { 'X-Requested-With':'XMLHttpRequest' }});
      const data = await res.json();

      if (preselect && !(data||[]).some(d => d.code === preselect)) {
        const opt = document.createElement('option');
        opt.value = preselect; opt.textContent = preselect;
        projetSelect.appendChild(opt);
      }

      projetSelect.innerHTML = '<option value="">-- Sélectionnez --</option>';
      (data||[]).forEach(row => {
        const opt = document.createElement('option');
        opt.value = row.code;
        opt.textContent = row.code + (row.label ? ' — ' + row.label : '');
        projetSelect.appendChild(opt);
      });

      if (preselect && (data||[]).some(d => d.code === preselect)) {
        projetSelect.value = preselect;
      }
    }catch(e){
      projetSelect.innerHTML = '<option value="">Erreur de chargement</option>';
    }
  }

  function hideCard(){ document.getElementById('infoCard').style.display='none'; }
  function fillCard(d){
    const safe = v => (v ?? '').toString().trim() || '-';
    document.getElementById('libelle_projet').textContent          = safe(d.libelle_projet);
    document.getElementById('nature').textContent                  = safe(d.nature);
    document.getElementById('domaine').textContent                 = safe(d.domaine);
    document.getElementById('sousDomaine').textContent             = safe(d.sousDomaine);
    document.getElementById('date_demarrage_prevue').textContent   = safe(d.date_demarrage_prevue);
    document.getElementById('date_fin_prevue').textContent         = safe(d.date_fin_prevue);
    document.getElementById('devise').textContent                  = safe(d.devise);
    document.getElementById('cout').textContent                    = d.cout ? new Intl.NumberFormat('fr-FR').format(d.cout) : '-';
    document.getElementById('infoCard').style.display              = '';
  }
  function loadCard(code){
    if(!code){ hideCard(); return; }
    fetch(@json(route('reattribution.projetCard',['code_projet'=>'__X__'])).replace('__X__', encodeURIComponent(code)))
      .then(r=>r.json()).then(d=>{ if(d) fillCard(d); else hideCard(); }).catch(()=> hideCard());
  }

  // Init + events
  document.addEventListener('DOMContentLoaded', ()=> reloadProjetOptions(typeSelect.value));
  typeSelect.addEventListener('change', ()=>{ reloadProjetOptions(typeSelect.value); hideCard(); });

  projetSelect.addEventListener('change', function(){
    const code = this.value;
    if(!code){ hideCard(); return; }
    const autoType = detectTypeFromCode(code);
    if (autoType !== typeSelect.value) {
      typeSelect.value = autoType;
      reloadProjetOptions(autoType, code);
    }
    loadCard(code);
  });

  // Confirmation avant soumission (form natif)
  document.getElementById('annulationForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const code  = projetSelect.value || '—';
    const motif = (document.querySelector('textarea[name="motif"]').value || '').trim();
    Swal.fire({
      title: 'Confirmer l’annulation',
      html: `Le projet <b>${code}</b> sera marqué <b>Annulé</b>.<br>Motif : <i>${motif || '(non renseigné)'}</i>`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Oui, annuler',
      cancelButtonText: 'Annuler'
    }).then(res => {
      if (res.isConfirmed) e.target.submit();
    });
  });

  // DataTable (unique init)
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof initDataTable === 'function') {
      initDataTable('{{ auth()->user()->acteur?->libelle_court }} {{ auth()->user()->acteur?->libelle_long }}', 'tableAnnules', "Liste des projets annulés");
    }
  });
</script>
@endsection
