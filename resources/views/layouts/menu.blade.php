<script src="{{ asset('assets/compiled/js/lookup-multiselect.js')}}"></script>
<script src="{{ asset('assets/compiled/js/lookup-select.js')}}"></script>
<style>
    /* ======== Style général ======== */
    body {
        margin: 0;
        font-family: Arial, sans-serif;
    }

    /* ======== Barre de navigation ======== */
    .navbar {
        background-color: #435EBE;
        padding: 18px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
    }

    /* Logo */
    .navbar-brand {
        color: #fff;
        text-decoration: none;
        font-size: 24px;
    }

    /* Bouton hamburger */
    .navbar-toggle {
        display: none;
        background-color: transparent;
        border: none;
        color: white;
        font-size: 28px;
        cursor: pointer;
    }

    /* Menu normal */
    .navbar-collapse {
        display: flex;
        justify-content: flex-end;
    }

    /* Liste des liens */
    .navbar-collapse ul {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
    }

    .navbar-collapse ul li {
        margin-left: 20px;
    }

    .navbar-collapse ul li a {
        color: #fff;
        text-decoration: none;
        padding: 10px 15px;
        display: block;
        transition: background 0.3s;
    }

    .navbar-collapse ul li a:hover {
        background-color: #575757;
        border-radius: 4px;
    }

    /* ======== Responsive (mobile) ======== */
    @media (max-width: 768px) {
        .navbar-toggle {
            display: block;
        }

        .navbar-collapse {
            display: none;
            flex-direction: column;
            align-items: center;
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            background-color: #333;
            padding: 10px 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .navbar-collapse.active {
            display: flex;
            animation: slideDown 0.3s ease-in-out;
        }

        .navbar-collapse ul {
            flex-direction: column;
            width: 100%;
            text-align: center;
        }

        .navbar-collapse ul li {
            width: 100%;
            margin: 0;
        }

        .navbar-collapse ul li a {
            padding: 15px;
            display: block;
            width: 100%;
            color: white;
            background: #444;
            border-bottom: 1px solid #555;
        }

        .navbar-collapse ul li a:hover {
            background: #575757;
        }
    }

    /* Animation du menu */
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Styles du Modal */
    .modal {
        display: none ;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);

        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background: #fff;
        padding: 20px;
        width: 80%;
        max-width: 1000px;
        border-radius: 8px;
        box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.3);
    }

    .close {
        float: right;
        font-size: 24px;
        cursor: pointer;
    }

    /* Styles des champs */


    .btn-primary {
        background-color: #007bff;
        color: white;
        padding: 10px 15px;
        border: none;
        cursor: pointer;
        border-radius: 5px;
    }

    .btn-primary:hover {
        background: #0056b3;
    }

    /* Styles des onglets */
    .tabs {
        display: flex;
        border-bottom: 2px solid #ccc;
    }

    .tab-link {
        flex: 1;
        padding: 10px;
        border: 1px solid #ddd;
        cursor: pointer;
        background: #f8f9fa;
        text-align: center;
    }

    .tab-link.active {
        background: #007bff;
        color: white;
    }

    .tab-pane {
        display: none;
    }

    .tab-pane.active {
        display: block;
    }
    /* Cacher les formulaires au début */
    .hidden {
        display: none;
    }

    /* Activer l'affichage */
    .formulaire {
        display: block;
    }
    /* Assure que le bouton est bien aligné à droite */
    .text-end {
        display: flex;
        justify-content: flex-end; /* Aligner à droite */
        width: 100%;
    }


</style>

<!-- ======= Barre de navigation ======= -->
<nav class="navbar">
    <a class="navbar-brand" href="{{ url('/')}}">BTP-PROJECT</a>
    <button type="button" class="navbar-toggle" id="navbar-toggle">
        ☰ <!-- Icône hamburger -->
    </button>
    <div class="navbar-collapse" id="navbar-menu">
        <ul>
            <li><a href="#" id="btnOuvrirModal" data-toggle="modale" data-target="#modaleAjouter"><i class="fas fa-user-plus"></i> Demande d'adhésion</a></li>
            <li><a href="{{ url('/login') }}"><i class="fas fa-sign-in-alt"></i> Connexion</a></li>
        </ul>
    </div>
</nav>


<!-- ======= Modal pour l'enregistrement ======= -->
<div id="modalAjouter" class="modal" >
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2 class="modal-title">Renseignez vos informations</h2>

        <!-- Sélection du type d'adhésion -->
        <div class="form-group">
            <label><strong>Type d'adhésion :</strong></label>
            <div class="radio-group">
                <label><input type="radio" name="typePersonne" value="morale" id="radioMorale"> Entreprise (Personne morale)</label><br>
                <label><input type="radio" name="typePersonne" value="physique" id="radioPhysique"> Individu (Personne physique)</label>
            </div>
        </div>

        <!-- Formulaire pour Entreprise -->
        <div id="entrepriseFields" class="formulaire hidden">
            <h3>Détails pour l’Entreprise</h3>
            <div class="tabs">
                <button class="tab-link active" data-tab="entreprise-general">Informations Générales</button>
                <button class="tab-link" data-tab="entreprise-legal">Informations Juridiques</button>
                <button class="tab-link" data-tab="entreprise-contact">Informations de Contact</button>
            </div>
            <div class="tab-content">
                <div class="tab-pane active" id="entreprise-general">
                    <div class="row">
                        <div class="col-md-6">
                            <label>Nom complet (Raison sociale) *</label>
                            <input type="text" class="form-control" placeholder="Nom complet de l'entreprise">
                        </div>
                        <div class="col-md-6">
                            <label>Nom abrégé </label>
                            <input type="text" class="form-control" placeholder="Nom abrégé de l'entreprise">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label>Date de création *</label>
                            <input type="date" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label>Forme juridique </label>
                            <select class="form-control" name="formJuri" id="formJuri">
                                <option value=""></option>
                            </select>
                        </div>
                    </div>

                </div>

                <div class="tab-pane" id="entreprise-legal">
                    <div class="row">
                        <div class="col-md-4">
                            <label>Numéro d'immatriculation *</label>
                            <input type="text" class="form-control" >
                        </div>
                        <div class="col-md-4">
                            <label>Numéro d'identification fiscale (NIF) </label>
                            <input type="text" class="form-control" >
                        </div>
                        <div class="col-md-4">
                            <label>Registre du commerce (RCCM) </label>
                            <input type="text" class="form-control" >
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="">Capital social</label>
                            <input type="number" class="form-control" >
                        </div>
                        <div class="col-md-6">
                            <label for="">Numéro d'agrément</label>
                            <input type="text" class="form-control" >
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="entreprise-contact">
                    <div class="row">
                        <div class="col-md-4">
                            <label>Code postale</label>
                            <input type="text" class="form-control" >
                        </div>
                        <div class="col-md-4">
                            <label>Adresse postale </label>
                            <input type="text" class="form-control" >
                        </div>
                        <div class="col-md-4">
                            <label>Adresse Siège </label>
                            <input type="text" class="form-control" >
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <label>Représentant légal</label>
                            <lookup-select name="repLegal" id="repLegal">
                                <option value="">Sélectionnez...</option>
                            </lookup-select>
                        </div>
                        <div class="col-md-3">
                            <label>Email </label>
                            <input type="text" class="form-control" >
                        </div>
                        <div class="col-md-3">
                            <label>Téléphone 1 </label>
                            <input type="text" class="form-control" >
                        </div>
                        <div class="col-md-3">
                            <label>Téléphone 2 </label>
                            <input type="text" class="form-control" >
                        </div>
                    </div>
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label>Personne de contact</label>
                            <lookup-multiselect name="persContact" id="persContact">
                                <option value="">Sélectionnez...</option>
                            </lookup-multiselect>
                        </div>
                        <!-- Conteneur pour afficher dynamiquement les champs -->
                        <div class="col-md-9 d-flex flex-wrap" id="contactContainer"></div>
                    </div>
                        <br> <br>
                    <div class="row">
                        <div class="col-md-12 text-end"><button id="btnEnregistrerMo" class="btn-primary">Enregistrer</button></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulaire pour Individu -->
        <div id="individuFields" class="formulaire hidden">
            <h3>Détails pour l’Individu</h3>
            <div class="tabs">
                <button class="tab-link active" data-tab="individu-general">Informations Personnelles</button>
                <button class="tab-link" data-tab="individu-contact">Informations de Contact</button>
                <button class="tab-link" data-tab="individu-admin">Informations Administratives</button>
            </div>

            <div class="tab-content">
                <div class="tab-pane active" id="individu-general">
                    <div class="row">
                        <div class="col-md-4">
                            <label>Nom complet *</label>
                            <input type="text" class="form-control" placeholder="Nom complet de l'entreprise">
                        </div>
                        <div class="col-md-4">
                            <label>Nom abrégé *</label>
                            <input type="text" class="form-control" placeholder="Nom abrégé de l'entreprise">
                        </div>
                        <div class="col-md-4">
                            <label>Date de création *</label>
                            <input type="date" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <label>Genre *</label>
                            <select class="form-control" name="genre" id="genre">
                                <option value=""></option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Situation matrimoniale </label>
                            <select class="form-control" name="sitMat" id="sitMat">
                                <option value=""></option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Pays d'origine *</label>
                            <lookup-select name="nationnalite" id="nationnalite">
                                <option value="">Sélectionnez...</option>

                            </lookup-select>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="individu-contact">
                    <div class="col">
                        <div class="row">
                            <div class="col-md-4">
                                <label>Email *</label>
                                <input type="email" class="form-control" placeholder="Email de l'entreprise">
                            </div>
                            <div class="col-md-4">
                                <label>Code postal </label>
                                <input type="text" class="form-control" >
                            </div>
                            <div class="col-md-4">
                                <label>Adresse postal </label>
                                <input type="text" class="form-control" >
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <label>Adresse *</label>
                                <input type="email" class="form-control" placeholder="Email de l'entreprise">
                            </div>
                            <div class="col-md-4">
                                <label>Téléphone bureau</label>
                                <input type="text" class="form-control" >
                            </div>
                            <div class="col-md-4">
                                <label>Téléphone mobile * </label>
                                <input type="text" class="form-control" >
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="individu-admin">

                        <div class="row">
                            <div class="col-md-4">
                                <label>Pièce d'identité </label>
                                <input type="email" class="form-control" placeholder="Email de l'entreprise">
                            </div>
                            <div class="col-md-4">
                                <label>Numéro pièce </label>
                                <input type="text" class="form-control" >
                            </div>
                            <div class="col-md-4">
                                <label>Date d'établissement</label>
                                <input type="date" class="form-control" >
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label>Date d'expiration</label>
                                <input type="email" class="form-control" placeholder="Email de l'entreprise">
                            </div>
                            <div class="col-md-6">
                                <label>Numéro fiscal</label>
                                <input type="text" class="form-control" >
                            </div>
                        </div> <br> <br>
                        <div class="row">
                            <div class="col-md-12 text-end"><button id="btnEnregistrerPhy" class="btn-primary">Enregistrer</button></div>
                        </div>
                </div>
            </div>
        </div>


    </div>
</div>





<script>
document.addEventListener("DOMContentLoaded", function () {
    const navbarToggle = document.getElementById('navbar-toggle');
    const navbarMenu = document.getElementById('navbar-menu');

    // Ouvrir et fermer le menu hamburger
    navbarToggle.addEventListener('click', function (event) {
        event.stopPropagation(); // Empêche le clic de se propager et de fermer immédiatement le menu
        navbarMenu.classList.toggle('active');

        // Fix: Gérer l'affichage du menu
        if (navbarMenu.classList.contains('active')) {
            navbarMenu.style.display = "flex";
        } else {
            navbarMenu.style.display = "";
        }
    });

    // Fermer le menu lorsqu'on clique en dehors
    document.addEventListener("click", function (event) {
        if (!navbarToggle.contains(event.target) && !navbarMenu.contains(event.target)) {
            navbarMenu.classList.remove('active');
            navbarMenu.style.display = "";
        }
    });
});
</script>
<script>
document.getElementById("btnOuvrirModal").addEventListener("click", function () {
    document.getElementById("modalAjouter").style.display = "flex";
});

document.querySelector(".close").addEventListener("click", function () {
    document.getElementById("modalAjouter").style.display = "none";
});

document.querySelectorAll(".tab-link").forEach(tab => {
    tab.addEventListener("click", function () {
        document.querySelectorAll(".tab-pane").forEach(pane => pane.classList.remove("active"));
        document.getElementById(this.dataset.tab).classList.add("active");
        document.querySelectorAll(".tab-link").forEach(btn => btn.classList.remove("active"));
        this.classList.add("active");
    });
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // Références des éléments
    const btnOuvrirModal = document.getElementById("btnOuvrirModal");
    const modalAjouter = document.getElementById("modalAjouter");
    const btnFermerModal = document.querySelector(".close");
    const radioPhysique = document.getElementById("radioPhysique");
    const radioMorale = document.getElementById("radioMorale");
    const formPhysique = document.getElementById("individuFields");
    const formMorale = document.getElementById("entrepriseFields");

    // Assurer que le modal est bien caché au départ
    modalAjouter.style.display = "none";

    // Ouvrir le modal
    btnOuvrirModal.addEventListener("click", function () {
        modalAjouter.style.display = "flex";
    });

    // Fermer le modal sur clic de la croix
    btnFermerModal.addEventListener("click", function () {
        modalAjouter.style.display = "none";
    });

    // Fermer le modal
    btnFermerModal.addEventListener("click", function () {
        modalAjouter.style.display = "none";
    });

    // Fermer le modal si on clique en dehors
    window.addEventListener("click", function (event) {
        if (event.target === modalAjouter) {
            modalAjouter.style.display = "none";
        }
    });

    // Fonction pour afficher le bon formulaire
    function togglePersonneFields() {
        if (radioPhysique.checked) {
            formPhysique.classList.remove("hidden");
            formMorale.classList.add("hidden");
        } else if (radioMorale.checked) {
            formMorale.classList.remove("hidden");
            formPhysique.classList.add("hidden");
        }
    }

    // Écouteurs pour les boutons radio
    radioPhysique.addEventListener("change", togglePersonneFields);
    radioMorale.addEventListener("change", togglePersonneFields);

    // Gestion des onglets
    document.querySelectorAll(".tab-link").forEach(tab => {
        tab.addEventListener("click", function () {
            const parentContainer = this.closest(".formulaire");

            // Désactiver tous les onglets et panes du même formulaire
            parentContainer.querySelectorAll(".tab-link").forEach(link => link.classList.remove("active"));
            parentContainer.querySelectorAll(".tab-pane").forEach(pane => pane.classList.remove("active"));

            // Activer l'onglet et le contenu associé
            this.classList.add("active");
            document.getElementById(this.dataset.tab).classList.add("active");
        });
    });
});
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const lookup = document.getElementById("nomPC"); // Sélection du lookup-multiselect
        const contactContainer = document.getElementById("contactContainer");
        const acteurs = '';{{--//@json($acteurRepres);--}} // Récupération des contacts depuis Laravel

        function updateContacts() {
            contactContainer.innerHTML = ""; // Vider le contenu

            let selectedValues = lookup.value; // Récupère les valeurs sélectionnées

            if (selectedValues.length === 0) {
                return; // Si aucune sélection, ne rien afficher
            }

            selectedValues.forEach(code => {
                let acteur = acteurs.find(a => a.code_acteur == code);
            // console.log('acteur :',acteur);
                if (acteur) {
                    let row = document.createElement("div");
                    row.classList.add("d-flex", "align-items-center", "me-3");

                    row.innerHTML = `
                        <div class="me-3">
                            <label>Nom</label>
                            <input type="text" class="form-control" value="${acteur.libelle_court} ${acteur.libelle_long}" readonly>
                        </div>
                        <div class="me-3">
                            <label>Email</label>
                            <input type="email" class="form-control" name="emailPC" value="${acteur.email || ''}">
                        </div>
                        <div class="me-3">
                            <label>Téléphone 1</label>
                            <input type="text" class="form-control" name="Tel1Pc" value="${acteur.telephone_mobile || ''}">
                        </div>
                        <div class="me-3">
                            <label>Téléphone 2</label>
                            <input type="text" class="form-control" name="Tel2PC" value="${acteur.telephone_bureau || ''}">
                        </div>
                    `;

                    contactContainer.appendChild(row);
                }
            });
        }

        // Écouter le changement de sélection sur `lookup-multiselect`
        lookup.addEventListener("change", updateContacts);

        // Optionnel : Afficher les données au chargement si des valeurs sont déjà sélectionnées
        setTimeout(updateContacts, 500);
    });

    document.addEventListener("DOMContentLoaded", function () {
        const lookupRL = document.getElementById("nomRL"); // Sélecteur du lookup-select
        const emailRL = document.querySelector("input[name='emailRL']");
        const telephone1RL = document.querySelector("input[name='telephone1RL']");
        const telephone2RL = document.querySelector("input[name='telephone2RL']");

        const acteurs =''; {{--//@json($acteurRepres);--}} // Récupération des acteurs depuis Laravel Blade

        function updateRepresentantLegal() {
            let selectedValue = lookupRL.value; // Récupérer l'ID sélectionné

            // Trouver les données du représentant légal
            let acteur = acteurs.find(a => a.code_acteur == selectedValue);

            if (acteur) {
                emailRL.value = acteur.email || ""; // Mettre à jour l'email
                telephone1RL.value = acteur.telephone_mobile || ""; // Mettre à jour Téléphone 1
                telephone2RL.value = acteur.telephone_bureau || ""; // Mettre à jour Téléphone 2
            } else {
                emailRL.value = ""; // Vider si aucun représentant légal trouvé
                telephone1RL.value = "";
                telephone2RL.value = "";
            }
        }

        // Écouter les changements sur le `lookup-select`
        lookupRL.addEventListener("change", updateRepresentantLegal);

        // Optionnel : Remplir les champs au chargement si une valeur est déjà sélectionnée
        setTimeout(updateRepresentantLegal, 500);

        // Ajouter les champs cachés dynamiques pour conserver les modifications lors du submit
        const form = document.querySelector("form");
        form.addEventListener("submit", function () {
            // Ajouter des champs cachés pour les valeurs modifiées
            let hiddenEmail = document.createElement("input");
            hiddenEmail.type = "hidden";
            hiddenEmail.name = "emailRL_modified";
            hiddenEmail.value = emailRL.value;
            form.appendChild(hiddenEmail);

            let hiddenTel1 = document.createElement("input");
            hiddenTel1.type = "hidden";
            hiddenTel1.name = "telephone1RL_modified";
            hiddenTel1.value = telephone1RL.value;
            form.appendChild(hiddenTel1);

            let hiddenTel2 = document.createElement("input");
            hiddenTel2.type = "hidden";
            hiddenTel2.name = "telephone2RL_modified";
            hiddenTel2.value = telephone2RL.value;
            form.appendChild(hiddenTel2);
        });
    });
</script>
