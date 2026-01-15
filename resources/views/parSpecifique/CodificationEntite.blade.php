@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- Flash Messages --}}
    @include('parSpecifique.partials.codification.flash-messages')

    <div class="row g-4">
        {{-- Colonne principale --}}
        <div class="col-lg-9">
            {{-- Formulaire de création/édition --}}
            @include('parSpecifique.partials.codification.form', [
                'paysAlpha3' => $paysAlpha3,
                'entityTypeOptions' => $entityTypeOptions
            ])

            {{-- Tableau des schémas existants --}}
            @include('parSpecifique.partials.codification.table', [
                'schemas' => $schemas
            ])
        </div>

        {{-- Panel latéral --}}
        <div class="col-lg-3">
            @include('parSpecifique.partials.codification.sidebar')
        </div>
    </div>
</div>

<style>
    /* ========== Pattern Builder Styles ========== */
    .pattern-builder {
        min-height: 52px;
        border: 2px dashed #dee2e6;
        border-radius: 0.5rem;
        padding: 0.5rem;
        display: flex;
        flex-wrap: wrap;
        gap: 0.375rem;
        align-items: center;
        background: #f8f9fa;
        transition: all 0.2s ease;
    }

    .pattern-builder:hover {
        border-color: #adb5bd;
        background: #fff;
    }

    .pattern-builder.drag-over {
        background: #e7f5ff;
        border-color: #339af0;
    }

    .pattern-builder-placeholder {
        color: #868e96;
        font-size: 0.875rem;
        font-style: italic;
    }

    /* ========== Token Chip Styles ========== */
    .pattern-token-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.25rem 0.625rem;
        border-radius: 1rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-size: 0.8125rem;
        font-weight: 500;
        font-family: 'Courier New', monospace;
        cursor: move;
        user-select: none;
        transition: all 0.2s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .pattern-token-chip:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .pattern-token-chip.dragging {
        opacity: 0.5;
        transform: scale(0.95);
        cursor: grabbing;
    }

    .pattern-token-chip .remove-chip {
        border: none;
        background: rgba(255, 255, 255, 0.3);
        color: white;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 1rem;
        line-height: 1;
    }

    .pattern-token-chip .remove-chip:hover {
        background: rgba(255, 255, 255, 0.5);
        transform: scale(1.1);
    }

    /* ========== Token Buttons (Sidebar) ========== */
    .token-button {
        font-family: 'Courier New', monospace;
        font-size: 0.8125rem;
        font-weight: 600;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .token-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .token-button:active {
        transform: translateY(0);
    }

    /* ========== Pattern Preview ========== */
    .pattern-preview {
        font-family: 'Courier New', monospace;
        font-size: 0.9rem;
        color: #495057;
        background: #f8f9fa;
        padding: 0.5rem;
        border-radius: 0.25rem;
        display: inline-block;
        min-width: 100px;
    }

    .pattern-preview.empty {
        color: #adb5bd;
        font-style: italic;
    }

    /* ========== Form Enhancements ========== */
    .form-label {
        font-weight: 500;
        color: #495057;
        margin-bottom: 0.5rem;
    }

    .form-label .text-muted {
        font-weight: 400;
        font-size: 0.875rem;
    }

    /* ========== Table Enhancements ========== */
    .table-hover tbody tr {
        transition: background-color 0.15s ease;
    }

    .action-buttons {
        display: flex;
        gap: 0.25rem;
    }

    /* ========== Card Shadows ========== */
    .card {
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        transition: box-shadow 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    /* ========== Animations ========== */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .alert {
        animation: fadeIn 0.3s ease;
    }

    /* ========== Responsive Adjustments ========== */
    @media (max-width: 768px) {
        .pattern-builder {
            min-height: 60px;
        }
        
        .action-buttons {
            flex-direction: column;
        }
    }
</style>
<script>
    /**
     * Codification Schema Builder
     * Gestion du drag & drop et de la construction de patterns avec valeurs dynamiques
     */

    class CodificationBuilder {
        constructor() {
            this.form = document.getElementById('schema-form');
            this.builder = document.getElementById('pattern-builder');
            this.patternInput = document.getElementById('pattern');
            this.sepInput = document.getElementById('token_separator');
            this.patternPreview = document.getElementById('pattern-preview');
            this.builderPlaceholder = document.getElementById('pattern-placeholder');
            
            // Form elements
            this.idField = document.getElementById('schema-id');
            this.entityTypeSelect = document.getElementById('entity_type');
            this.nameField = document.getElementById('name');
            this.activeChk = document.getElementById('active');
            
            // Buttons
            this.btnSubmit = document.getElementById('btn-submit');
            this.btnCancel = document.getElementById('btn-cancel-edit');
            
            // Title
            this.formTitle = document.getElementById('form-title');
            
            // Configuration des valeurs des tokens
            this.tokenValues = {};
            
            this.init();
        }

        init() {
            this.loadTokenConfiguration();
            this.setupTokenButtons();
            this.setupDragAndDrop();
            this.setupFormEvents();
            this.setupEditButtons();
            this.setupTokenValueModal();
            this.resetForm();
        }

        /**
         * Charger la configuration des valeurs de tokens depuis le backend
         */
        loadTokenConfiguration() {
            // Valeurs par défaut
            this.defaultTokenValues = {
                'PAYS': 'CIV',
                'DOMAINE': 'EHA',
                'SOUS_DOMAINE': 'RURAL',
                'TYPE': 'RF',
                'ANNEE': new Date().getFullYear().toString(),
                'MOIS': String(new Date().getMonth() + 1).padStart(2, '0'),
                'SEQ2': '01',
                'SEQ3': '001'
            };

            // Charger les configurations personnalisées si elles existent
            const savedConfig = localStorage.getItem('codification_token_values');
            if (savedConfig) {
                try {
                    this.tokenValues = JSON.parse(savedConfig);
                } catch (e) {
                    this.tokenValues = {};
                }
            }
        }

        /**
         * Sauvegarder la configuration des tokens
         */
        saveTokenConfiguration() {
            localStorage.setItem('codification_token_values', JSON.stringify(this.tokenValues));
        }

        /**
         * Configuration du modal de valeurs de tokens
         */
        setupTokenValueModal() {
            const modal = document.getElementById('tokenValueModal');
            if (!modal) return;

            const saveBtn = document.getElementById('save-token-value');
            const tokenSelect = document.getElementById('token-select');
            const valueInput = document.getElementById('token-value-input');

            // Charger la valeur actuelle quand on change de token
            tokenSelect?.addEventListener('change', () => {
                const token = tokenSelect.value;
                const currentValue = this.getTokenValue(token);
                if (valueInput) {
                    valueInput.value = currentValue;
                }
            });

            // Sauvegarder la nouvelle valeur
            saveBtn?.addEventListener('click', () => {
                const token = tokenSelect.value;
                const value = valueInput.value.trim();
                
                if (token && value) {
                    this.tokenValues[token] = value;
                    this.saveTokenConfiguration();
                    this.rebuildPattern();
                    
                    // Fermer le modal (Bootstrap)
                    const bsModal = bootstrap.Modal.getInstance(modal);
                    if (bsModal) bsModal.hide();
                    
                    this.showToast('Valeur mise à jour', 'success');
                }
            });
        }

        /**
         * Obtenir la valeur d'un token (personnalisée ou par défaut)
         */
        getTokenValue(token) {
            // Cas spécial pour TYPE : utiliser l'entity_type sélectionné
            if (token === 'TYPE' && this.entityTypeSelect.value) {
                return this.entityTypeSelect.value;
            }
            
            return this.tokenValues[token] || this.defaultTokenValues[token] || token;
        }

        /**
         * Configuration des boutons de tokens
         */
        setupTokenButtons() {
            document.querySelectorAll('.btn-token').forEach(btn => {
                btn.addEventListener('click', () => {
                    const token = btn.dataset.token;
                    this.addToken(token);
                });

                // Double-clic pour configurer la valeur
                btn.addEventListener('dblclick', (e) => {
                    e.preventDefault();
                    const token = btn.dataset.token;
                    this.openTokenValueEditor(token);
                });
            });

            // Changement de séparateur
            this.sepInput.addEventListener('input', () => {
                this.rebuildPattern();
            });

            // Mise à jour automatique quand on change l'entity_type
            this.entityTypeSelect.addEventListener('change', () => {
                this.rebuildPattern();
            });
        }

        /**
         * Ouvrir l'éditeur de valeur pour un token
         */
        openTokenValueEditor(token) {
            const modal = document.getElementById('tokenValueModal');
            if (!modal) {
                // Fallback : prompt simple
                const currentValue = this.getTokenValue(token);
                const newValue = prompt(`Définir la valeur pour {${token}}:`, currentValue);
                
                if (newValue !== null && newValue.trim() !== '') {
                    this.tokenValues[token] = newValue.trim();
                    this.saveTokenConfiguration();
                    this.rebuildPattern();
                    this.showToast(`Valeur de {${token}} mise à jour`, 'success');
                }
                return;
            }

            // Utiliser le modal Bootstrap
            const tokenSelect = document.getElementById('token-select');
            const valueInput = document.getElementById('token-value-input');
            const modalTitle = document.getElementById('tokenValueModalLabel');

            if (tokenSelect) tokenSelect.value = token;
            if (valueInput) valueInput.value = this.getTokenValue(token);
            if (modalTitle) modalTitle.textContent = `Configurer {${token}}`;

            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        }

        /**
         * Configuration du drag & drop
         */
        setupDragAndDrop() {
            this.builder.addEventListener('dragover', (e) => {
                e.preventDefault();
                this.builder.classList.add('drag-over');
                
                const dragging = this.builder.querySelector('.pattern-token-chip.dragging');
                if (!dragging) return;

                const afterElement = this.getDragAfterElement(e.clientX);
                if (afterElement == null) {
                    this.builder.appendChild(dragging);
                } else {
                    this.builder.insertBefore(dragging, afterElement);
                }
            });

            this.builder.addEventListener('dragleave', () => {
                this.builder.classList.remove('drag-over');
            });

            this.builder.addEventListener('drop', () => {
                this.builder.classList.remove('drag-over');
                this.rebuildPattern();
            });
        }

        /**
         * Configuration des événements du formulaire
         */
        setupFormEvents() {
            this.btnCancel.addEventListener('click', () => {
                this.resetForm();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });

            // Validation avant soumission
            this.form.addEventListener('submit', (e) => {
                if (!this.validateForm()) {
                    e.preventDefault();
                }
            });
        }

        /**
         * Configuration des boutons d'édition
         */
        setupEditButtons() {
            document.querySelectorAll('.btn-edit').forEach(btn => {
                btn.addEventListener('click', () => {
                    try {
                        const schema = JSON.parse(btn.dataset.schema);
                        this.loadSchema(schema);
                    } catch (error) {
                        console.error('Erreur lors du chargement du schéma:', error);
                        this.showToast('Erreur lors du chargement du schéma', 'danger');
                    }
                });
            });
        }

        /**
         * Ajouter un token au builder
         */
        addToken(token) {
            const chip = this.createChip(token);
            this.builder.appendChild(chip);
            this.rebuildPattern();
        }

        /**
         * Créer un chip de token
         */
        createChip(token) {
            const chip = document.createElement('span');
            chip.className = 'pattern-token-chip';
            chip.draggable = true;
            chip.dataset.token = token;
            chip.title = `Double-clic pour configurer la valeur`;
            chip.innerHTML = `
                <span class="token-label">{${token}}</span>
                <button type="button" class="remove-chip" aria-label="Retirer">&times;</button>
            `;

            // Événement de suppression
            chip.querySelector('.remove-chip').addEventListener('click', (e) => {
                e.stopPropagation();
                chip.remove();
                this.rebuildPattern();
            });

            // Double-clic pour éditer la valeur
            chip.addEventListener('dblclick', (e) => {
                e.preventDefault();
                this.openTokenValueEditor(token);
            });

            // Événements de drag
            chip.addEventListener('dragstart', () => {
                chip.classList.add('dragging');
            });

            chip.addEventListener('dragend', () => {
                chip.classList.remove('dragging');
                this.rebuildPattern();
            });

            return chip;
        }

        /**
         * Déterminer la position après laquelle insérer l'élément
         */
        getDragAfterElement(x) {
            const draggableElements = [
                ...this.builder.querySelectorAll('.pattern-token-chip:not(.dragging)')
            ];

            return draggableElements.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = x - (box.left + box.width / 2);
                
                if (offset < 0 && offset > closest.offset) {
                    return { offset, element: child };
                }
                return closest;
            }, { offset: Number.NEGATIVE_INFINITY }).element;
        }

        /**
         * Reconstruire le pattern à partir des chips
         */
        rebuildPattern() {
            const chips = this.builder.querySelectorAll('.pattern-token-chip');
            const tokens = Array.from(chips).map(chip => chip.dataset.token);
            const separator = this.sepInput.value || '';

            const pattern = tokens.length
                ? tokens.map(t => `{${t}}`).join(separator)
                : '';

            this.patternInput.value = pattern;
            this.updatePreview(pattern);
            this.updatePlaceholderVisibility();
        }

        /**
         * Mettre à jour l'aperçu du pattern
         */
        updatePreview(pattern) {
            if (!pattern) {
                this.patternPreview.textContent = 'Aucun pattern défini';
                this.patternPreview.classList.add('empty');
            } else {
                this.patternPreview.textContent = this.generateExample(pattern);
                this.patternPreview.classList.remove('empty');
            }
        }

        /**
         * Générer un exemple à partir du pattern avec les valeurs configurées
         */
        generateExample(pattern) {
            let example = pattern;
            
            // Remplacer chaque token par sa valeur
            const regex = /{([^}]+)}/g;
            let match;
            
            while ((match = regex.exec(pattern)) !== null) {
                const token = match[1];
                const value = this.getTokenValue(token);
                example = example.replace(`{${token}}`, value);
            }

            return example;
        }

        /**
         * Afficher/masquer le placeholder
         */
        updatePlaceholderVisibility() {
            const hasChips = this.builder.querySelector('.pattern-token-chip') !== null;
            if (this.builderPlaceholder) {
                this.builderPlaceholder.style.display = hasChips ? 'none' : 'inline';
            }
        }

        /**
         * Charger un pattern dans le builder
         */
        loadPatternIntoBuilder(pattern, separator) {
            this.clearBuilder();
            if (!pattern) return;

            const regex = /{([^}]+)}/g;
            let match;
            
            while ((match = regex.exec(pattern)) !== null) {
                const token = match[1];
                const chip = this.createChip(token);
                this.builder.appendChild(chip);
            }

            this.rebuildPattern();
        }

        /**
         * Vider le builder
         */
        clearBuilder() {
            this.builder.querySelectorAll('.pattern-token-chip').forEach(chip => chip.remove());
            this.rebuildPattern();
        }

        /**
         * Charger un schéma pour édition
         */
        loadSchema(schema) {
            this.idField.value = schema.id;

            // Entity type
            this.ensureOptionExists(schema.entity_type);
            this.entityTypeSelect.value = schema.entity_type;

            // Autres champs
            this.nameField.value = schema.name ?? '';
            this.sepInput.value = schema.token_separator ?? '_';
            this.activeChk.checked = !!schema.active;

            // Pattern
            this.patternInput.value = schema.pattern;
            this.loadPatternIntoBuilder(schema.pattern, this.sepInput.value);

            // UI
            this.formTitle.textContent = `Modifier le schéma #${schema.id}`;
            this.btnSubmit.textContent = 'Mettre à jour';
            this.btnCancel.classList.remove('d-none');

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        /**
         * S'assurer qu'une option existe dans le select
         */
        ensureOptionExists(value) {
            const exists = [...this.entityTypeSelect.options].some(o => o.value === value);
            if (!exists) {
                const option = new Option(`${value} (custom)`, value, true, true);
                this.entityTypeSelect.add(option);
            }
        }

        /**
         * Réinitialiser le formulaire
         */
        resetForm() {
            this.idField.value = '';
            this.entityTypeSelect.value = '';
            this.nameField.value = '';
            this.sepInput.value = '_';
            this.activeChk.checked = true;

            this.clearBuilder();
            this.patternInput.value = '';
            this.updatePreview('');

            this.formTitle.textContent = 'Nouveau schéma de codification';
            this.btnSubmit.textContent = 'Enregistrer';
            this.btnCancel.classList.add('d-none');
        }

        /**
         * Valider le formulaire
         */
        validateForm() {
            if (!this.entityTypeSelect.value) {
                this.showToast('Veuillez sélectionner un type d\'objet', 'warning');
                this.entityTypeSelect.focus();
                return false;
            }

            if (!this.patternInput.value) {
                this.showToast('Veuillez construire un pattern en ajoutant des tokens', 'warning');
                return false;
            }

            return true;
        }

        /**
         * Afficher un toast de notification
         */
        showToast(message, type = 'info') {
            // Si Bootstrap Toast est disponible
            const toastContainer = document.getElementById('toast-container');
            if (toastContainer) {
                const toastId = 'toast-' + Date.now();
                const toastHTML = `
                    <div id="${toastId}" class="toast align-items-center text-white bg-${type} border-0" role="alert">
                        <div class="d-flex">
                            <div class="toast-body">${message}</div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                        </div>
                    </div>
                `;
                toastContainer.insertAdjacentHTML('beforeend', toastHTML);
                
                const toastElement = document.getElementById(toastId);
                const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
                toast.show();
                
                toastElement.addEventListener('hidden.bs.toast', () => {
                    toastElement.remove();
                });
            } else {
                // Fallback : alert simple
                alert(message);
            }
        }
    }

    // Initialisation au chargement du DOM
    document.addEventListener('DOMContentLoaded', () => {
        new CodificationBuilder();
    });
</script>

@endsection