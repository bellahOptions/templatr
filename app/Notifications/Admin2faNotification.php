<?php

namespace App\Notifications;

use App\Models\Admin2faToken;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class Admin2faNotification extends Notification implements ShouldQueue
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
            ->view('emails.notification', [
                'user' => $notifiable,
                'title' => 'Admin Login Verification',
                'icon' => '🔐',
                'message' => 'You are attempting to access the Templatr admin area. Your one-time verification code is:',
                'actionUrl' => route('admin.2fa.form'),
                'actionText' => 'Go to Verification Page',
            ])
            ->line('**' . $this->token->token . '**')
            ->line('This code will expire in 10 minutes.')
            ->line('If you did not attempt this login, please secure your account immediately.');
    }
}
