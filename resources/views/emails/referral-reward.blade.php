@extends('emails.layout')

@section('title', 'You Earned Coins! - Templatr')

@section('content')
    <div style="text-align:center;">
        <div style="font-size:56px; margin-bottom:16px;">🎊</div>
        <h1>You Earned Rewards!</h1>
        <p>Great news, <strong>{{ $user->name }}</strong>!</p>
    </div>

    <div class="info-card" style="text-align:center;">
        <div style="font-size:48px; margin-bottom:12px;">🪙</div>
        <h3 style="font-size:18px; font-weight:700; color:#111827; margin:0 0 8px;">
            +{{ $coins }} Coins
        </h3>
        <p style="font-size:14px; color:#6b7280; margin:0;">
            @if(isset($referredUser))
                Your referral <strong>{{ $referredUser }}</strong> just made a purchase!
            @else
                Someone you referred just made a purchase!
            @endif
        </p>
    </div>

    <p style="text-align:center;">
        Keep sharing your referral link to earn more coins. Each successful referral earns you <strong>10 coins</strong> when they make their first purchase.
    </p>

    @if(isset($referralLink))
    <div style="background:#fefce8; border-radius:12px; padding:20px; text-align:center; margin-top:20px;">
        <p style="font-size:13px; font-weight:600; color:#92400e; margin:0 0 8px;">Your Referral Link</p>
        <div style="background:#fff; border:1px dashed #f59e0b; border-radius:8px; padding:8px 16px; display:inline-block; font-size:13px; color:#92400e; font-weight:500;">
            {{ $referralLink }}
        </div>
    </div>
    @endif

    <div style="text-align:center; margin-top:24px;">
        <a href="{{ route('affiliate.index') }}" class="btn-primary">View Your Rewards</a>
    </div>
@endsection

@section('footer_email', $user->email)
