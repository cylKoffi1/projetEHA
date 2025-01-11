<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Pays;
use App\Models\GroupeProjetPaysUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LoginController extends Controller
{
    /**
     * Affiche le formulaire de connexion.
     */
    public function showLoginForm()
    {
        Log::info('Affichage du formulaire de connexion.');
        return view('auth.connexion');
    }

    /**
     * Vérifie les informations d'identification (email et mot de passe).
     */
    public function checkUserAssociations(Request $request)
    {
        Log::info('Début de la vérification des identifiants.', ['email' => $request->email]);

        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            Log::info('Connexion réussie.', ['user_id' => $user->acteur_id]);

            if (!$user->is_active) {
                Log::warning('Compte désactivé.', ['user_id' => $user->acteur_id]);
                Auth::logout();
                return response()->json(['error' => 'Votre compte est désactivé.'], 403);
            }

            // Récupérer les pays associés à l'utilisateur
            $pays = GroupeProjetPaysUser::where('user_id', $user->acteur_id)
            ->join('pays', 'groupe_projet_pays_user.pays_code', '=', 'pays.alpha3')
            ->distinct()
            ->get(['pays.alpha3', 'pays.nom_fr_fr']);

            Log::info('Pays associés récupérés.', ['count' => $pays->count()]);

            if ($pays->count() >= 1) {
                Log::info('Utilisateur associé à plusieurs pays.', ['user_id' => $user->acteur_id]);
                session(['step' => 'choose_country']);
                return response()->json(['step' => 'choose_country', 'data' => $pays]);
            }

            if ($pays->count() === 1) {
                session(['pays_selectionne' => $pays->first()]);
                Log::info('Utilisateur associé à un seul pays.', ['pays_code' => $pays->first()]);

                return $this->handleGroupSelection($user, $pays->first());
            }

            Log::error('Aucun pays associé à l\'utilisateur.', ['user_id' => $user->acteur_id]);
            Auth::logout();
            return response()->json(['error' => 'Vous n\'êtes associé à aucun pays.'], 403);
        }

        Log::error('Échec de la connexion.', ['email' => $request->email]);
        return response()->json(['error' => 'Identifiants incorrects.'], 401);
    }

    /**
     * Gère la sélection des groupes projets pour un utilisateur dans un pays.
     */
    private function handleGroupSelection($user, $paysCode)
    {
        $groupes = GroupeProjetPaysUser::where('user_id', $user->acteur_id)
            ->where('pays_code', $paysCode)
            ->with('groupeProjet')
            ->get();
            Log::info('Groupes projets récupérés :', $groupes->toArray());
        Log::info('Projets récupérés après sélection du pays.', ['count' => $groupes->count()]);

        if ($groupes->count() >= 1) {
            session(['step' => 'choose_group']);
            return response()->json(['step' => 'choose_group', 'data' => $groupes]);
        }

        if ($groupes->count() === 0) {
            Log::warning('Aucun groupe projet disponible pour l\'utilisateur dans ce pays.', ['pays_code' => $paysCode]);
            Auth::logout();
            return response()->json(['error' => 'Vous n\'êtes associé à aucun groupe projet dans ce pays.'], 403);
        }

        session(['projet_selectionne' => $groupes->first()->groupe_projet_id]);

        session(['step' => 'finalize']);
        return response()->json(['step' => 'finalize']);
    }

    /**
     * Enregistre le choix du pays.
     */
    public function selectCountry(Request $request)
    {
        if (!Auth::check()) {
            Log::error('Utilisateur non authentifié lors de la sélection du pays.');
            return response()->json(['error' => 'Votre session a expiré. Veuillez vous reconnecter.'], 401);
        }

        $request->validate(['pays_code' => 'required|string']);

        session(['pays_selectionne' => $request->pays_code]);
        Log::info('Pays sélectionné.', ['pays_code' => $request->pays_code]);

        $user = Auth::user();
        return $this->handleGroupSelection($user, $request->pays_code);
    }

    /**
     * Enregistre le choix du groupe projet.
     */
    public function selectGroup(Request $request)
    {
        if (!Auth::check()) {
            Log::error('Utilisateur non authentifié lors de la sélection du groupe projet.');
            return response()->json(['error' => 'Votre session a expiré. Veuillez vous reconnecter.'], 401);
        }

        $request->validate(['projet_id' => 'required|exists:groupe_projet_pays_user,groupe_projet_id']);

        session(['projet_selectionne' => $request->projet_id]);
        session(['step' => 'finalize']);
        Log::info('Groupe projet sélectionné.', ['projet_id' => $request->projet_id]);

        return response()->json(['step' => 'finalize']);
    }

    /**
     * Finalise la connexion.
     */
    public function finalizeLogin(Request $request)
    {
        if (session('step') !== 'finalize') {
            Log::warning('Tentative d\'accès non autorisée à la page admin.');
            return redirect()->route('login')->with('error', 'Veuillez compléter toutes les étapes.');
        }

        Log::info('Connexion finalisée. Redirection vers la page admin.');
        session()->forget('step');
        return redirect()->intended('admin')->with('success', 'Connexion réussie.');
    }

    /**
     * Affiche la page d'administration.
     */
    public function adminDashboard(Request $request)
    {
        if (!Auth::check() || session('step') !== 'finalize') {
            Log::warning('Accès non autorisé à la page admin. Redirection vers la page de connexion.');
            return redirect()->route('pays',['ecran' => $request->input('ecran_id')])->with('error', 'Accès interdit. Veuillez vous reconnecter.');
        }

        Log::info('Affichage de la page admin.');
        $pays = Pays::all();
        return view('layouts.header', [
            'ecran' => $request->input('ecran_id'),
            'pays_selectionne' => session('pays_selectionne'),
            'projet_selectionne' => session('projet_selectionne'),
            'pays'=> $pays
        ]);
    }

    /**
     * Déconnecte l'utilisateur.
     */
    public function logout(Request $request)
    {
        Log::info('Déconnexion de l\'utilisateur.', ['user_id' => Auth::id()]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Log::info('Déconnexion réussie.');
        return redirect()->route('login');
    }
    /**
     *
     *
     **/

     public function getGroupsByCountry(Request $request)
     {
         $request->validate(['pays_code' => 'required|string']);

         $user = Auth::user();
         $groupes = GroupeProjetPaysUser::where('user_id', $user->acteur_id)
             ->where('pays_code', $request->pays_code)
             ->with('groupeProjet')
             ->get();

         return response()->json($groupes);
     }

     public function changeGroup(Request $request)
     {
         $request->validate([
             'pays_code' => 'required|string',
             'projet_id' => 'required|exists:groupe_projet_pays_user,groupe_projet_id'
         ]);

         session([
             'pays_selectionne' => $request->pays_code,
             'projet_selectionne' => $request->projet_id
         ]);

         return response()->json(['success' => true]);
     }


}
