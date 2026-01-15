<div class="step" id="step-2">
  <h5 class="text-secondary">👷 Informations / Maître d’ouvrage</h5>

  <div class="row">
    <label>Type de Maître d’ouvrage *</label>
    <div class="col">
      <div class="form-check"><input type="checkbox" id="moePublic" class="form-check-input type_ouvrage" name="type_ouvrage" value="Public"><label class="form-check-label" for="moePublic">Public</label></div>
      <div class="form-check"><input type="checkbox" id="moePrive" class="form-check-input type_ouvrage" name="type_ouvrage" value="Privé"><label class="form-check-label" for="moePrive">Privé</label></div>
    </div>

    <div class="col mt-3 d-none" id="optionsMoePrive">
      <label>Type de Privé *</label>
      <div class="col">
        <div class="form-check"><input class="form-check-input" type="radio" name="priveMoeType" id="moeEntreprise" value="Entreprise"><label class="form-check-label" for="moeEntreprise">Entreprise</label></div>
        <div class="form-check"><input class="form-check-input" type="radio" name="priveMoeType" id="moeIndividu" value="Individu"><label class="form-check-label" for="moeIndividu">Individu</label></div>
      </div>
    </div>

    <div class="col position-relative">
      <label>Nom acteur *</label>
      <select class="form-control required" name="acteurMoeSelect" id="acteurMoeSelect">
        <option value="">Sélectionnez un acteur</option>
      </select>
      <small class="text-muted">Entité assurant le rôle de Maître d’ouvrage.</small>
    </div>

    <div class="col">
      <label>De :</label>
      <select name="sectActivEntMoe" id="sectActivEntMoe" class="form-control">
        <option value="">Sélectionnez…</option>
        @foreach ($SecteurActivites as $SecteurActivite)
          <option value="{{ $SecteurActivite->code }}">{{ $SecteurActivite->libelle }}</option>
        @endforeach
      </select>
    </div>
  </div>

  <hr>
  <div class="row mt-3">
    <div class="col-8">
      <label>Description / Observations</label>
      <textarea class="form-control" id="descriptionMoe" rows="3" placeholder="Ajoutez des précisions sur le Maître d’ouvrage"></textarea>
    </div>
    <div class="col-4 mt-4">
      <div class="form-check"><input type="checkbox" class="form-check-input" id="isAssistantMoe"><label class="form-check-label" for="isAssistantMoe">Assistant Maître d’Ouvrage</label></div>
      @can("ajouter_ecran_" . $ecran->id)
      <button type="button" class="btn btn-secondary mt-2" id="addMoeBtn" style="height: 34px"><i class="fas fa-plus"></i> Ajouter</button>
      @endcan
    </div>
  </div>

  <div class="row mt-3">
    <table class="table table-bordered" id="moeTable">
      <thead><tr><th>Nom / Court</th><th>Prénom / Long</th><th>Secteur</th><th>Rôle</th><th>Action</th></tr></thead>
      <tbody></tbody>
    </table>
  </div>

  <div class="row mt-3">
    <div class="col">
      <button type="button" class="btn btn-secondary" onclick="prevStep()"><i class="fas fa-arrow-left"></i> Précédent</button>
    </div>
    <div class="col text-end">
      @can("ajouter_ecran_" . $ecran->id)
      <button type="button" class="btn btn-primary" onclick="saveStep2(nextStep)">Suivant <i class="fas fa-arrow-right"></i></button>
      @endcan
    </div>
  </div>
</div>


<script>
  function saveStep2(callback=null){
    const acteurs=[];
    $("#moeTable tbody tr").each(function(){
      const codeActeur = $(this).find("input[name='code_acteur_moe']").val();
      const role = $(this).find("input[name='role_moe']").val(); // 'moe' | 'amo'
      const secteurCode = $(this).find("td:eq(4)").text();
      if(codeActeur && role){ acteurs.push({ code_acteur: codeActeur, is_assistant: role==='amo' ? 1 : 0, secteur_code: secteurCode || null }); }
    });
    if(!acteurs.length){ alert("Ajoutez au moins un Maître d’Ouvrage/Assistant."); return; }

    $.post('{{ route("projet.etude.temp.save.step2") }}', {
      _token:'{{ csrf_token() }}',
      type_ouvrage: $('input[name="type_ouvrage"]:checked').val() || null,
      priveMoeType: $('input[name="priveMoeType"]:checked').val() || null,
      descriptionMoe: $('#descriptionMoe').val(),
      acteurs: acteurs
    }).done(()=>{ if(typeof callback==='function') callback(); else nextStep(); })
      .fail(xhr=>alert(xhr.responseJSON?.message||'Erreur Step 2'));
  }

  // exclusivité Public/Privé
  $(document).on('change','input[name="type_ouvrage"]',function(){
    if(this.checked){ $('input[name="type_ouvrage"]').not(this).prop('checked',false); }
    const isPrive = $('#moePrive').is(':checked');
    $('#optionsMoePrive').toggleClass('d-none', !isPrive);
    $('#acteurMoeSelect').html('<option value="">Sélectionnez un acteur</option>');
    if($('#moePublic').is(':checked')){
      fetch(`{{ url("/") }}/get-acteurs?type_ouvrage=Public`).then(r=>r.json()).then(fillActeursMOA);
    }
  });
  $(document).on('change','#moeEntreprise,#moeIndividu',function(){
    const t = this.id==='moeEntreprise'?'Entreprise':'Individu';
    fetch(`{{ url("/") }}/get-acteurs?type_ouvrage=Privé&priveMoeType=${encodeURIComponent(t)}`).then(r=>r.json()).then(fillActeursMOA);
  });
  function fillActeursMOA(rows){
    const $sel = $('#acteurMoeSelect').html('<option value="">Sélectionnez un acteur</option>');
    rows.forEach(a=>$sel.append(new Option(a.libelle_long, a.code_acteur)));
  }

  // Ajouter ligne
  $('#addMoeBtn').on('click', function(){
    const selected = $('#acteurMoeSelect option:selected');
    if(!selected.val()) return alert("Sélectionnez un acteur.");
    const isAssistant = $('#isAssistantMoe').is(':checked');
    const codeActeur = selected.val();
    const parts = selected.text().trim().split(/\s+/);
    const libelleCourt = parts[0]||selected.text(); const libelleLong = parts.slice(1).join(' ');
    const secteur = $('#sectActivEntMoe option:selected').text(); const secteurCode = $('#sectActivEntMoe').val();
    if(!isAssistant && $("#moeTable tbody input[name='role_moe'][value='moe']").length>0){ return alert('Un seul Maître d’Ouvrage.'); }

    const isMinistere = (libelleCourt||'').toLowerCase().includes('minist');
    const row = `
      <tr>
        <td>${libelleCourt}</td>
        <td>${libelleLong}</td>
        <td>${isMinistere ? secteur : "-"}</td>
        <td>${isAssistant ? "Assistant Maître d’Ouvrage" : "Maître d’Ouvrage"}</td>
        <td hidden>${isMinistere ? secteurCode : ""}</td>
        <td>
          <button type="button" class="btn btn-danger btn-sm remove-moe"><i class="fas fa-trash"></i></button>
          <input type="hidden" name="code_acteur_moe" value="${codeActeur}">
          <input type="hidden" name="role_moe" value="${isAssistant ? 'amo' : 'moe'}">
        </td>
      </tr>`;
    $('#moeTable tbody').append(row);
  });
  $(document).on('click','.remove-moe', function(){ $(this).closest('tr').remove(); });
</script>

