@extends('layouts.app')

@php use App\Helpers\CurrencyHelper; @endphp

@section('title', 'Browse Products - Templatr')
@section('meta_description', 'Explore our full library of premium design templates, graphics, fonts, audio, plugins and more. Filter by category, type and price. Instant download.')
@section('og_title', 'Browse Products - Templatr')
@section('og_description', 'Explore our full library of premium design templates, graphics, fonts, audio, plugins and more. Filter by category, type and price. Instant download.')
@section('canonical', route('products.index'))

@section('content')
{{-- Hero --}}
<section class="bg-black text-white py-16 md:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold">Browse <span class="text-[#FFC300]">Marketplace</span></h1>
            <p class="mt-4 text-gray-400 max-w-xl mx-auto">Discover thousands of premium digital resources for your creative projects, starting from just {{ CurrencyHelper::formatInt(3000) }}</p>
        </div>
        <div class="max-w-2xl mx-auto mt-8">
            @livewire('smart-search')
        </div>
    </div>
</section>

{{-- Product catalog with infinite scroll and reactive filters --}}
<section class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @livewire('product-catalog')
    </div>
</section>
@endsection
