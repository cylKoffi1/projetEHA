<?php

namespace App\Http\Controllers\Auth;

use App\Models\Personnel;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request; // Utilisez la classe correcte pour la gestion des demandes HTTP

class LoginController extends Controller
{
    use SendsPasswordResetEmails;
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    // public function __construct()
    // {
    //     $this->middleware('guest');
    // }


    /**
     * Show the login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.connexion');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('login', 'password');

        if (Auth::attempt($credentials)) {
            // Authentication passed
            $user = Auth::user();
            $personnel = $user->personnel;
            $domaines = $personnel->domaines;
            $groupesUtilisateur = $personnel->groupesUtilisateur;
            $expertises = $personnel->expertises;
            return redirect()->intended('/admin');
        }

        // Authentication failed
        return redirect()->route('login')->withErrors(['login' => 'Login ou mot de passe incorrect']);
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function logout()
    {
        Auth::logout();

        request()->session()->invalidate();

        request()->session()->regenerateToken();

        return redirect()->route('login')->with('succes', 'Vous êtes déconnecté.');

    }

    public function postResetForm(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $response = $this->sendResetLinkEmail($request);

        return $response == Password::RESET_LINK_SENT
            ? back()->with(['status' => __($response)])
            : back()->withErrors(['email' => __($response)]);
    }
    protected function sendResetLinkEmail(Request $request)
    {
        $this->validateEmail($request);

        // Use the relationship to get the user by email
        $user = Personnel::where('email', $request->email)->first();

        if (!$user) {
            return $this->sendResetLinkFailedResponse($request, Password::INVALID_USER);
        }

        $response = $this->broker()->sendResetLink(
            $this->credentials($request)
        );

        return $response;
    }
    protected function validateEmail(Request $request)
{
    $request->validate(['email' => 'required|email']);

    $user = Personnel::where('email', $request->email)->first();

    if (!$user) {
        return $this->sendResetLinkFailedResponse($request, Password::INVALID_USER);
    }
}


    protected function credentials(Request $request)
    {
        return ['email' => $request->email];
    }

    public function showResetForm(Request $request, $token = null)
    {
        return view('users.password-forgot')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

    public function ResetPasswordToken(string $token)
    {
        return view('users.reset-password', ['token' => $token]);
    }

    public function ResetPassword(Request $request)
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
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }
}
