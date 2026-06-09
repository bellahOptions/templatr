@extends('layouts.app')

@php use App\Helpers\CurrencyHelper; @endphp

@section('title', 'Browse Products - Templatr')
@section('meta_description', 'Explore our full library of premium design templates, graphics, fonts, audio, plugins and more. Filter by category, type and price. Instant download.')
@section('og_title', 'Browse Products - Templatr')
@section('og_description', 'Explore our full library of premium design templates, graphics, fonts, audio, plugins and more. Filter by category, type and price. Instant download.')
@section('canonical', route('products.index'))

@section('content')
<!-- Header -->
<section class="bg-black text-white py-16 md:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold">Browse <span class="text-[#FFC300]">Marketplace</span></h1>
            <p class="mt-4 text-gray-400 max-w-xl mx-auto">Discover thousands of premium digital resources for your creative projects, starting from just {{ CurrencyHelper::formatInt(3000) }}</p>
        </div>

        <!-- Search Bar -->
        <div class="max-w-2xl mx-auto mt-8">
            <form method="GET" action="{{ route('products.index') }}">
                <div class="flex">
                    <div class="flex-1 relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search items, authors, or categories..." 
                            class="w-full px-4 py-3.5 pl-12 rounded-l-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#FFC300] text-sm">
                        <svg class="absolute left-4 top-3.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <button type="submit" class="bg-[#FFC300] text-black px-6 rounded-r-xl font-semibold hover:bg-[#FFD633] transition-colors text-sm">Search</button>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Filters & Products -->
<section class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:grid lg:grid-cols-4 lg:gap-8">
            <!-- Filters Sidebar -->
            <div class="hidden lg:block">
                <div class="bg-white border border-gray-200 rounded-2xl p-6 sticky top-24">
                    <h3 class="font-semibold text-lg mb-6">Filters</h3>
                    
                    <!-- Categories -->
                    <div class="mb-6">
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Categories</h4>
                        <div class="space-y-1">
                            <a href="{{ route('products.index', array_merge(request()->except('category'), ['category' => ''])) }}" class="block text-sm px-3 py-2 rounded-lg {{ !request('category') ? 'bg-[#FFC300]/10 text-[#CC9900] font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-black' }} transition-colors">All Categories</a>
                            @foreach($categories as $category)
                            <a href="{{ route('products.index', array_merge(request()->except('category'), ['category' => $category->slug])) }}" class="block text-sm px-3 py-2 rounded-lg {{ request('category') == $category->slug ? 'bg-[#FFC300]/10 text-[#CC9900] font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-black' }} transition-colors">
                                {{ $category->name }}
                            </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Type -->
                    <div class="mb-6">
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">File Type</h4>
                        <div class="space-y-1">
                            <a href="{{ route('products.index', array_merge(request()->except('type'), ['type' => ''])) }}" class="block text-sm px-3 py-2 rounded-lg {{ !request('type') ? 'bg-[#FFC300]/10 text-[#CC9900] font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-black' }} transition-colors">All Types</a>
                            @foreach($types as $key => $type)
                            <a href="{{ route('products.index', array_merge(request()->except('type'), ['type' => $key])) }}" class="block text-sm px-3 py-2 rounded-lg {{ request('type') == $key ? 'bg-[#FFC300]/10 text-[#CC9900] font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-black' }} transition-colors">{{ $type }}</a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Price Range -->
                    <div>
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Price Range ({{ CurrencyHelper::SYMBOL }})</h4>
                        <form method="GET" action="{{ route('products.index') }}" class="space-y-2">
                            @foreach(request()->except('min_price', 'max_price') as $key => $value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach
                            <div class="flex space-x-2">
                                <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">
                                <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">
                            </div>
                            <button type="submit" class="w-full bg-black text-white text-sm py-2.5 rounded-lg font-medium hover:bg-gray-800 transition-colors">Apply</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Product Grid -->
            <div class="lg:col-span-3">
                <!-- Sort and Mobile Filter -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-3">
                    <p class="text-sm text-gray-500">Showing <span class="font-semibold text-gray-900">{{ $products->firstItem() ?? 0 }}</span> - <span class="font-semibold text-gray-900">{{ $products->lastItem() ?? 0 }}</span> of <span class="font-semibold text-gray-900">{{ $products->total() }}</span> items</p>
                    <div class="flex items-center space-x-3 w-full sm:w-auto">
                        <form method="GET" action="{{ route('products.index') }}" class="w-full sm:w-auto">
                            @foreach(request()->except('sort') as $key => $value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach
                            <select name="sort" onchange="this.form.submit()" class="text-sm border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FFC300] bg-white w-full sm:w-auto">
                                <option value="">Sort: Latest</option>
                                <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Sort: Price Low to High</option>
                                <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Sort: Price High to Low</option>
                                <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>Sort: Most Popular</option>
                            </select>
                        </form>
                    </div>
                </div>

                @if($products->isEmpty())
                <div class="text-center py-20">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">No items found</h3>
                    <p class="text-gray-500 text-sm">Try adjusting your search or filter criteria</p>
                    <a href="{{ route('products.index') }}" class="inline-flex mt-4 text-sm text-[#FFC300] hover:text-black font-semibold">Clear all filters</a>
                </div>
                @else
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
                    @foreach($products as $product)
                    <div class="group bg-white rounded-2xl overflow-hidden border border-gray-200 hover:border-[#FFC300] hover:shadow-xl transition-all duration-300">
                        <a href="{{ route('products.show', $product) }}">
                            <div class="aspect-[4/3] bg-gradient-to-br from-gray-100 to-gray-200 relative overflow-hidden">
                                @php
                                    $cardMedia = match(true) {
                                        (bool)$product->thumbnail && $product->thumbnail_is_video  => 'thumb-video',
                                        (bool)$product->thumbnail                                  => 'thumb-image',
                                        (bool)$product->preview_image && $product->preview_is_video => 'preview-video',
                                        (bool)$product->preview_image                              => 'preview-image',
                                        default                                                    => 'placeholder',
                                    };
                                @endphp
                                @switch($cardMedia)
                                    @case('thumb-video')
                                        <video src="{{ $product->thumbnail_url }}" class="w-full h-full object-cover" muted loop playsinline preload="metadata" onmouseenter="this.play()" onmouseleave="this.pause();this.currentTime=0"></video>
                                        @break
                                    @case('thumb-image')
                                        <img src="{{ $product->thumbnail_url }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="{{ $product->title }}" loading="lazy">
                                        @break
                                    @case('preview-video')
                                        <video src="{{ $product->preview_image_url }}" class="w-full h-full object-cover" muted loop playsinline preload="metadata" onmouseenter="this.play()" onmouseleave="this.pause();this.currentTime=0"></video>
                                        @break
                                    @case('preview-image')
                                        <img src="{{ $product->preview_image_url }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="{{ $product->title }}" loading="lazy">
                                        @break
                                    @default
                                        <div class="absolute inset-0 flex items-center justify-center opacity-30 grayscale">
                                            <img src="/templatr-logo.svg" class="w-20 h-auto" alt="Templatr" loading="lazy">
                                        </div>
                                @endswitch
                                @if($product->sale_price)
                                <div class="absolute top-3 left-3 bg-red-500 text-white text-xs font-bold px-2.5 py-1 rounded-lg">SALE</div>
                                @endif
                                <span class="absolute top-3 right-3 bg-black/60 text-white text-[11px] px-2.5 py-1 rounded-lg backdrop-blur-sm font-medium">{{ ucfirst($product->file_type) }}</span>
                            </div>
                        </a>
                        <div class="p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[11px] text-gray-500 font-medium">{{ $product->category->name }}</span>
                                <div class="flex items-center">
                                    <svg class="w-3.5 h-3.5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    <span class="text-xs text-gray-500 ml-1">{{ number_format($product->average_rating, 1) }}</span>
                                </div>
                            </div>
                            <a href="{{ route('products.show', $product) }}">
                                <h3 class="font-semibold text-gray-900 group-hover:text-[#FFC300] transition-colors line-clamp-1 text-sm">{{ $product->title }}</h3>
                            </a>
                            <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100">
                                <span class="text-[11px] text-gray-500">Animashaun</span>
                                <div class="text-right">
                                    @if($product->sale_price)
                                        <span class="text-[11px] text-gray-400 line-through">{{ CurrencyHelper::format($product->price) }}</span>
                                    @endif
                                    <span class="font-bold text-sm">{{ CurrencyHelper::format($product->sale_price ?? $product->price) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-10">
                    {{ $products->onEachSide(1)->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
