@extends('emails.layout')

@section('title', 'Verify Your Email - Templatr')

@section('content')
    <div style="text-align:center;">
        <div style="font-size:56px; margin-bottom:16px;">✉️</div>
        <h1>Verify Your Email Address</h1>
        <p>Hi <strong>{{ $user->name }}</strong>, thank you for creating an account on Templatr!</p>
    </div>

    <p>Please click the button below to verify your email address and get full access to your account.</p>

    <div style="text-align:center; margin: 32px 0;">
        <a href="{{ $verificationUrl }}" class="btn-primary">Verify Email Address</a>
    </div>

    <p style="font-size:14px; color:#6b7280;">This verification link will expire in 60 minutes.</p>

    <div style="margin-top:24px; padding-top:24px; border-top:1px solid #e5e7eb;">
        <p style="font-size:13px; color:#9ca3af; margin:0;">
            If you did not create an account, no further action is required.
        </p>
        <p style="font-size:12px; color:#9ca3af; word-break:break-all; margin-top:8px;">
            If you're having trouble clicking the button, copy and paste this URL into your browser:<br>
            <span style="color:#6b7280;">{{ $verificationUrl }}</span>
        </p>
    </div>
@endsection

@section('footer_email', $user->email)
