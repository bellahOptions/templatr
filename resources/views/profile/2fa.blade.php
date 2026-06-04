@extends('layouts.app')

@section('title', 'Two-Factor Authentication - Templatr')

@section('content')
<section class="py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <div class="mb-6">
            <a href="{{ route('profile.edit') }}" class="text-sm text-gray-500 hover:text-[#FFC300]">&larr; Back to Profile Settings</a>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-[#FFC300]/10 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-[#FFC300]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold">Two-Factor Authentication</h2>
                        <p class="text-sm text-gray-500">Add an extra layer of security to your account</p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                @if(session('success'))
                    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm mb-6">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm mb-6">
                        {{ session('error') }}
                    </div>
                @endif

                @if(session('info'))
                    <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-xl text-sm mb-6">
                        {{ session('info') }}
                    </div>
                @endif

                <!-- 2FA Status -->
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl mb-6">
                    <div class="flex items-center space-x-3">
                        @if($user->hasTwoFactorEnabled())
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-green-700">Enabled</p>
                                <p class="text-xs text-gray-500">Your account is protected with 2FA</p>
                            </div>
                        @else
                            <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-700">Disabled</p>
                                <p class="text-xs text-gray-500">Your account does not have 2FA enabled</p>
                            </div>
                        @endif
                    </div>

                    @if($user->hasTwoFactorEnabled())
                        <form method="POST" action="{{ route('profile.2fa.disable') }}" class="inline">
                            @csrf
                            <div class="flex items-center space-x-2">
                                <input type="password" name="current_password" placeholder="Current password"
                                       class="text-xs border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500"
                                       required>
                                <button type="submit"
                                        class="px-4 py-2 bg-red-600 text-white rounded-lg text-xs font-semibold hover:bg-red-700 transition-colors">
                                    Disable
                                </button>
                            </div>
                        </form>
                    @else
                        <form method="POST" action="{{ route('profile.2fa.enable') }}" class="inline">
                            @csrf
                            <button type="submit"
                                    class="px-4 py-2 bg-black text-white rounded-lg text-sm font-semibold hover:bg-gray-800 transition-colors">
                                Enable 2FA
                            </button>
                        </form>
                    @endif
                </div>

                <!-- 2FA Verify Code Form (shown after enable is clicked) -->
                @if(session('show_2fa_verify'))
                <div class="border border-[#FFC300] bg-yellow-50 rounded-xl p-6">
                    <h3 class="font-bold text-sm mb-2">Verify Your Email</h3>
                    <p class="text-xs text-gray-600 mb-4">A verification code was sent to {{ $user->email }}. Enter it below to confirm enabling 2FA.</p>
                    <form method="POST" action="{{ route('profile.2fa.confirm') }}">
                        @csrf
                        <div class="flex items-center space-x-3">
                            <input type="text" name="code" maxlength="6"
                                   placeholder="000000"
                                   class="w-32 text-center text-lg font-bold tracking-[6px] border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#FFC300]"
                                   required>
                            <button type="submit"
                                    class="px-6 py-3 bg-black text-white rounded-xl text-sm font-semibold hover:bg-gray-800 transition-colors">
                                Verify & Enable
                            </button>
                        </div>
                        @error('code')
                            <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                        @enderror
                    </form>
                </div>
                @endif

                <!-- Info Section -->
                <div class="mt-6 space-y-3">
                    <h3 class="font-bold text-sm">How it works</h3>
                    <div class="flex items-start space-x-3 text-sm text-gray-600">
                        <span class="w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">1</span>
                        <p>Enable 2FA from this page. A verification code will be sent to your email.</p>
                    </div>
                    <div class="flex items-start space-x-3 text-sm text-gray-600">
                        <span class="w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">2</span>
                        <p>Each time you sign in, a 6-digit code will be emailed to you.</p>
                    </div>
                    <div class="flex items-start space-x-3 text-sm text-gray-600">
                        <span class="w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">3</span>
                        <p>Enter the code to complete your login. Codes expire after 10 minutes.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
