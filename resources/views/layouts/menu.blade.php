<!doctype html>
<html class="no-js" lang="fr">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

  <!-- SEO Meta Tags -->
  <meta name="description" content="GESPRO-INFRAS - Spécialiste en gestion de projet et infrastructure de pays">
  
  @include('layouts.lurl')

  <!-- Bootstrap CSS -->
  <link href="{{ asset('betsa/assets/css/bootstrap.min.css') }}" rel="stylesheet">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    :root {
      --primary-color: #435EBE;
      --primary-hover: #374e9e;
      --text-light: #ffffff;
      --text-muted: rgba(255, 255, 255, 0.8);
      --transition-speed: 0.3s;
    }

    /* === HEADER STYLES === */
    .navbar {
      background-color: var(--primary-color);
      padding: 15px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: relative;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    /* Logo */
    .navbar-brand {
      color: var(--text-light);
      text-decoration: none;
      font-size: 2.5rem;
      font-weight: 700;
      display: flex;
      align-items: center;
    }

    .navbar-brand span {
      font-size: 0.8rem;
      margin-left: 8px;
      opacity: 0.8;
    }

    /* Menu toggle button */
    .navbar-toggle {
      display: none;
      background: none;
      border: none;
      color: var(--text-light);
      font-size: 1.5rem;
      cursor: pointer;
      padding: 5px;
    }

    /* Menu container */
    .navbar-collapse {
      display: flex;
      transition: all var(--transition-speed) ease;
    }

    /* Menu list */
    .navbar-nav {
      display: flex;
      list-style: none;
      margin: 0;
      padding: 0;
    }

    /* Menu items */
    .nav-item {
      margin-left: 15px;
    }

    /* Menu links */
    .nav-link {
      color: var(--text-light);
      text-decoration: none;
      padding: 8px 15px;
      border-radius: 4px;
      transition: all var(--transition-speed) ease;
      display: flex;
      align-items: center;
      font-weight: 500;
    }

    .nav-link i {
      margin-right: 8px;
      font-size: 0.9rem;
    }

    .nav-link:hover {
      background-color: rgba(255, 255, 255, 0.15);
    }

    /* Active link */
    .nav-link.active {
      background-color: rgba(255, 255, 255, 0.2);
    }

    /* === MODAL STYLES === */
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.7);
      z-index: 1000;
      align-items: center;
      justify-content: center;
      opacity: 0;
      transition: opacity var(--transition-speed) ease;
    }

    .modal.show {
      display: flex;
      opacity: 1;
    }

    .modal-content {
      background: white;
      border-radius: 8px;
      width: 90%;
      max-width: 800px;
      max-height: 90vh;
      overflow-y: auto;
      transform: translateY(8%);
      transition: transform var(--transition-speed) ease;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
    }

    .modal-header {
      padding: 20px;
      border-bottom: 1px solid #eee;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .modal-title {
      margin: 0;
      font-size: 1.5rem;
      color: var(--primary-color);
    }

    .close {
      font-size: 1.5rem;
      cursor: pointer;
      color: #777;
      transition: color var(--transition-speed) ease;
    }

    .close:hover {
      color: #333;
    }

    .modal-body {
      padding: 20px;
    }

    /* === FORM STYLES === */
    .form-group {
      margin-bottom: 20px;
    }

    .radio-group {
      display: flex;
      gap: 20px;
      margin-top: 10px;
    }

    .radio-group label {
      display: flex;
      align-items: center;
      cursor: pointer;
    }

    .radio-group input[type="radio"] {
      margin-right: 8px;
    }

    /* Tabs */
    .tabs {
      display: flex;
      border-bottom: 1px solid #ddd;
      margin-bottom: 20px;
    }

    .tab-link {
      padding: 12px 20px;
      cursor: pointer;
      background: #f8f9fa;
      border: 1px solid #ddd;
      border-bottom: none;
      margin-right: 5px;
      border-radius: 5px 5px 0 0;
      transition: all var(--transition-speed) ease;
    }

    .tab-link.active {
      background: white;
      border-bottom: 1px solid white;
      margin-bottom: -1px;
      color: var(--primary-color);
      font-weight: 600;
    }

    .tab-pane {
      display: none;
      animation: fadeIn var(--transition-speed) ease;
    }

    .tab-pane.active {
      display: block;
    }

    /* Form fields */
    .form-control {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 4px;
      transition: border-color var(--transition-speed) ease;
    }

    .form-control:focus {
      border-color: var(--primary-color);
      outline: none;
      box-shadow: 0 0 0 2px rgba(67, 94, 190, 0.2);
    }

    .btn-primary {
      background-color: var(--primary-color);
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      transition: background-color var(--transition-speed) ease;
    }

    .btn-primary:hover {
      background-color: var(--primary-hover);
    }

    /* Responsive grid */
    .row {
      display: flex;
      flex-wrap: wrap;
      margin: 0 -10px;
    }

    .col-md-6 {
      flex: 0 0 50%;
      max-width: 50%;
      padding: 0 10px;
      box-sizing: border-box;
    }

    /* Notification */
    .notification {
      position: fixed;
      top: 20px;
      left: 50%;
      transform: translateX(-50%);
      padding: 15px 25px;
      border-radius: 4px;
      color: white;
      z-index: 1100;
      display: flex;
      align-items: center;
      box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
      animation: slideDown 0.3s ease-out;
    }

    .notification.success {
      background-color: #28a745;
    }

    .notification.error {
      background-color: #dc3545;
    }

    .notification i {
      margin-right: 10px;
    }

    /* Animations */
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    @keyframes slideDown {
      from { transform: translate(-50%, -30px); opacity: 0; }
      to { transform: translate(-50%, 0); opacity: 1; }
    }

    /* === RESPONSIVE === */
    @media (max-width: 768px) {
      .navbar-toggle {
        display: block;
      }

      .navbar-collapse {
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        background-color: var(--primary-color);
        flex-direction: column;
        align-items: stretch;
        max-height: 0;
        overflow: hidden;
        box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
      }

      .navbar-collapse.show {
        max-height: 300px;
        padding: 10px 0;
      }

      .navbar-nav {
        flex-direction: column;
      }

      .nav-item {
        margin: 0;
      }

      .nav-link {
        padding: 12px 20px;
      }

      .col-md-6 {
        flex: 0 0 100%;
        max-width: 100%;
      }

      .radio-group {
        flex-direction: column;
        gap: 10px;
      }
    }
  </style>
</head>

<body>
  <!-- Header -->
  <header>
    <nav class="navbar">
      <a class="navbar-brand" href="{{ url('/') }}">GESPRO-INFRAS <span>version.β</span></a>
      <button class="navbar-toggle" id="navbar-toggle">
        <i class="fas fa-bars"></i>
      </button>
      <div class="navbar-collapse" id="navbar-collapse">
        <ul class="navbar-nav">
          <li class="nav-item">
            <a href="#" class="nav-link" id="btnOuvrirModal">
              <i class="fas fa-user-plus"></i> Demande d'adhésion
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ url('/login') }}" class="nav-link">
              <i class="fas fa-sign-in-alt"></i> Connexion
            </a>
          </li>
        </ul>
      </div>
    </nav>
  </header>

  <!-- Modal d'adhésion -->
  <div id="modalAjouter" class="modal">
    <center>
        <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Demande d'adhésion</h2>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <div class="form-group">
            <label><strong>Type d'adhésion :</strong></label>
            <div class="radio-group">
                <label>
                <input type="radio" name="typePersonne" value="morale" id="radioMorale"> 
                <i class="fas fa-building"></i> Entreprise (Personne morale)
                </label>
                <label>
                <input type="radio" name="typePersonne" value="physique" id="radioPhysique"> 
                <i class="fas fa-user"></i> Individu (Personne physique)
                </label>
            </div>
            </div>

            <!-- Formulaire Entreprise -->
            <div id="entrepriseFields" class="hidden">
            <div class="tabs">
                <div class="tab-link active" data-tab="entreprise-general">
                <i class="fas fa-info-circle"></i> Informations Générales
                </div>
                <div class="tab-link" data-tab="entreprise-legal">
                <i class="fas fa-gavel"></i> Informations Juridiques
                </div>
                <div class="tab-link" data-tab="entreprise-contact">
                <i class="fas fa-address-book"></i> Contact
                </div>
            </div>

            <div class="tab-content">
                <div id="entreprise-general" class="tab-pane active">
                <div class="row">
                    <div class="col-md-6 form-group">
                    <label>Nom complet (Raison sociale) *</label>
                    <input type="text" class="form-control" placeholder="Nom complet de l'entreprise">
                    </div>
                    <div class="col-md-6 form-group">
                    <label>Nom abrégé</label>
                    <input type="text" class="form-control" placeholder="Nom abrégé de l'entreprise">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group">
                    <label>Date de création *</label>
                    <input type="date" class="form-control">
                    </div>
                    <div class="col-md-6 form-group">
                    <label>Forme juridique</label>
                    <select class="form-control" name="formJuri" id="formJuri">
                        <option value=""></option>
                    </select>
                    </div>
                </div>
                </div>

                <div id="entreprise-legal" class="tab-pane">
                <div class="row">
                    <div class="col-md-4 form-group">
                    <label>Numéro d'immatriculation *</label>
                    <input type="text" class="form-control">
                    </div>
                    <div class="col-md-4 form-group">
                    <label>Numéro d'identification fiscale (NIF)</label>
                    <input type="text" class="form-control">
                    </div>
                    <div class="col-md-4 form-group">
                    <label>Registre du commerce (RCCM)</label>
                    <input type="text" class="form-control">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group">
                    <label>Capital social</label>
                    <input type="number" class="form-control">
                    </div>
                    <div class="col-md-6 form-group">
                    <label>Numéro d'agrément</label>
                    <input type="text" class="form-control">
                    </div>
                </div>
                </div>

                <div id="entreprise-contact" class="tab-pane">
                <div class="row">
                    <div class="col-md-4 form-group">
                    <label>Code postal</label>
                    <input type="text" class="form-control">
                    </div>
                    <div class="col-md-4 form-group">
                    <label>Adresse postale</label>
                    <input type="text" class="form-control">
                    </div>
                    <div class="col-md-4 form-group">
                    <label>Adresse Siège</label>
                    <input type="text" class="form-control">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 form-group">
                    <label>Représentant légal</label>
                    <lookup-select name="repLegal" id="repLegal">
                        <option value="">Sélectionnez...</option>
                    </lookup-select>
                    </div>
                    <div class="col-md-3 form-group">
                    <label>Email</label>
                    <input type="text" class="form-control">
                    </div>
                    <div class="col-md-3 form-group">
                    <label>Téléphone 1</label>
                    <input type="text" class="form-control">
                    </div>
                    <div class="col-md-3 form-group">
                    <label>Téléphone 2</label>
                    <input type="text" class="form-control">
                    </div>
                </div>
                <div class="row align-items-end">
                    <div class="col-md-3 form-group">
                    <label>Personne de contact</label>
                    <lookup-multiselect name="persContact" id="persContact">
                        <option value="">Sélectionnez...</option>
                    </lookup-multiselect>
                    </div>
                    <div class="col-md-9 d-flex flex-wrap" id="contactContainer"></div>
                </div>
                <div class="text-end" style="margin-top: 20px;">
                    <button id="btnEnregistrerMo" class="btn-primary">
                    <i class="fas fa-paper-plane"></i> Envoyer la demande
                    </button>
                </div>
                </div>
            </div>
            </div>

            <!-- Formulaire Individu -->
            <div id="individuFields" class="hidden">
            <div class="tabs">
                <div class="tab-link active" data-tab="individu-general">
                <i class="fas fa-user-circle"></i> Informations Personnelles
                </div>
                <div class="tab-link" data-tab="individu-contact">
                <i class="fas fa-address-card"></i> Contact
                </div>
                <div class="tab-link" data-tab="individu-admin">
                <i class="fas fa-file-alt"></i> Documents
                </div>
            </div>

            <div class="tab-content">
                <div id="individu-general" class="tab-pane active">
                <div class="row">
                    <div class="col-md-4 form-group">
                    <label>Nom complet *</label>
                    <input type="text" class="form-control" placeholder="Votre nom complet">
                    </div>
                    <div class="col-md-4 form-group">
                    <label>Date de naissance *</label>
                    <input type="date" class="form-control">
                    </div>
                    <div class="col-md-4 form-group">
                    <label>Genre *</label>
                    <select class="form-control" name="genre" id="genre">
                        <option value=""></option>
                    </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 form-group">
                    <label>Situation matrimoniale</label>
                    <select class="form-control" name="sitMat" id="sitMat">
                        <option value=""></option>
                    </select>
                    </div>
                    <div class="col-md-4 form-group">
                    <label>Pays d'origine *</label>
                    <lookup-select name="nationnalite" id="nationnalite">
                        <option value="">Sélectionnez...</option>
                    </lookup-select>
                    </div>
                </div>
                </div>

                <div id="individu-contact" class="tab-pane">
                <div class="row">
                    <div class="col-md-4 form-group">
                    <label>Email *</label>
                    <input type="email" class="form-control" placeholder="Votre email">
                    </div>
                    <div class="col-md-4 form-group">
                    <label>Code postal</label>
                    <input type="text" class="form-control">
                    </div>
                    <div class="col-md-4 form-group">
                    <label>Adresse postale</label>
                    <input type="text" class="form-control">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 form-group">
                    <label>Adresse *</label>
                    <input type="text" class="form-control" placeholder="Votre adresse">
                    </div>
                    <div class="col-md-4 form-group">
                    <label>Téléphone bureau</label>
                    <input type="text" class="form-control">
                    </div>
                    <div class="col-md-4 form-group">
                    <label>Téléphone mobile *</label>
                    <input type="text" class="form-control">
                    </div>
                </div>
                </div>

                <div id="individu-admin" class="tab-pane">
                <div class="row">
                    <div class="col-md-4 form-group">
                    <label>Type de pièce d'identité</label>
                    <select class="form-control">
                        <option value="">Sélectionnez...</option>
                        <option value="cni">Carte Nationale d'Identité</option>
                        <option value="passeport">Passeport</option>
                        <option value="permis">Permis de conduire</option>
                    </select>
                    </div>
                    <div class="col-md-4 form-group">
                    <label>Numéro pièce</label>
                    <input type="text" class="form-control">
                    </div>
                    <div class="col-md-4 form-group">
                    <label>Date d'établissement</label>
                    <input type="date" class="form-control">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group">
                    <label>Date d'expiration</label>
                    <input type="date" class="form-control">
                    </div>
                    <div class="col-md-6 form-group">
                    <label>Numéro fiscal</label>
                    <input type="text" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label>Télécharger une copie de votre pièce d'identité</label>
                    <input type="file" class="form-control">
                </div>
                <div class="text-end" style="margin-top: 20px;">
                    <button id="btnEnregistrerPhy" class="btn-primary">
                    <i class="fas fa-paper-plane"></i> Envoyer la demande
                    </button>
                </div>
                </div>
            </div>
            </div>
        </div>
        </div>
    </center>
  </div>

  <!-- Notification -->
  <div id="notification" class="notification hidden"></div>

  <!-- Scripts -->
  <script src="{{ asset('betsa/assets/js/jquery.js')}}"></script>
  <script src="{{ asset('assets/compiled/js/lookup-multiselect.js')}}"></script>
  <script src="{{ asset('assets/compiled/js/lookup-select.js')}}"></script>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
      // Gestion du menu mobile
      const navbarToggle = document.getElementById('navbar-toggle');
      const navbarCollapse = document.getElementById('navbar-collapse');
      
      navbarToggle.addEventListener('click', function() {
        navbarCollapse.classList.toggle('show');
      });

      // Fermer le menu quand on clique ailleurs
      document.addEventListener('click', function(e) {
        if (!navbarToggle.contains(e.target) && !navbarCollapse.contains(e.target)) {
          navbarCollapse.classList.remove('show');
        }
      });

      // Gestion du modal
      const modal = document.getElementById('modalAjouter');
      const btnOpen = document.getElementById('btnOuvrirModal');
      const btnClose = document.querySelector('.close');
      
      function openModal() {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
      }
      
      function closeModal() {
        modal.classList.remove('show');
        document.body.style.overflow = '';
      }
      
      btnOpen.addEventListener('click', openModal);
      btnClose.addEventListener('click', closeModal);
      
      // Fermer le modal quand on clique en dehors
      window.addEventListener('click', function(e) {
        if (e.target === modal) {
          closeModal();
        }
      });

      // Gestion des onglets
      function setupTabs(container) {
        const tabs = container.querySelectorAll('.tab-link');
        const panes = container.querySelectorAll('.tab-pane');
        
        tabs.forEach(tab => {
          tab.addEventListener('click', function() {
            // Désactiver tous les onglets
            tabs.forEach(t => t.classList.remove('active'));
            panes.forEach(p => p.classList.remove('active'));
            
            // Activer l'onglet sélectionné
            this.classList.add('active');
            const paneId = this.getAttribute('data-tab');
            document.getElementById(paneId).classList.add('active');
          });
        });
      }

      // Gestion des formulaires (entreprise/individu)
      const radioMorale = document.getElementById('radioMorale');
      const radioPhysique = document.getElementById('radioPhysique');
      const formMorale = document.getElementById('entrepriseFields');
      const formPhysique = document.getElementById('individuFields');
      
      function toggleForms() {
        if (radioMorale.checked) {
          formMorale.classList.remove('hidden');
          formPhysique.classList.add('hidden');
          setupTabs(formMorale);
        } else if (radioPhysique.checked) {
          formPhysique.classList.remove('hidden');
          formMorale.classList.add('hidden');
          setupTabs(formPhysique);
        }
      }
      
      radioMorale.addEventListener('change', toggleForms);
      radioPhysique.addEventListener('change', toggleForms);
      
      // Initialiser avec un formulaire par défaut
      radioMorale.checked = true;
      toggleForms();

      // Gestion des notifications
      function showNotification(message, type = 'success') {
        const notification = document.getElementById('notification');
        notification.textContent = message;
        notification.className = `notification ${type}`;
        
        setTimeout(() => {
          notification.classList.add('hidden');
        }, 3000);
      }

      // Simulation d'envoi de formulaire
      document.getElementById('btnEnregistrerMo').addEventListener('click', function() {
        showNotification('Votre demande d\'adhésion a été envoyée avec succès !', 'success');
        closeModal();
      });
      
      document.getElementById('btnEnregistrerPhy').addEventListener('click', function() {
        showNotification('Votre demande d\'adhésion a été envoyée avec succès !', 'success');
        closeModal();
      });

      // Gestion des contacts (exemple)
      const lookup = document.getElementById('persContact');
      const contactContainer = document.getElementById('contactContainer');
      
      if (lookup) {
        lookup.addEventListener('change', function() {
          const selectedValues = this.value;
          contactContainer.innerHTML = '';
          
          selectedValues.forEach(value => {
            // Simuler des données de contact
            const contactDiv = document.createElement('div');
            contactDiv.className = 'col-md-12 form-group';
            contactDiv.innerHTML = `
              <div class="row">
                <div class="col-md-3">
                  <label>Nom</label>
                  <input type="text" class="form-control" value="Contact ${value}" readonly>
                </div>
                <div class="col-md-3">
                  <label>Email</label>
                  <input type="email" class="form-control" name="emailPC">
                </div>
                <div class="col-md-3">
                  <label>Téléphone 1</label>
                  <input type="text" class="form-control" name="Tel1Pc">
                </div>
                <div class="col-md-3">
                  <label>Téléphone 2</label>
                  <input type="text" class="form-control" name="Tel2PC">
                </div>
              </div>
            `;
            contactContainer.appendChild(contactDiv);
          });
        });
      }
    });
  </script>
</body>
</html>