<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password - Templatr</title>
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
            font-size: 22px; font-weight: 700; color: #111827; margin: 0 0 8px;
        }
        .email-body p {
            font-size: 15px; color: #6b7280; line-height: 1.6; margin: 0 0 24px;
        }
        .btn-reset {
            display: inline-block;
            background: #000;
            color: #fff;
            padding: 14px 36px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
        }
        .btn-reset:hover { background: #1f2937; }
        .expiry-notice {
            background: #fef3c7;
            border-radius: 8px;
            padding: 12px;
            font-size: 13px;
            color: #92400e;
            margin: 24px 0;
        }
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
            <img src="{{ asset('templatr.svg') }}" alt="Templatr">
        </div>
        <div class="email-body">
            <div style="font-size:56px; margin-bottom:16px;">🔐</div>
            <h1>Password Reset Request</h1>
            <p>Hi <strong>{{ $user->name ?? 'there' }}</strong>, we received a request to reset your password. Click the button below to set a new one.</p>

            <a href="{{ $resetUrl ?? '#' }}" class="btn-reset">Reset My Password</a>

            <div class="expiry-notice">
                ⏰ This password reset link will expire in {{ $expiresIn ?? '60 minutes' }}.
            </div>

            <p style="font-size:14px; color:#9ca3af;">
                If you didn't request this, you can safely ignore this email. No changes have been made to your account.
            </p>
        </div>
        <div class="email-footer">
            <p style="margin:0 0 4px;">Templatr — A product of <a href="https://www.bellahoptions.com">Bellah Options</a></p>
            <p style="margin:0;"><a href="mailto:support@bellahoptions.com">Contact Support</a> if you need help.</p>
        </div>
    </div>
</body>
</html>
