@extends('layouts.app')

@section('title', 'Two-Factor Verification - Templatr')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-red-50 rounded-3xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m0 0v2m0-2h2m-2 0H10m9.364-7.364A9 9 0 1112 3a9 9 0 017.364 4.636z"/>
                </svg>
            </div>
            <h2 class="text-3xl font-bold text-gray-900">Two-Factor Verification</h2>
            <p class="mt-3 text-gray-600 text-sm leading-relaxed">
                A verification code has been sent to your email. Enter it below to access the admin area.
            </p>
        </div>

        <div class="bg-white py-8 px-6 shadow-xl rounded-2xl border border-gray-100">
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm mb-6 flex items-center">
                    <svg class="w-5 h-5 text-red-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm mb-6 flex items-center">
                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.2fa.verify') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="token" class="block text-sm font-medium text-gray-700 mb-2 text-center">
                        Verification Code
                    </label>
                    <input id="token" type="text" name="token" inputmode="numeric" autocomplete="one-time-code"
                        maxlength="6" required autofocus
                        class="block w-full text-center text-2xl tracking-[0.5em] font-bold px-4 py-4 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent @error('token') border-red-500 @enderror"
                        placeholder="000000">
                    @error('token')
                        <p class="text-red-500 text-xs mt-1 text-center">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full bg-red-600 text-white py-3 px-4 rounded-xl text-sm font-semibold hover:bg-red-700 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    Verify & Sign In
                </button>
            </form>

            <div class="mt-6 space-y-3">
                <form method="POST" action="{{ route('admin.2fa.send') }}">
                    @csrf
                    <button type="submit" class="w-full text-center text-sm text-gray-500 hover:text-gray-700 transition-colors">
                        Resend verification code
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.2fa.cancel') }}">
                    @csrf
                    <button type="submit" class="w-full text-center text-sm text-red-500 hover:text-red-700 transition-colors">
                        Cancel & return to sign in
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
