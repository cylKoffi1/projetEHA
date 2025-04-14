<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProjetRefuseNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $codeProjet;
    public $libelleProjet;
    public $commentaire;
    public $approbateur;

    public function __construct($codeProjet, $libelleProjet, $commentaire, $approbateur)
    {
        $this->codeProjet = $codeProjet;
        $this->libelleProjet = $libelleProjet;
        $this->commentaire = $commentaire;
        $this->approbateur = $approbateur;
    }

    public function build()
    {
        return $this->subject("Refus du projet : {$this->libelleProjet}")
                    ->markdown('emails.refus');
    }
}
