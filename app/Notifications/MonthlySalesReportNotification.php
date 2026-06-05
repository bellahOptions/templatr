<?php

namespace App\Notifications;

use App\Helpers\CurrencyHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MonthlySalesReportNotification extends Notification
{
    use Queueable;

    public function __construct(
        public array $report
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $report = $this->report;

        $data = [
            'user' => $notifiable,
            'month' => $report['month'],
            'totalRevenue' => CurrencyHelper::format($report['total_revenue']),
            'totalOrders' => number_format($report['total_orders']),
            'productsSold' => number_format($report['products_sold']),
            'newUsers' => number_format($report['new_users']),
            'newProducts' => number_format($report['new_products']),
            'actionUrl' => route('admin.dashboard'),
        ];

        if ($report['previous_month_revenue'] > 0) {
            $change = (($report['total_revenue'] - $report['previous_month_revenue']) / $report['previous_month_revenue']) * 100;
            $data['revenueChange'] = number_format(abs($change), 1);
            $data['revenueDirection'] = $change >= 0 ? 'increase' : 'decrease';
        }

        if (! empty($report['top_products'])) {
            $data['topProducts'] = array_map(function ($product) {
                return [
                    'title' => $product['title'],
                    'sales' => $product['sales'],
                    'revenue' => CurrencyHelper::format($product['revenue']),
                ];
            }, $report['top_products']);
        }

        return (new MailMessage)
            ->subject('Monthly Sales Report - '.$report['month'].' - Templatr')
            ->markdown('emails.monthly-report', $data);
    }
}
