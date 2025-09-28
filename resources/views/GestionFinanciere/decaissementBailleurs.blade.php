@extends('layouts.app')

@section('content')

@php
  // Évite les "Undefined variable $ecran" dans les @can
  $ecranId = $ecran->id ?? 0;
@endphp

<style>
  .table td, .table th { white-space: nowrap; }
  .card-form.sticky { position: sticky; top: 0; z-index: 5; background: #fff; box-shadow: 0 2px 10px rgba(0,0,0,.04); }
  .btn-spinner { width: 1.25rem; height: 1.25rem; border: .15rem solid currentColor; border-right-color: transparent; border-radius: 50%; display: inline-block; vertical-align: -2px; animation: spin .7s linear infinite; }
  @keyframes spin { to { transform: rotate(360deg); } }
</style>

@can("consulter_ecran_" . $ecranId)

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
          <h3><i class="bi bi-arrow-return-left return" onclick="goBack()" style="cursor:pointer;"></i> Gestion financière</h3>
        </div>
        <div class="col-12 col-md-6 order-md-2 order-first">
          <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Décaissements bailleurs</a></li>
            </ol>
          </nav>
          <script>
            setInterval(() => {
              const el = document.getElementById('date-now');
              if (el) el.textContent = new Date().toLocaleString();
            }, 1000);
            function goBack(){ history.back(); }
          </script>
        </div>
      </div>
    </div>
  </div>

  <div class="container-fluid">
    {{-- Flash --}}
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if(session('error'))   <div class="alert alert-danger">{{ session('error') }}</div> @endif

    {{-- ================== FORMULAIRE ================== --}}
    <div class="card card-form mb-4">
      <div class="card-header"><strong>Nouveau / Éditer un décaissement</strong></div>
      <div class="card-body">
        <form id="decForm" method="POST" action="{{ route('gf.decaissements.store') }}">
          @csrf
          <input type="hidden" name="_method" value="POST" id="formMethod">
          <input type="hidden" name="ecran_id" value="{{ $ecranId }}">

          <div class="row g-3">
            {{-- Projet --}}
            <div class="col-md-3">
              <label class="form-label">Projet *</label>
              <select name="code_projet" id="code_projet" class="form-select" required>
                <option value="">— Sélectionnez —</option>
                @foreach($projets as $p)
                  <option value="{{ $p->code_projet }}">{{ $p->code_projet }} - {{ $p->libelle_projet }}</option>
                @endforeach
              </select>
              @error('code_projet') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            {{-- Financement (engagement) --}}
            <div class="col-md-3">
              <label class="form-label">Financement</label>
              <select name="financer_id" id="financer_id" class="form-select">
                <option value="">— Aucun —</option>
              </select>
              <small class="text-muted d-block">Sélectionnez un engagement pour verrouiller le bailleur et la devise.</small>
            </div>

            {{-- Bailleur --}}
            <div class="col-md-3">
              <label class="form-label">Bailleur *</label>
              <select name="code_acteur" id="code_acteur" class="form-select" required>
                <option value="">— Sélectionnez —</option>
              </select>
              @error('code_acteur') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            {{-- Devise --}}
            <div class="col-md-3">
              <label class="form-label">Devise</label>
              <input type="text" name="devise" id="devise" class="form-control" placeholder="XOF, USD…" readonly>
            </div>

            {{-- Banque --}}
            <div class="col-md-2">
              <label class="form-label">Banque</label>
              <select name="banqueId" id="banqueId" class="form-select">
                <option value="">— Non précisé —</option>
                @foreach($banques as $b)
                  <option value="{{ $b->id }}">
                    {{ $b->sigle ?: $b->nom }}
                    @if($b->est_internationale) (Internationale) @elseif($b->code_pays) ({{ $b->code_pays }}) @endif
                  </option>
                @endforeach
              </select>
              @error('banqueId') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            {{-- Mode de paiement --}}
            <div class="col-md-2">
              <label class="form-label">Mode de paiement</label>
              <select name="mode_id" id="mode_id" class="form-select">
                <option value="">— Non précisé —</option>
                @foreach($modes as $m)
                  <option value="{{ $m->id }}">{{ $m->libelle }}</option>
                @endforeach
              </select>
            </div>

            {{-- Date décaissement --}}
            <div class="col-md-2">
              <label class="form-label">Date décaissement *</label>
              <input type="date" name="date_decaissement" class="form-control" required>
            </div>

            {{-- Tranche (auto) --}}
            <div class="col-md-2">
              <label class="form-label">Tranche N°</label>
              <input type="number" name="tranche_no" class="form-control" min="1" readonly>
            </div>

            {{-- Montant --}}
            <div class="col-md-2">
              <label class="form-label">Montant décaissé *</label>
              <input type="text" name="montant" class="form-control montant-input" required inputmode="decimal" placeholder="Ex: 10 000,00">
              <small id="montant-max-info" class="text-muted d-block mt-1"></small>
              @error('montant') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            {{-- Référence --}}
            <div class="col-md-2">
              <label class="form-label">Référence</label>
              <input type="text" name="reference" class="form-control" placeholder="Ref. ou note interne">
            </div>

            {{-- Commentaire --}}
            <div class="col-12">
              <label class="form-label">Commentaire</label>
              <textarea name="commentaire" class="form-control" rows="2"></textarea>
            </div>
          </div>

          <div class="text-end mt-3">
            @can("ajouter_ecran_" . $ecranId)
              <button type="submit" class="btn btn-primary" id="submitBtn">Enregistrer</button>
            @endcan
            @can("modifier_ecran_" . $ecranId)
              <button type="submit" class="btn btn-primary d-none" id="updateBtn">Mettre à jour</button>
            @endcan
            <button type="button" class="btn btn-secondary d-none" id="cancelEdit">Annuler édition</button>
          </div>
        </form>
      </div>
    </div>

    {{-- ================== LISTE ================== --}}
    <div class="card">
      <div class="card-header"><strong>Décaissements enregistrés</strong></div>
      <div class="card-body table-responsive">
        <table class="table table-striped table-bordered" id="tableDecs" style="width:100%">
          <thead>
            <tr>
              <th>Projet</th>
              <th>Bailleur</th>
              <th class="col-1">Tranche N°</th>
              <th style="text-align:right;">Montant</th>
              <th>Date décaissement</th>
              <th>Référence</th>
              <th style="width:140px">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($decaissements as $d)
              @php
                $bailleurNom = trim(($d->bailleur?->libelle_court ?? '') . ' ' . ($d->bailleur?->libelle_long ?? ''));
              @endphp
              <tr>
                <td>
                  <div class="fw-bold">{{ $d->code_projet }}</div>
                  <small class="text-muted">{{ $d->projet?->libelle_projet }}</small>
                </td>
                <td>{{ $bailleurNom ?: '—' }}</td>
                <td><small class="text-muted">Tranche {{ $d->tranche_no ?? '—' }}</small></td>
                <td style="text-align:right;">
                  {{ number_format($d->montant, 2, ',', ' ') }} {{ $d->devise }}
                </td>
                <td>{{ $d->date_decaissement?->format('d/m/Y') ?? '—' }}</td>
                <td>{{ $d->reference ?? '—' }}</td>
                <td>
                  <div class="btn-group btn-group-sm">
                    @can("modifier_ecran_" . $ecranId)
                      <button class="btn btn-outline-primary btn-edit"
                        data-id="{{ $d->id }}"
                        data-code_projet="{{ $d->code_projet }}"
                        data-financer_id="{{ $d->financer_id }}"
                        data-code_acteur="{{ $d->code_acteur }}"
                        data-bailleur_label="{{ $bailleurNom }}"
                        data-devise="{{ $d->devise }}"
                        data-reference="{{ $d->reference }}"
                        data-tranche_no="{{ $d->tranche_no }}"
                        data-montant="{{ $d->montant }}"
                        data-date_decaissement="{{ optional($d->date_decaissement)->format('Y-m-d') }}"
                        data-banque_id="{{ $d->banqueId }}"
                        data-mode_id="{{ $d->mode_id ?? '' }}"
                        data-commentaire="{{ $d->commentaire }}">
                        <i class="bi bi-pencil-square"></i>
                      </button>
                    @endcan

                    @can("supprimer_ecran_" . $ecranId)
                      <button type="button" class="btn btn-outline-danger"
                        onclick="confirmDelete('{{ route('gf.decaissements.destroy', $d->id) }}?ecran_id={{ $ecranId }}', () => location.reload())">
                        <i class="bi bi-trash"></i>
                      </button>
                    @endcan
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

  {{-- ================== SCRIPTS ================== --}}
  <script>
    /* --- Helpers génériques --- */
    function confirmDelete(url, onDone){
      if (window.Swal) {
        Swal.fire({
          icon:'warning', title:'Confirmer la suppression', text:'Cette opération est irréversible.',
          showCancelButton:true, confirmButtonText:'Supprimer', cancelButtonText:'Annuler'
        }).then(res => {
          if(res.isConfirmed){
            fetch(url, {method:'DELETE', headers:{'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':'{{ csrf_token() }}'}})
              .then(r => r.json())
              .then(j => {
                if (j.ok) {
                  Swal.fire({icon:'success', title: j.message || 'Supprimé.'});
                  onDone && onDone();
                } else {
                  Swal.fire({icon:'error', title: j.message || 'Suppression impossible.'});
                }
              })
              .catch(() => Swal.fire({icon:'error', title:'Erreur serveur'}));
          }
        });
      } else if (confirm('Supprimer ?')) {
        fetch(url, {method:'DELETE', headers:{'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':'{{ csrf_token() }}'}})
          .then(r=>r.json()).then(()=>onDone && onDone());
      }
    }

    function waitUntil(predicate, {interval=100, timeout=5000} = {}){
      return new Promise((resolve, reject) => {
        const start = Date.now();
        const id = setInterval(() => {
          if (predicate()) { clearInterval(id); resolve(); }
          else if (Date.now() - start > timeout) { clearInterval(id); reject(new Error('Timeout')); }
        }, interval);
      });
    }

    function findBestFinancementOption(bailleurId, devise) {
      const sel = document.getElementById('financer_id');
      if (!sel) return null;
      let best = null;
      for (const opt of sel.options) {
        if (!opt.value) continue;
        const b = opt.dataset.bailleurId || '';
        const d = opt.dataset.devise || '';
        if (b && b === String(bailleurId)) {
          best = opt;
          if (devise && d === String(devise)) return opt;
        }
      }
      return best;
    }

    (function(){
      const $form        = $('#decForm');
      const $proj        = $('#code_projet');
      const $fin         = $('#financer_id');
      const $act         = $('#code_acteur');
      const $dev         = $('#devise');
      const $banque      = $('#banqueId');
      const $mode        = $('#mode_id');
      const $submitBtn   = $('#submitBtn');
      const $updateBtn   = $('#updateBtn');
      const $cancelEdit  = $('#cancelEdit');

      /* ===== Verrou de tranche en mode édition ===== */
      let formMode = 'create';          // 'create' | 'edit'
      let suppressTrancheAuto = false;  // bloque le recalcul auto
      function setFormMode(mode){
        formMode = mode;
        suppressTrancheAuto = (mode === 'edit');
      }
      setFormMode('create');
      function allowAutoTrancheIfUserAction(e){
        if (e && e.originalEvent) suppressTrancheAuto = false;
      }

      /* ===== Remplissage des financements quand projet change ===== */
      $proj.on('change', function(e){
        allowAutoTrancheIfUserAction(e);
        const code = this.value;
        $fin.html('<option value="">— Aucun —</option>');
        $act.html('<option value="">— Sélectionnez —</option>');
        if(!code) return;

        fetch(`{{ route('gf.decaissements.financementsByProjet','__CODE__') }}`.replace('__CODE__', encodeURIComponent(code)))
          .then(r => r.json())
          .then(list => {
            const seen = new Set();
            list.forEach(item => {
              const opt = document.createElement('option');
              opt.value = item.id;
              opt.text  = `${item.montant_fmt} ${item.devise || ''}`;
              opt.dataset.bailleurId    = item.bailleur_id || '';
              opt.dataset.bailleurLabel = item.bailleur_label || '';
              opt.dataset.devise        = item.devise || '';
              opt.dataset.montant       = item.montant || 0;
              $fin.append(opt);

              if (item.bailleur_id && !seen.has(item.bailleur_id)) {
                seen.add(item.bailleur_id);
                $act.append(new Option(item.bailleur_label, item.bailleur_id));
              }
            });
          })
          .catch(() => window.Swal ? Swal.fire({icon:'error', title:'Erreur de chargement'}) : alert('Erreur de chargement'));
      });

      /* ===== Sélection d’un engagement ===== */
      $fin.on('change', function(e){
        allowAutoTrancheIfUserAction(e);

        const opt = this.selectedOptions[0];
        if (!opt) return;

        const bailleurId     = opt.dataset.bailleurId;
        const bailleurLabel  = opt.dataset.bailleurLabel;
        const devise         = opt.dataset.devise;
        const montantFinance = parseFloat(opt.dataset.montant || 0);

        $('#montant-max-info').text(
          isFinite(montantFinance) && montantFinance > 0
            ? `Maximum autorisé : ${montantFinance.toLocaleString('fr-FR')} ${devise || ''}`
            : ''
        );

        if (bailleurId) {
          if (!$act.find(`option[value="${bailleurId}"]`).length) {
            $act.append(new Option(bailleurLabel, bailleurId));
          }
          $act.val(bailleurId).prop('readonly', true);
        } else {
          $act.prop('readonly', false).val('');
        }

        if (devise) $dev.val(devise);

        if (!suppressTrancheAuto) updateNextTranche();
      });

      function updateNextTranche(){
        if (suppressTrancheAuto) return;
        const code_projet = $proj.val();
        const financer_id = $fin.val();
        if (!code_projet) return;

        const url = `{{ route('gf.decaissements.nextTranche') }}?code_projet=${encodeURIComponent(code_projet)}&financer_id=${encodeURIComponent(financer_id||'')}`;
        fetch(url)
          .then(r => r.json())
          .then(data => {
            const $tr = $('input[name="tranche_no"]');
            const current = ($tr.val() || '').trim();
            if (formMode === 'create' || current === '') {
              $tr.val(data.next || 1);
            }
          })
          .catch(() => window.Swal ? Swal.fire({icon:'error', title:'Erreur récupération tranche'}) : alert('Erreur récupération tranche'));
      }

      /* ===== Format / Unformat montant ===== */
      function formatMontant(value) {
        const raw = (value||'').toString();
        const parts = raw.replace(/[^\d.,]/g, '').split(',');
        const intPart = parts[0].replace(/\./g,'').replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
        const decPart = parts[1] ? ',' + parts[1].slice(0,2) : '';
        return intPart + decPart;
      }
      function unformatMontant(value) {
        return (value||'').toString().replace(/\s/g, '').replace(',', '.');
      }
      $('.montant-input').on('input', function() {
        const start = this.selectionStart;
        const before = this.value;
        const formatted = formatMontant(before);
        this.value = formatted;
        const delta = formatted.length - before.length;
        const pos = Math.max(0, (start||0) + delta);
        this.setSelectionRange(pos, pos);
      });

      /* ===== Soumission AJAX ===== */
      $form.on('submit', function(e){
        e.preventDefault();

        const selectedOption = $fin[0]?.selectedOptions[0];
        const plafond = selectedOption ? parseFloat(selectedOption.dataset.montant || '0') : Infinity;
        const $montantField = $('input[name="montant"]');
        const montantSaisi = parseFloat(unformatMontant($montantField.val() || '0'));

        if (isNaN(montantSaisi) || montantSaisi <= 0) {
          return window.Swal ? Swal.fire({icon:'warning', title:'Montant invalide'}) : alert('Montant invalide');
        }
        if (isFinite(plafond) && plafond > 0 && montantSaisi > plafond) {
          return window.Swal ? Swal.fire({icon:'warning', title:'Montant trop élevé', text:'Le montant dépasse le financement disponible.'}) : alert('Montant trop élevé');
        }

        // passer la valeur numérique brute
        $montantField.val(montantSaisi);

        const method = $('#formMethod').val().toUpperCase();
        const action = $form.attr('action');

        $submitBtn.prop('disabled', true);
        $updateBtn.prop('disabled', true);

        $.ajax({
          url: action,
          type: method,
          data: $form.serialize(),
          success: function(res){
            if (res.ok) {
              window.Swal ? Swal.fire({icon:'success', title: res.message || 'Succès'}) : alert(res.message || 'Succès');
              setTimeout(() => location.reload(), 700);
            } else {
              window.Swal ? Swal.fire({icon:'warning', title: res.message || 'Avertissement'}) : alert(res.message || 'Avertissement');
            }
          },
          error: function(xhr){
            const msg = xhr.responseJSON?.message || 'Erreur serveur';
            let details = '';
            if (xhr.status === 422 && xhr.responseJSON?.errors) {
              details = Object.values(xhr.responseJSON.errors).map(arr=>arr.join('<br>')).join('<br>');
            }
            window.Swal ? Swal.fire({icon:'error', title: msg, html: details}) : alert(msg);
          },
          complete: function(){
            $submitBtn.prop('disabled', false);
            $updateBtn.prop('disabled', false);
          }
        });
      });

      /* ===== Mode ÉDITION ===== */
      $('.btn-edit').on('click', async function(){
        const d = this.dataset;

        setFormMode('edit'); // active le verrou de tranche

        $('#decForm').attr('action', '{{ route("gf.decaissements.update", "__ID__") }}'.replace('__ID__', d.id));
        $('#formMethod').val('PUT');

        @can("ajouter_ecran_" . $ecranId)  $('#submitBtn').addClass('d-none'); @endcan
        @can("modifier_ecran_" . $ecranId) $('#updateBtn').removeClass('d-none'); @endcan
        $('#cancelEdit').removeClass('d-none');

        // Projet -> charge engagements
        $('#code_projet').val(d.code_projet).trigger('change');

        try {
          await waitUntil(() => $('#financer_id option').length > 1, {timeout: 5000});

          let selected = false;
          if (d.financer_id) {
            $('#financer_id').val(d.financer_id);
            if ($('#financer_id').val() !== d.financer_id) {
              const tmp = new Option(`${d.financer_id}`, d.financer_id);
              $('#financer_id').append(tmp).val(d.financer_id);
            }
            $('#financer_id').trigger('change'); // ignoré par le verrou
            selected = true;
          }

          if (!selected && d.code_acteur) {
            const opt = findBestFinancementOption(d.code_acteur, d.devise || '');
            if (opt) {
              $('#financer_id').val(opt.value).trigger('change'); // ignoré par le verrou
              selected = true;
            }
          }

          if (!selected && d.code_acteur) {
            if (!$('#code_acteur').find(`option[value="${d.code_acteur}"]`).length) {
              $('#code_acteur').append(new Option(d.bailleur_label || d.code_acteur, d.code_acteur));
            }
            $('#code_acteur').val(d.code_acteur);
            if (d.devise) $('#devise').val(d.devise);
          }
        } catch (e) {
          if (d.code_acteur) {
            if (!$('#code_acteur').find(`option[value="${d.code_acteur}"]`).length) {
              $('#code_acteur').append(new Option(d.bailleur_label || d.code_acteur, d.code_acteur));
            }
            $('#code_acteur').val(d.code_acteur);
          }
          if (d.devise) $('#devise').val(d.devise);
        }

        // Champs simples (y compris tranche à CONSERVER)
        $('input[name="reference"]').val(d.reference || '');
        $('input[name="tranche_no"]').val(d.tranche_no || ''); // on pose après les triggers
        $('input[name="montant"]').val(formatMontant(d.montant || ''));
        $('input[name="date_decaissement"]').val(d.date_decaissement || '');
        $('textarea[name="commentaire"]').val(d.commentaire || '');

        // Banque & mode
        if (d.banque_id) {
          $('#banqueId').val(d.banque_id);
          if ($('#banqueId').val() !== d.banque_id) {
            const opt = new Option(d.banque_id, d.banque_id);
            $('#banqueId').append(opt).val(d.banque_id);
          }
        }
        if (d.mode_id) $('#mode_id').val(d.mode_id);

        document.querySelector('.card-form')?.scrollIntoView({behavior:'smooth', block:'start'});
      });

      /* ===== Annuler ÉDITION ===== */
      $cancelEdit.on('click', function(){
        $form.attr('action', '{{ route("gf.decaissements.store") }}');
        $('#formMethod').val('POST');

        @can("ajouter_ecran_" . $ecranId)  $('#submitBtn').removeClass('d-none'); @endcan
        $('#updateBtn').addClass('d-none');

        $(this).addClass('d-none');
        $form[0].reset();
        $act.prop('readonly', false).html('<option value="">— Sélectionnez —</option>');
        $fin.html('<option value="">— Aucun —</option>');
        $('#montant-max-info').text('');
        $('#banqueId').val('');
        $('#mode_id').val('');

        setFormMode('create'); // re-permet le recalcul auto
        document.querySelector('.card-form')?.scrollIntoView({behavior:'smooth', block:'start'});
      });

      /* ===== DataTable optionnel ===== */
      $(document).ready(function() {
        if (typeof initDataTable === 'function') {
          initDataTable('{{ auth()->user()->acteur?->libelle_court }} {{ auth()->user()->acteur?->libelle_long }}', 'tableDecs', 'Liste des décaissements bailleur');
        }
      });
    })();
  </script>

@endcan

@endsection
