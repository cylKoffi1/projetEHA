<?php
namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificationMail;

class NotificationService
{
    /**
     * Crée une notification et l'envoie par email automatiquement.
     */
    public static function envoyerNotification($utilisateur_id, $message)
    {
        // Enregistrement dans la base de données
        $notification = Notification::create([
            'utilisateur_id' => $utilisateur_id,
            'message' => $message,
            'statut_id' => 1, // "non_lu"
            'envoye' => 'non'
        ]);

        // Envoi d'un email automatique
        Mail::to($notification->utilisateur->email)->send(new NotificationMail($notification));

        // Mise à jour du statut d'envoi
        $notification->update(['envoye' => 'oui']);
    }
}
