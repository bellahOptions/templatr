<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Receipt - CreativeMarket</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f9fafb;
            margin: 0;
            padding: 0;
        }
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }
        .email-header {
            background: #000;
            padding: 32px 40px;
            text-align: center;
        }
        .email-header img {
            height: 36px;
            max-width: 160px;
        }
        .email-body {
            padding: 40px;
        }
        .email-body h1 {
            font-size: 22px;
            font-weight: 700;
            color: #111827;
            margin: 0 0 8px;
        }
        .email-body p {
            font-size: 15px;
            color: #6b7280;
            line-height: 1.6;
            margin: 0 0 24px;
        }
        .order-info {
            background: #f9fafb;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .order-info-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 14px;
        }
        .order-info-row .label {
            color: #6b7280;
        }
        .order-info-row .value {
            color: #111827;
            font-weight: 600;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        .items-table th {
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #9ca3af;
            padding: 8px 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 14px;
            color: #374151;
        }
        .items-table td:last-child {
            text-align: right;
            font-weight: 600;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-top: 2px solid #000;
            margin-top: 8px;
            font-size: 16px;
            font-weight: 700;
            color: #111827;
        }
        .btn-download {
            display: inline-block;
            background: #000;
            color: #fff;
            padding: 14px 32px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            margin-top: 8px;
        }
        .btn-download:hover {
            background: #1f2937;
        }
        .email-footer {
            padding: 24px 40px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 13px;
            color: #9ca3af;
        }
        .email-footer a {
            color: #f59e0b;
            text-decoration: none;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-completed { background: #d1fae5; color: #065f46; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-failed { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-header">
            <img src="{{ asset('templatr.svg') }}" alt="CreativeMarket">
        </div>
        <div class="email-body">
            <h1>🎉 Order Confirmed!</h1>
            <p>Thank you, <strong>{{ $order->customer_name }}</strong>! Your order has been placed successfully.</p>

            <div class="order-info">
                <div class="order-info-row">
                    <span class="label">Order #</span>
                    <span class="value">{{ $order->order_number }}</span>
                </div>
                <div class="order-info-row">
                    <span class="label">Date</span>
                    <span class="value">{{ $order->created_at->format('F j, Y \a\t g:i A') }}</span>
                </div>
                <div class="order-info-row">
                    <span class="label">Customer</span>
                    <span class="value">{{ $order->customer_name }}</span>
                </div>
                <div class="order-info-row">
                    <span class="label">Email</span>
                    <span class="value">{{ $order->customer_email }}</span>
                </div>
                <div class="order-info-row">
                    <span class="label">Status</span>
                    <span class="value">
                        <span class="status-badge status-{{ $order->status === 'completed' ? 'completed' : ($order->status === 'pending' ? 'pending' : 'failed') }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </span>
                </div>
                <div class="order-info-row">
                    <span class="label">Payment</span>
                    <span class="value">{{ ucfirst($order->payment_method) }} — {{ ucfirst($order->payment_status) }}</span>
                </div>
            </div>

            <h2 style="font-size:16px; font-weight:700; color:#111827; margin:0 0 12px;">Items Purchased</h2>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Type</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->product->title }}</strong>
                            @if($item->product->version)
                                <br><span style="font-size:12px; color:#9ca3af;">v{{ $item->product->version }}</span>
                            @endif
                        </td>
                        <td>{{ ucfirst($item->product->file_type) }}</td>
                        <td>{{ App\Helpers\CurrencyHelper::format($item->price) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="total-row">
                <span>Total Paid</span>
                <span>{{ App\Helpers\CurrencyHelper::format($order->total_amount) }}</span>
            </div>

            <div style="text-align:center; margin-top: 24px;">
                <a href="{{ route('orders.show', $order) }}" class="btn-download">
                    <svg style="display:inline; width:18px; height:18px; vertical-align:middle; margin-right:8px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Download Your Items
                </a>
            </div>

            <div style="margin-top:32px; padding-top:24px; border-top:1px solid #e5e7eb;">
                <p style="font-size:14px; color:#6b7280; margin:0;">
                    Need help? <a href="mailto:support@bellahoptions.com" style="color:#f59e0b;">Contact Support</a>
                </p>
            </div>
        </div>
        <div class="email-footer">
            <p style="margin:0 0 8px;">
                <strong>CreativeMarket</strong> — A product of <a href="https://www.bellahoptions.com">Bellah Options</a>
            </p>
            <p style="margin:0;">This email was sent to {{ $order->customer_email }}. If you didn't make this purchase, please contact us immediately.</p>
        </div>
    </div>
</body>
</html>
