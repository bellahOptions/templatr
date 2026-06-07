@extends('layouts.app')

@section('title', 'Terms of Service - Templatr')
@section('meta_description', 'Read the Templatr Terms of Service. Understand your rights and obligations when purchasing and using our digital creative assets.')
@section('og_title', 'Terms of Service - Templatr')
@section('canonical', route('terms.show'))

@section('content')
<div class="min-h-screen bg-gray-50 py-16">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold text-gray-900 mb-3">Terms of Service</h1>
            @if($terms)
            <p class="text-sm text-gray-500">Last updated {{ $terms->updated_at->format('F j, Y') }}</p>
            @endif
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-8 sm:p-12">
            @if($terms)
                <div class="prose prose-gray max-w-none terms-content">
                    {!! $terms->content !!}
                </div>
            @else
                <div class="text-center py-16 text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-lg font-medium text-gray-500">Terms of Service coming soon.</p>
                    <p class="text-sm mt-1">Please check back later.</p>
                </div>
            @endif
        </div>

        <div class="mt-8 text-center">
            <a href="{{ route('home') }}" class="text-sm text-gray-500 hover:text-gray-800 transition-colors">
                &larr; Back to Home
            </a>
        </div>
    </div>
</div>

@push('styles')
<style>
    .terms-content h1 { font-size: 1.5rem; font-weight: 700; margin-top: 1.5rem; margin-bottom: 0.75rem; color: #111827; }
    .terms-content h2 { font-size: 1.25rem; font-weight: 600; margin-top: 1.5rem; margin-bottom: 0.5rem; color: #111827; }
    .terms-content h3 { font-size: 1.1rem; font-weight: 600; margin-top: 1.25rem; margin-bottom: 0.5rem; color: #374151; }
    .terms-content p { margin-bottom: 1rem; color: #374151; line-height: 1.75; }
    .terms-content ul, .terms-content ol { padding-left: 1.5rem; margin-bottom: 1rem; color: #374151; }
    .terms-content li { margin-bottom: 0.35rem; line-height: 1.7; }
    .terms-content ul li { list-style-type: disc; }
    .terms-content ol li { list-style-type: decimal; }
    .terms-content a { color: #FFC300; text-decoration: underline; }
    .terms-content strong { font-weight: 600; color: #111827; }
</style>
@endpush
@endsection
