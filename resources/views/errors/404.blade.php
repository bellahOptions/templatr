@extends('layouts.app')

@section('title', '404 Not Found - CreativeMarket')

@section('content')
<div class="min-h-screen flex items-center justify-center py-20 px-4">
    <div class="max-w-lg w-full text-center">
        <div class="w-24 h-24 bg-yellow-50 rounded-3xl flex items-center justify-center mx-auto mb-8 animate-bounce">
            <svg class="w-12 h-12 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
            </svg>
        </div>
        <h1 class="text-7xl font-bold text-gray-900 mb-2">404</h1>
        <p class="text-xl font-semibold text-gray-800 mb-2">Page Not Found</p>
        <p class="text-gray-500 mb-8 max-w-sm mx-auto">The page you're looking for doesn't exist or has been moved. Let's get you back on track.</p>
        <div class="flex items-center justify-center space-x-4">
            <a href="{{ route('home') }}" class="inline-flex items-center bg-black text-white px-6 py-3 rounded-xl font-semibold hover:bg-gray-800 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Go Home
            </a>
            <a href="{{ route('products.index') }}" class="inline-flex items-center border border-gray-300 text-gray-700 px-6 py-3 rounded-xl font-semibold hover:bg-gray-50 transition-colors">
                Browse Items
            </a>
        </div>
    </div>
</div>
@endsection
