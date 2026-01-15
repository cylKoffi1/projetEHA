<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Affiche le formulaire de demande de réinitialisation de mot de passe.
     *
     * @return \Illuminate\View\View
     */
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    /**
     * Envoie l'email de réinitialisation de mot de passe.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|string'
        ]);

        $identifier = trim($request->email);
        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;

        // Chercher l'utilisateur par email ou login
        $user = null;
        if ($isEmail) {
            $user = \App\Models\User::where('email', $identifier)->first();
            if (!$user) {
                $user = \App\Models\User::where('login', $identifier)->first();
            }
        } else {
            $user = \App\Models\User::where('login', $identifier)->first();
            if (!$user) {
                $user = \App\Models\User::where('email', $identifier)->first();
            }
        }

        if (!$user) {
            return back()->withErrors(['email' => 'Aucun compte trouvé avec cet identifiant.']);
        }

        if (empty($user->email)) {
            return back()->withErrors(['email' => 'Aucun email associé à ce compte. Veuillez contacter l\'administrateur.']);
        }

        // Utiliser l'email de l'utilisateur pour l'envoi du lien
        $response = $this->broker()->sendResetLink(
            ['email' => $user->email]
        );

        return $response == Password::RESET_LINK_SENT
            ? back()->with('status', 'Un lien de réinitialisation a été envoyé à votre adresse email : ' . $user->email)
            : back()->withErrors(['email' => trans($response)]);
    }

    /**
     * Valide le format de l'email.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function validateEmail(Request $request)
    {
        $request->validate(['email' => 'required|string']);
    }

    /**
     * Obtient le broker de réinitialisation de mot de passe.
     *
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    public function broker()
    {
        return Password::broker();
    }
}
