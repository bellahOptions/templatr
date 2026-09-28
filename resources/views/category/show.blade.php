@extends('layouts.app')

@php
    // Avoid "WordPress Themes Templates & Assets" style duplication for self-describing names.
    $describesItself = \Illuminate\Support\Str::contains(
        strtolower($category->name),
        ['template', 'theme', 'plugin', 'font', 'audio', 'video', 'graphic', 'asset', 'kit', 'print', '3d']
    );

    $categoryTitle = $describesItself
        ? $category->name
        : $category->name.' Templates & Assets';

    $categoryDescription = $category->description
        ?: 'Download premium '.strtolower($category->name).' from Templatr by Bellah Options — '.number_format($productCount).' hand-checked items with a commercial licence, instant download and lifetime updates.';
@endphp

@section('title', $categoryTitle)
@section('meta_description', \Illuminate\Support\Str::limit($categoryDescription, 155))
@section('og_title', $categoryTitle)
@section('og_description', \Illuminate\Support\Str::limit($categoryDescription, 155))
@section('canonical', route('category.show', $category))

@push('structured_data')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "BreadcrumbList",
    "itemListElement": [
        { "@@type": "ListItem", "position": 1, "name": "Home", "item": "{{ route('home') }}" },
        { "@@type": "ListItem", "position": 2, "name": "Marketplace", "item": "{{ route('products.index') }}" },
        { "@@type": "ListItem", "position": 3, "name": "{{ $category->name }}", "item": "{{ route('category.show', $category) }}" }
    ]
}
</script>
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "CollectionPage",
    "name": "{{ $category->name }}",
    "description": "{{ \Illuminate\Support\Str::limit($categoryDescription, 200) }}",
    "url": "{{ route('category.show', $category) }}",
    "mainEntity": {
        "@@type": "ItemList",
        "numberOfItems": {{ $productCount }},
        "itemListElement": [
            @foreach($topProducts as $index => $item)
            {
                "@@type": "ListItem",
                "position": {{ $index + 1 }},
                "url": "{{ route('products.show', $item) }}",
                "name": "{{ addslashes($item->title) }}"
            }{{ $loop->last ? '' : ',' }}
            @endforeach
        ]
    }
}
</script>
@endpush

@section('content')
    {{-- Breadcrumbs --}}
    <nav aria-label="Breadcrumb" class="border-b border-gray-200 bg-gray-50">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <ol class="scrollbar-hide flex items-center gap-2 overflow-x-auto py-3 text-sm">
                <li><a href="{{ route('home') }}" wire:navigate class="text-gray-600 transition-colors hover:text-[var(--color-primary-ink)]">Home</a></li>
                <li aria-hidden="true" class="text-gray-400">/</li>
                <li><a href="{{ route('products.index') }}" wire:navigate class="text-gray-600 transition-colors hover:text-[var(--color-primary-ink)]">Marketplace</a></li>
                <li aria-hidden="true" class="text-gray-400">/</li>
                <li aria-current="page" class="font-medium text-gray-900">{{ $category->name }}</li>
            </ol>
        </div>
    </nav>

    {{-- Category hero --}}
    <section class="bg-black py-14 text-white md:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <p class="mb-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#FFC300]">Category</p>
            <h1 class="text-3xl font-bold tracking-tight md:text-5xl">{{ $category->name }}</h1>
            <p class="mt-4 max-w-2xl text-sm leading-relaxed text-gray-300 md:text-base">
                {{ $categoryDescription }}
            </p>
            <dl class="mt-8 flex flex-wrap gap-x-10 gap-y-4">
                <div>
                    <dt class="text-xs uppercase tracking-wider text-gray-500">Items</dt>
                    <dd class="text-xl font-bold text-white">{{ number_format($productCount) }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wider text-gray-500">Licence</dt>
                    <dd class="text-xl font-bold text-white">Commercial</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wider text-gray-500">Delivery</dt>
                    <dd class="text-xl font-bold text-white">Instant</dd>
                </div>
            </dl>
        </div>
    </section>

    {{-- Most downloaded in this category --}}
    @if($topProducts->isNotEmpty())
        <section class="border-b border-gray-100 py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-8 flex items-end justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-bold md:text-2xl">Top {{ $category->name }}</h2>
                        <p class="mt-1 text-sm text-gray-600">Most downloaded in this category</p>
                    </div>
                    <a href="{{ route('products.index', ['sort' => 'popular']) }}" wire:navigate
                       class="hidden text-sm font-semibold text-gray-900 transition-colors hover:text-[var(--color-primary-ink)] sm:inline-flex">
                        See all
                    </a>
                </div>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($topProducts as $index => $product)
                        <x-product-card :product="$product" :priority="$index < 4" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Full catalogue, locked to this category --}}
    <section class="py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h2 class="sr-only">All {{ $category->name }} items</h2>
            @livewire('product-catalog', ['category' => $category->slug])
        </div>
    </section>

    {{-- Related categories --}}
    @if($relatedCategories->count() > 1)
        <section class="border-t border-gray-100 bg-gray-50 py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h2 class="mb-6 text-lg font-bold">Browse other categories</h2>
                <ul class="flex flex-wrap gap-2">
                    @foreach($relatedCategories as $related)
                        @continue($related->is($category))
                        <li>
                            <a href="{{ route('category.show', $related) }}" wire:navigate
                               class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition-all hover:border-[#FFC300] hover:text-gray-900">
                                {{ $related->name }}
                                <span class="text-xs text-gray-500">{{ $related->products_count }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif
@endsection
