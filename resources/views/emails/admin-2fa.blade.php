@extends('emails.layout')

@section('title', 'Admin Login Verification - Templatr')

@section('content')
    <div style="text-align:center;">
        <div style="font-size:56px; margin-bottom:16px;">🔐</div>
        <h1>Admin Login Verification</h1>
        <p>Hi <strong>{{ $user->name }}</strong>, you are attempting to access the Templatr admin area.</p>
    </div>

    <p>Your one-time verification code is:</p>

    <div class="code-display">{{ $code }}</div>

    <p style="font-size:14px; color:#6b7280; text-align:center;">This code will expire in 10 minutes.</p>

    <div style="text-align:center; margin-top: 16px;">
        <a href="{{ $actionUrl }}" class="btn-primary">Go to Verification Page</a>
    </div>

    <div style="margin-top:24px; padding-top:24px; border-top:1px solid #e5e7eb;">
        <p style="font-size:14px; color:#ef4444; font-weight:600; margin:0;">
            ⚠️ If you did not attempt this login, please secure your account immediately.
        </p>
    </div>
@endsection

@section('footer_email', $user->email)
