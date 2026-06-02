@extends('layouts.app')

@section('title', 'Session Expired - CreativeMarket')

@section('content')
<div class="min-h-screen flex items-center justify-center py-20 px-4">
    <div class="max-w-lg w-full text-center">
        <div class="w-24 h-24 bg-orange-50 rounded-3xl flex items-center justify-center mx-auto mb-8 animate-pulse">
            <svg class="w-12 h-12 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h1 class="text-7xl font-bold text-gray-900 mb-2">419</h1>
        <p class="text-xl font-semibold text-gray-800 mb-2">Session Expired</p>
        <p class="text-gray-500 mb-8 max-w-sm mx-auto">Your session has expired. Please refresh the page and try again.</p>
        <div class="flex items-center justify-center space-x-4">
            <a href="{{ url()->current() }}" class="inline-flex items-center bg-black text-white px-6 py-3 rounded-xl font-semibold hover:bg-gray-800 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Refresh Page
            </a>
            <a href="{{ route('login') }}" class="inline-flex items-center border border-gray-300 text-gray-700 px-6 py-3 rounded-xl font-semibold hover:bg-gray-50 transition-colors">
                Sign In Again
            </a>
        </div>
    </div>
</div>
@endsection
