<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PasswordSetupNotification extends Notification
{
    use Queueable;

    protected $token;
    protected $email;

    public function __construct($token, $email)
    {
        $this->token = $token;
        $this->email = $email;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        // Password setup link (dummy URL for now, replace with your frontend or API route)
        $url = env('FRONTEND_URL', 'http://localhost') . '/set-password?token=' . $this->token . '&email=' . urlencode($this->email);

        return (new MailMessage)
            ->subject('Set Up Your Manager Password')
            ->line('You have been added as a manager. Please click the link below to set up your password.')
            ->action('Set Password', $url)
            ->line('This link will expire in 24 hours.');
    }
}
