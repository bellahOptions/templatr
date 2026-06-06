<?php

namespace App\Notifications;

use App\Models\TermsOfService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TermsUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public TermsOfService $terms) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Our Terms of Service Have Been Updated - Templatr')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('We have updated our Terms of Service. Please review the latest version at your earliest convenience.')
            ->line('The update was made on '.$this->terms->updated_at->format('F j, Y').'.')
            ->action('Read Updated Terms', route('terms.show'))
            ->line('By continuing to use Templatr, you agree to the updated Terms of Service.')
            ->line('Thank you for being part of our community.');
    }
}
