@extends('emails.layout')

@section('title', 'Password Changed - Templatr')

@section('content')
    <div style="text-align:center;">
        <div style="font-size:56px; margin-bottom:16px;">🔒</div>
        <h1>Password Changed Successfully</h1>
        <p>Hi <strong>{{ $user->name }}</strong>, your Templatr account password was successfully changed.</p>
    </div>

    <div class="info-card" style="background:#fef2f2; border:1px solid #fecaca;">
        <p style="font-size:14px; font-weight:600; color:#991b1b; margin:0 0 8px;">
            ⚠️ If you did NOT change your password
        </p>
        <ul style="font-size:13px; color:#991b1b; margin:0; padding-left:20px;">
            <li style="margin-bottom:4px;">Reset your password using the "Forgot Password" link on the login page.</li>
            <li>Contact support immediately if you need assistance.</li>
        </ul>
    </div>

    @if(isset($actionUrl))
    <div style="text-align:center; margin-top: 16px;">
        <a href="{{ $actionUrl }}" class="btn-primary">Go to Templatr</a>
    </div>
    @endif

    <p style="margin-top:24px; font-size:13px; color:#9ca3af;">Thank you for using Templatr!</p>
@endsection

@section('footer_email', $user->email)
