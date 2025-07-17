@php
    $valeur = $valeurs[$carac->idCaracteristique] ?? null;
    $type = strtolower($carac->type->libelleTypeCaracteristique ?? '');
    $name = "caracteristiques[" . ($valeur?->idValeurCaracteristique ?? 'new_' . $carac->idCaracteristique) . "]";
    $val = $valeur?->valeur ?? '';
    $valeursPossibles = $carac->valeursPossibles ?? [];
    $unite = $valeur?->unite?->symbole ?? $carac->unite?->symbole ?? null;
    $hasChildren = $carac->enfants->isNotEmpty();
    $toggleId = 'carac-children-' . $carac->idCaracteristique;
    $itemId = 'carac-item-' . $carac->idCaracteristique;
    $isEven = ($index ?? 0) % 2 === 0;
    $isLast = ($index ?? 0) === ($totalItems ?? 1) - 1;

    $idUniteRef = $carac->unite?->idUnite ?? null;
    $selectedUniteId = $valeur?->uniteDerivee?->id ?? ($idUniteRef && isset($unitesDerivees[$idUniteRef]) && count($unitesDerivees[$idUniteRef]) > 0
        ? $unitesDerivees[$idUniteRef][0]->id
        : null);
@endphp


@if($loop->index % 2 === 0)
    <div class="row caracteristique-row">
@endif

<div class="col-md-6 caracteristique-col">
    <div class="caracteristique-card card shadow-sm mb-3" 
         id="{{ $itemId }}"
         data-type="{{ $type }}"
         data-level="{{ $level }}"
         style="border-left: 4px solid {{ $level > 0 ? '#3a7bd5' : '#6c757d' }};">
        
        <div class="card-body">
            <!-- En-tête de la carte -->
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="d-flex align-items-center flex-grow-1">
                    <!-- Bouton toggle pour les enfants -->
                    @if($hasChildren)
                        <button type="button" 
                                class="btn btn-sm btn-toggle me-2" 
                                data-toggle-target="{{ $toggleId }}"
                                data-expanded="false">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    @else
                        <span class="no-children-spacer me-2"></span>
                    @endif
                    
                    <!-- Titre et icône -->
                    <h5 class="card-title mb-0">
                        {{ $carac->libelleCaracteristique }}
                        @if($carac->description)
                            <i class="bi bi-info-circle text-primary ms-1" 
                               data-tooltip="{{ $carac->description }}"></i>
                        @endif
                    </h5>
                </div>
                
                <!-- Badge type -->
                <span class="badge bg-type-{{ $type }}">
                    @switch($type)
                        @case('nombre') <i class="bi bi-123"></i> @break
                        @case('liste') <i class="bi bi-list-ul"></i> @break
                        @case('boolean') <i class="bi bi-toggle-on"></i> @break
                        @default <i class="bi bi-text-paragraph"></i>
                    @endswitch
                </span>
            </div>
            
            <!-- Contenu de la caractéristique -->
            <div class="caracteristique-content">
                @if($type === 'liste')
                    <select name="{{ $name }}" class="form-select form-select-sm mb-2">
                        <option value="">Sélectionner une valeur</option>
                        @foreach($valeursPossibles as $option)
                            <option value="{{ $option->valeur }}" {{ $option->valeur == $val ? 'selected' : '' }}>
                                {{ $option->valeur }}
                            </option>
                        @endforeach
                    </select>
                @elseif($type === 'boolean')
                    <div class="form-check form-switch">
                        <input type="hidden" name="{{ $name }}" value="0">
                        <input type="checkbox" class="form-check-input" name="{{ $name }}" 
                               value="1" {{ $val == 1 ? 'checked' : '' }}>
                        <label class="form-check-label">Oui / Non</label>
                    </div>
                @elseif($type === 'nombre')
                <div class="input-group input-group-sm mb-2">
                    <input type="number" step="any" name="{{ $name }}" value="{{ $val }}" 
                        class="form-control" placeholder="Valeur numérique">
                    
                    @if($unite)
                        <span class="input-group-text">{{ $unite }}</span>
                        @php
                            $unitList = $unitesDerivees[$carac->unite->idUnite] ?? [];
                        @endphp
                        @if(count($unitList))
                            <div class="unite-roller-container" data-id="{{ $carac->idCaracteristique }}">
                            <div class="unite-roller-libelle">—</div>    
                            <div class="unite-roller-list" data-name="unites_derivees[{{ $carac->idCaracteristique }}]">
                                    @foreach($unitList as $der)
                                        <div class="unite-roller-item {{ $der->id == $selectedUniteId ? 'selected' : '' }}"
                                            data-id="{{ $der->id }}"
                                            data-symbol="{{ $der->code }}"
                                            data-libelle="{{ $der->libelle }}">
                                            {{ $der->code }}
                                        </div>
                                    @endforeach
                                </div>
                                <input type="hidden" name="unites_derivees[{{ $carac->idCaracteristique }}]" value="{{ $selectedUniteId }}">
                            </div>

                        @endif
                    @endif
                </div>

                @else
                    <input type="text" name="{{ $name }}" value="{{ $val }}" 
                           class="form-control form-control-sm mb-2" placeholder="Valeur textuelle">
                @endif
            </div>
            
            <!-- Pied de carte -->
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    @if($valeur && $valeur->updated_at)
                        <i class="bi bi-clock-history"></i> Modifié {{ $valeur->updated_at->diffForHumans() }}
                    @else
                        <i class="bi bi-plus-circle"></i> Non renseigné
                    @endif
                </small>
                
                @if($hasChildren)
                    <small class="text-primary">
                        <i class="bi bi-collection"></i> {{ $carac->enfants->count() }} sous-éléments
                    </small>
                @endif
            </div>
        </div>
    </div>
</div>

@if($loop->index % 2 === 1 || $loop->last)
    </div>
@endif

<!-- Affichage des enfants -->
@if($hasChildren)
    <div class="row children-container" id="{{ $toggleId }}" style="display: none;">
        <div class="col-12">
            <div class="ps-4" style="border-left: 2px dashed #dee2e6;">
                @php
                    $children = $carac->enfants->sortBy('ordre');
                    $childrenCount = $children->count();
                @endphp
                
                @foreach($children as $childIndex => $child)
                    @include('infrastructures.partials.caracteristique-edit', [
                        'carac' => $child,
                        'valeurs' => $valeurs,
                        'unitesDerivees' => $unitesDerivees,
                        'level' => $level + 1,
                        'index' => $childIndex,
                        'totalItems' => $childrenCount
                    ])
                @endforeach
            </div>
        </div>
    </div>
@endif

<style>
    .caracteristique-card {
        transition: all 0.3s ease;
        border-radius: 8px;
        overflow: hidden;
    }

    .children-container {
        margin-top: -10px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }

    .children-container.show {
        display: block !important;
    }

    .caracteristique-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
    }

    .caracteristique-card .card-title {
        font-size: 0.95rem;
        font-weight: 600;
        color: #2c3e50;
    }

    .caracteristique-card .badge {
        font-size: 0.7rem;
        font-weight: 500;
        padding: 4px 8px;
    }

    .bg-type-nombre { background-color: #4e73df; }
    .bg-type-liste { background-color: #1cc88a; }
    .bg-type-boolean { background-color: #f6c23e; }
    .bg-type-texte { background-color: #e74a3b; }

    .btn-toggle {
        width: 28px;
        height: 28px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background-color: rgba(58, 123, 213, 0.1);
        color: #3a7bd5;
        border: none;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .btn-toggle:hover {
        background-color: rgba(58, 123, 213, 0.2);
    }

    .btn-toggle[data-expanded="true"] i {
        transform: rotate(90deg);
    }

    .btn-toggle i {
        transition: transform 0.3s ease;
    }

    .no-children-spacer {
        display: inline-block;
        width: 28px;
        height: 28px;
    }

    .caracteristique-row {
        margin-bottom: 15px;
    }

    .caracteristique-col {
        padding-left: 8px;
        padding-right: 8px;
    }

    /* Styles pour le roller picker des unités */
    .unite-roller-container {
        position: relative;
        width: 72px;
        height: 32px;
        overflow-y: auto;
        margin: 0 auto;
        border-radius: 20px;
        background: linear-gradient(145deg, #f0f0f0, #cacaca);
        box-shadow: inset 5px 5px 15px #b8b8b8, inset -5px -5px 15px #ffffff;
        scroll-snap-type: y mandatory;
    }
    .unite-roller-list::before,
    .unite-roller-list::after {
        content: "";
        display: block;
        height: 32px; /* ajustable selon la hauteur du conteneur */
        flex-shrink: 0;
    }

    .unite-roller-list {
        display: flex;
        flex-direction: column;
        align-items: center;
        scroll-behavior: smooth;
    }

    .unite-roller-item {
        scroll-snap-align: center;
        font-size: 1.2rem;
        padding: 10px 0;
        opacity: 0.4;
        transition: all 0.3s ease;
        color: #333;
        position: relative;
    }

    .unite-roller-item.selected {
        font-size: 20px;
        font-weight: 700;
        color: #000;
        opacity: 1;
        transform: scale(0.8);

    }

    .unite-roller-libelle {
        position: fixed;
        top: 50px;
        left: 73%;
        transform: translateX(-50%);
        background-color: #444;
        color: white;
        font-size: 0.7rem;
        padding: 4px 8px;
        border-radius: 6px;
        white-space: nowrap;
        z-index: 1000;
        opacity: 0.9;
        pointer-events: none;
    }

</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des tooltips personnalisés
    document.querySelectorAll('[data-tooltip]').forEach(el => {
        const tooltipText = el.getAttribute('data-tooltip');
        if (tooltipText) {
            el.addEventListener('mouseenter', function(e) {
                const tooltip = document.createElement('div');
                tooltip.className = 'custom-tooltip';
                tooltip.textContent = tooltipText;
                tooltip.style.cssText = `
                    position: absolute;
                    background: #333;
                    color: white;
                    padding: 5px 10px;
                    border-radius: 4px;
                    font-size: 12px;
                    z-index: 1000;
                    pointer-events: none;
                    max-width: 200px;
                    word-wrap: break-word;
                `;
                document.body.appendChild(tooltip);
                
                const rect = el.getBoundingClientRect();
                tooltip.style.left = rect.left + 'px';
                tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
                
                el._tooltip = tooltip;
            });
            
            el.addEventListener('mouseleave', function() {
                if (el._tooltip) {
                    document.body.removeChild(el._tooltip);
                    el._tooltip = null;
                }
            });
        }
    });
    
    // Gestion du highlight sur changement
    document.querySelectorAll('.caracteristique-content input, .caracteristique-content select').forEach(el => {
        el.addEventListener('change', function() {
            const card = this.closest('.caracteristique-card');
            card.style.boxShadow = '0 0 0 2px rgba(58, 123, 213, 0.5)';
            setTimeout(() => {
                card.style.boxShadow = '';
            }, 1000);
        });
    });

    // Gestion des boutons toggle sans Bootstrap
    document.querySelectorAll('.btn-toggle').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('data-toggle-target');
            const target = document.getElementById(targetId);
            const icon = this.querySelector('i');
            const isExpanded = this.getAttribute('data-expanded') === 'true';
            
            if (!target || !icon) return;
            
            if (isExpanded) {
                // Fermer
                target.style.display = 'none';
                target.classList.remove('show');
                icon.style.transform = 'rotate(0deg)';
                this.setAttribute('data-expanded', 'false');
            } else {
                // Ouvrir
                target.style.display = 'block';
                target.classList.add('show');
                icon.style.transform = 'rotate(90deg)';
                this.setAttribute('data-expanded', 'true');
            }
        });
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.unite-roller-container').forEach(container => {
        const list = container.querySelector('.unite-roller-list');
        const hiddenInput = container.querySelector('input[type="hidden"]');
        const items = Array.from(container.querySelectorAll('.unite-roller-item'));

        let lastScrollTime = 0;
        let isScrolling;

        // Fonction pour détecter l'élément centré
        const detectSelected = () => {
            const containerRect = container.getBoundingClientRect();
            const centerY = containerRect.top + container.offsetHeight / 2;

            let closest = null;
            let minDistance = Infinity;

            items.forEach(item => {
                const rect = item.getBoundingClientRect();
                const itemCenter = rect.top + rect.height / 2;
                const distance = Math.abs(centerY - itemCenter);

                if (distance < minDistance) {
                    minDistance = distance;
                    closest = item;
                }
            });

            if (closest) {
                items.forEach(i => i.classList.remove('selected'));
                closest.classList.add('selected');
                hiddenInput.value = closest.dataset.id;
                // MAJ du libellé affiché
                const libelleZone = container.querySelector('.unite-roller-libelle');
                if (libelleZone) {
                    libelleZone.textContent = closest.dataset.libelle;
                }
            }
        };

        // Sur scroll, attendre un peu avant de détecter
        container.addEventListener('scroll', () => {
            clearTimeout(isScrolling);
            isScrolling = setTimeout(() => {
                detectSelected();
            }, 50);
        });

        // Si clic sur un élément, scroll vers lui
        items.forEach(item => {
            item.addEventListener('click', () => {
                const topOffset = item.offsetTop - container.clientHeight / 2 + item.offsetHeight / 2;
                container.scrollTo({ top: topOffset, behavior: 'smooth' });

                // Sélection immédiate visuelle + mise à jour de l'input
                items.forEach(i => i.classList.remove('selected'));
                item.classList.add('selected');
                hiddenInput.value = item.dataset.id;

                // MAJ libellé visible
                const libelleZone = container.querySelector('.unite-roller-libelle');
                if (libelleZone) {
                    libelleZone.textContent = item.dataset.libelle;
                }
            });
        });


        // Initialiser
        window.addEventListener('load', () => {
            const selectedItem = container.querySelector('.unite-roller-item.selected');
            if (selectedItem) {
                const topOffset = selectedItem.offsetTop - container.clientHeight / 2 + selectedItem.offsetHeight / 2;
                container.scrollTo({ top: topOffset });

                        // MAJ initiale du libellé
                const libelleZone = container.querySelector('.unite-roller-libelle');
                if (libelleZone) {
                    libelleZone.textContent = selectedItem.dataset.libelle;
                }
            }
        });
    });
});
</script>

