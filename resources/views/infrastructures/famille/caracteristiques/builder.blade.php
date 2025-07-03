
<style>


    .container {
        padding: 20px;
        background-color: #DBECF8;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
        height: calc(100vh - 40px);
    }

    .panel {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .panel-header {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        padding: 20px;
        text-align: center;
    }

    .panel-header h2 {
        font-size: 1.5rem;
        margin-bottom: 5px;
    }

    .panel-content {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
    }

    /* Formulaire de création */
    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #2c3e50;
    }



    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }

    .form-actions {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }


    /* Arbre hiérarchique */
    .tree-container {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 15px;
        background: #f8f9fa;
    }

    .tree-node {
        margin-bottom: 10px;
    }

    .node-item {
        background: white;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }

    .node-item:hover {
        border-color: #667eea;
        transform: translateX(5px);
    }

    .node-item.selected {
        border-color: #667eea;
        background: #e3f2fd;
    }

    .node-header {
        display: flex;
        align-items: center;
        justify-content: between;
        gap: 10px;
    }

    .node-icon {
        width: 30px;
        height: 30px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        color: white;
    }

    .node-info {
        flex: 1;
    }

    .node-title {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 4px;
    }

    .node-meta {
        font-size: 12px;
        color: #6c757d;
        display: flex;
        gap: 15px;
    }

    .node-actions {
        display: flex;
        gap: 5px;
    }

    .node-btn {
        width: 30px;
        height: 30px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
    }

    .node-btn.add {
        background: #28a745;
        color: white;
    }

    .node-btn.edit {
        background: #ffc107;
        color: #212529;
    }

    .node-btn.delete {
        background: #dc3545;
        color: white;
    }

    .node-children {
        margin-left: 30px;
        margin-top: 10px;
        padding-left: 20px;
        border-left: 2px solid #dee2e6;
    }

    /* Types et badges */
    .type-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
    }

    .type-text { background: #e3f2fd; color: #1976d2; }
    .type-number { background: #f3e5f5; color: #7b1fa2; }
    .type-select { background: #e8f5e8; color: #388e3c; }
    .type-boolean { background: #fff3e0; color: #f57c00; }
    .type-date { background: #fce4ec; color: #c2185b; }
    .type-group { background: #f1f8e9; color: #689f38; }

    /* Modal */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }

    .modal.show {
        display: flex;
    }

    .modal-content {
        background: white;
        border-radius: 15px;
        padding: 30px;
        width: 90%;
        max-width: 600px;
        max-height: 80vh;
        overflow-y: auto;
    }

    .modal-header {
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e9ecef;
    }

    .modal-header h3 {
        color: #2c3e50;
    }

    /* Export/Import */
    .export-section {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        margin-top: 20px;
    }

    .json-display {
        background: #2d3748;
        color: #e2e8f0;
        padding: 15px;
        border-radius: 8px;
        font-family: 'Courier New', monospace;
        font-size: 12px;
        max-height: 300px;
        overflow-y: auto;
    }

    /* Conditions */
    .condition-item {
        background: #fff3cd;
        border: 1px solid #ffeaa7;
        border-radius: 6px;
        padding: 10px;
        margin: 5px 0;
        font-size: 12px;
    }

    @media (max-width: 1200px) {
        .container {
            grid-template-columns: 1fr;
            height: auto;
        }
    }
</style>
<div class="container">
    <!-- Panel de création -->
    <div class="panel">
        <div class="panel-header">
            <h2><i class="fas fa-plus-circle"></i> Créateur de Caractéristiques</h2>
            <p>Définissez votre structure hiérarchique</p>
        </div>
        <div class="panel-content">
            <form id="characteristicForm">
                
                <div class="row">
                    <div class="col-8">
                        <label>Nom de la caractéristique</label>
                        <input type="text" id="charName" class="form-control" placeholder="Ex: Surface totale">
                    </div>
                    <div class="col-4">
                        <label>Parent</label>
                        <select id="charParent" class="form-control">
                            <option value="">🏠 Racine</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <label>Type</label>
                        <select id="charType" class="form-control">
                            <option value="text">📝 Texte</option>
                            <option value="number">🔢 Nombre</option>
                            <option value="select">📋 Liste déroulante</option>
                            <option value="boolean">☑️ Oui/Non</option>
                            <option value="date">📅 Date</option>
                        </select>
                    </div>

                    <div class="col">
                        <label>Ordre</label>
                        <input type="number" id="charOrder" class="form-control" value="1" min="1">
                    </div>
                </div>

                <div id="selectOptions" class="form-group" style="display: none;">
                    <label>Options (séparées par des virgules)</label>
                    <input type="text" id="charOptions" class="form-control" placeholder="Ex: Option1, Option2, Option3">
                </div>

                <div class="form-row">
                    <div class="form-group" id="unitSection" style="display: none;">
                        <label>Unité</label>
                        <input type="text" id="charUnit" class="form-control" placeholder="Ex: m², kg, %">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label d-block">Type de répétition :</label>
                    <div class="d-flex align-items-center gap-4">
                        <div class="form-check me-4">
                            <input class="form-check-input" type="checkbox" id="charUnique" name="charUnique" checked>
                            <label class="form-check-label" for="charUnique">Unique</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="charRepeatable" name="charRepeatable">
                            <label class="form-check-label" for="charRepeatable">Répétable</label>
                        </div>
                    </div>

                    <div id="repeatCountContainer" class="mt-3" style="display: none;">
                        <label for="repeatCount" class="form-label">Nombre de répétitions</label>
                        <input type="number" class="form-control" id="repeatCount" name="repeatCount" min="1" value="1">
                    </div>
                </div>



                <div class="form-group">
                    <label>Description</label>
                    <textarea id="charDescription" class="form-control" rows="3" placeholder="Description détaillée..."></textarea>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-primary" onclick="addCharacteristic()">
                        <i class="fas fa-plus"></i> Ajouter
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="resetForm()">
                        <i class="fas fa-undo"></i> Réinitialiser
                    </button>
                </div>
            </form>

            <div class="export-section">
                <h4><i class="fas fa-download"></i> Export/Import</h4>
                <div class="form-actions">
                    <button class="btn btn-success" onclick="exportStructure()">
                        <i class="fas fa-file-export"></i> Exporter JSON
                    </button>
                    <input type="file" id="importFile" accept=".json" style="display: none;" onchange="importStructure(event)">
                    <button class="btn btn-secondary" onclick="document.getElementById('importFile').click()">
                        <i class="fas fa-file-import"></i> Importer JSON
                    </button>
                    <button class="btn btn-danger" onclick="clearAll()">
                        <i class="fas fa-trash"></i> Tout effacer
                    </button>
                </div>
            </div> 
        </div>
    </div>
    
    <!-- Panel de visualisation -->
    <div class="panel">
        <div class="panel-header">
            <h2><i class="fas fa-sitemap"></i> Structure Hiérarchique</h2>
            <p>Votre arbre de caractéristiques</p>
        </div>
        <div class="panel-content">
            <div id="treeContainer" class="tree-container">
                <div id="emptyState" style="text-align: center; color: #6c757d; padding: 40px;">
                    <i class="fas fa-tree" style="font-size: 48px; margin-bottom: 20px;"></i>
                    <p>Commencez par ajouter votre première caractéristique</p>
                </div>
            </div>
            

            <div class="export-section">
                <h4><i class="fas fa-code"></i> Prévisualisation JSON</h4>
                <div id="jsonPreview" class="json-display">
                    {
                    "message": "Ajoutez des caractéristiques pour voir la structure"
                    }
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de condition -->
<div id="conditionModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-filter"></i> Ajouter une condition</h3>
        </div>
        <div class="form-group">
            <label>Caractéristique source</label>
            <select id="conditionSource" class="form-control"></select>
        </div>
        <div class="form-group">
            <label>Opérateur</label>
            <select id="conditionOperator" class="form-control">
                <option value="equals">Égal à</option>
                <option value="not_equals">Différent de</option>
                <option value="contains">Contient</option>
                <option value="greater_than">Supérieur à</option>
                <option value="less_than">Inférieur à</option>
            </select>
        </div>
        <div class="form-group">
            <label>Valeur</label>
            <input type="text" id="conditionValue" class="form-control">
        </div>
        <div class="form-actions">
            <button class="btn btn-primary" onclick="saveCondition()">
                <i class="fas fa-check"></i> Ajouter
            </button>
            <button class="btn btn-secondary" onclick="closeConditionModal()">
                <i class="fas fa-times"></i> Annuler
            </button>
        </div>
    </div>
</div>

