<div class="step" id="step-4">
  <h5 class="text-secondary">💰 Ressources Financières</h5>

  <div class="col-12 col-md-3 mb-3">
    <label for="typeFinancement">Type de financement</label>
    <select id="typeFinancement" name="type_financement" class="form-control">
      <option value="">Sélectionner le type</option>
      @foreach ($typeFinancements ?? \App\Models\TypeFinancement::all() as $tf)
        <option value="{{ $tf->code_type_financement }}">{{ $tf->libelle }}</option>
      @endforeach
    </select>
  </div>

  <div class="row g-3">
    <div class="col-2">
      <label>Local *</label><br>
      <div class="form-check form-check-inline">
        <input type="radio" id="BailOui" name="BaillOui" value="1" class="form-check-input"><label for="BailOui" class="form-check-label">Oui</label>
      </div>
      <div class="form-check form-check-inline">
        <input type="radio" id="BailNon" name="BaillOui" value="0" class="form-check-input"><label for="BailNon" class="form-check-label">Non</label>
      </div>
    </div>

    <div class="col">
      <label for="bailleur">Bailleur</label>
      <lookup-select name="bailleur" id="bailleur" placeholder="Sélectionner un bailleur">
        <option value="">Sélectionner le bailleur</option>
      </lookup-select>
    </div>

    <div class="col d-none" id="chargeDeContainer">
      <label for="chargeDe">En charge de :</label>
      <select name="chargeDe" id="chargeDe" class="form-control">
        @foreach ($SecteurActivites as $SecteurActivite)
          <option value="{{ $SecteurActivite->id }}">{{ $SecteurActivite->libelle }}</option>
        @endforeach
      </select>
    </div>

    <div class="col-md-2">
      <label for="montant">Montant</label>
      <input type="text" id="montant" class="form-control text-end" placeholder="Montant" oninput="formatNumber(this)">
    </div>

    <div class="col-md-1">
      <label for="deviseBailleur">Devise</label>
      <input type="text" id="deviseBailleur" class="form-control" value="{{ $Devises[0]->code_devise ?? 'XOF' }}" readonly>
    </div>

    <div class="col-md-3">
      <label for="commentaire">Commentaire</label>
      <input type="text" id="commentaire" class="form-control" placeholder="Commentaire">
    </div>

    <div class="col text-end">
      @can("ajouter_ecran_" . $ecran->id)
      <button type="button" class="btn btn-secondary" id="addFinancementBtn">Ajouter</button>
      @endcan
    </div>
  </div>

  <div class="mt-4">
    <table class="table table-bordered" id="tableFinancements">
      <thead><tr><th>Bailleur</th><th>En charge de</th><th class="text-end">Montant</th><th>Devise</th><th>Local</th><th>Type</th><th>Commentaire</th><th>Action</th></tr></thead>
      <tbody></tbody>
    </table>
  </div>

  <div class="row mt-3">
    <div class="col">
      <button type="button" class="btn btn-secondary" onclick="prevStep()"><i class="fas fa-arrow-left"></i> Précédent</button>
    </div>
    <div class="col text-end">
      @can("ajouter_ecran_" . $ecran->id)
      <button type="button" class="btn btn-primary" onclick="saveStep4(nextStep)">Suivant <i class="fas fa-arrow-right"></i></button>
      @endcan
    </div>
  </div>
</div>

<script>
  let financementIndex = 0;

  $('#addFinancementBtn').on('click', function(){
    const bailleurLookup = document.getElementById('bailleur');
    const selected = bailleurLookup?.getSelected?.();
    const Financement = document.getElementById('typeFinancement');

    if(!selected?.value) return alert('Sélectionnez un bailleur.');
    if(!Financement.value) return alert('Sélectionnez le type de financement.');

    const montant = $('#montant').val(); if(!montant) return alert('Saisir le montant.');
    const typeFinText = Financement.selectedOptions[0]?.textContent ?? '';
    const localValue = document.querySelector('input[name="BaillOui"]:checked')?.value ?? '';
    const enChargeDeValue = $('#chargeDe').val(); const enChargeDeText = $('#chargeDe option:selected').text();
    const row = `
      <tr>
        <td>${selected.text}<input type="hidden" name="financements[${financementIndex}][bailleur]" value="${selected.value}"></td>
        <td>${enChargeDeText}<input type="hidden" name="financements[${financementIndex}][chargeDe]" value="${enChargeDeValue}"></td>
        <td class="text-end">${montant}<input type="hidden" name="financements[${financementIndex}][montant]" value="${montant}"></td>
        <td>{{ $Devises[0]->code_devise ?? 'XOF' }}<input type="hidden" name="financements[${financementIndex}][devise]" value="{{ $Devises[0]->code_devise ?? 'XOF' }}"></td>
        <td>${localValue==1?'Oui':'Non'}<input type="hidden" name="financements[${financementIndex}][local]" value="${localValue}"></td>
        <td>${typeFinText}<input type="hidden" name="financements[${financementIndex}][typeFinancement]" value="${Financement.value}"></td>
        <td>${$('#commentaire').val()}<input type="hidden" name="financements[${financementIndex}][commentaire]" value="${$('#commentaire').val()}"></td>
        <td><button type="button" class="btn btn-danger btn-sm removeRow"><i class="fas fa-trash"></i></button></td>
      </tr>`;
    $('#tableFinancements tbody').append(row); financementIndex++;

    // reset
    $('#montant,#commentaire').val('');
    $('#BailOui,#BailNon').prop('checked',false);
    $('#chargeDe').val('');
    bailleurLookup?.clear?.();
  });

  $('#tableFinancements').on('click','.removeRow', function(){ $(this).closest('tr').remove(); });

  function saveStep4(callback=null){
    localStorage.setItem("type_financement", $('#typeFinancement').val());
    const coutProjet = parseFloat(($('#coutProjet').val()||'').replace(/\s/g,'')) || 0;
    let somme=0; $('#tableFinancements tbody tr').each(function(){
      const m = ($(this).find('input[name$="[montant]"]').val()||'0').replace(/\s/g,''); somme += parseFloat(m)||0;
    });
    if(somme>coutProjet){ alert(`⚠️ La somme des financements (${somme.toLocaleString('fr-FR')}) dépasse le coût du projet (${coutProjet.toLocaleString('fr-FR')}).`); return; }

    const financements=[];
    $('#tableFinancements tbody tr').each(function(){
      financements.push({
        bailleur: $(this).find('input[name$="[bailleur]"]').val(),
        montant:  parseFloat(($(this).find('input[name$="[montant]"]').val()||'0').replace(/\s/g,'')) || 0,
        enChargeDe: $(this).find('input[name$="[chargeDe]"]').val() || null,
        devise: $(this).find('input[name$="[devise]"]').val(),
        local: $(this).find('input[name$="[local]"]').val(),
        commentaire: $(this).find('input[name$="[commentaire]"]').val() || ''
      });
    });
    if(!financements.length) return alert('Aucun financement ajouté.');

    $.post('{{ route("projet.etude.temp.save.step4") }}', {
      _token:'{{ csrf_token() }}',
      type_financement: $('#typeFinancement').val(),
      financements: financements
    }).done(()=>{ if(typeof callback==='function') callback(); else nextStep(); })
      .fail(xhr=>alert(xhr.responseJSON?.message||'Erreur Step 4'));
  }

  // bailleurs: local / non-local + champ "En charge de"
  document.addEventListener('DOMContentLoaded', function () {
    const radios = document.querySelectorAll('input[name="BaillOui"]');
    const bailleurLookup = document.getElementById('bailleur');
    const chargeDe = document.getElementById('chargeDeContainer');

    function handleChargeDeDisplay() {
      const selected = bailleurLookup.getSelected?.();
      if (selected?.value === '5689') { chargeDe.classList.remove('d-none'); }
      else { chargeDe.classList.add('d-none'); }
    }
    if(bailleurLookup){ bailleurLookup.addEventListener('ready', ()=>{ bailleurLookup.addEventListener('change', handleChargeDeDisplay); }); if(bailleurLookup.getSelected){ bailleurLookup.addEventListener('change', handleChargeDeDisplay); } }

    radios.forEach(r=>{
      r.addEventListener('change', function(){
        fetch(`{{ url('/') }}/get-bailleurs?local=${this.value}`)
          .then(res=>res.json())
          .then(data=>{
            const options = data.map(a=>({ value: a.code_acteur.toString(), text: `${a.libelle_court||''} ${a.libelle_long||''}`.trim(), codePays:a.code_pays }));
            bailleurLookup.setOptions?.(options); bailleurLookup.clear?.(); chargeDe.classList.add('d-none');
          });
      });
    });
  });
</script>

