@extends('layouts.app')

@section('title', 'Too Many Requests - CreativeMarket')

@section('content')
<div class="min-h-screen flex items-center justify-center py-20 px-4">
    <div class="max-w-lg w-full text-center">
        <div class="w-24 h-24 bg-red-50 rounded-3xl flex items-center justify-center mx-auto mb-8">
            <svg class="w-12 h-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
            </svg>
        </div>
        <h1 class="text-7xl font-bold text-gray-900 mb-2">429</h1>
        <p class="text-xl font-semibold text-gray-800 mb-2">Too Many Requests</p>
        <p class="text-gray-500 mb-8 max-w-sm mx-auto">You've sent too many requests. Please slow down and try again shortly.</p>
        <div class="flex items-center justify-center space-x-4">
            <a href="{{ route('home') }}" class="inline-flex items-center bg-black text-white px-6 py-3 rounded-xl font-semibold hover:bg-gray-800 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Go Home
            </a>
            <a href="javascript:history.back()" class="inline-flex items-center border border-gray-300 text-gray-700 px-6 py-3 rounded-xl font-semibold hover:bg-gray-50 transition-colors">
                Try Again
            </a>
        </div>
    </div>
</div>
@endsection
