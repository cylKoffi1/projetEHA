<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class ResetPasswordController extends Controller
{
    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }
    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.passwords.reset')->with([
            'token' => $token,
            'email' => $request->email // Passe aussi l'email si nécessaire
        ]);
    }

    public function resetPassword(Request $request)
    {
        // 1️⃣ Validation des entrées utilisateur
        $request->validate([
            'email' => 'required|email|exists:utilisateurs,email',
            'password' => 'required|min:8|confirmed',
            'token' => 'required'
        ]);

        // 2️⃣ Vérifier si un token existe pour cet email
        $reset = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$reset) {
            return back()->withErrors(['token' => 'Aucun token trouvé pour cet e-mail.']);
        }

        // 3️⃣ Vérifier si le token a expiré (expire après 60 minutes)
        $expirationTime = Carbon::parse($reset->created_at)->addMinutes(60);
        if (Carbon::now()->greaterThan($expirationTime)) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withErrors(['token' => 'Le token a expiré. Veuillez demander un nouveau lien de réinitialisation.']);
        }

        // 4️⃣ Vérifier la validité du token avec `Hash::check()`
        if (!Hash::check($request->token, $reset->token)) {
            return back()->withErrors(['token' => 'Ce token de réinitialisation est invalide.']);
        }

        // 5️⃣ Mettre à jour le mot de passe de l'utilisateur
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->withErrors(['email' => 'Utilisateur introuvable.']);
        }

        $user->password = Hash::make($request->password);
        $user->must_change_password = false; // Optionnel : si l'utilisateur doit changer le mot de passe après connexion
        $user->save();

        // 6️⃣ Supprimer le token après utilisation
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('status', 'Votre mot de passe a été mis à jour avec succès.');
    }
}
