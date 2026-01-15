@extends('layouts.app')

@section('content')
<div class="page-heading">
  <div class="page-title">
    <div class="row">
      <div class="col-sm-12">
        <li class="breadcrumb-item" style="list-style:none;text-align:right;padding:5px;">
          <span id="date-now" style="color:#34495E;margin-left:15px;"></span>
        </li>
      </div>
    </div>
    <div class="row">
      <div class="col-12 col-md-6 order-md-1 order-last">
        <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Projet</h3>
      </div>
      <div class="col-12 col-md-6 order-md-2 order-first">
        <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="">Gestion des exceptions</a></li>
            <li class="breadcrumb-item active" aria-current="page">Suspendre projet</li>
          </ol>
        </nav>
        <script>
          setInterval(()=>{ const el=document.getElementById('date-now'); if(el) el.textContent=new Date().toLocaleString(); },1000);
        </script>
      </div>
    </div>
  </div>
</div>
<div class="container">
  <div class="card">
    <div class="card-header">
      <h3>Suspension d’un projet</h3>
    </div>
    <div class="card-body">
      @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif
      @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
      @endif

      {{-- Sélection du type de projet --}}
      <div class="row align-items-end mb-3">
        <div class="col-3">
          <label class="form-label">Type de projet</label>
          <select id="susp_type_projet" class="form-control">
            @can('projettype.select', 'INF')
              <option value="PROJET">Projet d'infrastructure</option>
            @endcan
            @can('projettype.select', 'APP')
              <option value="APPUI">Projet d'appui</option>
            @endcan
            @can('projettype.select', 'ETU')
              <option value="ETUDE">Projet d'étude</option>
            @endcan
          </select>
          <small class="text-muted">Choisissez le type pour filtrer la liste des codes.</small>
        </div>
      </div>

      {{-- Formulaire de suspension --}}
      <form id="suspensionForm" method="POST" action="{{ route('projets.suspension.store') }}">
        @csrf
        <input type="hidden" name="ecran_id" value="{{ $ecran->id ?? '' }}">

        <div class="row">
          <div class="col-md-3">
            <label for="code_projet_suspendre">Projet à suspendre *</label>
            <select name="code_projet" id="code_projet_suspendre" class="form-control" required>
              <option value="">-- Sélectionnez un projet --</option>
            </select>
          </div>

          {{-- Carte d’info projet --}}
          <div id="infoCard" class="col-md-9" style="display:none;">
            <div class="card shadow-sm border-primary mb-3" style="border:none;">
              <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center" style="min-height:57px;">
                <div><small class="d-block">Nature : <strong><span id="nature"></span></strong></small></div>
                <div><small class="d-block"><strong><span id="libelle_projet"></span></strong></small></div>
                <div>
                  <small class="d-block"><strong>Domaine</strong> : <strong><span id="domaine"></span></strong></small>
                  <small class="d-block"><strong>Sous domaine</strong> : <strong><span id="sousDomaine"></span></strong></small>
                </div>
              </div>
              <div class="card-body">
                <div class="row g-4">
                  <div class="col-md-8">
                    <div class="d-flex align-items-start mb-3">
                      <i class="bi bi-calendar-check me-3 fs-4 text-primary"></i>
                      <div>
                        <h6 class="mb-1 fw-bold text-muted">Période</h6>
                        <p class="mb-0">
                          Du <span id="date_demarrage_prevue"></span>
                          au <span id="date_fin_prevue"></span>
                        </p>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="d-flex align-items-start mb-3">
                      <i class="bi bi-cash-coin me-3 fs-4 text-primary"></i>
                      <div>
                        <h6 class="mb-1 fw-bold text-muted">Budget</h6>
                        <p class="mb-0"><span id="cout"></span> <span id="devise"></span></p>
                      </div>
                    </div>
                  </div>
                </div><!--/row-->
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-9">
            <label for="motif">Motif de la suspension *</label>
            <textarea name="motif" class="form-control" rows="2" required placeholder="Expliquez la raison de la suspension..."></textarea>
          </div>
          <div class="col-3 text-end" style="top:23px">
            @can("modifier_ecran_" . ($ecran->id ?? 0))
              <button id="btnSuspendre" type="submit" class="btn btn-warning mt-3">Suspendre le projet</button>
            @endcan
          </div>
        </div>
      </form>
    </div>
  </div>

  <hr>

  {{-- Tableau des projets suspendus --}}
  <div class="card mt-4">
    <div class="card-header">
      <h5>📋 Projets suspendus</h5>
    </div>
    <div class="card-body">
      <table class="table table-striped table-bordered" id="tableSuspendus" style="width:100%">
        <thead>
          <tr>
            <th>Code</th>
            <th class="col-3">Libellé</th>
            <th class="col-1">Date suspension</th>
            <th>Motif de suspension</th>
            <th>Date redémarrage</th>
            <th>Actions</th>
            <th>Statut</th>
          </tr>
        </thead>
        <tbody>
          @foreach($projetsSuspendus as $p)
            <tr>
              <td>{{ $p->code_projet }}</td>
              <td class="col-3">{{ $p->libelle_projet }}</td>
              <td class="col-1">{{ ($p->dernier_type == 5) ? ($p->date_suspension ?? '-') : '-' }}</td>
              <td>{{ ($p->dernier_type == 5) ? ($p->motif_suspension ?? 'Aucun motif') : '-' }}</td>

              <form method="POST" class="form-redemarrage" action="{{ route('projets.redemarrer') }}">
                @csrf
                <input type="hidden" name="ecran_id" value="{{ $ecran->id ?? '' }}">
                <input type="hidden" name="code_projet" value="{{ $p->code_projet }}">
                <td>
                  @if($p->dernier_type == 6)
                    {{ $p->date_redemarrage ?? '-' }}
                  @else
                    <input type="date" name="dateRedemarrage" class="form-control" required>
                  @endif
                </td>
                <td>
                  @if($p->dernier_type == 5)
                    @can("modifier_ecran_" . ($ecran->id ?? 0))
                      <button type="submit" class="btn btn-success" style="font-size:12px;">Redémarrer</button>
                    @endcan
                  @endif
                </td>
                <td>{{ $p->statut_libelle }}</td>
              </form>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- Styles SweetAlert optionnels --}}
<style>
  .swal2-center-text { text-align: center; }
  .swal2-actions { justify-content: center !important; }
</style>

{{-- Scripts --}}
<script>
  // DataTable
  document.addEventListener('DOMContentLoaded', function(){
    if (typeof initDataTable === 'function') {
      initDataTable('{{ auth()->user()->acteur?->libelle_court }} {{ auth()->user()->acteur?->libelle_long }}',
                    'tableSuspendus',
                    "Liste des projets suspendus");
    }
  });

  const typeSelect   = document.getElementById('susp_type_projet');
  const projetSelect = document.getElementById('code_projet_suspendre');

  // Détecte le type par préfixe
  const PREFIX_TO_TYPE = [
    { prefix: 'ET_',    type: 'ETUDE'  },
    { prefix: 'APPUI_', type: 'APPUI'  },
  ];
  function detectTypeFromCode(code) {
    if (!code) return 'PROJET';
    const hit = PREFIX_TO_TYPE.find(p => code.startsWith(p.prefix));
    return hit ? hit.type : 'PROJET';
  }

  // Recharge les options selon le type
  async function reloadProjetOptionsSuspendre(type, preselect=null) {
    projetSelect.innerHTML = '<option value="">Chargement...</option>';
    try {
      const url = @json(route('projets.suspension.options')) + '?type=' + encodeURIComponent(type || 'PROJET');
      const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
      const data = await res.json();

      projetSelect.innerHTML = '<option value="">-- Sélectionnez un projet --</option>';
      (data || []).forEach(row => {
        const opt = document.createElement('option');
        opt.value = row.code;
        opt.textContent = row.code + (row.label ? ' — ' + row.label : '');
        projetSelect.appendChild(opt);
      });

      if (preselect && (data || []).some(d => d.code === preselect)) {
        projetSelect.value = preselect;
        loadProjetCardDetails(preselect);
      }
    } catch (e) {
      projetSelect.innerHTML = '<option value="">Erreur de chargement</option>';
    }
  }

  // Carte projet
  function showCard(d) {
    const safe = v => (v ?? '').toString().trim() || '-';
    document.getElementById('libelle_projet').textContent      = safe(d.libelle_projet);
    document.getElementById('nature').textContent               = safe(d.nature);
    document.getElementById('domaine').textContent              = safe(d.domaine);
    document.getElementById('sousDomaine').textContent          = safe(d.sousDomaine);
    document.getElementById('date_demarrage_prevue').textContent= safe(d.date_demarrage_prevue);
    document.getElementById('date_fin_prevue').textContent      = safe(d.date_fin_prevue);
    document.getElementById('devise').textContent               = safe(d.devise);
    document.getElementById('cout').textContent                 = d.cout ? new Intl.NumberFormat('fr-FR').format(d.cout) : '-';
    document.getElementById('infoCard').style.display = '';
  }
  function loadProjetCardDetails(code) {
    if (!code) { document.getElementById('infoCard').style.display='none'; return; }
    fetch(@json(url('/getProjetCard')) + '/' + encodeURIComponent(code))
      .then(r => r.json())
      .then(d => { if (d) showCard(d); else document.getElementById('infoCard').style.display='none'; })
      .catch(() => document.getElementById('infoCard').style.display='none');
  }

  // Init
  document.addEventListener('DOMContentLoaded', () => {
    reloadProjetOptionsSuspendre(typeSelect.value);
  });

  // Changement type
  typeSelect.addEventListener('change', () => {
    reloadProjetOptionsSuspendre(typeSelect.value);
    document.getElementById('infoCard').style.display='none';
  });

  // Changement projet
  projetSelect.addEventListener('change', function(){
    const code = this.value;
    if (!code) { document.getElementById('infoCard').style.display='none'; return; }

    const autoType = detectTypeFromCode(code);
    if (autoType !== typeSelect.value) {
      typeSelect.value = autoType;
      reloadProjetOptionsSuspendre(autoType, code);
      return;
    }
    loadProjetCardDetails(code);
  });

  // Soumission AJAX + SweetAlert
  $(function () {
    const $form = $('#suspensionForm');
    const $btn  = $('#btnSuspendre');

    const esc = (s) => $('<div/>').text(s ?? '').html();

    $form.on('submit', function (e) {
      e.preventDefault();

      const code  = $('#code_projet_suspendre').val();
      const motif = ($('textarea[name="motif"]').val() || '').trim();

      Swal.fire({
        title: 'Confirmer la suspension',
        icon: 'warning',
        html: `
          <div class="swal2-center-text">
            <p>Voulez-vous vraiment suspendre le projet <b>${esc(code)}</b> ?</p>
            ${motif ? `<p><b>Motif :</b> ${esc(motif)}</p>` : ''}
          </div>
        `,
        customClass: { title: 'swal2-center-text', htmlContainer: 'swal2-center-text' },
        showCancelButton: true,
        confirmButtonText: 'Oui, suspendre',
        cancelButtonText: 'Annuler',
        confirmButtonColor: '#f39c12',
        cancelButtonColor: '#6c757d',
        width: 520
      }).then((res) => {
        if (!res.isConfirmed) return;

        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Suspension...');

        $.ajax({
          url: $form.attr('action'),
          method: 'POST',
          data: $form.serialize(),
          dataType: 'json',
          success: function (json) {
            if (json?.success) {
              Swal.fire({ icon: 'success', title: 'Succès', text: json.message || 'Projet suspendu avec succès.', timer: 1800, showConfirmButton: false })
                .then(() => location.reload());
            } else {
              Swal.fire({ icon: 'error', title: 'Erreur', text: json?.message || 'Erreur lors de la suspension du projet.' });
            }
          },
          error: function (xhr) {
            if (xhr.status === 422) {
              const errs = xhr.responseJSON?.errors || {};
              const firstMsg = Object.values(errs)[0]?.[0] || xhr.responseJSON?.message || "Données invalides.";
              Swal.fire({ icon: 'error', title: 'Validation', text: firstMsg });
              return;
            }
            const msg = xhr.responseJSON?.message || 'Erreur serveur.';
            Swal.fire({ icon: 'error', title: 'Erreur', text: msg });
          },
          complete: function () {
            $btn.prop('disabled', false).text('Suspendre le projet');
          }
        });
      });
    });

    // Redémarrage AJAX
    $('.form-redemarrage').on('submit', function (e) {
      e.preventDefault();
      const form = $(this);
      $.ajax({
        url: form.attr('action'),
        method: 'POST',
        data: form.serialize(),
        success: function (res) {
          if (res.success) {
            alert(res.success);
            window.location.href = @json(route('projets.suspension.form'));
          } else if (res.error) {
            alert(res.error);
          }
        },
        error: function (xhr) {
          const msg = xhr.responseJSON?.error || xhr.responseJSON?.message || "Une erreur est survenue.";
          alert(msg);
        }
      });
    });
  });
</script>
@endsection
