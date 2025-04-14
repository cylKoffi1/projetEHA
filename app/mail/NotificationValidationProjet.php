<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificationValidationProjet extends Mailable
{
    use Queueable, SerializesModels;

    public $codeProjet;
    public $libelleProjet;
    public $approbateur;

    public function __construct($codeProjet, $libelleProjet, $approbateur)
    {
        $this->codeProjet = $codeProjet;
        $this->libelleProjet = $libelleProjet;
        $this->approbateur = $approbateur;
    }

    public function build()
    {
        return $this->subject("Validation du projet {$this->libelleProjet}")
                    ->markdown('emails.validation');
    }

}
