@extends('layouts.app')

@section('content')
@isset($ecran)
    @can("consulter_ecran_" . $ecran->id)
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
                <input type="hidden" name="ecran_id" value="{{ $ecran->id }}">

                {{-- Cible du paiement --}}
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Payer *</label>
                        <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="pay_target" id="pay_prest" value="prestataire" checked>
                        <label class="btn btn-outline-primary" for="pay_prest">Prestataire</label>
                        <input type="radio" class="btn-check" name="pay_target" id="pay_benef" value="beneficiaire">
                        <label class="btn btn-outline-primary" for="pay_benef">Bénéficiaire</label>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <label class="form-label"><span id="label-acteur">Prestataire</span> *</label>
                        <select name="code_acteur" id="code_acteur" class="form-select" required>
                        <option value="">— Sélectionnez —</option>
                        @foreach($prestataires as $a)
                            <option value="{{ $a->code_acteur }}">{{ trim(($a->libelle_court ?? '').' '.($a->libelle_long ?? '')) }}</option>
                        @endforeach
                        </select>
                        @error('code_acteur') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Devise</label>
                        <input type="text" name="devise" id="devise" class="form-control" placeholder="XOF">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Statut *</label>
                        <select name="statut_id" id="statut_id" class="form-select" required>
                        @foreach($statuts as $s)
                            <option value="{{ $s->id }}">{{ $s->libelle }}</option>
                        @endforeach
                        </select>
                    </div>
                </div>

                {{-- ONGLET CONTEXTE --}}
                <ul class="nav nav-tabs mt-4" id="ctxTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="proj-tab" data-bs-toggle="tab" data-bs-target="#ctx-proj" type="button" role="tab">Projet</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tc-tab" data-bs-toggle="tab" data-bs-target="#ctx-tc" type="button" role="tab">Travaux connexes</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="rf-tab" data-bs-toggle="tab" data-bs-target="#ctx-rf" type="button" role="tab">Formation (Renforcement)</button>
                </li>
                </ul>

                <div class="tab-content border-start border-end border-bottom p-3 rounded-bottom" id="ctxTabsContent">
                {{-- Projet --}}
                <div class="tab-pane fade show active" id="ctx-proj" role="tabpanel">
                    <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Projet *</label>
                        <select name="code_projet" id="code_projet" class="form-select" required>
                        <option value="">— Sélectionnez —</option>
                        
                        @foreach($projets as $p)
                            <option value="{{ $p->code_projet }}">{{ $p->code_projet }} — {{ $p->libelle_projet }}</option>
                        @endforeach
                        </select>
                        @error('code_projet') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    </div>
                </div>

                {{-- Travaux connexes --}}
                <div class="tab-pane fade" id="ctx-tc" role="tabpanel">
                    <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Projet *</label>
                        <select id="code_projet_tc" class="form-select">
                        <option value="">— Sélectionnez —</option>
                        @foreach($projets as $p)
                            <option value="{{ $p->code_projet }}">{{ $p->code_projet }} — {{ $p->libelle_projet }}</option>
                        @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Travaux connexes *</label>
                        <select name="code_travaux_connexe" id="code_travaux_connexe" class="form-select" disabled>
                        <option value="">— Sélectionnez un projet d’abord —</option>
                        </select>
                    </div>
                    </div>
                </div>

                {{-- Formation / Renforcement --}}
                <div class="tab-pane fade" id="ctx-rf" role="tabpanel">
                    <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Projet *</label>
                        <select id="code_projet_rf" class="form-select">
                        <option value="">— Sélectionnez —</option>
                        <option value="">Aucun projet</option>
                        @foreach($projets as $p)
                            <option value="{{ $p->code_projet }}">{{ $p->code_projet }} — {{ $p->libelle_projet }}</option>
                        @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Formation (Renforcement) *</label>
                        <select name="code_renforcement" id="code_renforcement" class="form-select" disabled>
                        <option value="">— Sélectionnez un projet d’abord —</option>
                        </select>
                    </div>
                    </div>
                </div>
                </div>

                {{-- Facture / Règlement --}}
                <div class="row g-3 mt-3">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">Date Facture</label>
                            <input type="date" name="date_facture" id="date_facture" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tranche N°</label>
                            <input type="number" min="1" name="tranche_no" id="tranche_no" class="form-control" placeholder="auto">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Mt. Facture</label>
                            <input type="number" step="0.01" min="0" name="montant_facture" id="montant_facture" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Réf. Facture</label>
                            <input type="text" name="reference_facture" id="reference_facture" class="form-control">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">Date Règlement *</label>
                            <input type="date" name="date_reglement" id="date_reglement" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Mode règlement *</label>
                            <select name="mode_id" id="mode_id" class="form-select" required>
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
                            <label class="form-label">Solde</label>
                            <input type="text" id="solde_affiche" class="form-control" readonly>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Commentaire</label>
                            <textarea name="commentaire" id="commentaire" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-3 justify-content-end mt-3">
                <div class="text-end me-auto">
                    <small class="text-muted">Solde facture estimé : <span id="soldeOut">—</span></small>
                </div>
                @can("ajouter_ecran_" . $ecran->id)
                <button type="submit" class="btn btn-primary" id="submitBtn">Enregistrer</button>
                @endcan
                @can("modifier_ecran_" . $ecran->id)
                <button type="submit" class="btn btn-primary d-none" id="updateBtn">Mettre à jour</button>
                @endcan
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
                                    @can("modifier_ecran_" . $ecran->id)
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
                                    @endcan
                                    @can("supprimer_ecran_" . $ecran->id)
                                    <button type="button"
                                            class="btn btn-outline-danger btn-sm"
                                            onclick="confirmDelete('{{ route('gf.reglements.destroy', $r->id) }}?ecran_id={{ $ecran->id }}', () => location.reload())">
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
                {{ $reglements->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>

<script>
(function(){
  const fmt = n => (isNaN(n)?0:n).toLocaleString('fr-FR',{minimumFractionDigits:2,maximumFractionDigits:2});
  const $montantFacture = $('#montant_facture');
  const $montantRegle   = $('#montant_regle');
  const $soldeOut       = $('#soldeOut');
  const $soldeAff       = $('#solde_affiche');

  function recalcSolde(){
    const f = parseFloat($montantFacture.val());
    const r = parseFloat($montantRegle.val());
    if (isNaN(f)) { $soldeOut.text('—'); $soldeAff.val(''); return; }
    const s = f - (isNaN(r)?0:r);
    $soldeOut.text(fmt(s));
    $soldeAff.val(fmt(s));
  }
  $montantFacture.on('input', recalcSolde);
  $montantRegle.on('input', recalcSolde);

  // Prestataire / Bénéficiaire toggle
  $('input[name="pay_target"]').on('change', function(){
    const isPrest = $('#pay_prest').is(':checked');
    $('#label-acteur').text(isPrest ? 'Prestataire' : 'Bénéficiaire');
    // Optionnel : si tu veux charger une autre source côté serveur, fais-le ici
  });

  // Chargement listés dépendants (TC et RF) par projet
  async function loadContextLists(codeProjet){
    if(!codeProjet) {
      $('#code_travaux_connexe').prop('disabled',true).html('<option value="">— Sélectionnez un projet d’abord —</option>');
      $('#code_renforcement').prop('disabled',true).html('<option value="">— Sélectionnez un projet d’abord —</option>');
      return;
    }
    try{
      const url = '{{ route("gf.reglements.contextByProjet","__CP__") }}'.replace('__CP__', encodeURIComponent(codeProjet));
      const res = await fetch(url, {headers:{'Accept':'application/json'}});
      const data = await res.json();

      const $tc = $('#code_travaux_connexe');
      const $rf = $('#code_renforcement');

      // Travaux connexes
      $tc.prop('disabled', false).empty().append('<option value="">— Sélectionnez —</option>');
      (data.travaux || []).forEach(t => {
        $tc.append(new Option(`${t.code} — ${t.libelle}`, t.code));
      });

      // Renforcements
      $rf.prop('disabled', false).empty().append('<option value="">— Sélectionnez —</option>');
      (data.renforcements || []).forEach(r => {
        $rf.append(new Option(`${r.code} — ${r.titre}`, r.code));
      });

    } catch(e){
      console.error(e);
      Swal.fire({icon:'error', title:'Erreur', text:'Chargement du contexte impossible.'});
    }
  }

  // Quand projet change dans chaque onglet
  $('#code_projet').on('change', function(){ /* rien ; c’est le contexte Projet */ });
  $('#code_projet_tc').on('change', function(){ loadContextLists(this.value); });
  $('#code_projet_rf').on('change', function(){ loadContextLists(this.value); });

  // Soumission AJAX (ton bloc existant amélioré)
  const form = document.getElementById('regForm');
  const methodInput = document.getElementById('formMethod');

  form.addEventListener('submit', async function(e){
    e.preventDefault();

    // Mutex de contexte : on poste un seul contexte
    const activeTab = document.querySelector('#ctxTabs .nav-link.active')?.id || 'proj-tab';
    const fd = new FormData(form);

    // Normalise code_projet selon l’onglet
    if (activeTab === 'proj-tab') {
      // conserve code_projet, vide TC/RF
      fd.delete('code_travaux_connexe');
      fd.delete('code_renforcement');
    } else if (activeTab === 'tc-tab') {
      const cp = $('#code_projet_tc').val();
      if (!cp) return Swal.fire({icon:'warning',title:'Projet requis',text:'Sélectionnez un projet.'});
      fd.set('code_projet', cp);
      fd.delete('code_renforcement');
    } else if (activeTab === 'rf-tab') {
      const cp = $('#code_projet_rf').val();
      if (!cp) return Swal.fire({icon:'warning',title:'Projet requis',text:'Sélectionnez un projet.'});
      fd.set('code_projet', cp);
      fd.delete('code_travaux_connexe');
    }

    // _method / headers
    const url   = form.getAttribute('action');
    const isPUT = (methodInput.value || 'POST').toUpperCase() === 'PUT';
    if (isPUT && !fd.has('_method')) fd.append('_method','PUT');

    try{
      const res  = await fetch(url, { method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}, body:fd });
      const data = await res.json();

      if (!res.ok) {
        if (data?.errors) {
          const lines = Object.values(data.errors).flat().map(s=>`<li>${s}</li>`).join('');
          return Swal.fire({icon:'error',title:'Veuillez corriger les erreurs',html:`<ul style="text-align:left">${lines}</ul>`});
        }
        return Swal.fire({icon:'error',title:'Erreur',text:data?.error||'Une erreur est survenue.'});
      }

      if (data.ok) {
        Swal.fire({icon:'success',title:data.message||'Succès',timer:1500,showConfirmButton:false});
        setTimeout(()=>window.location.reload(), 800);
      } else {
        Swal.fire({icon:'warning',title:'Info',text:data?.error||'Traitement non effectué.'});
      }
    } catch(err){
      Swal.fire({icon:'error',title:'Erreur réseau',text:String(err)});
    }
  });

  // Edition (complète ton code existant avec contextes si tu stockes tranche_no / contextes)
  $('.btn-edit').on('click', function(){
    const d = this.dataset;

    $('#regForm').attr('action', '{{ route("gf.reglements.update","__ID__") }}'.replace('__ID__', d.id));
    $('#formMethod').val('PUT');
    
    // Afficher le bon bouton selon les droits
    @can("ajouter_ecran_" . $ecran->id)
    $('#submitBtn').addClass('d-none');
    @endcan
    @can("modifier_ecran_" . $ecran->id)
    $('#updateBtn').removeClass('d-none');
    @endcan
    $('#cancelEdit').removeClass('d-none');

    // Acteur
    $('select[name="code_acteur"]').val(d.code_acteur);
    $('#devise').val(d.devise || '');
    $('#mode_id').val(d.mode_id);
    $('#statut_id').val(d.statut_id);

    // Facture / Reglement
    $('#reference_facture').val(d.reference_facture || '');
    $('#date_facture').val(d.date_facture || '');
    $('#montant_facture').val(d.montant_facture || '');
    $('#montant_regle').val(d.montant_regle || '');
    $('#date_reglement').val(d.date_reglement || '');
    $('#tranche_no').val(d.tranche_no || '');
    $('#commentaire').val(d.commentaire || '');

    // Contexte : par défaut on place dans “Projet”
    $('#proj-tab').tab('show');
    $('#code_projet').val(d.code_projet);

    recalcSolde();
  });

  // Annuler édition
  $('#cancelEdit').on('click', function(){
    $('#regForm').attr('action', '{{ route("gf.reglements.store") }}');
    $('#formMethod').val('POST');
    
    // Afficher le bon bouton selon les droits
    @can("ajouter_ecran_" . $ecran->id)
    $('#submitBtn').removeClass('d-none');
    @endcan
    $('#updateBtn').addClass('d-none');
    $(this).addClass('d-none');
    $('#regForm')[0].reset();
    $soldeOut.text('—'); $soldeAff.val('');
    $('#proj-tab').tab('show');
  });

  // DataTable init
  $(document).ready(function() {
    if (typeof initDataTable === 'function') {
      initDataTable('{{ auth()->user()->acteur?->libelle_court }} {{ auth()->user()->acteur?->libelle_long }}','tableReglements','Liste des règlements');
    }
  });
})();
</script>
    @endcan
@endisset
@endsection
