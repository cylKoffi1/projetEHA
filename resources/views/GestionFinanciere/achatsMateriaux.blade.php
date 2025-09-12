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
              <li class="breadcrumb-item"><a href="">Achat de matériaux</a></li>
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
        <div class="card-header"><strong>Nouvel achat / Éditer</strong></div>
        <div class="card-body">
            <form id="achatForm" method="POST" action="{{ route('gf.achats.store') }}">
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

                    <div class="col-md-4">
                        <label class="form-label">Fournisseur *</label>
                        <select name="code_acteur" id="code_acteur" class="form-select" required>
                            <option value="">— Sélectionnez —</option>
                            @foreach($fournisseurs as $f)
                                <option value="{{ $f->code_acteur }}">{{ trim(($f->libelle_court ?? '').' '.($f->libelle_long ?? '')) }}</option>
                            @endforeach
                        </select>
                        @error('code_acteur') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Réf. BC</label>
                        <input type="text" name="reference_bc" class="form-control" placeholder="Bon de commande">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Date commande *</label>
                        <input type="date" name="date_commande" class="form-control" required>
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

                    <div class="col-12">
                        <label class="form-label">Commentaire</label>
                        <textarea name="commentaire" class="form-control" rows="2"></textarea>
                    </div>
                </div>

                <hr>

                <h6 class="mb-2">Lignes de matériaux</h6>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="lignesTable">
                        <thead >
                            <tr>
                                <th style="width:26%">Libellé</th>
                                <th style="width:8%">Unité</th>
                                <th style="width:10%">Qté prévue</th>
                                <th style="width:10%">Qté reçue</th>
                                <th style="width:12%">PU</th>
                                <th style="width:8%">TVA %</th>
                                <th style="width:13%">Montant HT</th>
                                <th style="width:13%">Montant TTC</th>
                                <th style="width:6%"></th>
                            </tr>
                        </thead>
                        <tbody id="lignesBody">
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="9">
                                    <button class="btn btn-sm btn-outline-primary" type="button" id="addLine">
                                        <i class="bi bi-plus-circle"></i> Ajouter une ligne
                                    </button>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="row justify-content-end">
                    <div class="col-md-4">
                        <div class="card p-2">
                            <div class="d-flex justify-content-between"><span>Total HT</span><strong id="totalHT">0</strong></div>
                            <div class="d-flex justify-content-between"><span>TVA</span><strong id="totalTVA">0</strong></div>
                            <div class="d-flex justify-content-between"><span>Total TTC</span><strong id="totalTTC">0</strong></div>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-primary" id="submitBtn">Enregistrer</button>
                    <button type="button" class="btn btn-secondary d-none" id="cancelEdit">Annuler édition</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Liste des achats --}}
    <div class="card">
        <div class="card-header"><strong>Achats enregistrés</strong></div>
        <div class="card-body table-responsive">
            <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="tableAchats">
                <thead >
                    <tr>
                        <th>Projet</th>
                        <th>Fournisseur</th>
                        <th>Réf / Date</th> 
                        <th>Statut</th>
                        <th>Total TTC</th>
                        <th style="width:110px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($achats as $a)
                        @php
                            $fourn = trim(($a->fournisseur?->libelle_court ?? '').' '.($a->fournisseur?->libelle_long ?? ''));
                            $lines = $a->lignes->map(fn($l)=>[
                                'libelle_materiau'=>$l->libelle_materiau,
                                'unite'=>$l->unite,
                                'quantite_prevue'=>$l->quantite_prevue,
                                'quantite_recue'=>$l->quantite_recue,
                                'prix_unitaire'=>$l->prix_unitaire,
                                'tva'=>$l->tva,
                            ]);
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $a->code_projet }}</div>
                                <small class="text-muted">{{ $a->projet?->libelle_projet }}</small>
                            </td>
                            <td>{{ $fourn ?: '—' }}</td>
                            <td>
                                {{ $a->reference_bc ?? '—' }}<br>
                                <small class="text-muted">{{ $a->date_commande?->format('d/m/Y') ?? '—' }}</small>
                            </td>
                            <td><span class="badge bg-secondary">{{ $a->statut?->libelle ?? '—' }}</span></td>
                            <td>{{ number_format($a->total_ttc, 2, ',', ' ') }} {{ $a->devise }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary btn-edit"
                                        data-id="{{ $a->id }}"
                                        data-code_projet="{{ $a->code_projet }}"
                                        data-code_acteur="{{ $a->code_acteur }}"
                                        data-reference_bc="{{ $a->reference_bc }}"
                                        data-date_commande="{{ optional($a->date_commande)->format('Y-m-d') }}"
                                        data-devise="{{ $a->devise }}"
                                        data-statut_id="{{ $a->statut_id }}"
                                        data-commentaire="{{ $a->commentaire }}"
                                        data-lignes='@json($lines)'>
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button type="button"
                                            class="btn btn-outline-danger btn-sm"
                                            onclick="confirmDelete('{{ route('gf.achats.destroy', $a->id) }}', () => location.reload(), {
                                                title: 'Supprimer cet achat ?',
                                                text:  'Cette action est irréversible.',
                                                successMessage: 'Achat supprimé avec succès.'
                                            })">
                                    <i class="bi bi-trash"></i>
                                    </button>

                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-2">
                {{ $achats->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    const fmt = n => (isNaN(n)?0:n).toLocaleString('fr-FR', {minimumFractionDigits:2, maximumFractionDigits:2});

    const $body = $('#lignesBody');
    const tpl = () => `
        <tr>
            <td><input class="form-control" name="lignes[][libelle_materiau]" required></td>
            <td><input class="form-control" name="lignes[][unite]"></td>
            <td><input type="number" step="0.0001" min="0" class="form-control qtePrev" name="lignes[][quantite_prevue]" required></td>
            <td><input type="number" step="0.0001" min="0" class="form-control qteRec"  name="lignes[][quantite_recue]"></td>
            <td><input type="number" step="0.0001" min="0" class="form-control pu" name="lignes[][prix_unitaire]" required></td>
            <td><input type="number" step="0.01" min="0" max="100" class="form-control tva" name="lignes[][tva]" value="0"></td>
            <td class="ht text-end">0,00</td>
            <td class="ttc text-end">0,00</td>
            <td class="text-center">
                <button class="btn btn-sm btn-outline-danger delLine" type="button"><i class="bi bi-x"></i></button>
            </td>
        </tr>`;

    function recalc(){
        let totHT=0, totTVA=0, totTTC=0;
        $body.find('tr').each(function(){
            const q  = parseFloat($(this).find('.qtePrev').val()) || 0;
            const pu = parseFloat($(this).find('.pu').val()) || 0;
            const t  = parseFloat($(this).find('.tva').val()) || 0;

            const ht  = q*pu;
            const tva = ht*(t/100);
            const ttc = ht+tva;

            $(this).find('.ht').text(fmt(ht));
            $(this).find('.ttc').text(fmt(ttc));

            totHT += ht; totTVA += tva; totTTC += ttc;
        });
        $('#totalHT').text(fmt(totHT));
        $('#totalTVA').text(fmt(totTVA));
        $('#totalTTC').text(fmt(totTTC));
    }

    $('#addLine').on('click', ()=>{ $body.append(tpl()); });
    $body.on('click', '.delLine', function(){ $(this).closest('tr').remove(); recalc(); });
    $body.on('input', '.qtePrev,.pu,.tva', recalc);

    // une ligne par défaut
    $('#addLine').click();

    // ---- AJAX SUBMIT (Create / Update) ----
    $('#achatForm').on('submit', function(e){
        e.preventDefault();

        const url    = this.action;
        const token  = $('input[name="_token"]').val();
        const method = $('#formMethod').val() || 'POST';

        // build payload manuellement (plus propre pour les tableaux)
        const lignes = [];
        $('#lignesBody tr').each(function(){
            const l = {
                libelle_materiau: $(this).find('input[name$="[libelle_materiau]"]').val() || '',
                unite:            $(this).find('input[name$="[unite]"]').val() || null,
                quantite_prevue:  parseFloat($(this).find('input[name$="[quantite_prevue]"]').val() || 0),
                quantite_recue:   parseFloat($(this).find('input[name$="[quantite_recue]"]').val() || 0),
                prix_unitaire:    parseFloat($(this).find('input[name$="[prix_unitaire]"]').val() || 0),
                tva:              parseFloat($(this).find('input[name$="[tva]"]').val() || 0),
            };
            if (l.libelle_materiau) lignes.push(l);
        });

        const payload = {
            code_projet:   $('select[name="code_projet"]').val(),
            code_acteur:   $('select[name="code_acteur"]').val(),
            reference_bc:  $('input[name="reference_bc"]').val(),
            date_commande: $('input[name="date_commande"]').val(),
            devise:        $('input[name="devise"]').val(),
            statut_id:     $('select[name="statut_id"]').val(),
            commentaire:   $('textarea[name="commentaire"]').val(),
            lignes
        };

        // Pour PUT/PATCH via jQuery AJAX → on envoie POST + _method
        if (method !== 'POST') { payload._method = method; }

        $.ajax({
            url: url,
            type: 'POST',
            data: JSON.stringify(payload),
            contentType: 'application/json; charset=utf-8',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            success: function(res){
                const msg = (res && res.message) ? res.message : 'Opération réussie.';
                if (window.Swal) {
                    Swal.fire({icon:'success', title:'Succès', text: msg}).then(()=> {
                        // recharge pour voir la liste à jour
                        window.location.reload();
                    });
                } else {
                    alert(msg);
                    window.location.reload();
                }
            },
            error: function(xhr){
                let msg = 'Une erreur est survenue.';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    // détails de validation si dispo
                    if (xhr.responseJSON.errors) {
                        const list = Object.values(xhr.responseJSON.errors).flat().join('\n');
                        msg += '\n' + list;
                    }
                }
                if (window.Swal) {
                    Swal.fire({icon:'error', title:'Erreur', text: msg});
                } else {
                    alert(msg);
                }
            }
        });
    });

    // ---- Passage en mode édition (déjà en place) ----
    $('.btn-edit').on('click', function(){
        const d = this.dataset;

        $('#achatForm').attr('action', '{{ route("gf.achats.update","__ID__") }}'.replace('__ID__', d.id));
        $('#formMethod').val('PUT');
        $('#submitBtn').text('Mettre à jour');
        $('#cancelEdit').removeClass('d-none');

        $('select[name="code_projet"]').val(d.code_projet);
        $('select[name="code_acteur"]').val(d.code_acteur);
        $('input[name="reference_bc"]').val(d.reference_bc || '');
        $('input[name="date_commande"]').val(d.date_commande || '');
        $('input[name="devise"]').val(d.devise || '');
        $('select[name="statut_id"]').val(d.statut_id || '');
        $('textarea[name="commentaire"]').val(d.commentaire || '');

        $body.empty();
        try {
            const L = JSON.parse(d.lignes || '[]');
            if (Array.isArray(L) && L.length) {
                L.forEach(x=>{
                    $body.append(tpl());
                    const $tr = $body.find('tr').last();
                    $tr.find('input[name$="[libelle_materiau]"]').val(x.libelle_materiau || '');
                    $tr.find('input[name$="[unite]"]').val(x.unite || '');
                    $tr.find('.qtePrev').val(x.quantite_prevue || 0);
                    $tr.find('.qteRec').val(x.quantite_recue || 0);
                    $tr.find('.pu').val(x.prix_unitaire || 0);
                    $tr.find('.tva').val(x.tva ?? 0);
                });
            } else {
                $('#addLine').click();
            }
        } catch(e) { $('#addLine').click(); }

        recalc();
    });

    // ---- Annuler édition ----
    $('#cancelEdit').on('click', function(){
        $('#achatForm').attr('action', '{{ route("gf.achats.store") }}');
        $('#formMethod').val('POST');
        $('#submitBtn').text('Enregistrer');
        $(this).addClass('d-none');
        $('#achatForm')[0].reset();
        $body.empty();
        $('#addLine').click();
        recalc();
    });

    // ---- Suppression en AJAX ----
    $(document).on('submit', 'form[action*="gf/achats/"][method="POST"]', function(e){
        // n’intercepte que les formulaires de suppression (avec @method('DELETE'))
        const hasDelete = $(this).find('input[name="_method"][value="DELETE"]').length > 0;
        if (!hasDelete) return; // laisser les autres passer (au cas où)

        e.preventDefault();

        const url   = this.action;
        const token = $(this).find('input[name="_token"]').val();

        $.ajax({
            url: url,
            type: 'POST',
            data: { _method: 'DELETE' },
            headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
            success: function(res){
                const msg = (res && res.message) ? res.message : 'Supprimé.';
                if (window.Swal) {
                    Swal.fire({icon:'success', title:'Succès', text: msg}).then(()=> {
                        window.location.reload();
                    });
                } else {
                    alert(msg);
                    window.location.reload();
                }
            },
            error: function(xhr){
                let msg = 'Suppression impossible.';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                if (window.Swal) {
                    Swal.fire({icon:'error', title:'Erreur', text: msg});
                } else {
                    alert(msg);
                }
            }
        });
    });

    // DataTable (si tu utilises ta fonction utilitaire)
    $(document).ready(function() {
        if (typeof initDataTable === 'function') {
            initDataTable('{{ auth()->user()->acteur?->libelle_court }} {{ auth()->user()->acteur?->libelle_long }}', 'tableAchats', 'Liste des achats');
        }
    });
})();
</script>


@endsection
