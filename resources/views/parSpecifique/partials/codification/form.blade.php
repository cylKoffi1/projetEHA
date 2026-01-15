<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0" id="form-title">
            <i class="bi bi-diagram-3 me-2"></i>
            Nouveau schéma de codification
        </h5>
        <button type="button" class="btn btn-sm btn-outline-secondary d-none" id="btn-cancel-edit">
            <i class="bi bi-x-circle me-1"></i>
            Annuler
        </button>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('codif.store') }}" id="schema-form">
            @csrf
            <input type="hidden" name="id" id="schema-id">
            <input type="hidden" name="pays_alpha3" value="{{ $paysAlpha3 }}">

            <div class="row g-3">
                {{-- Pays --}}
                <div class="col-md-3" style="display: none;">
                    <label class="form-label">
                        <i class="bi bi-flag me-1"></i>
                        Pays (Alpha3)
                    </label>
                    <input type="text" class="form-control" value="{{ $paysAlpha3 }}" disabled>
                </div>

                {{-- Type d'objet --}}
                <div class="col-md-4">
                    <label class="form-label">
                        <i class="bi bi-tag me-1"></i>
                        Type d'objet <span class="text-danger">*</span>
                    </label>
                    <select name="entity_type" id="entity_type" class="form-select" required>
                        <option value="">-- Sélectionner --</option>
                        @foreach($entityTypeOptions as $value => $label)
                            <option value="{{ $value }}"
                                {{ old('entity_type') == $value ? 'selected' : '' }}>
                                {{ $label }} ({{ $value }})
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i>
                        Cette valeur sera utilisée pour le token {TYPE}
                    </small>
                </div>

                {{-- Libellé --}}
                <div class="col-md-5">
                    <label class="form-label">
                        <i class="bi bi-card-text me-1"></i>
                        Libellé
                    </label>
                    <input type="text" 
                           name="name" 
                           id="name"
                           class="form-control"
                           value="{{ old('name') }}"
                           placeholder="Ex: Projet d'appui, Étude de projet...">
                </div>

                {{-- Builder visuel --}}
                <div class="col-12">
                    <label class="form-label">
                        <i class="bi bi-bricks me-1"></i>
                        Construction du modèle
                        <small class="text-muted">
                            (Cliquez pour ajouter, glissez-déposez pour réorganiser, double-clic pour configurer)
                        </small>
                    </label>
                    <div id="pattern-builder" class="pattern-builder">
                        <span class="pattern-builder-placeholder" id="pattern-placeholder">
                            Cliquez sur les tokens à droite pour construire votre modèle
                        </span>
                    </div>
                    <small class="text-muted d-block mt-1">
                        <i class="bi bi-lightbulb me-1"></i>
                        <strong>Astuce :</strong> Double-cliquez sur un token (bouton ou chip) pour personnaliser sa valeur d'exemple
                    </small>
                </div>

                {{-- Pattern généré --}}
                <div class="col-md-6">
                    <label class="form-label">
                        <i class="bi bi-code-square me-1"></i>
                        Pattern généré
                    </label>
                    <input type="text" 
                           name="pattern" 
                           id="pattern"
                           class="form-control font-monospace" 
                           readonly>
                </div>

                {{-- Aperçu dynamique --}}
                <div class="col-md-6">
                    <label class="form-label">
                        <i class="bi bi-eye me-1"></i>
                        Aperçu dynamique
                    </label>
                    <div class="form-control bg-light">
                        <span id="pattern-preview" class="pattern-preview empty">
                            Aucun pattern défini
                        </span>
                    </div>
                    <small class="text-muted">
                        Mise à jour automatique selon vos configurations
                    </small>
                </div>

                {{-- Séparateur --}}
                <div class="col-md-3">
                    <label class="form-label">
                        <i class="bi bi-dash-lg me-1"></i>
                        Séparateur
                    </label>
                    <input type="text" 
                           name="token_separator" 
                           id="token_separator"
                           class="form-control text-center font-monospace" 
                           value="{{ old('token_separator', '_') }}" 
                           maxlength="5">
                    <small class="text-muted">Ex: _ - . /</small>
                </div>

                {{-- Actif --}}
                <div class="col-md-3 d-flex align-items-end">
                    <div class="form-check form-switch">
                        <input type="checkbox" 
                               class="form-check-input" 
                               id="active" 
                               name="active"
                               {{ old('active', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="active">
                            <i class="bi bi-toggle-on me-1"></i>
                            Schéma actif
                        </label>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-outline-secondary d-none" id="btn-cancel-edit-bottom">
                    <i class="bi bi-x-circle me-1"></i>
                    Annuler
                </button>
                <button type="submit" class="btn btn-primary" id="btn-submit">
                    <i class="bi bi-check-circle me-1"></i>
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>