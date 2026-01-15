<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificationValidationProjet extends Mailable
{
    use Queueable, SerializesModels;

    public string $codeProjet;
    public string $libelleProjet;
    public string $lastActorLabel;
    public array  $destinataire;
    public ?string $ctaUrl;

    public function __construct(
        string $codeProjet,
        string $libelleProjet,
        string $lastActorLabel,
        array  $destinataire,
        ?string $ctaUrl = null
    ) {
        $this->codeProjet     = $codeProjet;
        $this->libelleProjet  = $libelleProjet;
        $this->lastActorLabel = $lastActorLabel;
        $this->destinataire   = $destinataire;
        $this->ctaUrl         = $ctaUrl;
    }

    public function build()
    {
        return $this->subject("Validation du projet {$this->libelleProjet}")
            ->markdown('emails.validation')
            ->with([
                'codeProjet'      => $this->codeProjet,
                'libelleProjet'   => $this->libelleProjet,
                'lastActorLabel'  => $this->lastActorLabel,       // ← auteur de la dernière validation
                'recipientLabel'  => trim(($this->destinataire['libelle_court'] ?? '').' '.($this->destinataire['libelle_long'] ?? '')) ?: null,
                'ctaUrl'          => $this->ctaUrl ?? route('approbations.dashboard'),
            ]);
    }
}
