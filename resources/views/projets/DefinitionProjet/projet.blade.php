{{-- resources/views/projets/DefinitionProjet/projet.blade.php --}}
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
          <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i> Projet</h3>
        </div>
        <div class="col-12 col-md-6 order-md-2 order-first">
          <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Définition de projet</a></li>
              <li class="breadcrumb-item active" aria-current="page">Nouveau projet</li>
            </ol>
          </nav>
          <script>
            setInterval(() => {
              const el = document.getElementById('date-now');
              if (el) el.textContent = new Date().toLocaleString();
            }, 1000);
          </script>
        </div>
      </div>
    </div>
  </div>

  {{-- Flash messages --}}
  @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
      <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
    </div>
  @endif
  @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
      <i class="bi bi-x-circle-fill me-2"></i>{{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
    </div>
  @endif
  @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
    </div>
  @endif

  <div class="container">
    <div class="row match-height">
      <div class="col-12">
        <div class="card">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h4 class="card-title mb-0">Chef de projet</h4>
          </div>
          <div class="card-body">
            <form id="contratForm" action="{{ route('contrats.store') }}" method="POST">
              @csrf
              <input type="hidden" name="ecran_id" value="{{ $ecran->id }}">
              <input type="hidden" name="contrat_id" id="contrat_id">

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
                  <small class="text-muted">Choisissez d’abord le type, puis le projet.</small>
                </div>

                <div class="col-md-4">
                  <label class="form-label">Projet <span class="text-danger">*</span></label>
                  <select name="projet_id" id="projet_id" class="form-control" required disabled>
                    <option value="">-- Sélectionnez le type d'abord --</option>
                  </select>
                  <small class="text-muted">
                    Affiche uniquement les projets <strong>validés (workflow)</strong>, et <strong>sans chef actif</strong>.
                  </small>
                </div>

                <div class="col-md-4">
                  <label class="form-label">Chef de projet <span class="text-danger">*</span></label>
                  <select name="chef_projet_id" class="form-control" required>
                    <option value="">-- Sélectionnez --</option>
                    @foreach($chefs as $chef)
                      <option value="{{ $chef->code_acteur }}">{{ $chef?->libelle_court }} {{ $chef?->libelle_long }}</option>
                    @endforeach
                  </select>
                </div>
              </div>

              <div class="row g-3 mt-1">
                <div class="col-md-6">
                  <label class="form-label">Date début <span class="text-danger">*</span></label>
                  <input type="date" name="date_debut" class="form-control" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Date fin <span class="text-danger">*</span></label>
                  <input type="date" name="date_fin" class="form-control" required>
                </div>
              </div>

              @can("ajouter_ecran_" . $ecran->id)
              <div class="text-end mt-3">
                <button type="submit" id="formButton" class="btn btn-primary mt-3">
                  <i class="bi bi-check2-circle me-1"></i> Enregistrer
                </button>
              </div>
              @endcan
            </form>
          </div>
        </div>
      </div>
    </div>

    {{-- TABLE LISTE DES CONTRATS --}}
    <div class="card">
      <div class="card-body">
        <table class="table table-striped table-bordered" cellspacing="0" style="width:100%" id="table1">
          <thead>
            <tr>
              <th>Code projet</th>
              <th>Chef projet</th>
              <th>Date début</th>
              <th>Date fin</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach($contrats as $contrat)
              <tr>
                <td>{{ $contrat->code_projet }}</td>
                <td>{{ $contrat->acteur->libelle_court ?? '' }} {{ $contrat->acteur->libelle_long ?? '' }}</td>
                <td>{{ $contrat->date_debut }}</td>
                <td>{{ $contrat->date_fin }}</td>
                <td>
                  <div class="dropdown">
                    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="dropdownMenu{{ $contrat->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                      Actions
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenu{{ $contrat->id }}">
                      @can("modifier_ecran_" . $ecran->id)
                        <li>
                          <button class="dropdown-item text-warning" type="button"
                            onclick="editContrat(@js([
                              'id'          => $contrat->id,
                              'code_projet' => $contrat->code_projet,
                              'code_acteur' => $contrat->code_acteur,
                              'date_debut'  => $contrat->date_debut,
                              'date_fin'    => $contrat->date_fin,
                            ]))">
                            <i class="bi bi-pencil-square"></i> Modifier
                          </button>
                        </li>
                      @endcan

                      @can("supprimer_ecran_" . $ecran->id)
                        <li>
                          <form action="{{ route('contrats.destroy', $contrat->id) }}" method="POST" onsubmit="return confirm('Confirmer la suppression ?');">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="ecran_id" value="{{ $ecran->id }}">
                            <button class="dropdown-item text-danger" type="submit">
                              <i class="bi bi-trash"></i> Supprimer
                            </button>
                          </form>
                        </li>
                      @endcan

                      @can("consulter_ecran_" . $ecran->id)
                        <li>
                          <a class="dropdown-item text-info" href="{{ route('contrats.fiche', $contrat->id) }}">
                            <i class="bi bi-file-earmark-text"></i> Voir fiche
                          </a>
                        </li>
                        <li>
                          <a class="dropdown-item text-secondary" href="{{ route('contrats.pdf', $contrat->id) }}">
                            <i class="bi bi-download"></i> Télécharger
                          </a>
                        </li>
                      @endcan
                    </ul>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

{{-- JS --}}
<script>
  function goBack(){ window.history.back(); }

  // DataTable si dispo globalement
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof initDataTable === 'function') {
      initDataTable('{{ auth()->user()->acteur?->libelle_court }} {{ auth()->user()->acteur?->libelle_long }}', 'table1', 'Liste des chefs de projet');
    }
  });

  // Soumission AJAX (create / update)
  document.getElementById('contratForm').addEventListener('submit', function(e){
    e.preventDefault();
    const form   = e.currentTarget;
    const url    = form.getAttribute('action');
    const method = (form.querySelector('input[name="_method"]')?.value) || 'POST';

    fetch(url, {
      method: method,
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value
      },
      body: new URLSearchParams(new FormData(form)).toString()
    })
    .then(async r => {
      if (r.ok) return r.json();
      const data = await r.json().catch(()=>({}));
      const errs = (data && data.errors) ? Object.values(data.errors).flat() : [data.message || 'Erreur inconnue'];
      throw new Error(errs.join('\n'));
    })
    .then(data => {
      if (data.success) { alert(data.success); setTimeout(()=>location.reload(), 600); }
      else { location.reload(); }
    })
    .catch(err => alert(err.message));
  });

  // Chargement dynamique des projets selon le type
  const typeSel   = document.getElementById('type_projet');
  const projetSel = document.getElementById('projet_id');

  async function loadProjectsByType(type, preselect = null) {
    projetSel.innerHTML = '<option value="">-- Chargement... --</option>';
    projetSel.disabled = true;

    if (!type) {
      projetSel.innerHTML = '<option value="">-- Sélectionnez le type d\'abord --</option>';
      return;
    }

    try {
      const url = @json(route('contrats.optionsProjets')) + '?type=' + encodeURIComponent(type);
      const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
      if (!res.ok) throw new Error('HTTP ' + res.status);
      const data = await res.json();

      if (!Array.isArray(data) || data.length === 0) {
        projetSel.innerHTML = '<option value="">Aucun projet disponible</option>';
      } else {
        projetSel.innerHTML = '<option value="">-- Sélectionnez --</option>';
        data.forEach(row => {
          const opt = document.createElement('option');
          opt.value = row.code;
          opt.textContent = row.code + (row.label ? ' — ' + row.label : '');
          projetSel.appendChild(opt);
        });
        if (preselect && data.some(d => d.code === preselect)) {
          projetSel.value = preselect;
        }
      }
      projetSel.disabled = false;
    } catch (e) {
      console.error(e);
      projetSel.innerHTML = '<option value="">Erreur de chargement</option>';
      projetSel.disabled = true;
      alert("Erreur lors du chargement des projets.");
    }
  }

  typeSel.addEventListener('change', () => loadProjectsByType(typeSel.value));

  // Détection du type selon le code (pour l’édition)
  function detectTypeFromCode(code){
    if (!code) return '';
    if (code.startsWith('ET_')) return 'ETUDE';
    if (code.startsWith('APPUI_')) return 'APPUI';
    return 'PROJET';
  }

  // Edition d’un contrat : pré-remplir type + recréer la liste des projets puis sélectionner
  async function editContrat(data) {
    document.getElementById('contrat_id').value = data.id;

    // Détecter et fixer le type
    const detectedType = detectTypeFromCode(data.code_projet);
    typeSel.value = detectedType;

    // Charger les projets du type et présélectionner le code
    await loadProjectsByType(detectedType, data.code_projet);

    // Remplir le reste
    document.querySelector('select[name="chef_projet_id"]').value = data.code_acteur;
    document.querySelector('input[name="date_debut"]').value = data.date_debut;
    document.querySelector('input[name="date_fin"]').value   = data.date_fin;

    // Switch en mode UPDATE
    const form = document.getElementById('contratForm');
    // retirer ancien _method s'il existe
    const old = form.querySelector('input[name="_method"]');
    if (old) old.remove();
    form.setAttribute('action', `{{ url('/') }}/contrats/${data.id}`);
    const hidden = document.createElement('input');
    hidden.type = 'hidden'; hidden.name = '_method'; hidden.value = 'PUT';
    form.appendChild(hidden);

    document.getElementById('formButton').textContent = 'Mettre à jour';
  }
</script>
@endsection
