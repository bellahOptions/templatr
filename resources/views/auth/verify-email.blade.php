@extends('layouts.app')

@section('title', 'Verify Email - Templatr')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-[#FFC300]/10 rounded-3xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-[#FFC300]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <h2 class="text-3xl font-bold text-gray-900">Verify your email</h2>
            <p class="mt-3 text-gray-600 text-sm leading-relaxed">
                We've sent a verification link to <strong class="text-gray-900">{{ auth()->user()->email }}</strong>.
                Please check your inbox and click the link to activate your account.
            </p>
        </div>

        <div class="bg-white py-8 px-6 shadow-xl rounded-2xl border border-gray-100">
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm mb-6 flex items-center">
                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('warning'))
                <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-xl text-sm mb-6 flex items-center">
                    <svg class="w-5 h-5 text-yellow-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                    {{ session('warning') }}
                </div>
            @endif

            <div class="space-y-4">
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <div class="flex items-start space-x-3">
                        <svg class="w-5 h-5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="text-sm text-gray-600">
                                Didn't receive the email? Check your spam folder or
                                <form method="POST" action="{{ route('verification.resend') }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-[#FFC300] font-semibold hover:text-[#E6B000] transition-colors">
                                        click here to resend
                                    </button>
                                </form>.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <div class="flex items-start space-x-3">
                        <svg class="w-5 h-5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <div>
                            <p class="text-sm text-gray-600">
                                Using a different email?
                                <a href="{{ route('profile.edit') }}" class="text-[#FFC300] font-semibold hover:text-[#E6B000] transition-colors">
                                    Update your email
                                </a>.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-6 border-t border-gray-100">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-center text-sm text-gray-500 hover:text-gray-700 transition-colors">
                        Sign out and try again later
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
