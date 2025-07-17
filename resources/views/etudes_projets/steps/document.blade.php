<div class="modal fade" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true" style="background: transparent !important;">
    <div class="modal-dialog">
        <div class="modal-content" style="width: 100% !important; background: white;">
            <div class="modal-header">
                <h5 class="modal-title" id="documentModalLabel">📜 Documents à fournir</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul>
                    <li>📄 Cahier des Charges</li>
                    <li>📊 Études Préliminaires (Faisabilité, Impact Environnemental, Géotechnique)</li>
                    <li>📜 Plans et Maquettes du Projet</li>
                    <li>💰 Budget Prévisionnel</li>
                    <li>📝 Permis de Construire (si applicable)</li>
                    <li>🏢 Justificatif de propriété du terrain</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!-- 🟡 Étape  : Documents -->
<div class="step" id="step-7">
    <div class="document-upload-section">
        <h5 class="text-secondary">📎 Documents et Pièces Justificatives</h5>
        
        <div class="upload-container">
            <!-- Zone de dépôt -->
            <div class="upload-dropzone" id="dropZone">
                <i class="fas fa-cloud-upload-alt"></i>
                <p>Glissez-déposez vos fichiers ici</p>
                <p class="small">ou</p>
                <button type="button" class="btn btn-outline-primary" id="browseFilesBtn">
                    Parcourir vos fichiers
                </button>
                <p class="file-limits">
                    Formats acceptés: .pdf, .dwg, .jpg, .docx, .xlsx<br>
                    Taille max: 100MB par fichier
                </p>
                <input type="file" id="fileUpload" multiple style="display: none;" 
                    accept=".pdf,.dwg,.dxf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.zip,.rar">
            </div>
            
            <!-- Barre de progression -->
            <div class="upload-progress mt-3" id="uploadProgressContainer" style="display: none;">
                <div class="progress-info">
                    <span id="uploadStatus">Préparation de l'envoi...</span>
                    <span id="uploadPercent">0%</span>
                </div>
                <div class="progress">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                        id="uploadProgressBar" role="progressbar" style="width: 0%"></div>
                </div>
            </div>
            
            <!-- Fichiers sélectionnés -->
            <div class="uploaded-files-list mt-3" id="uploadedFilesList">
                <div class="list-header">
                    <span>Fichiers à uploader (<span id="fileCount">0</span>)</span>
                    <span id="totalSize">0 MB</span>
                </div>
                <div class="files-container" id="filesContainer">
                    <!-- Les fichiers apparaîtront ici -->
                </div>
            </div>
        </div>
        <br><br>
        <div class="row upload-actions">
            <div class="col">
            <button type="button" class="btn btn-secondary" onclick="prevStep()">
            <i class="fas fa-arrow-left"></i> Précédent
            </button>
            </div>
            <div class="col text-end">
            <button type="button" class="btn btn-success" id="submitDocumentsBtn" disabled>
                <i class="fas fa-check"></i> Valider les documents
            </button>
            </div>
        </div>
    </div>
</div>