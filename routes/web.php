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
use App\Http\Controllers\cloturerProjetController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\EtudeProjet;
use App\Http\Controllers\GanttController;
use App\Http\Controllers\PaysController;
use App\Http\Controllers\ProjetController;
use App\Http\Controllers\SigController;
use App\Http\Controllers\GeoJSONController;
use App\Http\Controllers\Naissance;
use App\Http\Controllers\pibController;
use App\Http\Controllers\ProjectStatusController;
use App\Http\Controllers\RealiseProjetController;
use App\Http\Controllers\representationGraphique;
use App\Http\Controllers\sigAdminController;
use App\Http\Controllers\StatController;
use App\Models\Renforcement;
use Laravel\Ui\Presets\React;
use PasswordResetController as GlobalPasswordResetController;

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
Route::middleware(['auth', 'auth.session'])->group(function () {
    Route::get('admin', [AdminController::class, 'index'])->name('projets.index');
    Route::get('/projets-data', [AdminController::class, 'getProjetData']);
    Route::get('/admin/initSidebar', [AdminController::class, 'initSidebar']);
    // PAYS, DISTRICT, REGIONS, DEPARTEMENTS, SOUS-PREFECTURES, LOCALITES
    Route::get('admin/pays', [PaysController::class, 'pays'])->name('pays');
    Route::post('admin/pays', [PaysController::class, 'storePays'])->name('pays.store');
    Route::get('admin/district', [PaysController::class, 'district'])->name('district');
    Route::get('admin/departement', [PaysController::class, 'departement'])->name('departement');
    Route::get('admin/sous_prefecture', [PaysController::class, 'sous_prefecture'])->name('sous_prefecture');
    Route::get('admin/localite', [PaysController::class, 'localite'])->name('localite');
    Route::get('admin/region', [PaysController::class, 'region'])->name('region');
    Route::post('/check-district-code', [PaysController::class, 'checkDistrictCode']);
    Route::post('/check-region-code', [PaysController::class, 'checkRegionCode']);
    Route::post('/check-pays-code', [PaysController::class, 'checkPaysCode']);
    Route::post('/check-departement-code', [PaysController::class, 'checkDepartementCode']);
    Route::post('/check-localite-code', [PaysController::class, 'checkLocaliteCode']);
    Route::post('/check-sous_prefecture-code', [PaysController::class, 'checkSous_prefectureCode']);
    Route::post('admin/sous_prefecture', [PaysController::class, 'storeSous_prefecture'])->name('sous_prefecture.store');
    Route::post('admin/district', [PaysController::class, 'storeDistrict'])->name('district.store');
    Route::post('admin/departement', [PaysController::class, 'storeDepartement'])->name('departement.store');
    Route::post('admin/localite', [PaysController::class, 'storeLocalite'])->name('localite.store');
    Route::post('admin/region', [PaysController::class, 'storeRegion'])->name('region.store');
    Route::get('admin/district/{code}', [PaysController::class, 'getDistrict'])->name('district.show');
    Route::get('admin/region/{code}', [PaysController::class, 'getRegion'])->name('region.show');
    Route::get('admin/localite/{code}', [PaysController::class, 'getLocalite'])->name('localite.show');
    Route::get('admin/departement/{code}', [PaysController::class, 'getDepartement'])->name('departement.show');
    Route::get('admin/sous_prefecture/{code}', [PaysController::class, 'getSous_prefecture'])->name('sous_prefecture.show');
    Route::delete('admin/district/delete/{code}', [PaysController::class, 'deleteDistrict'])->name('district.delete');
    Route::delete('admin/sous_prefecture/delete/{code}', [PaysController::class, 'deleteSous_prefecture'])->name('sous_prefecture.delete');
    Route::delete('admin/region/delete/{code}', [PaysController::class, 'deleteRegion'])->name('region.delete');
    Route::delete('admin/departement/delete/{code}', [PaysController::class, 'deleteDepartement'])->name('departement.delete');
    Route::delete('admin/localite/delete/{code}', [PaysController::class, 'deleteLocalite'])->name('localite.delete');
    Route::post('admin/district/update', [PaysController::class, 'updateDistrict'])->name('district.update');
    Route::post('admin/region/update', [PaysController::class, 'updateRegion'])->name('region.update');
    Route::post('admin/localite/update', [PaysController::class, 'updateLocalite'])->name('localite.update');
    Route::post('admin/sous_prefecture/update', [PaysController::class, 'updateSous_prefecture'])->name('sous_prefecture.update');
    Route::post('admin/departement/update', [PaysController::class, 'updateDepartement'])->name('departement.update');
    Route::get('admin/get-districts/{pays}', [PaysController::class, 'getDistricts']);
    Route::get('admin/get-regions/{districtId}', [PaysController::class, 'getRegions']);
    Route::get('admin/get-departements/{regionId}', [PaysController::class, 'getDepartements']);
    Route::get('admin/get-sous_prefecture/{departementId}', [PaysController::class, 'getSous_prefectures']);



    /* ***********************  Paramètre généraux *******************************/
    Route::get('admin/statutProjet', [PlateformeController::class, 'statutProjet'])->name('statutProjet');
    Route::get('admin/typeBailleur', [PlateformeController::class, 'typeBailleur'])->name('typeBailleur');
    Route::get('admin/typeEtablissement', [PlateformeController::class, 'typeEtablissement'])->name('typeEtablissement');
    Route::get('admin/typeMateriauxConduite', [PlateformeController::class, 'typeMateriauxConduite'])->name('typeMateriauxConduite');
    Route::get('admin/typeResaux', [PlateformeController::class, 'typeResaux'])->name('typeResaux');
    Route::get('admin/typeStation', [PlateformeController::class, 'typeStation'])->name('typeStation');
    Route::get('admin/typeStockage', [PlateformeController::class, 'typeStockage'])->name('typeStockage');
    Route::get('admin/uniteStockage', [PlateformeController::class, 'uniteStockage'])->name('uniteStockage');
    Route::get('admin/uniteDistance', [PlateformeController::class, 'uniteDistance'])->name('uniteDistance');
    Route::get('admin/uniteMesure', [PlateformeController::class, 'uniteMesure'])->name('uniteMesure');
    Route::get('admin/uniteSurface', [PlateformeController::class, 'uniteSurface'])->name('uniteSurface');
    Route::get('admin/uniteTraitement', [PlateformeController::class, 'uniteTraitement'])->name('uniteTraitement');
    Route::get('admin/uniteVolume', [PlateformeController::class, 'uniteVolume'])->name('uniteVolume');
    Route::get('admin/typeReservoire', [PlateformeController::class, 'typeReservoire'])->name('typeReservoire');
    Route::get('admin/typeInstrument', [PlateformeController::class, 'typeInstrument'])->name('typeInstrument');


    //***************** Ouvrage de transport ************* */
    Route::get('admin/ouvrageTransport', [PlateformeController::class, 'ouvrageTransport'])->name('ouvrageTransport');
    Route::get('admin/ouvrageTransport/{code}', [PlateformeController::class, 'getOuvrageTransport'])->name('ouvrageTransport.show');
    Route::post('admin/ouvrageTransport', [PlateformeController::class, 'storeOuvrageTransport'])->name('ouvrageTransport.store');
    Route::post('admin/ouvrageTransport/update', [PlateformeController::class, 'updateOuvrageTransport'])->name('ouvrageTransport.update');
    Route::delete('admin/ouvrageTransport/delete/{code}', [PlateformeController::class, 'deleteOuvrageTransport'])->name('ouvrageTransport.delete');
    Route::post('/check-ouvrageTransport-code', [PlateformeController::class, 'checkOuvrageTransportCode']);


    //***************** Outils de collecte ************* */
    Route::get('admin/outilsCollecte', [PlateformeController::class, 'outilsCollecte'])->name('outilsCollecte');
    Route::get('admin/outilsCollecte/{code}', [PlateformeController::class, 'getOutilsCollecte'])->name('outilsCollecte.show');
    Route::post('admin/outilsCollecte', [PlateformeController::class, 'storeOutilsCollecte'])->name('outilsCollecte.store');
    Route::post('admin/outilsCollecte/update', [PlateformeController::class, 'updateOutilsCollecte'])->name('outilsCollecte.update');
    Route::delete('admin/outilsCollecte/delete/{code}', [PlateformeController::class, 'deleteOutilsCollecte'])->name('outilsCollecte.delete');
    Route::post('/check-outilsCollecte-code', [PlateformeController::class, 'checkOutilsCollecteCode']);


    //***************** Niveau d'accès aux données ************* */
    Route::get('admin/niveauAccesDonnees', [PlateformeController::class, 'niveauAccesDonnees'])->name('niveauAccesDonnees');
    Route::get('admin/niveauAccesDonnees/{code}', [PlateformeController::class, 'getNiveauAccesDonnees'])->name('niveauAccesDonnees.show');
    Route::post('admin/niveauAccesDonnees', [PlateformeController::class, 'storeNiveauAccesDonnees'])->name('niveauAccesDonnees.store');
    Route::post('admin/niveauAccesDonnees/update', [PlateformeController::class, 'updateNiveauAccesDonnees'])->name('niveauAccesDonnees.update');
    Route::delete('admin/niveauAccesDonnees/delete/{code}', [PlateformeController::class, 'deleteNiveauAccesDonnees'])->name('niveauAccesDonnees.delete');
    Route::post('/check-niveauAccesDonnees-code', [PlateformeController::class, 'checkNiveauAccesDonneesCode']);

    //***************** Matériel de stockage ************* */
    Route::get('admin/materielStockage', [PlateformeController::class, 'materielStockage'])->name('materielStockage');
    Route::get('admin/materielStockage/{code}', [PlateformeController::class, 'getMaterielStockage'])->name('materielStockage.show');
    Route::post('admin/materielStockage', [PlateformeController::class, 'storeMaterielStockage'])->name('materielStockage.store');
    Route::post('admin/materielStockage/update', [PlateformeController::class, 'updateMaterielStockage'])->name('materielStockage.update');
    Route::delete('admin/materielStockage/delete/{code}', [PlateformeController::class, 'deleteMaterielStockage'])->name('materielStockage.delete');
    Route::post('/check-materielStockage-code', [PlateformeController::class, 'checkMaterielStockageCode']);

    //***************** Groupe utilisateur ************* */
    Route::get('admin/groupeUtilisateur', [PlateformeController::class, 'groupeUtilisateur'])->name('groupeUtilisateur');
    Route::get('admin/groupeUtilisateur/{code}', [PlateformeController::class, 'getGroupeUtilisateur'])->name('groupeUtilisateur.show');
    Route::post('admin/groupeUtilisateur', [PlateformeController::class, 'storeGroupeUtilisateur'])->name('groupeUtilisateur.store');
    Route::post('admin/groupeUtilisateur/update', [PlateformeController::class, 'updateGroupeUtilisateur'])->name('groupeUtilisateur.update');
    Route::delete('admin/groupeUtilisateur/delete/{code}', [PlateformeController::class, 'deleteGroupeUtilisateur'])->name('groupeUtilisateur.delete');
    Route::post('/check-groupeUtilisateur-code', [PlateformeController::class, 'checkGroupeUtilisateurCode']);
    Route::get('/admin/get-groupes/{code}', [PlateformeController::class, 'getGroupeUtilisateursByFonction'])->name('groupeUtilisateur.byFonction');

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
    Route::get('admin/familleinfrastructure', [PlateformeController::class, 'familleinfrastructure'])->name('familleinfrastructure');
    Route::get('admin/familleinfrastructure/{code}', [PlateformeController::class, 'getFamilleinfrastructure'])->name('familleinfrastructure.show');
    Route::post('admin/familleinfrastructure', [PlateformeController::class, 'storeFamilleinfrastructure'])->name('familleinfrastructure.store');
    Route::post('admin/familleinfrastructure/update', [PlateformeController::class, 'updateFamilleinfrastructure'])->name('familleinfrastructure.update');
    Route::delete('admin/familleinfrastructure/delete/{code}', [PlateformeController::class, 'deleteFamilleinfrastructure'])->name('familleinfrastructure.delete');
    Route::post('/check-familleinfrastructure-code', [PlateformeController::class, 'checkFamilleinfrastructureCode']);

    //***************** Cour d'eau ************* */
    Route::get('admin/courdeau', [PlateformeController::class, 'courdeau'])->name('courdeau');
    Route::get('admin/courdeau/{code}', [PlateformeController::class, 'getCourDeau'])->name('courdeau.show');
    Route::post('admin/courdeau', [PlateformeController::class, 'storeCourDeau'])->name('courdeau.store');
    Route::post('admin/courdeau/update', [PlateformeController::class, 'updateCourDeau'])->name('courdeau.update');
    Route::delete('admin/courdeau/delete/{code}', [PlateformeController::class, 'deleteCourDeau'])->name('courdeau.delete');
    Route::post('/check-courdeau-code', [PlateformeController::class, 'checkCourDeauCode']);

    //***************** Action à mener ************* */
    Route::get('admin/actionmener', [PlateformeController::class, 'actionMener'])->name('actionMener');
    Route::get('admin/actionmener/{code}', [PlateformeController::class, 'getActionmener'])->name('actionMener.show');
    Route::post('admin/actionmener', [PlateformeController::class, 'storeActionmener'])->name('actionMener.store');
    Route::post('admin/actionmener/update', [PlateformeController::class, 'updateActionmener'])->name('actionMener.update');
    Route::delete('admin/actionmener/delete/{code}', [PlateformeController::class, 'deleteActionmener'])->name('actionMener.delete');
    Route::post('/check-actionmener-code', [PlateformeController::class, 'checkActionmenerCode']);

    //***************** Acquifère ************* */
    Route::get('admin/acquifere', [PlateformeController::class, 'acquifere'])->name('acquifere');
    Route::get('admin/acquifere/{code}', [PlateformeController::class, 'getAcquifere'])->name('acquifere.show');
    Route::post('admin/acquifere', [PlateformeController::class, 'storeAcquifere'])->name('acquifere.store');
    Route::post('admin/acquifere/update', [PlateformeController::class, 'updateAcquifere'])->name('acquifere.update');
    Route::delete('admin/acquifere/delete/{code}', [PlateformeController::class, 'deleteAcquifere'])->name('acquifere.delete');
    Route::post('/check-acquifere-code', [PlateformeController::class, 'checkAcquifereCode']);

    //***************** Bassins ************* */
    Route::get('admin/bassin', [PlateformeController::class, 'bassin'])->name('bassin');
    Route::get('admin/bassin/{code}', [PlateformeController::class, 'getBassin'])->name('bassin.show');
    Route::post('admin/bassin', [PlateformeController::class, 'storeBassin'])->name('bassin.store');
    Route::post('admin/bassin/update', [PlateformeController::class, 'updateBassin'])->name('bassin.update');
    Route::delete('admin/bassin/delete/{code}', [PlateformeController::class, 'deleteBassin'])->name('bassin.delete');
    Route::post('/check-bassin-code', [PlateformeController::class, 'checkBassinCode']);

    //***************** approbation ************* */
    Route::get('admin/approbation', [PlateformeController::class, 'approbation'])->name('approbation');
    Route::post('/storeApprobation', [PlateformeController::class, 'storeApprobation'])->name('approbateur.store');
    Route::delete('/approbation/{id}', [PlateformeController::class, 'deleteApprobation']);
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
    Route::post('admin/domaines/update', [PlateformeController::class, 'updateDomaine'])->name('domaine.update');
    Route::delete('admin/domaine/delete/{code}', [PlateformeController::class, 'deleteDomaine'])->name('domaine.delete');
    Route::post('/check-domaine-code', [PlateformeController::class, 'checkDomaineCode']);


    //***************** sous-domaines ************* */
    Route::post('/check-sous-domaines-code', [PlateformeController::class, 'checkSousDomaineCode']);
    Route::get('admin/sous-domaines', [PlateformeController::class, 'sousDomaines'])->name('sous_domaines');
    Route::get('admin/sous-domaines/{code}', [PlateformeController::class, 'getSousDomaine'])->name('sous_domaines.show');
    Route::post('admin/sous-domaines', [PlateformeController::class, 'storeSousDomaine'])->name('sous_domaines.store');
    Route::post('admin/sous-domaines/update', [PlateformeController::class, 'updateSousDomaine'])->name('sous_domaines.update');
    Route::delete('admin/sous-domaines/delete/{code}', [PlateformeController::class, 'deleteSousDomaine'])->name('sous_domaines.delete');



    /* ***********************  Paramètre spécifiques *******************************/

    //***************** AGENCES ************* */
    Route::get('admin/agences', [PlateformeController::class, 'agences'])->name('agences');
    Route::get('admin/agences/{code}', [PlateformeController::class, 'getAgence'])->name('agence.show');
    Route::delete('admin/agences/delete/{code}', [PlateformeController::class, 'deleteAgence'])->name('agence.delete');
    Route::post('/check-agence-code', [PlateformeController::class, 'checkAgenceCode']);
    Route::post('admin/agence', [PlateformeController::class, 'storeAgence'])->name('agence.store');
    Route::post('admin/agence/update', [PlateformeController::class, 'updateAgence'])->name('agence.update');

    //***************** BAILLEURS ************* */
    Route::get('admin/bailleurs', [PlateformeController::class, 'bailleurs'])->name('bailleurs');
    Route::get('/admin/bailleur/{code}', [PlateformeController::class, 'getBailleur'])->name('bailleur.show');
    Route::delete('admin/bailleur/delete/{code}', [PlateformeController::class, 'deleteBailleur'])->name('bailleur.delete');
    Route::post('/check-bailleur-code', [PlateformeController::class, 'checkBailleurCode']);
    Route::post('admin/bailleur', [PlateformeController::class, 'storeBailleur'])->name('bailleur.store');
    Route::post('admin/bailleur/update', [PlateformeController::class, 'updateBailleur'])->name('bailleur.update');

    //***************** ETABLISSEMENTS ************* */
    Route::get('admin/etablissements', [PlateformeController::class, 'etablissements'])->name('etablissements');
    Route::get('admin/etablissement/{code}', [PlateformeController::class, 'getEtablissement'])->name('etablissement.show');
    Route::delete('admin/etablissement/delete/{code}', [PlateformeController::class, 'deleteEtablissement'])->name('etablissement.delete');
    Route::post('/check-etablissement-code', [PlateformeController::class, 'checkEtablissementCode']);
    Route::post('admin/etablissement', [PlateformeController::class, 'storeEtablissement'])->name('etablissement.store');
    Route::post('admin/etablissement/update', [PlateformeController::class, 'updateEtablissement'])->name('etablissement.update');
    Route::get('admin/get-niveaux/{typeId}', [PlateformeController::class, 'getNiveaux']);

    //***************** BENEFICIAIRES ************* */
    Route::get('admin/beneficiaires', [PlateformeController::class, 'beneficiaires'])->name('beneficiaires');
    Route::get('admin/etablissements', [PlateformeController::class, 'etablissements'])->name('etablissements');
    Route::get('admin/ministeres', [PlateformeController::class, 'ministeres'])->name('ministeres');


    //***************** PROJETS ************* */
    Route::get('admin/projet', [ProjetController::class, 'projet'])->name('projet');
    Route::get('/get-regions/{districtCode}', [ProjetController::class, 'getRegions']);
    Route::get('admin/projets/liste', [ProjetController::class, 'Projets'])->name('projet.liste');
    Route::get('/get-sous-domaines/{domaineCode}', [ProjetController::class, 'getSousDomaines']);
    Route::get('admin/get-cours_eau/{eauId}', [ProjetController::class, 'getCours_eau']);
    Route::get('admin/get-insfrastructures/{domaineId}', [ProjetController::class, 'getInsfrastructures']);
    Route::get('/getBeneficiaires/{type}/{code}', 'ProjetController@getBeneficiaires')->name('getBeneficiaires');
    Route::get('/verifier_code_projet', [ProjetController::class, 'verifierCodeProjet']);
    Route::post('/recup', [ProjetController::class, 'votreFonction'])->name('maRoute');
    Route::post('/enregistrer-formulaire', [ProjetController::class, 'store'])->name('enregistrer.formulaire');
    Route::get('/projet/getTable', [ProjetController::class, 'getTable']);
    Route::get('admin/editionProjet', [ProjetController::class, 'editionProjet']);

        /*****************ETUDE DE PROJET**************** */
        Route::get('admin/naissanceProjet',[EtudeProjet::class, 'createNaissance'])->name('project.create');
        Route::post('/projects/store', [EtudeProjet::class, 'storeNaissance'])->name('project.store');
        /***********************VALIDATION***************** */

        Route::get('admin/validationProjet', [EtudeProjet::class, 'validatet'])->name('projects.validate');
        Route::get('/planning/show', [EtudeProjet::class, 'showPlanning'])->name('planning.show');

        /********************PLANIFICATION***************** */
        Route::get('admin/planifierProjet', [GanttController::class, 'index']);
        /*Route::post('/gantt/task', [GanttController::class, 'storeTask'])->name('gantt.task.store');
        Route::put('/gantt/task/{id}', [GanttController::class, 'updateTask']);
        Route::delete('/gantt/task/{id}', [GanttController::class, 'deleteTask']);
        Route::post('/gantt/link', [GanttController::class, 'storeLink']);
        Route::put('/gantt/link/{id}', [GanttController::class, 'updateLink']);
        Route::delete('/gantt/link/{id}', [GanttController::class, 'deleteLink']);
        Route::post('/gantt/save', [GanttController::class, 'saveGantt'])->name('gantt.save');
        Route::get('/gantt/load/{project_id}', [GanttController::class, 'loadData']);
        Route::get('/gantt/check/{projectId}', [GanttController::class, 'checkProjectData']);
        */
        Route::get('/gantt/load/{projectId}', [GanttController::class, 'load']);
        Route::post('/gantt/save/{projectId}', [GanttController::class, 'save']);
        Route::delete('/gantt/delete/{projectId}', [GanttController::class, 'delete']);
        /********************RENFORCEMENT***************** */
        Route::get('admin/renforcementProjet', [EtudeProjet::class, 'renfo'])->name('renforcements.index');
        Route::delete('/renforcementDelete/{id}', [EtudeProjet::class, 'deleteRenforcement']);
        Route::put('/renforcements/{code}', [EtudeProjet::class, 'update'])->name('renforcements.update');
        Route::post('admin/renforcementProjet', [EtudeProjet::class, 'store'])->name('renforcements.store');

        /****************************ACTIVITE CONNEXE******************** */
        Route::get('admin/activiteConnexeProjet',[EtudeProjet::class, 'activite'])->name('activite.index');
        Route::post('admin/activiteConnexeProjet', [EtudeProjet::class, 'storeConnexe'])->name('travaux_connexes.store');
        Route::delete('/activiteConnexeProjet/{id}', [EtudeProjet::class, 'destroyConnexe'])->name('travaux_connexes.destroy');
        
        /**************************** REATTRIBUTION DE PROJET ******************************/
    Route::get('admin/reatributionProjet', [ProjetController::class, 'reatributionProjet'])->name('reattribution.index');
    Route::put('/reattribution', [ProjetController::class, 'storereat'])->name('reattribution.store');
    Route::put('/reattribution/{id}', [ProjetController::class, 'updatereat'])->name('reattribution.update');
    Route::delete('/reattribution/{id}', [ProjetController::class, 'destroyreat'])->name('reattribution.destroy');
    Route::get('/getProjectDetails/{codeProjet}', [ProjetController::class, 'getProjectDetails']);


    /**************************** GESTION DES EDITIONS **********************************/
        /**************************** GESTION DES ANNEXE 1 ******************************/
        Route::get('admin/projet/edition/InfosPrincip', [AnnexeController::class, 'InfosPrincip'])->name('projet.InfosPrincip');
        Route::get('admin/projet/edition/InfosSecond', [AnnexeController::class, 'InfosSecond'])->name('projet.InfosSecond');
        Route::get('admin/projet/edition/InfosTert', [AnnexeController::class, 'InfosTert'])->name('projet.InfosTert');

        /**************************** GESTION DES ANNEXE 2 ******************************/
        Route::get('admin/projet/edition/ficheCollecte', [AnnexeController::class, 'FicheCollecte'])->name('projet.InfosTert');
        Route::get('admin/projet/edition/ficheImprimer/{code}',[AnnexeController::class, 'FicheCollecteImprimer'])->name('Annexe2.FicheCollecte');
        Route::get('/getProjectDetails', [AnnexeController::class, 'getProjectDetails']);

    //***************** REALISATION ************* */
    Route::get('admin/realise/PramatreRealise', [RealiseProjetController::class, 'PramatreRealise']);
    Route::get('admin/realise', [RealiseProjetController::class, 'realise']);
    Route::post('/get-project-details', [RealiseProjetController::class, 'getProjectDetails'])->name('get.project.details');
    Route::post('/fetch-project-details', [RealiseProjetController::class, 'fetchDetails'])->name('fetch.project.details');
    Route::get('/admin/realise', [RealiseProjetController::class, 'VoirListe']);
    Route::get('/fetchProjectDetails', [RealiseProjetController::class, 'fetchProjectDetails']);
    Route::get('/getProjetData', [RealiseProjetController::class, 'getProjetData']);
    Route::get('/getBeneficiaires', [RealiseProjetController::class, 'getBeneficiaires']);
    Route::get('/getNumeroOrdre', [RealiseProjetController::class, 'getNumeroOrdre']);
    Route::get('/getFamilleInfrastructure', [RealiseProjetController::class, 'getFamilleInfrastructure']);
    Route::post('/enregistrer-caracteristiques', [RealiseProjetController::class, 'storeCaracteristiques'])->name('enregistrer.Caracteristiques');
    Route::post('/enregistrer-datesEffectives', [RealiseProjetController::class, 'enregistrerDatesEffectives'])->name('enregistrer-dates-effectives');
    //Route::get('/getDataDateEffective', [RealiseProjetController::class, 'obtenirDonneesProjet'])->name('obtenir-donnees-projet');
    Route::get('admin/etatAvancement', [RealiseProjetController::class, 'etatAvancement']);
    Route::post('/admin/etatAvancement', [RealiseProjetController::class,'enregistrerBeneficiaires'])->name('enregistrer.beneficiaires');
    Route::get('/recuperer-beneficiaires', [RealiseProjetController::class, 'recupererBeneficiaires'])->name('recuperer-beneficiaires');

    Route::get('/check-code-projet', [RealiseProjetController::class, 'checkCodeProjet']);
    Route::post('/enregistrer-niveau-avancement', [RealiseProjetController::class, 'enregistrerNiveauAvancement'])->name('enregistrer.niveauAvancement');
    Route::post('/enregistrer-dateseffectives', [RealiseProjetController::class, 'enregistrerDateFinEffective'])->name('enregistrer.dateFinEffective');
    Route::get('/get-donnees-formulaire', [RealiseProjetController::class, 'getDonneesPourFormulaire'])->name('get.donnees.formulaire');



    Route::get('/get-project-status/{id}', [ProjectStatusController::class, 'getProjectStatus']);    //***************** GESTION FINANCIERE ************* */
    Route::get('admin/graphique', [representationGraphique::class, 'graphique']);
    Route::get('admin/pib', [pibController::class, 'pib']);


    //********************CLOTURER **************************//
    Route::get('admin/cloture', [cloturerProjetController::class, 'cloturer']);
    Route::post('/cloturer-projet', [cloturerProjetController::class, 'cloturerProjet'])->name('cloturer_projet');
    //***************** GESTION SIG ************* */
    Route::get('admin/carte', [sigAdminController::class, 'carte']);

    Route::get('/filter-map', [GeoJSONController::class, 'filter'])->name('filter.map');


    Route::get('/get-projet-data', 'ProjetController@getProjetData');

    Route::get('/dash', function () {
        return view('dash');
    })->name('dash');


    /**************************** GESTION DES STATISTIQUES **********************************/
    Route::get('admin/stat_nombre_projet', [StatController::class, 'statNombreProjet']);
    Route::get('admin/stat-finance', [StatController::class, 'statFinance']);

    Route::get('/nombreProjetLien', [StatController::class, 'statNombreData'])->name('nombre.data');
    Route::get('/stat-finance_projet/data', [StatController::class, 'statFinanceData'])->name('finance.data');
    /**************************** GESTION DES UTILISATEURS **********************************/
    Route::get('admin/personnel', [UserController::class, 'personnel'])->name('users.personnel');
    Route::get('admin/personnel/create', [UserController::class, 'createPersonnel'])->name('personnel.create');
    Route::post('admin/personnel/store', [UserController::class, 'storePersonnel'])->name('personnel.store');
    Route::delete('/admin/personnel/{code_personnel}', [UserController::class, 'destroy'])->name('utilisateurs.destroy');

    Route::get('admin/personnel/details-personne/{personneId}', [UserController::class, 'detailsPersonne'])->name('personnel.details');
    Route::get('admin/personnel/get-personne/{personneId}', [UserController::class, 'getPersonne'])->name('personne.updateForm');
    Route::post('admin/personnel/update/{personnelId}', [UserController::class, 'updatePersonne'])->name('personne.update');
    Route::get('/check-email-personne', [UserController::class, 'checkEmail_personne']);
    Route::get('admin/get-personne-email/{personnelId}', [UserController::class, 'getPersonneInfos'])->name('personne.get');
    Route::post('/SousDomaine_Domaine-ajax', [UserController::class, 'getDomaines']);

    Route::get('admin/users', [UserController::class, 'users'])->name('users.users');
    Route::get('admin/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/fetch-sous-domaine', [UserController::class, 'fetchSousDomaine'])->name('fetch.sous_domaine');
    Route::post('/admin/users/store', [UserController::class, 'store'])->name('users.store');
    Route::get('/check-username', [UserController::class, 'checkUsername']);
    Route::get('/check-email', [UserController::class, 'checkEmail']);
    Route::get('admin/users/get-user/{userId}', [UserController::class, 'getUser'])->name('users.get');
    Route::get('/admin/users/details-user/{userId}', [UserController::class, 'detailsUser'])->name('users.details');
    Route::post('/admin/users/update/{userId}', [UserController::class, 'update'])->name('users.update');
    Route::post('/admin/users/details-user/{userId}', [UserController::class, 'update_auth'])->name('users.update_auth');
    Route::post('/change-password', [UserController::class, 'changePassword'])->name('password.change');
    Route::delete('/admin/delete-user/{id}', [UserController::class, 'deleteUser'])->name('users.delete');
    Route::get('/getIndicatif/{paysId}', [UserController::class, 'getIndicatif'])->name('getIndicatif');

    Route::get('RecupererDonneesUser/{userId}', [ProjetController::class, 'getDonneUser'])->name('GetDonneeUser');
    /**************************** GESTION DES HABILITATIONS **********************************/
     Route::get('/admin/habilitations', [RoleAssignmentController::class, 'habilitations'])->name('habilitations.index');
     Route::get('/admin/role-assignment', [RoleAssignmentController::class, 'index'])->name('role-assignment.index');
     Route::post('/admin/role-assignment/assign', [RoleAssignmentController::class, 'assignRoles'])->name('role-assignment.assign');
     Route::get('/get-role-permissions/{roleId}', [RoleAssignmentController::class, 'getRolePermissions']);


     Route::get('/admin/rubriques', [RoleAssignmentController::class, 'rubriques'])->name('rubriques.index');
     Route::post('/admin/rubrique/store', [RoleAssignmentController::class, 'storeRubrique'])->name('rubrique.store');
     Route::get('/admin/rubrique/get-rubrique/{id}', [RoleAssignmentController::class, 'getRubrique'])->name('rubrique.updateForm');
     Route::post('/admin/rubrique/update', [RoleAssignmentController::class, 'updateRubrique'])->name('rubrique.update');
     Route::delete('admin/rubrique/delete/{code}', [RoleAssignmentController::class, 'deleteRubrique'])->name('rubrique.delete');
     Route::get('/get-sous-menus/{rubrique}', [RoleAssignmentController::class, 'getSousMenus']);


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





    });




// MAP
Route::get('/map', function () {
    return view('map');
});
Route::get('/getBase64Image', function () {
    $imagePath = public_path('betsa/assets/images/ehaImages/armoirie.png');
    $imageData = file_get_contents($imagePath);
    $base64Image = base64_encode($imageData);

    return response()->json(['base64Image' => $base64Image]);
});

route::get('/geojson', function () {
    $path = public_path('geojson/gadm41_CIV_4.json'); // Mettez à jour le chemin selon votre structure

});


// Routes d'authentification
Route::get('/connexion', [LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/connexion', [LoginController::class, 'login'])->name('login.login');
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
Route::get('/sig', [GeoJSONController::class, 'showSIG'])->name('carte.sig');
Route::get('/filter-maps', [GeoJSONController::class, 'filter'])->name('filter.maps');
Route::get('/filtered-data', [GeoJSONController::class, 'showFilteredData'])->name('filtered.data');
Route::get('test', [AdminController::class, 'test']);
