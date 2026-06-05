<?php

namespace App\Notifications;

use App\Models\Admin2faToken;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class Admin2faNotification extends Notification
{
    use Queueable;

    public function __construct(public Admin2faToken $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Admin Login Verification - Templatr')
            ->markdown('emails.admin-2fa', [
                'user' => $notifiable,
                'code' => $this->token->token,
                'actionUrl' => route('admin.2fa.form'),
            ]);
    }
}
