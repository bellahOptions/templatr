<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Notification - Templatr</title>
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
        .notification-card {
            background: #f9fafb;
            border-radius: 16px;
            padding: 24px;
            margin: 24px 0;
            text-align: left;
        }
        .notification-card .icon {
            font-size: 32px;
            margin-bottom: 12px;
        }
        .notification-card h3 {
            font-size: 16px;
            font-weight: 700;
            color: #111827;
            margin: 0 0 8px;
        }
        .notification-card .message {
            font-size: 14px;
            color: #6b7280;
            line-height: 1.6;
            margin: 0;
        }
        .btn-action {
            display: inline-block;
            background: #000;
            color: #fff;
            padding: 14px 32px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
        }
        .btn-action:hover { background: #1f2937; }
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
            <div style="font-size:56px; margin-bottom:16px;">🔔</div>
            <h1>{{ $notification->title ?? 'New Notification' }}</h1>
            <p>Hi <strong>{{ $user->name }}</strong>, you have a new notification from Templatr.</p>

            <div class="notification-card">
                <div class="icon">{{ $notification->icon ?? '📌' }}</div>
                <h3>{{ $notification->title ?? 'Notification' }}</h3>
                <p class="message">{{ $notification->message ?? '' }}</p>
            </div>

            @if($notification->action_url ?? false)
            <a href="{{ $notification->action_url }}" class="btn-action">
                {{ $notification->action_text ?? 'View Details' }}
            </a>
            @endif

            <p style="margin-top:24px; font-size:13px; color:#9ca3af;">
                You can manage your notification preferences in your account settings.
            </p>
        </div>
        <div class="email-footer">
            <p style="margin:0 0 4px;">Templatr — A product of <a href="https://www.bellahoptions.com">Bellah Options</a></p>
            <p style="margin:0;">{{ $user->email }} · <a href="{{ route('profile.edit') }}">Preferences</a></p>
        </div>
    </div>
</body>
</html>
