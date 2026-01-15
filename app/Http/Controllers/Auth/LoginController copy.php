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
use Illuminate\Support\Facades\Hash;

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
     * Gère la sélection des groupes projets pour un utilisateur dans un pays.
     */
    private function handleGroupSelection($user, $paysCode)
    {
        $groupes = GroupeProjetPaysUser::where('user_id', $user->acteur_id)
            ->where('pays_code', $paysCode)
            ->with('groupeProjet')
            ->get();

        if ($groupes->isEmpty()) {
            Log::warning('Aucun groupe projet disponible pour l\'utilisateur.', ['pays_code' => $paysCode]);
            Auth::logout();
            return response()->json(['error' => 'Vous n\'êtes associé à aucun groupe projet dans ce pays.'], 403);
        }

        // Si l'utilisateur n'a qu'un seul groupe projet, on le sélectionne automatiquement
        if ($groupes->count() === 1) {
            session(['projet_selectionne' => $groupes->first()->groupe_projet_id]);
            session(['step' => 'finalize']);
            Log::info('Utilisateur associé à un seul groupe projet.', ['projet_id' => $groupes->first()->groupe_projet_id]);

            return response()->json(['step' => 'finalize']);
        }

        // Sinon, demander à l'utilisateur de choisir son groupe
        session(['step' => 'choose_group']);
        return response()->json(['step' => 'choose_group', 'data' => $groupes]);
    }
    /**
     * Vérifie les informations d'identification (email et mot de passe).
     */
    public function checkUserAssociations(Request $request)
    {
        Log::info('Début de la vérification des identifiants.', ['login' => $request->login]);
    
        $request->validate([
            'login' => 'required|login',
            'password' => 'required|string',
        ]);
    
        $credentials = $request->only('login', 'password');
    
        if (!Auth::attempt($credentials)) {
            Log::error('Échec de la connexion.', ['login' => $request->login]);
            return response()->json(['error' => 'Identifiants incorrects.'], 401);
        }
    
        $user = Auth::user();
        
        if (!$user->is_active) {
            Auth::logout();
            return response()->json(['error' => 'Votre compte est désactivé.'], 403);
        }
    
        if ($user->is_blocked) {
            Auth::logout();
            return response()->json(['error' => 'Votre compte est bloqué. Veuillez contacter l\'administrateur.'], 403);
        }
    
        if (Hash::check('123456789', $user->password)) {
            $user->increment('default_password_attempts');
    
            if ($user->default_password_attempts > 1) {
                $user->update(['is_blocked' => true]);
                Auth::logout();
                return response()->json(['error' => 'Votre compte a été bloqué. Contactez l\'administrateur.'], 403);
            }
    
            session(['force_password_change' => true]);
        }
    
        Log::info('Connexion réussie.', ['user_id' => $user->acteur_id]);
    
        // Récupérer les pays associés à l'utilisateur
        $pays = GroupeProjetPaysUser::where('user_id', $user->acteur_id)
            ->join('pays', 'groupe_projet_pays_user.pays_code', '=', 'pays.alpha3')
            ->distinct()
            ->get(['pays.alpha3', 'pays.nom_fr_fr']);
    
        if ($pays->isEmpty()) {
            Log::error('Aucun pays associé à l\'utilisateur.', ['user_id' => $user->acteur_id]);
            Auth::logout();
            return response()->json(['error' => 'Vous n\'êtes associé à aucun pays.'], 403);
        }
    
        // Si l'utilisateur a un seul pays
        if ($pays->count() === 1) {
            $paysSelectionne = $pays->first();
            Log::info('Utilisateur associé à un seul pays.', ['pays_code' => $paysSelectionne->alpha3]);
        
            session(['pays_selectionne' => $paysSelectionne->alpha3]);
        
            // Récupérer les groupes projets associés à ce pays
            $groupes = GroupeProjetPaysUser::where('user_id', $user->acteur_id)
                ->where('pays_code', $paysSelectionne->alpha3)
                ->with('groupeProjet')
                ->get();
        
            if ($groupes->isEmpty()) {
                Log::warning('Aucun groupe projet disponible pour l\'utilisateur.', ['user_id' => $user->acteur_id]);
                Auth::logout();
                return response()->json(['error' => 'Aucun groupe projet trouvé pour ce pays.'], 403);
            }
            
            // Si l'utilisateur n'a qu'un seul groupe projet dans ce pays
            if ($groupes->count() === 1) {
                $groupeSelectionne = $groupes->first();
                session(['projet_selectionne' => $groupeSelectionne->groupe_projet_id]);
                session(['step' => 'finalize']);
                
                Log::info('Utilisateur associé à un seul groupe projet dans le pays.', [
                    'pays_code' => $paysSelectionne->alpha3,
                    'projet_id' => $groupeSelectionne->groupe_projet_id
                ]);
                
                return response()->json([
                    'step' => 'finalize',
                    'force_password_change' => session('force_password_change', false),
                ]);
            }
            
            // Si plusieurs groupes, passer à l'étape de choix
            session(['step' => 'choose_group']);
            return response()->json([
                'step' => 'choose_group',
                'data' => $groupes,
                'pays_code' => $paysSelectionne->alpha3,
                'force_password_change' => session('force_password_change', false),
            ]);
        }
        
        // Si l'utilisateur a plusieurs pays, il doit en choisir un
        Log::info('Utilisateur associé à plusieurs pays.', ['user_id' => $user->acteur_id]);
        session(['step' => 'choose_country']);
        return response()->json(['step' => 'choose_country', 'data' => $pays]);
    }

    /*
    public function checkUserAssociations(Request $request)
    {
        Log::info('Début de la vérification des identifiants.', ['email' => $request->email]);
    
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
    
        $credentials = $request->only('email', 'password');
    
        if (!Auth::attempt($credentials)) {
            Log::error('Échec de la connexion.', ['email' => $request->email]);
            return response()->json(['error' => 'Identifiants incorrects.'], 401);
        }
    
        $user = Auth::user();
        
        if (!$user->is_active) {
            Auth::logout();
            return response()->json(['error' => 'Votre compte est désactivé.'], 403);
        }
    
        if ($user->is_blocked) {
            Auth::logout();
            return response()->json(['error' => 'Votre compte est bloqué. Veuillez contacter l\'administrateur.'], 403);
        }
    
        if (Hash::check('123456789', $user->password)) {
            $user->increment('default_password_attempts');
    
            if ($user->default_password_attempts > 1) {
                $user->update(['is_blocked' => true]);
                Auth::logout();
                return response()->json(['error' => 'Votre compte a été bloqué. Contactez l\'administrateur.'], 403);
            }
    
            session(['force_password_change' => true]);
        }
    
        Log::info('Connexion réussie.', ['user_id' => $user->acteur_id]);
    
        // Récupérer les pays associés à l'utilisateur
        $pays = GroupeProjetPaysUser::where('user_id', $user->acteur_id)
            ->join('pays', 'groupe_projet_pays_user.pays_code', '=', 'pays.alpha3')
            ->distinct()
            ->get(['pays.alpha3', 'pays.nom_fr_fr']);
    
        if ($pays->isEmpty()) {
            Log::error('Aucun pays associé à l\'utilisateur.', ['user_id' => $user->acteur_id]);
            Auth::logout();
            return response()->json(['error' => 'Vous n\'êtes associé à aucun pays.'], 403);
        }
    
        // Si l'utilisateur a un seul pays
        if ($pays->count() === 1) {
            $paysSelectionne = $pays->first();
            Log::info('Utilisateur associé à un seul pays.', ['pays_code' => $paysSelectionne->alpha3]);
        
            session(['pays_selectionne' => $paysSelectionne->alpha3]);
        
            // Récupérer les groupes projets associés à ce pays
            $groupes = GroupeProjetPaysUser::where('user_id', $user->acteur_id)
                ->where('pays_code', $paysSelectionne->alpha3)
                ->with('groupeProjet')
                ->get();
        
            if ($groupes->isEmpty()) {
                Log::warning('Aucun groupe projet disponible pour l\'utilisateur.', ['user_id' => $user->acteur_id]);
                Auth::logout();
                return response()->json(['error' => 'Aucun groupe projet trouvé pour ce pays.'], 403);
            }
            
            // Si l'utilisateur n'a qu'un seul groupe projet dans ce pays
            if ($groupes->count() === 1) {
                $groupeSelectionne = $groupes->first();
                session(['projet_selectionne' => $groupeSelectionne->groupe_projet_id]);
                session(['step' => 'finalize']);
                
                Log::info('Utilisateur associé à un seul groupe projet dans le pays.', [
                    'pays_code' => $paysSelectionne->alpha3,
                    'projet_id' => $groupeSelectionne->groupe_projet_id
                ]);
                
                return response()->json([
                    'step' => 'finalize',
                    'force_password_change' => session('force_password_change', false),
                ]);
            }
            
            // Si plusieurs groupes, passer à l'étape de choix
            session(['step' => 'choose_group']);
            return response()->json([
                'step' => 'choose_group',
                'data' => $groupes,
                'pays_code' => $paysSelectionne->alpha3,
                'force_password_change' => session('force_password_change', false),
            ]);
        }
        
        // Si l'utilisateur a plusieurs pays, il doit en choisir un
        Log::info('Utilisateur associé à plusieurs pays.', ['user_id' => $user->acteur_id]);
        session(['step' => 'choose_country']);
        return response()->json(['step' => 'choose_country', 'data' => $pays]);
    }
    */


    /**
     * Enregistre le choix du pays.
     */
    public function selectCountry(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Votre session a expiré. Veuillez vous reconnecter.'], 401);
        }

        $request->validate(['pays_code' => 'required|string']);
        session(['pays_selectionne' => $request->pays_code]);

        return $this->handleGroupSelection(Auth::user(), $request->pays_code);
    }

    /**
     * Enregistre le choix du groupe projet.
     */
    public function selectGroup(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Votre session a expiré. Veuillez vous reconnecter.'], 401);
        }

        $request->validate(['projet_id' => 'required|exists:groupe_projet_pays_user,groupe_projet_id']);
        session(['projet_selectionne' => $request->projet_id]);
        session(['step' => 'finalize']);

        return response()->json(['step' => 'finalize']);
    }

    /**
     * Finalise la connexion.
     */
    public function finalizeLogin()
    {
        if (session('step') !== 'finalize') {
            return redirect()->route('login')->with('error', 'Veuillez compléter toutes les étapes.');
        }

        session()->forget('step');
        return redirect()->intended('admin')->with('success', 'Connexion réussie.');
    }

    /**
     * Déconnecte l'utilisateur.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function showResetForm(Request $request, $token = null)
    {
        return view('users.reset-password')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

    /**
     * Page pour changer de groupe projet et de pays .
     */
    /**
     * Récupère les groupes projets associés à un pays donné pour l'utilisateur connecté.
     */
    public function getGroupsByCountry(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Votre session a expiré. Veuillez vous reconnecter.'], 401);
        }

        $request->validate(['pays_code' => 'required|string']);

        $user = Auth::user();
        
        $groupes = GroupeProjetPaysUser::where('user_id', $user->acteur_id)
            ->where('pays_code', $request->pays_code)
            ->with('groupeProjet')
            ->get();

        if ($groupes->isEmpty()) {
            return response()->json(['error' => 'Aucun groupe projet trouvé pour ce pays.'], 404);
        }

        return response()->json($groupes);
    }
    /**
     * Change le pays et le groupe projet sélectionné par l'utilisateur.
     */
    public function changeGroup(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Votre session a expiré. Veuillez vous reconnecter.'], 401);
        }

        $request->validate([
            'pays_code' => 'required|string',
            'projet_id' => 'required|exists:groupe_projet_pays_user,groupe_projet_id'
        ]);

        // Vérifier si l'utilisateur est bien associé au pays et au groupe projet
        $user = Auth::user();
        $exists = GroupeProjetPaysUser::where('user_id', $user->acteur_id)
            ->where('pays_code', $request->pays_code)
            ->where('groupe_projet_id', $request->projet_id)
            ->exists();

        if (!$exists) {
            return response()->json(['error' => 'Vous n\'êtes pas autorisé à rejoindre ce groupe projet.'], 403);
        }

        // Mettre à jour la session avec le nouveau pays et groupe projet
        session([
            'pays_selectionne' => $request->pays_code,
            'projet_selectionne' => $request->projet_id
        ]);

        Log::info('Changement du pays et groupe projet', [
            'user_id' => $user->acteur_id,
            'pays_code' => $request->pays_code,
            'projet_id' => $request->projet_id
        ]);

        return response()->json(['success' => true]);
    }

    public function groupeProjetSelectionne()
    {
        // Vérifiez si l'utilisateur est authentifié
        if (!auth()->check() || !session('projet_selectionne')) {
            // Redirigez vers la page de connexion
            return redirect()->route('login');
        }

        // Logique pour afficher la vue
        return view('auth.connexion');
    }
}
