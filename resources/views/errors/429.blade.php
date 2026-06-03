<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>429 - Too Many Requests | Templatr</title>
    <link rel="icon" href="/favicon.ico">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #000; color: #fff; min-height: 100vh; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .bg-grid { position: fixed; inset: 0; opacity: 0.05; background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23FFC300' fill-opacity='0.4'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E"); pointer-events: none; }
        .glow { position: fixed; width: 600px; height: 600px; border-radius: 50%; background: radial-gradient(circle, rgba(255,195,0,0.08) 0%, transparent 70%); top: 50%; left: 50%; transform: translate(-50%, -50%); pointer-events: none; }
        .container { position: relative; text-align: center; padding: 2rem; max-width: 480px; }
        h1 { font-size: 8rem; font-weight: 800; background: linear-gradient(135deg, #FFC300, #FFD633); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; line-height: 1; margin-bottom: 0.5rem; }
        .subtitle { font-size: 1.25rem; font-weight: 600; margin-bottom: 0.75rem; }
        .desc { color: #9CA3AF; font-size: 0.95rem; line-height: 1.7; margin-bottom: 2rem; }
        .btn-group { display: flex; flex-wrap: wrap; gap: 1rem; justify-content: center; }
        .btn { display: inline-flex; align-items: center; padding: 0.875rem 2rem; border-radius: 0.75rem; font-weight: 600; font-size: 0.9rem; text-decoration: none; transition: all 0.3s; }
        .btn-primary { background: #FFC300; color: #000; }
        .btn-primary:hover { background: #FFD633; transform: translateY(-2px); box-shadow: 0 10px 30px rgba(255,195,0,0.25); }
        .btn-secondary { background: transparent; color: #fff; border: 1px solid #374151; }
        .btn-secondary:hover { border-color: #FFC300; color: #FFC300; }
        .icon { margin-right: 0.5rem; }
        .footer-text { margin-top: 2rem; color: #6B7280; font-size: 0.8rem; }
    </style>
</head>
<body>
    <div class="bg-grid"></div>
    <div class="glow"></div>
    <div class="container">
        <h1>429</h1>
        <p class="subtitle">Too Many Requests</p>
        <p class="desc">You've sent too many requests. Please slow down and try again shortly.</p>
        <div class="btn-group">
            <a href="{{ url('/') }}" class="btn btn-primary">
                <svg class="icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Go Home
            </a>
            <a href="javascript:history.back()" class="btn btn-secondary">Try Again</a>
        </div>
        <p class="footer-text">&copy; {{ date('Y') }} Templatr. Premium Creative &amp; Web Resources.</p>
    </div>
</body>
</html>