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
    "@@context": "https://schema.org",
    "@@type": "Organization",
    "name": "Templatr",
    "url": "{{ url('/') }}",
    "logo": "{{ asset('templatr.svg') }}",
    "sameAs": []
}
</script>
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "WebSite",
    "name": "Templatr",
    "url": "{{ url('/') }}",
    "potentialAction": {
        "@@type": "SearchAction",
        "target": {
            "@@type": "EntryPoint",
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
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8 text-center reveal">
            <div class="p-4">
                <div class="text-3xl md:text-4xl font-bold text-[#FFC300]">{{ \App\Models\Product::count() }}+</div>
                <div class="text-gray-400 text-sm mt-1">Premium Items</div>
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


<!-- Featured Tools / Supported Platforms -->
<section class="py-14 overflow-hidden bg-gray-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center mb-10 reveal">
        <p class="text-xs font-semibold tracking-widest uppercase text-gray-500 mb-3">Compatible Platforms &amp; Technologies</p>
        <h2 class="text-2xl md:text-3xl font-bold text-white">Works with your <span class="text-[#FFC300]">favorite tools</span></h2>
    </div>
    <div class="tools-marquee-outer relative flex overflow-hidden" style="mask-image:linear-gradient(to right,transparent,black 10%,black 90%,transparent);-webkit-mask-image:linear-gradient(to right,transparent,black 10%,black 90%,transparent);">
        <div class="tools-track flex items-center gap-8 px-4">

            <div class="tool-chip">
                <div class="tool-icon-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 48 48"><path fill="#03A9F4" d="M6,10c0-2.209,1.791-4,4-4h28c2.209,0,4,1.791,4,4v28c0,2.209-1.791,4-4,4H10c-2.209,0-4-1.791-4-4V10z"/><path fill="#020F16" d="M20.016,19.174h-2.002v4.434h1.973c0.547,0,0.97-0.179,1.27-0.537s0.449-0.879,0.449-1.563c0-0.71-0.153-1.274-0.459-1.694S20.53,19.181,20.016,19.174z"/><path fill="#020F16" d="M9,9v30h30V9H9z M23.365,24.789C22.539,25.597,21.393,26,19.928,26h-1.914v5h-2.871V16.781h4.844c1.406,0,2.528,0.437,3.364,1.309s1.255,2.005,1.255,3.398S24.192,23.981,23.365,24.789z M32.682,30.336c-0.709,0.573-1.641,0.859-2.793,0.859c-0.775,0-1.459-0.151-2.051-0.454s-1.057-0.725-1.392-1.265s-0.503-1.123-0.503-1.748h2.627c0.014,0.481,0.125,0.843,0.337,1.084s0.558,0.361,1.04,0.361c0.742,0,1.113-0.335,1.113-1.006c0-0.234-0.112-0.451-0.337-0.649S30,27.052,29.225,26.713c-1.139-0.462-1.922-0.94-2.349-1.436s-0.64-1.11-0.64-1.846c0-0.925,0.334-1.688,1.001-2.29s1.552-0.903,2.651-0.903c1.158,0,2.086,0.3,2.783,0.898s1.045,1.403,1.045,2.412h-2.764c0-0.859-0.357-1.289-1.074-1.289c-0.293,0-0.533,0.091-0.723,0.273s-0.283,0.437-0.283,0.762c0,0.234,0.104,0.441,0.313,0.62s0.699,0.435,1.475,0.767c1.127,0.417,1.922,0.881,2.388,1.392s0.698,1.174,0.698,1.987C33.746,29.005,33.391,29.763,32.682,30.336z"/></svg>
                </div>
                <span class="tool-name">Photoshop</span>
            </div>

            <div class="tool-chip">
                <div class="tool-icon-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 48 48"><path fill="#FF5722" d="M6,10c0-2.209,1.791-4,4-4h28c2.209,0,4,1.791,4,4v28c0,2.209-1.791,4-4,4H10c-2.209,0-4-1.791-4-4V10z"/><path fill="#1C0802" d="M9,9v30h30V9H9z M23.691,31l-0.762-2.91h-3.916L18.252,31h-3.037l4.443-14.219h2.627L26.758,31H23.691z M30.85,31h-2.773V20.434h2.773V31z M30.552,18.754c-0.271,0.28-0.636,0.42-1.099,0.42s-0.828-0.14-1.099-0.42s-0.405-0.632-0.405-1.055c0-0.43,0.137-0.781,0.41-1.055s0.639-0.41,1.094-0.41s0.82,0.137,1.094,0.41s0.41,0.625,0.41,1.055C30.957,18.122,30.822,18.474,30.552,18.754z"/><path fill="#1C0802" d="M19.639 25.697L22.295 25.697 20.967 20.629z"/></svg>
                </div>
                <span class="tool-name">Illustrator</span>
            </div>

            <div class="tool-chip">
                <div class="tool-icon-wrap">
                    <img src="https://img.icons8.com/3d-fluency/94/wordpress.png" alt="WordPress" class="w-9 h-9 object-contain">
                </div>
                <span class="tool-name">WordPress</span>
            </div>

            <div class="tool-chip">
                <div class="tool-icon-wrap">
                    <img src="https://img.icons8.com/arcade/64/laravel.png" alt="Laravel" class="w-9 h-9 object-contain">
                </div>
                <span class="tool-name">Laravel</span>
            </div>

            <div class="tool-chip">
                <div class="tool-icon-wrap">
                    <img src="https://img.icons8.com/officel/80/php-logo.png" alt="PHP" class="w-9 h-9 object-contain">
                </div>
                <span class="tool-name">PHP</span>
            </div>

            <div class="tool-chip">
                <div class="tool-icon-wrap">
                    <img src="https://img.icons8.com/color/48/html-5--v1.png" alt="HTML5" class="w-9 h-9 object-contain">
                </div>
                <span class="tool-name">HTML5</span>
            </div>

            <div class="tool-chip">
                <div class="tool-icon-wrap">
                    <img src="https://img.icons8.com/color/48/shopify.png" alt="Shopify" class="w-9 h-9 object-contain">
                </div>
                <span class="tool-name">Shopify</span>
            </div>

            <div class="tool-chip">
                <div class="tool-icon-wrap">
                    <img src="https://img.icons8.com/external-those-icons-flat-those-icons/24/external-Drupal-Logo-social-media-those-icons-flat-those-icons.png" alt="Drupal" class="w-8 h-8 object-contain">
                </div>
                <span class="tool-name">Drupal</span>
            </div>

            <div class="tool-chip">
                <div class="tool-icon-wrap">
                    <img src="https://img.icons8.com/external-those-icons-flat-those-icons/24/external-Joomla-Logo-social-media-those-icons-flat-those-icons.png" alt="Joomla" class="w-8 h-8 object-contain">
                </div>
                <span class="tool-name">Joomla</span>
            </div>

            <div class="tool-chip">
                <div class="tool-icon-wrap">
                    <img src="https://img.icons8.com/color/48/woocommerce.png" alt="WooCommerce" class="w-9 h-9 object-contain">
                </div>
                <span class="tool-name">WooCommerce</span>
            </div>

            <div class="tool-chip">
                <div class="tool-icon-wrap">
                    <img src="https://img.icons8.com/color/48/adobe-after-effects--v1.png" alt="After Effects" class="w-9 h-9 object-contain">
                </div>
                <span class="tool-name">After Effects</span>
            </div>

            <div class="tool-chip">
                <div class="tool-icon-wrap">
                    <img src="https://img.icons8.com/color/48/adobe-premiere-pro--v1.png" alt="Premiere Pro" class="w-9 h-9 object-contain">
                </div>
                <span class="tool-name">Premiere Pro</span>
            </div>
            
            <div class="tool-chip">
                <div class="tool-icon-wrap">
                    <img width="48" height="48" src="https://img.icons8.com/fluency/48/coreldraw-2021.png" alt="coreldraw-2021" class="w-9 h-9 object-contain"/>
                </div>
                <span class="tool-name">CorelDraw</span>
            </div>
        </div>
        <!-- Duplicate track for seamless infinite loop -->
        <div class="tools-track flex items-center gap-8 px-4" aria-hidden="true">

            <div class="tool-chip">
                <div class="tool-icon-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 48 48"><path fill="#03A9F4" d="M6,10c0-2.209,1.791-4,4-4h28c2.209,0,4,1.791,4,4v28c0,2.209-1.791,4-4,4H10c-2.209,0-4-1.791-4-4V10z"/><path fill="#020F16" d="M20.016,19.174h-2.002v4.434h1.973c0.547,0,0.97-0.179,1.27-0.537s0.449-0.879,0.449-1.563c0-0.71-0.153-1.274-0.459-1.694S20.53,19.181,20.016,19.174z"/><path fill="#020F16" d="M9,9v30h30V9H9z M23.365,24.789C22.539,25.597,21.393,26,19.928,26h-1.914v5h-2.871V16.781h4.844c1.406,0,2.528,0.437,3.364,1.309s1.255,2.005,1.255,3.398S24.192,23.981,23.365,24.789z M32.682,30.336c-0.709,0.573-1.641,0.859-2.793,0.859c-0.775,0-1.459-0.151-2.051-0.454s-1.057-0.725-1.392-1.265s-0.503-1.123-0.503-1.748h2.627c0.014,0.481,0.125,0.843,0.337,1.084s0.558,0.361,1.04,0.361c0.742,0,1.113-0.335,1.113-1.006c0-0.234-0.112-0.451-0.337-0.649S30,27.052,29.225,26.713c-1.139-0.462-1.922-0.94-2.349-1.436s-0.64-1.11-0.64-1.846c0-0.925,0.334-1.688,1.001-2.29s1.552-0.903,2.651-0.903c1.158,0,2.086,0.3,2.783,0.898s1.045,1.403,1.045,2.412h-2.764c0-0.859-0.357-1.289-1.074-1.289c-0.293,0-0.533,0.091-0.723,0.273s-0.283,0.437-0.283,0.762c0,0.234,0.104,0.441,0.313,0.62s0.699,0.435,1.475,0.767c1.127,0.417,1.922,0.881,2.388,1.392s0.698,1.174,0.698,1.987C33.746,29.005,33.391,29.763,32.682,30.336z"/></svg>
                </div>
                <span class="tool-name">Photoshop</span>
            </div>

            <div class="tool-chip">
                <div class="tool-icon-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 48 48"><path fill="#FF5722" d="M6,10c0-2.209,1.791-4,4-4h28c2.209,0,4,1.791,4,4v28c0,2.209-1.791,4-4,4H10c-2.209,0-4-1.791-4-4V10z"/><path fill="#1C0802" d="M9,9v30h30V9H9z M23.691,31l-0.762-2.91h-3.916L18.252,31h-3.037l4.443-14.219h2.627L26.758,31H23.691z M30.85,31h-2.773V20.434h2.773V31z M30.552,18.754c-0.271,0.28-0.636,0.42-1.099,0.42s-0.828-0.14-1.099-0.42s-0.405-0.632-0.405-1.055c0-0.43,0.137-0.781,0.41-1.055s0.639-0.41,1.094-0.41s0.82,0.137,1.094,0.41s0.41,0.625,0.41,1.055C30.957,18.122,30.822,18.474,30.552,18.754z"/><path fill="#1C0802" d="M19.639 25.697L22.295 25.697 20.967 20.629z"/></svg>
                </div>
                <span class="tool-name">Illustrator</span>
            </div>

            <div class="tool-chip">
                <div class="tool-icon-wrap">
                    <img src="https://img.icons8.com/3d-fluency/94/wordpress.png" alt="WordPress" class="w-9 h-9 object-contain">
                </div>
                <span class="tool-name">WordPress</span>
            </div>

            <div class="tool-chip">
                <div class="tool-icon-wrap">
                    <img src="https://img.icons8.com/arcade/64/laravel.png" alt="Laravel" class="w-9 h-9 object-contain">
                </div>
                <span class="tool-name">Laravel</span>
            </div>

            <div class="tool-chip">
                <div class="tool-icon-wrap">
                    <img src="https://img.icons8.com/officel/80/php-logo.png" alt="PHP" class="w-9 h-9 object-contain">
                </div>
                <span class="tool-name">PHP</span>
            </div>

            <div class="tool-chip">
                <div class="tool-icon-wrap">
                    <img src="https://img.icons8.com/color/48/html-5--v1.png" alt="HTML5" class="w-9 h-9 object-contain">
                </div>
                <span class="tool-name">HTML5</span>
            </div>

            <div class="tool-chip">
                <div class="tool-icon-wrap">
                    <img src="https://img.icons8.com/color/48/shopify.png" alt="Shopify" class="w-9 h-9 object-contain">
                </div>
                <span class="tool-name">Shopify</span>
            </div>

            <div class="tool-chip">
                <div class="tool-icon-wrap">
                    <img src="https://img.icons8.com/external-those-icons-flat-those-icons/24/external-Drupal-Logo-social-media-those-icons-flat-those-icons.png" alt="Drupal" class="w-8 h-8 object-contain">
                </div>
                <span class="tool-name">Drupal</span>
            </div>

            <div class="tool-chip">
                <div class="tool-icon-wrap">
                    <img src="https://img.icons8.com/external-those-icons-flat-those-icons/24/external-Joomla-Logo-social-media-those-icons-flat-those-icons.png" alt="Joomla" class="w-8 h-8 object-contain">
                </div>
                <span class="tool-name">Joomla</span>
            </div>

            <div class="tool-chip">
                <div class="tool-icon-wrap">
                    <img src="https://img.icons8.com/color/48/woocommerce.png" alt="WooCommerce" class="w-9 h-9 object-contain">
                </div>
                <span class="tool-name">WooCommerce</span>
            </div>

            <div class="tool-chip">
                <div class="tool-icon-wrap">
                    <img src="https://img.icons8.com/color/48/adobe-after-effects--v1.png" alt="After Effects" class="w-9 h-9 object-contain">
                </div>
                <span class="tool-name">After Effects</span>
            </div>

            <div class="tool-chip">
                <div class="tool-icon-wrap">
                    <img src="https://img.icons8.com/color/48/adobe-premiere-pro--v1.png" alt="Premiere Pro" class="w-9 h-9 object-contain">
                </div>
                <span class="tool-name">Premiere Pro</span>
            </div>
            <div class="tool-chip">
                <div class="tool-icon-wrap">
                    <img width="48" height="48" src="https://img.icons8.com/fluency/48/coreldraw-2021.png" alt="coreldraw-2021" class="w-9 h-9 object-contain"/>
                </div>
                <span class="tool-name">CorelDraw</span>
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

@push('styles')
<style>
    @keyframes tools-scroll {
        from { transform: translateX(0); }
        to   { transform: translateX(-100%); }
    }
    .tools-track {
        animation: tools-scroll 32s linear infinite;
        flex-shrink: 0;
        white-space: nowrap;
    }
    .tools-marquee-outer:hover .tools-track {
        animation-play-state: paused;
    }
    .tool-chip {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.45rem;
        flex-shrink: 0;
        cursor: default;
    }
    .tool-chip:hover {
        transform: translateY(-4px);
        transition: transform 0.3s ease;
    }
    .tool-icon-wrap {
        width: 64px;
        height: 64px;
        background: rgba(255,255,255,0.04);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease, transform 0.3s ease;
    }
    .tool-chip:hover .tool-icon-wrap {
        background: rgba(255, 195, 0, 0.07);
        border-color: rgba(255, 195, 0, 0.35);
        box-shadow: 0 0 22px rgba(255, 195, 0, 0.18);
        transform: scale(1.12);
    }
    .tool-name {
        font-size: 0.68rem;
        font-weight: 500;
        color: #6b7280;
        transition: color 0.3s ease;
        user-select: none;
    }
    .tool-chip:hover .tool-name {
        color: #e5e7eb;
    }
</style>
@endpush

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
