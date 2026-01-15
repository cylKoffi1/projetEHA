{{-- resources/views/projets/DefinitionProjet/changementChefProjet.blade.php --}}
@extends('layouts.app')

@section('content')
<div>
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
          <h3><i class="bi bi-arrow-return-left return" onclick="history.back()"></i> Changement de chef de projet</h3>
        </div>
        <div class="col-12 col-md-6 order-md-2 order-first">
          <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Définition de projet</a></li>
              <li class="breadcrumb-item active" aria-current="page">Changement de chef</li>
            </ol>
          </nav>
          <script>setInterval(()=>document.getElementById('date-now').textContent=new Date().toLocaleString(),1000);</script>
        </div>
      </div>
    </div>
  </div>

  {{-- Alertes serveur (post-redirect) --}}
  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      {{ session('error') }}
      <button class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif
  @if($errors->any())
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
      <ul class="mb-0">
        @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
      </ul>
      <button class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
    </div>
  @endif

  <!-- Zone d’alertes JS -->
  <div id="flash-zone"></div>

  <div class="container">
    <div class="row match-height">
      <div class="col-12">
        <div class="card">
          <div class="card-header"><h4 class="card-title">Changement de Chef de projet</h4></div>
          <div class="card-body">

            {{-- Le formulaire englobe tout, mais les sections sont protégées par @can --}}
            <form id="changeChefForm" action="{{ route('contrats.chef.update') }}" method="POST">
              @csrf
              <input type="hidden" name="ecran_id" value="{{ $ecran->id ?? '' }}">

              {{-- === Bloc CONSULTATION: visible si droit consulter === --}}
              @can("consulter_ecran_" . ($ecran->id ?? 0))
              <div class="row g-3">
                <div class="col-md-4">
                  <label class="form-label">Type de projet <span class="text-danger">*</span></label>
                  <select id="type_projet" name="type_projet" class="form-control" required>
                    <option value="">-- Sélectionnez --</option>                   
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

                <div class="col-md-8">
                  <label class="form-label">Contrat concerné <span class="text-danger">*</span></label>
                  <select name="contrat_id" id="contrat_id_chef" class="form-control" required disabled>
                    <option value="">-- Sélectionnez le type d'abord --</option>
                  </select>
                </div>
              </div>
              @else
                <div class="alert alert-info mt-2">
                  Vous n’avez pas l’autorisation de <strong>consulter</strong> les contrats sur cet écran.
                </div>
              @endcan

              {{-- === Bloc ACTION: visible si droit modifier === --}}
              @can("modifier_ecran_" . ($ecran->id ?? 0))
              <div class="row g-3 mt-1">
                <div class="col-md-6">
                  <label class="form-label">Nouveau chef de projet <span class="text-danger">*</span></label>
                  <select name="nouveau_chef_id" id="nouveau_chef" class="form-control" required>
                    <option value="">-- Sélectionnez --</option>
                    @foreach($chefs as $chef)
                      <option value="{{ $chef->code_acteur }}">{{ $chef->libelle_court }} {{ $chef->libelle_long }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Motif du changement <span class="text-danger">*</span></label>
                  <textarea name="motif" id="motif" class="form-control" rows="3" required placeholder="Expliquez la raison du changement..."></textarea>
                </div>
              </div>

              <button type="submit" class="btn btn-warning mt-3">
                <i class="bi bi-arrow-repeat me-1"></i> Valider le changement
              </button>
              @else
                <div class="alert alert-info mt-3">
                  Vous n’avez pas l’autorisation de <strong>modifier</strong> sur cet écran.
                </div>
              @endcan
            </form>

          </div>
        </div>
      </div>
    </div>

    {{-- Tableau récap des contrats: consultable uniquement si droit consulter --}}
    @can("consulter_ecran_" . ($ecran->id ?? 0))
    <div class="card">
      <div class="card-body">
        <table id="table1" class="table table-striped table-bordered" style="width:100%">
          <thead>
            <tr>
              <th>Contrat #</th>
              <th>Code projet</th>
              <th>Chef projet</th>
              <th>Début</th>
              <th>Fin</th>
              <th>Statut</th>
            </tr>
          </thead>
          <tbody id="tbody-contrats">
            {{-- rempli dynamiquement --}}
          </tbody>
        </table>
      </div>
    </div>
    @endcan
  </div>
</div>

<script>
  // Horloge
  setInterval(()=>{ const el=document.getElementById('date-now'); if(el) el.textContent=new Date().toLocaleString() },1000);

  // DataTable si dispo
  document.addEventListener('DOMContentLoaded', function(){
    if (typeof initDataTable === 'function') {
      initDataTable('{{ auth()->user()->acteur?->libelle_court }} {{ auth()->user()->acteur?->libelle_long }}', 'table1', 'Contrats actifs');
    }
  });

  // ======= Helpers alertes Bootstrap =======
  function escapeHtml(str){
    return String(str)
      .replace(/&/g,'&amp;')
      .replace(/</g,'&lt;')
      .replace(/>/g,'&gt;')
      .replace(/"/g,'&quot;')
      .replace(/'/g,'&#039;');
  }

  function extractErrors(data){
    if (data?.errors) return Object.values(data.errors).flat().filter(Boolean);
    if (data?.message) return [data.message];
    return ['Erreur inconnue.'];
  }

  // ======= Chargement des contrats par type =======
  const typeSel   = document.getElementById('type_projet');
  const contratSel= document.getElementById('contrat_id_chef');
  const tbody     = document.getElementById('tbody-contrats');

  async function loadContratsByType(type){
    if(!contratSel) return; // si pas le droit consulter, pas de select
    contratSel.innerHTML = '<option value="">-- Chargement... --</option>';
    contratSel.disabled  = true;
    if (tbody) tbody.innerHTML = '';

    if(!type){
      contratSel.innerHTML = '<option value="">-- Sélectionnez le type d\'abord --</option>';
      return;
    }
    try{
      const url = @json(route('contrats.optionsContrats')) + '?type=' + encodeURIComponent(type);
      const res = await fetch(url, {headers:{'X-Requested-With':'XMLHttpRequest'}});
      if(!res.ok) throw new Error('HTTP '+res.status);
      const data = await res.json();

      if(!Array.isArray(data) || data.length===0){
        contratSel.innerHTML = '<option value="">Aucun contrat actif disponible</option>';
      }else{
        contratSel.innerHTML = '<option value="">-- Sélectionnez --</option>';
        data.forEach(row=>{
          const opt = document.createElement('option');
          opt.value = row.id;
          opt.textContent = `${row.code_projet} — ${row.chef_label} (du ${row.date_debut} au ${row.date_fin})`;
          contratSel.appendChild(opt);
        });
      }
      contratSel.disabled = false;

      if(Array.isArray(data) && tbody){
        tbody.innerHTML = data.map(r => `
          <tr>
            <td>${r.id}</td>
            <td>${r.code_projet}</td>
            <td>${r.chef_label}</td>
            <td>${r.date_debut}</td>
            <td>${r.date_fin}</td>
            <td><span class="badge bg-success">Actif</span></td>
          </tr>
        `).join('');
      }
    }catch(e){
      console.error(e);
      contratSel.innerHTML = '<option value="">Erreur de chargement</option>';
      alert("Erreur lors du chargement des contrats.", 'error');
    }
  }

  if (typeSel) {
    typeSel.addEventListener('change', ()=>loadContratsByType(typeSel.value));
  }

  // ======= Soumission AJAX du changement de chef =======
  document.getElementById('changeChefForm')?.addEventListener('submit', async function(e){
    e.preventDefault();
    const form = e.currentTarget;

    const res = await fetch(form.action, {
      method: 'POST',
      headers: {
        'X-Requested-With':'XMLHttpRequest',
        'Content-Type':'application/x-www-form-urlencoded',
        'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value
      },
      body: new URLSearchParams(new FormData(form)).toString()
    });

    const data = await res.json().catch(()=> ({}));
    if (res.ok && data?.success) {
      alert(data.success);
      const t = typeSel?.value;
      if (t) loadContratsByType(t);
      return;
    }

    if (res.status === 422) {
      alert(extractErrors(data), 'error');
    } else {
      const msg = data?.error || data?.message || 'Erreur lors du changement de chef.';
      alert(msg, 'error');
    }
  });
</script>
@endsection
