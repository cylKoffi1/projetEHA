{{-- STEP 2 --}}
<div class="step" id="step-2">
@isset($ecran)
@can("consulter_ecran_" . $ecran->id)

<h5 class="text-secondary">📂 Sélection du projet</h5>

{{-- Filtres gelés --}}
<div>
  @foreach ($Pays as $alpha3 => $nom_fr_fr)
    <input type="hidden" id="filtrePays" value="{{ $alpha3 }}">
    <input type="hidden" id="filtrePaysLib" value="{{ $nom_fr_fr }}">
  @endforeach

  <input type="hidden" id="filtreGroupe" value="{{ $groupeSelectionne }}">
  <input type="hidden" id="filtreDomaineLib" readonly>
  <input type="hidden" id="filtreDomaine" value="">
  <input type="hidden" id="filtreSousDomaineLib" readonly>
  <input type="hidden" id="filtreSousDomaine" value="">
</div>

{{-- Fallback session si localStorage indisponible (lecture Step1 si dispo) --}}
<input type="hidden" id="filtreDomaineInit" value="{{ session('step1.code_domaine') }}">
<input type="hidden" id="filtreSousDomaineInit" value="{{ session('step1.code_sous_domaine') }}">
<input type="hidden" id="filtreDomaineLibInit"
       value="{{ optional($Domaines->firstWhere('code', session('step1.code_domaine')))->libelle }}">
<input type="hidden" id="filtreSousDomaineLibInit" value="">

{{-- Localisation du projet --}}
<div class="card">
  <div class="card-body">
    <h6 class="mb-3">📍 Localisation du projet</h6>

    <div class="row g-3">
      <div class="col-md-3">
        <label>Pays</label>
        @foreach ($Pays as $alpha3 => $nom_fr_fr)
          <input type="text" class="form-control" value="{{ $nom_fr_fr }}" id="locPaysLib" readonly>
          <input type="hidden" id="locPays" value="{{ $alpha3 }}">
        @endforeach
      </div>

      <div class="col-md-3">
        <label id="niveau1Label">Localité *</label>
        <lookup-select id="niveau1Select">
          <option value="">Sélectionnez une localité</option>
        </lookup-select>
      </div>

      <div class="col-md-3">
        <label id="niveau2Label">Niveau</label>
        <select class="form-control" id="niveau2Select" disabled>
          <option value="">Sélectionnez un niveau</option>
        </select>
      </div>

      <div class="col-md-3">
        <label id="niveau3Label">Découpage</label>
        <select class="form-control" id="niveau3Select" disabled>
          <option value="">Sélectionnez un découpage</option>
        </select>
      </div>
    </div>

    <div class="form-text mt-2">
      Sélectionnez la localité du projet d'appui. Le Niveau et le Découpage se remplissent automatiquement.
    </div>
  </div>
</div>

<hr class="my-3">

{{-- Sélecteur de projets --}}
<div class="row g-3 align-items-end">
  <div class="col-md-8">
    <label for="selectProjet" class="form-label">Projet *</label>
    <select id="selectProjet" class="form-control" disabled>
      <option value="">Sélectionnez un projet</option>
    </select>
    <div id="helpProjet" class="form-text">
      Les projets sont filtrés par pays, groupe, domaine et sous-domaine.
    </div>
  </div>
  <div class="col-md-4 text-end d-flex gap-2 justify-content-end">
    <button type="button" class="btn btn-outline-primary" id="btnChargerProjets">
      <i class="bi bi-search"></i> Charger les projets
    </button>
    <button type="button" class="btn btn-success" id="btnAjouter" disabled>
      <i class="bi bi-plus-circle"></i> Ajouter
    </button>
  </div>
</div>

{{-- Panneau d’infos de base --}}
<div id="blocInfosProjet" class="card mt-3 d-none">
  <div class="card-body">
    <h6 class="mb-3">🧾 Informations de base</h6>
    <div class="row g-3">
      <div class="col-md-4">
        <label>Code projet</label>
        <input type="text" class="form-control" id="infoCode" readonly>
      </div>
      <div class="col-md-8">
        <label>Intitulé</label>
        <input type="text" class="form-control" id="infoIntitule" readonly>
      </div>
      <div class="col-md-3">
        <label>Domaine</label>
        <input type="text" class="form-control" id="infoDomaine" readonly>
      </div>
      <div class="col-md-3">
        <label>Sous-domaine</label>
        <input type="text" class="form-control" id="infoSousDomaine" readonly>
      </div>
      <div class="col-md-3">
        <label>Début prévu</label>
        <input type="text" class="form-control" id="infoDateDebut" readonly>
      </div>
      <div class="col-md-3">
        <label>Fin prévue</label>
        <input type="text" class="form-control" id="infoDateFin" readonly>
      </div>
      <div class="col-md-3">
        <label>Coût du projet</label>
        <input type="text" class="form-control text-end" id="infoMontant" readonly>
      </div>
      <div class="col-md-2">
        <label>Devise</label>
        <input type="text" class="form-control" id="infoDevise" readonly>
      </div>
    </div>
  </div>
</div>

{{-- Tableau récapitulatif --}}
<div class="card mt-4">
  <div class="card-body">
    <h6 class="mb-3">📋 Liste des projets sélectionnés</h6>
    <div class="table-responsive">
      <table class="table table-bordered align-middle" id="tableSelections">
        <thead class="table-light">
          <tr>
            <th style="width:25%">Code projet</th>
            <th style="width:45%">Libellé projet</th>
            <th style="width:25%">Localité</th>
            <th style="width:5%" class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<div class="row mt-4">
  <div class="col">
    <button type="button" class="btn btn-secondary" onclick="prevStep()">
      <i class="fas fa-arrow-left"></i> Précédent
    </button>
  </div>
  <div class="col text-end">
    @can("ajouter_ecran_" . $ecran->id)
    <button type="button" class="btn btn-primary" id="btnValiderProjet" disabled>
      Suivant <i class="fas fa-arrow-right"></i>
    </button>
    @endcan
  </div>
</div>

@endcan
@endisset
</div>

<script>
(function () {
  /* ================== ETAT GLOBAL EN MÉMOIRE (pas de localStorage) ================== */
  let selections = []; // [{code_projet, libelle_projet, localite_libelle}]
  window.__PROJETS_CACHE__ = window.__PROJETS_CACHE__ || {}; // rempli par /projets.search

  /* ================== OUTILS ================== */
  function getDomOrStorage(selId, keyCode, keyLib) {
    // garde la logique de fallback naif si step1 est encore affiché sur la page
    const el = document.getElementById(selId);
    const has = !!(el && el.value && el.value.trim() !== '');
    const code = has ? el.value : (localStorage.getItem ? (localStorage.getItem(keyCode) || '') : '');
    const lib  = has ? (el.selectedOptions?.[0]?.text || '') : (localStorage.getItem ? (localStorage.getItem(keyLib) || '') : '');
    return { code, lib };
  }

  function readStep1Filters(forceRefresh = false) {
      const pays    = document.getElementById('filtrePays')?.value || '';
      const paysLib = document.getElementById('filtrePaysLib')?.value || '';

      // On relit EN DIRECT les selects de l'étape 1 si présents dans le DOM
      const domaineSelectEl   = document.getElementById('domaineSelect');
      const sousDomSelectEl   = document.getElementById('sousDomaineSelect');

      let codeDom  = '';
      let libDom   = '';
      let codeSdom = '';
      let libSdom  = '';

      if (domaineSelectEl && domaineSelectEl.value) {
          codeDom = domaineSelectEl.value;
          libDom  = domaineSelectEl.options[domaineSelectEl.selectedIndex]?.text || '';
      }
      if (sousDomSelectEl && sousDomSelectEl.value) {
          codeSdom = sousDomSelectEl.value;
          libSdom  = sousDomSelectEl.options[sousDomSelectEl.selectedIndex]?.text || '';
      }

      // Fallback si on arrive direct sur step 2 après reload
      if (!codeDom)  codeDom  = localStorage.getItem('step1_code_domaine')
                      || document.getElementById('filtreDomaineInit')?.value
                      || '';
      if (!libDom)   libDom   = localStorage.getItem('step1_lib_domaine')
                      || document.getElementById('filtreDomaineLibInit')?.value
                      || '';
      if (!codeSdom) codeSdom = localStorage.getItem('step1_code_sdomaine')
                      || document.getElementById('filtreSousDomaineInit')?.value
                      || '';
      if (!libSdom)  libSdom  = localStorage.getItem('step1_lib_sdomaine')
                      || document.getElementById('filtreSousDomaineLibInit')?.value
                      || '';

      // Mets aussi à jour les <input hidden>
      document.getElementById('filtreDomaine').value        = codeDom;
      document.getElementById('filtreDomaineLib').value     = libDom || '—';
      document.getElementById('filtreSousDomaine').value    = codeSdom;
      document.getElementById('filtreSousDomaineLib').value = libSdom || '—';

      // 🔍 LOG ici pour debug à chaque recalcul
      console.log('[readStep1Filters] =>', {
          pays,
          paysLib,
          codeDom,         // domaine code
          libDom,          // domaine libellé
          codeSdom,        // sous-domaine code
          libSdom          // sous-domaine libellé
      });

      if (forceRefresh) {
          console.log('[readStep1Filters] (forceRefresh=true) filtres utilisés pour la recherche projets');
      }

      return { pays, paysLib, codeDom, libDom, codeSdom, libSdom };
  }

  function formatMoney(x) {
    if (x === null || x === undefined || x === '') return '';
    const n = Number(x); if (isNaN(n)) return x;
    return n.toLocaleString('fr-FR', { maximumFractionDigits: 0 });
  }

  /* ================== LOCALISATION ================== */
  let selectedLocalite = {
    id: null, code_rattachement: null, libelle: null,
    niveau: null, code_decoupage: null, libelle_decoupage: null
  };

  function loadLocalites() {
    const paysCode = document.getElementById('filtrePays')?.value || document.getElementById('locPays')?.value || '';
    if (!paysCode) return;

    fetch(`{{ url('/') }}/get-localites/${paysCode}`)
      .then(r => r.json())
      .then(data => {
        const $lookup = document.getElementById('niveau1Select');
        $lookup.innerHTML = '<option value="">Sélectionnez une localité</option>';

        const libCount = {};
        (Array.isArray(data) ? data : []).forEach(loc => {
          const key = (loc.libelle || '').trim().toLowerCase();
          libCount[key] = (libCount[key] || 0) + 1;
        });

        (Array.isArray(data) ? data : []).forEach(loc => {
          const dup = libCount[(loc.libelle || '').trim().toLowerCase()] > 1;
          const label = dup && loc.libelle_decoupage ? `${loc.libelle} (${loc.libelle_decoupage})` : loc.libelle;

          const opt = document.createElement('option');
          opt.value = loc.id;
          opt.textContent = label;
          opt.setAttribute('data-code', loc.code_rattachement || '');
          $lookup.appendChild(opt);
        });
      })
      .catch(() => console.error('Erreur localités.'));
  }

  function onLocaliteChanged() {
    const lookup = document.getElementById("niveau1Select");
    const lId = lookup.value;

    const selected = lookup.getSelected ? lookup.getSelected() : null;
    const localiteText = selected ? selected.text : (lookup.selectedOptions?.[0]?.textContent || '');
    const codeRattach  = selected ? selected.code : (lookup.selectedOptions?.[0]?.getAttribute('data-code') || null);

    selectedLocalite.id = lId || null;
    selectedLocalite.libelle = localiteText || '';
    selectedLocalite.code_rattachement = codeRattach || null;

    if (!lId) {
      document.getElementById("niveau2Select").innerHTML = '<option value="">Sélectionnez un niveau</option>';
      document.getElementById("niveau2Select").disabled = true;
      document.getElementById("niveau3Select").innerHTML = '<option value="">Sélectionnez un découpage</option>';
      document.getElementById("niveau3Select").disabled = true;
      selectedLocalite = { id:null, code_rattachement:null, libelle:null, niveau:null, code_decoupage:null, libelle_decoupage:null };
      return;
    }

    fetch(`{{ url('/') }}/get-decoupage-niveau/${lId}`)
      .then(r => r.json())
      .then(data => {
        const n2 = document.getElementById("niveau2Select");
        const n3 = document.getElementById("niveau3Select");

        n2.innerHTML = `<option value="${data.niveau ?? ''}">${data.niveau ?? ''}</option>`;
        n2.disabled = false;

        n3.innerHTML = `<option value="${data.code_decoupage ?? ''}">${data.libelle_decoupage ?? ''}</option>`;
        n3.disabled = false;

        // force en string
        selectedLocalite.niveau            = (data.niveau ?? '').toString();
        selectedLocalite.code_decoupage    = (data.code_decoupage ?? '').toString();
        selectedLocalite.libelle_decoupage = (data.libelle_decoupage ?? '').toString();

        console.log('[Step2] Localité sélectionnée', selectedLocalite);
      })
      .catch(() => console.error('Erreur découpage.'));
  }

  /* ================== CHARGEMENT PROJETS ================== */
  function loadProjets() {
      // ⬇️ On relit les filtres à chaque clic
      const { pays, codeDom: fDom, codeSdom: fSdom } = readStep1Filters(true);

      const $select = document.getElementById('selectProjet');
      const $btnAdd = document.getElementById('btnAjouter');
      const $btnNext= document.getElementById('btnValiderProjet');
      const $bloc   = document.getElementById('blocInfosProjet');

      $select.innerHTML = '<option value="">Sélectionnez un projet</option>';
      $select.disabled  = true;
      $btnAdd.disabled  = true;
      $btnNext.disabled = selections.length === 0;
      $bloc.classList.add('d-none');

      if (!pays || !fDom || !fSdom) {
          document.getElementById('helpProjet').innerText =
            "Sélectionnez le domaine et le sous-domaine à l’étape 1 puis revenez ici.";
          console.warn('[loadProjets] Impossible de charger: filtres incomplets', { pays, fDom, fSdom });
          return;
      }

      const url = `{{ route('projets.search') }}?domaine=${encodeURIComponent(fDom)}&SousDomaine=${encodeURIComponent(fSdom)}&pays=${encodeURIComponent(pays)}`;

      // 🔎 LOG AVANT FETCH : tu verras exactement ce qui part
      console.log('[loadProjets] URL utilisée :', url);
      console.log('[loadProjets] Paramètres envoyés :', {
          domaine: fDom,
          sousDomaine: fSdom,
          pays: pays
      });

      document.getElementById('helpProjet').innerText = "Chargement…";

      fetch(url)
        .then(r => r.json())
        .then(rows => {
          console.log('[loadProjets] Réponse projets :', rows);

          document.getElementById('helpProjet').innerText =
            (rows && rows.length) ? "Projets chargés." : "Aucun projet trouvé pour ces filtres.";

          if (!rows || !rows.length) return;

          window.__PROJETS_CACHE__ = {};
          rows.forEach(p => {
            window.__PROJETS_CACHE__[p.code_projet] = p;
            const label = `${p.code_projet} — ${p.libelle_projet ?? ''}`;
            $select.add(new Option(label, p.code_projet));
          });

          $select.disabled = false;

          if (rows.length === 1) {
            $select.value = rows[0].code_projet;
            onProjetChanged();
          }
        })
        .catch((e) => {
          console.error('[loadProjets] Erreur fetch projets', e);
          document.getElementById('helpProjet').innerText = "Erreur de chargement.";
        });
  }

  function onProjetChanged() {
    const code  = document.getElementById('selectProjet').value;
    const $bloc = document.getElementById('blocInfosProjet');
    const $btnAdd = document.getElementById('btnAjouter');

    $btnAdd.disabled = !code;
    if (!code) { $bloc.classList.add('d-none'); return; }

    const p = window.__PROJETS_CACHE__?.[code];
    if (p) {
      document.getElementById('infoCode').value = p.code_projet || '';
      document.getElementById('infoIntitule').value = p.libelle_projet || '';
      document.getElementById('infoDomaine').value = p.domaine_libelle || p.code_domaine || '';
      document.getElementById('infoSousDomaine').value = p.sous_domaine_libelle || p.code_sous_domaine || '';
      document.getElementById('infoDateDebut').value = (p.date_demarrage_prevue ?? '').substring(0,10);
      document.getElementById('infoDateFin').value = (p.date_fin_prevue ?? '').substring(0,10);
      document.getElementById('infoMontant').value = formatMoney(p.cout_projet);
      document.getElementById('infoDevise').value = p.code_devise || '';
      $bloc.classList.remove('d-none');
      console.log('[Step2] Projet sélectionné', p);
    }
  }

  /* ================== TABLEAU / SÉLECTIONS EN MÉMOIRE ================== */
  const tableBody   = document.querySelector("#tableSelections tbody");
  const btnAjouter  = document.getElementById("btnAjouter");
  const selectProjet= document.getElementById("selectProjet");
  const btnSuivant  = document.getElementById("btnValiderProjet");

  function renderTable() {
    tableBody.innerHTML = "";
    selections.forEach(item => {
      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td><strong>${item.code_projet}</strong></td>
        <td>${item.libelle_projet || ""}</td>
        <td>${item.localite_libelle || "—"}</td>
        <td class="text-center">
          <button class="btn btn-sm btn-outline-danger btnDeleteRow" title="Supprimer" data-code="${item.code_projet}">
            <i class="bi bi-trash"></i>
          </button>
        </td>
      `;
      tableBody.appendChild(tr);
    });

    // bind delete
    tableBody.querySelectorAll('.btnDeleteRow').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const code = e.currentTarget.getAttribute('data-code');
        selections = selections.filter(s => s.code_projet !== code);
        console.log('[Step2] Suppression', code, '=>', selections);
        renderTable();
        updateButtonsState();
      });
    });
  }

  function addCurrentSelectionToTable() {
      const code = selectProjet.value;
      if (!code) { alert("Sélectionnez un projet."); return; }

      const p = window.__PROJETS_CACHE__?.[code] || {};
      const libelle = (document.getElementById('infoIntitule')?.value || p.libelle_projet || "");
      const localiteLibelle = (selectedLocalite?.libelle || "");

      // Empêche les doublons dans le tableau visuel
      if (selections.some(s => s.code_projet === code)) return;

      // ⬇️ ICI : on garde le projet courant pour l'étape 3
      try {
          localStorage.setItem("code_projet_temp", code);
          localStorage.setItem('libelle_projet_temp', libelle);
          console.log("[Step2] code_projet_temp enregistré :", code);
      } catch(e) {
          console.warn("[Step2] Impossible d'écrire code_projet_temp dans localStorage :", e);
      }

      selections.push({
        code_projet: code,
        libelle_projet: libelle,
        localite_libelle: localiteLibelle
      });
      console.log('[Step2] Ajout sélection', selections);
      renderTable();
      updateButtonsState();
  }

  function updateButtonsState() {
    btnAjouter.disabled = !selectProjet.value;
    btnSuivant.disabled = selections.length === 0;
  }

  /* ================== SAUVEGARDE (POST LISTE) ================== */
  function saveAllAndNext() {
      if (!selections.length) {
        alert("Ajoutez au moins un projet dans la liste.");
        return;
      }

      const projetsEnrichis = selections.map(s => {
        const p = (window.__PROJETS_CACHE__ || {})[s.code_projet] || {};
        return {
          code_projet: s.code_projet,
          libelle: s.libelle_projet || p.libelle_projet || '',
          localite: s.localite_libelle || '',
          details: {
            code_alpha3_pays: p.code_alpha3_pays ?? document.getElementById('filtrePays')?.value ?? null,
            code_domaine: p.code_domaine ?? document.getElementById('filtreDomaine')?.value ?? null,
            domaine_libelle: p.domaine_libelle ?? document.getElementById('filtreDomaineLib')?.value ?? null,
            code_sous_domaine: p.code_sous_domaine ?? document.getElementById('filtreSousDomaine')?.value ?? null,
            sous_domaine_libelle: p.sous_domaine_libelle ?? document.getElementById('filtreSousDomaineLib')?.value ?? null,
            date_demarrage_prevue: p.date_demarrage_prevue ?? document.getElementById('infoDateDebut')?.value ?? null,
            date_fin_prevue: p.date_fin_prevue ?? document.getElementById('infoDateFin')?.value ?? null,
            cout_projet: p.cout_projet ?? null,
            code_devise: p.code_devise ?? null,
            libelle_projet: p.libelle_projet ?? document.getElementById('infoIntitule')?.value ?? '',
          }
        };
      });

      // ⬇️ ICI : on mémorise le projet choisi pour l'étape 3
      // on prend le premier élément de la liste sélectionnée
      const projetPrincipal = projetsEnrichis[0];
      if (projetPrincipal && projetPrincipal.code_projet) {
          try {
              localStorage.setItem("code_projet_temp", projetPrincipal.code_projet);
              console.log("[Step2] code_projet_temp (depuis saveAllAndNext) :", projetPrincipal.code_projet);
          } catch(e) {
              console.warn("[Step2] Impossible d'écrire code_projet_temp dans localStorage :", e);
          }
      }

      const payload = {
        projets: projetsEnrichis,
        localite: selectedLocalite.id ? {
          id: selectedLocalite.id,
          code_rattachement: selectedLocalite.code_rattachement,
          libelle: selectedLocalite.libelle,
          niveau: (selectedLocalite.niveau ?? '').toString(),
          code_decoupage: (selectedLocalite.code_decoupage ?? '').toString(),
          libelle_decoupage: (selectedLocalite.libelle_decoupage ?? '').toString()
        } : null
      };

      console.log('[Step2] Payload envoyé', payload);

      fetch(`{{ route('projet.appui.temp.save.step2') }}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept'      : 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(payload)
      })
      .then(async (r) => {
        const isJson = (r.headers.get('content-type') || '').includes('application/json');
        const data = isJson ? await r.json() : await r.text();

        console.log('[Step2] Réponse', { status: r.status, data });

        if (!r.ok) {
          if (r.status === 422 && isJson && data?.errors) {
            const first = Object.values(data.errors)[0];
            alert(`Erreur de validation : ${Array.isArray(first) ? first[0] : first}`);
          } else {
            alert(typeof data === 'string' ? data : (data?.message || 'Erreur lors de la sauvegarde.'));
          }
          return;
        }

        if (data?.success) {
          selections = [];
          renderTable();
          updateButtonsState();
          if (typeof nextStep === 'function') nextStep();
        } else {
          alert(data?.message || 'Erreur lors de la sauvegarde.');
        }
      })
      .catch((e) => {
        console.error('[Step2] Exception fetch', e);
        alert('Erreur lors de la sauvegarde (réseau).');
      });
  }

  /* ================== INIT & EVENTS ================== */
  readStep1Filters();
  loadProjets();
  loadLocalites();

  document.getElementById('btnChargerProjets').addEventListener('click', loadProjets);
  document.getElementById('selectProjet').addEventListener('change', () => { onProjetChanged(); updateButtonsState(); });
  document.getElementById('niveau1Select').addEventListener('change', onLocaliteChanged);
  document.getElementById('btnAjouter').addEventListener('click', addCurrentSelectionToTable);
  document.getElementById('btnValiderProjet').addEventListener('click', saveAllAndNext);

  // état initial
  updateButtonsState();
})();
</script>
