<?php

use App\Http\Controllers\PlateformeController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\RoleAssignmentController;
use App\Http\Controllers\UserController;
use App\Models\Ecran;
use App\Models\Pays;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnnexeController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CaracteristiqueStructureController;
use App\Http\Controllers\cloturerProjetController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\EtatController;
use App\Http\Controllers\EtudeProjet;
use App\Http\Controllers\GanttController;
use App\Http\Controllers\GeojsonController;
use App\Http\Controllers\GestionDemographieController;
use App\Http\Controllers\GestionFinanciereController;
use App\Http\Controllers\InfrastructureController;
use App\Http\Controllers\InfrastructureMapController;
use App\Http\Controllers\PaysController;
use App\Http\Controllers\ProjetController;
use App\Http\Controllers\SigController;
use App\Http\Controllers\Naissance;
use App\Http\Controllers\ParGeneraux\FonctionTypeActeurController;
use App\Http\Controllers\ParGeneraux\GroupProjectPermissionsController;
use App\Http\Controllers\ParGeneraux\RolePermissionsController;
use App\Http\Controllers\ParGeneraux\TypeActeurController;
use App\Http\Controllers\ParSpecifique\ActeurController;
use App\Http\Controllers\pibController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\ProjectStatusController;
use App\Http\Controllers\ProjetValidationController;
use App\Http\Controllers\RealiseProjetController;
use App\Http\Controllers\representationGraphique;
use App\Http\Controllers\sigAdminController;
use App\Http\Controllers\SigAdminInfrastructureController;
use App\Http\Controllers\StatController;
use App\Http\Controllers\UtilisateurController;
use App\Http\Controllers\WorkflowValidationController;
use App\Models\Domaine;
use App\Models\EtudeProject;
use App\Models\LocalitesPays;
use App\Models\Renforcement;
use App\Models\SousDomaine;
use App\Models\Utilisateur;
use App\Services\GridFsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Laravel\Ui\Presets\React;
use PasswordResetController as GlobalPasswordResetController;


use Illuminate\Support\Facades\Session;
Session::start();

Route::get('/test-session', function () {
    session(['projet_selectionne' => 'test_projet']);
    return response()->json(session()->all());
});

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('/', function () {
    $ecran = Ecran::find(29);
    $ecrans = Ecran::all();
    return view('index', compact('ecran','ecrans'));

});
Route::get('/admin/modele', function () {
    return view('armonisation.modele');

});

// Exemple de route protégée (accessible uniquement aux utilisateurs authentifiés)
Route::middleware(['auth', 'auth.session', 'check.projet'/*, 'prevent.multiple.sessions'*/])->group(function () {
    Route::get('admin', [AdminController::class, 'index'])->name('projets.index');
    Route::get('/projets-data', [AdminController::class, 'getProjetData']);
    Route::get('/admin/initSidebar', [AdminController::class, 'initSidebar']);

    /**************************** PROFIL UTILISATEUR **********************************/
    Route::get('/profil', [ProfilController::class, 'index'])->name('profil.index');
    Route::post('/profil', [ProfilController::class, 'update'])->name('profil.update');



    // PAYS
    Route::get('admin/pays', [PaysController::class, 'pays'])->name('pays');
    // Route pour afficher le formulaire d'édition (GET)
    Route::get('/pays/{id}/edit', [PaysController::class, 'edit']);
    Route::put('/pays/{id}', [PaysController::class, 'update'])->name('pays.update');
    Route::post('/pays', [PaysController::class, 'storePays'])->name('pays.store');
    Route::delete('/pays/{id}', [PaysController::class, 'deletePays'])->name('pays.destroy');
  
    /**************************** GESTION DE DEMOGRAPHIE **********************************/
    /** Page principale */
    Route::get('admin/nombreHabitants', [GestionDemographieController::class, 'habitantIndex'])
        ->name('habitants.index');

    /** APIs AJAX */
    Route::get('admin/demographie/schema',    [GestionDemographieController::class, 'schema']);
    Route::get('admin/demographie/localites', [GestionDemographieController::class, 'localites']);

    /** Enregistrement */
    Route::post('admin/demographie', [GestionDemographieController::class, 'storeHabitants'])->name('habitants.store');

    //  Nouveau : stats et liste
    Route::get('stats', [GestionDemographieController::class, 'stats'])->name('habitants.stats');
    Route::get('entries', [GestionDemographieController::class, 'entries'])->name('habitants.entries');
    /****************************LOCALITE PAYS  ************************************************/
    Route::get('admin/localites',         [GestionDemographieController::class, 'indexLocalite'])->name('localites.index');
    Route::get('admin/localites/schema',   [GestionDemographieController::class, 'schemaLocalite']);
    Route::get('admin/localites/children', [GestionDemographieController::class, 'localitesPays']);
    Route::post('admin/localites',        [GestionDemographieController::class, 'storeLocalite'])->name('localites.store');
    Route::post('admin/localites/import',  [GestionDemographieController::class, 'importLocalite'])->name('localites.import');
    Route::get('admin/localites/template', [GestionDemographieController::class, 'templateLocalite'])->name('localites.template');

    //***************** Genre ************* */
    Route::get('admin/genre', [PlateformeController::class, 'genre'])->name('genre');
    Route::get('admin/genre/{code}', [PlateformeController::class, 'getGenre'])->name('genre.show');
    Route::post('admin/genre', [PlateformeController::class, 'storeGenre'])->name('genre.store');
    Route::post('admin/genre/update', [PlateformeController::class, 'updateGenre'])->name('genre.update');
    Route::delete('admin/genre/delete/{code}', [PlateformeController::class, 'deleteGenre'])->name('genre.delete');
    Route::post('/check-genre-code', [PlateformeController::class, 'checkGenreCode']);



    //***************** Fonctions utilisateur ************* */
    Route::get('admin/fonctionUtilisateur', [PlateformeController::class, 'fonctionUtilisateur'])->name('fonctionUtilisateur');
    Route::get('admin/fonctionUtilisateur/{code}', [PlateformeController::class, 'getFonctionUtilisateur'])->name('fonctionUtilisateur.show');
    Route::post('admin/fonctionUtilisateur', [PlateformeController::class, 'storeFonctionUtilisateur'])->name('fonctionUtilisateur.store');
    Route::post('admin/fonctionUtilisateur/update', [PlateformeController::class, 'updateFonctionUtilisateur'])->name('fonctionUtilisateur.update');
    Route::delete('admin/fonctionUtilisateur/delete/{code}', [PlateformeController::class, 'deleteFonctionUtilisateur'])->name('fonctionUtilisateur.delete');
    Route::post('/check-fonctionUtilisateur-code', [PlateformeController::class, 'checkFonctionUtilisateurCode']);

    //***************** Fonction groupes ************* */

    Route::get('admin/fonctionGroupe', [PlateformeController::class, 'fonctionGroupe'])->name('fonctionGroupe');
    Route::post('/admin/fg/store/', [PlateformeController::class, 'storeFonctionGroupe'])->name('fg.store');
    Route::delete('admin/fonctionGroupe/delete/{code}', [PlateformeController::class, 'deleteFonctionGroupe'])->name('fg.delete');


    //***************** Famille d'infrastructure ************* */
    Route::get('famille/{id}/formulaire', [PlateformeController::class, 'renderForm'])->name('famille.formulaire');
    Route::get('admin/familleinfrastructure', [PlateformeController::class, 'familleinfrastructure'])->name('parGeneraux.familleinfrastructure');
    Route::get('admin/familleinfrastructure/{code}', [PlateformeController::class, 'getFamilleinfrastructure'])->name('familleinfrastructure.show');
    Route::post('admin/familleinfrastructure', [PlateformeController::class, 'storeFamilleinfrastructure'])->name('familleinfrastructure.store');
    Route::post('/familleinfrastructure/{id}/update', [PlateformeController::class, 'updateFamilleInfrastructure'])->name('familleinfrastructure.update');
    Route::delete('/familleinfrastructure/delete/{id}', [PlateformeController::class, 'deleteFamilleInfrastructure'])->name('familleinfrastructure.delete');
    Route::post('/check-familleinfrastructure-code', [PlateformeController::class, 'checkFamilleinfrastructureCode']);
    Route::get('/getDomaineByGroupeProjet/{code}', [PlateformeController::class, 'getDomaineByGroupeProjet']);
    Route::get('/get-sous-domaines/{codeDomaine}/{codeGroupeProjet}', [PlateformeController::class, 'getSousDomaines']);
    Route::delete('/famille-infrastructure/{famille}/structure/delete', [CaracteristiqueStructureController::class, 'destroyStructure']);

    Route::prefix('familles/{famille}/caracteristiques')->name('caracteristiques.')->group(function () {
        // Récupérer toutes les caractéristiques d'une famille (hiérarchie)
        Route::get('/', [CaracteristiqueStructureController::class, 'index'])->name('index');

        // Enregistrer un ensemble de caractéristiques (JSON imbriqué)
        Route::post('/', [CaracteristiqueStructureController::class, 'store'])->name('store');

        // Mettre à jour la structure complète
        Route::put('/', [CaracteristiqueStructureController::class, 'update'])->name('update');

        // Supprimer toutes les caractéristiques d'une famille (optionnel)
        Route::delete('/', [CaracteristiqueStructureController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('caracteristiques')->name('caracteristique.')->group(function () {
        Route::get('/{id}', [CaracteristiqueStructureController::class, 'show'])->name('show');
        Route::put('/{id}', [CaracteristiqueStructureController::class, 'updateSingle'])->name('update.single');
        Route::delete('/{id}', [CaracteristiqueStructureController::class, 'destroySingle'])->name('destroy.single');
    });

    // Routes pour la structure hiérarchique des caractéristiques
    Route::prefix('famille-infrastructure/{famille}/structure')->group(function() {
        Route::get('/data', [CaracteristiqueStructureController::class, 'getStructures'])->name('famille.structure.get');
        Route::post('/save', [CaracteristiqueStructureController::class, 'saveStructure'])->name('famille.structure.save');
    });
    Route::delete('famille-infrastructure/{famille}/structure', [CaracteristiqueStructureController::class, 'destroy'])->name('famille.structure.destroy');

    //***************** Action à mener ************* */
    Route::get('admin/actionmener', [PlateformeController::class, 'actionMener'])->name('actionMener');
    Route::get('admin/actionmener/{code}', [PlateformeController::class, 'getActionmener'])->name('actionMener.show');
    Route::post('admin/actionmener', [PlateformeController::class, 'storeActionmener'])->name('actionMener.store');
    Route::post('admin/actionmener/update', [PlateformeController::class, 'updateActionmener'])->name('actionMener.update');
    Route::delete('admin/actionmener/delete/{code}', [PlateformeController::class, 'deleteActionmener'])->name('actionMener.delete');
    Route::post('/check-actionmener-code', [PlateformeController::class, 'checkActionmenerCode']);

    //***************** approbation ************* */
    Route::get('admin/commissionValidation', [PlateformeController::class, 'approbation'])->name('approbation');
    Route::post('/storeApprobation', [PlateformeController::class, 'storeApprobation'])->name('approbateur.store');
    Route::delete('/approbation/{id}', [PlateformeController::class, 'deleteApprobation'])->name('approbateur.delete');
    Route::put('/approbateur/update', [PlateformeController::class, 'updateApprobateur'])->name('approbateur.update');
    Route::get('/get-structure/{code_personnel}', [PlateformeController::class, 'getStructure'])->name('getStructure');

    //***************** Dévises ************* */
    Route::get('admin/devises', [PlateformeController::class, 'devises'])->name('devises');
    Route::get('admin/devise/{code}', [PlateformeController::class, 'getDevise'])->name('devise.show');
    Route::post('admin/devise', [PlateformeController::class, 'storeDevise'])->name('devise.store');
    Route::post('admin/devise/update', [PlateformeController::class, 'updateDevise'])->name('devise.update');
    Route::delete('admin/devise/delete/{code}', [PlateformeController::class, 'deleteDevise'])->name('devise.delete');
    Route::post('/check-devise-code', [PlateformeController::class, 'checkDeviseCode']);

    //***************** Domaines ************* */
    Route::get('admin/domaines', [PlateformeController::class, 'domaines'])->name('domaines');
    Route::get('admin/domaine/{code}', [PlateformeController::class, 'getDomaine'])->name('domaine.show');
    Route::post('admin/domaines', [PlateformeController::class, 'storeDomaine'])->name('domaine.store');
    Route::put('admin/domaines/update', [PlateformeController::class, 'updateDomaine'])->name('domaine.update');
    Route::delete('admin/domaine/delete/{code}', [PlateformeController::class, 'deleteDomaine'])->name('domaine.delete');
    Route::post('/check-domaine-code', [PlateformeController::class, 'checkDomaineCode']);


    //***************** sous-domaines ************* */
    Route::post('/check-sous-domaines-code', [PlateformeController::class, 'checkSousDomaineCode']);
    Route::get('admin/sous-domaines', [PlateformeController::class, 'sousDomaines'])->name('sous_domaines');
    Route::get('admin/sous-domaines/{code}', [PlateformeController::class, 'getSousDomaine'])->name('sous_domaines.show');
    Route::post('admin/sous-domaines', [PlateformeController::class, 'storeSousDomaine'])->name('sous_domaines.store');
    Route::post('admin/sous-domaines/update', [PlateformeController::class, 'updateSousDomaine'])->name('sous_domaines.update');
    Route::delete('admin/sous-domaines/delete/{code}', [PlateformeController::class, 'deleteSousDomaine'])->name('sous_domaines.delete');




    //***************** PROJETS ************* */
    Route::get('admin/projets/liste', [ProjetController::class, 'ConsultationProjet'])->name('projets.consultation');
    Route::get('admin/projet', [ProjetController::class, 'projet'])->name('projet');
    Route::get('/get-sous-domaines/{domaineCode}', [ProjetController::class, 'getSousDomaines']);
    Route::get('admin/get-cours_eau/{eauId}', [ProjetController::class, 'getCours_eau']);
    Route::get('admin/get-insfrastructures/{domaineId}', [ProjetController::class, 'getInsfrastructures']);
    Route::get('/getBeneficiaires/{type}/{code}', 'ProjetController@getBeneficiaires')->name('getBeneficiaires');
    Route::get('/verifier_code_projet', [ProjetController::class, 'verifierCodeProjet']);
    Route::post('/recup', [ProjetController::class, 'votreFonction'])->name('maRoute');
    //Route::post('/enregistrer-formulaire', [ProjetController::class, 'store'])->name('enregistrer.formulaire');
    Route::get('/projet/getTable', [ProjetController::class, 'getTable']);
    Route::get('admin/editionProjet', [ProjetController::class, 'editionProjet']);

    Route::post('/contrats/store', [ProjetController::class, 'store'])->name('contrats.store');
    Route::get('/contrats/{id}/fiche', [ProjetController::class, 'fiche'])->name('contrats.fiche');
    Route::get('/contrats/{id}/pdf', [ProjetController::class, 'pdf'])->name('contrats.pdf');
    Route::put('/contrats/{id}', [ProjetController::class, 'update'])->name('contrats.update');
    Route::delete('/contrats/{id}', [ProjetController::class, 'destroy'])->name('contrats.destroy');
    Route::get('admin/projet/changementChefProjet', [ProjetController::class, 'changerChef']);
    Route::post('/contrats/chef/update', [ProjetController::class, 'changerChefUpdate'])->name('contrats.chef.update');

        /*****************ETUDE DE PROJET**************** */
        Route::get('admin/naissanceProjet',[EtudeProjet::class, 'createNaissance'])->name('project.create');
        Route::get('/pays/{alpha3}/niveaux', [EtudeProjet::class, 'getNiveauxAdministratifs']);
        Route::get('/pays/{alpha3}/niveau/{niveau}/localites', [EtudeProjet::class, 'getLocalitesByNiveau']);
        Route::get('/get-latest-project-number/{location}/{category}/{typeFinancement}', [EtudeProjet::class, 'getLatestProjectNumber']);
        Route::get('admin/modeliser', [EtudeProjet::class, 'modelisation']);
        Route::get('/get-bailleurs', [EtudeProjet::class, 'getBailleursParStatutLocal']);

                /*******************SAUVEGARDE DE DEMANDE DE PROJET */
                Route::post('/projets/temp/save-step1', [EtudeProjet::class, 'saveStep1'])->name('projets.temp.save.step1');
                Route::post('/projets/temp/save-step2', [EtudeProjet::class, 'saveStep2'])->name('projets.temp.save.step2');
                Route::post('/projets/temp/save-step3', [EtudeProjet::class, 'saveStep3'])->name('projets.temp.save.step3');
                Route::post('/projets/temp/save-step4', [EtudeProjet::class, 'saveStep4'])->name('projets.temp.save.step4');
                Route::post('/projets/temp/save-step5', [EtudeProjet::class, 'saveStep5'])->name('projets.temp.save.step5');
                Route::post('/projets/temp/save-step6', [EtudeProjet::class, 'saveStep6'])->name('projets.temp.save.step6');
                Route::post('/projets/temp/save-step7', [EtudeProjet::class, 'saveStep7'])->name('projets.temp.save.step7');
                Route::post('/projets/finaliser', [EtudeProjet::class, 'finaliserProjet'])->name('projets.finaliser');

        /***********************VALIDATION***************** */

            Route::get('admin/validationProjetss', [EtudeProjet::class, 'validation'])->name('projects.validate');
            Route::get('/planning/show', [EtudeProjet::class, 'showPlanning'])->name('planning.show');
            Route::post('/planning/{id}/approve', [EtudeProjet::class, 'approve'])->name('projects.approve');
            Route::get('/get-infrastructure-localite/{code}', [EtudeProjet::class, 'getLocaliteInfrastructure']);

            Route::get('admin/validationProjet', [ProjetValidationController::class, 'index'])->name('projets.validation.index');
            Route::get('/projets/validation/{codeProjet}', [ProjetValidationController::class, 'show'])->name('projets.validation.show');

            Route::post('/projets/validation/{codeProjet}/valider', [ProjetValidationController::class, 'valider'])->name('projets.validation.valider');
            Route::post('/projets/validation/{codeProjet}/refuser', [ProjetValidationController::class, 'refuser'])->name('projets.validation.refuser');
        /************************SUIVRE APPROBATION************* */
            Route::get('admin/Svapprob', [EtudeProjet::class, 'suivreApp']);
        /************************Historique approbation ************* */
            Route::get('admin/histAppb', [EtudeProjet::class, 'historiqueApp'])->name('approval.history');

        /********************PLANIFICATION***************** */
            Route::get('admin/planifierProjet', [GanttController::class, 'index']);
            Route::get('/data', [GanttController::class ,'get']);
        /********************RENFORCEMENT***************** */
         // LISTE / FORMULAIRE écran
        Route::get('admin/renforcementProjet', [EtudeProjet::class, 'indexRenfo'])->name('renforcements.index');
        // changer le statut (démarrer, achever, reporter, annuler)
        Route::put('/renforcementProjet/status/{code}', [EtudeProjet::class, 'updateRenfoStatus'])->name('renforcements.status');

        // CRUD
        Route::post('/renforcementProjet/store', [EtudeProjet::class, 'storeRenfo'])->name('renforcements.store');

        Route::put('/renforcementProjet/update/{code}', [EtudeProjet::class, 'updateRenfo'])->name('renforcements.update');

        Route::delete('/renforcementProjet/delete/{code}', [EtudeProjet::class, 'destroyRenfo'])->name('renforcements.destroy');
                /****************************ACTIVITE CONNEXE******************** */
                    Route::get('admin/activiteConnexeProjet',[EtudeProjet::class, 'activite'])->name('activite.index');
                    Route::post('admin/activiteConnexeProjet', [EtudeProjet::class, 'storeConnexe'])->name('travaux_connexes.store');
                    Route::delete('/activiteDelete/{id}', [EtudeProjet::class, 'deleteActivite'])->name('travaux_connexes.destroy');
                    Route::put('/activite/{id}', [EtudeProjet::class, 'updateConnexe'])->name('travaux_connexes.update');
                /**************************** REATTRIBUTION DE PROJET ******************************/
                    Route::get('admin/reatributionProjet', [ProjetController::class, 'reatributionProjet'])->name('maitre_ouvrage.index');
                    Route::get('/get-execution-by-projet/{code_projet}', [ProjetController::class, 'getExecutionByProjet']);
                    Route::get('/getProjetADeleted/{code_projet}', [ProjetController::class, 'getProjetSupprimer']);
                    Route::prefix('reatributionProjet')->group(function () {
                        Route::post('/', [ProjetController::class, 'storeReatt'])->name('maitre_ouvrage.store');
                        Route::put('/{id}', [ProjetController::class, 'updateReatt'])->name('maitre_ouvrage.update');
                        Route::delete('/{id}', [ProjetController::class, 'destroyReatt'])->name('maitre_ouvrage.destroy');
                    });
                /**************************** ANNULER DE PROJET ******************************/
                Route::get('admin/annulProjet', [ProjetController::class, 'formAnnulation'])->name('projets.annulation.form');
                Route::post('/projets/annulation', [ProjetController::class, 'annulerProjet'])->name('projets.annulation.store');
                Route::post('/projets/redemarrer', [ProjetController::class, 'redemarrerProjet'])->name('projets.redemarrer');

                /*******************************SUSPENDRE PROJET ***************************** */
                Route::get('admin/attenteProjet', [ProjetController::class, 'formSuspension'])->name('projets.suspension.form');
                Route::post('/projets/suspendre', [ProjetController::class, 'suspendreProjet'])->name('projets.suspension.store');



            /**************************** GESTION DES EDITIONS **********************************/

            Route::get('admin/editionProjet', [AnnexeController::class, 'index'])->name('admin.edition.projet');
            // Routes pour les exports PDF
                Route::get('/pdf/projet/{code}', [AnnexeController::class, 'exportProjet'])->name('pdf.projet');
                Route::get('/pdf/acteur/{code}', [AnnexeController::class, 'exportActeur'])->name('pdf.acteur');
                Route::get('/pdf/contrat/{code}', [AnnexeController::class, 'exportContrat'])->name('pdf.contrat');
                Route::get('/pdf/infrastructure/{code}', [AnnexeController::class, 'exportInfrastructure'])->name('pdf.infrastructure');

                // Route pour l'export multiple
                Route::post('/pdf/export-multiple', [AnnexeController::class, 'exportMultiple'])->name('pdf.export.multiple');

            Route::get('/projets/{projet}', [AnnexeController::class, 'show'])
            ->name('projets.show');
            
            //***************** REALISATION ************* */
            Route::get('admin/realise/PramatreRealise', [RealiseProjetController::class, 'PramatreRealise']);
            Route::get('admin/realise', [RealiseProjetController::class, 'realise']);
            Route::post('/get-project-details', [RealiseProjetController::class, 'getProjectDetails'])->name('get.project.details');
            Route::post('/fetch-project-details', [RealiseProjetController::class, 'fetchDetails'])->name('fetch.project.details');
            Route::get('/admin/realise', [RealiseProjetController::class, 'VoirListe'])->name('projet.realise');
            Route::get('/fetchProjectDetails', [RealiseProjetController::class, 'fetchProjectDetails']);
            Route::get('/getProjetData', [RealiseProjetController::class, 'getProjetData']);
            Route::get('/getBeneficiaires', [RealiseProjetController::class, 'getBeneficiaires']);
            Route::get('/getNumeroOrdre', [RealiseProjetController::class, 'getNumeroOrdre']);
            Route::get('/getFamilleInfrastructure', [RealiseProjetController::class, 'getFamilleInfrastructure']);
            Route::get('/getInfrastructuresByProjet', [RealiseProjetController::class, 'getInfrastructuresByProjet']);
            Route::get('/get-familles-by-projet', [RealiseProjetController::class, 'getFamillesByProjet']);
            Route::get('/recuperer-caracteristiques', [RealiseProjetController::class, 'recupererCaracteristiques'])
            ->name('projets.recuperer.caracteristiques');

            //Route::get('/getDataDateEffective', [RealiseProjetController::class, 'obtenirDonneesProjet'])->name('obtenir-donnees-projet');
            Route::get('admin/etatAvancement', [RealiseProjetController::class, 'etatAvancement']);
            Route::post('/admin/realise', [RealiseProjetController::class,'enregistrerBeneficiaires'])->name('enregistrer.beneficiaires');
            Route::get('/recuperer-beneficiaires', [RealiseProjetController::class, 'recupererBeneficiaires'])->name('recuperer-beneficiaires');
            Route::post('/enregistrer-dates-effectives', [RealiseProjetController::class, 'enregistrerDatesEffectives'])->name('enregistrer-dates-effectives');
            Route::get('/check-code-projet', [RealiseProjetController::class, 'checkCodeProjet']);
            Route::post('/enregistrer-niveau-avancement', [RealiseProjetController::class, 'enregistrerNiveauAvancement'])->name('enregistrer.niveauAvancement');
            Route::post('/enregistrer-dateseffectives', [RealiseProjetController::class, 'enregistrerDateFinEffective'])->name('enregistrer.dateFinEffective');
            Route::get('/get-historique-avancement', [RealiseProjetController::class, 'getHistorique'])->name('get.historique.avancement');
            Route::post('/save-avancement', [RealiseProjetController::class, 'saveAvancement'])->name('save.avancement');
            Route::delete('/delete-suivi/{id}', [RealiseProjetController::class, 'deleteSuivi'])->name('delete.suivi');

            Route::post('/caracteristiques/store', [RealiseProjetController::class, 'storeCaracteristiques'])->name('caracteristique.store');
            Route::get('/get-donnees-suivi', [RealiseProjetController::class, 'getDonneesFormulaireSimplifie'])->name('get.donnees.suivi');

            Route::get('/verifier-projet-finalisable', [RealiseProjetController::class, 'verifierProjetFinalisable'])->name('verifier.projet.finalisable');

            Route::get('/get-project-status/{id}', [ProjectStatusController::class, 'getProjectStatus']);    //***************** GESTION FINANCIERE ************* */
            Route::get('admin/graphique', [representationGraphique::class, 'graphique']);


            //********************CLOTURER **************************//
            Route::get('admin/cloture', [cloturerProjetController::class, 'cloturer']);
            Route::post('/cloturer-projet', [cloturerProjetController::class, 'cloturerProjet'])->name('cloturer.projet');
            //***************** GESTION SIG ************* */
            Route::get('admin/carte', [sigAdminController::class, 'carte']);
            //Route::get('admin/autresRequetes', [InfrastructureMapController::class, 'showMap'])->name('infrastructures.map');
            Route::get('admin/autresRequetes', [sigAdminController::class, 'page'])->name('sig.infras.page');
            Route::get('/api/infrastructures/geojson', [InfrastructureMapController::class, 'getInfrastructuresGeoJson']);
            Route::get('/api/infrastructures/familles-colors', [InfrastructureMapController::class, 'getFamillesColors']);

            Route::get('', [sigAdminController::class, 'Autrecarte']);
            //Route::get('/filtre-options', [sigAdminController::class, 'getFiltreOptions']);
            //Route::get('admin/autresRequetes', [sigAdminController::class, 'page'])->name('sig.infras.page');
            // Légende dynamique
            
            Route::get('admin/autresRequetes', [SigAdminInfrastructureController::class, 'pageInfras'])->name('sig.infras');

            // agrégats & légende
            /*          Route::get('/api/legende/{groupe}', [SigAdminInfrastructureController::class, 'getByGroupe']);

                        // Agrégat principal (utilisé par map.js → loadProjectData)
                        Route::get('/api/projects', [SigAdminInfrastructureController::class, 'getProjects']);
                        Route::get('/api/filtrer-projets', [SigAdminInfrastructureController::class, 'getFiltreOptionsEtProjets']);

                        // Détails (tiroir)
                        Route::get('/api/project-details', [SigAdminInfrastructureController::class, 'getProjectDetails']);

                        // Légende fallback (optionnel)
                        Route::get('/api/legend', [SigAdminInfrastructureController::class, 'legend']);

                        // Carte Afrique
                        Route::get('/api/projects/all', [SigAdminInfrastructureController::class, 'getAllProjects']);

                        // (optionnels, si utilisés ailleurs)
                        Route::get('/api/aggregate', [SigAdminInfrastructureController::class, 'aggregate']);
                        Route::get('/api/details',   [SigAdminInfrastructureController::class, 'details']);
            */

            Route::get('/get-projet-data', 'ProjetController@getProjetData');

            Route::get('/dash', function () {
                return view('dash');
            })->name('dash');

            /***************************WORKFLOW DE VALIDATION******************** */
                        
                    // --- VUES (toutes servies par le contrôleur) ---
                    Route::get('admin/workflows/ui',                 [WorkflowValidationController::class, 'ui'])->name('workflows.ui');
                    Route::get('admin/workflows/create',             [WorkflowValidationController::class, 'createForm'])->name('workflows.createForm');
                    Route::get('admin/workflows/{id}/design',        [WorkflowValidationController::class, 'designForm'])->name('workflows.designForm');
                    Route::get('admin/workflows/{id}/bindings',      [WorkflowValidationController::class, 'bindingsView'])->name('workflows.bindingsView');
                    Route::get('admin/approbations/{instance}/view', [WorkflowValidationController::class, 'instanceView'])->name('approbations.instanceView');
                    Route::get('admin/approbations/dashboard', [WorkflowValidationController::class, 'dashboard'])->name('approbations.dashboard');
                    Route::post('/approbations/steps/{stepInstanceId}/act', [WorkflowValidationController::class, 'act'])->name('approbations.act');
               
                    // --- Conception / Admin (JSON) ---
                    Route::get('/workflows',                [WorkflowValidationController::class, 'index'])->name('workflows.index');
                    Route::post('/workflows',               [WorkflowValidationController::class, 'store'])->name('workflows.store');
                    Route::get('/workflows/{id}',           [WorkflowValidationController::class, 'show'])->name('workflows.show');
                    Route::put('/workflows/{id}',           [WorkflowValidationController::class, 'update'])->name('workflows.update');
                    Route::post('/workflows/{id}/publish',  [WorkflowValidationController::class, 'publish'])->name('workflows.publish');
                    Route::delete('/workflows/{id}',        [WorkflowValidationController::class, 'destroy'])->name('workflows.destroy');

                    // Liaisons (bindings)
                    Route::post('/workflows/{id}/bind',     [WorkflowValidationController::class, 'bind'])->name('workflows.bind');
                    Route::get('/workflows/{id}/bindings',  [WorkflowValidationController::class, 'bindings'])->name('workflows.bindings');

                    // --- Exécution (JSON) ---
                    Route::post('/approbations/start',      [WorkflowValidationController::class, 'start'])->name('approbations.start');
                    Route::get('/approbations/{instance}',  [WorkflowValidationController::class, 'showInstance'])->name('approbations.show');

                    // Actions
                    Route::post('/approbations/etapes/{stepInstance}/act', [WorkflowValidationController::class, 'act'])->name('approbations.act');

                    // Simulation & SLA
                    Route::post('/workflows/{id}/simulate', [WorkflowValidationController::class, 'simulate'])->name('workflows.simulate');
                    Route::post('/workflows/sla/tick',      [WorkflowValidationController::class, 'slaTick'])->name('workflows.slaTick');

            /****************************GESTION FINANCIERE  **********************************************/
            Route::get('gf/decaissements/financements/{codeProjet}', [GestionFinanciereController::class, 'financementsByProjet'])
                ->name('gf.decaissements.financementsByProjet');

            Route::get ('admin/decaissementBailleurs',        [GestionFinanciereController::class, 'decaissementsIndex'])->name('gf.decaissements.index');
            Route::post('admin/decaissementBailleurs',        [GestionFinanciereController::class, 'decaissementsStore'])->name('gf.decaissements.store');
            Route::put ('admin/decaissementBailleurs/{id}',   [GestionFinanciereController::class, 'decaissementsUpdate'])->name('gf.decaissements.update');
            Route::delete('admin/decaissementBailleurs/{id}', [GestionFinanciereController::class, 'decaissementsDestroy'])->name('gf.decaissements.destroy');
            Route::get('gf/decaissements/next-tranche',       [GestionFinanciereController::class, 'getNextTranche'])->name('gf.decaissements.nextTranche');

            Route::get   ('admin/achatsMateriaux',            [GestionFinanciereController::class, 'achatsIndex'])->name('gf.achats.index');
            Route::post  ('admin/achatsMateriaux',            [GestionFinanciereController::class, 'achatsStore'])->name('gf.achats.store');
            Route::put   ('admin/achatsMateriaux/{id}',       [GestionFinanciereController::class, 'achatsUpdate'])->name('gf.achats.update');
            Route::delete('admin/achatsMateriaux/{id}',       [GestionFinanciereController::class, 'achatsDestroy'])->name('gf.achats.destroy');
            
            // Règlements prestataires
            Route::get   ('admin/reglementsPrestataires',      [GestionFinanciereController::class, 'reglementsIndex'])->name('gf.reglements.index');
            Route::post  ('admin/reglementsPrestataires',      [GestionFinanciereController::class, 'reglementsStore'])->name('gf.reglements.store');
            Route::put   ('admin/reglementsPrestataires/{id}', [GestionFinanciereController::class, 'reglementsUpdate'])->name('gf.reglements.update');
            Route::delete('admin/reglementsPrestataires/{id}', [GestionFinanciereController::class, 'reglementsDestroy'])->name('gf.reglements.destroy');
            Route::get('/context/{codeProjet}', [GestionFinanciereController::class, 'contextByProjet'])->name('gf.reglements.contextByProjet');
            
            Route::get('admin/representationGraphique', [GestionFinanciereController::class, 'representationGraphique'])->name('gf.representation');
            
                Route::get('admin/pib', [GestionFinanciereController::class, 'pibIndex'])->name('gf.pib.index');

                // Data JSON pour le graphe par secteur
                Route::get('admin/pib/data', [GestionFinanciereController::class, 'pibParSecteurData'])->name('gf.representations.pib.data');
                 // CRUD PIB
                Route::post('admin/pib/store', [GestionFinanciereController::class, 'storePIB'])->name('pib.store');
                Route::put('admin/pib/update/{id}', [GestionFinanciereController::class, 'updatePIB'])->name('pib.update');
                Route::delete('admin/pib/destroy/{id}', [GestionFinanciereController::class, 'destroyPIB'])->name('pib.destroy');
   
            Route::get('admin/banques', [PlateformeController::class, 'indexBanque'])->name('banques.index');
            Route::get('/banques/list', [PlateformeController::class, 'listBanque'])->name('banques.list');
            Route::post('/banques', [PlateformeController::class, 'storeBanque'])->name('banques.store');
            Route::put('/banques/{id}', [PlateformeController::class, 'updateBanque'])->name('banques.update');
            Route::delete('/banques/{id}', [PlateformeController::class, 'destroyBanque'])->name('banques.destroy');
            /**************************** GESTION DES STATISTIQUES **********************************/

            Route::prefix('admin')->group(function () {
                Route::get('stat_nombre_projet', [StatController::class, 'statNombreProjet'])->name('tb.nombre.vue');
                Route::get('stat-finance',       [StatController::class, 'statFinance'])->name('tb.finance.vue');
            });

            Route::get('/nombreProjetLien',            [StatController::class, 'statNombreData'])->name('nombre.data');
            Route::get('/stat-finance_projet/data',    [StatController::class, 'statFinanceData'])->name('finance.data');

            /**************************** GESTION DES UTILISATEURS **********************************/
            Route::get('admin/personnel', [UserController::class, 'personnel'])->name('users.personnel');
            Route::get('admin/personnel/create', [UserController::class, 'createPersonnel'])->name('personnel.create');
            Route::post('admin/personnel/store', [UserController::class, 'storePersonnel'])->name('personnel.store');
            Route::delete('/admin/personnel/{code_personnel}', [UserController::class, 'destroy'])->name('utilisateurs.destroy');
            Route::get('/domaines/{groupeProjet}', [UserController::class, 'getDomainesByGroupeProjet']);
            Route::get('/sous-domaines/{domaine}/{groupeProjet}', [UserController::class, 'getSousDomaines']);

            Route::get('admin/personnel/details-personne/{personneId}', [UserController::class, 'detailsPersonne'])->name('personnel.details');
            Route::get('admin/personnel/get-personne/{personneId}', [UserController::class, 'getPersonne'])->name('personne.updateForm');
            Route::post('admin/personnel/update/{personnelId}', [UserController::class, 'updatePersonne'])->name('personne.update');
            Route::get('/check-email-personne', [UserController::class, 'checkEmail_personne']);
            Route::get('admin/get-personne-email/{personnelId}', [UserController::class, 'getPersonneInfos'])->name('personne.get');
            Route::post('/SousDomaine_Domaine-ajax', [UserController::class, 'getDomaines']);
            Route::post('/changer-mot-de-passe', [UtilisateurController::class, 'changePassword'])->name('password.change');

            Route::get('admin/users', [UserController::class, 'users'])->name('users.users');
            Route::get('admin/users/create', [UserController::class, 'create'])->name('users.create');
            Route::post('/fetch-sous-domaine', [UserController::class, 'fetchSousDomaine'])->name('fetch.sous_domaine');
            Route::post('/admin/users/store', [UserController::class, 'store'])->name('users.store');
            Route::get('/check-username', [UserController::class, 'checkUsername']);
            Route::get('/check-email', [UserController::class, 'checkEmail']);
            Route::get('admin/users/get-user/{userId}', [UserController::class, 'getUser'])->name('users.get');
            Route::get('/admin/users/details-user/{userId}', [UtilisateurController::class, 'detailsUser'])->name('users.details');
            Route::post('/admin/users/update/{userId}', [UserController::class, 'update'])->name('users.update');
            Route::post('/admin/users/details-user/{userId}', [UserController::class, 'update_auth'])->name('users.update_auth');
            Route::post('/change-password', [UserController::class, 'changePassword'])->name('password.change');
            Route::delete('/admin/delete-user/{id}', [UserController::class, 'deleteUser'])->name('users.delete');
            Route::get('/getIndicatif/{paysId}', [UserController::class, 'getIndicatif'])->name('getIndicatif');
            Route::post('/admin/utilisateurs/debloquer/{id}', [UtilisateurController::class, 'debloquer'])->name('utilisateurs.debloquer');

            Route::get('RecupererDonneesUser/{userId}', [ProjetController::class, 'getDonneUser'])->name('GetDonneeUser');
            // Routes principales pour les infrastructures
            Route::get('/familles/{codeDomaine}', [InfrastructureController::class, 'getFamillesByDomaine']);
            Route::get('/familles-by-domaine/{codeDomaine}', [InfrastructureController::class, 'getFamillesByDomaine']);
            Route::get('/familles/{idFamille}/caracteristiques', [InfrastructureController::class, 'getCaracteristiques']);
            Route::get('/famillesCaracteristiquess/{idFamille}/', [InfrastructureController::class, 'getCaracteristiques']);

            Route::prefix('admin/infrastructures')->group(function () {
            Route::get('/{id}/impression', [InfrastructureController::class, 'print'])->name('infrastructures.print');
            Route::get('/infrastructures/print', [InfrastructureController::class, 'imprimer'])->name('infrastructures.imprimer');
            // Historique
            Route::get('/{id}/historique', [InfrastructureController::class, 'historique'])
            ->name('infrastructures.historique');

            Route::put('/infrastructures/{infrastructure}/caracteristiques', [InfrastructureController::class, 'updateCaracteristiques'])
            ->name('infrastructures.caracteristiques.updateMultiple');

            Route::delete('admin/infrastructure/image/{id}/{code}', [InfrastructureController::class, 'deleteImage'])->name('infrastructure.image.delete');

            // Liste des infrastructures
            Route::get('/', [InfrastructureController::class, 'index'])
                ->name('infrastructures.index');

            // Formulaire de création
            Route::get('/create', [InfrastructureController::class, 'create'])
                ->name('infrastructures.create');

            // Enregistrement
            Route::post('/', [InfrastructureController::class, 'store'])
                ->name('infrastructures.store');

            // Détails d'une infrastructure
            Route::get('/{id}', [InfrastructureController::class, 'show'])
                ->name('infrastructures.show');

            // Formulaire d'édition
            Route::get('/{id}/edit', [InfrastructureController::class, 'edit'])
                ->name('infrastructures.edit');

            // Mise à jour
            Route::put('/{id}', [InfrastructureController::class, 'update'])
                ->name('infrastructures.update');

            // Suppression
            Route::delete('/{id}', [InfrastructureController::class, 'destroy'])
                ->name('infrastructures.destroy');

            // Gestion des caractéristiques
            Route::post('/{id}/caracteristiques', [InfrastructureController::class, 'storeCaracteristique'])
                ->name('infrastructures.caracteristiques.store');

            Route::delete('/caracteristiques/{id}', [InfrastructureController::class, 'destroyCaracteristique'])
                ->name('infrastructures.caracteristiques.destroy');
            Route::get('/localites/by-pays', [InfrastructureController::class, 'getByPays'])
            ->name('localites.byPays');

            Route::get('/localites/niveaux', [InfrastructureController::class, 'getNiveaux'])
                ->name('localites.niveaux');
                Route::get('/localites/by-pays/{paysCode}', function ($paysCode) {
                    return response()->json(
                        LocalitesPays::getByPaysCode($paysCode)
                    );
                });

                Route::get('/localites/{codeLocalite}/details', function ($codeLocalite) {
                    return response()->json(
                        LocalitesPays::getFullLocaliteData($codeLocalite)
                    );
                });

        });

    /**************************** GESTION DES HABILITATIONS **********************************/
     Route::get('/admin/habilitations', [RoleAssignmentController::class, 'habilitations'])->name('habilitations.index');
     Route::get('/admin/role-assignment', [RoleAssignmentController::class, 'index'])->name('role-assignment.index');
     Route::post('/admin/role-assignment/assign', [RoleAssignmentController::class, 'assignRoles'])->name('role-assignment.assign');
     Route::get('/get-role-permissions/{groupeId}', [RoleAssignmentController::class, 'getRolePermissions']);




     Route::get('/admin/rubriques', [RoleAssignmentController::class, 'rubriques'])->name('rubriques.index');
     Route::post('/admin/rubrique/store', [RoleAssignmentController::class, 'storeRubrique'])->name('rubrique.store');
     Route::get('/admin/rubrique/get-rubrique/{id}', [RoleAssignmentController::class, 'getRubrique'])->name('rubrique.updateForm');
     Route::post('/admin/rubrique/update', [RoleAssignmentController::class, 'updateRubrique'])->name('rubrique.update');
     Route::delete('admin/rubrique/delete/{code}', [RoleAssignmentController::class, 'deleteRubrique'])->name('rubrique.delete');
     Route::get('/get-sous-menus/{rubrique}', [RoleAssignmentController::class, 'getSousMenus']);
    // Sous-menu: JSON pour préremplir le formulaire d’édition
    Route::get('/admin/sous_menu/get-sous_menu/{code}', [RoleAssignmentController::class, 'getSous_menu'])
    ->name('sous_menu.get');

    // Sous-menus parents possibles (filtrés) pour le <select> Parent
    Route::get('/admin/sous_menu/parents', [RoleAssignmentController::class, 'getSousMenusParents'])
    ->name('sous_menu.parents');


     Route::get('/admin/sous_menu', [RoleAssignmentController::class, 'sous_menus'])->name('sous_menu.index');
     Route::post('/admin/sous_menu/store', [RoleAssignmentController::class, 'storeSous_menu'])->name('sous_menu.store');
     Route::get('/admin/sous_menu/get-sous_menu/{id}', [RoleAssignmentController::class, 'getSous_menu'])->name('sous_menu.updateForm');
     Route::post('/admin/sous_menu/update', [RoleAssignmentController::class, 'updateSous_menu'])->name('sous_menu.update');
     Route::delete('admin/sous_menu/delete/{code}', [RoleAssignmentController::class, 'deleteSous_menu'])->name('sous_menu.delete');

     Route::get('/admin/ecrans', [RoleAssignmentController::class, 'ecrans'])->name('ecran.index');
     Route::post('/admin/ecran/store', [RoleAssignmentController::class, 'storeEcran'])->name('ecran.store');
     Route::get('/admin/ecran/get-ecran/{id}', [RoleAssignmentController::class, 'getEcran'])->name('ecran.updateForm');
     Route::post('/admin/ecran/update', [RoleAssignmentController::class, 'updateEcran'])->name('ecran.update');
     Route::delete('admin/ecran/delete/{code}', [RoleAssignmentController::class, 'deleteEcran'])->name('ecran.delete');
     Route::post('/admin/ecran/bulk-delete', [RoleAssignmentController::class, 'bulkDeleteEcrans'])->name('ecran.bulkDelete');



     Route::post('/get-groups-by-country', [LoginController::class, 'getGroupsByCountry'])->name('login.getGroupsByCountry');
     Route::post('/change-group', [LoginController::class, 'changeGroup'])->name('login.changeGroup');

    Route::get('/get-progress/{key}', function ($key) {
        return response()->json(['progress' => Cache::get($key, 0)]);
    });







    Route::get('/notifications', function () {
        return view('notifications');
    })->name('notifications');

     /*************************GEOJSON */

     Route::post('/geojson', [GeojsonController::class, 'store'])->name('geojson.store');

     // Servir aux cartes (accepte 0..4 : tu as L0 et L4 en base)
     Route::get('/geojson/{alpha3}/{level}.json.js', [GeojsonController::class, 'serveJs'])
         ->where(['alpha3' => '[A-Z]{3}', 'level' => '[0-4]']);

     Route::get('/geojson/{alpha3}/{level}.json', [GeojsonController::class, 'serveJson'])
         ->where(['alpha3' => '[A-Z]{3}', 'level' => '[0-4]']);

    /*************************TYPE ACTEURS */
    Route::get('admin/type-acteurs', [TypeActeurController::class, 'index'])->name('type-acteurs.index');
    Route::get('/get-infrastructures/{domaine}/{sousDomaine}/{pays}', [EtudeProjet::class, 'getInfrastructures']);

    Route::get('/get-acteurs', [EtudeProjet::class, 'getActeurs'])->name('acteur.filter');
    Route::get('/get-actor-details', [EtudeProjet::class, 'getActeurs'])->name('acteur.filter');
    Route::post('/type-acteurs', [TypeActeurController::class, 'store'])->name('type-acteurs.store');
    Route::put('/type-acteurs/{cd_type_acteur}', [TypeActeurController::class, 'update'])->name('type-acteurs.update');
    Route::delete('/type-acteurs/{cd_type_acteur}', [TypeActeurController::class, 'destroy'])->name('type-acteurs.destroy');
    Route::delete('/type-acteurs/bulk-delete', [TypeActeurController::class, 'bulkDelete'])->name('type-acteurs.bulkDelete');
    Route::get('/get-localites/{pays}', [EtudeProjet::class, 'getLocalites']);
    Route::get('/get-familles/{code_sous_domaine}', [EtudeProjet::class, 'getFamilles']);
    Route::get('/get-caracteristiques/{idType}', [EtudeProjet::class, 'getCaracteristiques']);
    Route::get('/get-unites/{idCaracteristique}', [EtudeProjet::class, 'getUnites']);

    Route::get('/get-decoupage-niveau/{localite}', [EtudeProjet::class, 'getDecoupageNiveau']);
    Route::get('/get-sous-domaines/{domaineCode}', function ($domaineCode) {
        $sousDomaines = SousDomaine::where('code_groupe_projet', session('projet_selectionne'))
            ->where('code_domaine', $domaineCode) // ← ajout de ce filtre
            ->get()
            ->map(function ($sousDomaine) {
                return [
                    'code_sous_domaine' => $sousDomaine->code_sous_domaine,
                    'lib_sous_domaine' => $sousDomaine->lib_sous_domaine,
                ];
            });

        return response()->json($sousDomaines);
    });


    /*************************ACTEURS *******/
    Route::get('admin/acteurs', [ActeurController::class, 'index'])->name('acteurs.index');
    Route::post('/acteurs', [ActeurController::class, 'store'])->name('acteurs.store');
    Route::put('/acteurs/{id}', [ActeurController::class, 'update'])->name('acteurs.update');
    Route::delete('/acteurs/{id}', [ActeurController::class, 'destroy'])->name('acteurs.destroy');
    Route::patch('admin/acteurs/{id}/restore', [ActeurController::class, 'restore'])->name('acteurs.restore');
    Route::get('/acteurs/{id}/edit', [ActeurController::class, 'edit'])->name('acteurs.edit');
    /************************FONCTION TYPE ACTEUR */
    Route::get('admin/fonction-type-acteur', [FonctionTypeActeurController::class, 'index'])->name('fonction-type-acteur.index');
    Route::post('/fonction-type-acteur', [FonctionTypeActeurController::class, 'store'])->name('fonction-type-acteur.store');
    Route::delete('/fonction-type-acteur/{id}', [FonctionTypeActeurController::class, 'destroy'])->name('fonction-type-acteur.destroy');

    /*************************UTILISATEUR */
    Route::get('admin/utilisateurs', [UtilisateurController::class, 'index'])->name('utilisateurs.index');
    Route::post('/utilisateurs', [UtilisateurController::class, 'store'])->name('utilisateurs.store');
    Route::get('/utilisateurs/{id}', [UtilisateurController::class, 'show'])->name('utilisateurs.show');
    Route::put('/utilisateurs/{id}', [UtilisateurController::class, 'update'])->name('utilisateurs.update');
    Route::post('/utilisateurs/{id}/disable', [UtilisateurController::class, 'disable'])->name('utilisateurs.disable');
    Route::post('/utilisateurs/{id}/restore', [UtilisateurController::class, 'restore'])->name('utilisateurs.restore');

    Route::get('/fonctions-par-type-acteur/{typeActeur}', [UtilisateurController::class, 'getFonctionsByTypeActeur']);

    /*************************PERMISSIONS */
    // Gestion des permissions de rôles
    Route::get('admin/role_permissions', [RolePermissionsController::class, 'index'])->name('role_permissions.index'); // Affichage des permissions
    Route::post('/role_permissions', [RolePermissionsController::class, 'store'])->name('role_permissions.store'); // Création d'une nouvelle permission
    Route::put('/role_permissions/{id}', [RolePermissionsController::class, 'store'])->name('role_permissions.update'); // Mise à jour d'une permission
    Route::delete('/role_permissions/{id}', [RolePermissionsController::class, 'destroy'])->name('role_permissions.destroy'); // Suppression d'une permission

    // Gestion des permissions pour groupes projets
    Route::get('admin/group_project_permissions', [GroupProjectPermissionsController::class, 'index'])->name('group_project_permissions.index'); // Affichage des permissions
    Route::post('/group_project_permissions', [GroupProjectPermissionsController::class, 'store'])->name('group_project_permissions.store'); // Création d'une nouvelle permission
    Route::put('/group_project_permissions/{id}', [GroupProjectPermissionsController::class, 'store'])->name('group_project_permissions.update'); // Mise à jour d'une permission
    Route::delete('/group_project_permissions/{id}', [GroupProjectPermissionsController::class, 'destroy'])->name('group_project_permissions.destroy'); // Suppression d'une permission

    // Groupe Projet

    Route::get('admin/groupeUtilisateur', [GroupProjectPermissionsController::class, 'groupe'])->name('groupes-utilisateurs.index');
    Route::get('/groupeUtilisateur/{id}/edit', [GroupProjectPermissionsController::class, 'edit'])->name('groupes-utilisateurs.edit');
    Route::post('/groupeUtilisateur/store', [GroupProjectPermissionsController::class, 'storeGroupe'])->name('groupes-utilisateurs.store');
    Route::post('/groupeUtilisateur/update/{id}', [GroupProjectPermissionsController::class, 'updateGroupe'])->name('groupes-utilisateurs.update');
    Route::post('admin/groupeUtilisateur/delete/{id}', [GroupProjectPermissionsController::class, 'destroyGroupe'])->name('groupes-utilisateurs.destroy');

});


// MAP
Route::get('/map', function () {
    return view('map');
});
Route::get('/pays/armoirie/base64', function () {
    $user = Auth::user();
    $pays = $user?->paysSelectionne();
    $armoirie = $pays?->armoirie; // ex: "ci.png" ou "Data/armoirie/ci.png"

    if (!$armoirie) {
        return response()->json(['error' => 'Image non disponible.'], 404);
    }

    // Normalise pour n’accepter qu’un nom de fichier (évite ../ etc.)
    $filename = basename($armoirie);

    $baseDir = public_path('Data/armoiries');
    $path = $baseDir . DIRECTORY_SEPARATOR . $filename;

    // Vérif d’existence
    if (!is_file($path)) {
        return response()->json(['error' => 'Fichier non trouvé.'], 404);
    }

    // Lire les octets + MIME
    $bytes = file_get_contents($path);
    if ($bytes === false || $bytes === '') {
        return response()->json(['error' => 'Contenu vide.'], 404);
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $path) ?: 'image/png';
    finfo_close($finfo);

    // (Optionnel) méta pour cache côté client
    $mtime = filemtime($path) ?: time();

    return response()->json([
        'base64Image' => base64_encode($bytes),
        'mime'        => $mime,
        'filename'    => $filename,
        'lastModified'=> $mtime,
    ]);
});
route::get('/geojson', function () {
    $path = public_path('geojson/gadm41_CIV_4.json'); // Mettez à jour le chemin selon votre structure

});


// Routes d'authentification
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');

Route::post('/login/check', [LoginController::class, 'checkUserAssociations'])->name('login.check');
Route::post('/login/select-country', [LoginController::class, 'selectCountry'])->name('login.selectCountry');
Route::post('/login/select-group', [LoginController::class, 'selectGroup'])->name('login.selectGroup');
Route::post('/login/finalize', [LoginController::class, 'finalizeLogin'])->name('login.finalize');
//Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

//Route::post('/connexion', [LoginController::class, 'login'])->name('login.login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Routes des projets sur la page standard
Route::get('/projetDistricts', [ProjetController::class, 'projetDistrict']);

// Routes pour la réinitialisation de mot de passe
Route::get('/password/reset', [LoginController::class, 'showResetForm'])->name('password.request')->middleware('guest')->name('password.request');
Route::post('/forgot-password', [LoginController::class, 'postResetForm'])->middleware('guest')->name('password.email');

Route::get('/reset-password/{token}', [LoginController::class, 'ResetPasswordToken'])->middleware('guest')->name('password.reset');

Route::post('/reset-password', [LoginController::class, 'ResetPassword'])->middleware('guest')->name('password.update');

//Routes changer de mot de passe accueil

//génération du code geojson
Route::get('/test', [AdminController::class, 'test']);

// routes/web.php

// Routes pour la réinitialisation de mot de passe
Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [ResetPasswordController::class, 'resetPassword'])->name('password.update');

Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');

Route::get('/etat/pdf', [EtatController::class, 'generatePDF'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);


Route::get('/{id}/impressions', [InfrastructureController::class, 'print'])->name('infrastructures.printNoConnect');
