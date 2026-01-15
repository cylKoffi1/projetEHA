<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Pays;
use App\Models\GroupeProjetPaysUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
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
     * Vérifie les informations d'identification (login et mot de passe)
     * et envoie un code OTP par email si elles sont correctes.
     */
    public function checkUserAssociations(Request $request)
    {
        Log::info('Début de la vérification des identifiants.', ['login' => $request->login]);
    
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);
    
        $identifier = trim($request->login);
        $password = $request->password;
        
        // Déterminer si l'identifiant est un email ou un login
        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;
        
        // Chercher l'utilisateur par login ou email
        $user = null;
        if ($isEmail) {
            // Essayer d'abord avec l'email
            $user = User::where('email', $identifier)->first();
            // Si pas trouvé, essayer avec le login (au cas où l'email serait aussi un login)
            if (!$user) {
                $user = User::where('login', $identifier)->first();
            }
        } else {
            // Essayer d'abord avec le login
            $user = User::where('login', $identifier)->first();
            // Si pas trouvé, essayer avec l'email (au cas où le login serait aussi un email)
            if (!$user) {
                $user = User::where('email', $identifier)->first();
            }
        }
        
        // Vérifier si l'utilisateur existe et si le mot de passe est correct
        if (!$user || !Hash::check($password, $user->password)) {
            Log::error('Échec de la connexion.', ['identifier' => $identifier]);
            return response()->json(['error' => 'Identifiants incorrects.'], 401);
        }
        
        // Connecter l'utilisateur manuellement
        Auth::login($user);
    
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
    
        Log::info('Connexion réussie, génération du code OTP.', ['user_id' => $user->acteur_id]);

        // Vérifier si un code OTP existe déjà en session (éviter l'envoi multiple)
        $existingCode = session('login_otp_code');
        $existingExpiresAt = session('login_otp_expires_at');
        
        // Si un code existe déjà et n'est pas expiré, ne pas renvoyer
        if ($existingCode && $existingExpiresAt && now()->lessThan($existingExpiresAt)) {
            Log::info('Code OTP déjà envoyé, réutilisation du code existant.', ['user_id' => $user->id]);
            return response()->json([
                'step' => 'verify_otp',
                'email' => $user->email
            ]);
        }

        // Génération du code OTP à 6 chiffres
        $code = random_int(100000, 999999);

        session([
            'login_otp_user_id'    => $user->id,
            'login_otp_code'       => $code,
            'login_otp_expires_at' => now()->addMinutes(10),
            'step'                 => 'verify_otp',
        ]);

        if (!empty($user->email)) {
            try {
                Mail::send('emails.login_otp', ['code' => $code, 'user' => $user], function ($m) use ($user) {
                    $m->to($user->email, $user->login)
                      ->subject('Code de vérification de connexion');
                });
                Log::info('Code OTP envoyé par email.', ['user_id' => $user->id, 'email' => $user->email]);
            } catch (\Throwable $e) {
                Log::error('Erreur lors de l\'envoi de l\'OTP par email', [
                    'user_id' => $user->id,
                    'error'   => $e->getMessage(),
                ]);
                return response()->json(['error' => "Impossible d'envoyer le code de vérification. Contactez l'administrateur."], 500);
            }
        } else {
            Log::warning('Utilisateur sans email pour OTP.', ['user_id' => $user->id]);
            return response()->json(['error' => "Aucun email n'est associé à votre compte. Contactez l'administrateur."], 500);
        }

        return response()->json([
            'step' => 'verify_otp',
            'email' => $user->email
        ]);
    }

    /**
     * Vérifie le code OTP et poursuit le flux (pays / groupe / finalisation).
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6',
        ]);

        $expectedCode = session('login_otp_code');
        $expiresAt    = session('login_otp_expires_at');

        if (!$expectedCode || !$expiresAt) {
            return response()->json(['error' => 'Aucun code actif. Veuillez vous reconnecter.'], 400);
        }

        if (now()->greaterThan($expiresAt)) {
            Auth::logout();
            session()->forget(['login_otp_code','login_otp_user_id','login_otp_expires_at']);
            return response()->json(['error' => 'Le code a expiré. Veuillez vous reconnecter.'], 400);
        }

        if ($request->code != $expectedCode) {
            return response()->json(['error' => 'Code de vérification incorrect.'], 422);
        }

        // Code correct : on nettoie la session OTP
        session()->forget(['login_otp_code','login_otp_user_id','login_otp_expires_at']);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Session expirée. Veuillez vous reconnecter.'], 401);
        }

        return $this->proceedAfterLogin($user);
    }

    /**
     * Logique après authentification (choix pays / groupe / finalisation).
     */
    private function proceedAfterLogin($user)
    {
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
            ['token' => $token, 'login' => $request->login]
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
