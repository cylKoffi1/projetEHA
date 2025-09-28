@extends('layouts.app')
@section('content')
<div class="container py-3">
  <div class="d-flex align-items-center mb-3">
    <h3 class="me-auto">Instance d’approbation #{{ $instanceId }}</h3>
    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Retour</a>
  </div>

  <div id="inst"></div>

  <div class="card mt-3">
    <div class="card-body">
      <h5>Action sur une étape</h5>
      <div class="row g-2">
        <div class="col-md-3">
          <label class="form-label">Étape (instance)</label>
          <select id="si-id" class="form-select"></select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Action</label>
          <select id="act-code" class="form-select">
            <option value="APPROUVER">APPROUVER</option>
            <option value="REJETER">REJETER</option>
            <option value="DELEGUER">DELEGUER</option>
            <option value="COMMENTER">COMMENTER</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Déléguer à (code acteur)</label>
          <input id="delegate-to" class="form-control" placeholder="AC002 (si DELEGUER)">
        </div>
        <div class="col-md-12">
          <label class="form-label">Commentaire</label>
          <textarea id="act-comment" class="form-control" rows="2"></textarea>
        </div>
        <div class="col-md-12">
          <button id="btn-act" class="btn btn-primary mt-2">Envoyer l’action</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
const INSTANCE_ID = {{ (int)$instanceId }};

async function loadInstance() {
  const res = await fetch(`{{ url('/approbations') }}/${INSTANCE_ID}`, {headers:{'Accept':'application/json'}});
  const inst = await res.json();

  // Header
  $('#inst').html(`
    <div class="card">
      <div class="card-body">
        <div><strong>Workflow:</strong> ${inst.version.workflow.code} — ${inst.version.workflow.nom}</div>
        <div><strong>Module:</strong> ${inst.module_code} | <strong>Objet:</strong> ${inst.type_cible}#${inst.id_cible}</div>
        <div><strong>Statut instance:</strong> ${inst.statut_id}</div>
      </div>
    </div>
  `);

  // Step select
  const $sel = $('#si-id').empty();
  (inst.etapes||[]).forEach(s => {
    $sel.append(`<option value="${s.id}">Step#${s.id} — etape:${s.etape_workflow_id} — statut:${s.statut_id} — approvals:${s.nombre_approbations}/${s.quorum_requis}</option>`);
  });
}

$('#btn-act').on('click', async function(){
  const sid = $('#si-id').val();
  const code = $('#act-code').val();
  const meta = {};
  if (code === 'DELEGUER') meta.delegate_to = $('#delegate-to').val().trim();

  const res = await fetch(`{{ url('/approbations/etapes') }}/${sid}/act`, {
    method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},
    body: JSON.stringify({ action_code: code, commentaire: $('#act-comment').val(), meta })
  });
  const data = await res.json();
  if (!res.ok) return alert((data.message||data.error||'Erreur') + '\n' + JSON.stringify(data));
  alert('Action envoyée.');
  await loadInstance();
});

loadInstance();
</script>
@endsection
