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
        <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i> Réattribution du Maître d’œuvre</h3>
      </div>
      <div class="col-12 col-md-6 order-md-2 order-first">
        <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="">Gestion des exceptions</a></li>
            <li class="breadcrumb-item active" aria-current="page">Réattribution de projet</li>
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
  <div class="row match-height">
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex align-items-end justify-content-between">
          <div>
            <h4 class="card-title mb-0">Réattribution du Maître d’œuvre</h4>
            <small class="text-muted">Choisissez d’abord le type pour recharger uniquement la liste des projets.</small>
          </div>
        </div>

        <div class="card-body">

          {{-- Ligne "Type de projet" (au-dessus du formulaire, le layout ne change pas) --}}
          <div class="row align-items-end mb-3">
            <div class="col-3">
              <label class="form-label">Type de projet</label>
              <select id="reatt_type_projet" class="form-control">
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
            </div>
          </div>

          {{-- FORMULAIRE PRINCIPAL --}}
          <form id="moForm" method="POST" action="{{ route('maitre_ouvrage.store') }}">
            @csrf
            <input type="hidden" name="ecran_id" value="{{ $ecran->id ?? '' }}">
            <input type="hidden" name="_method" value="POST">
            <input type="hidden" name="execution_id" id="execution_id">

            <div class="row">
              <div class="col-4">
                <label class="form-label">Projet *</label>
                <select name="projet_id" class="form-control" required id="projetSelect">
                  <option value="">-- Sélectionnez --</option>
                  @foreach($projets as $projet)
                    <option value="{{ $projet->code_projet }}">{{ $projet->code_projet }}</option>
                  @endforeach
                </select>
              </div>

              {{-- Carte d’info projet (cachée par défaut) --}}
              <div id="projetInfoCard" class="col-md-8" style="display:none;">
                <div class="card shadow-sm border-primary mb-3">
                  <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center" style="min-height:57px;">
                    <div><small class="d-block">Nature : <strong><span id="card_nature"></span></strong></small></div>
                    <div><small class="d-block"><strong><span id="card_libelle"></span></strong></small></div>
                    <div>
                      <small class="d-block"><strong>Domaine</strong> : <strong><span id="card_domaine"></span></strong></small>
                      <small class="d-block"><strong>Sous domaine</strong> : <strong><span id="card_sousDomaine"></span></strong></small>
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
                              Du <span id="card_date_demarrage_prevue"></span>
                              au <span id="card_date_fin_prevue"></span>
                            </p>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="d-flex align-items-start mb-3">
                          <i class="bi bi-cash-coin me-3 fs-4 text-primary"></i>
                          <div>
                            <h6 class="mb-1 fw-bold text-muted">Budget</h6>
                            <p class="mb-0">
                              <span id="card_cout"></span> <span id="card_devise"></span>
                            </p>
                          </div>
                        </div>
                      </div>
                    </div><!--/row-->
                  </div>
                </div>
              </div>
            </div>

            {{-- Sélection d’acteur --}}
            <div class="row mt-3">
              <div class="col-4">
                <label>Type de Maître d’œuvre *</label><br>
                <div class="form-check form-check-inline">
                  <input class="form-check-input type_ouvrage" type="radio" name="type_ouvrage" id="moePublic" value="Public">
                  <label class="form-check-label" for="moePublic">Public</label>
                </div>
                <div class="form-check form-check-inline">
                  <input class="form-check-input type_ouvrage" type="radio" name="type_ouvrage" id="moePrive" value="Privé">
                  <label class="form-check-label" for="moePrive">Privé</label>
                </div>
              </div>

              {{-- Options Privé --}}
              <div class="col-3 d-none" id="optionsMoePrive">
                <label>Type de Privé *</label><br>
                <div class="col form-check">
                  <input class="form-check-input type_prive" type="radio" name="priveMoeType" id="moeEntreprise" value="Entreprise">
                  <label class="form-check-label" for="moeEntreprise">Entreprise</label>
                </div>
                <div class="col form-check">
                  <input class="form-check-input type_prive" type="radio" name="priveMoeType" id="moeIndividu" value="Individu">
                  <label class="form-check-label" for="moeIndividu">Individu</label>
                </div>
              </div>

              <div class="col">
                <label for="acteurMoeSelect">Nouveau maître d’œuvre *</label>
                <select name="acteur_id" id="acteurMoeSelect" class="form-control" required>
                  <option value="">Sélectionnez un acteur</option>
                  @foreach($acteurs as $acteur)
                    <option value="{{ $acteur->code_acteur }}">{{ $acteur->libelle_court }} {{ $acteur->libelle_long }}</option>
                  @endforeach
                </select>
                <small class="text-muted">Entité qui assure le rôle de Maître d’œuvre.</small>
              </div>

              <div class="col" id="secteurContainer" style="display:none;">
                <label for="sectActivEntMoe">Secteur d’activité</label>
                <select name="secteur_id" id="sectActivEntMoe" class="form-control">
                  <option value="">Sélectionnez...</option>
                  @foreach ($SecteurActivites as $secteur)
                    <option value="{{ $secteur->code }}">{{ $secteur->libelle }}</option>
                  @endforeach
                </select>
              </div>
            </div>

            <div class="row mt-2">
              <div class="col-9">
                <label for="motif">Motif *</label>
                <textarea name="motif" id="motif" class="form-control" rows="2" required placeholder="Expliquer la raison de la réattribution."></textarea>
              </div>
              <div class="col text-end">
                @can("ajouter_ecran_" . ($ecran->id ?? 0))
                  <button type="submit" class="btn btn-primary mt-4" id="formButton">Enregistrer</button>
                @endcan
              </div>
            </div>

          </form>
        </div>
      </div>
    </div>
  </div>

  {{-- TABLE existing --}}
  <h5 class="mt-4">Maîtres d’œuvre existants</h5>
  <div class="card">
    <div class="card-body">
      <table class="table table-striped table-bordered" cellspacing="0" style="width:100%" id="table1">
        <thead>
          <tr>
            <th>Code Projet</th>
            <th>Maître d’œuvre</th>
            <th>Type</th>
            <th>Secteur</th>
            <th>Motif</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($executions as $execution)
            <tr>
              <td>{{ $execution->code_projet }}</td>
              <td>{{ $execution->acteur->libelle_court ?? '' }} {{ $execution->acteur->libelle_long ?? '' }}</td>
              <td>
                @if(in_array($execution?->acteur?->type_acteur, ['eta','clt']))
                  <span class="badge bg-success">Public</span>
                @else
                  <span class="badge bg-secondary">Privé</span>
                @endif
              </td>
              <td>{{ $execution?->secteurActivite?->libelle ?? '-' }}</td>
              <td>{{ $execution?->motif ?? '-' }}</td>
              <td>
                <div class="dropdown">
                  <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="dropdownMenu{{ $execution->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                    Actions
                  </button>
                  <ul class="dropdown-menu" aria-labelledby="dropdownMenu{{ $execution->id }}">
                    @can("modifier_ecran_" . ($ecran->id ?? 0))
                      <li>
                        <button class="dropdown-item text-warning" type="button"
                          onclick="editMO(@js([
                            'id'            => $execution?->id,
                            'code_projet'   => $execution?->code_projet,
                            'code_acteur'   => $execution?->code_acteur,
                            'secteur_id'    => $execution?->secteur_id,
                            'secteur_libelle' => $execution?->secteurActivite?->libelle ?? null,
                            'motif'         => $execution?->motif,
                            'acteur_type'   => $execution?->acteur?->type_acteur,
                            'acteur_nom'    => trim(($execution?->acteur?->libelle_court ?? '').' '.($execution?->acteur?->libelle_long ?? '')),
                          ]))">
                          <i class="bi bi-pencil-square"></i> Modifier
                        </button>
                      </li>
                    @endcan
                    @can("supprimer_ecran_" . ($ecran->id ?? 0))
                      <li>
                        <button class="dropdown-item text-danger" type="button" onclick="deleteMO({{ $execution->id }})">
                          <i class="bi bi-trash"></i> Supprimer
                        </button>
                      </li>
                    @endcan
                  </ul>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- JS --}}
<script>
  function goBack(){ window.history.back(); }
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof initDataTable === 'function') {
      initDataTable('{{ auth()->user()->acteur?->libelle_court }} {{ auth()->user()->acteur?->libelle_long }}', 'table1', "Liste des maîtres d’œuvre");
    }
  });
</script>

<script>
  const typeSelect   = document.getElementById('reatt_type_projet');
  const projetSelect = document.getElementById('projetSelect');

  async function reloadProjetOptions(type, preselect=null){
    projetSelect.innerHTML = '<option value="">Chargement...</option>';
    try {
      const url = @json(route('reattribution.optionsProjets')) + '?type=' + encodeURIComponent(type || 'PROJET');
      const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
      const data = await res.json();

      if (preselect && !(data||[]).some(d => d.code === preselect)) {
        const opt = document.createElement('option');
        opt.value = preselect;
        opt.textContent = preselect;
        projetSelect.appendChild(opt);
      }

      projetSelect.innerHTML = '<option value="">-- Sélectionnez --</option>';
      (data || []).forEach(row => {
        const opt = document.createElement('option');
        opt.value = row.code;
        opt.textContent = row.code + (row.label ? ' — ' + row.label : '');
        projetSelect.appendChild(opt);
      });

      if (preselect && (data||[]).some(d => d.code === preselect)) {
        projetSelect.value = preselect;
      }
    } catch (e) {
      projetSelect.innerHTML = '<option value="">Erreur de chargement</option>';
    }
  }

  // Init
  document.addEventListener('DOMContentLoaded', () => reloadProjetOptions(typeSelect.value));
  typeSelect.addEventListener('change', () => { reloadProjetOptions(typeSelect.value); hideProjetCard(); });

  // Quand on choisit un projet : carte et préchargement exécution
  projetSelect.addEventListener('change', function(){
    const code = this.value;
    if (!code) { hideProjetCard(); return; }

    const autoType = detectTypeFromCode(code);

    if (autoType !== typeSelect.value) {
    typeSelect.value = autoType;
    // on recharge la liste du bon type puis on remet la valeur choisie
    reloadProjetOptions(autoType, code);
    }

    loadProjetCardDetails(code);

    fetch(@json(url('/get-execution-by-projet')) + '/' + encodeURIComponent(code))
      .then(r => r.json())
      .then(data => { if (data) editMO(data); else resetFormForCreate(code); })
      .catch(()=> resetFormForCreate(code));
  });

  function hideProjetCard(){ document.getElementById('projetInfoCard').style.display='none'; }

  function fillProjetCard(d) {
    const safe = v => (v ?? '').toString().trim() || '-';
    document.getElementById('card_libelle').textContent = safe(d.libelle_projet);
    document.getElementById('card_nature').textContent  = safe(d.nature);
    document.getElementById('card_domaine').textContent = safe(d.domaine);
    document.getElementById('card_sousDomaine').textContent = safe(d.sousDomaine);
    document.getElementById('card_date_demarrage_prevue').textContent = safe(d.date_demarrage_prevue);
    document.getElementById('card_date_fin_prevue').textContent = safe(d.date_fin_prevue);
    document.getElementById('card_devise').textContent = safe(d.devise);
    document.getElementById('card_cout').textContent = d.cout ? new Intl.NumberFormat('fr-FR').format(d.cout) : '-';

    document.getElementById('projetInfoCard').style.display = '';
  }

  function loadProjetCardDetails(code){
    fetch(@json(url('/getProjetADeleted')) + '/' + encodeURIComponent(code))
      .then(r => r.json())
      .then(d => { if (d) fillProjetCard(d); else hideProjetCard(); })
      .catch(()=> hideProjetCard());
  }

  // Mappe tes préfixes → type fonctionnel.
    // Ajoute/édite les lignes si tes préfixes diffèrent.
    const PREFIX_TO_TYPE = [
        { prefix: 'ET_',     type: 'ETUDE'  },
        { prefix: 'APPUI_',  type: 'APPUI'  },
    ];

    // Retourne PROJET si aucun préfixe spécial ne matche
    function detectTypeFromCode(code) {
        if (!code) return 'PROJET';
        const hit = PREFIX_TO_TYPE.find(p => code.startsWith(p.prefix));
        return hit ? hit.type : 'PROJET';
    }
</script>

<script>
  // Règles d’affichage du secteur
  const REQUIRES_SECTEUR_ACTOR_CODES = new Set(['5689']);
  function updateSecteurVisibility() {
    const selectedActor = $('#acteurMoeSelect').val();
    const isPrive = $('input[name="type_ouvrage"]:checked').val() === 'Privé';
    const isEntreprise = $('input[name="priveMoeType"]:checked').val() === 'Entreprise';

    const opt = $('#acteurMoeSelect option:selected');
    const requiresByActor = (selectedActor && REQUIRES_SECTEUR_ACTOR_CODES.has(String(selectedActor)))
      || (opt.data('requires-secteur') === 1 || opt.data('requires-secteur') === '1');
    const requiresByType = isPrive && isEntreprise;

    $('#secteurContainer').toggle(requiresByActor);
  }

  $(document).on('change', '#acteurMoeSelect, .type_ouvrage, .type_prive', updateSecteurVisibility);

  // Charger la liste d’acteurs selon type public/privé
  function fetchActeurs(preselectValue) {
    const acteurSelect = document.getElementById('acteurMoeSelect');
    const typeOuvrage = document.querySelector('input[name="type_ouvrage"]:checked')?.value;
    const priveType   = document.querySelector('input[name="priveMoeType"]:checked')?.value;

    if (!typeOuvrage) return Promise.resolve();

    let url = `{{ url('/') }}/get-acteurs?type_ouvrage=${typeOuvrage}`;
    if (typeOuvrage === 'Privé' && priveType) url += `&priveMoeType=${priveType}`;

    return fetch(url)
      .then(r => r.json())
      .then(list => {
        acteurSelect.innerHTML = '<option value="">Sélectionnez un acteur</option>';
        list.forEach(a => {
          const opt = document.createElement('option');
          opt.value = a.code_acteur;
          opt.textContent = a.libelle_long;
          if (REQUIRES_SECTEUR_ACTOR_CODES.has(String(a.code_acteur)) || a.requires_secteur) {
            opt.dataset.requiresSecteur = '1';
          }
          acteurSelect.appendChild(opt);
        });
        if (preselectValue) acteurSelect.value = preselectValue;
        updateSecteurVisibility();
      });
  }

  // Soumission AJAX
  $('#moForm').on('submit', function(e) {
    e.preventDefault();
    const form   = $(this);
    const url    = form.attr('action');
    const method = form.find('input[name="_method"]').val() || 'POST';

    $.ajax({
      url: url,
      type: method,
      data: form.serialize(),
      success: function(res) {
        alert(res.success || 'Opération effectuée.');
        location.reload();
      },
      error: function(xhr) {
        if (xhr.status === 422 && xhr.responseJSON?.errors) {
          const errs = Object.values(xhr.responseJSON.errors).flat().join('\n');
          alert(errs);
        } else {
          alert(xhr.responseJSON?.error || 'Erreur serveur.');
        }
      }
    });
  });

  // Mettre le formulaire en CREATE
  function resetFormForCreate(codeProjet) {
    $('#execution_id').val('');
    $('#motif').val('');
    $('#acteurMoeSelect').val('');
    $('#sectActivEntMoe').val('');
    $('input[name="type_ouvrage"]').prop('checked', false);
    $('input[name="priveMoeType"]').prop('checked', false);
    $('#optionsMoePrive').addClass('d-none');

    // action/method
    $('#moForm').attr('action', '{{ route("maitre_ouvrage.store") }}');
    $('input[name="_method"]').val('POST');
    $('#formButton').text('Enregistrer');
  }

  // EDIT : bascule correctement en UPDATE (corrige le bug)
    async function editMO(data) {
    const code = data.code_projet || '';
    const detectedType = detectTypeFromCode(code);

    // 1) Fixe le type dans la ligne "Type de projet"
    document.getElementById('reatt_type_projet').value = detectedType;

    // 2) Recharge la liste des projets du type et présélectionne le code
    await reloadProjetOptions(detectedType, code);

    // 3) Préremplissage des champs
    $('#execution_id').val(data.id ?? '');
    $('#motif').val(data.motif ?? '');
    $('#projetSelect').val(code);

    // 4) Radios public/privé
    const typeActeur = data.acteur_type;
    const isPublic = ['eta','clt'].includes(typeActeur);
    $('#moePublic').prop('checked', isPublic);
    $('#moePrive').prop('checked', !isPublic);
    $('#optionsMoePrive').toggleClass('d-none', isPublic);

    // 5) Liste des acteurs selon public/privé, puis présélection
    await fetchActeurs(data.code_acteur ?? '');
    if ($('#sectActivEntMoe option[value="'+(data.secteur_id ?? '')+'"]').length) {
        $('#sectActivEntMoe').val(data.secteur_id);
    }
    updateSecteurVisibility();

    // 6) Bascule CREATE/UPDATE
    const $form = $('#moForm');
    if (data.id) {
        $form.attr('action', '{{ url("/reatributionProjet") }}' + '/' + data.id);
        $('input[name="_method"]').val('PUT');
        $('#formButton').text('Mettre à jour');
    } else {
        $form.attr('action', '{{ route("maitre_ouvrage.store") }}');
        $('input[name="_method"]').val('POST');
        $('#formButton').text('Enregistrer');
    }
    }

  // Delete
  function deleteMO(id) {
    const url = `{{ url('/reatributionProjet') }}/${id}`;
    if (!confirm('Confirmer la suppression ?')) return;
    $.ajax({
      url: url,
      type: 'DELETE',
      data: {_token: @json(csrf_token())},
      success: function(r){ alert(r.success); location.reload(); },
      error: function(xhr){ alert(xhr.responseJSON?.error || 'Erreur serveur.'); }
    });
  }

  // Radios : afficher sous-type privé + recharger acteurs
  document.querySelectorAll('.type_ouvrage').forEach(r => {
    r.addEventListener('change', () => {
      document.getElementById('optionsMoePrive').classList.toggle('d-none', r.value !== 'Privé');
      fetchActeurs();
    });
  });
  document.querySelectorAll('.type_prive').forEach(r => r.addEventListener('change', fetchActeurs));
</script>
@endsection
