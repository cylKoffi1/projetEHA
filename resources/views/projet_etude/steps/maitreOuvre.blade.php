<div class="step" id="step-3">
  <h5 class="text-secondary">🏗️ Informations / Maître d'œuvre</h5>

  <div class="row">
    <label>Type de Maître d'œuvre *</label>
    <div class="col">
      <div class="form-check"><input type="checkbox" id="public" class="form-check-input type_mo" name="type_mo" value="Public"><label class="form-check-label" for="public">Public</label></div>
      <div class="form-check"><input type="checkbox" id="prive" class="form-check-input type_mo" name="type_mo" value="Privé"><label class="form-check-label" for="prive">Privé</label></div>
      <small class="text-muted">Le maître d'œuvre peut être public (État) ou privé (Entreprise/Individu).</small>
    </div>

    <div class="col mt-3 d-none" id="optionsPrive">
      <label>Type de Privé *</label>
      <div class="col">
        <div class="form-check"><input class="form-check-input" type="radio" name="priveType" id="entreprise" value="Entreprise"><label class="form-check-label" for="entreprise">Entreprise</label></div>
        <div class="form-check"><input class="form-check-input" type="radio" name="priveType" id="individu" value="Individu"><label class="form-check-label" for="individu">Individu</label></div>
      </div>
    </div>

    <div class="col">
      <label>Nom Acteur *</label>
      <select class="form-control required" name="acteurSelect" id="acteurSelect">
        <option value="">Sélectionnez un acteur</option>
      </select>
      <small class="text-muted">Entité assurant le rôle de Maître d'œuvre.</small>
    </div>

    <div class="col">
      <label>De :</label>
      <select name="sectActivEnt" id="sectActivEnt" class="form-control">
        <option value="">Sélectionnez…</option>
        @foreach ($SecteurActivites as $SecteurActivite)
          <option value="{{ $SecteurActivite->code }}">{{ $SecteurActivite->libelle }}</option>
        @endforeach
      </select>
    </div>
  </div>

  <div class="row mt-2">
    <div class="col-10">
      <label>Description / Observations</label>
      <textarea class="form-control" id="descriptionInd" rows="3" placeholder="Notes…"></textarea>
    </div>
    <div class="col-2 mt-4">
      @can("ajouter_ecran_" . $ecran->id)
      <button type="button" class="btn btn-secondary" id="addMoeuvreBtn" style="height: 34px">
        <i class="fas fa-plus"></i> Ajouter
      </button>
      @endcan
    </div>
  </div>

  <div class="row mt-3">
    <table class="table table-bordered" id="moeuvreTable">
      <thead><tr><th>Nom / Court</th><th>Prénom / Long</th><th>Secteur</th><th>Action</th></tr></thead>
      <tbody></tbody>
    </table>
  </div>

  <div class="row mt-3">
    <div class="col">
      <button type="button" class="btn btn-secondary" onclick="prevStep()"><i class="fas fa-arrow-left"></i> Précédent</button>
    </div>
    <div class="col text-end">
      @can("ajouter_ecran_" . $ecran->id)
      <button type="button" class="btn btn-primary" onclick="saveStep3(nextStep)">Suivant <i class="fas fa-arrow-right"></i></button>
      @endcan
    </div>
  </div>
</div>

<script>
  function saveStep3(callback=null){
    const acteurs=[];
    $("#moeuvreTable tbody tr").each(function(){
      const codeActeur = $(this).find('input[name="code_acteur_moeuvre[]"]').val();
      const secteurId  = $(this).find('input[name="secteur_id[]"]').val();
      acteurs.push({ code_acteur: codeActeur, secteur_id: secteurId });
    });
    if(!acteurs.length){ alert("Ajoutez au moins un Maître d’œuvre."); return; }

    $.post('{{ route("projet.etude.temp.save.step3") }}', {
      _token:'{{ csrf_token() }}',
      acteurs: acteurs
    }).done(()=>{ if(typeof callback==='function') callback(); else nextStep(); })
      .fail(xhr=>alert(xhr.responseJSON?.message||'Erreur Step 3'));
  }

  // check exclusif
  $(document).on('change','input[name="type_mo"]',function(){
    if(this.checked){ $('input[name="type_mo"]').not(this).prop('checked',false); }
    const isPrive = $('#prive').is(':checked'); $('#optionsPrive').toggleClass('d-none', !isPrive);
    $('#acteurSelect').html('<option value="">Sélectionnez un acteur</option>');
    if($('#public').is(':checked')){
      fetch(`{{ url('/') }}/get-acteurs?type_mo=Public`).then(r=>r.json()).then(fillActeursMOE);
    }
  });
  $(document).on('change','#entreprise,#individu',function(){
    const t = this.id==='entreprise'?'Entreprise':'Individu';
    fetch(`{{ url('/') }}/get-acteurs?type_mo=Privé&priveType=${encodeURIComponent(t)}`).then(r=>r.json()).then(fillActeursMOE);
  });
  function fillActeursMOE(rows){
    const $sel = $('#acteurSelect').html('<option value="">Sélectionnez un acteur</option>');
    rows.forEach(a=>$sel.append(new Option(a.libelle_long, a.code_acteur)));
  }

  $('#addMoeuvreBtn').on('click', function(){
    const selected = $("#acteurSelect option:selected");
    if(!selected.val()) return alert("Sélectionnez un acteur.");
    const codeActeur = selected.val();
    const parts = selected.text().trim().split(/\s+/);
    const libelleCourt = parts[0]||selected.text(); const libelleLong = parts.slice(1).join(' ');
    const secteur = $("#sectActivEnt option:selected").text(); const secteurCode = $("#sectActivEnt").val();
    if($("#moeuvreTable tbody input[value='"+codeActeur+"']").length>0) return alert("Déjà ajouté.");

    const isMinistere = (libelleCourt||'').toLowerCase().includes('minist');
    const row = `
      <tr>
        <td>${libelleCourt}</td>
        <td>${libelleLong}</td>
        <td>${isMinistere ? secteur : "-"}</td>
        <td>
          <button type="button" class="btn btn-danger btn-sm remove-moeuvre"><i class="fas fa-trash"></i></button>
          <input type="hidden" name="code_acteur_moeuvre[]" value="${codeActeur}">
          <input type="hidden" name="secteur_id[]" value="${isMinistere ? secteurCode : ''}">
        </td>
      </tr>`;
    $("#moeuvreTable tbody").append(row);
  });
  $(document).on("click",".remove-moeuvre", function(){ $(this).closest("tr").remove(); });
</script>

