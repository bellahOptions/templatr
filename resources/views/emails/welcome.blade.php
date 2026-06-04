@extends('emails.layout')

@section('title', 'Welcome to Templatr!')

@section('content')
    <div style="text-align:center;">
        <div style="font-size:56px; margin-bottom:16px;">👋</div>
        <h1>Welcome to Templatr, {{ $user->name }}!</h1>
        <p>We're thrilled to have you join our community of creators and innovators. Get ready to discover premium digital resources at unbeatable prices.</p>
    </div>

    <div class="features-grid">
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

    <div class="info-card" style="text-align:center;">
        <h2 style="font-size:18px; color:#111827; margin:0 0 8px;">Ready to explore?</h2>
        <p>Browse thousands of templates, graphics, fonts, and more.</p>
        <a href="{{ route('products.index') }}" class="btn-primary">Start Exploring</a>
    </div>

    @if(isset($referralLink) && $referralLink)
    <div style="background:#fefce8; border-radius:12px; padding:20px; text-align:center; margin-top:20px;">
        <p style="font-size:14px; font-weight:600; color:#92400e; margin:0 0 8px;">🎉 Share & Earn Coins!</p>
        <p style="font-size:13px; color:#92400e; margin:0 0 8px;">Refer a friend and earn rewards when they make their first purchase.</p>
        <div style="background:#fff; border:1px dashed #f59e0b; border-radius:8px; padding:8px 16px; display:inline-block; font-size:13px; color:#92400e; font-weight:500;">
            {{ $referralLink }}
        </div>
    </div>
    @endif
@endsection

@section('footer_email', $user->email)
