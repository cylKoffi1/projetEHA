@extends('layouts.app')

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-sm-12">
                    <li class="breadcrumb-item" style="list-style: none; text-align: right; padding: 5px; font-family: Arial, Helvetica, sans-serif;"><span id="date-now" style="color: #34495E; font-family: Verdana, Geneva, Tahoma, sans-serif; margin-left: 15px;"></span></li>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Projet </h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="">Gestion des exceptions</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Reattribution de projet</li>

                        </ol>
                    </nav>
                    <div class="row">
                        <script>
                            setInterval(function() {
                                document.getElementById('date-now').textContent = getCurrentDate();
                            }, 1000);

                            function getCurrentDate() {
                                // Implémentez la logique pour obtenir la date actuelle au format souhaité
                                var currentDate = new Date();
                                return currentDate.toLocaleString(); // Vous pouvez utiliser une autre méthode pour le formatage
                            }

                        </script>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row match-height">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3>Réattribution du Maître d’œuvre</h3>
                    </div>
                    <div class="card-body">
                        <form id="moForm" method="POST" action="{{ route('maitre_ouvrage.store') }}">
                            @csrf
                            <input type="hidden" name="ecran_id" value="{{ $ecran->id }}">
                            <input type="hidden" name="_method" value="POST">
                            <input type="hidden" name="execution_id" id="execution_id">

                            <div class="row">
                                <div class="col-4">
                                    <label for="projet_id">Projet</label>
                                    <select name="projet_id" class="form-control" required>
                                        <option value="">-- Sélectionnez --</option>
                                        @foreach($projets as $projet)
                                            <option value="{{ $projet->code_projet }}">{{ $projet->code_projet }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- Affichage des infos du MO actuel -->
                               <!-- DÉTAILS PROJET (identique à l'écran Annulation) -->
<div id="projetInfoCard" class="col-md-8" style="display:none; border:none;">
  <div class="card shadow-sm border-primary mb-3">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center" style="min-height:57px;">
      <div>
        <small class="d-block">Nature : <strong><span id="card_nature"></span></strong></small>
      </div>
      <div>
        <small class="d-block"><strong><span id="card_libelle"></span></strong></small>
      </div>
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
      </div> <!-- /row -->
    </div> <!-- /card-body -->
  </div> <!-- /card -->
</div>


                               
                            </div>

                            

                            <!-- Sélection d’acteur -->
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
                                <!-- Options Privé -->
                                <div class="col-3 d-none" id="optionsMoePrive">
                                    <label>Type de Privé *</label><br>
                                    <div class="col form-check ">
                                        <input class="form-check-input type_prive" type="radio" name="priveMoeType" id="moeEntreprise" value="Entreprise">
                                        <label class="form-check-label" for="moeEntreprise">Entreprise</label>
                                    </div>
                                    <div class="col form-check">
                                        <input class="form-check-input type_prive" type="radio" name="priveMoeType" id="moeIndividu" value="Individu">
                                        <label class="form-check-label" for="moeIndividu">Individu</label>
                                    </div>
                                </div>
                                <div class="col">
                                    <label for="acteur_id">Nouveau maître d'oeuvre *</label>
                                    <select name="acteur_id" id="acteurMoeSelect" class="form-control" required>
                                        <option value="">Sélectionnez un acteur</option>
                                        @foreach($acteurs as $acteur)
                                            <option value="{{ $acteur->code_acteur }}">{{ $acteur->libelle_court }} {{ $acteur->libelle_long }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Entité qui assure le rôle de Maître d’œuvre</small>
                                </div>

                                <div class="col" id="secteurContainer" style="display: none;">
                                    <label for="secteur_id">Secteur d’activité</label>
                                    <select name="secteur_id" id="sectActivEntMoe" class="form-control">
                                        <option value="">Sélectionnez...</option>
                                        @foreach ($SecteurActivites as $secteur)
                                            <option value="{{ $secteur->code }}">{{ $secteur->libelle }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-9">
                                    <label for="motif">Motif *</label>
                                    <textarea name="motif" id="motif" class="form-control" rows="2" required placeholder="Expliquer la raison de la réattribution."></textarea>
                                </div>
                                <div class="col text-end">
                                    @can("ajouter_ecran_" . $ecran->id)
                                    <button type="submit" class="btn btn-primary mt-3" id="formButton">Enregistrer</button>
                                    @endcan
                                </div>
                            </div>
                            
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <hr>

    <h5 class="mt-4">Maîtres d’œuvre existants</h5>
    <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="table1">
        <thead>
            <tr>
                <th>Code Projet</th>
                <th>Maître d’œuvre</th>
                <th>Type M.Œuvre</th>
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

                    {{-- Type Maître d’œuvre --}}
                    <td>
                        @if(in_array($execution->acteur->type_acteur, ['eta', 'clt']))
                            <span class="badge bg-success">Public</span>
                        @else
                            <span class="badge bg-secondary">Privé</span>
                        @endif
                    </td>

                    <td>{{ $execution->secteurActivite->libelle ?? '-' }}</td>
                    <td>{{ $execution->motif ?? '-' }}</td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="dropdownMenu{{ $execution->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                Actions
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenu{{ $execution->id }}">
                                @can("modifier_ecran_" . $ecran->id)
                                <li>
                                <button
                                    class="dropdown-item text-warning"
                                    type="button"
                                    onclick="editMO(@js([
                                        'id'            => $execution->id,
                                        'code_projet'   => $execution->code_projet,
                                        'code_acteur'   => $execution->code_acteur,
                                        'secteur_id'    => $execution->secteur_id,
                                        'secteur_libelle' => $execution->secteurActivite->libelle ?? null,
                                        'motif'         => $execution->motif,
                                        'acteur_type'   => $execution->acteur->type_acteur,
                                        'acteur_nom'    => trim(($execution->acteur->libelle_court ?? '').' '.($execution->acteur->libelle_long ?? '')),
                                    ]))">
                                    <i class="bi bi-pencil-square"></i> Modifier
                                    </button>
                                </li>
                                @endcan
                                @can("supprimer_ecran_" . $ecran->id)
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

<!-- SCRIPT -->
<script>
// Les acteurs qui imposent l’affichage du secteur (tu peux en ajouter)
const REQUIRES_SECTEUR_ACTOR_CODES = new Set(['5689']);

function updateSecteurVisibility() {
  const selectedActor = $('#acteurMoeSelect').val();
  const isPrive = $('input[name="type_ouvrage"]:checked').val() === 'Privé';
  const isEntreprise = $('input[name="priveMoeType"]:checked').val() === 'Entreprise';

  // 1) Règle "5689" (ou option marquée data-requires-secteur="1")
  const opt = $('#acteurMoeSelect option:selected');
  const requiresByActor =
      (selectedActor && REQUIRES_SECTEUR_ACTOR_CODES.has(String(selectedActor))) ||
      (opt.data('requires-secteur') === 1 || opt.data('requires-secteur') === '1');

  // 2) Règle métier (Privé + Entreprise)
  const requiresByType = isPrive && isEntreprise;

  $('#secteurContainer').toggle(requiresByActor || requiresByType);
}

// Binder une seule fois
$(document).on('change', '#acteurMoeSelect, .type_ouvrage, .type_prive', updateSecteurVisibility);
</script>
<script>
  function safe(v) { return (v ?? '').toString().trim() || '-'; }

  function fillProjetCard(d) {
    $('#card_libelle').text(safe(d.libelle_projet));
    $('#card_nature').text(safe(d.nature));
    $('#card_domaine').text(safe(d.domaine));
    $('#card_sousDomaine').text(safe(d.sousDomaine));
    $('#card_date_demarrage_prevue').text(safe(d.date_demarrage_prevue));
    $('#card_date_fin_prevue').text(safe(d.date_fin_prevue));
    $('#card_devise').text(safe(d.devise));

    const cout = d.cout ? new Intl.NumberFormat('fr-FR').format(d.cout) : '-';
    $('#card_cout').text(cout);

    $('#projetInfoCard').show();
  }

  function loadProjetCardDetails(codeProjet) {
    // ✅ Réutilise l’endpoint déjà présent sur l’écran Annulation
    // Si tu préfères, crée /get-projet-card/{code} qui renvoie le même JSON.
    fetch(`{{ url('/') }}/getProjetADeleted/${codeProjet}`)
      .then(r => r.json())
      .then(data => {
        if (!data) { $('#projetInfoCard').hide(); return; }
        fillProjetCard(data);
      })
      .catch(() => { $('#projetInfoCard').hide(); });
  }
</script>

<script>
    $(document).ready(function() {
        initDataTable('{{ auth()->user()->acteur?->libelle_court }} {{ auth()->user()->acteur?->libelle_long }}', 'table1', "Liste des maitres d'oeuvre")
    });

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

            // Marquage explicite côté client (en plus du Set ci-dessus)
            if (REQUIRES_SECTEUR_ACTOR_CODES.has(String(a.code_acteur)) || a.requires_secteur) {
            opt.dataset.requiresSecteur = '1';
            }
            acteurSelect.appendChild(opt);
        });

        // Pré-sélection après remplissage (évite que la valeur « saute »)
        if (preselectValue) acteurSelect.value = preselectValue;

        // Recalcule la visibilité du secteur après maj de la liste
        updateSecteurVisibility();
        });
    }



    $('select[name="projet_id"]').on('change', function () {
        const selectedProjet = $(this).val();
        if (!selectedProjet) return;
        
        loadProjetCardDetails(selectedProjet);
        fetch(`{{ url("/")}}/get-execution-by-projet/${selectedProjet}`)
            .then(response => response.json())
            .then(data => {
                if (!data) {
                    // Réinitialise le formulaire
                    $('#execution_id').val('');
                    $('#motif').val('');
                    $('#acteurMoeSelect').val('');
                    $('input[name="secteur_id"]').val('');
                    $('input[name="type_ouvrage"]').prop('checked', false);
                    $('input[name="priveMoeType"]').prop('checked', false);
                    $('#optionsMoePrive').addClass('d-none');
                    return;
                }
                console.log(data);
                editMO(data); 
            })
            .catch(err => {
                console.error('Erreur chargement exécution:', err);
            });

    });

    document.addEventListener("DOMContentLoaded", function () {
        const acteurSelect = document.getElementById("acteurMoeSelect");
        const secteurContainer = document.getElementById("secteurContainer");



        document.querySelectorAll('.type_ouvrage').forEach(input => {
            input.addEventListener('change', () => {
                document.getElementById('optionsMoePrive').classList.toggle('d-none', input.value !== 'Privé');
                fetchActeurs();
            });
        });

        document.querySelectorAll('.type_prive').forEach(input => {
            input.addEventListener('change', fetchActeurs);
        });

        acteurSelect.addEventListener('change', function () {
            secteurContainer.style.display = (this.value === '5689') ? 'block' : 'none';
        });
    });

    $('#moForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const url = form.attr('action');
        const method = form.find('input[name="_method"]').val() || 'POST';

        $.ajax({
            url: url,
            type: method,
            data: form.serialize(),
            success: function(response) {
                alert(response.success);
                location.reload();
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.error || 'Erreur serveur.');
            }
        });
    });
    function toggleSecteurContainer() {
        const isPrive = $('input[name="type_ouvrage"]:checked').val() === 'Privé';
        const isEntreprise = $('input[name="priveMoeType"]:checked').val() === 'Entreprise';
        $('#secteurContainer').toggle(isPrive && isEntreprise);
    }
    $('.type_ouvrage, .type_prive').on('change', toggleSecteurContainer);
    async function editMO(data) {
  // Affiche carte d’infos
  $('#moeInfosCard').show();

  // Nom acteur : on prend acteur_nom s’il existe, sinon on reconstruit proprement
  const lc = data.acteur?.libelle_court ?? '';
  const ll = data.acteur?.libelle_long ?? '';
  const nomReconstruit = (lc || ll) ? `${lc} ${ll}`.trim() : null;
  const acteurNom = data.acteur_nom ?? nomReconstruit ?? data.code_acteur ?? '-';
  $('#moe-acteur').text(acteurNom);

  // Type public/privé : on utilise ce qu’on a (priorité à acteur_type à plat)
  const typeActeur = data.acteur_type ?? data.acteur?.type_acteur;
  $('#moe-type').text(['eta','clt'].includes(typeActeur) ? 'Public' : 'Privé');

  // Secteur (libellé si fourni, sinon on cherche le texte de l’option)
  const secVal   = data.secteur_id ?? '';
  const secText  = data.secteur_libelle 
                   ?? $('#sectActivEntMoe option[value="'+secVal+'"]').text() 
                   ?? secVal 
                   ?? '-';
  $('#moe-secteur').text(secText || '-');

  // Champs simples
  $('#execution_id').val(data.id ?? '');
  $('#motif').val(data.motif ?? '');
  $('select[name="projet_id"]').val(data.code_projet); // ne pas trigger change ici

  // Radios
  const isPublic = ['eta','clt'].includes(typeActeur);
  $('#moePublic').prop('checked', isPublic);
  $('#moePrive').prop('checked', !isPublic);
  if (!isPublic) {
    $('#optionsMoePrive').removeClass('d-none');
    if (data.prive_type === 'Entreprise' || (!data.prive_type && secVal)) {
      $('#moeEntreprise').prop('checked', true);
    } else if (data.prive_type === 'Individu') {
      $('#moeIndividu').prop('checked', true);
    } else {
      $('.type_prive').prop('checked', false);
    }
  } else {
    $('#optionsMoePrive').addClass('d-none');
    $('.type_prive').prop('checked', false);
  }

  // Recharge la liste des acteurs selon le type choisi puis présélectionne l’acteur
  await fetchActeurs(data.code_acteur ?? '');

  // Applique la valeur du secteur si l’option existe
  if ($('#sectActivEntMoe option[value="'+secVal+'"]').length) {
    $('#sectActivEntMoe').val(secVal);
  } else {
    $('#sectActivEntMoe').val('');
  }

  // Recalcule la visibilité (règle 5689 + Privé/Entreprise)
  updateSecteurVisibility();

  // Mode create (si voulu)
  $('#moForm').attr('action', '{{ route("maitre_ouvrage.store") }}');
  $('input[name="_method"]').val('POST');
  $('#formButton').text('Enregistrer');
}





    function deleteMO(id) {
    const url = `{{ url('/reatributionProjet') }}/${id}`;

    confirmDelete(url, 
        // onSuccess
        () => { location.reload(); }, 
        // messages (optionnels)
        {
            title: 'Confirmer la suppression',
            text: 'Voulez-vous supprimer cette réattribution de maître d’œuvre ?',
            successMessage: 'La réattribution a été supprimée.'
        }
    );
}

</script>
@endsection
