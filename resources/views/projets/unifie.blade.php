@extends('layouts.app')

<style>
    body { background-color: #f8f9fa; }
    .container {
        background: white; padding: 30px; border-radius: 10px;
        box-shadow: 0px 0px 10px rgba(0,0,0,.1);
    }
    .form-group label { font-weight: bold; }
    .checkbox-group {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,.1);
        margin-bottom: 30px;
    }
    .checkbox-item {
        display: flex;
        align-items: center;
        padding: 12px;
        margin: 8px 0;
        border: 2px solid #e0e0e0;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s;
    }
    .checkbox-item:hover {
        background: #f5f5f5;
        border-color: #007bff;
    }
    .checkbox-item input[type="checkbox"] {
        width: 20px;
        height: 20px;
        margin-right: 12px;
        cursor: pointer;
    }
    .checkbox-item label {
        cursor: pointer;
        font-weight: 600;
        font-size: 16px;
        margin: 0;
        flex: 1;
    }
    .form-container {
        display: none !important;
        margin-top: 20px;
        padding: 20px;
        border: 2px solid #007bff;
        border-radius: 8px;
        background: #fff;
    }
    .form-container.active {
        display: block !important;
        animation: fadeIn 0.3s;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .badge-direct {
        background: #28a745;
        color: white;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        margin-left: 10px;
    }
</style>

@section('content')

@can("consulter_ecran_" . $ecran->id)

<section id="multiple-column-form">
  <div class="page-heading">
    <div class="page-title">
      <div class="row">
        <div class="col-sm-12">
          <li class="breadcrumb-item" style="list-style:none; text-align:right; padding:5px;">
            <span id="date-now" style="color:#34495E;"></span>
          </li>
        </div>
      </div>
      <div class="row">
        <div class="col-12 col-md-6 order-md-1 order-last">
          <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Création de Projets</h3>
        </div>
        <div class="col-12 col-md-6 order-md-2 order-first">
          <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="">Projets</a></li>
              <li class="breadcrumb-item active" aria-current="page">Création unifiée (Sans Validation)</li>
            </ol>
            <div class="row">
              <script>
                setInterval(function() {
                  document.getElementById('date-now').textContent = new Date().toLocaleString();
                }, 1000);
              </script>
            </div>
          </nav>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title">
          Sélectionnez le(s) type(s) de projet(s) à créer
          <span class="badge-direct">Enregistrement Direct (Sans Validation)</span>
        </h5>
      </div>

      <div class="card-content">
        <div class="col-12">
          <div class="container mt-5">
            
            <!-- Checkboxes de sélection -->
            <div class="checkbox-group">
              <h4 class="mb-4">Types de projets :</h4>
              <div class="checkbox-item">
                <input type="checkbox" id="chk-infrastructure" name="project_types[]" value="infrastructure" onchange="toggleForm('infrastructure')">
                <label for="chk-infrastructure" style="cursor: pointer;">📐 Infrastructure (Projet d'infrastructure)</label>
              </div>
              <div class="checkbox-item">
                <input type="checkbox" id="chk-etude" name="project_types[]" value="etude" onchange="toggleForm('etude')">
                <label for="chk-etude" style="cursor: pointer;">📊 Étude de Projet</label>
              </div>
              <div class="checkbox-item">
                <input type="checkbox" id="chk-appui" name="project_types[]" value="appui" onchange="toggleForm('appui')">
                <label for="chk-appui" style="cursor: pointer;">🤝 Projet d'Appui</label>
              </div>
            </div>

            <!-- Formulaire Infrastructure -->
            <div id="form-infrastructure" class="form-container">
              <div class="card">
                <div class="card-header">
                  <h5 class="card-title">📐 Infrastructure (Projet d'infrastructure)</h5>
                  <span class="badge bg-success">Enregistrement Direct (Sans Validation)</span>
                </div>
                <div class="card-content">
                  <div class="col-12">
                    <div class="container mt-3">
                      <div class="progress mb-4">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 20%;" id="progressBar-infra"></div>
                      </div>
                      @canany(["ajouter_ecran_" . $ecran->id, "modifier_ecran_" . $ecran->id])
                      <form class="col-12" id="projectForm-infra">
                        @php $directMode = true; @endphp
                        @include('etudes_projets.steps.document', ['directMode' => true, 'ecran' => $ecran, 'NaturesTravaux' => $NaturesTravaux, 'GroupeProjets' => $GroupeProjets, 'groupeSelectionne' => $groupeSelectionne, 'Domaines' => $Domaines, 'SousDomaines' => $SousDomaines, 'SecteurActivites' => $SecteurActivites, 'Pays' => $Pays, 'deviseCouts' => $deviseCouts, 'Devises' => $Devises, 'unitesDerivees' => $unitesDerivees, 'acteurs' => $acteurs, 'typeFinancements' => $typeFinancements, 'generatedCodeProjet' => $generatedCodeProjet])
                        @include('etudes_projets.steps.Financement', ['directMode' => true, 'ecran' => $ecran, 'typeFinancements' => $typeFinancements, 'bailleurActeurs' => $bailleurActeurs, 'Devises' => $Devises, 'unitesDerivees' => $unitesDerivees])
                        @include('etudes_projets.steps.maitreOuvre', ['directMode' => true, 'ecran' => $ecran, 'acteurs' => $acteurs])
                        @include('etudes_projets.steps.maitreOuvrages', ['directMode' => true, 'ecran' => $ecran, 'acteurs' => $acteurs])
                        @include('etudes_projets.steps.Information_Generales', ['directMode' => true, 'ecran' => $ecran, 'NaturesTravaux' => $NaturesTravaux, 'GroupeProjets' => $GroupeProjets, 'groupeSelectionne' => $groupeSelectionne, 'Domaines' => $Domaines, 'SousDomaines' => $SousDomaines, 'SecteurActivites' => $SecteurActivites, 'Pays' => $Pays, 'deviseCouts' => $deviseCouts, 'Devises' => $Devises, 'unitesDerivees' => $unitesDerivees, 'acteurs' => $acteurs, 'typeFinancements' => $typeFinancements, 'generatedCodeProjet' => $generatedCodeProjet])
                        @include('etudes_projets.steps.Infrastructure', ['directMode' => true, 'ecran' => $ecran, 'infrastructures' => $infrastructures, 'familleInfrastructures' => $familleInfrastructures, 'Domaines' => $Domaines, 'SousDomaines' => $SousDomaines])
                        @include('etudes_projets.steps.actionAMener', ['directMode' => true, 'ecran' => $ecran, 'actionMener' => $actionMener])
                      </form>
                      @else
                      <div class="alert alert-info mt-3">
                        Vous pouvez consulter cette page, mais vous n'avez pas les droits pour créer ou modifier ce projet.
                      </div>
                      @endcanany
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Formulaire Étude -->
            <div id="form-etude" class="form-container">
              <div class="card">
                <div class="card-header">
                  <h5 class="card-title">📊 Étude de Projet</h5>
                  <span class="badge bg-success">Enregistrement Direct (Sans Validation)</span>
                </div>
                <div class="card-content">
                  <div class="col-12">
                    <div class="container mt-3">
                      <div class="progress mb-4">
                        <div class="progress-bar bg-success" role="progressbar" id="progressBar-etude"></div>
                      </div>
                      @canany(["ajouter_ecran_" . $ecran->id, "modifier_ecran_" . $ecran->id])
                      <form class="col-12" id="projectForm-etude">
                        @php 
                          $generatedCodeEtude = 'TMP-' . strtoupper(\Illuminate\Support\Str::random(6));
                        @endphp
                        @include('projet_etude.steps.Information_Generales', ['directMode' => true, 'ecran' => $ecran, 'EtudeTypes' => $EtudeTypes ?? collect(), 'Livrables' => $Livrables ?? collect(), 'NaturesTravaux' => $NaturesTravaux, 'GroupeProjets' => $GroupeProjets, 'Domaines' => $Domaines, 'SousDomaines' => $SousDomaines, 'SecteurActivites' => $SecteurActivites, 'Pays' => $Pays, 'deviseCouts' => $deviseCouts, 'groupeSelectionne' => $groupeSelectionne, 'Devises' => $Devises, 'unitesDerivees' => $unitesDerivees, 'acteurs' => $acteurs, 'typeFinancements' => $typeFinancements, 'generatedCodeProjet' => $generatedCodeEtude])
                        @include('projet_etude.steps.maitreOuvrages', ['directMode' => true, 'ecran' => $ecran, 'acteurs' => $acteurs])
                        @include('projet_etude.steps.maitreOuvre', ['directMode' => true, 'ecran' => $ecran, 'acteurs' => $acteurs])
                        @include('projet_etude.steps.Financement', ['directMode' => true, 'ecran' => $ecran, 'typeFinancements' => $typeFinancements, 'bailleurActeurs' => $bailleurActeurs, 'Devises' => $Devises, 'unitesDerivees' => $unitesDerivees])
                        @include('projet_etude.steps.document', ['directMode' => true, 'ecran' => $ecran, 'EtudeTypes' => $EtudeTypes ?? collect(), 'Livrables' => $Livrables ?? collect(), 'NaturesTravaux' => $NaturesTravaux, 'GroupeProjets' => $GroupeProjets, 'Domaines' => $Domaines, 'SousDomaines' => $SousDomaines, 'SecteurActivites' => $SecteurActivites, 'Pays' => $Pays, 'deviseCouts' => $deviseCouts, 'groupeSelectionne' => $groupeSelectionne, 'Devises' => $Devises, 'unitesDerivees' => $unitesDerivees, 'acteurs' => $acteurs, 'typeFinancements' => $typeFinancements, 'generatedCodeProjet' => $generatedCodeEtude])
                      </form>
                      @else
                      <div class="alert alert-info mt-3">
                        Vous pouvez consulter cette page, mais vous n'avez pas les droits pour créer ou modifier ce projet.
                      </div>
                      @endcanany
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Formulaire Appui -->
            <div id="form-appui" class="form-container">
              <div class="card">
                <div class="card-header">
                  <h5 class="card-title">🤝 Projet d'Appui</h5>
                  <span class="badge bg-success">Enregistrement Direct (Sans Validation)</span>
                </div>
                <div class="card-content">
                  <div class="col-12">
                    <div class="container mt-3">
                      <div class="progress mb-4">
                        <div class="progress-bar bg-success" role="progressbar" id="progressBar-appui"></div>
                      </div>
                      @canany(["ajouter_ecran_" . $ecran->id, "modifier_ecran_" . $ecran->id])
                      <form class="col-12" id="projectForm-appui">
                        @php 
                          $generatedCodeAppui = 'TMP-' . strtoupper(\Illuminate\Support\Str::random(6));
                        @endphp
                        @include('projet_appui.steps.Information_Generales', ['directMode' => true, 'ecran' => $ecran, 'NaturesTravaux' => $NaturesTravaux, 'GroupeProjets' => $GroupeProjets, 'Domaines' => $Domaines, 'SousDomaines' => $SousDomaines, 'SecteurActivites' => $SecteurActivites, 'Pays' => $Pays, 'deviseCouts' => $deviseCouts, 'groupeSelectionne' => $groupeSelectionne, 'Devises' => $Devises, 'unitesDerivees' => $unitesDerivees, 'acteurs' => $acteurs, 'typeFinancements' => $typeFinancements, 'generatedCodeProjet' => $generatedCodeAppui])
                        @include('projet_appui.steps.projetselect', ['directMode' => true, 'ecran' => $ecran])
                        @include('projet_appui.steps.beneficiaire', ['directMode' => true, 'ecran' => $ecran])
                        @include('projet_appui.steps.maitreOuvrages', ['directMode' => true, 'ecran' => $ecran, 'acteurs' => $acteurs])
                        @include('projet_appui.steps.maitreOuvre', ['directMode' => true, 'ecran' => $ecran, 'acteurs' => $acteurs])
                        @include('projet_appui.steps.Financement', ['directMode' => true, 'ecran' => $ecran, 'typeFinancements' => $typeFinancements, 'bailleurActeurs' => $bailleurActeurs, 'Devises' => $Devises, 'unitesDerivees' => $unitesDerivees])
                        @include('projet_appui.steps.document', ['directMode' => true, 'ecran' => $ecran, 'NaturesTravaux' => $NaturesTravaux, 'GroupeProjets' => $GroupeProjets, 'Domaines' => $Domaines, 'SousDomaines' => $SousDomaines, 'SecteurActivites' => $SecteurActivites, 'Pays' => $Pays, 'deviseCouts' => $deviseCouts, 'groupeSelectionne' => $groupeSelectionne, 'Devises' => $Devises, 'unitesDerivees' => $unitesDerivees, 'acteurs' => $acteurs, 'typeFinancements' => $typeFinancements, 'generatedCodeProjet' => $generatedCodeAppui])
                      </form>
                      @else
                      <div class="alert alert-info mt-3">
                        Vous pouvez consulter cette page, mais vous n'avez pas les droits pour créer ou modifier ce projet.
                      </div>
                      @endcanany
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
function toggleForm(type) {
    console.log('Toggle form for:', type);
    const checkbox = document.getElementById('chk-' + type);
    const formContainer = document.getElementById('form-' + type);
    
    if (!checkbox || !formContainer) {
        console.error('Element not found for type:', type);
        return;
    }
    
    // Afficher/masquer le formulaire selon l'état de la checkbox
    if (checkbox.checked) {
        formContainer.classList.add('active');
        formContainer.style.display = 'block';
        console.log('Form shown:', type);
    } else {
        formContainer.classList.remove('active');
        formContainer.style.display = 'none';
        console.log('Form hidden:', type);
    }
}

// Initialiser l'affichage au chargement
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing forms...');
    ['infrastructure', 'etude', 'appui'].forEach(type => {
        const checkbox = document.getElementById('chk-' + type);
        const formContainer = document.getElementById('form-' + type);
        
        if (checkbox && formContainer) {
            // Initialiser l'état selon la checkbox
            if (checkbox.checked) {
                formContainer.classList.add('active');
                formContainer.style.display = 'block';
            } else {
                formContainer.classList.remove('active');
                formContainer.style.display = 'none';
            }
        }
    });
});
</script>

@else
  <div class="alert alert-warning mt-3">
    Vous n'avez pas les droits pour consulter cette page.
  </div>
@endcan

@endsection

