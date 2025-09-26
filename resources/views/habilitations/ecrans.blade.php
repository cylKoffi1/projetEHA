{{-- resources/views/habilitations/ecrans.blade.php --}}
@extends('layouts.app')

@section('content')

@if (session('success'))
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
  </div>
@endif

<style>
  .invalid-feedback{display:block;width:100%;margin-top:6px;font-size:80%;color:#dc3545}
  .form-card{border-radius:10px;box-shadow:0 4px 10px rgba(0,0,0,.06)}
  .bulk-bar{display:flex;gap:.5rem;flex-wrap:wrap;align-items:center}
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
              <li class="breadcrumb-item active" aria-current="page">Écrans</li>
            </ol>
          </nav>
        </div>
      </div>
    </div>
  </div>

  {{-- ======= FORMULAIRE unique (création + modification) ======= --}}
  <div class="row">
    <div class="col-12">
      <div class="card form-card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0" id="form-title">Ajout d’un écran</h5>
          <button type="button" class="btn btn-light btn-sm" id="btn-reset-form">
            <i class="bi bi-arrow-counterclockwise"></i> Réinitialiser
          </button>
        </div>
        <div class="card-body">
          <form id="ecran-form" class="form" method="POST" action="{{ route('ecran.store') }}" data-parsley-validate>
            @csrf
            <input type="hidden" name="ecran_id" value="{{ $ecran->id }}">
            <input type="hidden" id="form_mode" value="create"> {{-- create|edit --}}
            <input type="hidden" id="edit_code" name="edit_code">

            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label" for="code_rubrique">Rubrique :</label>
                <select class="form-select" id="code_rubrique" name="code_rubrique" >
                  <option value="">Sélectionnez une rubrique</option>
                  @foreach($rubriques as $rubrique)
                    <option value="{{ $rubrique->code }}">{{ $rubrique->libelle }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-4">
                <label class="form-label" for="code_sous_menu">Sous-menu :</label>
                <select class="form-select" id="code_sous_menu" name="code_sous_menu" >
                  <option value="">Sélectionnez un sous-menu</option>
                  @foreach($sous_menus as $sous_menu)
                    <option value="{{ $sous_menu->code }}">{{ $sous_menu->libelle }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-4">
                <label class="form-label" for="permission_id">Permission :</label>
                <select class="form-select" id="permission_id" name="permission_id" disabled>
                  <option value="">Générée automatiquement</option>
                  @foreach($permissions as $permission)
                    <option value="{{ $permission->id }}">{{ $permission->name }}</option>
                  @endforeach
                </select>
                <small class="text-muted">La permission <code>consulter_ecran_{id}</code> sera créée/assignée automatiquement.</small>
              </div>

              <div class="col-md-4">
                <label class="form-label" for="libelle">Libellé :</label>
                <input type="text" class="form-control" id="libelle" name="libelle" required placeholder="Libellé">
              </div>

              <div class="col-md-4">
                <label class="form-label" for="path">Route :</label>
                <input type="text" class="form-control" id="path" name="path" required placeholder="ex: /admin/xxx">
              </div>

              <div class="col-md-4">
                <label class="form-label" for="ordre">Ordre :</label>
                <input type="number" class="form-control" id="ordre" name="ordre" min="1" required>
              </div>
            </div>

            <div class="mt-3 d-flex justify-content-end gap-2 mt-2 " >
              @can("ajouter_ecran_" . $ecran->id)
                <button type="submit" class="btn btn-primary" id="submit-create">Enregistrer</button>
              @endcan
              @can("modifier_ecran_" . $ecran->id)
                <button type="submit" class="btn btn-success d-none" id="submit-update">Modifier</button>
                <button type="button" class="btn btn-outline-secondary d-none" id="cancel-edit">Annuler modification</button>
              @endcan
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  {{-- ======= TABLEAU + suppression multiple ======= --}}
  <div class="row match-height">
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0">Liste des écrans</h5>

          @can("supprimer_ecran_" . $ecran->id)
          <form id="bulk-delete-form" method="POST" action="{{ route('ecran.bulkDelete') }}" class="bulk-bar">
            @csrf
            <input type="hidden" name="ecran_id" value="{{ $ecran->id }}">
            <button type="submit" id="btn-bulk-delete" class="btn btn-outline-danger btn-sm" disabled>
              <i class="bi bi-trash3"></i> Supprimer la sélection
            </button>
          </form>
          @endcan
        </div>

        <div class="card-content">
          <div class="card-body">
            <form id="table-form">
              <div class="table-responsive">
                <table class="table table-striped table-bordered" id="table1" style="width:100%">
                  <thead>
                    <tr>
                      <th style="width:36px;">
                        <input type="checkbox" id="check-all">
                      </th>
                      <th>Id</th>
                      <th>Rubrique</th>
                      <th>Sous-menu</th>
                      <th>Permission</th>
                      <th>Libellé</th>
                      <th>Route</th>
                      <th>Ordre</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($ecrans as $e)
                      <tr>
                        <td>
                          @can("supprimer_ecran_" . $ecran->id)
                          <input type="checkbox" class="row-check" name="ids[]" value="{{ $e->id }}" form="bulk-delete-form">
                          @endcan
                        </td>
                        <td>{{ $e->id }}</td>
                        <td>{{ $e->rubrique->libelle ?? '' }}</td>
                        <td>{{ $e->sousMenu->libelle ?? '' }}</td>
                        <td>{{ $e->permission->name ?? '' }}</td>
                        <td>{{ $e->libelle }}</td>
                        <td>{{ $e->path }}</td>
                        <td>{{ $e->ordre }}</td>
                        <td class="text-nowrap">
                          @can("modifier_ecran_" . $ecran->id)
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="loadEcran('{{ $e->id }}')">
                              <i class="bi bi-pencil-square"></i>
                            </button>
                          @endcan

                          @can("supprimer_ecran_" . $ecran->id)
                            <form action="{{ route('ecran.delete', $e->id) }}" method="POST" style="display:inline;">
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
              </div>
            </form>
          </div>
        </div>

      </div>
    </div>
  </div>
</section>

<script>
  // Horloge
  setInterval(()=>{ const el=document.getElementById('date-now'); if(el) el.textContent=new Date().toLocaleString(); },1000);

  // Helpers formulaire
  function setCreateMode(){
    $('#form_mode').val('create');
    $('#form-title').text("Ajout d’un écran");
    $('#submit-create').removeClass('d-none');
    $('#submit-update, #cancel-edit').addClass('d-none');
    $('#ecran-form').attr('action', '{{ route('ecran.store') }}');
    $('#edit_code').val('');
  }
  function setEditMode(id){
    $('#form_mode').val('edit');
    $('#form-title').text("Modification d’écran");
    $('#submit-create').addClass('d-none');
    $('#submit-update, #cancel-edit').removeClass('d-none');
    $('#ecran-form').attr('action', '{{ route('ecran.update') }}');
    $('#edit_code').val(id);
  }
  function resetForm(){
    $('#ecran-form')[0].reset();
    setCreateMode();
  }

  // Charger un écran dans le formulaire
  function loadEcran(id){
    $.get('{{ url('/admin/ecran/get-ecran') }}/' + id, function(data){
      $('#libelle').val(data.libelle);
      $('#ordre').val(data.ordre);
      $('#path').val(data.path);
      $('#code_sous_menu').val(data.code_sous_menu);
      $('#code_rubrique').val(data.code_rubrique);
      $('#permission_id').val(data.permission_id);
      setEditMode(id);
    }).fail(function(xhr){
      console.error(xhr.responseJSON?.error || 'Erreur de chargement');
    });
  }

  $(document).ready(function(){
    initDataTable('{{ auth()->user()?->acteur?->libelle_court }} {{ auth()->user()?->acteur?->libelle_long }}', 'table1', 'Liste des écrans');

    // Boutons formulaire
    $('#btn-reset-form, #cancel-edit').on('click', resetForm);

    // Sélection multiple
    const $btnBulk = $('#btn-bulk-delete');
    $('#check-all').on('change', function(){
      const checked = this.checked;
      $('.row-check').prop('checked', checked).trigger('change');
    });
    $(document).on('change', '.row-check', function(){
      const any = $('.row-check:checked').length > 0;
      $btnBulk.prop('disabled', !any);
      if (!this.checked) $('#check-all').prop('checked', false);
      if ($('.row-check:checked').length === $('.row-check').length) $('#check-all').prop('checked', true);
    });
  });
</script>
@endsection
