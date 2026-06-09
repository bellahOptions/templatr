@extends('layouts.app')

@section('title', 'Forgot Password - Templatr')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-[#FFC300] rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            <h2 class="text-3xl font-bold text-gray-900">Forgot your password?</h2>
            <p class="mt-2 text-gray-600">Enter your email and we'll send you a reset link.</p>

            @if(session('status'))
                <div class="mt-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm flex items-center">
                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('status') }}
                </div>
            @endif
        </div>

        <div class="bg-white py-8 px-6 shadow-xl rounded-2xl border border-gray-100">
            <form method="POST" action="{{ route('password.email') }}" class="space-y-6"
                  x-data="{ submitting: false }" @submit="submitting = true">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="block w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300] focus:border-transparent @error('email') border-red-500 @enderror"
                        placeholder="you@example.com">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    :disabled="submitting"
                    :class="submitting ? 'opacity-60 cursor-not-allowed' : 'hover:bg-gray-800'"
                    class="w-full bg-black text-white py-3 px-4 rounded-xl text-sm font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black">
                    <span x-text="submitting ? 'Sending...' : 'Send Reset Link'">Send Reset Link</span>
                </button>

                <p class="text-center text-sm text-gray-600">
                    Remember your password?
                    <a href="{{ route('login') }}" class="text-[#FFC300] font-semibold hover:text-[#E6B000]">Sign in</a>
                </p>
            </form>
        </div>
    </div>
</div>
@endsection
