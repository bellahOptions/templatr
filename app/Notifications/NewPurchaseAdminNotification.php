<?php

namespace App\Notifications;

use App\Models\Order;
use App\Helpers\CurrencyHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewPurchaseAdminNotification extends Notification
{
    use Queueable;

    public function __construct(public Order $order)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $items = $this->order->items->map(function ($item) {
            return $item->product?->title ?? 'Product #' . $item->product_id;
        })->implode(', ');

        $customerName = $this->order->user?->name ?? $this->order->guest_name ?? 'Guest';
        $customerEmail = $this->order->customer_email;

        return (new MailMessage)
            ->subject('New Purchase - ' . CurrencyHelper::format($this->order->total_amount) . ' - Templatr')
            ->greeting('New Purchase Received!')
            ->line('A new purchase has been made on Templatr.')
            ->line('**Customer:** ' . $customerName . ' (' . $customerEmail . ')')
            ->line('**Order Number:** ' . $this->order->order_number)
            ->line('**Amount:** ' . CurrencyHelper::format($this->order->total_amount))
            ->line('**Items:** ' . $items)
            ->line('**Payment Method:** ' . ucfirst($this->order->payment_method))
            ->line('**Date:** ' . $this->order->created_at->format('F j, Y g:i A'))
            ->action('View Order', route('admin.orders.show', $this->order))
            ->line('Thank you for managing Templatr!')
            ->salutation('Best regards, Templatr System');
    }
}
