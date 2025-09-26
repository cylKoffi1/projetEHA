<!-- resources/views/parGeneraux/devises.blade.php -->
@extends('layouts.app')

@section('content')
@isset($ecran)
  @can("consulter_ecran_" . $ecran->id)

@if (session('success'))
<script>
  (function () {
    const msg = @json(session('success'));
    function ready(){ if (window.jQuery && typeof $('#alertModal').modal==='function'){ $('#alertMessage').text(msg); $('#alertModal').modal('show'); } else { setTimeout(ready,50); } }
    document.readyState==='loading'?document.addEventListener('DOMContentLoaded',ready):ready();
  })();
</script>
@endif

<style>
  .invalid-feedback{display:block;margin-top:6px;font-size:80%;color:#dc3545}
  .form-card{border-radius:10px;box-shadow:0 4px 10px rgba(0,0,0,.06)}
  .sticky-actions{display:flex;gap:.5rem;flex-wrap:wrap}
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
              <li class="breadcrumb-item"><a href="">Paramètre généraux</a></li>
              <li class="breadcrumb-item active" aria-current="page">Dévises</li>
            </ol>
          </nav>
        </div>
      </div>
    </div>
  </div>

  {{-- ======= FORMULAIRE (création + modification) ======= --}}
  <div class="row">
    <div class="col-12">
      <div class="card form-card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0">
            <span id="form-title">Ajout d'une dévise</span>
          </h5>
          <div class="sticky-actions">
            <button type="button" class="btn btn-light btn-sm" id="btn-reset-form" title="Réinitialiser le formulaire">
              <i class="bi bi-arrow-counterclockwise"></i> Réinitialiser
            </button>
          </div>
        </div>
        <div class="card-body">
          <form class="form" id="devise-form" method="POST" action="{{ route('devise.store') }}" data-parsley-validate>
            @csrf
            <input type="hidden" name="ecran_id" value="{{ $ecran->id }}">
            <input type="hidden" id="form_mode" value="create"> {{-- create|edit --}}

            <div class="row g-3">
              <div class="col-md-3">
                <label class="form-label" for="code">Code :</label>
                <input type="text" class="form-control" id="code" name="code" placeholder="Ex: XOF" required>
                <div class="invalid-feedback" id="code-feedback" style="display:none;">Code déjà utilisé</div>
              </div>
              <div class="col-md-3">
                <label class="form-label" for="libelle">Libelle :</label>
                <input type="text" class="form-control" id="libelle" name="libelle" placeholder="Franc CFA" required>
              </div>
              <div class="col-md-3">
                <label class="form-label" for="monnaie">Monnaie :</label>
                <input type="text" class="form-control" id="monnaie" name="monnaie" placeholder="Franc" required>
              </div>
              <div class="col-md-3">
                <label class="form-label" for="code_long">Code long :</label>
                <input type="text" class="form-control" id="code_long" name="code_long" placeholder="Code long" required>
              </div>
              <div class="col-md-3">
                <label class="form-label" for="code_court">Code court :</label>
                <input type="text" class="form-control" id="code_court" name="code_court" placeholder="Code court" required>
              </div>

              <div class="col-md-9">
                <label class="form-label" for="pays_ids">Pays associés (facultatif) :</label>
                <select class="form-select" id="pays_ids" name="pays_ids" >
                  @foreach($pays as $p)
                    <option value="{{ $p->id }}">{{ $p->nom_fr_fr }} ({{ $p->alpha3 }})</option>
                  @endforeach
                </select>
                <small class="text-muted">
                  Les pays sélectionnés seront associés à cette dévise (champ <code>pays.code_devise</code>).  
                  <strong>Note :</strong> on n'enlève pas d'association existante automatiquement.
                </small>
              </div>
            </div>

            <div class="mt-3 d-flex gap-2">
              @can("ajouter_ecran_" . $ecran->id)
                <button type="submit" class="btn btn-primary" id="submit-create">Enregistrer</button>
              @endcan
              @can("modifier_ecran_" . $ecran->id)
                <button type="button" class="btn btn-success d-none" id="submit-update">Modifier</button>
                <button type="button" class="btn btn-outline-secondary d-none" id="cancel-edit">Annuler modification</button>
              @endcan
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  {{-- ======= TABLEAU ======= --}}
  <div class="row match-height">
    <div class="col-12">
      <div class="card">
        <div class="card-header text-center">
          <h5 class="card-title mb-0">Liste des dévises</h5>
        </div>
        <div class="card-content">
          <div class="card-body">
            <table class="table table-striped table-bordered" id="table1" style="width:100%">
              <thead>
                <tr>
                  <th>Code</th>
                  <th>Libelle</th>
                  <th>Monnaie</th>
                  <th>Code Long</th>
                  <th>Code court</th>
                  <th>Pays liés</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($devises as $devise)
                  @php
                    $paysLies = $paysParDevise[$devise->code] ?? collect();
                  @endphp
                  <tr>
                    <td>{{ $devise->code }}</td>
                    <td>{{ $devise->libelle }}</td>
                    <td>{{ $devise->monnaie }}</td>
                    <td>{{ $devise->code_long }}</td>
                    <td>{{ $devise->code_court }}</td>
                    <td>
                      @if($paysLies->count())
                        {{ $paysLies->pluck('nom_fr_fr')->join(', ') }}
                      @else
                        <span class="text-muted">—</span>
                      @endif
                    </td>
                    <td>
                      <div class="d-flex gap-2">
                        @can("modifier_ecran_" . $ecran->id)
                          <button class="btn btn-sm btn-outline-primary" onclick="loadDevise('{{ $devise->code }}')">
                            <i class="bi bi-pencil-square"></i>
                          </button>
                        @endcan
                        @can("supprimer_ecran_" . $ecran->id)
                          <button class="btn btn-sm btn-outline-danger" onclick="deleteDevise('{{ $devise->code }}')">
                            <i class="bi bi-trash3-fill"></i>
                          </button>
                        @endcan
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
  </div>
</section>

<script>
  // Horloge
  setInterval(()=>{ const el=document.getElementById('date-now'); if(el) el.textContent=new Date().toLocaleString(); },1000);

  $(document).ready(function () {
    initDataTable('{{ auth()->user()->acteur->libelle_court }} {{ auth()->user()->acteur->libelle_long }}', 'table1', 'Liste des dévises');

    // ----- Validation code unique en création -----
    $('#code').on('input', function(){
      const mode = $('#form_mode').val();
      if (mode !== 'create') return;         // pas besoin en édition
      const code = $(this).val();
      if (!code) return;
      $.post('/check-devise-code', {_token:'{{ csrf_token() }}', code}, function(resp){
        if (resp.exists){
          $('#code').addClass('is-invalid').removeClass('is-valid');
          $('#code-feedback').show();
          $('#submit-create').prop('disabled', true);
        } else {
          $('#code').addClass('is-valid').removeClass('is-invalid');
          $('#code-feedback').hide();
          $('#submit-create').prop('disabled', false);
        }
      });
    });

    // ----- Boutons formulaire -----
    $('#btn-reset-form').on('click', resetForm);
    $('#cancel-edit').on('click', resetForm);

    // En mode édition, on poste sur la route update :
    $('#submit-update').on('click', function(){
      const $f = $('#devise-form');
      $f.attr('action', '{{ route('devise.update') }}');
      $f.append('<input type="hidden" name="code_edit" id="code_edit_hidden">');
      $('#code_edit_hidden').val($('#code').val());
      $f.trigger('submit');
    });
  });

  function resetForm(){
    $('#devise-form').trigger('reset');
    $('#pays_ids').val([]).change?.();
    $('#code').prop('readonly', false).removeClass('is-invalid is-valid');
    $('#code-feedback').hide();
    $('#form_mode').val('create');
    $('#form-title').text("Ajout d'une dévise");
    $('#submit-create').removeClass('d-none');
    $('#submit-update, #cancel-edit').addClass('d-none');
    $('#devise-form').attr('action', '{{ route('devise.store') }}');
    $('#code_edit_hidden').remove();
  }

  // Charge une dévise dans le formulaire (édition)
  function loadDevise(code){
    $.get('{{ url('admin/devise') }}/' + code, function(data){
      // champs dévise
      $('#code').val(data.code).prop('readonly', true);
      $('#libelle').val(data.libelle);
      $('#monnaie').val(data.monnaie);
      $('#code_long').val(data.code_long);
      $('#code_court').val(data.code_court);

      // pays liés (pré-sélection)
      const paysIds = (data.pays_ids||[]);
      $('#pays_ids').val(paysIds).change?.();

      // bascule en mode édition
      $('#form_mode').val('edit');
      $('#form-title').text("Modification de dévise");
      $('#submit-create').addClass('d-none');
      $('#submit-update, #cancel-edit').removeClass('d-none');
      $('#devise-form').attr('action','{{ route('devise.update') }}');
    }).fail(function(xhr){
      const err = (xhr.responseJSON && xhr.responseJSON.error) ? xhr.responseJSON.error : "Erreur de chargement";
      showPopup(err);
    });
  }

  function deleteDevise(code){
    if (!confirm("Êtes-vous sûr de vouloir supprimer cette devise ?")) return;
    $.ajax({
      url: '{{ url('admin/devise/delete') }}/' + code,
      method: 'DELETE',
      data: {_token: '{{ csrf_token() }}', ecran_id: '{{ $ecran->id }}'},
      success: function(){
        showPopup("Dévise supprimé avec succès.");
        window.location.reload(true);
      },
      error: function(resp){
        showPopup(resp.responseJSON?.error || "Suppression impossible.");
      }
    });
  }
</script>

  @endcan
@endisset
@endsection
