<div class="step active" id="step-1">
  <h5 class="text-secondary">📋 Informations Générales</h5>

  <div class="row">
    <div class="col-4">
      <label>Nature des travaux *</label>
      <input type="hidden" name="natureTraveaux" id="natureTraveaux" value="{{ $NaturesTravaux->first()->code ?? '' }}">
      <input type="text" name="natureTraveauxLibelle" id="natureTraveauxLibelle"
             class="form-control" value="{{ $NaturesTravaux->first()->libelle ?? '' }}" readonly>
    </div>

    <div class="col-4">
      <label>Groupe de Projet *</label>
      <select class="form-control" name="groupe_projet" disabled>
        <option value="">Sélectionner</option>
        @foreach ($GroupeProjets as $groupe)
          <option value="{{ $groupe->code }}" {{ $groupeSelectionne == $groupe->code ? 'selected' : '' }}>
            {{ $groupe->libelle }}
          </option>
        @endforeach
      </select>
    </div>

    <div class="col-4">
      <label>Nom du Projet *</label>
      <input type="text" class="form-control" id="nomProjet" name="nomProjet" placeholder="Nom du projet" required>
    </div>
  </div>

  <div class="row mt-2">
    <div class="col">
      <label>Domaine *</label>
      <select name="domaine" id="domaineSelect" class="form-control">
        <option value="">Sélectionner domaine</option>
        @foreach ($Domaines as $domaine)
          <option value="{{ $domaine->code }}">{{ $domaine->libelle }}</option>
        @endforeach
      </select>
    </div>
    <div class="col">
      <label>Sous-Domaine *</label>
      <select name="SousDomaine" id="sousDomaineSelect" class="form-control" disabled>
        <option value="">Sélectionner sous domaine</option>
      </select>
    </div>
    <div class="col">
      <label>Date Début prévisionnelle *</label>
      <input type="date" class="form-control" id="dateDemarragePrev">
    </div>
    <div class="col">
      <label>Date Fin prévisionnelle *</label>
      <input type="date" class="form-control" id="dateFinPrev">
    </div>
  </div>

  <div class="row mt-2">
    <div class="col-md-3">
      <label>Coût du projet</label>
      <input type="text" name="coutProjet" id="coutProjet" class="form-control text-end" oninput="formatNumber(this)">
    </div>
    <div class="col-md-2">
      <label>Devise du coût</label>
      <input type="text" name="code_devise" id="deviseCout" class="form-control" value="{{ $deviseCouts->code_long ?? 'XOF' }}" readonly>
    </div>
  </div>

  <div class="row mt-2">
    <div class="col">
      <label>Commentaire</label>
      <textarea class="form-control" name="commentaireProjet" id="commentaireProjet" rows="2"></textarea>
    </div>
  </div>

  <div class="row mt-3">
    <div class="col text-end">
      @can("ajouter_ecran_" . $ecran->id)
      <button type="button" class="btn btn-primary" onclick="saveStep1(nextStep)">
        Suivant <i class="fas fa-arrow-right"></i>
      </button>
      @endcan
    </div>
  </div>

  {{-- Hidden infos for Step 2 --}}
  @foreach ($Pays as $alpha3 => $nom_fr_fr)
    <input type="hidden" id="paysSelect" value="{{ $alpha3 }}">
  @endforeach
</div>

<script>
  function saveStep1(callback=null){
    const data = {
      _token:'{{ csrf_token() }}',
      libelle_projet: $('#nomProjet').val(),
      code_domaine: $('#domaineSelect').val(),
      code_sous_domaine: $('#sousDomaineSelect').val(),
      date_demarrage_prevue: $('#dateDemarragePrev').val(),
      date_fin_prevue: $('#dateFinPrev').val(),
      cout_projet: ($('#coutProjet').val()||'').replace(/\s/g,''),
      code_devise: $('#deviseCout').val(),
      commentaire: $('#commentaireProjet').val(),
      code_nature: $('#natureTraveaux').val(),
      code_pays: $('#paysSelect').val(),
    };
    $.post('{{ route("projet.appui.temp.save.step1") }}', data)
      .done(() => {
        localStorage.setItem('step1_code_domaine', $('#domaineSelect').val());
        localStorage.setItem('step1_lib_domaine', $('#domaineSelect option:selected').text());
        localStorage.setItem('step1_code_sdomaine', $('#sousDomaineSelect').val());
        localStorage.setItem('step1_lib_sdomaine', $('#sousDomaineSelect option:selected').text());
        if (typeof callback==='function') callback(); else nextStep();
      })
      .fail(xhr => alert(xhr.responseJSON?.message || 'Erreur Step 1'));
  }

  // Domaines -> sous-domaines
  $(document).on('change','#domaineSelect',function(){
    const code = this.value; const $sd = $('#sousDomaineSelect').prop('disabled',true).empty()
      .append(`<option value="">Sélectionner sous domaine</option>`);
    if(!code) return;
    fetch(`{{ url('/') }}/get-sous-domaines/${encodeURIComponent(code)}`)
      .then(r=>r.json())
      .then(rows => {
        rows.forEach(sd => $sd.append(new Option(sd.lib_sous_domaine, sd.code_sous_domaine)));
        $sd.prop('disabled', false);
      });
  });

  // Dates guard
  (function(){
    const d1=document.getElementById('dateDemarragePrev'), d2=document.getElementById('dateFinPrev');
    function valider(){ if(d1.value && d2.value && new Date(d1.value)>new Date(d2.value)){ alert('La date de début ne peut pas être postérieure à la date de fin.'); d1.value=''; d2.value=''; } }
    d1.addEventListener('change', valider); d2.addEventListener('change', valider);
  })();
</script>

