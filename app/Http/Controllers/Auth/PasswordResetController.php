<?php

namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Models\Personnel;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\Request;


class PasswordResetController extends Controller
{

    public function forgotPassword(Request $request)
    {
        // Valider l'adresse e-mail
        $request->validate(['email' => 'required|email']);

        // Rechercher l'utilisateur dans la base de données
        $user = Personnel::where('email', $request->email)->first();

        if ($user) {
            // Stocker l'e-mail dans la session pour une utilisation ultérieure
            Session::put('reset_email', $request->email);

            // Afficher le formulaire de confirmation d'identité
            return view('confirm_identity_form');
        }

        return redirect()->back()->withErrors(['email' => 'Aucun utilisateur trouvé avec cette adresse e-mail.']);
    }

    public function confirmIdentity(Request $request)
    {
        // Vérifier si l'utilisateur a confirmé son identité
        if ($request->filled('confirm')) {
            // Afficher le formulaire de réinitialisation du mot de passe
            return view('reset_password_form');
        }

        return redirect()->route('login')->withErrors(['confirm' => 'Veuillez confirmer votre identité pour continuer.']);
    }
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $response = Password::sendResetLink(
            $request->only('email')
        );

        return $response == Password::RESET_LINK_SENT
            ? back()->with(['status' => __($response)])
            : back()->withErrors(['email' => __($response)]);
    }

    public function resetPasswords(Request $request)
    {
        // Valider les champs du formulaire
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        // Récupérer l'e-mail de la session
        $email = Session::get('reset_email');

        // Rechercher l'utilisateur avec l'e-mail
        $user = Personnel::where('email', $email)->first();

        if ($user) {
            // Mettre à jour le mot de passe de l'utilisateur
            $user->update([
                'password' => Hash::make($request->password),
            ]);

            // Supprimer l'e-mail de la session
            Session::forget('reset_email');

            return redirect()->route('login')->with('success', 'Votre mot de passe a été réinitialisé avec succès.');
        }

        return redirect()->route('login')->withErrors(['email' => 'Utilisateur introuvable.']);
    }
}
