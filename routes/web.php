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
use App\Http\Controllers\EtatController;
use App\Http\Controllers\EtudeProjet;
use App\Http\Controllers\GanttController;
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
use App\Http\Controllers\StatController;
use App\Http\Controllers\UtilisateurController;
use App\Models\Domaine;
use App\Models\EtudeProject;
use App\Models\LocalitesPays;
use App\Models\Renforcement;
use App\Models\SousDomaine;
use App\Models\Utilisateur;
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

    
    // PAYS, DISTRICT, REGIONS, DEPARTEMENTS, SOUS-PREFECTURES, LOCALITES
    Route::get('admin/pays', [PaysController::class, 'pays'])->name('pays');
    // Route pour afficher le formulaire d'édition (GET)
    Route::get('/pays/{id}/edit', [PaysController::class, 'edit']);
    Route::put('/pays/{id}', [PaysController::class, 'update'])->name('pays.update');
    Route::post('/pays', [PaysController::class, 'storePays'])->name('pays.store');
    Route::delete('/pays/{id}', [PaysController::class, 'deletePays'])->name('pays.destroy');
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
    Route::get('admin/familleinfrastructure', [PlateformeController::class, 'familleinfrastructure'])->name('parGeneraux.familleinfrastructure');
    Route::get('admin/familleinfrastructure/{code}', [PlateformeController::class, 'getFamilleinfrastructure'])->name('familleinfrastructure.show');
    Route::post('admin/familleinfrastructure', [PlateformeController::class, 'storeFamilleinfrastructure'])->name('familleinfrastructure.store');
    Route::post('/familleinfrastructure/update', [PlateformeController::class, 'updateFamilleInfrastructure'])->name('familleinfrastructure.update');
    Route::delete('/familleinfrastructure/delete/{id}', [PlateformeController::class, 'deleteFamilleInfrastructure'])->name('familleinfrastructure.delete');
    Route::post('/check-familleinfrastructure-code', [PlateformeController::class, 'checkFamilleinfrastructureCode']);
    Route::post('/familleinfrastructure/caracteristiques', [PlateformeController::class, 'storeCaracteristiquesFamille'])->name('familleinfrastructure.caracteristiques.store');
    Route::get('/famille/{id}/caracteristiques', [PlateformeController::class, 'getCaracteristiquesFamille']);
    Route::delete('/famille/caracteristique/{famille_id}/{caracteristique_id}', [PlateformeController::class, 'supprimerCaracteristiqueFamille']);

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

       // Page principale
        Route::get('admin/CaractTypeUnite', [PlateformeController::class, 'CaractTypeUniteIndex'])->name('CaractTypeUnite');

        // Type de Caractéristique
        Route::post('/type-caracteristique/store', [PlateformeController::class, 'storeTypeCaracteristique'])->name('type-caracteristique.store');
        Route::post('/type-caracteristique/update', [PlateformeController::class, 'updateTypeCaracteristique'])->name('type-caracteristique.update');
        Route::delete('/type-caracteristique/delete/{id}', [PlateformeController::class, 'deleteTypeCaracteristique'])->name('type-caracteristique.delete');

        // Caractéristique
        Route::post('/caracteristique/store', [PlateformeController::class, 'storeCaracteristique'])->name('caracteristique.store');
        Route::post('/caracteristique/update', [PlateformeController::class, 'updateCaracteristique'])->name('caracteristique.update');
        Route::delete('/caracteristique/delete/{id}', [PlateformeController::class, 'deleteCaracteristique'])->name('caracteristique.delete');

        // Unité
        Route::post('/unite/store', [PlateformeController::class, 'storeUnite'])->name('unite.store');
        Route::post('/unite/update', [PlateformeController::class, 'updateUnite'])->name('unite.update');
        Route::delete('/unite/delete/{id}', [PlateformeController::class, 'deleteUnite'])->name('unite.delete');

    //***************** PROJETS ************* */
    Route::get('admin/projet', [ProjetController::class, 'projet'])->name('projet');
    Route::get('admin/projets/liste', [ProjetController::class, 'Projets'])->name('projet.liste');
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
        /*****************ETUDE DE PROJET**************** */
        Route::get('admin/naissanceProjet',[EtudeProjet::class, 'createNaissance'])->name('project.create');
        Route::get('/pays/{alpha3}/niveaux', [EtudeProjet::class, 'getNiveauxAdministratifs']);
        Route::get('/pays/{alpha3}/niveau/{niveau}/localites', [EtudeProjet::class, 'getLocalitesByNiveau']);
        Route::get('/get-latest-project-number/{location}/{category}/{typeFinancement}', [EtudeProjet::class, 'getLatestProjectNumber']);
        Route::get('admin/modeliser', [EtudeProjet::class, 'modelisation']);
        Route::post('/contrats/chef/update', [ProjetController::class, 'changerChefUpdate'])->name('contrats.chef.update');

                /*******************SAUVEGARDE DE DEMANDE DE PROJET */
                Route::post('/projets/temp/save-step1', [EtudeProjet::class, 'saveStep1'])->name('projets.temp.save.step1');
                Route::post('/projets/temp/save-step2', [EtudeProjet::class, 'saveStep2'])->name('projets.temp.save.step2');
                Route::post('/projets/temp/save-step3', [EtudeProjet::class, 'saveStep3'])->name('projets.temp.save.step3');
                Route::post('/projets/temp/save-step4', [EtudeProjet::class, 'saveStep4'])->name('projets.temp.save.step4');
                Route::post('/projets/temp/save-step5', [EtudeProjet::class, 'saveStep5'])->name('projets.temp.save.step5');
                Route::post('/projets/temp/save-step6', [EtudeProjet::class, 'saveStep6'])->name('projets.temp.save.step6');
                Route::post('/projets/temp/save-step7', [EtudeProjet::class, 'saveStep7'])->name('projets.temp.save.step7');
                Route::post('/projets/finaliser', [EtudeProjet::class, 'finaliserProjet'])->name('projets.finaliser');
                Route::delete('/projets/abort', [EtudeProjet::class, 'abortProjet'])->name('projets.abort');

        /***********************VALIDATION***************** */

            Route::get('admin/validationProjetss', [EtudeProjet::class, 'validation'])->name('projects.validate');
            Route::get('/planning/show', [EtudeProjet::class, 'showPlanning'])->name('planning.show');
            Route::post('/planning/{id}/approve', [EtudeProjet::class, 'approve'])->name('projects.approve');
           
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
            Route::get('admin/renforcementProjet', [EtudeProjet::class, 'renfo'])->name('renforcements.index');
            Route::post('/renforcementProjet/store', [EtudeProjet::class, 'storerenfo'])->name('renforcements.store');
            Route::put('/renforcementProjet/update/{code}', [EtudeProjet::class, 'updaterenfo'])->name('renforcements.update');
            Route::delete('/renforcementProjet/delete/{code}', [EtudeProjet::class, 'destroyrenfo'])->name('renforcements.destroy');
        /****************************ACTIVITE CONNEXE******************** */
            Route::get('admin/activiteConnexeProjet',[EtudeProjet::class, 'activite'])->name('activite.index');
            Route::post('admin/activiteConnexeProjet', [EtudeProjet::class, 'storeConnexe'])->name('travaux_connexes.store');
            Route::delete('/activiteDelete/{id}', [EtudeProjet::class, 'deleteActivite']);
            Route::put('/activite/{id}', [EtudeProjet::class, 'updateConnexe'])->name('tavaux_connexes.update');

        /**************************** REATTRIBUTION DE PROJET ******************************/
            Route::get('admin/reatributionProjet', [ProjetController::class, 'reatributionProjet'])->name('maitre_ouvrage.index');
            Route::get('/get-execution-by-projet/{code_projet}', [ProjetController::class, 'getExecutionByProjet']);
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
    Route::get('/admin/realise', [RealiseProjetController::class, 'VoirListe']);
    Route::get('/fetchProjectDetails', [RealiseProjetController::class, 'fetchProjectDetails']);
    Route::get('/getProjetData', [RealiseProjetController::class, 'getProjetData']);
    Route::get('/getBeneficiaires', [RealiseProjetController::class, 'getBeneficiaires']);
    Route::get('/getNumeroOrdre', [RealiseProjetController::class, 'getNumeroOrdre']);
    Route::get('/getFamilleInfrastructure', [RealiseProjetController::class, 'getFamilleInfrastructure']);
    Route::get('/getInfrastructuresByProjet', [RealiseProjetController::class, 'getInfrastructuresByProjet']);
    Route::get('/get-familles-by-projet', [RealiseProjetController::class, 'getFamillesByProjet']);

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
    // Finalisation partielle d'une infrastru
    Route::post('/projet/finaliser-partiel', [RealiseProjetController::class, 'finaliserPartiel'])->name('finaliser.partiel');
    // Finalisation totale d'un projet
    Route::post('/projet/finaliser', [RealiseProjetController::class, 'finaliserProjet'])->name('finaliser.projet');
    Route::get('/verifier-projet-finalisable', [RealiseProjetController::class, 'verifierProjetFinalisable'])->name('verifier.projet.finalisable');

    Route::get('/get-project-status/{id}', [ProjectStatusController::class, 'getProjectStatus']);    //***************** GESTION FINANCIERE ************* */
    Route::get('admin/graphique', [representationGraphique::class, 'graphique']);
    Route::get('admin/pib', [pibController::class, 'pib']);
    Route::post('admin/pib/store', [pibController::class, 'store'])->name('pib.store');
    Route::put('admin/pib/update/{id}', [pibController::class, 'update'])->name('pib.update');
    Route::delete('admin/pib/destroy/{id}', [pibController::class, 'destroy'])->name('pib.destroy');


    //********************CLOTURER **************************//
    Route::get('admin/cloture', [cloturerProjetController::class, 'cloturer']);
    Route::post('/cloturer-projet', [cloturerProjetController::class, 'cloturerProjet'])->name('cloturer_projet');
    //***************** GESTION SIG ************* */
    Route::get('admin/carte', [sigAdminController::class, 'carte']);
    Route::get('admin/autresRequetes', [InfrastructureMapController::class, 'showMap'])->name('infrastructures.map');
    Route::get('/api/infrastructures/geojson', [InfrastructureMapController::class, 'getInfrastructuresGeoJson']);
    Route::get('/api/infrastructures/familles-colors', [InfrastructureMapController::class, 'getFamillesColors']);
    
    //Route::get('admin/autresRequetes', [sigAdminController::class, 'Autrecarte']);
    Route::get('/filtre-options', [sigAdminController::class, 'getFiltreOptions']);

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

    Route::prefix('admin/infrastructures')->group(function () {
    Route::get('/{id}/impression', [InfrastructureController::class, 'print'])->name('infrastructures.print');
    Route::get('/infrastructures/print', [InfrastructureController::class, 'imprimer'])->name('infrastructures.imprimer');
    // Historique
    Route::get('/{id}/historique', [InfrastructureController::class, 'historique'])
    ->name('infrastructures.historique');
    
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




     Route::post('/get-groups-by-country', [LoginController::class, 'getGroupsByCountry'])->name('login.getGroupsByCountry');
     Route::post('/change-group', [LoginController::class, 'changeGroup'])->name('login.changeGroup');

    Route::get('/get-progress/{key}', function ($key) {
        return response()->json(['progress' => Cache::get($key, 0)]);
    });







    Route::get('/notifications', function () {
        return view('notifications');
    })->name('notifications');



    /*************************TYPE ACTEURS */
    Route::get('admin/type-acteurs', [TypeActeurController::class, 'index'])->name('type-acteurs.index');

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
Route::get('/getBase64Image', function () {
    $user = Auth::user();
    $pays = $user?->paysSelectionne();
    $armoirie = $pays?->armoirie;

    if (!$armoirie) {
        return response()->json(['error' => 'Image non disponible.'], 404);
    }

    $imagePath = public_path($armoirie);

    if (!file_exists($imagePath)) {
        return response()->json(['error' => 'Fichier non trouvé.'], 404);
    }

    $imageData = file_get_contents($imagePath);
    $base64Image = base64_encode($imageData);

    return response()->json(['base64Image' => $base64Image]);
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


