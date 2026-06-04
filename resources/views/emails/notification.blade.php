@extends('emails.layout')

@section('title', 'New Notification - Templatr')

@section('content')
    <div style="text-align:center;">
        <div style="font-size:56px; margin-bottom:16px;">🔔</div>
        <h1>{{ $title ?? 'New Notification' }}</h1>
        <p>Hi <strong>{{ $user->name }}</strong>, you have a new notification from Templatr.</p>
    </div>

    <div class="info-card">
        <div style="font-size:32px; margin-bottom:12px;">{{ $icon ?? '📌' }}</div>
        <h3 style="font-size:16px; font-weight:700; color:#111827; margin:0 0 8px;">{{ $title ?? 'Notification' }}</h3>
        <p style="font-size:14px; color:#6b7280; line-height:1.6; margin:0;">{{ $message ?? '' }}</p>
    </div>

    @if(isset($actionUrl) && $actionUrl)
    <div style="text-align:center;">
        <a href="{{ $actionUrl }}" class="btn-primary">
            {{ $actionText ?? 'View Details' }}
        </a>
    </div>
    @endif

    <p style="margin-top:24px; font-size:13px; color:#9ca3af;">
        You can manage your notification preferences in your account settings.
    </p>
@endsection

@section('footer_email', $user->email)
