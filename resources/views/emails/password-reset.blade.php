@extends('emails.layout')

@section('title', 'Reset Your Password - Templatr')

@section('content')
    <div style="text-align:center;">
        <div style="font-size:56px; margin-bottom:16px;">🔐</div>
        <h1>Reset Your Password</h1>
        <p>We received a request to reset the password for your Templatr account.</p>
    </div>

    <div style="text-align:center; margin: 24px 0;">
        <a href="{{ $resetUrl }}" class="btn-primary">Reset Password</a>
    </div>

    <p style="font-size:14px; color:#6b7280;">
        This password reset link will expire in 60 minutes. If you did not request a password reset, no further action is required.
    </p>

    <div style="margin-top:24px; padding-top:24px; border-top:1px solid #e5e7eb;">
        <p style="font-size:14px; color:#6b7280; margin:0;">
            If you're having trouble clicking the button, copy and paste the URL below into your web browser:
        </p>
        <p style="font-size:12px; color:#9ca3af; word-break:break-all; margin-top:8px;">{{ $resetUrl }}</p>
    </div>
@endsection

@section('footer_email', $user->email)
