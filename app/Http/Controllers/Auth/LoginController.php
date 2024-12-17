<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Acteur;
use App\Models\Ecran;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use SendsPasswordResetEmails;

    /**
     * Affiche le formulaire de connexion.
     */
    public function showLoginForm()
    {
        return view('auth.connexion');
    }

    /**
     * Gérer une tentative de connexion.
     */
    public function login(Request $request)
    {
        $ecran = Ecran::find($request->input('ecran_id'));

        // Validation des données de connexion
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        // Vérifier si l'utilisateur est déjà connecté
        if ($this->isUserAlreadyLoggedIn($credentials['email'])) {
            Log::info('Tentative de connexion pour un utilisateur déjà connecté : ' . $credentials['email']);
            return redirect()->route('login', ['ecran_id' => $ecran->id])
                ->withErrors(['email' => 'Cet utilisateur est déjà connecté.']);
        }

        // Vérification des identifiants
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $this->addUserToConnectedUsers($user);

            Log::info('Connexion réussie pour l\'utilisateur : ' . $user->email);

            return redirect()->intended('/admin');
        }

        Log::error('Échec de la connexion pour l\'email : ' . $credentials['email']);
        return redirect()->route('login', ['ecran_id' => $ecran->id])
            ->withErrors(['email' => 'Adresse email ou mot de passe incorrect.']);
    }

    /**
     * Ajouter un utilisateur à la liste des utilisateurs connectés avec un token.
     */
    protected function addUserToConnectedUsers($user)
    {
        $token = Str::random(60); // Génère un token unique
        $user->update(['api_token' => hash('sha256', $token)]); // Stocke le token
        Log::info('Utilisateur ajouté à la liste des connectés : ' . $user->email);
    }

    /**
     * Vérifier si l'utilisateur est déjà connecté.
     */
    protected function isUserAlreadyLoggedIn($email)
    {
        $user = User::where('email', $email)->first();
        return $user && $user->api_token !== null;
    }

    /**
     * Gérer la déconnexion de l'utilisateur.
     */
    public function logout(Request $request)
    {
        $user = Auth::user();

        // Supprimer le token de l'utilisateur lors de la déconnexion
        $user->update(['api_token' => null]);

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Log::info('Utilisateur déconnecté : ' . $user->email);

        return redirect()->route('login')->with('success', 'Vous êtes déconnecté.');
    }

    /**
     * Réinitialisation du mot de passe (formulaire d'envoi de lien).
     */
    public function postResetForm(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $response = $this->sendResetLinkEmail($request);

        return $response == Password::RESET_LINK_SENT
            ? back()->with(['status' => __($response)])
            : back()->withErrors(['email' => __($response)]);
    }

    /**
     * Réinitialisation du mot de passe (formulaire).
     */
    public function showResetForm(Request $request, $token = null)
    {
        return view('users.reset-password')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

    /**
     * Réinitialiser le mot de passe.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
                Log::info('Mot de passe réinitialisé pour l\'utilisateur : ' . $user->email);
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }
}
