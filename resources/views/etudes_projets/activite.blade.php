{{-- resources/views/etudes_projets/activite.blade.php --}}
@extends('layouts.app')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<style>
  .invalid-feedback{display:block}
  .select2-container{width:100%!important}
</style>
@endpush

@section('content')
@isset($ecran)
@can("consulter_ecran_" . $ecran->id)

<div class="container-fluid py-3">
  <div class="row mb-3">
    <div class="col"><h3>Activités connexes</h3></div>
    <div class="col-auto"><span id="date-now" class="text-muted"></span></div>
  </div>

  {{-- Flash -> modal --}}
  @if (session('success'))
    <script>window.addEventListener('DOMContentLoaded',()=>{showAlert(`{{ session('success') }}`);});</script>
  @endif
  @if (session('error'))
    <script>window.addEventListener('DOMContentLoaded',()=>{showAlert(`{{ session('error') }}`);});</script>
  @endif
  @if ($errors->any())
    <div class="alert alert-danger">
      <div class="fw-semibold mb-1">Veuillez corriger :</div>
      <ul class="mb-0">
        @foreach ($errors->all() as $err) <li>{{ $err }}</li> @endforeach
      </ul>
    </div>
  @endif

  <div class="card mb-4">
    <div class="card-header"><strong id="formTitle">Enregistrer une activité</strong></div>
    <div class="card-body">
      {{-- CREATE --}}
      <form id="addForm" action="{{ route('travaux_connexes.store') }}" method="POST" novalidate>
        @csrf
        <input type="hidden" name="ecran_id" value="{{ $ecran->id }}">

        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Projet <span class="text-danger">*</span></label>
            <select name="code_projet" id="code_projet" class="form-select" required>
              <option value="">—</option>
              @foreach($projets as $projet)
                <option value="{{ $projet->code_projet }}">{{ $projet->code_projet }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Type de travaux <span class="text-danger">*</span></label>
            <select name="type_travaux_id" id="type_travaux_id" class="form-select" required>
              <option value="">—</option>
              @foreach($typesTravaux as $t)
                <option value="{{ $t->id }}">{{ $t->libelle }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Coût (XOF) <span class="text-danger">*</span></label>
            <input type="text" name="cout_projet" id="cout_projet" class="form-control text-end" required oninput="formatNumber(this)">
          </div>
          <div class="col-md-3"></div>

          <div class="col-md-3">
            <label class="form-label">Début prévisionnel <span class="text-danger">*</span></label>
            <input type="date" name="date_debut_previsionnelle" id="date_debut_previsionnelle" class="form-control" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Fin prévisionnelle <span class="text-danger">*</span></label>
            <input type="date" name="date_fin_previsionnelle" id="date_fin_previsionnelle" class="form-control" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Début effectif</label>
            <input type="date" name="date_debut_effective" id="date_debut_effective" class="form-control">
          </div>
          <div class="col-md-3">
            <label class="form-label">Fin effective</label>
            <input type="date" name="date_fin_effective" id="date_fin_effective" class="form-control">
          </div>

          <div class="col-12">
            <label class="form-label">Commentaire</label>
            <textarea name="commentaire" id="commentaire" class="form-control" rows="2"></textarea>
          </div>

          <div class="col-12 text-end">
            @can("ajouter_ecran_" . $ecran->id)
              <button type="submit" class="btn btn-primary">Enregistrer</button>
            @endcan
          </div>
        </div>
      </form>

      {{-- EDIT --}}
      <div id="editFormContainer" class="mt-4 d-none">
        <hr>
        <form id="formAction" action="" method="POST">
          @csrf
          @method('PUT')
          <input type="hidden" name="ecran_id" value="{{ $ecran->id }}">
          <input type="hidden" id="edit_codeActivite" name="codeActivite">

          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label">Projet</label>
              <input type="text" id="edit_code_projet" name="code_projet" class="form-control" readonly>
            </div>
            <div class="col-md-3">
              <label class="form-label">Type de travaux <span class="text-danger">*</span></label>
              <select id="edit_type_travaux_id" name="type_travaux_id" class="form-select" required>
                @foreach($typesTravaux as $t)
                  <option value="{{ $t->id }}">{{ $t->libelle }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Coût (XOF) <span class="text-danger">*</span></label>
              <input type="text" id="edit_cout_projet" name="cout_projet" class="form-control text-end" required oninput="formatNumber(this)">
            </div>
            <div class="col-md-3"></div>

            <div class="col-md-3">
              <label class="form-label">Début prévisionnel <span class="text-danger">*</span></label>
              <input type="date" id="edit_date_debut_previsionnelle" name="date_debut_previsionnelle" class="form-control" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">Fin prévisionnelle <span class="text-danger">*</span></label>
              <input type="date" id="edit_date_fin_previsionnelle" name="date_fin_previsionnelle" class="form-control" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">Début effectif</label>
              <input type="date" id="edit_date_debut_effective" name="date_debut_effective" class="form-control">
            </div>
            <div class="col-md-3">
              <label class="form-label">Fin effective</label>
              <input type="date" id="edit_date_fin_effective" name="date_fin_effective" class="form-control">
            </div>

            <div class="col-12">
              <label class="form-label">Commentaire</label>
              <textarea id="edit_commentaire" name="commentaire" class="form-control" rows="2"></textarea>
            </div>

            <div class="col-12 text-end">
              @can("modifier_ecran_" . $ecran->id)
                <button type="submit" class="btn btn-primary">Modifier</button>
                <button type="button" class="btn btn-outline-secondary" onclick="cancelEdit()">Annuler</button>
              @endcan
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- LISTE --}}
  <div class="card">
    <div class="card-header"><strong>Liste des activités connexes</strong></div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle" id="table">
          <thead class="table-light">
            <tr>
              <th>Code</th>
              <th>Projet</th>
              <th>Type</th>
              <th class="text-end">Coût (XOF)</th>
              <th>Début prév.</th>
              <th>Fin prév.</th>
              <th>Début eff.</th>
              <th>Fin eff.</th>
              <th>Commentaire</th>
              <th style="width:1%;">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($travaux as $t)
              <tr>
                <td>{{ $t->codeActivite }}</td>
                <td>{{ $t->projet?->code_projet }}</td>
                <td>{{ $t->typeTravaux?->libelle }}</td>
                <td class="text-end">{{ $t->cout_projet_fmt }}</td>
                <td>{{ optional($t->date_debut_previsionnelle)->format('d/m/Y') }}</td>
                <td>{{ optional($t->date_fin_previsionnelle)->format('d/m/Y') }}</td>
                <td>{{ optional($t->date_debut_effective)->format('d/m/Y') }}</td>
                <td>{{ optional($t->date_fin_effective)->format('d/m/Y') }}</td>
                <td>{{ $t->commentaire }}</td>
                <td class="text-nowrap">
                  <div class="btn-group btn-group-sm">
                    @can("modifier_ecran_" . $ecran->id)
                    <button class="btn btn-outline-primary" title="Modifier"
                      onclick="editActivite(
                        @js($t->codeActivite),
                        @js($t->code_projet),
                        @js($t->type_travaux_id),
                        @js($t->cout_projet),
                        @js(optional($t->date_debut_previsionnelle)->toDateString()),
                        @js(optional($t->date_fin_previsionnelle)->toDateString()),
                        @js(optional($t->date_debut_effective)->toDateString()),
                        @js(optional($t->date_fin_effective)->toDateString()),
                        @js($t->commentaire)
                      )"><i class="bi bi-pencil"></i></button>
                    @endcan
                    @can("supprimer_ecran_" . $ecran->id)
                    <button class="btn btn-outline-danger" title="Supprimer"
                      onclick="deleteActivite(@js($t->codeActivite))"><i class="bi bi-trash"></i></button>
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

{{-- MODALE ALERT --}}
<div class="modal fade" id="alertModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h6 class="modal-title">Information</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body"><div id="alertMessage"></div></div>
      <div class="modal-footer py-2">
        <button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>

@endcan
@endisset
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.full.min.js"></script>
<script>
  // Horloge
  setInterval(()=>{ const el=document.getElementById('date-now'); if(el) el.textContent=new Date().toLocaleString(); },1000);

  // Select2
  $(function(){
    $('#code_projet,#type_travaux_id,#edit_type_travaux_id').select2({width:'resolve', allowClear:true, placeholder:'— Sélectionner —'});
    if (typeof initDataTable === 'function') {
      initDataTable('{{ auth()->user()->acteur?->libelle_court }} {{ auth()->user()->acteur?->libelle_long }}','table','Liste des activités connexes');
    }
  });

  // Alert helper
  function showAlert(msg){ document.getElementById('alertMessage').textContent = msg; new bootstrap.Modal('#alertModal').show(); }

  // Formatage nombres (espaces)
  function formatNumber(input){
    let v = input.value.replace(/[^\d]/g,'');
    input.value = v.replace(/\B(?=(\d{3})+(?!\d))/g,' ');
  }

  // Dates cohérentes
  ['date_fin_previsionnelle','edit_date_fin_previsionnelle'].forEach(id=>{
    const fin=document.getElementById(id);
    const deb=document.getElementById(id.includes('edit_')?'edit_date_debut_previsionnelle':'date_debut_previsionnelle');
    if(fin&&deb){ fin.addEventListener('change',()=>{ if(new Date(fin.value)<new Date(deb.value)){ showAlert('La date de fin ne peut précéder la date de début.'); fin.value=deb.value; } }); }
  });
  ['date_fin_effective','edit_date_fin_effective'].forEach(id=>{
    const fin=document.getElementById(id);
    const deb=document.getElementById(id.includes('edit_')?'edit_date_debut_effective':'date_debut_effective');
    if(fin&&deb){ fin.addEventListener('change',()=>{ if(deb.value && new Date(fin.value)<new Date(deb.value)){ showAlert('La date de fin effective ne peut précéder la date de début effective.'); fin.value=deb.value; } }); }
  });

  // Edit
  function formatNumberForDisplay(v){ const s=(v??'').toString().replace(/\s+/g,''); return s.replace(/\B(?=(\d{3})+(?!\d))/g,' '); }
  window.editActivite = function(code, code_projet, type_id, cout, d1, d2, d3, d4, com){
    document.getElementById('addForm').classList.add('d-none');
    const box = document.getElementById('editFormContainer'); box.classList.remove('d-none');

    document.getElementById('edit_codeActivite').value = code;
    document.getElementById('edit_code_projet').value  = code_projet;
    document.getElementById('edit_type_travaux_id').value = type_id;
    $('#edit_type_travaux_id').trigger('change');

    document.getElementById('edit_cout_projet').value = formatNumberForDisplay(cout ?? 0);
    document.getElementById('edit_date_debut_previsionnelle').value = d1 ?? '';
    document.getElementById('edit_date_fin_previsionnelle').value   = d2 ?? '';
    document.getElementById('edit_date_debut_effective').value      = d3 ?? '';
    document.getElementById('edit_date_fin_effective').value        = d4 ?? '';
    document.getElementById('edit_commentaire').value               = com ?? '';

    document.getElementById('formAction').action = "{{ url('/') }}/activite/" + encodeURIComponent(code);
    window.scrollTo({top:0, behavior:'smooth'});
  };

  window.cancelEdit = function(){
    document.getElementById('formAction').reset();
    document.getElementById('editFormContainer').classList.add('d-none');
    document.getElementById('addForm').classList.remove('d-none');
  };

  // Delete JSON
  window.deleteActivite = async function(code){
    if(!confirm('Supprimer cette activité ?')) return;
    try{
      const res = await fetch("{{ route('travaux_connexes.destroy', ':id') }}".replace(':id', encodeURIComponent(code)), {
        method:'DELETE',
        headers:{ 'X-CSRF-TOKEN': "{{ csrf_token() }}", 'Accept':'application/json' }
      });
      const data = await res.json().catch(()=> ({}));
      if(!res.ok || data.ok === false){ showAlert(data.message || 'Erreur lors de la suppression.'); return; }
      showAlert(data.message || 'Supprimé.');
      setTimeout(()=> location.reload(), 700);
    }catch(e){ console.error(e); showAlert('Erreur réseau.'); }
  };
  $('#addForm').off('submit').on('submit', async function (e) {
    e.preventDefault();
    const fd = new FormData(this);

    try {
        const res = await fetch(this.action, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}", 'Accept': 'application/json' },
            body: fd
        });

        const data = await res.json().catch(()=>({}));
        if (!res.ok || data.ok === false) {
            alert(data.message || 'Erreur lors de l’enregistrement.');
            return;
        }
        alert(data.message || 'Opération réussie.');
        location.reload();
    } catch (err) {
        alert("Erreur réseau.");
    }
});

</script>
@endsection
