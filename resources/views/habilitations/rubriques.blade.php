{{-- resources/views/habilitations/rubriques.blade.php --}}
@extends('layouts.app')

@section('content')

{{-- Message de succès (sans popup) --}}
@if (session('success'))
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
  </div>
@endif

{{-- Erreurs de validation --}}
@if ($errors->any())
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <ul class="mb-0">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
  </div>
@endif

@php
  $nextOrdre = (($rubriquePlusGrandOrdre->ordre ?? 0) + 1);
@endphp

<style>
  .invalid-feedback{display:block;width:100%;margin-top:6px;font-size:80%;color:#dc3545}
  .form-card{border-radius:10px;box-shadow:0 4px 10px rgba(0,0,0,.06)}
  .icon-preview{display:inline-flex;align-items:center;gap:.5rem}
</style>

<section id="multiple-column-form">
  <div class="page-heading">
    <div class="page-title">
      <div class="row">
        <div class="col-sm-12">
          <div class="breadcrumb-item" style="text-align:right;padding:5px;">
            <span id="date-now" style="color:#34495E;"></span>
          </div>
        </div>
      </div>
      <div class="row align-items-center">
        <div class="col-12 col-md-6 order-md-1 order-last">
          <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i> Plateforme</h3>
        </div>
        <div class="col-12 col-md-6 order-md-2 order-first">
          <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="">Gestion des habilitations</a></li>
              <li class="breadcrumb-item active" aria-current="page">Rubriques</li>
            </ol>
          </nav>
        </div>
      </div>
    </div>
  </div>

  {{-- ====== FORMULAIRE UNIQUE (création + modification) ====== --}}
  <div class="row">
    <div class="col-12">
      <div class="card form-card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0" id="form-title">Ajout d’une rubrique</h5>
          <button type="button" class="btn btn-light btn-sm" id="btn-reset-form">
            <i class="bi bi-arrow-counterclockwise"></i> Réinitialiser
          </button>
        </div>
        <div class="card-body">
          <form id="rubrique-form" class="form" method="POST" action="{{ route('rubrique.store') }}" data-parsley-validate>
            @csrf
            <input type="hidden" name="ecran_id" value="{{ $ecran->id }}">
            <input type="hidden" id="form_mode" value="create"> {{-- create|edit --}}
            <input type="hidden" id="edit_code" name="edit_code">

            <div class="row g-3">
              <div class="col-md-4 col-12">
                <label class="form-label" for="libelle">Libellé :</label>
                <input type="text" class="form-control" id="libelle" name="libelle" placeholder="Libellé" required>
              </div>

              <div class="col-md-4 col-12">
                <label class="form-label" for="ordre">Ordre :</label>
                <input type="number" class="form-control" id="ordre" name="ordre" min="1" value="{{ $nextOrdre }}" required>
              </div>

              <div class="col-md-4 col-12">
                <label class="form-label" for="class_icone">Classe icône :</label>
                <div class="input-group">
                  <input type="text" class="form-control" id="class_icone" name="class_icone"
                         value="bi bi-people-fill md-2" placeholder="ex: bi bi-people-fill" required>
                  <span class="input-group-text icon-preview">
                    <i id="icon-preview" class="bi bi-people-fill md-2"></i>
                  </span>
                </div>
                <small class="text-muted">Saisir une classe Bootstrap Icons (ex: <code>bi bi-gear</code>).</small>
              </div>
            </div>

            <div class="mt-3 d-flex justify-content-end gap-2">
              <button type="submit" class="btn btn-primary" id="submit-create">Enregistrer</button>
              <button type="submit" class="btn btn-success d-none" id="submit-update">Modifier</button>
              <button type="button" class="btn btn-outline-secondary d-none" id="cancel-edit">Annuler modification</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  {{-- ====== TABLEAU ====== --}}
  <div class="row match-height">
    <div class="col-12">
      <div class="card">
        <div class="card-header text-center">
          <h5 class="card-title mb-0">Liste des rubriques</h5>
        </div>

        <div class="card-content">
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-bordered" id="table1" style="width: 100%">
                <thead>
                  <tr>
                    <th>Code</th>
                    <th>Libellé</th>
                    <th>Ordre</th>
                    <th>Classe icône</th>
                    <th style="width:120px;">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($rubriques as $rubrique)
                  <tr>
                    <td>{{ $rubrique->code }}</td>
                    <td>{{ $rubrique->libelle }}</td>
                    <td>{{ $rubrique->ordre }}</td>
                    <td class="text-nowrap">
                      <i class="{{ $rubrique->class_icone ?? 'bi bi-gear' }} me-1"></i>
                      {{ $rubrique->class_icone }}
                    </td>
                    <td class="text-nowrap">
                      @can("modifier_ecran_" . $ecran->id)
                        <button type="button" class="btn btn-sm btn-outline-primary me-1"
                                onclick="loadRubrique('{{ $rubrique->code }}')">
                          <i class="bi bi-pencil-square"></i>
                        </button>
                      @endcan

                      @can("supprimer_ecran_" . $ecran->id)
                        <form action="{{ route('rubrique.delete', $rubrique->code) }}" method="POST" style="display:inline;">
                          @csrf @method('DELETE')
                          <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-trash3-fill"></i>
                          </button>
                        </form>
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
    </div>
  </div>
</section>

<script>
  // horloge
  setInterval(()=>{ const el=document.getElementById('date-now'); if(el) el.textContent=new Date().toLocaleString(); },1000);

  // aperçu de l'icône
  document.addEventListener('input', function(e){
    if(e.target && e.target.id === 'class_icone'){
      const v = e.target.value || '';
      const el = document.getElementById('icon-preview');
      if(el){ el.className = v; }
    }
  });

  // helpers de mode
  function switchNames(mode){
    // crée/modifie : on renomme les champs pour correspondre aux endpoints existants
    if(mode === 'create'){
      document.getElementById('libelle').setAttribute('name','libelle');
      document.getElementById('ordre'  ).setAttribute('name','ordre');
      document.getElementById('class_icone').setAttribute('name','class_icone');
    } else {
      document.getElementById('libelle').setAttribute('name','edit_libelle');
      document.getElementById('ordre'  ).setAttribute('name','edit_ordre');
      document.getElementById('class_icone').setAttribute('name','edit_class_icone');
    }
  }

  function setCreateMode(){
    document.getElementById('form_mode').value = 'create';
    document.getElementById('form-title').textContent = "Ajout d’une rubrique";
    document.getElementById('submit-create').classList.remove('d-none');
    document.getElementById('submit-update').classList.add('d-none');
    document.getElementById('cancel-edit').classList.add('d-none');
    document.getElementById('rubrique-form').setAttribute('action', '{{ route("rubrique.store") }}');
    document.getElementById('edit_code').value = '';
    switchNames('create');
  }

  function setEditMode(code){
    document.getElementById('form_mode').value = 'edit';
    document.getElementById('form-title').textContent = "Modification de rubrique";
    document.getElementById('submit-create').classList.add('d-none');
    document.getElementById('submit-update').classList.remove('d-none');
    document.getElementById('cancel-edit').classList.remove('d-none');
    document.getElementById('rubrique-form').setAttribute('action', '{{ route("rubrique.update") }}');
    document.getElementById('edit_code').value = code;
    switchNames('edit');
  }

  function resetForm(){
    const form = document.getElementById('rubrique-form');
    form.reset();
    // rétablir l’aperçu icône
    document.getElementById('icon-preview').className = 'bi bi-people-fill md-2';
    setCreateMode();
  }

  // charge une rubrique dans le formulaire (sans popup)
  function loadRubrique(code){
    const url = '{{ url("/admin/rubrique/get-rubrique") }}' + '/' + encodeURIComponent(code);
    fetch(url).then(r => r.json()).then(data => {
      if(data?.error){ console.error(data.error); return; }
      document.getElementById('libelle').value = data.libelle || '';
      document.getElementById('ordre').value = data.ordre ?? '';
      document.getElementById('class_icone').value = data.class_icone || '';
      document.getElementById('icon-preview').className = data.class_icone || '';
      setEditMode(data.code);
    }).catch(err => console.error(err));
  }

  // init
  document.addEventListener('DOMContentLoaded', function(){
    // DataTable custom
    if (typeof initDataTable === 'function') {
      initDataTable(
        '{{ auth()->user()?->acteur?->libelle_court }} {{ auth()->user()?->acteur?->libelle_long }}',
        'table1',
        'Liste des rubriques'
      );
    }
    document.getElementById('btn-reset-form').addEventListener('click', resetForm);
    document.getElementById('cancel-edit').addEventListener('click', resetForm);
    setCreateMode();
  });
</script>
@endsection
