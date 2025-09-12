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
              <li class="breadcrumb-item"><a href="">Décaissements bailleurs</a></li>
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

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-header"><strong>Nouveau / Éditer un décaissement</strong></div>
        <div class="card-body">
            <form id="decForm" method="POST" action="{{ route('gf.decaissements.store') }}">
                @csrf
                <input type="hidden" name="_method" value="POST" id="formMethod">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Projet *</label>
                        <select name="code_projet" id="code_projet" class="form-select" required>
                            <option value="">— Sélectionnez —</option>
                            @foreach($projets as $p)
                                <option value="{{ $p->code_projet }}">{{ $p->code_projet }}</option>
                            @endforeach
                        </select>
                        @error('code_projet') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Financement (optionnel)</label>
                        <select name="financer_id" id="financer_id" class="form-select">
                            <option value="">— Aucun —</option>
                        </select>
                        <small class="text-muted">Sélectionner un engagement pour verrouiller le bailleur et la devise.</small>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Bailleur *</label>
                        <select name="code_acteur" id="code_acteur" class="form-select" required readonly>
                            <option value="">— Sélectionnez —</option>
                            {{-- Optionnellement, on peut précharger quelques acteurs courants ici --}}
                        </select>
                        @error('code_acteur') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Devise</label>
                        <input type="text" name="devise" id="devise" class="form-control" placeholder="XOF, USD..." readonly>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Montant *</label>
                        <input type="number" step="0.01" min="0.01" name="montant" class="form-control" required>
                        @error('montant') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Référence</label>
                        <input type="text" name="reference" class="form-control" placeholder="Ref. ou note interne">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Tranche N°</label>
                        <input type="number" name="tranche_no" class="form-control" min="1">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Statut *</label>
                        <select name="statut_id" class="form-select" required>
                            @foreach($statuts as $s)
                                <option value="{{ $s->id }}" @selected($s->code==='demandee')>{{ $s->libelle }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Demandé le</label>
                        <input type="date" name="date_demande" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Validé le</label>
                        <input type="date" name="date_validation" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Décaissé le</label>
                        <input type="date" name="date_decaissement" class="form-control">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Commentaire</label>
                        <textarea name="commentaire" class="form-control" rows="2"></textarea>
                    </div>
                </div>

                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-primary" id="submitBtn">Enregistrer</button>
                    <button type="button" class="btn btn-secondary d-none" id="cancelEdit">Annuler édition</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Liste --}}
    <div class="card">
        <div class="card-header"><strong>Décaissements enregistrés</strong></div>
        <div class="card-body table-responsive">
            <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="tableDecs">
                <thead>
                    <tr>
                        <th>Projet</th>
                        <th>Bailleur</th>
                        <th>Réf / Tranche</th>
                        <th>Montant</th>
                        <th>Dates</th>
                        <th>Statut</th>
                        <th style="width:110px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($decaissements as $d)
                        @php
                            $bailleurNom = trim(($d->bailleur?->libelle_court ?? '').' '.($d->bailleur?->libelle_long ?? ''));
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $d->code_projet }}</div>
                                <small class="text-muted">{{ $d->projet?->libelle_projet }}</small>
                            </td>
                            <td>{{ $bailleurNom ?: '—' }}</td>
                            <td>
                                {{ $d->reference ?? '—' }}<br>
                                <small class="text-muted">Tranche {{ $d->tranche_no ?? '—' }}</small>
                            </td>
                            <td>{{ number_format($d->montant, 2, ',', ' ') }} {{ $d->devise }}</td>
                            <td>
                                <small>Dem.: {{ $d->date_demande?->format('d/m/Y') ?? '—' }}</small><br>
                                <small>Val.: {{ $d->date_validation?->format('d/m/Y') ?? '—' }}</small><br>
                                <small>Déc.: {{ $d->date_decaissement?->format('d/m/Y') ?? '—' }}</small>
                            </td>
                            <td><span class="badge bg-secondary">{{ $d->statut?->libelle }}</span></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary btn-edit"
                                        data-id="{{ $d->id }}"
                                        data-code_projet="{{ $d->code_projet }}"
                                        data-financer_id="{{ $d->financer_id }}"
                                        data-code_acteur="{{ $d->code_acteur }}"
                                        data-devise="{{ $d->devise }}"
                                        data-reference="{{ $d->reference }}"
                                        data-tranche_no="{{ $d->tranche_no }}"
                                        data-montant="{{ $d->montant }}"
                                        data-statut_id="{{ $d->statut_id }}"
                                        data-date_demande="{{ optional($d->date_demande)->format('Y-m-d') }}"
                                        data-date_validation="{{ optional($d->date_validation)->format('Y-m-d') }}"
                                        data-date_decaissement="{{ optional($d->date_decaissement)->format('Y-m-d') }}"
                                        data-commentaire="{{ $d->commentaire }}">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button type="button"
                                            class="btn btn-outline-danger btn-sm"
                                            onclick="confirmDelete('{{ route('gf.decaissements.destroy', $d->id) }}', () => location.reload())">
                                    <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-2">
                {{ $decaissements->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    const $form = $('#decForm');
    const $proj = $('#code_projet');
    const $fin  = $('#financer_id');
    const $act  = $('#code_acteur');
    const $dev  = $('#devise');
    const $submitBtn = $('#submitBtn');
    const $cancelEdit = $('#cancelEdit');

    // petit helper alert
    function toast(icon, title) {
        Swal.fire({icon, title, timer: 2000, showConfirmButton: false});
    }

    // 1) Chargement financements + bailleurs quand on choisit un projet
    $proj.on('change', function(){
        const code = this.value;
        $fin.html('<option value="">— Aucun —</option>');
        $act.html('<option value="">— Sélectionnez —</option>');
        if(!code) return;

        fetch(`{{ route('gf.decaissements.financementsByProjet','__CODE__') }}`.replace('__CODE__', code))
            .then(r => r.json())
            .then(list => {
                // Alimenter "Financement" avec MONTANT uniquement
                list.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.id;
                    opt.text  = `${item.montant_fmt} ${item.devise}`; // <- montant seul (avec devise)
                    // on garde des meta pour la suite
                    opt.dataset.bailleurId    = item.bailleur_id || '';
                    opt.dataset.bailleurLabel = item.bailleur_label || '';
                    opt.dataset.devise        = item.devise || '';
                    $fin.append(opt);
                });

                // Alimenter "Bailleur" avec LIBELLE (avec « Ministère de … » si dispo)
                const seen = new Set();
                list.forEach(item => {
                    if (!item.bailleur_id || seen.has(item.bailleur_id)) return;
                    seen.add(item.bailleur_id);
                    $act.append(new Option(item.bailleur_label, item.bailleur_id));
                });
            })
            .catch(() => toast('error','Erreur de chargement'));
    });

    // 2) Quand on choisit un financement => sélectionner bailleur + devise
$fin.on('change', function(){
    const opt = this.selectedOptions[0];
    if(!opt) {
        $act.removeClass('locked').val('');
        return;
    }
    const bailleurId    = opt.dataset.bailleurId;
    const bailleurLabel = opt.dataset.bailleurLabel;
    const devise        = opt.dataset.devise;

    if (bailleurId) {
        if (!$act.find(`option[value="${bailleurId}"]`).length) {
            $act.append(new Option(bailleurLabel || bailleurId, bailleurId));
        }
        $act.val(bailleurId).addClass('locked'); // <- pas disabled !
    } else {
        $act.removeClass('locked').val('');
    }
    if (devise) $dev.val(devise);
});

// Quand on annule l’édition ou change de projet, penser à déverrouiller :
$cancelEdit.on('click', function(){
    $act.removeClass('locked').val('');
});
$proj.on('change', function(){
    $act.removeClass('locked').val('');
});


    // 3) Soumission AJAX (POST ou PUT selon l’action du form)
    $form.on('submit', function(e){
        e.preventDefault();
        const action = $form.attr('action');
        const method = ($('#formMethod').val() || 'POST').toUpperCase();

        $submitBtn.prop('disabled', true);

        $.ajax({
            url: action,
            type: method,
            data: $form.serialize(),
            success: function(res){
                if (res.ok) {
                    toast('success', res.message || 'Opération réussie.');
                    setTimeout(()=> location.reload(), 700);
                } else {
                    toast('warning', res.message || 'Vérifiez les champs.');
                }
            },
            error: function(xhr){
                const msg = xhr.responseJSON?.message || 'Erreur serveur';
                let details = '';
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    details = Object.values(xhr.responseJSON.errors).map(arr=>arr.join('<br>')).join('<br>');
                }
                Swal.fire({icon:'error',title:msg, html:details});
            },
            complete: function(){ $submitBtn.prop('disabled', false); }
        });
    });

    // 4) Bouton Éditer (inchangé, mais le submit est maintenant AJAX)
    $('.btn-edit').on('click', function(){
        const d = this.dataset;
        $('#decForm').attr('action', '{{ route("gf.decaissements.update", "__ID__") }}'.replace('__ID__', d.id));
        $('#formMethod').val('PUT');
        $submitBtn.text('Mettre à jour');
        $cancelEdit.removeClass('d-none');

        // Remplissage
        $proj.val(d.code_projet).trigger('change');

        setTimeout(() => {
            if (d.financer_id) $fin.val(d.financer_id).trigger('change');
        }, 300);

        if (d.code_acteur) {
            if (!$act.find(`option[value="${d.code_acteur}"]`).length) {
                $act.append(new Option(d.code_acteur, d.code_acteur));
            }
            $act.val(d.code_acteur);
        }
        $dev.val(d.devise || '');
        $('input[name="reference"]').val(d.reference || '');
        $('input[name="tranche_no"]').val(d.tranche_no || '');
        $('input[name="montant"]').val(d.montant || '');
        $('select[name="statut_id"]').val(d.statut_id || '');
        $('input[name="date_demande"]').val(d.date_demande || '');
        $('input[name="date_validation"]').val(d.date_validation || '');
        $('input[name="date_decaissement"]').val(d.date_decaissement || '');
        $('textarea[name="commentaire"]').val(d.commentaire || '');
    });

    // 5) Annuler édition → repasse en mode POST
    $cancelEdit.on('click', function(){
        $('#decForm').attr('action', '{{ route("gf.decaissements.store") }}');
        $('#formMethod').val('POST');
        $submitBtn.text('Enregistrer');
        $(this).addClass('d-none');
        $('#decForm')[0].reset();
        $act.prop('disabled', false).html('<option value="">— Sélectionnez —</option>');
        $fin.html('<option value="">— Aucun —</option>');
    });

})();
$(document).ready(function() {
        if (typeof initDataTable === 'function') {
            initDataTable('{{ auth()->user()->acteur?->libelle_court }} {{ auth()->user()->acteur?->libelle_long }}', 'tableDecs', 'Liste des décaissements bailleur');
        }
    });
</script>

@endsection
