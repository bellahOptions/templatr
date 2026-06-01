@extends('layouts.app')

@section('title', 'Create Account - CreativeMarket')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-[#FFC300] rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-black" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                </svg>
            </div>
            <h2 class="text-3xl font-bold text-gray-900">Create your account</h2>
            <p class="mt-2 text-gray-600">Join CreativeMarket today</p>
        </div>

        <div class="bg-white py-8 px-6 shadow-xl rounded-2xl border border-gray-100">
            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf

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
