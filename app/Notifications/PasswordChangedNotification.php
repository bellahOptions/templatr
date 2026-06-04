<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PasswordChangedNotification extends Notification implements ShouldQueue
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
            ->view('emails.notification', [
                'user' => $notifiable,
                'title' => 'Password Changed Successfully',
                'icon' => '🔒',
                'message' => 'Your Templatr account password was successfully changed. If you made this change, no further action is needed.',
                'actionUrl' => url('/'),
                'actionText' => 'Go to Templatr',
            ])
            ->line('If you did NOT change your password, please secure your account immediately:')
            ->line('- Reset your password using the "Forgot Password" link on the login page.')
            ->line('- Contact support if you need assistance.')
            ->line('Thank you for using Templatr!');
    }
}
