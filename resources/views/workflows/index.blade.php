@extends('layouts.app')

@section('content')
<div class="container py-3">
  <div class="d-flex align-items-center mb-3">
    <h3 class="me-auto">Workflows d’approbation</h3>
    {{-- <a href="{{ route('workflows.index') }}" class="btn btn-outline-secondary me-2">Rafraîchir</a> --}}
    <a href="{{ route('workflows.createForm') }}" class="btn btn-primary">Nouveau workflow</a>
  </div>

  <div class="card">
    <div class="card-body">
      <table class="table table-sm align-middle" id="wf-table">
        <thead>
          <tr>
            <th>Code</th><th>Libelle</th><th>Pays</th><th>Groupe</th><th>Versions</th><th></th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<script>
$(async function() {
  const res = await fetch(`{{ route('workflows.index') }}`, {headers: {'Accept':'application/json'}});
  const data = await res.json();
  const rows = data.data || data; // paginate ou pas
  const $tb = $('#wf-table tbody').empty();
  rows.forEach(w => {
    const vers = (w.versions||[]).map(v=>`v${v.numero_version}${v.publie?' ✅':''}`).join(', ');
    $tb.append(`
      <tr>
        <td>${w.code}</td>
        <td>${w.nom}</td>
        <td>${w.code_pays}</td>
        <td>${w.groupe_projet_id ?? ''}</td>
        <td>${vers}</td>
        <td class="text-end">
          <a class="btn btn-sm btn-outline-primary" href="{{ route('workflows.designForm', ['id' => '__ID__']) }}">Éditer</a>
          <a class="btn btn-sm btn-outline-secondary" href="{{ route('workflows.bindingsView', ['id' => '__ID__']) }}">Liaisons</a>
        </td>
      </tr>
    `.replaceAll('__ID__', w.id));
  });
});
</script>
@endsection
