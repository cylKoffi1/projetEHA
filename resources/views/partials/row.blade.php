@foreach ($sousMenus as $sm)
  {{-- Ligne Sous-menu --}}
  <tr class="rubrique_sub_row_{{ $sm->code_rubrique }} sub-row" style="display:none">
    <td class="fw-medium text-success">{{ $sm->code }}</td>
    <td class="ps-3">
      <span class="text-muted me-2">{{ str_repeat('— ', $level) }}</span>
      <i class="bi bi-folder2 me-2 text-success"></i>
      <span>{{ $sm->libelle }}</span>
      <span class="badge badge-sousmenu ms-2">Sous-menu</span>
    </td>
    <td class="text-center">
      @if($sm->ecrans->count() > 0 || ($sm->sousSousMenusRecursive && $sm->sousSousMenusRecursive->count() > 0))
        <button type="button" class="btn btn-sm btn-expand rounded-circle p-1" 
                style="width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;"
                onclick="toggleSubMenu(this, 'sm_sub_row_{{ $sm->code }}')" 
                title="Afficher/Masquer les éléments">
          <i class="bi bi-plus" style="font-size: 12px;"></i>
        </button>
      @endif
    </td>
    {{-- Pas d'action directe sur le SM pour CRUD --}}
    <td class="text-center"><i class="bi bi-dash text-muted"></i></td>
    <td class="text-center"><i class="bi bi-dash text-muted"></i></td>
    <td class="text-center"><i class="bi bi-dash text-muted"></i></td>
    <td class="text-center">
      @canany(["ajouter_ecran_" . $ecranId, "modifier_ecran_" . $ecranId, "supprimer_ecran_" . $ecranId])
        <div class="form-check d-flex justify-content-center">
          <input type="checkbox" name="consulterSousMenu[]" value="{{ $sm->code }}" 
                 class="form-check-input" id="consulter_sm_{{ $sm->code }}">
        </div>
      @else
        <div class="form-check d-flex justify-content-center">
          <input type="checkbox" class="form-check-input" disabled>
        </div>
      @endcanany
    </td>
  </tr>

  {{-- Écrans du sous-menu --}}
  @foreach ($sm->ecrans as $e)
    <tr class="sm_sub_row_{{ $sm->code }} sub-row" style="display:none; background-color: #fafbfc;">
      <td class="fw-medium text-muted ps-4">{{ $e->id }}</td>
      <td class="ps-4">
        <span class="text-muted me-2">{{ str_repeat('— ', $level+1) }}</span>
        <i class="bi bi-window me-2 text-info"></i>
        <span class="text-dark">{{ $e->libelle }}</span>
        <span class="badge badge-ecran ms-2">Écran</span>
      </td>
      <td></td>
      
      {{-- Ajouter --}}
      <td class="text-center">
        @can("ajouter_ecran_" . $ecranId)
          <div class="form-check d-flex justify-content-center">
            <input type="checkbox" name="ajouterSousMenuEcran[]" value="{{ $e->id }}" 
                   class="form-check-input" id="ajouter_sme_{{ $e->id }}">
          </div>
        @else
          <div class="form-check d-flex justify-content-center">
            <input type="checkbox" class="form-check-input" disabled>
          </div>
        @endcan
      </td>
      
      {{-- Modifier --}}
      <td class="text-center">
        @can("modifier_ecran_" . $ecranId)
          <div class="form-check d-flex justify-content-center">
            <input type="checkbox" name="modifierSousMenuEcran[]" value="{{ $e->id }}" 
                   class="form-check-input" id="modifier_sme_{{ $e->id }}">
          </div>
        @else
          <div class="form-check d-flex justify-content-center">
            <input type="checkbox" class="form-check-input" disabled>
          </div>
        @endcan
      </td>
      
      {{-- Supprimer --}}
      <td class="text-center">
        @can("supprimer_ecran_" . $ecranId)
          <div class="form-check d-flex justify-content-center">
            <input type="checkbox" name="supprimerSousMenuEcran[]" value="{{ $e->id }}" 
                   class="form-check-input" id="supprimer_sme_{{ $e->id }}">
          </div>
        @else
          <div class="form-check d-flex justify-content-center">
            <input type="checkbox" class="form-check-input" disabled>
          </div>
        @endcan
      </td>
      
      {{-- Consulter --}}
      <td class="text-center">
        @canany(["ajouter_ecran_" . $ecranId, "modifier_ecran_" . $ecranId, "supprimer_ecran_" . $ecranId])
          <div class="form-check d-flex justify-content-center">
            <input type="checkbox" name="consulterSousMenuEcran[]" value="{{ $e->id }}" 
                   class="form-check-input" id="consulter_sme_{{ $e->id }}">
          </div>
        @else
          <div class="form-check d-flex justify-content-center">
            <input type="checkbox" class="form-check-input" disabled>
          </div>
        @endcanany
      </td>
    </tr>
  @endforeach

  {{-- Descendance récursive --}}
  @if($sm->sousSousMenusRecursive && $sm->sousSousMenusRecursive->count())
    @include('partials.row', [
      'sousMenus' => $sm->sousSousMenusRecursive,
      'level'     => $level + 1,
      'ecranId'   => $ecranId,
    ])
  @endif
@endforeach