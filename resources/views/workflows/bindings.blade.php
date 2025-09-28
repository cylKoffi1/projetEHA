{{-- resources/views/workflows/bindings.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container py-3">
  <div class="d-flex align-items-center mb-3">
    <h3 class="me-auto">Liaisons du workflow #{{ $workflowId }}</h3>
    <a href="{{ route('workflows.ui') }}" class="btn btn-outline-secondary">Retour</a>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <div class="row g-3">
        {{-- Version publiée --}}
        <div class="col-md-3">
          <label class="form-label">Version publiée <span class="text-danger">*</span></label>
          <select id="b-num" class="form-select">
            <option value="">— Sélectionner —</option>
            @forelse($publishedVersions as $v)
              <option value="{{ $v->numero_version }}">v{{ $v->numero_version }}</option>
            @empty
              <option value="">(Aucune version publiée)</option>
            @endforelse
          </select>
        </div>

        {{-- Liaisons existantes (pour pré-remplir) --}}
        <div class="col-md-9">
          <label class="form-label">Liaisons existantes</label>
          <select id="b-existing" class="form-select">
            <option value="">— Sélectionner une liaison —</option>
            @foreach($bindings as $b)
              @php
                $ver = optional($b->version)->numero_version;
                $label = ($ver ? "v$ver" : "ver#{$b->version_workflow_id}")
                       ." • {$b->module_code} • {$b->type_cible}"
                       .($b->id_cible ? " • ID:{$b->id_cible}" : " • (par type)")
                       .($b->par_defaut ? " • défaut" : "");
              @endphp
              <option
                value="{{ $b->id }}"
                data-num="{{ $ver }}"
                data-module="{{ $b->module_code }}"
                data-type="{{ $b->type_cible }}"
                data-idc="{{ $b->id_cible }}"
                data-default="{{ (int)$b->par_defaut }}"
              >{{ $label }}</option>
            @endforeach
          </select>
          <div class="form-text">Choisir une liaison remplit le formulaire ci-dessous.</div>
        </div>
      </div>

      <div class="row g-3 mt-1">
        <div class="col-md-4">
          <label class="form-label">Module <span class="text-danger">*</span></label>
          <input id="b-module" class="form-control" placeholder="ex : ETUDE_PROJET">
        </div>
        <div class="col-md-4">
          <label class="form-label">Type d’objet <span class="text-danger">*</span></label>
          <input id="b-type" class="form-control" placeholder="ex : etude_projet">
        </div>
        <div class="col-md-4">
          <label class="form-label">ID objet (optionnel)</label>
          <input id="b-id" class="form-control" placeholder="ex : 123 (vide = par type)">
        </div>
      </div>

      <div class="d-flex align-items-center mt-3">
        <div class="form-check">
          <input id="b-default" type="checkbox" class="form-check-input">
          <label for="b-default" class="form-check-label">Définir comme “par défaut”</label>
        </div>
        <button id="btn-bind" class="btn btn-primary ms-auto">Enregistrer la liaison</button>
      </div>
    </div>
  </div>

  {{-- Tableau des liaisons --}}
  <div class="card">
    <div class="card-body">
      <h5 class="mb-2">Liaisons existantes</h5>
      <div class="table-responsive">
        <table class="table table-sm align-middle">
          <thead>
            <tr>
              <th>#</th>
              <th>Version</th>
              <th>Module</th>
              <th>Type</th>
              <th>ID cible</th>
              <th>Par défaut</th>
              <th>Créée le</th>
            </tr>
          </thead>
          <tbody>
            @forelse($bindings as $idx => $b)
              <tr>
                <td>{{ $idx+1 }}</td>
                <td>{{ optional($b->version)->numero_version ? 'v'.optional($b->version)->numero_version : '#'.$b->version_workflow_id }}</td>
                <td>{{ $b->module_code }}</td>
                <td>{{ $b->type_cible }}</td>
                <td>{{ $b->id_cible }}</td>
                <td>{{ $b->par_defaut ? '✅' : '' }}</td>
                <td>{{ $b->created_at }}</td>
              </tr>
            @empty
              <tr><td colspan="7" class="text-muted text-center">Aucune liaison</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="small text-muted">Une liaison par ID a priorité sur la liaison “par type”.</div>
    </div>
  </div>
</div>

{{-- Modale feedback --}}
<div class="modal fade" id="feedbackModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title" id="feedbackTitle">Information</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
    </div>
    <div class="modal-body" id="feedbackBody">...</div>
    <div class="modal-footer">
      <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
    </div>
  </div></div>
</div>
<script>
(function(){
  const CSRF = '{{ csrf_token() }}';
  const bindUrl = "{{ url('/workflows/'.$workflowId.'/bind') }}";


  const $vers   = document.getElementById('b-num');
  const $exist  = document.getElementById('b-existing');
  const $module = document.getElementById('b-module');
  const $type   = document.getElementById('b-type');
  const $idc    = document.getElementById('b-id');
  const $def    = document.getElementById('b-default');

  // Pré-remplissage depuis le <select> “Liaisons existantes”
  $exist?.addEventListener('change', function(){
    const opt = this.selectedOptions[0];
    if (!opt || !opt.dataset) return;
    const num = opt.dataset.num || '';
    $vers.value   = num ? String(num) : '';
    $module.value = opt.dataset.module || '';
    $type.value   = opt.dataset.type || '';
    $idc.value    = opt.dataset.idc || '';
    $def.checked  = opt.dataset.default === '1';
  });

  // Enregistrer / MAJ liaison (upsert)
  document.getElementById('btn-bind').addEventListener('click', async () => {
    const body = {
      numero_version: parseInt($vers.value || '0',10),
      module_code: ($module.value||'').trim(),
      type_cible:  ($type.value||'').trim(),
      id_cible:    ($idc.value||'').trim() || null,
      par_defaut:  !!$def.checked
    };
    if (!body.numero_version) return alert('Choisis une version publiée', 'warning');
    if (!body.module_code)    return alert('Le module est requis', 'warning');
    if (!body.type_cible)     return alert('Le type est requis', 'warning');
    if (body.id_cible && body.par_defaut) return alert('“Par défaut” seulement si ID vide', 'warning');

    try{
      const res  = await fetch(bindUrl, {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
        body: JSON.stringify(body)
      });
      const data = await res.json();
      if (!res.ok) throw new Error(data.message || data.error || 'Erreur');
      alert('Liaison enregistrée. Rafraîchis la page pour voir la liste mise à jour.');
    }catch(e){ alert( e.message, 'error'); }
  });


})();
</script>

@endsection
