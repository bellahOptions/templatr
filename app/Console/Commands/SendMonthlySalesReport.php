<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Notifications\MonthlySalesReportNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendMonthlySalesReport extends Command
{
    protected $signature = 'app:send-monthly-sales-report {--month= : The month to report on (format: Y-m)}';

    protected $description = 'Send monthly sales report to admin and specified email';

    public function handle(): int
    {
        $month = $this->option('month')
            ? Carbon::createFromFormat('Y-m', $this->option('month'))
            : now()->subMonth();

        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();

        $this->info('Generating sales report for '.$month->format('F Y').'...');

        // Gather report data
        $orders = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth]);

        $totalRevenue = (float) $orders->sum('total_amount');
        $totalOrders = $orders->count();

        $productsSold = OrderItem::whereHas('order', function ($q) use ($startOfMonth, $endOfMonth) {
            $q->where('payment_status', 'paid')
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
        })->count();

        $newUsers = User::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
        $newProducts = Product::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();

        // Previous month revenue for comparison
        $prevMonthStart = $startOfMonth->copy()->subMonth();
        $prevMonthEnd = $endOfMonth->copy()->subMonth();
        $previousMonthRevenue = (float) Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$prevMonthStart, $prevMonthEnd])
            ->sum('total_amount');

        // Top 5 selling products
        $topProducts = OrderItem::whereHas('order', function ($q) use ($startOfMonth, $endOfMonth) {
            $q->where('payment_status', 'paid')
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
        })
            ->selectRaw('product_id, COUNT(*) as sales, SUM(price) as revenue')
            ->groupBy('product_id')
            ->orderByDesc('sales')
            ->take(5)
            ->get()
            ->map(function ($item) {
                $product = Product::find($item->product_id);

                return [
                    'title' => $product?->title ?? 'Deleted Product #'.$item->product_id,
                    'sales' => $item->sales,
                    'revenue' => (float) $item->revenue,
                ];
            })
            ->toArray();

        $report = [
            'month' => $month->format('F Y'),
            'total_revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
            'products_sold' => $productsSold,
            'new_users' => $newUsers,
            'new_products' => $newProducts,
            'previous_month_revenue' => $previousMonthRevenue,
            'top_products' => $topProducts,
        ];

        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Revenue', number_format($totalRevenue, 2)],
                ['Total Orders', number_format($totalOrders)],
                ['Products Sold', number_format($productsSold)],
                ['New Users', number_format($newUsers)],
                ['New Products', number_format($newProducts)],
                ['Prev Month Revenue', number_format($previousMonthRevenue, 2)],
            ]
        );

        // Send to all admin users
        $adminUsers = User::where('role', 'admin')->get();
        $this->info('Sending report to '.$adminUsers->count().' admin(s)...');

        foreach ($adminUsers as $admin) {
            $admin->notify(new MonthlySalesReportNotification($report));
        }

        $specificEmail = config('services.admin.notification_email');
        if ($specificEmail && filter_var($specificEmail, FILTER_VALIDATE_EMAIL)) {
            $this->info('Sending report to '.$specificEmail.'...');
            Notification::route('mail', $specificEmail)
                ->notify(new MonthlySalesReportNotification($report));
        }

        $this->info('Monthly sales report sent successfully!');

        return Command::SUCCESS;
    }
}
