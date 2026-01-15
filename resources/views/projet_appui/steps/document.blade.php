{{-- Modal aide --}}
<div class="modal fade" id="documentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">📜 Documents à fournir</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
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
  </div></div>
</div>

<div class="step" id="step-7">
  <div class="document-upload-section">
    <h5 class="text-secondary">📎 Documents et Pièces Justificatives</h5>

    <div class="upload-dropzone mt-2" id="dropZone">
      <i class="fas fa-cloud-upload-alt fa-2x"></i>
      <p class="mt-2 mb-1">Glissez-déposez vos fichiers ici</p>
      <p class="small mb-2">ou</p>
      @can("ajouter_ecran_" . $ecran->id)
      <button type="button" class="btn btn-outline-primary" id="browseFilesBtn">Parcourir vos fichiers</button>
      @endcan
      <p class="file-limits mt-2">Formats: .pdf, .dwg, .dxf, .jpg, .png, .docx, .xlsx, .zip, .rar (100MB max / fichier)</p>
      <input type="file" id="fileUpload" multiple style="display:none" accept=".pdf,.dwg,.dxf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.zip,.rar">
    </div>

    <div class="upload-progress mt-3" id="uploadProgressContainer" style="display:none;">
      <div class="d-flex justify-content-between mb-1"><span id="uploadStatus">Préparation…</span><span id="uploadPercent">0%</span></div>
      <div class="progress"><div class="progress-bar progress-bar-striped progress-bar-animated" id="uploadProgressBar" role="progressbar" style="width:0%"></div></div>
    </div>

    <div class="uploaded-files-list mt-3" id="uploadedFilesList">
      <div class="d-flex justify-content-between px-3 py-2 bg-light border"><span>Fichiers à uploader (<span id="fileCount">0</span>)</span><span id="totalSize">0 MB</span></div>
      <div class="files-container p-2" id="filesContainer"></div>
    </div>

    <div class="row mt-4">
      <div class="col">
        <button type="button" class="btn btn-secondary" onclick="prevStep()"><i class="fas fa-arrow-left"></i> Précédent</button>
      </div>
      <div class="col text-end">
        @can("ajouter_ecran_" . $ecran->id)
        <button type="button" class="btn btn-success" id="submitDocumentsBtn" disabled><i class="fas fa-check"></i> Valider les documents</button>
        @endcan
      </div>
    </div>
  </div>
</div>

<script>
  const MAX_FILE_SIZE = 100*1024*1024; const MAX_TOTAL_SIZE = 500*1024*1024;
  const ALLOWED_TYPES = ['application/pdf','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/vnd.ms-excel','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','image/jpeg','image/png','application/zip','application/x-rar-compressed','application/x-dwg','application/x-dxf'];
  let filesToUpload = [];

  document.addEventListener('DOMContentLoaded', function(){
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileUpload');
    const browseBtn = document.getElementById('browseFilesBtn');

    browseBtn?.addEventListener('click', ()=>fileInput.click());
    fileInput.addEventListener('change', handleFileSelect);

    dropZone.addEventListener('dragover', e=>{ e.preventDefault(); dropZone.classList.add('dragover'); });
    dropZone.addEventListener('dragleave', e=>{ e.preventDefault(); dropZone.classList.remove('dragover'); });
    dropZone.addEventListener('drop', e=>{
      e.preventDefault(); dropZone.classList.remove('dragover');
      if(e.dataTransfer.files.length){ fileInput.files = e.dataTransfer.files; handleFileSelect({ target: fileInput }); }
    });

    document.getElementById('submitDocumentsBtn').addEventListener('click', uploadFiles);
  });

  function handleFileSelect(event){
    const files = Array.from(event.target.files); let totalSize = files.reduce((a,f)=>a+f.size,0);
    for(const file of files){
      if(!ALLOWED_TYPES.includes(file.type)) return alert(`Type non autorisé: ${file.name}`);
      if(file.size>MAX_FILE_SIZE) return alert(`Fichier trop volumineux (>100MB): ${file.name}`);
    }
    if(totalSize>MAX_TOTAL_SIZE) return alert(`Total > 500MB (${formatFileSize(totalSize)})`);
    filesToUpload = filesToUpload.concat(files); updateFileList();
  }

  function updateFileList(){
    const container=document.getElementById('filesContainer'); const fileCount=document.getElementById('fileCount'); const totalSize=document.getElementById('totalSize'); const submitBtn=document.getElementById('submitDocumentsBtn');
    container.innerHTML=''; let total=0;
    filesToUpload.forEach((file,idx)=>{ total+=file.size; const item=document.createElement('div'); item.className='d-flex align-items-center border-bottom py-2';
      item.innerHTML = `<div class="me-2"><i class="fas fa-file"></i></div>
        <div class="flex-grow-1"><div class="fw-medium">${file.name}</div><div class="text-muted small">${formatFileSize(file.size)}</div></div>
        <button type="button" class="btn btn-link text-danger p-0" onclick="removeFile(${idx})"><i class="fas fa-times"></i></button>`;
      container.appendChild(item);
    });
    fileCount.textContent = filesToUpload.length; totalSize.textContent = formatFileSize(total);
    submitBtn.disabled = filesToUpload.length===0;
  }
  function removeFile(index){ filesToUpload.splice(index,1); updateFileList(); }
  function formatFileSize(bytes){ if(bytes===0) return '0 o'; const k=1024, sizes=['o','KB','MB','GB']; const i=Math.floor(Math.log(bytes)/Math.log(k)); return (bytes/Math.pow(k,i)).toFixed(2)+' '+sizes[i]; }

  async function uploadFiles(){
    if(!filesToUpload.length) return alert('Aucun fichier.');
    const progressStatus=$('#uploadStatus'), progressBar=$('#uploadProgressBar'), progressPercent=$('#uploadPercent'), progressContainer=$('#uploadProgressContainer'), submitBtn=$('#submitDocumentsBtn');
    progressContainer.show(); progressStatus.text("Préparation…"); progressBar.css('width','0%').removeClass('bg-danger bg-success'); progressPercent.text('0%'); submitBtn.prop('disabled', true);

    try{
      const codeProjet = localStorage.getItem('code_projet_temp') || 'TMP';
      const formData = new FormData(); formData.append('code_projet', codeProjet); formData.append('_token', '{{ csrf_token() }}');
      filesToUpload.forEach(f => formData.append('fichiers[]', f));

      const res = await fetch(`{{ route('projet.appui.temp.save.step7') }}`, { method:'POST', body: formData, headers:{'Accept':'application/json'} });
      const data = await res.json();
      if(res.ok && data.success){
        progressBar.addClass('bg-success').css('width','100%'); progressStatus.text('Upload terminé'); progressPercent.text('100%');
        setTimeout(()=> finaliserCodeProjet(), 800);
      } else { throw new Error(data.message || 'Erreur serveur'); }
    } catch (e){
      progressBar.addClass('bg-danger'); progressStatus.text('Erreur: '+e.message); submitBtn.prop('disabled', false);
      alert("Échec de l'upload : "+e.message);
    }
  }

  function finaliserCodeProjet(){
    // Finalise sans dépendre du localStorage (la session contient les étapes)

    fetch(`{{ isset($directMode) && $directMode ? route('projets.appui.finaliser.direct') : route('projets.finaliser') }}`, {
      method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
      body: JSON.stringify({})
    }).then(async (r)=>{
      const text = await r.text(); let data; try{ data = JSON.parse(text);}catch{ data=null; }
      if(!r.ok) return alert(data?.message || 'Erreur serveur.');
      if(data?.success){
        localStorage.removeItem('code_projet_temp'); localStorage.removeItem('type_financement'); 
        alert(data.message || 'Projet finalisé !');
      } else { alert(data?.message || 'Finalisation échouée.'); }
    }).catch(()=>alert('Erreur réseau lors de la finalisation.'));
  }
</script>

