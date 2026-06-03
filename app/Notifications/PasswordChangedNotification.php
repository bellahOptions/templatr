<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PasswordChangedNotification extends Notification
{
    use Queueable;

    public function __construct()
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Password Has Been Changed - Templatr')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your Templatr account password was successfully changed.')
            ->line('If you made this change, no further action is needed.')
            ->line('If you did NOT change your password, please secure your account immediately:')
            ->line('- Reset your password using the "Forgot Password" link on the login page.')
            ->line('- Contact support if you need assistance.')
            ->action('Go to Templatr', url('/'))
            ->line('Thank you for using Templatr!')
            ->salutation('Best regards, Templatr Security Team');
    }
}
