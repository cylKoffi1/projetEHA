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
<div class="step " id="step-7">
    @isset($ecran)
    @can("consulter_ecran_" . $ecran->id)
    <div class="document-upload-section">
        <h5 class="text-secondary">📎 Documents et Pièces Justificatives</h5>
        
        <div class="upload-container">
            <!-- Zone de dépôt -->
            <div class="upload-dropzone" id="dropZone">
                <i class="fas fa-cloud-upload-alt"></i>
                <p>Glissez-déposez vos fichiers ici</p>
                <p class="small">ou</p>
                @can("ajouter_ecran_" . $ecran->id)
                <button type="button" class="btn btn-outline-primary" id="browseFilesBtn">
                    Parcourir vos fichiers
                </button>
                @endcan
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
            @can("consulter_ecran_" . $ecran->id)
            <div class="uploaded-files-list mt-3" id="uploadedFilesList">
                <div class="list-header">
                    <span>Fichiers à uploader (<span id="fileCount">0</span>)</span>
                    <span id="totalSize">0 MB</span>
                </div>
                <div class="files-container" id="filesContainer">
                    <!-- Les fichiers apparaîtront ici -->
                </div>
            </div>
            @endcan
        </div>
        <br><br>
        <div class="row upload-actions">
            <div class="col">
            <button type="button" class="btn btn-secondary" onclick="prevStep()">
            <i class="fas fa-arrow-left"></i> Précédent
            </button>
            </div>
            <div class="col text-end">
            @can("ajouter_ecran_" . $ecran->id)
            <button type="button" class="btn btn-success" id="submitDocumentsBtn" disabled>
                <i class="fas fa-check"></i> Valider les documents
            </button>
            @endcan
            </div>
        </div>
    </div>
</div>
@endcan
    @endisset
<script>
    // Configuration
    const MAX_FILE_SIZE = 100 * 1024 * 1024; // 100MB
    const MAX_TOTAL_SIZE = 500 * 1024 * 1024; // 500MB
    const ALLOWED_TYPES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'image/jpeg',
        'image/png',
        'application/zip',
        'application/x-rar-compressed',
        'application/x-dwg',
        'application/x-dxf'
    ];

    // Variables globales
    let filesToUpload = [];

    // Événements
    document.addEventListener('DOMContentLoaded', function() {
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileUpload');
        const browseBtn = document.getElementById('browseFilesBtn');
        
        // Gestion du clic sur le bouton "Parcourir"
        browseBtn.addEventListener('click', function() {
            fileInput.click();
        });
        
        // Gestion de la sélection de fichiers
        fileInput.addEventListener('change', handleFileSelect);
        
        // Gestion du drag and drop
        dropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });
        
        dropZone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
        });
        
        dropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
            
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                handleFileSelect({ target: fileInput });
            }
        });
        
        // Gestion du bouton de soumission
        document.getElementById('submitDocumentsBtn').addEventListener('click', uploadFiles);
    });

    function handleFileSelect(event) {
        const files = Array.from(event.target.files);
        let totalSize = 0;
        
        // Vérification des fichiers
        for (const file of files) {
            // Vérification du type
            if (!ALLOWED_TYPES.includes(file.type)) {
                alert(`Le type de fichier "${file.name}" n'est pas autorisé.`);
                return;
            }
            
            // Vérification de la taille
            if (file.size > MAX_FILE_SIZE) {
                alert(`Le fichier "${file.name}" dépasse la taille maximale de 100MB.`);
                return;
            }
            
            totalSize += file.size;
        }
        
        // Vérification de la taille totale
        if (totalSize > MAX_TOTAL_SIZE) {
            alert(`La taille totale des fichiers (${formatFileSize(totalSize)}) dépasse la limite de 500MB.`);
            return;
        }
        
        // Ajout des fichiers à la liste
        filesToUpload = filesToUpload.concat(files);
        updateFileList();
    }

    function updateFileList() {
        const container = document.getElementById('filesContainer');
        const fileCount = document.getElementById('fileCount');
        const totalSize = document.getElementById('totalSize');
        const submitBtn = document.getElementById('submitDocumentsBtn');
        
        // Calcul de la taille totale
        let totalSizeBytes = 0;
        
        // Vide le conteneur
        container.innerHTML = '';
        
        // Ajoute chaque fichier
        filesToUpload.forEach((file, index) => {
            totalSizeBytes += file.size;
            
            const fileItem = document.createElement('div');
            fileItem.className = 'file-item';
            fileItem.innerHTML = `
                <div class="file-icon">
                    <i class="fas ${getFileIcon(file.type)}"></i>
                </div>
                <div class="file-info">
                    <div class="file-name">${file.name}</div>
                    <div class="file-size">${formatFileSize(file.size)}</div>
                </div>
                <div class="file-remove" onclick="removeFile(${index})">
                    <i class="fas fa-times"></i>
                </div>
            `;
            
            container.appendChild(fileItem);
        });
        
        // Met à jour les informations globales
        fileCount.textContent = filesToUpload.length;
        totalSize.textContent = formatFileSize(totalSizeBytes);
        
        // Active/désactive le bouton de soumission
        submitBtn.disabled = filesToUpload.length === 0;
    }

    function removeFile(index) {
        filesToUpload.splice(index, 1);
        updateFileList();
    }

    function getFileIcon(fileType) {
        const icons = {
            'application/pdf': 'fa-file-pdf',
            'image/': 'fa-file-image',
            'application/msword': 'fa-file-word',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document': 'fa-file-word',
            'application/vnd.ms-excel': 'fa-file-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': 'fa-file-excel',
            'application/zip': 'fa-file-archive',
            'application/x-rar-compressed': 'fa-file-archive',
            'application/x-dwg': 'fa-file-alt',
            'application/x-dxf': 'fa-file-alt'
        };
        
        for (const [key, icon] of Object.entries(icons)) {
            if (fileType.includes(key.replace('*', ''))) {
                return icon;
            }
        }
        
        return 'fa-file';
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    async function uploadFiles() {
        if (filesToUpload.length === 0) {
            showErrorAlert('Aucun fichier à uploader.');
            return;
        }

        const progressStatus = document.getElementById('uploadStatus');
        const progressBar = document.getElementById('uploadProgressBar');
        const progressPercent = document.getElementById('uploadPercent');
        const progressContainer = document.getElementById('uploadProgressContainer');
        const submitBtn = document.getElementById('submitDocumentsBtn');

        // Init UI
        progressContainer.style.display = 'block';
        progressStatus.textContent = "Préparation de l'envoi...";
        progressBar.style.width = '0%';
        progressPercent.textContent = '0%';
        progressBar.classList.remove('bg-danger', 'bg-success');
        submitBtn.disabled = true;

        try {
            const codeProjet = localStorage.getItem('code_projet_temp');
            if (!codeProjet) throw new Error("Aucun projet sélectionné. Veuillez revenir à l'étape 1.");

            const formData = new FormData();
            formData.append('code_projet', codeProjet);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            filesToUpload.forEach(file => formData.append('fichiers[]', file));

            const response = await fetch('{{ route("projets.temp.save.step7") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json' // Important pour bien recevoir du JSON même en cas d’erreur Laravel
                }
            });

            const data = await response.json();

            if (response.ok && data.success) {
                progressBar.classList.add('bg-success');
                progressBar.style.width = '100%';
                progressStatus.textContent = 'Upload terminé avec succès!';
                progressPercent.textContent = '100%';

                setTimeout(() => {
                    finaliserCodeProjet();
                 window.location.href = '{{ route("project.create", ["ecran_id" => $ecran->id]) }}';
                }, 1500);
            } else {
                throw new Error(data.message || 'Erreur serveur');
            }

        } catch (error) {
            progressBar.classList.add('bg-danger');
            progressStatus.textContent = 'Erreur: ' + error.message;
            showErrorAlert(
                "Échec de l'upload : " + error.message +
                "\n\nVérifie :\n- Taille et type des fichiers\n- Ta connexion Internet\n- Que le projet est bien sélectionné"
            );
            submitBtn.disabled = false;
        }
    }



    function handleUploadError(error, progressStatus, progressBar, submitBtn) {
        console.error("Erreur lors de l'upload:", error);
        
        // Mise à jour de l'UI
        progressStatus.textContent = 'Erreur: ' + error.message;
        progressBar.classList.remove('progress-bar-animated');
        progressBar.classList.add('bg-danger');
        submitBtn.disabled = false;
        
        // Affichage de l'erreur à l'utilisateur
        showErrorAlert(
            "Échec de l'upload: " + error.message + 
            "\n\nVeuillez vérifier :" +
            "\n- La taille des fichiers (max 100MB par fichier, 500MB total)" +
            "\n- Le type des fichiers (PDF, Word, Excel, images, etc.)" +
            "\n- Votre connexion internet"
        );
    }

    function showErrorAlert(message) {
        // Utilisation de SweetAlert si disponible, sinon alert() natif
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Erreur',
                text: message,
                icon: 'error',
                confirmButtonText: 'OK',
                customClass: {
                    container: 'swal2-container-error'
                }
            });
        } else {
            alert(message);
        }
    }

    function finaliserCodeProjet() {
        const codeTemp = localStorage.getItem('code_projet_temp');
        const codeLocalisation = localStorage.getItem('code_localisation');
        const typeFinancement = localStorage.getItem('type_financement');
        console.log('codeTemp',codeTemp);
        console.log('codeLocalisation', codeLocalisation);
        console.log('typeFinancement', typeFinancement);
        
        if (!codeTemp || !codeLocalisation || !typeFinancement) {
            alert("Des informations manquent pour finaliser le projet.");
            return;
        }

        fetch('{{ route("projets.finaliser") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json', // ✅ pour forcer JSON même en cas d'erreur Laravel
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                code_projet_temp: codeTemp,
                code_localisation: codeLocalisation,
                type_financement: typeFinancement
            })
        })
        .then(async (response) => {
            const text = await response.text();

            try {
                const data = JSON.parse(text);

                if (response.ok) {
                    // ✅ Réponse OK
                    if (data.success) {
                        localStorage.removeItem('code_projet_temp');
                        localStorage.removeItem('type_financement');
                        localStorage.removeItem('code_localisation');
                        alert(data.message || "Projet finalisé avec succès !");
                        console.log("Code projet final :", data.code_projet_final);
                    } else {
                        alert(data.message || "Finalisation échouée.");
                    }
                } else {
                    // ❌ Laravel a répondu avec une erreur 422, 500, etc.
                    console.error("Erreur Laravel :", data);
                    alert(data.message || "Erreur serveur lors de la finalisation.");
                }
            } catch (e) {
                // 💥 Laravel a peut-être renvoyé du HTML (vue Blade)
                console.error("Réponse non JSON :", text);
                alert("Erreur inattendue. Le serveur a retourné une réponse non valide.");
            }
        })
        .catch(error => {
            console.error('Erreur réseau ou serveur lors de la finalisation :', error);
            alert("Une erreur est survenue lors de la finalisation du projet.");
        });
    }

</script>