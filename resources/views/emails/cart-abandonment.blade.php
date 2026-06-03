<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>You left something behind! - Templatr</title>
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
            font-size: 24px; font-weight: 700; color: #111827; margin: 0 0 8px;
        }
        .email-body p {
            font-size: 15px; color: #6b7280; line-height: 1.6; margin: 0 0 24px;
        }
        .cart-items {
            margin-bottom: 24px;
        }
        .cart-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px;
            background: #f9fafb;
            border-radius: 12px;
            margin-bottom: 12px;
        }
        .cart-item-icon {
            width: 48px;
            height: 48px;
            background: #fef3c7;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .cart-item-info { flex: 1; }
        .cart-item-info h3 {
            font-size: 15px; font-weight: 600; color: #111827; margin: 0 0 2px;
        }
        .cart-item-info .meta {
            font-size: 12px; color: #9ca3af;
        }
        .cart-item-price { font-weight: 700; color: #111827; font-size: 15px; }
        .price-pulse {
            color: #f59e0b; font-weight: 700;
        }
        .btn-checkout {
            display: inline-block;
            background: #000;
            color: #fff;
            padding: 14px 36px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            transition: background 0.2s;
        }
        .btn-checkout:hover { background: #1f2937; }
        .btn-continue {
            display: inline-block;
            border: 1px solid #d1d5db;
            color: #374151;
            padding: 13px 36px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            margin-left: 12px;
        }
        .email-footer {
            padding: 24px 40px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 13px;
            color: #9ca3af;
        }
        .email-footer a { color: #f59e0b; text-decoration: none; }
        .urgency-badge {
            display: inline-block;
            background: #fef3c7;
            color: #92400e;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        @media only screen and (max-width: 480px) {
            .cart-item { flex-wrap: wrap; }
            .btn-continue { margin-left: 0; margin-top: 8px; display: inline-block; }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-header">
            <img src="{{ asset('templatr.svg') }}" alt="Templatr">
        </div>
        <div class="email-body">
            <div style="text-align:center;">
                <div style="font-size:56px; margin-bottom:16px;">🛒</div>
                <h1>You left something behind!</h1>
                <p>Hey <strong>{{ $user->name }}</strong>, we noticed you left some items in your cart. Don't miss out!</p>
                <div class="urgency-badge">⏳ Items in your cart are reserved</div>
            </div>

            <div class="cart-items">
                @foreach($cartItems as $item)
                <div class="cart-item">
                    <div class="cart-item-icon">
                        @php
                            $icons = [
                                'graphic' => '🎨', 'template' => '📄', 'audio' => '🎵',
                                'video' => '🎬', 'font' => '🔤', 'plugin' => '🔌', '3d' => '🧊'
                            ];
                            $icon = $icons[$item['file_type'] ?? 'template'] ?? '📄';
                        @endphp
                        <span style="font-size:24px;">{{ $icon }}</span>
                    </div>
                    <div class="cart-item-info">
                        <h3>{{ $item['title'] ?? 'Digital Product' }}</h3>
                        <div class="meta">{{ $item['type'] ?? 'Digital Download' }}</div>
                    </div>
                    <div class="cart-item-price">
                        {{ App\Helpers\CurrencyHelper::format($item['price'] ?? 0) }}
                    </div>
                </div>
                @endforeach
            </div>

            <div style="text-align:center; margin: 28px 0;">
                <a href="{{ route('cart.index') }}" class="btn-checkout">Complete Your Order</a>
                <a href="{{ route('products.index') }}" class="btn-continue">Continue Shopping</a>
            </div>

            <div style="background:#fefce8; border-radius:12px; padding:16px; text-align:center; margin-top: 24px;">
                <p style="font-size:14px; color:#92400e; margin:0; font-weight:500;">
                    💡 Pro Tip: Complete your purchase now and get instant access to your downloads!
                </p>
            </div>

            <div style="margin-top:24px; padding-top:20px; border-top:1px solid #e5e7eb;">
                <p style="font-size:13px; color:#9ca3af; text-align:center; margin:0;">
                    If you have any questions, reply to this email or <a href="mailto:support@bellahoptions.com">contact support</a>.
                </p>
            </div>
        </div>
        <div class="email-footer">
            <p style="margin:0 0 4px;">Templatr — A product of <a href="https://www.bellahoptions.com">Bellah Options</a></p>
            <p style="margin:0;">{{ $user->email }}</p>
        </div>
    </div>
</body>
</html>
