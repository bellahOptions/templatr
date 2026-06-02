<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>You earned a referral reward! - CreativeMarket</title>
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
        .email-body { padding: 40px; text-align: center; }
        .email-body h1 {
            font-size: 24px; font-weight: 700; color: #111827; margin: 0 0 8px;
        }
        .email-body p {
            font-size: 15px; color: #6b7280; line-height: 1.6; margin: 0 0 24px;
        }
        .reward-box {
            background: #fefce8;
            border: 2px solid #fde68a;
            border-radius: 20px;
            padding: 32px;
            margin: 24px 0;
        }
        .reward-amount {
            font-size: 48px;
            font-weight: 800;
            color: #f59e0b;
            line-height: 1;
        }
        .reward-label {
            font-size: 14px;
            color: #92400e;
            margin-top: 4px;
        }
        .btn-redeem {
            display: inline-block;
            background: #000;
            color: #fff;
            padding: 14px 36px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
        }
        .btn-redeem:hover { background: #1f2937; }
        .referral-stats {
            display: flex;
            justify-content: center;
            gap: 32px;
            margin: 24px 0;
        }
        .stat-item { text-align: center; }
        .stat-number { font-size: 24px; font-weight: 700; color: #111827; }
        .stat-label { font-size: 12px; color: #6b7280; }
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
            <div style="font-size:56px; margin-bottom:16px;">🎉</div>
            <h1>You earned a referral reward!</h1>
            <p>Great news, <strong>{{ $user->name }}</strong>! Someone you referred just made a purchase.</p>

            <div class="reward-box">
                <div class="reward-amount">+{{ $coins }} coins</div>
                <div class="reward-label">≈ {{ App\Helpers\CurrencyHelper::format($value ?? 0) }} value</div>
            </div>

            @if(isset($referralName))
            <p style="font-size:14px; color:#6b7280;">
                Referred user: <strong>{{ $referralName }}</strong>
            </p>
            @endif

            <div class="referral-stats">
                <div class="stat-item">
                    <div class="stat-number">{{ $totalReferrals ?? 0 }}</div>
                    <div class="stat-label">Total Referrals</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">{{ $totalCoins ?? 0 }}</div>
                    <div class="stat-label">Total Coins</div>
                </div>
            </div>

            <a href="{{ route('affiliate.index') }}" class="btn-redeem">View Referral Dashboard</a>

            <p style="margin-top:24px; font-size:14px; color:#9ca3af;">
                Keep sharing your referral link to earn more coins!
            </p>
        </div>
        <div class="email-footer">
            <p style="margin:0 0 4px;">CreativeMarket — A product of <a href="https://www.bellahoptions.com">Bellah Options</a></p>
            <p style="margin:0;">{{ $user->email }} · <a href="{{ route('affiliate.index') }}">Referral Dashboard</a></p>
        </div>
    </div>
</body>
</html>
