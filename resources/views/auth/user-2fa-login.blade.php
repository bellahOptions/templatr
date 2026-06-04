@extends('layouts.app')

@section('title', 'Verify Your Login - Templatr')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-[#FFC300]/10 rounded-3xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-[#FFC300]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h2 class="text-3xl font-bold text-gray-900">Two-Factor Authentication</h2>
            <p class="mt-3 text-gray-600 text-sm leading-relaxed">
                A verification code has been sent to <strong class="text-gray-900">{{ auth()->user()->email }}</strong>.
                Please enter it below to complete your login.
            </p>
        </div>

        <div class="bg-white py-8 px-6 shadow-xl rounded-2xl border border-gray-100">
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm mb-6">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('profile.2fa.login.verify') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-2 text-center">
                        Verification Code
                    </label>
                    <div class="flex justify-center">
                        <input id="code" type="text" name="code" maxlength="6" inputmode="numeric" autocomplete="one-time-code"
                               placeholder="000000"
                               class="w-48 text-center text-3xl font-bold tracking-[10px] border border-gray-300 rounded-xl px-4 py-4 focus:outline-none focus:ring-2 focus:ring-[#FFC300] @error('code') border-red-500 @enderror"
                               required autofocus>
                    </div>
                    @error('code')
                        <p class="text-red-500 text-xs mt-2 text-center">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <button type="submit"
                            class="w-full flex justify-center py-3.5 px-4 bg-black text-white rounded-xl text-sm font-semibold hover:bg-gray-800 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#FFC300]">
                        Verify & Sign In
                    </button>
                </div>
            </form>

            <div class="mt-6 pt-6 border-t border-gray-100">
                <div class="bg-gray-50 rounded-xl p-4 text-center">
                    <p class="text-xs text-gray-500">
                        Didn't receive the code? A new one is sent automatically when you visit this page. 
                        Check your spam folder or wait a moment.
                    </p>
                </div>
            </div>

            <div class="mt-4 text-center">
                <a href="{{ route('profile.2fa.login.resend') }}" class="text-sm text-[#FFC300] font-semibold hover:text-[#E6B000] transition-colors">
                    Resend Code
                </a>
                <span class="text-gray-300 mx-2">·</span>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-sm text-gray-500 hover:text-gray-700 transition-colors">
                        Sign Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
