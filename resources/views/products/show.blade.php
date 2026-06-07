@extends('layouts.app')

@php
    use App\Helpers\CurrencyHelper;
    $productImage = $product->thumbnail
        ? (str_starts_with($product->thumbnail, 'http') ? $product->thumbnail : asset('storage/' . $product->thumbnail))
        : asset('og-image.jpg');
    $productDescription = substr(strip_tags($product->description ?? ''), 0, 155);
    $productPrice = $product->sale_price ?? $product->price;
@endphp

@section('title', $product->title . ' - Templatr')
@section('meta_description', $productDescription)
@section('og_type', 'product')
@section('og_title', $product->title . ' - Templatr')
@section('og_description', $productDescription)
@section('og_image', $productImage)
@section('og_url', route('products.show', $product))
@section('canonical', route('products.show', $product))

@push('structured_data')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "Product",
    "name": "{{ addslashes($product->title) }}",
    "description": "{{ addslashes($productDescription) }}",
    "image": "{{ $productImage }}",
    "url": "{{ route('products.show', $product) }}",
    "sku": "{{ $product->slug }}",
    "offers": {
        "@@type": "Offer",
        "price": "{{ $productPrice }}",
        "priceCurrency": "{{ CurrencyHelper::CODE }}",
        "availability": "https://schema.org/InStock",
        "url": "{{ route('products.show', $product) }}"
    }@if($product->reviews_count ?? false),
    "aggregateRating": {
        "@@type": "AggregateRating",
        "ratingValue": "{{ number_format($product->average_rating ?? 0, 1) }}",
        "reviewCount": "{{ $product->reviews_count }}"
    }@endif
}
</script>
@endpush

@section('content')
<!-- Breadcrumb -->
<section class="bg-gray-50 border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex items-center space-x-2 text-sm overflow-x-auto scrollbar-hide">
            <a href="{{ route('home') }}" class="text-gray-500 hover:text-[#FFC300] whitespace-nowrap">Home</a>
            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('products.index', $product->category ? ['category' => $product->category->slug] : []) }}" class="text-gray-500 hover:text-[#FFC300] whitespace-nowrap">{{ $product->category?->name ?? 'Uncategorized' }}</a>
            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-900 font-medium truncate">{{ $product->title }}</span>
        </div>
    </div>
</section>

<!-- Product Details -->
<section class="py-8 md:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:grid lg:grid-cols-3 lg:gap-12">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Preview Image -->
                <div class="aspect-video bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl overflow-hidden relative mb-8">
                    @if($product->preview_image)
                    <img src="{{ $product->preview_image_url }}" class="w-full h-full object-cover" alt="{{ $product->title }}" loading="lazy">
                    @elseif($product->thumbnail)
                    <img src="{{ $product->thumbnail_url }}" class="w-full h-full object-cover" alt="{{ $product->title }}" loading="lazy">
                    @else
                    <div class="absolute inset-0 flex items-center justify-center opacity-30 grayscale">
                        <img src="/templatr-logo.svg" class="w-32 h-auto" alt="Templatr" loading="lazy">
                    </div>
                    @endif
                    @if($product->sale_price)
                    <div class="absolute top-4 left-4 bg-red-500 text-white text-sm font-bold px-3 py-1.5 rounded-lg">SALE - Save {{ CurrencyHelper::format($product->price - $product->sale_price) }}</div>
                    @endif
                </div>

                <!-- Title & Actions -->
                <div class="flex items-start justify-between mb-6">
                    <div class="flex-1 min-w-0">
                        <h1 class="text-2xl md:text-3xl font-bold text-gray-900">{{ $product->title }}</h1>
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-2 mt-3">
                            <div class="flex items-center">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-4 h-4 md:w-5 md:h-5 {{ $i <= round($product->average_rating) ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                                <span class="ml-2 text-sm text-gray-600">{{ number_format($product->average_rating, 1) }} ({{ $product->review_count }} reviews)</span>
                            </div>
                            <span class="text-sm text-gray-500">{{ number_format($product->view_count) }} views</span>
                        </div>
                    </div>
                    @auth
                    <form method="POST" action="{{ route('wishlist.toggle', $product) }}" class="inline flex-shrink-0 ml-4">
                        @csrf
                        <button type="submit" class="p-2.5 border border-gray-200 rounded-xl hover:border-red-300 hover:text-red-500 transition-colors {{ $isInWishlist ? 'text-red-500 border-red-300 bg-red-50' : 'text-gray-400' }}">
                            <svg class="w-5 h-5" fill="{{ $isInWishlist ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </button>
                    </form>
                    @endauth
                </div>

                <!-- Description -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">Description</h2>
                    <div class="prose prose-gray max-w-none">
                        {!! $product->description !!}
                    </div>
                </div>

                <!-- Features -->
                @if($product->features && count($product->features) > 0)
                <div class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">Features</h2>
                    <ul class="space-y-2.5">
                        @foreach($product->features as $feature)
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-[#FFC300] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span class="text-gray-700 text-sm leading-relaxed">{{ $feature }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- Details -->
                <div class="grid grid-cols-2 gap-4 md:gap-6 mb-8 p-6 bg-gray-50 rounded-2xl">
                    @if($product->version)
                    <div>
                        <span class="text-xs text-gray-500 uppercase tracking-wider font-medium">Version</span>
                        <p class="font-semibold mt-1">{{ $product->version }}</p>
                    </div>
                    @endif
                    @if($product->file_size)
                    <div>
                        <span class="text-xs text-gray-500 uppercase tracking-wider font-medium">File Size</span>
                        <p class="font-semibold mt-1">{{ number_format($product->file_size, 2) }} MB</p>
                    </div>
                    @endif
                    <div>
                        <span class="text-xs text-gray-500 uppercase tracking-wider font-medium">File Type</span>
                        <p class="font-semibold mt-1 capitalize">{{ $product->file_type }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase tracking-wider font-medium">Downloads</span>
                        <p class="font-semibold mt-1">{{ number_format($product->download_count) }}</p>
                    </div>
                    @if($product->requirements)
                    <div class="col-span-2">
                        <span class="text-xs text-gray-500 uppercase tracking-wider font-medium">Requirements</span>
                        <p class="font-semibold mt-1">{{ $product->requirements }}</p>
                    </div>
                    @endif
                    @if($product->tags)
                    <div class="col-span-2">
                        <span class="text-xs text-gray-500 uppercase tracking-wider font-medium">Tags</span>
                        <div class="flex flex-wrap gap-2 mt-2">
                            @foreach(json_decode($product->tags, true) ?? [] as $tag)
                            <a href="{{ route('products.index', ['search' => $tag]) }}" class="px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-xs text-gray-600 hover:border-[#FFC300] hover:text-black transition-colors">{{ $tag }}</a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Reviews -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold mb-6">Reviews <span class="text-gray-400 text-base font-normal">({{ $reviews->count() }})</span></h2>
                    
                    @auth
                        @if(!$userReview && $hasPurchased)
                        <div class="bg-gray-50 rounded-2xl p-6 mb-6">
                            <h3 class="font-semibold mb-4">Write a Review</h3>
                            <form method="POST" action="{{ route('products.review', $product) }}">
                                @csrf
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
                                    <div class="flex space-x-1" x-data="{ rating: 0 }">
                                        @for($i = 5; $i >= 1; $i--)
                                        <label class="cursor-pointer" @mouseenter="rating = {{ $i }}" @mouseleave="rating = 0">
                                            <input type="radio" name="rating" value="{{ $i }}" class="hidden" @change="rating = {{ $i }}" required>
                                            <svg class="w-8 h-8 transition-colors" :class="rating >= {{ $i }} ? 'text-yellow-400' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        </label>
                                        @endfor
                                    </div>
                                    @error('rating')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="mb-4">
                                    <label for="review" class="block text-sm font-medium text-gray-700 mb-2">Your Review</label>
                                    <textarea id="review" name="review" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]" placeholder="Share your experience..."></textarea>
                                </div>
                                <button type="submit" class="bg-black text-white px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-gray-800 transition-colors">Submit Review</button>
                            </form>
                        </div>
                        @endif
                    @endauth

                    @if($reviews->isEmpty())
                    <div class="text-center py-10 bg-gray-50 rounded-2xl">
                        <p class="text-gray-500">No reviews yet. Be the first to review!</p>
                    </div>
                    @else
                    <div class="space-y-4">
                        @foreach($reviews as $review)
                        <div class="border border-gray-200 rounded-2xl p-5">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                                        <span class="font-bold text-sm">{{ substr($review->user->name, 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-sm">{{ $review->user->name }}</p>
                                        <div class="flex items-center">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            @endfor
                                        </div>
                                    </div>
                                </div>
                                <span class="text-xs text-gray-400">{{ $review->created_at->diffForHumans() }}</span>
                            </div>
                            @if($review->review)
                            <p class="text-gray-600 text-sm">{{ $review->review }}</p>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white border border-gray-200 rounded-2xl p-6 sticky top-24 shadow-sm">
                    <!-- Author Info -->
                    <div class="text-center mb-6 pb-6 border-b border-gray-100">
                        <div class="w-16 h-16 bg-gradient-to-br from-[#FFC300] to-[#FFD633] rounded-full flex items-center justify-center mx-auto mb-3">
                            <span class="text-xl font-bold text-black">{{ substr($product->author->name, 0, 1) }}</span>
                        </div>
                        <h3 class="font-semibold">{{ $product->author->name }}</h3>
                        <p class="text-xs text-gray-500">{{ $product->author->products()->count() }} items</p>
                    </div>

                    <!-- Price -->
                    <div class="text-center mb-6">
                        @if($product->sale_price)
                        <div>
                            <span class="text-3xl font-bold text-[#FFC300]">{{ CurrencyHelper::format($product->sale_price) }}</span>
                            <span class="text-lg text-gray-400 line-through ml-2">{{ CurrencyHelper::format($product->price) }}</span>
                        </div>
                        @else
                        <span class="text-3xl font-bold">{{ CurrencyHelper::format($product->price) }}</span>
                        @endif
                        <p class="text-xs text-gray-500 mt-1">One-time payment • Lifetime access</p>
                    </div>

                    <!-- Actions -->
                    @auth
                        @if($hasPurchased)
                            <form method="POST" action="{{ route('products.download', $product) }}">
                                @csrf
                                <button type="submit" class="w-full bg-green-500 text-white py-3.5 rounded-xl font-semibold hover:bg-green-600 transition-colors flex items-center justify-center space-x-2 mb-3">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    <span>Download Now</span>
                                </button>
                            </form>
                            @if($downloadInfo)
                                <p class="text-xs text-center {{ $downloadInfo['is_downloadable'] ? 'text-green-600' : 'text-red-500' }}">
                                    {{ $downloadInfo['is_downloadable'] ? '✓ Purchased (' . $downloadInfo['remaining_downloads'] . ' download(s) left)' : '✗ Download limit reached (max ' . $downloadInfo['max_downloads'] . ')' }}
                                </p>
                            @else
                                <p class="text-xs text-green-600 text-center">✓ Purchased</p>
                            @endif
                        @else
                            <form
                                method="POST"
                                action="{{ route('cart.add', $product) }}"
                                x-data="{ loading: false, added: false }"
                                @submit.prevent="
                                    if (loading || added) return;
                                    loading = true;
                                    fetch($el.action, {
                                        method: 'POST',
                                        body: new FormData($el),
                                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                                    })
                                    .then(r => r.json())
                                    .then(data => {
                                        loading = false;
                                        added = true;
                                        window.dispatchEvent(new CustomEvent('cart-updated'));
                                        window.dispatchEvent(new CustomEvent('show-toast', {
                                            detail: { message: data.message || 'Added to cart!', type: data.success ? 'success' : 'info' }
                                        }));
                                    })
                                    .catch(() => { loading = false; $el.submit(); })
                                "
                            >
                                @csrf
                                <button
                                    type="submit"
                                    :disabled="loading || added"
                                    class="w-full bg-[#FFC300] text-black py-3.5 rounded-xl font-bold hover:bg-[#FFD633] active:scale-[0.98] transition-all flex items-center justify-center space-x-2 mb-3 disabled:opacity-70"
                                >
                                    <svg x-show="!loading && !added" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                                    <svg x-show="added" class="w-5 h-5 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <svg x-show="loading" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
                                    <span x-text="added ? 'In Cart ✓' : loading ? 'Adding...' : 'Add to Cart - {{ CurrencyHelper::format($product->sale_price ?? $product->price) }}'"></span>
                                </button>
                            </form>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="block w-full bg-[#FFC300] text-black py-3.5 rounded-xl font-bold hover:bg-[#FFD633] transition-colors text-center mb-3">
                            Sign In to Purchase
                        </a>
                    @endauth

                    <!-- Info Points -->
                    <div class="space-y-3 mt-6 pt-6 border-t border-gray-100">
                        <div class="flex items-center space-x-3 text-sm text-gray-600">
                            <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            <span>Lifetime access</span>
                        </div>
                        <div class="flex items-center space-x-3 text-sm text-gray-600">
                            <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Free updates</span>
                        </div>
                        <div class="flex items-center space-x-3 text-sm text-gray-600">
                            <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            <span>Secure payment via Paystack, Flutterwave, Interswitch</span>
                        </div>
                        <div class="flex items-center space-x-3 text-sm text-gray-600">
                            <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <span>6 months support</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related Products -->
@if($relatedProducts->isNotEmpty())
<section class="py-12 md:py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold mb-8">Related <span class="text-[#FFC300]">Items</span></h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach($relatedProducts as $related)
            <div class="group bg-white rounded-2xl overflow-hidden border border-gray-200 hover:border-[#FFC300] hover:shadow-lg transition-all duration-300">
                <a href="{{ route('products.show', $related) }}">
                    <div class="aspect-[4/3] bg-gradient-to-br from-gray-100 to-gray-200 relative overflow-hidden">
                        @if($related->thumbnail)
                        <img src="{{ $related->thumbnail_url }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" alt="{{ $related->title }}" loading="lazy">
                        @elseif($related->preview_image)
                        <img src="{{ $related->preview_image_url }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" alt="{{ $related->title }}" loading="lazy">
                        @else
                        <div class="absolute inset-0 flex items-center justify-center opacity-30 grayscale">
                            <img src="/templatr-logo.svg" class="w-14 h-auto" alt="Templatr" loading="lazy">
                        </div>
                        @endif
                    </div>
                </a>
                <div class="p-4">
                    <a href="{{ route('products.show', $related) }}">
                        <h3 class="font-semibold text-sm line-clamp-1 group-hover:text-[#FFC300] transition-colors">{{ $related->title }}</h3>
                    </a>
                    <div class="flex items-center justify-between mt-2">
                        <span class="font-bold text-sm">{{ CurrencyHelper::format($related->sale_price ?? $related->price) }}</span>
                        <div class="flex items-center">
                            <svg class="w-3.5 h-3.5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <span class="text-xs text-gray-600 ml-1">{{ number_format($related->average_rating, 1) }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection
