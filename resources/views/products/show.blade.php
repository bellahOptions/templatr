@extends('layouts.app')

@section('title', $product->title . ' - CreativeMarket')

@section('content')
<!-- Breadcrumb -->
<section class="bg-gray-50 border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex items-center space-x-2 text-sm">
            <a href="{{ route('home') }}" class="text-gray-500 hover:text-[#FFC300]">Home</a>
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('products.index', ['category' => $product->category->slug]) }}" class="text-gray-500 hover:text-[#FFC300]">{{ $product->category->name }}</a>
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-900 font-medium truncate">{{ $product->title }}</span>
        </div>
    </div>
</section>

<!-- Product Details -->
<section class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:grid lg:grid-cols-3 lg:gap-12">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Preview Image -->
                <div class="aspect-video bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl overflow-hidden relative mb-8">
                    <div class="absolute inset-0 flex items-center justify-center">
                        <svg class="w-24 h-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    @if($product->sale_price)
                    <div class="absolute top-4 left-4 bg-red-500 text-white text-sm font-bold px-3 py-1.5 rounded-lg">SALE - Save ${{ number_format($product->price - $product->sale_price, 2) }}</div>
                    @endif
                </div>

                <!-- Title & Actions -->
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ $product->title }}</h1>
                        <div class="flex items-center space-x-4 mt-3">
                            <div class="flex items-center">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-5 h-5 {{ $i <= round($product->average_rating) ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                                <span class="ml-2 text-sm text-gray-600">{{ number_format($product->average_rating, 1) }} ({{ $product->review_count }} reviews)</span>
                            </div>
                            <span class="text-sm text-gray-500">{{ $product->view_count }} views</span>
                        </div>
                    </div>
                    @auth
                    <form method="POST" action="{{ route('wishlist.toggle', $product) }}" class="inline">
                        @csrf
                        <button type="submit" class="p-2 border border-gray-200 rounded-lg hover:border-red-300 hover:text-red-500 transition-colors {{ $isInWishlist ? 'text-red-500 border-red-300 bg-red-50' : 'text-gray-400' }}">
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
                        <p>{{ $product->description }}</p>
                    </div>
                </div>

                <!-- Details -->
                <div class="grid grid-cols-2 gap-6 mb-8 p-6 bg-gray-50 rounded-xl">
                    @if($product->version)
                    <div>
                        <span class="text-xs text-gray-500 uppercase tracking-wider">Version</span>
                        <p class="font-semibold mt-1">{{ $product->version }}</p>
                    </div>
                    @endif
                    @if($product->file_size)
                    <div>
                        <span class="text-xs text-gray-500 uppercase tracking-wider">File Size</span>
                        <p class="font-semibold mt-1">{{ number_format($product->file_size / 1048576, 2) }} MB</p>
                    </div>
                    @endif
                    <div>
                        <span class="text-xs text-gray-500 uppercase tracking-wider">File Type</span>
                        <p class="font-semibold mt-1 capitalize">{{ $product->file_type }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase tracking-wider">Downloads</span>
                        <p class="font-semibold mt-1">{{ number_format($product->download_count) }}</p>
                    </div>
                    @if($product->requirements)
                    <div class="col-span-2">
                        <span class="text-xs text-gray-500 uppercase tracking-wider">Requirements</span>
                        <p class="font-semibold mt-1">{{ $product->requirements }}</p>
                    </div>
                    @endif
                    @if($product->tags)
                    <div class="col-span-2">
                        <span class="text-xs text-gray-500 uppercase tracking-wider">Tags</span>
                        <div class="flex flex-wrap gap-2 mt-2">
                            @foreach(json_decode($product->tags, true) ?? [] as $tag)
                            <a href="{{ route('products.index', ['search' => $tag]) }}" class="px-3 py-1 bg-white border border-gray-200 rounded-full text-xs text-gray-600 hover:border-[#FFC300] hover:text-black transition-colors">{{ $tag }}</a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Reviews -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold mb-6">Reviews ({{ $reviews->count() }})</h2>
                    
                    @auth
                        @if(!$userReview && $hasPurchased)
                        <div class="bg-gray-50 rounded-xl p-6 mb-6">
                            <h3 class="font-semibold mb-4">Write a Review</h3>
                            <form method="POST" action="{{ route('products.review', $product) }}">
                                @csrf
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
                                    <div class="flex space-x-1">
                                        @for($i = 5; $i >= 1; $i--)
                                        <label class="cursor-pointer">
                                            <input type="radio" name="rating" value="{{ $i }}" class="hidden" required>
                                            <svg class="w-8 h-8 text-gray-300 hover:text-yellow-400 star-{{ $i }}" fill="currentColor" viewBox="0 0 20 20"
                                                onmouseenter="this.classList.add('text-yellow-400')"
                                                onmouseleave="document.querySelectorAll('input[name=rating]:checked').length == 0 ? this.classList.remove('text-yellow-400') : ''">
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
                                <button type="submit" class="bg-black text-white px-6 py-2 rounded-lg text-sm font-semibold hover:bg-gray-800 transition-colors">Submit Review</button>
                            </form>
                        </div>
                        @endif
                    @endauth

                    @if($reviews->isEmpty())
                    <div class="text-center py-10">
                        <p class="text-gray-500">No reviews yet. Be the first to review!</p>
                    </div>
                    @else
                    <div class="space-y-4">
                        @foreach($reviews as $review)
                        <div class="border border-gray-200 rounded-xl p-5">
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
                            <span class="text-3xl font-bold text-[#FFC300]">${{ number_format($product->sale_price, 2) }}</span>
                            <span class="text-lg text-gray-400 line-through ml-2">${{ number_format($product->price, 2) }}</span>
                        </div>
                        @else
                        <span class="text-3xl font-bold">${{ number_format($product->price, 2) }}</span>
                        @endif
                        <p class="text-xs text-gray-500 mt-1">One-time payment</p>
                    </div>

                    <!-- Actions -->
                    @auth
                        @if($hasPurchased)
                            <form method="POST" action="{{ route('products.download', $product) }}">
                                @csrf
                                <button type="submit" class="w-full bg-green-500 text-white py-3 rounded-xl font-semibold hover:bg-green-600 transition-colors flex items-center justify-center space-x-2 mb-3">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    <span>Download Now</span>
                                </button>
                            </form>
                            <p class="text-xs text-green-600 text-center">✓ Purchased</p>
                        @else
                            <form method="POST" action="{{ route('cart.add', $product) }}">
                                @csrf
                                <button type="submit" class="w-full bg-[#FFC300] text-black py-3 rounded-xl font-bold hover:bg-[#FFD633] transition-colors flex items-center justify-center space-x-2 mb-3">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                                    <span>Add to Cart</span>
                                </button>
                            </form>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="block w-full bg-[#FFC300] text-black py-3 rounded-xl font-bold hover:bg-[#FFD633] transition-colors text-center mb-3">
                            Sign In to Purchase
                        </a>
                    @endauth

                    <!-- Info Points -->
                    <div class="space-y-3 mt-6 pt-6 border-t border-gray-100">
                        <div class="flex items-center space-x-3 text-sm text-gray-600">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            <span>Lifetime access</span>
                        </div>
                        <div class="flex items-center space-x-3 text-sm text-gray-600">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Free updates</span>
                        </div>
                        <div class="flex items-center space-x-3 text-sm text-gray-600">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            <span>Secure payment</span>
                        </div>
                        <div class="flex items-center space-x-3 text-sm text-gray-600">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
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
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold mb-8">Related <span class="text-[#FFC300]">Items</span></h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($relatedProducts as $related)
            <div class="group bg-white rounded-xl overflow-hidden border border-gray-200 hover:border-[#FFC300] hover:shadow-lg transition-all duration-300">
                <a href="{{ route('products.show', $related) }}">
                    <div class="aspect-[4/3] bg-gradient-to-br from-gray-100 to-gray-200 relative">
                        <div class="absolute inset-0 flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        </div>
                    </div>
                </a>
                <div class="p-4">
                    <a href="{{ route('products.show', $related) }}">
                        <h3 class="font-semibold text-sm line-clamp-1 group-hover:text-[#FFC300] transition-colors">{{ $related->title }}</h3>
                    </a>
                    <div class="flex items-center justify-between mt-2">
                        <span class="text-xs text-gray-500">${{ number_format($related->sale_price ?? $related->price, 2) }}</span>
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
