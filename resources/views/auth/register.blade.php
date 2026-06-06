@extends('layouts.app')

@section('title', 'Create Account - Templatr')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-[#FFC300] rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" viewBox="0 0 88 93" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M88 7.16H0V93.16H36V51.16H44H52V93.16H88V7.16Z"/>
                </svg>
            </div>
            <h2 class="text-3xl font-bold text-gray-900">Create your account</h2>
            <p class="mt-2 text-gray-600">Join Templatr today</p>

            @if($referralCode ?? false)
                <div class="mt-3 inline-flex items-center px-4 py-2 bg-green-50 border border-green-200 rounded-xl">
                    <svg class="w-4 h-4 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm text-green-700 font-medium">You were referred by a friend! 🎉</span>
                </div>
            @endif
        </div>

        <div class="bg-white py-8 px-6 shadow-xl rounded-2xl border border-gray-100">
            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf

                @if($referralCode ?? false)
                    <input type="hidden" name="referral_code" value="{{ $referralCode }}">
                @endif

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                        class="block w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300] focus:border-transparent @error('name') border-red-500 @enderror"
                        placeholder="John Doe">
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required
                        class="block w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300] focus:border-transparent @error('email') border-red-500 @enderror"
                        placeholder="you@example.com">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input id="password" type="password" name="password" required
                        class="block w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300] focus:border-transparent @error('password') border-red-500 @enderror"
                        placeholder="Min. 8 characters">
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required
                        class="block w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300] focus:border-transparent"
                        placeholder="Confirm your password">
                </div>

                <div class="pt-1">
                    <label class="flex items-start space-x-3 cursor-pointer">
                        <input type="checkbox" name="terms_accepted" id="terms_accepted" value="1"
                            class="mt-0.5 w-4 h-4 rounded border-gray-300 text-[#FFC300] focus:ring-[#FFC300] focus:ring-2 cursor-pointer flex-shrink-0"
                            {{ old('terms_accepted') ? 'checked' : '' }}>
                        <span class="text-sm text-gray-600 leading-snug">
                            I have read and agree to the
                            <a href="{{ route('terms.show') }}" target="_blank" rel="noopener noreferrer"
                               class="font-semibold text-gray-900 underline underline-offset-2 hover:text-[#FFC300] transition-colors">
                                Terms of Service
                            </a>.
                            By creating an account, I consent to my data being processed as described therein.
                        </span>
                    </label>
                    @error('terms_accepted')
                        <p class="text-red-500 text-xs mt-1.5 ml-7">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full bg-black text-white py-3 px-4 rounded-xl text-sm font-semibold hover:bg-gray-800 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black">
                    Create Account
                </button>

                <p class="text-center text-sm text-gray-600">
                    Already have an account?
                    <a href="{{ route('login') }}" class="text-[#FFC300] font-semibold hover:text-[#E6B000]">Sign in</a>
                </p>
            </form>
        </div>
    </div>
</div>
@endsection
