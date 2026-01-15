<div class="sticky-top" style="top: 1rem;">
    {{-- Tokens disponibles --}}
    <div class="card mb-3">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="bi bi-puzzle me-2"></i>
                Tokens disponibles
            </h6>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">
                <i class="bi bi-info-circle me-1"></i>
                <strong>Simple clic :</strong> Ajouter au pattern<br>
                <i class="bi bi-gear me-1"></i>
                <strong>Double clic :</strong> Configurer la valeur
            </p>

            <div class="d-flex flex-wrap gap-2 mb-3">
                <button type="button" 
                        class="btn btn-outline-primary btn-sm token-button btn-token" 
                        data-token="PAYS"
                        title="Simple clic: ajouter | Double clic: configurer">
                    {PAYS}
                </button>
                <button type="button" 
                        class="btn btn-outline-primary btn-sm token-button btn-token" 
                        data-token="DOMAINE"
                        title="Simple clic: ajouter | Double clic: configurer">
                    {DOMAINE}
                </button>
                <button type="button" 
                        class="btn btn-outline-primary btn-sm token-button btn-token" 
                        data-token="SOUS_DOMAINE"
                        title="Simple clic: ajouter | Double clic: configurer">
                    {SOUS_DOMAINE}
                </button>
                <button type="button" 
                        class="btn btn-outline-info btn-sm token-button btn-token" 
                        data-token="TYPE"
                        title="Simple clic: ajouter | Valeur: entity_type sélectionné">
                    {TYPE}
                    <i class="bi bi-link-45deg small"></i>
                </button>
                <button type="button" 
                        class="btn btn-outline-primary btn-sm token-button btn-token" 
                        data-token="ANNEE"
                        title="Simple clic: ajouter | Double clic: configurer">
                    {ANNEE}
                </button>
                <button type="button" 
                        class="btn btn-outline-primary btn-sm token-button btn-token" 
                        data-token="MOIS"
                        title="Simple clic: ajouter | Double clic: configurer">
                    {MOIS}
                </button>
                <button type="button" 
                        class="btn btn-outline-primary btn-sm token-button btn-token" 
                        data-token="SEQ2"
                        title="Simple clic: ajouter | Double clic: configurer">
                    {SEQ2}
                </button>
                <button type="button" 
                        class="btn btn-outline-primary btn-sm token-button btn-token" 
                        data-token="SEQ3"
                        title="Simple clic: ajouter | Double clic: configurer">
                    {SEQ3}
                </button>
            </div>

            <div class="alert alert-info small py-2 mb-0">
                <i class="bi bi-lightbulb-fill me-1"></i>
                <strong>Note :</strong> Le token {TYPE} utilise automatiquement la valeur du type d'objet sélectionné.
            </div>
        </div>
    </div>

    {{-- Exemple --}}
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="bi bi-book me-2"></i>
                Exemple
            </h6>
        </div>
        <div class="card-body small">
            <p class="mb-2">
                <strong>Pattern :</strong>
            </p>
            <div class="bg-light p-2 rounded font-monospace mb-2" style="font-size: 0.75rem;">
                {PAYS}_{DOMAINE}_{TYPE}_{ANNEE}_{MOIS}_{SEQ3}
            </div>
            
            <p class="mb-2">
                <strong>Résultat :</strong>
            </p>
            <div class="bg-success bg-opacity-10 text-success p-2 rounded font-monospace fw-bold" style="font-size: 0.8rem;">
                CIV_EHA_RF_2025_11_001
            </div>
            
            <hr class="my-2">
            
            <p class="text-muted mb-0" style="font-size: 0.7rem;">
                Où RF = type d'objet sélectionné<br>
                Et 001 = séquence auto-incrémentée
            </p>
        </div>
    </div>
</div>
