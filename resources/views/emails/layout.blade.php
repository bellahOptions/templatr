<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Templatr')</title>
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
        .btn-primary {
            display: inline-block;
            background: #000;
            color: #fff !important;
            padding: 14px 32px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
        }
        .btn-primary:hover { background: #1f2937; }
        .code-display {
            background: #f9fafb;
            border: 2px dashed #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            margin: 24px 0;
            font-size: 32px;
            font-weight: 700;
            letter-spacing: 8px;
            color: #111827;
        }
        .info-card {
            background: #f9fafb;
            border-radius: 12px;
            padding: 20px;
            margin: 24px 0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 14px;
        }
        .info-row .label { color: #6b7280; }
        .info-row .value { color: #111827; font-weight: 600; }
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
        .items-table td:last-child { text-align: right; font-weight: 600; }
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
        .email-footer {
            padding: 24px 40px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 13px;
            color: #9ca3af;
        }
        .email-footer a { color: #f59e0b; text-decoration: none; }
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
        .features-grid {
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
        @media only screen and (max-width: 480px) {
            .features-grid { grid-template-columns: 1fr; }
            .email-body { padding: 24px; }
            .email-header { padding: 24px; }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-header">
            <img src="{{ asset('templatr.svg') }}" alt="Templatr">
        </div>
        <div class="email-body">
            @yield('content')
        </div>
        <div class="email-footer">
            <p style="margin:0 0 4px;">
                <strong>Templatr</strong> — A product of <a href="https://www.bellahoptions.com">Bellah Options</a>
            </p>
            <p style="margin:0;">
                @yield('footer_email', '')
                @hasSection('footer_email') · @endif
                <a href="{{ url('/') }}">Visit Templatr</a>
            </p>
        </div>
    </div>
</body>
</html>
