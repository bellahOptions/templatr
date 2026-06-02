@extends('layouts.app')

@section('title', '403 Forbidden - CreativeMarket')

@section('content')
<div class="min-h-screen flex items-center justify-center py-20 px-4">
    <div class="max-w-lg w-full text-center">
        <div class="w-24 h-24 bg-red-50 rounded-3xl flex items-center justify-center mx-auto mb-8 animate-fade-in-up">
            <svg class="w-12 h-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
            </svg>
        </div>
        <h1 class="text-7xl font-bold text-gray-900 mb-2">403</h1>
        <p class="text-xl font-semibold text-gray-800 mb-2">Access Forbidden</p>
        <p class="text-gray-500 mb-8 max-w-sm mx-auto">You don't have permission to access this page. Please check your credentials or contact support.</p>
        <div class="flex items-center justify-center space-x-4">
            <a href="{{ route('home') }}" class="inline-flex items-center bg-black text-white px-6 py-3 rounded-xl font-semibold hover:bg-gray-800 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Go Home
            </a>
            <a href="javascript:history.back()" class="inline-flex items-center border border-gray-300 text-gray-700 px-6 py-3 rounded-xl font-semibold hover:bg-gray-50 transition-colors">
                Go Back
            </a>
        </div>
    </div>
</div>
@endsection
