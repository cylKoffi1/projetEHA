@extends('layouts.app')

@section('content')
<div class="container py-3">
  <div class="d-flex align-items-center mb-3">
    <h3 class="me-auto">Tableau de bord des approbations</h3>
    <a href="{{ route('workflows.ui') }}" class="btn btn-outline-secondary">Workflows</a>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <div class="d-flex gap-2 mb-2">
        <button id="btn-approve-selected" class="btn btn-success btn-sm" disabled>Approuver sélection</button>
        <button id="btn-reject-selected"  class="btn btn-danger btn-sm"  disabled>Rejeter sélection</button>
        <button id="btn-delegate-selected" class="btn btn-outline-primary btn-sm" disabled>Déléguer sélection</button>
        <div class="ms-auto">
          <input id="filter" class="form-control form-control-sm" placeholder="Filtrer… (module, ID, workflow)">
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-sm align-middle" id="tb-approvals">
          <thead>
            <tr>
              <th style="width:28px">
                <input type="checkbox" id="chk-all">
              </th>
              <th>Objet</th>
              <th>Workflow</th>
              <th>Étape</th>
              <th>Doivent valider AVANT</th>
              <th>Doivent valider APRÈS</th>
              <th>Statut</th>
              <th style="width:260px">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($rows as $r)
              <tr data-row='@json($r)'>
                <td><input type="checkbox" class="chk-row"></td>
                <td>
                  <div class="fw-medium">{{ $r['module'] }} • {{ $r['type'] }}</div>
                  <div class="text-muted small">ID: {{ $r['target_id'] }}</div>
                </td>
                <td>
                  <div>{{ $r['workflow_name'] }}</div>
                  <div class="text-muted small">v{{ $r['version'] ?? '—' }}</div>
                </td>
                <td>Étape {{ $r['step_pos'] }}</td>
                <td class="small">
                  @if(empty($r['before'])) <span class="text-muted">—</span>
                  @else {{ implode(', ', $r['before']) }} @endif
                </td>
                <td class="small">
                  @if(empty($r['after'])) <span class="text-muted">—</span>
                  @else {{ implode(', ', $r['after']) }} @endif
                </td>
                <td>
                  @switch($r['status_code'])
                    @case('EN_COURS') <span class="badge bg-warning text-dark">En cours</span> @break
                    @case('PENDING')  <span class="badge bg-secondary">En attente</span> @break
                    @default          <span class="badge bg-light text-muted">{{ $r['status_code'] }}</span>
                  @endswitch
                </td>
                <td>
                  <div class="d-flex gap-2">
                    <button class="btn btn-success btn-sm btn-approve">Approuver</button>
                    <button class="btn btn-danger btn-sm btn-reject">Rejeter</button>
                    <button class="btn btn-outline-primary btn-sm btn-delegate">Déléguer</button>
                  </div>
                </td>
              </tr>
            @empty
              <tr><td colspan="8" class="text-center text-muted">Aucune approbation en attente</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="small text-muted mt-2">
        Astuce : sélectionne plusieurs lignes pour approuver / rejeter en masse.
      </div>
    </div>
  </div>
</div>

{{-- Modal délégation --}}
<div class="modal fade" id="dlgDelegate" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title">Déléguer l’approbation</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
    </div>
    <div class="modal-body">
      <label class="form-label">Code acteur destinataire</label>
      <input id="delegate-to" class="form-control" placeholder="ex: ACT123">
      <div class="form-text">Le code doit correspondre à un acteur valide.</div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
      <button class="btn btn-primary" id="btn-do-delegate">Déléguer</button>
    </div>
  </div></div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  const CSRF = '{{ csrf_token() }}';
  const actUrl = (id) => `{{ url('/approbations/steps') }}/${id}/act`;

  const $tbl   = document.querySelector('#tb-approvals tbody');
  const $chkAll= document.getElementById('chk-all');
  const $filter= document.getElementById('filter');

  /** Helpers **/
  const selectedRows = () => [...$tbl.querySelectorAll('tr')].filter(tr => tr.querySelector('.chk-row')?.checked);
  const rowData = (tr) => JSON.parse(tr.dataset.row || '{}');

  function setBulkButtonsState(){
    const has = selectedRows().length > 0;
    document.getElementById('btn-approve-selected').disabled = !has;
    document.getElementById('btn-reject-selected').disabled  = !has;
    document.getElementById('btn-delegate-selected').disabled= !has;
  }

  /** Filtre plein texte **/
  $filter.addEventListener('input', () => {
    const q = ($filter.value || '').toLowerCase();
    [...$tbl.rows].forEach(tr => {
      const d = rowData(tr);
      const hay = `${d.module} ${d.type} ${d.target_id} ${d.workflow_name}`.toLowerCase();
      tr.style.display = hay.includes(q) ? '' : 'none';
    });
  });

  /** Sélections **/
  $chkAll.addEventListener('change', () => {
    const visibleRows = [...$tbl.rows].filter(tr => tr.style.display !== 'none');
    visibleRows.forEach(tr => tr.querySelector('.chk-row').checked = $chkAll.checked);
    setBulkButtonsState();
  });
  $tbl.addEventListener('change', (e) => {
    if (e.target.classList.contains('chk-row')) setBulkButtonsState();
  });

  /** Actions unitaires **/
  $tbl.addEventListener('click', async (e) => {
    const tr = e.target.closest('tr');
    if (!tr) return;
    const d = rowData(tr);

    if (e.target.classList.contains('btn-approve')) {
      await doAct(d.step_id, 'APPROUVER');
      tr.remove();
    }
    if (e.target.classList.contains('btn-reject')) {
      await doAct(d.step_id, 'REJETER');
      tr.remove();
    }
    if (e.target.classList.contains('btn-delegate')) {
      openDelegate([d.step_id]);
    }
  });

  /** Actions en masse **/
  document.getElementById('btn-approve-selected').addEventListener('click', async () => {
    const ids = selectedRows().map(tr => rowData(tr).step_id);
    for (const id of ids) await doAct(id, 'APPROUVER');
    selectedRows().forEach(tr => tr.remove());
    setBulkButtonsState();
  });
  document.getElementById('btn-reject-selected').addEventListener('click', async () => {
    const ids = selectedRows().map(tr => rowData(tr).step_id);
    for (const id of ids) await doAct(id, 'REJETER');
    selectedRows().forEach(tr => tr.remove());
    setBulkButtonsState();
  });
  document.getElementById('btn-delegate-selected').addEventListener('click', () => {
    const ids = selectedRows().map(tr => rowData(tr).step_id);
    openDelegate(ids);
  });

  /** Délégation **/
  let toDelegate = [];
  function openDelegate(stepIds){
    toDelegate = stepIds;
    document.getElementById('delegate-to').value = '';
    new bootstrap.Modal(document.getElementById('dlgDelegate')).show();
  }
  document.getElementById('btn-do-delegate').addEventListener('click', async () => {
    const code = (document.getElementById('delegate-to').value || '').trim();
    if (!code) return alert('Saisis un code acteur destinataire');
    for (const id of toDelegate) {
      await doAct(id, 'DELEGUER', { delegate_to: code });
    }
    // on ne retire pas la ligne : elle reste à l’écran jusqu’à rafraîchissement
    bootstrap.Modal.getInstance(document.getElementById('dlgDelegate')).hide();
  });

  /** Appel API act() **/
  async function doAct(stepId, actionCode, meta=null){
    try{
      const res = await fetch(actUrl(stepId), {
        method: 'POST',
        headers: {
          'Content-Type':'application/json',
          'X-CSRF-TOKEN': CSRF,
          'Accept':'application/json'
        },
        body: JSON.stringify({ action_code: actionCode, meta })
      });
      const data = await res.json();
      if (!res.ok) throw new Error(data.error || data.message || 'Erreur');
    }catch(e){
      alert(e.message);
    }
  }
})();
</script>
@endpush
