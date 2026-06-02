<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - CreativeMarket</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f9fafb;
            margin: 0;
            padding: 0;
        }
        .email-wrapper {
            max-width: 560px;
            margin: 40px auto;
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
        .email-header img { height: 36px; max-width: 160px; }
        .email-body { padding: 40px; }
        .email-body h1 {
            font-size: 22px; font-weight: 700; color: #111827; margin: 0 0 8px;
        }
        .email-body p {
            font-size: 15px; color: #6b7280; line-height: 1.6; margin: 0 0 24px;
        }
        .order-summary {
            background: #f9fafb;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 14px;
        }
        .detail-row .label { color: #6b7280; }
        .detail-row .value { color: #111827; font-weight: 600; }
        .product-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .product-list li {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
            font-size: 14px;
        }
        .product-list li:last-child { border-bottom: none; }
        .product-list .title { font-weight: 600; color: #111827; }
        .product-list .price { color: #374151; }
        .total-line {
            display: flex;
            justify-content: space-between;
            padding: 16px 0 0;
            margin-top: 8px;
            border-top: 2px solid #000;
            font-size: 16px;
            font-weight: 700;
            color: #111827;
        }
        .btn-orders {
            display: inline-block;
            background: #000;
            color: #fff;
            padding: 14px 32px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
        }
        .btn-orders:hover { background: #1f2937; }
        .email-footer {
            padding: 24px 40px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 13px;
            color: #9ca3af;
        }
        .email-footer a { color: #f59e0b; text-decoration: none; }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-header">
            <img src="{{ asset('templatr.svg') }}" alt="CreativeMarket">
        </div>
        <div class="email-body">
            <h1>Order Confirmed ✅</h1>
            <p>Hi <strong>{{ $order->user->name }}</strong>, your order has been confirmed and is being processed.</p>

            <div class="order-summary">
                <div class="detail-row">
                    <span class="label">Order</span>
                    <span class="value">#{{ $order->order_number }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Date</span>
                    <span class="value">{{ $order->created_at->format('M j, Y g:i A') }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Status</span>
                    <span class="value" style="color:#059669;">{{ ucfirst($order->status) }}</span>
                </div>
            </div>

            <h2 style="font-size:16px; font-weight:700; color:#111827; margin:0 0 12px;">Summary</h2>
            <ul class="product-list">
                @foreach($order->items as $item)
                <li>
                    <span class="title">{{ $item->product->title }}</span>
                    <span class="price">{{ App\Helpers\CurrencyHelper::format($item->price) }}</span>
                </li>
                @endforeach
            </ul>
            <div class="total-line">
                <span>Total</span>
                <span>{{ App\Helpers\CurrencyHelper::format($order->total_amount) }}</span>
            </div>

            <div style="text-align:center; margin-top:28px;">
                <a href="{{ route('orders.index') }}" class="btn-orders">View My Orders</a>
            </div>
        </div>
        <div class="email-footer">
            <p style="margin:0 0 4px;">CreativeMarket — A product of <a href="https://www.bellahoptions.com">Bellah Options</a></p>
            <p style="margin:0;">{{ $order->user->email }} · <a href="mailto:support@bellahoptions.com">Support</a></p>
        </div>
    </div>
</body>
</html>
