<?php

namespace App\Notifications;

use App\Helpers\CurrencyHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class MonthlySalesReportNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public array $report
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $report = $this->report;

        $mail = (new MailMessage)
            ->subject('Monthly Sales Report - ' . $report['month'] . ' - Templatr')
            ->view('emails.notification', [
                'user' => $notifiable,
                'title' => 'Monthly Sales Report',
                'icon' => '📊',
                'message' => 'Here is your sales summary for **' . $report['month'] . '**:',
                'actionUrl' => route('admin.dashboard'),
                'actionText' => 'View Full Dashboard',
            ])
            ->line('**Total Revenue:** ' . CurrencyHelper::format($report['total_revenue']))
            ->line('**Total Orders:** ' . number_format($report['total_orders']))
            ->line('**Products Sold:** ' . number_format($report['products_sold']))
            ->line('**New Users Registered:** ' . number_format($report['new_users']))
            ->line('**New Products Added:** ' . number_format($report['new_products']));

        if ($report['previous_month_revenue'] > 0) {
            $change = (($report['total_revenue'] - $report['previous_month_revenue']) / $report['previous_month_revenue']) * 100;
            $changeFormatted = number_format(abs($change), 1);
            $direction = $change >= 0 ? 'increase' : 'decrease';
            $mail->line('**Revenue vs Last Month:** ' . $changeFormatted . '% ' . $direction);
        }

        if (!empty($report['top_products'])) {
            $mail->line('---')
                ->line('**Top Selling Products:**');
            foreach ($report['top_products'] as $index => $product) {
                $mail->line(($index + 1) . '. ' . $product['title'] . ' (' . $product['sales'] . ' sales - ' . CurrencyHelper::format($product['revenue']) . ')');
            }
        }

        $mail->line('Thank you for using Templatr!');

        return $mail;
    }
}
