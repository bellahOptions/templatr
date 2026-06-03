@extends('layouts.app')

@section('title', 'Edit Profile - Templatr')

@section('content')
<section class="py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <div class="flex items-center space-x-2 text-sm mb-6">
            <a href="{{ route('profile.index') }}" class="text-gray-500 hover:text-[#FFC300]">Profile</a>
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-900 font-medium">Edit Profile</span>
        </div>

        <div class="mb-8">
            <h1 class="text-3xl font-bold">Edit <span class="text-[#FFC300]">Profile</span></h1>
            <p class="text-gray-600 mt-1">Update your account information</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl p-8 mb-8">
            <h2 class="text-lg font-bold mb-6">Profile Information</h2>
            <form method="POST" action="{{ route('profile.edit') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required
                        class="block w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300] @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required
                        class="block w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300] @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="bio" class="block text-sm font-medium text-gray-700 mb-1">Bio</label>
                    <textarea id="bio" name="bio" rows="3" class="block w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">{{ old('bio', $user->bio) }}</textarea>
                </div>

                @if($user->isAuthor())
                <div>
                    <label for="paypal_email" class="block text-sm font-medium text-gray-700 mb-1">PayPal Email (for earnings)</label>
                    <input id="paypal_email" type="email" name="paypal_email" value="{{ old('paypal_email', $user->paypal_email) }}"
                        class="block w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">
                </div>
                @endif

                <button type="submit" class="w-full bg-black text-white py-3 rounded-xl font-semibold hover:bg-gray-800 transition-colors">
                    Update Profile
                </button>
            </form>
        </div>

        <!-- Change Password -->
        <div class="bg-white border border-gray-200 rounded-2xl p-8">
            <h2 class="text-lg font-bold mb-6">Change Password</h2>
            <form method="POST" action="{{ route('profile.password') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                    <input id="current_password" type="password" name="current_password" required
                        class="block w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300] @error('current_password') border-red-500 @enderror">
                    @error('current_password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                    <input id="password" type="password" name="password" required
                        class="block w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300] @error('password') border-red-500 @enderror">
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required
                        class="block w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">
                </div>

                <button type="submit" class="w-full bg-black text-white py-3 rounded-xl font-semibold hover:bg-gray-800 transition-colors">
                    Update Password
                </button>
            </form>
        </div>
    </div>
</section>
@endsection
