<?php

namespace App\Notifications;

use App\Helpers\CurrencyHelper;
use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewPurchaseAdminNotification extends Notification
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $items = $this->order->items->map(function ($item) {
            return $item->product?->title ?? 'Product #'.$item->product_id;
        })->implode(', ');

        $customerName = $this->order->user?->name ?? $this->order->guest_name ?? 'Guest';
        $customerEmail = $this->order->customer_email;

        $recipientName = $notifiable instanceof User ? $notifiable->name : 'Admin';

        return (new MailMessage)
            ->subject('New Purchase - '.CurrencyHelper::format($this->order->total_amount).' - Templatr')
            ->markdown('emails.new-purchase', [
                'recipientName' => $recipientName,
                'customerName' => $customerName,
                'customerEmail' => $customerEmail,
                'orderNumber' => $this->order->order_number,
                'amount' => CurrencyHelper::format($this->order->total_amount),
                'items' => $items,
                'paymentMethod' => ucfirst($this->order->payment_method),
                'orderDate' => $this->order->created_at->format('F j, Y g:i A'),
                'actionUrl' => route('admin.orders.show', $this->order),
            ]);
    }
}
