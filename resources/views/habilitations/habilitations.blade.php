@extends('layouts.app')

@section('content')

{{-- Messages non bloquants --}}
@if (session('success'))
  <div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
  </div>
@endif
@if ($errors->any())
  <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <ul class="mb-0">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
  </div>
@endif

<style>
  .sub-row{background:#f8f9fa}
  .dropdown-toggle::after{display:none}
  .table th {background-color: #f1f5f9; color: #475569; font-weight: 600;}
  .main-row {background-color: #ffffff;}
  .table-hover tbody tr:hover {background-color: #f8fafc;}
  .badge-ecran {background-color: #e0f2fe; color: #0369a1;}
  .badge-rubrique {background-color: #f0f9ff; color: #0c4a6e;}
  .badge-sousmenu {background-color: #f0fdf4; color: #166534;}
  .icon-action {color: #6b7280; font-size: 1.1rem;}
  .icon-action.ajouter {color: #10b981;}
  .icon-action.modifier {color: #f59e0b;}
  .icon-action.supprimer {color: #ef4444;}
  .icon-action.consulter {color: #3b82f6;}
  .btn-expand {background-color: #e0f2fe; border-color: #bae6fd; color: #0369a1;}
  .btn-expand:hover {background-color: #bae6fd; border-color: #7dd3fc;}
  .form-check-input:checked {background-color: #3b82f6; border-color: #3b82f6;}
</style>

<section id="multiple-column-form">
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-12">
                <div class="breadcrumb-item" style="text-align:right;padding:5px;">
                    <span id="date-now" style="color:#34495E;"></span>
                </div>
            </div>
        </div>
        <div class="row align-items-center">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i> Plateforme</h3>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="">Gestion des habilitations</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Habilitations</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

  @can("consulter_ecran_" . $ecran->id)
  <div class="card shadow-sm border-0">
    <div class="card-header text-white">
      <h5 class="mb-0"><i class="bi bi-gear me-2"></i>Gestion des habilitations</h5>
    </div>
    <div class="card-body">
      <form id="habilitationsForm" method="POST" action="{{ route('role-assignment.assign') }}">
        @csrf
        <input type="hidden" name="ecran_id" value="{{ $ecran->id }}">

        <div class="row g-3 mb-4">
          <div class="row g-3 mb-4">
              {{-- Type utilisateur --}}
              <div class="col-sm-4">
                  <label for="type_utilisateur" class="form-label fw-semibold">
                      <i class="bi bi-person-badge me-1"></i>Type d'utilisateur
                  </label>
                  <select class="form-select" id="type_utilisateur">
                      <option value="">Tous les types</option>
                      @foreach($typesUtilisateurs as $type)
                          <option value="{{ $type->id }}">{{ $type->libelle }}</option>
                      @endforeach
                  </select>
              </div>

              {{-- Groupe utilisateur --}}
              <div class="col-sm-4">
                  <label for="role" class="form-label fw-semibold">
                      <i class="bi bi-people me-1"></i>Groupe utilisateur
                  </label>
                  <select class="form-select" id="role" name="role" required>
                      <option value="" selected>Sélectionnez un groupe utilisateur</option>
                      @foreach($roles as $r)
                          <option value="{{ $r->code }}"
                                  data-type="{{ $r->type_utilisateur_id }}">
                              {{ $r->libelle_groupe }}
                          </option>
                      @endforeach
                  </select>
              </div>
          </div>
          
          {{--<div class="col-sm-4">
            <label for="role" class="form-label fw-semibold"><i class="bi bi-people me-1"></i>Groupe utilisateur</label>
            <select class="form-select" id="role" name="role" required>
              <option value="" selected>Sélectionnez un groupe utilisateur</option>
              @foreach($roles as $r)
                <option value="{{ $r->code }}">{{ $r->libelle_groupe }}</option>
              @endforeach
            </select>
          </div>
        </div>--}}

        {{-- ========== BLOC INDEPENDANT : DROITS PAR TYPE DE PROJET ========== --}}
        <div class="card border-0 mb-3">
          <div class="card-header bg-white">
            <h6 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>Droits par type de projet</h6>
          </div>
          <div class="card-body">
            <div class="row gy-2">
              <div class="col-md-3">
                <div class="form-check">
                  <input class="form-check-input projet-type-cb" type="checkbox" id="typeINF" name="projetTypes[]" value="INF">
                  <label class="form-check-label" for="typeINF">
                    Projet d'infrastructure
                  </label>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-check">
                  <input class="form-check-input projet-type-cb" type="checkbox" id="typeAPP" name="projetTypes[]" value="APP">
                  <label class="form-check-label" for="typeAPP">
                    Projet d'appui 
                  </label>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-check">
                  <input class="form-check-input projet-type-cb" type="checkbox" id="typeETU" name="projetTypes[]" value="ETU">
                  <label class="form-check-label" for="typeETU">
                    Projet d'étude
                  </label>
                </div>
              </div>
            </div>
            <small class="text-muted d-block mt-2">
              Ces cases attribuent les permissions Spatie&nbsp;:
              <code>projettype.select.INF</code>, <code>.APP</code>, <code>.ETU</code> au groupe sélectionné.
            </small>
          </div>
        </div>
        {{-- ========== FIN BLOC TYPES ========== --}}

        <div class="table-responsive rounded">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th colspan="3"></th>
                <th colspan="4" class="text-center bg-light-primary">Autorisations</th>
              </tr>
              <tr>
                <th><i class="bi bi-hash me-1"></i>Code</th>
                <th><i class="bi bi-file-text me-1"></i>Page</th>
                <th><i class="bi bi-arrows-expand me-1"></i>+/-</th>

                {{-- Ajouter --}}
                <th class="text-center">
                  @can("ajouter_ecran_" . $ecran->id)
                    <div class="form-check d-flex justify-content-center align-items-center">
                      <input type="checkbox" id="checkAllAjouter" onclick="toggleAll('ajouter')" class="form-check-input me-2">
                      <label for="checkAllAjouter" class="form-check-label fw-semibold"><i class="bi bi-plus-circle icon-action ajouter me-1"></i>Ajouter</label>
                    </div>
                  @else
                    <span class="fw-semibold"><i class="bi bi-plus-circle icon-action ajouter me-1"></i>Ajouter</span>
                  @endcan
                </th>

                {{-- Modifier --}}
                <th class="text-center">
                  @can("modifier_ecran_" . $ecran->id)
                    <div class="form-check d-flex justify-content-center align-items-center">
                      <input type="checkbox" id="checkAllModifier" onclick="toggleAll('modifier')" class="form-check-input me-2">
                      <label for="checkAllModifier" class="form-check-label fw-semibold"><i class="bi bi-pencil-square icon-action modifier me-1"></i>Modifier</label>
                    </div>
                  @else
                    <span class="fw-semibold"><i class="bi bi-pencil-square icon-action modifier me-1"></i>Modifier</span>
                  @endcan
                </th>

                {{-- Supprimer --}}
                <th class="text-center">
                  @can("supprimer_ecran_" . $ecran->id)
                    <div class="form-check d-flex justify-content-center align-items-center">
                      <input type="checkbox" id="checkAllSupprimer" onclick="toggleAll('supprimer')" class="form-check-input me-2">
                      <label for="checkAllSupprimer" class="form-check-label fw-semibold"><i class="bi bi-trash icon-action supprimer me-1"></i>Supprimer</label>
                    </div>
                  @else
                    <span class="fw-semibold"><i class="bi bi-trash icon-action supprimer me-1"></i>Supprimer</span>
                  @endcan
                </th>

                {{-- Consulter --}}
                <th class="text-center">
                  @canany(["ajouter_ecran_" . $ecran->id, "modifier_ecran_" . $ecran->id, "supprimer_ecran_" . $ecran->id])
                    <div class="form-check d-flex justify-content-center align-items-center">
                      <input type="checkbox" id="checkAllConsulter" onclick="toggleAll('consulter')" class="form-check-input me-2">
                      <label for="checkAllConsulter" class="form-check-label fw-semibold"><i class="bi bi-eye icon-action consulter me-1"></i>Consulter</label>
                    </div>
                  @else
                    <span class="fw-semibold"><i class="bi bi-eye icon-action consulter me-1"></i>Consulter</span>
                  @endcanany
                </th>
              </tr>
            </thead>

            <tbody id="sousMenuTable_body">
              @foreach ($rubriques as $rubrique)
                {{-- Ligne Rubrique --}}
                <tr class="main-row">
                  <td class="fw-bold text-primary">{{ $rubrique->code }}</td>
                  <td class="fw-semibold">
                    <i class="bi bi-folder me-2 text-warning"></i>{{ $rubrique->libelle }}
                  </td>
                  <td>
                    <button type="button" class="btn btn-sm btn-expand rounded-circle"
                            onclick="toggleSubMenu(this, 'rubrique_sub_row_{{ $rubrique->code }}')">
                      <i class="bi bi-plus"></i>
                    </button>
                  </td>

                  {{-- Ajouter / Modifier / Supprimer: cellules vides au niveau Rubrique --}}
                  <td class="text-center"><i class="bi bi-dash text-muted"></i></td>
                  <td class="text-center"><i class="bi bi-dash text-muted"></i></td>
                  <td class="text-center"><i class="bi bi-dash text-muted"></i></td>

                  {{-- Consulter Rubrique --}}
                  <td class="text-center">
                    @canany(["ajouter_ecran_" . $ecran->id, "modifier_ecran_" . $ecran->id, "supprimer_ecran_" . $ecran->id])
                      <div class="form-check d-flex justify-content-center">
                        <input type="checkbox" name="consulterRubrique[]" value="{{ $rubrique->code }}" class="form-check-input">
                      </div>
                    @else
                      <div class="form-check d-flex justify-content-center">
                        <input type="checkbox" disabled class="form-check-input">
                      </div>
                    @endcanany
                  </td>
                </tr>

                {{-- Écrans de la rubrique --}}
                @foreach ($rubrique->ecrans as $e)
                  <tr class="rubrique_sub_row_{{ $rubrique->code }} sub-row" style="display:none">
                    <td class="text-muted ps-4">{{ $e->id }}</td>
                    <td class="ps-4">
                      <i class="bi bi-window me-2 text-info"></i>{{ $e->libelle }}
                      <span class="badge badge-ecran ms-2">Écran</span>
                    </td>
                    <td></td>

                    {{-- Ajouter --}}
                    <td class="text-center">
                      @can("ajouter_ecran_" . $ecran->id)
                        <div class="form-check d-flex justify-content-center">
                          <input type="checkbox" name="ajouterRubriqueEcran[]" value="{{ $e->id }}" class="form-check-input">
                        </div>
                      @else
                        <div class="form-check d-flex justify-content-center">
                          <input type="checkbox" disabled class="form-check-input">
                        </div>
                      @endcan
                    </td>

                    {{-- Modifier --}}
                    <td class="text-center">
                      @can("modifier_ecran_" . $ecran->id)
                        <div class="form-check d-flex justify-content-center">
                          <input type="checkbox" name="modifierRubriqueEcran[]" value="{{ $e->id }}" class="form-check-input">
                        </div>
                      @else
                        <div class="form-check d-flex justify-content-center">
                          <input type="checkbox" disabled class="form-check-input">
                        </div>
                      @endcan
                    </td>

                    {{-- Supprimer --}}
                    <td class="text-center">
                      @can("supprimer_ecran_" . $ecran->id)
                        <div class="form-check d-flex justify-content-center">
                          <input type="checkbox" name="supprimerRubriqueEcran[]" value="{{ $e->id }}" class="form-check-input">
                        </div>
                      @else
                        <div class="form-check d-flex justify-content-center">
                          <input type="checkbox" disabled class="form-check-input">
                        </div>
                      @endcan
                    </td>

                    {{-- Consulter --}}
                    <td class="text-center">
                      @canany(["ajouter_ecran_" . $ecran->id, "modifier_ecran_" . $ecran->id, "supprimer_ecran_" . $ecran->id])
                        <div class="form-check d-flex justify-content-center">
                          <input type="checkbox" name="consulterRubriqueEcran[]" value="{{ $e->id }}" class="form-check-input">
                        </div>
                      @else
                        <div class="form-check d-flex justify-content-center">
                          <input type="checkbox" disabled class="form-check-input">
                        </div>
                      @endcanany
                    </td>
                  </tr>
                @endforeach

                {{-- Sous-menus (récursif) --}}
                @include('partials.row', [
                  'sousMenus' => $rubrique->sousMenus,
                  'level'     => 1,
                  'ecranId'   => $ecran->id,
                ])
              @endforeach
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-end mt-4">
          @canany(["ajouter_ecran_" . $ecran->id, "modifier_ecran_" . $ecran->id, "supprimer_ecran_" . $ecran->id])
            <button type="submit" id="btn-submit" class="btn btn-primary">
              <i class="bi bi-check-lg me-2"></i><span class="label">Enregistrer</span>
              <span class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"></span>
            </button>
          @else
            <button type="button" class="btn btn-secondary" disabled title="Vous n'avez pas les droits pour modifier ces habilitations">
              <i class="bi bi-lock me-2"></i>Enregistrer
            </button>
          @endcanany
        </div>
      </form>
    </div>
  </div>
  @else
    <div class="alert alert-warning mt-3">
      <i class="bi bi-exclamation-triangle me-2"></i>Vous n'êtes pas autorisé à consulter cette page.
    </div>
  @endcan
</section>

<script>
  // Horloge
  setInterval(() => {
    const el = document.getElementById('date-now');
    if (el) el.textContent = new Date().toLocaleString();
  }, 1000);

  function toggleAll(type){
    let sel = '';
    switch(type){
      case 'ajouter':
        sel = 'input[name="ajouterRubriqueEcran[]"], input[name="ajouterSousMenuEcran[]"]'; break;
      case 'modifier':
        sel = 'input[name="modifierRubriqueEcran[]"], input[name="modifierSousMenuEcran[]"]'; break;
      case 'supprimer':
        sel = 'input[name="supprimerRubriqueEcran[]"], input[name="supprimerSousMenuEcran[]"]'; break;
      case 'consulter':
        sel = 'input[name="consulterRubrique[]"], input[name="consulterSousMenu[]"], input[name="consulterRubriqueEcran[]"], input[name="consulterSousMenuEcran[]"]'; break;
    }
    const master = document.getElementById('checkAll' + type.charAt(0).toUpperCase() + type.slice(1));
    if (!master) return;
    document.querySelectorAll(sel).forEach(cb => {
      if (!cb.disabled) cb.checked = master.checked;
    });
  }

  function toggleSubMenu(btn, className){
    const icon = btn.querySelector('i');
    document.querySelectorAll('.' + className).forEach(tr => {
      tr.style.display = (tr.style.display === 'none' || !tr.style.display) ? 'table-row' : 'none';
    });
    if (icon.classList.contains('bi-plus')) {
      icon.classList.remove('bi-plus');
      icon.classList.add('bi-dash');
    } else {
      icon.classList.remove('bi-dash');
      icon.classList.add('bi-plus');
    }
  }

  // Pré-cochage quand on change de rôle
  document.getElementById('role').addEventListener('change', function(){
    const roleId = this.value;
    // reset
    document.querySelectorAll('input[type="checkbox"]').forEach(cb => {
      if (!cb.disabled) cb.checked = false;
    });

    if (!roleId) return;

    fetch('{{ url('/') }}/get-role-permissions/' + roleId)
      .then(r => r.json())
      .then(data => {
        // Rubriques
        (data.rubriques || []).forEach(code => {
          const el = document.querySelector(`input[name="consulterRubrique[]"][value="${code}"]`);
          if (el && !el.disabled) el.checked = true;
        });

        // Sous-menus
        (data.sous_menus || []).forEach(code => {
          const el = document.querySelector(`input[name="consulterSousMenu[]"][value="${code}"]`);
          if (el && !el.disabled) el.checked = true;
        });

        // Écrans: consulter
        (data.ecrans_consulter || []).forEach(id => {
          const el = document.querySelector(
            `input[name="consulterRubriqueEcran[]"][value="${id}"], input[name="consulterSousMenuEcran[]"][value="${id}"]`
          );
          if (el && !el.disabled) el.checked = true;
        });

        // Écrans: CRUD
        (data.ecrans_ajouter || []).forEach(id => {
          const el = document.querySelector(
            `input[name="ajouterRubriqueEcran[]"][value="${id}"], input[name="ajouterSousMenuEcran[]"][value="${id}"]`
          );
          if (el && !el.disabled) el.checked = true;
        });
        (data.ecrans_modifier || []).forEach(id => {
          const el = document.querySelector(
            `input[name="modifierRubriqueEcran[]"][value="${id}"], input[name="modifierSousMenuEcran[]"][value="${id}"]`
          );
          if (el && !el.disabled) el.checked = true;
        });
        (data.ecrans_supprimer || []).forEach(id => {
          const el = document.querySelector(
            `input[name="supprimerRubriqueEcran[]"][value="${id}"], input[name="supprimerSousMenuEcran[]"][value="${id}"]`
          );
          if (el && !el.disabled) el.checked = true;
        });

        // >>> NOUVEAU : Types de projet
        document.querySelectorAll('.projet-type-cb').forEach(cb => cb.checked = false);
        (data.project_types || []).forEach(code => {
          const el = document.querySelector(`.projet-type-cb[value="${code}"]`);
          if (el) el.checked = true;
        });
      })
      .catch(console.error);
  });

  // Spinner UX
  const btn = document.getElementById('btn-submit');
  const form = document.getElementById('habilitationsForm');
  if (form && btn) {
    form.addEventListener('submit', function(){
      btn.disabled = true;
      const sp = btn.querySelector('.spinner-border');
      if (sp) sp.classList.remove('d-none');
      const lbl = btn.querySelector('.label');
      if (lbl) lbl.textContent = 'Enregistrement...';
    });
  }
</script>
<script>

  // --- Filtrage des groupes par type utilisateur ---
  (function () {
    const typeSelect = document.getElementById('type_utilisateur');
    const roleSelect = document.getElementById('role');

    if (!typeSelect || !roleSelect) return;

    // On sauve toutes les options originales (sauf le placeholder)
    const allRoleOptions = Array.from(roleSelect.querySelectorAll('option'))
      .map(opt => ({
        value: opt.value,
        text: opt.textContent,
        typeId: opt.getAttribute('data-type'),
        isPlaceholder: opt.value === ''
      }));

    // Quand on change de type
    typeSelect.addEventListener('change', function () {
      const selectedTypeId = this.value || '';

      // Reset du select role
      roleSelect.innerHTML = '';

      // On recrée le placeholder
      const placeholder = allRoleOptions.find(o => o.isPlaceholder);
      if (placeholder) {
        const opt = document.createElement('option');
        opt.value = '';
        opt.textContent = placeholder.text;
        roleSelect.appendChild(opt);
      }

      // On ajoute les groupes du type (ou tous si aucun type)
      allRoleOptions
        .filter(o => !o.isPlaceholder)
        .filter(o => selectedTypeId === '' || (o.typeId == selectedTypeId))
        .forEach(o => {
          const opt = document.createElement('option');
          opt.value = o.value;
          opt.textContent = o.text;
          opt.setAttribute('data-type', o.typeId ?? '');
          roleSelect.appendChild(opt);
        });

      // On vide les cochages existants dès qu'on change de type
      document.querySelectorAll('input[type="checkbox"]').forEach(cb => {
        if (!cb.disabled) cb.checked = false;
      });

      // On remet le select role à vide (il faudra choisir un groupe)
      roleSelect.value = '';
    });
  })();
</script>
<!--code d'utilisation de type de projet dans les blades et controler-->
<!-- 
@can('projettype.select', 'INF')
  <option value="INF">Projet d'infrastructure</option>
@endcan
@can('projettype.select', 'APP')
  <option value="APP">Projet d'appui</option>
@endcan
@can('projettype.select', 'ETU')
  <option value="ETU">Projet d'étude</option>
@endcan

if ($request->filled('projet_type_code')) {
    $code = strtoupper($request->input('projet_type_code'));
    abort_if(\Gate::denies('projettype.select', $code), 403, "Vous n'êtes pas autorisé à sélectionner le type $code.");
}
-->
@endsection
