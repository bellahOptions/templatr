<?php

namespace App\Notifications;

use App\Models\EmailVerification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends Notification
{
    use Queueable;

    public function __construct(public EmailVerification $verification)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('verification.verify', [
            'token' => $this->verification->token,
            'email' => $notifiable->email,
        ]);

        return (new MailMessage)
            ->subject('Verify Your Email - Templatr')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Thank you for creating an account on Templatr.')
            ->line('Please click the button below to verify your email address and get full access to your account.')
            ->action('Verify Email Address', $url)
            ->line('This verification link will expire in 60 minutes.')
            ->line('If you did not create an account, no further action is required.')
            ->salutation('Best regards, The Templatr Team');
    }
}
