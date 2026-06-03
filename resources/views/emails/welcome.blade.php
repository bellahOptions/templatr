<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Templatr!</title>
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
        .cta-box {
            text-align: center;
            padding: 32px;
            background: #f9fafb;
            border-radius: 16px;
            margin: 24px 0;
        }
        .cta-box h2 {
            font-size: 18px; color: #111827; margin: 0 0 8px;
        }
        .btn-primary {
            display: inline-block;
            background: #000;
            color: #fff;
            padding: 14px 36px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            margin-top: 12px;
        }
        .btn-primary:hover { background: #1f2937; }
        .features {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin: 24px 0;
        }
        .feature-card {
            background: #f9fafb;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }
        .feature-card .icon { font-size: 28px; margin-bottom: 8px; }
        .feature-card h3 { font-size: 14px; font-weight: 600; color: #111827; margin: 0 0 4px; }
        .feature-card p { font-size: 12px; color: #6b7280; margin: 0; line-height: 1.4; }
        .email-footer {
            padding: 24px 40px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 13px;
            color: #9ca3af;
        }
        .email-footer a { color: #f59e0b; text-decoration: none; }
        @media only screen and (max-width: 480px) {
            .features { grid-template-columns: 1fr; }
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
                <div style="font-size:56px; margin-bottom:16px;">👋</div>
                <h1>Welcome to Templatr, {{ $user->name }}!</h1>
                <p>We're thrilled to have you join our community of creators and innovators. Get ready to discover premium digital resources at unbeatable prices.</p>
            </div>

            <div class="features">
                <div class="feature-card">
                    <div class="icon">🎨</div>
                    <h3>Premium Resources</h3>
                    <p>Curated digital assets starting from just ₦3,000</p>
                </div>
                <div class="feature-card">
                    <div class="icon">⚡</div>
                    <h3>Instant Downloads</h3>
                    <p>Get your purchases immediately after payment</p>
                </div>
                <div class="feature-card">
                    <div class="icon">🎁</div>
                    <h3>Referral Rewards</h3>
                    <p>Earn coins for every friend you refer</p>
                </div>
                <div class="feature-card">
                    <div class="icon">🛡️</div>
                    <h3>Secure Checkout</h3>
                    <p>Safe & encrypted payment processing</p>
                </div>
            </div>

            <div class="cta-box">
                <h2>Ready to explore?</h2>
                <p>Browse thousands of templates, graphics, fonts, and more.</p>
                <a href="{{ route('products.index') }}" class="btn-primary">Start Exploring</a>
            </div>

            @if($referralLink ?? false)
            <div style="background:#fefce8; border-radius:12px; padding:20px; text-align:center; margin-top:20px;">
                <p style="font-size:14px; font-weight:600; color:#92400e; margin:0 0 8px;">🎉 Share & Earn Coins!</p>
                <p style="font-size:13px; color:#92400e; margin:0 0 8px;">Refer a friend and earn rewards when they make their first purchase.</p>
                <div style="background:#fff; border:1px dashed #f59e0b; border-radius:8px; padding:8px 16px; display:inline-block; font-size:13px; color:#92400e; font-weight:500;">
                    {{ $referralLink }}
                </div>
            </div>
            @endif
        </div>
        <div class="email-footer">
            <p style="margin:0 0 4px;">Templatr — A product of <a href="https://www.bellahoptions.com">Bellah Options</a></p>
            <p style="margin:0;">{{ $user->email }} · <a href="{{ route('profile.edit') }}">Manage preferences</a></p>
        </div>
    </div>
</body>
</html>
