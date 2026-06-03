<?php

namespace App\Notifications;

use App\Models\Admin2faToken;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class Admin2faNotification extends Notification
{
    use Queueable;

    public function __construct(public Admin2faToken $token)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Admin Login Verification - Templatr')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You are attempting to access the Templatr admin area.')
            ->line('Your one-time verification code is:')
            ->line('**' . $this->token->token . '**')
            ->line('This code will expire in 10 minutes.')
            ->line('If you did not attempt this login, please secure your account immediately.')
            ->salutation('Best regards, The Templatr Team');
    }
}
