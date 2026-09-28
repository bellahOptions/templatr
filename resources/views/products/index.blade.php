@extends('layouts.app')

@php
    use App\Helpers\CurrencyHelper;

    $activeCategory = request('category')
        ? \App\Models\Category::where('slug', request('category'))->first()
        : null;

    // Faceted URLs (type/sort/price/search) are thin duplicates — keep them crawlable but out of the index.
    $hasFacets = request()->hasAny(['type', 'sort', 'min_price', 'max_price', 'search']);

    $pageTitle = $activeCategory
        ? $activeCategory->name.' Templates & Assets'
        : 'Browse Premium Templates, Graphics, Fonts & Plugins';

    $pageDescription = $activeCategory
        ? ($activeCategory->description ?: 'Download premium '.strtolower($activeCategory->name).' with a commercial licence, instant delivery and lifetime updates. Starting from '.CurrencyHelper::formatInt(3000).'.')
        : 'Explore the full Templatr by Bellah Options library of premium design templates, WordPress themes and plugins, graphics, fonts, audio and video assets. Filter by category, file type and price — instant download with a commercial licence.';

    $canonicalUrl = $activeCategory
        ? route('category.show', $activeCategory)
        : route('products.index');
@endphp

@section('title', $pageTitle)
@section('meta_description', \Illuminate\Support\Str::limit($pageDescription, 155))
@section('og_title', $pageTitle)
@section('og_description', \Illuminate\Support\Str::limit($pageDescription, 155))
@section('canonical', $canonicalUrl)
@section('robots', $hasFacets ? 'noindex, follow' : 'index, follow, max-image-preview:large, max-snippet:-1')

@push('structured_data')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "BreadcrumbList",
    "itemListElement": [
        { "@@type": "ListItem", "position": 1, "name": "Home", "item": "{{ route('home') }}" },
        @if($activeCategory)
        { "@@type": "ListItem", "position": 2, "name": "Marketplace", "item": "{{ route('products.index') }}" },
        { "@@type": "ListItem", "position": 3, "name": "{{ $activeCategory->name }}", "item": "{{ route('category.show', $activeCategory) }}" }
        @else
        { "@@type": "ListItem", "position": 2, "name": "Marketplace", "item": "{{ route('products.index') }}" }
        @endif
    ]
}
</script>
@endpush

@section('content')
{{-- Hero --}}
<section class="bg-black text-white py-14 md:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-3xl md:text-5xl font-bold tracking-tight">
                @if($activeCategory)
                    {{ $activeCategory->name }} <span class="text-[#FFC300]">Templates &amp; Assets</span>
                @else
                    Browse the <span class="text-[#FFC300]">Marketplace</span>
                @endif
            </h1>
            <p class="mt-4 text-gray-300 max-w-2xl mx-auto text-sm md:text-base leading-relaxed">
                {{ $pageDescription }}
            </p>
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
