
<div class="top-area">
				<div class="header-area">
					<!-- Start Navigation -->
				    <nav class="navbar navbar-default bootsnav  navbar-sticky navbar-scrollspy"  data-minus-value-desktop="70" data-minus-value-mobile="55" data-speed="1000">

				        <div class="container">

				            <!-- Start Header Navigation -->
				            <div class="navbar-header">
				                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-menu">
				                    <i class="fa fa-bars"></i>
				                </button>
								<a class="navbar-brand" href="/">
									BTP-PROJECT<span></span>
								  </a>

				            </div><!--/.navbar-header-->
				            <!-- End Header Navigation -->

				            <!-- Collect the nav links, forms, and other content for toggling -->
				            <div class="collapse navbar-collapse menu-ui-design" id="navbar-menu">
				                <ul class="nav navbar-nav navbar-right" data-in="fadeInDown" data-out="fadeOutUp">

                                <li ><a href="#" data-toggle="modal" data-target="#modalAjouter" class="col-3 text-center"> <i class="fas fa-sign-in-alt"></i>  Demande d'adhésion</a></li>
				                    <li ><a href="{{ url('/login') }}"> <i class="fas fa-sign-in-alt"></i>  Connexion</a></li>


				                </ul><!--/.nav -->
				            </div><!-- /.navbar-collapse -->
				        </div><!--/.container-->
				    </nav><!--/nav-->
				    <!-- End Navigation -->
				</div><!--/.header-area-->
                <div class="modal fade" id="modalAjouter" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Renseigner vos informations</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                            </div>
                            <div class="modal-body">
                                    <div class="col mt-3 d-none" id="optionsMoePrive">
                                        <label>Type de Privé *</label>
                                        <div class="col">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="priveMoeType" id="moeEntreprise" value="Entreprise" onchange="toggleMoeFields()">
                                                <label class="form-check-label" for="moeEntreprise">Entreprise</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="priveMoeType" id="moeIndividu" value="Individu" onchange="toggleMoeFields()">
                                                <label class="form-check-label" for="moeIndividu">Individu</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Sélection du type de bailleur -->
                                    <div class="row mt-3 d-none" id="moeEntrepriseFields">
                                        <hr>
                                        <h6>Détails pour l’Entreprise</h6>
                                        <div class="col-12">
                                            <ul class="nav nav-tabs" id="moeentrepriseTabs" role="tablist">
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link active" id="moeentreprise-general-tab" data-bs-toggle="tab" data-bs-target="#moeentreprise-general" type="button" role="tab" aria-controls="moeentreprise-general" aria-selected="true">Informations Générales</button>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link" id="moeentreprise-legal-tab" data-bs-toggle="tab" data-bs-target="#moeentreprise-legal" type="button" role="tab" aria-controls="moeentreprise-legal" aria-selected="false">Informations Juridiques</button>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link" id="moeentreprise-contact-tab" data-bs-toggle="tab" data-bs-target="#moeentreprise-contact" type="button" role="tab" aria-controls="moeentreprise-contact" aria-selected="false">Informations de Contact</button>
                                                </li>
                                            </ul>
                                            <div class="tab-content mt-3" id="moeentrepriseTabsContent">
                                                <!-- Tab 1: Informations Générales -->
                                                <div class="tab-pane fade show active" id="moeentreprise-general" role="tabpanel" aria-labelledby="moeentreprise-general-tab">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <label>Code de l'Entreprise :</label>
                                                            <input type="text" name="codeEntMoe" class="form-control" placeholder="Nom de l'entreprise">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label>Nom de l'Entreprise :</label>
                                                            <input type="text" name="nomEntMoe" class="form-control" placeholder="Nom de l'entreprise">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label>Date de création :</label>
                                                            <input type="text" name="dateCreationEntMoe" class="form-control" placeholder="Adresse complète">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label>Secteur d'activité :</label>
                                                            <select name="sectActivEntMoe" id="SecteurActiviteEntreprise" class="form-control">
                                                                <option value="">Sélectionnez...</option>

                                                            </select>
                                                        </div>
                                                        <div class="col-md-4 ">
                                                            <label>Forme Juridique :</label>
                                                            <select name="FormeJuriEntMoe" id="FormeJuridique" class="form-control">
                                                                <option value="">Sélectionnez...</option>

                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Tab 2: Informations Juridiques -->
                                                <div class="tab-pane fade" id="moeentreprise-legal" role="tabpanel" aria-labelledby="moeentreprise-legal-tab">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <label>Numéro d’Immatriculation :</label>
                                                            <input type="text" name="NumImmEntMoe" class="form-control" placeholder="Numéro RCCM">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label>Numéro d’Identification Fiscale (NIF) :</label>
                                                            <input type="text" name="NIFEntMoe" class="form-control" placeholder="Numéro fiscal">
                                                        </div>
                                                        <div class="col-md-6 mt-2">
                                                            <label>Capital Social :</label>
                                                            <input type="number" name="CapitalEntMoe" class="form-control" placeholder="Capital social de l’entreprise">
                                                        </div>
                                                        <div class="col-md-6 mt-2">
                                                            <label>Numéro d'agrément :</label>
                                                            <input type="text"  name="NumAgreEntMoe" id="Numéroagrement" class="form-control">
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Tab 3: Informations de Contact -->
                                                <div class="tab-pane fade" id="moeentreprise-contact" role="tabpanel" aria-labelledby="moeentreprise-contact-tab">
                                                    <div class="row">
                                                        <div class="col-4">
                                                            <label>Code postale</label>
                                                            <input type="text" name="CodePostEntMoe" class="form-control"  placeholder="Code postale">
                                                        </div>
                                                        <div class="col-4">
                                                            <label>Adresse postale</label>
                                                            <input type="text"  class="form-control" name="AddPostEntMoe" placeholder="Code postale">
                                                        </div>
                                                        <div class="col-4">
                                                            <label>Adresse Siège</label>
                                                            <input type="text" class="form-control" name="AddSieEntMoe" placeholder="Code postale">
                                                        </div>
                                                        <hr>
                                                        <div class="col-md-3">
                                                            <label>Représentant Légal :</label>
                                                            <input type="text" class="form-control"  name="RepLeEntMoe" placeholder="Nom du représentant légal">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label>Email:</label>
                                                            <input type="email" class="form-control" name="EmailRepLeEntMoe" placeholder="Email du représentant légal">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label>Téléphone 1:</label>
                                                            <input type="text" class="form-control" name="Tel1RepLeEntMoe" placeholder="Téléphone 1 du représentant légal">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label>Téléphone 2:</label>
                                                            <input type="text" class="form-control" name="Tel2RepLeEntMoe" placeholder="Téléphone 2 du représentant légal">
                                                        </div>
                                                        <hr>
                                                        <div class="col-md-3">
                                                            <label>Personne de Contact :</label>
                                                            <input type="text" class="form-control" name="NomPersContEntMoe" placeholder="Nom de la personne de contact">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label>Email:</label>
                                                            <input type="email" class="form-control" name="EmailPersContEntMoe" placeholder="Email du personne de Contact">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label>Téléphone 1:</label>
                                                            <input type="text" class="form-control" name="Tel1PersContEntMoe" placeholder="Téléphone 1 de la ersonne de Contact">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label>Téléphone 2:</label>
                                                            <input type="text" class="form-control" name="Tel2PersContEntMoe" placeholder="Téléphone 2 de la Personne de Contact">
                                                        </div>
                                                        <hr>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- MOE Individu Fields -->
                                    <div class="row mt-3 d-none" id="moeIndividuFields">
                                        <hr>
                                        <h6>Détails pour l’Individu</h6>
                                        <div class="col-12">
                                            <ul class="nav nav-tabs" id="moeindividuTabs" role="tablist">
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link active" id="moeindividu-general-tab" data-bs-toggle="tab" data-bs-target="#moeindividu-general" type="button" role="tab" aria-controls="moeindividu-general" aria-selected="true">Informations Personnelles</button>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link" id="moeindividu-contact-tab" data-bs-toggle="tab" data-bs-target="#moeindividu-contact" type="button" role="tab" aria-controls="moeindividu-contact" aria-selected="false">Informations de Contact</button>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link" id="moeindividu-admin-tab" data-bs-toggle="tab" data-bs-target="#moeindividu-admin" type="button" role="tab" aria-controls="moeindividu-admin" aria-selected="false">Informations Administratives</button>
                                                </li>
                                            </ul>
                                            <div class="tab-content mt-3" id="moeindividuTabsContent">
                                                <!-- Tab 1: Informations Personnelles -->
                                                <div class="tab-pane fade show active" id="moeindividu-general" role="tabpanel" aria-labelledby="moeindividu-general-tab">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <label>Nom *:</label>
                                                            <input type="text" name="NomIndMoe" class="form-control" placeholder="Nom">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label>Prénom *:</label>
                                                            <input type="text" name="PrenomIndMoe" class="form-control" placeholder="Prénom">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label>Date de Naissance :</label>
                                                            <input type="date" name="DateNaissanceIndMoe" class="form-control">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label>Genre</label>
                                                            <select name="genreIndMoe" id="genre" class="form-control">
                                                                <option value="">Sélectionnez...</option>

                                                            </select>
                                                        </div>
                                                        <div class="col-md-4 ">
                                                            <label>Situation Matrimoniale :</label>
                                                            <select class="form-control" name="SitMatIndMoe">
                                                                <option value="">Sélectionnez...</option>

                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label>Pays d'origine :</label>
                                                            <select name="nationnaliteIndMoe" id="nationnalite" class="form-control">
                                                                <option value="">Sélectionner le pays </option>

                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Tab 2: Informations de Contact -->
                                                <div class="tab-pane fade" id="moeindividu-contact" role="tabpanel" aria-labelledby="moeindividu-contact-tab">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <label>Email *:</label>
                                                            <input type="email" name="emailIndMoe" class="form-control" placeholder="Email">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label for="codePostal">Code postal</label>
                                                            <input type="text" name="CodePostalIndMoe" id="CodePostal" class="form-control">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label>Adresse postale:</label>
                                                            <input type="text" name="AddPostIndMoe" class="form-control" placeholder="Adresse">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label>Adresse *</label>
                                                            <input type="text" name="AddSiegeIndMoe" class="form-control" placeholder="Adresse">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label>Téléphone Bureau *</label>
                                                            <input type="text" name="TelBureauIndMoe" class="form-control" placeholder="Téléphone">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label>Téléphone mobile *</label>
                                                            <input type="text" name="TelMobileIndMoe" class="form-control" placeholder="Téléphone">
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Tab 3: Informations Administratives -->
                                                <div class="tab-pane fade" id="moeindividu-admin" role="tabpanel" aria-labelledby="moeindividu-admin-tab">
                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <label>Pièce d’Identité :</label>
                                                            <select class="form-control" name="PieceIdIndMoe">


                                                            </select>

                                                        </div>
                                                        <div class="col-md-3">
                                                            <label>Numéro Pièce:</label>
                                                            <input type="text" name="NumPeceIndMoe" class="form-control" placeholder="Numéro de CNI">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label>Date de etablissement:</label>
                                                            <input type="date" name="DateEtablissementIndMoe" class="form-control" placeholder="Numéro de CNI">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label>Date de expiration:</label>
                                                            <input type="date" name="DateExpIndMoe" class="form-control" placeholder="Numéro de CNI">
                                                        </div>

                                                        <div class="col-md-6">
                                                            <label>Numéro Fiscal :</label>
                                                            <input type="text" name="NumFiscIndMoe" class="form-control" placeholder="Numéro fiscal">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label>Secteur d'activité :</label>
                                                            <select name="sectActIndMoe" id="SecteurActiviteEntreprise" class="form-control">
                                                                <option value="">Sélectionnez...</option>

                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                <button type="button" class="btn btn-primary" id="btnEnregistrer">Enregistrer</button>
                            </div>
                        </div>
                    </div>
                </div>
			    <div class="clearfix"></div>

			</div><br><br><br><br>
            <script src="{{ asset('betsa/assets/js/jquery.js')}}"></script>

        <!--modernizr.min.js-->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js"></script>

		<!--bootstrap.min.js-->
        <script src="{{ asset('betsa/assets/js/bootstrap.min.js')}}"></script>

		<!-- bootsnav js -->
		<script src="{{ asset('betsa/assets/js/bootsnav.js')}}"></script>

		<!--owl.carousel.js-->
        <script src="{{ asset('betsa/assets/js/owl.carousel.min.js')}}"></script>

		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>

        <!--Custom JS-->
        <script src="{{ asset('betsa/assets/js/custom.js')}}"></script>

<script>
     document.addEventListener("DOMContentLoaded", function () {
        // Empêcher la sélection de plusieurs options pour type_ouvrage
        const type_ouvrages = document.querySelectorAll('input[name="type_ouvrage"]');
        type_ouvrages.forEach((checkbox) => {
            checkbox.addEventListener("change", function () {
                if (this.checked) {
                    type_ouvrages.forEach((cb) => {
                        if (cb !== this) cb.checked = false;
                    });
                }
            });
        });
        // Gestion du Maître d’Ouvrage
        function toggleTypeMoe() {
            const publicRadio = document.getElementById('moePublic');
            const priveRadio = document.getElementById('moePrive');
            const optionsMoePrive = document.getElementById('optionsMoePrive');
            const moeEntrepriseFields = document.getElementById('moeEntrepriseFields');
            const individuFields = document.getElementById('moeIndividuFields');
            const acteurMoeSelect = document.getElementById('acteurMoeSelect');

            if (publicRadio.checked) {
                optionsMoePrive.classList.add('d-none');
                moeEntrepriseFields.classList.add('d-none');
                individuFields.classList.add('d-none');
                fetchMoeActeurs('Public');
            } else if (priveRadio.checked) {
                optionsMoePrive.classList.remove('d-none');
                acteurMoeSelect.innerHTML = '<option value="">Sélectionnez un acteur</option>';

                const entrepriseRadio = document.getElementById('moeEntreprise');
                const individuRadio = document.getElementById('moeIndividu');

                if (entrepriseRadio.checked) {
                    moeEntrepriseFields.classList.remove('d-none');
                    individuFields.classList.add('d-none');
                } else if (individuRadio.checked) {
                    individuFields.classList.remove('d-none');
                    moeEntrepriseFields.classList.add('d-none');
                } else {
                    moeEntrepriseFields.classList.add('d-none');
                    individuFields.classList.add('d-none');
                }
            } else {
                optionsMoePrive.classList.add('d-none');
                acteurMoeSelect.innerHTML = '<option value="">Sélectionnez un acteur</option>';
            }
        }

        function toggleMoeFields() {
            const entrepriseRadio = document.getElementById('moeEntreprise');
            const individuRadio = document.getElementById('moeIndividu');
            const moeEntrepriseFields = document.getElementById('moeEntrepriseFields');
            const individuFields = document.getElementById('moeIndividuFields');
            const typeOuvrage = document.querySelector('input[name="type_ouvrage"]:checked')?.value;

            if (entrepriseRadio.checked) {
                fetchMoeActeurs(typeOuvrage, 'Entreprise');
                moeEntrepriseFields.classList.remove('d-none');
                individuFields.classList.add('d-none');
            } else if (individuRadio.checked) {
                fetchMoeActeurs(typeOuvrage, 'Individu');
                individuFields.classList.remove('d-none');
                moeEntrepriseFields.classList.add('d-none');
            }
        }
        document.getElementById('moePublic').addEventListener('change', toggleTypeMoe);
            document.getElementById('moePrive').addEventListener('change', toggleTypeMoe);
            document.getElementById('moeEntreprise').addEventListener('change', toggleMoeFields);
            document.getElementById('moeIndividu').addEventListener('change', toggleMoeFields);
            ajouterBailleurBtn.addEventListener('click', function () {
            const modal = new bootstrap.Modal(document.getElementById('modalAjouter'));
            modal.show();
        });

        document.getElementById('btnEnregistrer').addEventListener('click', function () {
            alert('Bailleur enregistré avec succès !');
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalAjouter'));
            modal.hide();
        });
    });
</script>
