<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;

class CustomResetPasswordNotification extends ResetPasswordNotification
{
    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $resetUrl = url(config('app.url') . route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->email
        ], false));

        return (new MailMessage)
            ->subject('Réinitialisation de votre mot de passe - GP-INFRAS')
            ->view('emails.reset_password', [
                'actionUrl' => $resetUrl,
                'url' => $resetUrl,
                'user' => $notifiable
            ]);
    }
}
