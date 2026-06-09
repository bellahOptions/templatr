@extends('layouts.app')

@section('title', 'Reset Password - Templatr')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-[#FFC300] rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h2 class="text-3xl font-bold text-gray-900">Set new password</h2>
            <p class="mt-2 text-gray-600">Choose a strong password for your account.</p>
        </div>

        <div class="bg-white py-8 px-6 shadow-xl rounded-2xl border border-gray-100">
            <form method="POST" action="{{ route('password.update') }}" class="space-y-6"
                  x-data="{ submitting: false }" @submit="submitting = true">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div>
                    <label for="email_display" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input id="email_display" type="email" value="{{ $email }}" disabled
                        class="block w-full px-4 py-3 border border-gray-200 rounded-xl text-sm bg-gray-50 text-gray-500 cursor-not-allowed">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                    <input id="password" type="password" name="password" required
                        class="block w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300] focus:border-transparent @error('password') border-red-500 @enderror"
                        placeholder="Min. 8 characters">
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required
                        class="block w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300] focus:border-transparent"
                        placeholder="Repeat your new password">
                </div>

                @if($errors->has('email'))
                    <p class="text-red-500 text-xs">{{ $errors->first('email') }}</p>
                @endif

                <button type="submit"
                    :disabled="submitting"
                    :class="submitting ? 'opacity-60 cursor-not-allowed' : 'hover:bg-gray-800'"
                    class="w-full bg-black text-white py-3 px-4 rounded-xl text-sm font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black">
                    <span x-text="submitting ? 'Resetting...' : 'Reset Password'">Reset Password</span>
                </button>

                <p class="text-center text-sm text-gray-600">
                    <a href="{{ route('login') }}" class="text-[#FFC300] font-semibold hover:text-[#E6B000]">Back to Sign In</a>
                </p>
            </form>
        </div>
    </div>
</div>
@endsection
