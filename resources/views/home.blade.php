@extends('layouts.app')

@php use App\Helpers\CurrencyHelper; @endphp

@section('title', 'Templatr - Premium Creative & Web Resources')
@section('meta_description', 'Download premium design templates, graphics, fonts, audio, plugins and more. Instant delivery, commercial license included. Nigeria\'s #1 creative marketplace.')
@section('og_title', 'Templatr - Premium Creative & Web Resources')
@section('og_description', 'Download premium design templates, graphics, fonts, audio, plugins and more. Instant delivery, commercial license included. Nigeria\'s #1 creative marketplace.')
@section('canonical', url('/'))

@push('structured_data')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Organization",
    "name": "Templatr",
    "url": "{{ url('/') }}",
    "logo": "{{ asset('templatr.svg') }}",
    "sameAs": []
}
</script>
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "WebSite",
    "name": "Templatr",
    "url": "{{ url('/') }}",
    "potentialAction": {
        "@type": "SearchAction",
        "target": {
            "@type": "EntryPoint",
            "urlTemplate": "{{ url('/products') }}?search={search_term_string}"
        },
        "query-input": "required name=search_term_string"
    }
}
</script>
@endpush

@section('content')

<livewire:hero-slideshow />

<!-- Trusted By / Stats Bar -->
<section class="bg-black border-t border-gray-800/50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-10">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8 text-center reveal">
            <div class="p-4">
                <div class="text-3xl md:text-4xl font-bold text-[#FFC300]">{{ \App\Models\Product::count() }}+</div>
                <div class="text-gray-400 text-sm mt-1">Premium Items</div>
            </div>
            <div class="p-4">
                <div class="text-3xl md:text-4xl font-bold text-[#FFC300]">{{ \App\Models\User::authors()->count() }}+</div>
                <div class="text-gray-400 text-sm mt-1">Expert Authors</div>
            </div>
            <div class="p-4">
                <div class="text-3xl md:text-4xl font-bold text-[#FFC300]">{{ \App\Models\User::count() }}+</div>
                <div class="text-gray-400 text-sm mt-1">Happy Customers</div>
            </div>
            <div class="p-4">
                <div class="text-3xl md:text-4xl font-bold text-[#FFC300]">{{ number_format(\App\Models\Product::sum('download_count')) }}+</div>
                <div class="text-gray-400 text-sm mt-1">Total Downloads</div>
            </div>
        </div>
    </div>
</section>

    
<!-- Featured Products -->
<section id="featured" class="py-16 md:py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between mb-10 reveal">
            <div>
                <h2 class="text-3xl md:text-4xl font-bold">Featured <span class="text-[#FFC300]">Items</span></h2>
                <p class="mt-2 text-gray-500">Hand-picked premium resources for you</p>
            </div>
            <a href="{{ route('products.index') }}" class="hidden sm:flex items-center text-sm font-semibold text-black hover:text-[#FFC300] transition-colors group">
                View All
                <svg class="w-4 h-4 ml-1 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach($featuredProducts as $index => $product)
            <div class="group bg-white rounded-2xl overflow-hidden border border-gray-200 hover:border-[#FFC300] hover:shadow-xl transition-all duration-500 reveal stagger-{{ min($index + 1, 6) }}">
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
                                <img src="{{ $product->thumbnail_url }}" alt="{{ $product->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                                @break
                            @case('preview-video')
                                <video src="{{ $product->preview_image_url }}" class="w-full h-full object-cover" muted loop playsinline preload="metadata" onmouseenter="this.play()" onmouseleave="this.pause();this.currentTime=0"></video>
                                @break
                            @case('preview-image')
                                <img src="{{ $product->preview_image_url }}" alt="{{ $product->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                                @break
                            @default
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
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
                        <span class="text-[11px] text-gray-500 font-medium">{{ $product->category?->name ?? 'Uncategorized' }}</span>
                        <div class="flex items-center" x-data>
                            <span class="text-xs text-yellow-400">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= round($product->average_rating))
                                        &#9733;
                                    @else
                                        &#9734;
                                    @endif
                                @endfor
                            </span>
                            <span class="text-xs text-gray-500 ml-1">{{ number_format($product->average_rating, 1) }}</span>
                        </div>
                    </div>
                    <a href="{{ route('products.show', $product) }}">
                        <h3 class="font-semibold text-gray-900 group-hover:text-[#FFC300] transition-colors line-clamp-1 text-sm">{{ $product->title }}</h3>
                    </a>
                    <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100">
                        <div class="flex items-center space-x-2">
                            <div class="w-6 h-6 bg-gray-200 rounded-full flex items-center justify-center">
                                <span class="text-[10px] font-bold">{{ substr($product->author?->name ?? '?', 0, 1) }}</span>
                            </div>
                            <span class="text-[11px] text-gray-500">{{ $product->author?->name ?? 'Unknown' }}</span>
                        </div>
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
    </div>
</section>

<!-- Product Suggestions (Powered by UserActivity Algorithm) -->
@auth
    <livewire:product-suggestions />
@endauth

<!-- New Arrivals -->
<section class="py-16 md:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between mb-10 reveal">
            <div>
                <h2 class="text-3xl md:text-4xl font-bold">New <span class="text-[#FFC300]">Arrivals</span></h2>
                <p class="mt-2 text-gray-500">Latest additions to our marketplace</p>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach($newProducts as $index => $product)
            <div class="group bg-white rounded-2xl overflow-hidden border border-gray-200 hover:border-[#FFC300] hover:shadow-xl transition-all duration-500 reveal stagger-{{ min($index + 1, 6) }}">
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
                                <img src="{{ $product->thumbnail_url }}" alt="{{ $product->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                                @break
                            @case('preview-video')
                                <video src="{{ $product->preview_image_url }}" class="w-full h-full object-cover" muted loop playsinline preload="metadata" onmouseenter="this.play()" onmouseleave="this.pause();this.currentTime=0"></video>
                                @break
                            @case('preview-image')
                                <img src="{{ $product->preview_image_url }}" alt="{{ $product->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                                @break
                            @default
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                </div>
                        @endswitch
                        <span class="absolute top-3 left-3 bg-[#FFC300] text-black text-xs font-bold px-2.5 py-1 rounded-lg">NEW</span>
                        <span class="absolute bottom-3 left-3 bg-black/60 text-white text-[11px] px-2.5 py-1 rounded-lg backdrop-blur-sm font-medium">{{ ucfirst($product->file_type) }}</span>
                    </div>
                </a>
                <div class="p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] text-gray-500 font-medium">{{ $product->category?->name ?? 'Uncategorized' }}</span>
                        <div class="flex items-center">
                            <svg class="w-3.5 h-3.5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <span class="text-xs text-gray-500 ml-1">{{ number_format($product->average_rating, 1) }}</span>
                        </div>
                    </div>
                    <a href="{{ route('products.show', $product) }}">
                        <h3 class="font-semibold text-gray-900 group-hover:text-[#FFC300] transition-colors line-clamp-1 text-sm">{{ $product->title }}</h3>
                    </a>
                    <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100">
                        <span class="text-[11px] text-gray-500">by {{ $product->author?->name ?? 'Unknown' }}</span>
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
        <div class="text-center mt-10 reveal">
            <a href="{{ route('products.index') }}" class="inline-flex items-center bg-black text-white px-8 py-3.5 rounded-xl font-semibold hover:bg-gray-800 transition-colors">
                Browse All Items
                <svg class="w-4 h-4 ml-2 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="py-16 md:py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 reveal">
            <h2 class="text-3xl md:text-4xl font-bold">How It <span class="text-[#FFC300]">Works</span></h2>
            <p class="mt-4 text-gray-500 max-w-xl mx-auto">Get premium resources in three simple steps</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-4xl mx-auto">
            <div class="text-center reveal stagger-1">
                <div class="w-16 h-16 bg-[#FFC300] rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <span class="text-2xl font-bold text-black">1</span>
                </div>
                <h3 class="font-bold text-lg mb-2">Browse</h3>
                <p class="text-gray-500 text-sm">Explore our vast collection of premium resources across multiple categories.</p>
            </div>
            <div class="text-center reveal stagger-2">
                <div class="w-16 h-16 bg-[#FFC300] rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <span class="text-2xl font-bold text-black">2</span>
                </div>
                <h3 class="font-bold text-lg mb-2">Purchase</h3>
                <p class="text-gray-500 text-sm">Buy with confidence using our secure payment gateways — one low price.</p>
            </div>
            <div class="text-center reveal stagger-3">
                <div class="w-16 h-16 bg-[#FFC300] rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <span class="text-2xl font-bold text-black">3</span>
                </div>
                <h3 class="font-bold text-lg mb-2">Download & Create</h3>
                <p class="text-gray-500 text-sm">Download instantly and start using your resources in your projects.</p>
            </div>
        </div>
    </div>
</section>


@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    });
</script>
@endpush
