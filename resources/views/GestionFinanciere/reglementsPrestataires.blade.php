@extends('layouts.app')

@section('content')

<div class="page-heading">
    <div class="page-title">
      <div class="row">
        <div class="col-sm-12">
          <li class="breadcrumb-item" style="list-style:none; text-align:right; padding:5px;">
            <span id="date-now" style="color:#34495E;"></span>
          </li>
        </div>
      </div>
      <div class="row">
        <div class="col-12 col-md-6 order-md-1 order-last">
          <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Gestion financière</h3>
        </div>
        <div class="col-12 col-md-6 order-md-2 order-first">
          <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="">Règlements prestataires</a></li>
            </ol>
          </nav>
          <script>
            setInterval(function() {
              document.getElementById('date-now').textContent = new Date().toLocaleString();
            }, 1000);
          </script>
        </div>
      </div>
    </div>
</div>
<div class="container-fluid">
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))   <div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="card mb-4">
        <div class="card-header"><strong>Nouveau règlement / Éditer</strong></div>
        <div class="card-body">
            <form id="regForm" method="POST" action="{{ route('gf.reglements.store') }}">
                @csrf
                <input type="hidden" name="_method" value="POST" id="formMethod">

                <div class="row g-3">
                

                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">Projet</label>
                            <select name="code_projet" id="code_projet" class="form-select" required>
                                <option value="">— Sélectionnez —</option>
                                @foreach($projets as $p)
                                    <option value="{{ $p->code_projet }}">{{ $p->code_projet }} — {{ $p->libelle_projet }}</option>
                                @endforeach
                            </select>
                            @error('code_projet') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Prestataire *</label>
                            <select name="code_acteur" id="code_acteur" class="form-select" required>
                                <option value="">— Sélectionnez —</option>
                                @foreach($prestataires as $a)
                                    <option value="{{ $a->code_acteur }}">{{ trim(($a->libelle_court ?? '').' '.($a->libelle_long ?? '')) }}</option>
                                @endforeach
                            </select>
                            @error('code_acteur') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Mt. Facture</label>
                            <input type="number" step="0.01" min="0" name="montant_facture" id="montant_facture" class="form-control">
                        </div>

                        <div class="col-md-1">
                            <label class="form-label">Devise</label>
                            <input type="text" name="devise" class="form-control" placeholder="XOF">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Statut *</label>
                            <select name="statut_id" class="form-select" required>
                                @foreach($statuts as $s)
                                    <option value="{{ $s->id }}">{{ $s->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-7"></div>
                        <div class="col-md-2">
                            <label class="form-label">Date Facture</label>
                            <input type="date" name="date_facture" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Réf. Facture</label>
                            <input type="text" name="reference_facture" class="form-control">
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <label class="form-label">Date Règlement *</label>
                            <input type="date" name="date_reglement" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Tranche N°</label>
                            <input type="number" name="date_reglement" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Mode règlement *</label>
                            <select name="mode_id" class="form-select" required>
                                @foreach($modes as $m)
                                    <option value="{{ $m->id }}">{{ $m->libelle }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Montant réglé *</label>
                            <input type="number" step="0.01" min="0.01" name="montant_regle" id="montant_regle" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Solde *</label>
                            <input type="number" step="0.01" min="0.01" name="montant_regle" id="montant_regle" class="form-control" required>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Commentaire</label>
                        <textarea name="commentaire" class="form-control" rows="2"></textarea>
                    </div>
                </div>

                <div class="d-flex gap-3 justify-content-end mt-3">
                    <div class="text-end me-auto">
                        <small class="text-muted">Solde facture estimé : <span id="soldeOut">—</span></small>
                    </div>

                    <button type="submit" class="btn btn-primary" id="submitBtn">Enregistrer</button>
                    <button type="button" class="btn btn-secondary d-none" id="cancelEdit">Annuler édition</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Liste --}}
    <div class="card">
        <div class="card-header"><strong>Règlements enregistrés</strong></div>
        <div class="card-body table-responsive">
            <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="tableReglements">
                <thead>
                    <tr>
                        <th>Projet</th>
                        <th>Prestataire</th>
                        <th>Facture</th>
                        <th>Montants</th>
                        <th>Mode / Statut</th>
                        <th>Date règlt.</th>
                        <th style="width:110px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reglements as $r)
                        @php
                            $prest = trim(($r->prestataire?->libelle_court ?? '').' '.($r->prestataire?->libelle_long ?? ''));
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $r->code_projet }}</div>
                                <small class="text-muted">{{ $r->projet?->libelle_projet }}</small>
                            </td>
                            <td>{{ $prest ?: '—' }}</td>
                            <td>
                                {{ $r->reference_facture ?: '—' }}<br>
                                <small class="text-muted">{{ $r->date_facture?->format('d/m/Y') ?: '—' }}</small>
                            </td>
                            <td>
                                <div>Facture : <strong>{{ number_format($r->montant_facture ?? 0, 2, ',', ' ') }}</strong> {{ $r->devise }}</div>
                                <div>Réglé : <strong class="text-success">{{ number_format($r->montant_regle, 2, ',', ' ') }}</strong> {{ $r->devise }}</div>
                                @if(!is_null($r->solde))
                                    <div>Solde : <strong class="{{ $r->solde < 0 ? 'text-danger' : 'text-primary' }}">{{ number_format($r->solde, 2, ',', ' ') }}</strong> {{ $r->devise }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $r->mode?->libelle ?? '—' }}</span><br>
                                <span class="badge bg-info">{{ $r->statut?->libelle ?? '—' }}</span>
                            </td>
                            <td>{{ $r->date_reglement?->format('d/m/Y') }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary btn-edit"
                                        data-id="{{ $r->id }}"
                                        data-code_projet="{{ $r->code_projet }}"
                                        data-code_acteur="{{ $r->code_acteur }}"
                                        data-reference_facture="{{ $r->reference_facture }}"
                                        data-date_facture="{{ optional($r->date_facture)->format('Y-m-d') }}"
                                        data-montant_facture="{{ $r->montant_facture }}"
                                        data-montant_regle="{{ $r->montant_regle }}"
                                        data-devise="{{ $r->devise }}"
                                        data-mode_id="{{ $r->mode_id }}"
                                        data-statut_id="{{ $r->statut_id }}"
                                        data-date_reglement="{{ optional($r->date_reglement)->format('Y-m-d') }}"
                                        data-commentaire="{{ $r->commentaire }}">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button type="button"
                                            class="btn btn-outline-danger btn-sm"
                                            onclick="confirmDelete('{{ route('gf.reglements.destroy', $r->id) }}', () => location.reload())">
                                    <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-2">
                {{ $reglements->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    const fmt = n => (isNaN(n)?0:n).toLocaleString('fr-FR', {minimumFractionDigits:2, maximumFractionDigits:2});
    const $montantFacture = $('#montant_facture');
    const $montantRegle   = $('#montant_regle');
    const $soldeOut       = $('#soldeOut');

    function recalcSolde(){
        const f = parseFloat($montantFacture.val());
        const r = parseFloat($montantRegle.val());
        if (isNaN(f)) { $soldeOut.text('—'); return; }
        const s = f - (isNaN(r)?0:r);
        $soldeOut.text(fmt(s));
    }
    $montantFacture.on('input', recalcSolde);
    $montantRegle.on('input', recalcSolde);

    // Édition
    $('.btn-edit').on('click', function(){
        const d = this.dataset;
        $('#regForm').attr('action', '{{ route("gf.reglements.update","__ID__") }}'.replace('__ID__', d.id));
        $('#formMethod').val('PUT');
        $('#submitBtn').text('Mettre à jour');
        $('#cancelEdit').removeClass('d-none');

        $('select[name="code_projet"]').val(d.code_projet);
        $('select[name="code_acteur"]').val(d.code_acteur);
        $('select[name="mode_id"]').val(d.mode_id);
        $('select[name="statut_id"]').val(d.statut_id);

        $('input[name="reference_facture"]').val(d.reference_facture || '');
        $('input[name="date_facture"]').val(d.date_facture || '');
        $('input[name="montant_facture"]').val(d.montant_facture || '');
        $('input[name="montant_regle"]').val(d.montant_regle || '');
        $('input[name="devise"]').val(d.devise || '');
        $('input[name="date_reglement"]').val(d.date_reglement || '');
        $('textarea[name="commentaire"]').val(d.commentaire || '');

        recalcSolde();
    });

    // Annuler édition
    $('#cancelEdit').on('click', function(){
        $('#regForm').attr('action', '{{ route("gf.reglements.store") }}');
        $('#formMethod').val('POST');
        $('#submitBtn').text('Enregistrer');
        $(this).addClass('d-none');
        $('#regForm')[0].reset();
        $soldeOut.text('—');
    });

    // DataTable (si dispo)
    $(document).ready(function() {
        if (typeof initDataTable === 'function') {
            initDataTable('{{ auth()->user()->acteur?->libelle_court }} {{ auth()->user()->acteur?->libelle_long }}', 'tableReglements', 'Liste des règlements');
        }
    });
})();
</script>
<script>
(function(){
  const form = document.getElementById('regForm');
  const methodInput = document.getElementById('formMethod');

  form.addEventListener('submit', async function(e){
    e.preventDefault();

    const url    = form.getAttribute('action');
    const isPUT  = (methodInput.value || 'POST').toUpperCase() === 'PUT';
    const fd     = new FormData(form);

    // Toujours poster en POST et envoyer _method=PUT si édition
    if (isPUT && !fd.has('_method')) fd.append('_method','PUT');

    try {
      const res = await fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept':'application/json' },
        body: fd
      });

      const data = await res.json();

      if (!res.ok) {
        if (data?.errors) {
          // Agrège les erreurs de validation
          const lines = Object.values(data.errors).flat().map(s => `<li>${s}</li>`).join('');
          Swal.fire({ icon:'error', title:'Veuillez corriger les erreurs', html:`<ul style="text-align:left">${lines}</ul>` });
        } else {
          Swal.fire({ icon:'error', title:'Erreur', text: data?.error || 'Une erreur est survenue.' });
        }
        return;
      }

      if (data.ok) {
        Swal.fire({ icon:'success', title:data.message || 'Succès', timer:1600, showConfirmButton:false });
        // recharge la page pour rafraîchir la liste
        setTimeout(()=>window.location.reload(), 700);
      } else {
        Swal.fire({ icon:'warning', title:'Info', text:data?.error || 'Traitement non effectué.' });
      }
    } catch (err) {
      Swal.fire({ icon:'error', title:'Erreur réseau', text:String(err) });
    }
  });
})();
</script>

@endsection
