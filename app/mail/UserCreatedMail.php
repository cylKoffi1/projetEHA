<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $password;

    public function __construct($user, $password)
    {
        $this->user = $user;
        $this->password = $password;
    }

    public function build()
    {
        return $this->subject('Bienvenue sur la plateforme - Informations de connexion')
            ->view('emails.user_created')
            ->with([
                'name' => $this->user->acteur->libelle_court .' '. $this->user->acteur->libelle_long,
                'login' => $this->user->login,
                'password' => $this->password,
                'url' => url('/login')
            ]);
    }
}
