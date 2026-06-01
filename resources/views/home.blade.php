@extends('layouts.app')

@section('content')
<!-- Hero Section -->
<section class="relative bg-black text-white overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-br from-[#FFC300]/20 via-black to-black"></div>
    <div class="absolute inset-0 opacity-20" style="background-image: url('data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23FFC300' fill-opacity='0.4'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 md:py-32">
        <div class="text-center max-w-4xl mx-auto">
            <h1 class="text-4xl md:text-6xl lg:text-7xl font-bold tracking-tight leading-tight">
                Premium Digital
                <span class="text-[#FFC300]">Resources</span>
                <br>for Creatives
            </h1>
            <p class="mt-6 text-lg md:text-xl text-gray-300 max-w-2xl mx-auto">
                Discover thousands of high-quality design assets, templates, fonts, graphics, and more — crafted by the world's best creators.
            </p>
            <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('products.index') }}" class="bg-[#FFC300] text-black px-8 py-4 rounded-xl text-base font-bold hover:bg-[#FFD633] transition-all transform hover:scale-105 shadow-lg shadow-[#FFC300]/25">
                    Explore Marketplace
                </a>
                <a href="#featured" class="border border-gray-600 text-white px-8 py-4 rounded-xl text-base font-semibold hover:border-[#FFC300] hover:text-[#FFC300] transition-all">
                    View Featured Items
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="bg-black border-t border-gray-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div>
                <div class="text-3xl md:text-4xl font-bold text-[#FFC300]">{{ \App\Models\Product::count() }}+</div>
                <div class="text-gray-400 text-sm mt-1">Premium Items</div>
            </div>
            <div>
                <div class="text-3xl md:text-4xl font-bold text-[#FFC300]">{{ \App\Models\User::authors()->count() }}+</div>
                <div class="text-gray-400 text-sm mt-1">Expert Authors</div>
            </div>
            <div>
                <div class="text-3xl md:text-4xl font-bold text-[#FFC300]">{{ \App\Models\User::count() }}+</div>
                <div class="text-gray-400 text-sm mt-1">Happy Customers</div>
            </div>
            <div>
                <div class="text-3xl md:text-4xl font-bold text-[#FFC300]">{{ \App\Models\Product::sum('download_count') }}+</div>
                <div class="text-gray-400 text-sm mt-1">Total Downloads</div>
            </div>
        </div>
    </div>
</section>

<!-- Categories -->
<section class="py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold">Browse by <span class="text-[#FFC300]">Category</span></h2>
            <p class="mt-4 text-gray-600 max-w-xl mx-auto">Find exactly what you need from our curated collection of creative resources</p>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
            @foreach($categories as $category)
            <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="group bg-white border border-gray-200 rounded-xl p-6 text-center hover:border-[#FFC300] hover:shadow-lg hover:shadow-[#FFC300]/10 transition-all duration-300">
                <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:bg-[#FFC300] transition-colors">
                    <svg class="w-6 h-6 text-gray-600 group-hover:text-black transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-sm">{{ $category->name }}</h3>
                <p class="text-xs text-gray-500 mt-1">{{ $category->products()->count() }} items</p>
            </a>
            @endforeach
        </div>
    </div>
</section>

<!-- Featured Products -->
<section id="featured" class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-12">
            <div>
                <h2 class="text-3xl md:text-4xl font-bold">Featured <span class="text-[#FFC300]">Items</span></h2>
                <p class="mt-2 text-gray-600">Hand-picked premium resources for you</p>
            </div>
            <a href="{{ route('products.index') }}" class="hidden sm:flex items-center text-sm font-semibold text-black hover:text-[#FFC300] transition-colors">
                View All
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($featuredProducts as $product)
            <div class="group bg-white rounded-xl overflow-hidden border border-gray-200 hover:border-[#FFC300] hover:shadow-xl transition-all duration-300">
                <a href="{{ route('products.show', $product) }}">
                    <div class="aspect-[4/3] bg-gradient-to-br from-gray-100 to-gray-200 relative overflow-hidden">
                        <div class="absolute inset-0 flex items-center justify-center">
                            <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        @if($product->sale_price)
                        <div class="absolute top-3 left-3 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-lg">SALE</div>
                        @endif
                        <span class="absolute top-3 right-3 bg-black/50 text-white text-xs px-2 py-1 rounded-lg backdrop-blur-sm">{{ ucfirst($product->file_type) }}</span>
                    </div>
                </a>
                <div class="p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs text-gray-500">{{ $product->category->name }}</span>
                        <div class="flex items-center text-yellow-400">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <span class="text-xs text-gray-600 ml-1">{{ number_format($product->average_rating, 1) }}</span>
                        </div>
                    </div>
                    <a href="{{ route('products.show', $product) }}">
                        <h3 class="font-semibold text-gray-900 group-hover:text-[#FFC300] transition-colors line-clamp-1">{{ $product->title }}</h3>
                    </a>
                    <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100">
                        <span class="text-xs text-gray-500">by {{ $product->author->name }}</span>
                        <div class="text-right">
                            @if($product->sale_price)
                                <span class="text-xs text-gray-400 line-through">${{ number_format($product->price, 2) }}</span>
                            @endif
                            <span class="font-bold text-gray-900">${{ number_format($product->sale_price ?? $product->price, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- New Arrivals -->
<section class="py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-12">
            <div>
                <h2 class="text-3xl md:text-4xl font-bold">New <span class="text-[#FFC300]">Arrivals</span></h2>
                <p class="mt-2 text-gray-600">Latest additions to our marketplace</p>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($newProducts as $product)
            <div class="group bg-white rounded-xl overflow-hidden border border-gray-200 hover:border-[#FFC300] hover:shadow-xl transition-all duration-300">
                <a href="{{ route('products.show', $product) }}">
                    <div class="aspect-[4/3] bg-gradient-to-br from-gray-100 to-gray-200 relative overflow-hidden">
                        <div class="absolute inset-0 flex items-center justify-center">
                            <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <span class="absolute top-3 right-3 bg-[#FFC300] text-black text-xs font-bold px-2 py-1 rounded-lg">NEW</span>
                        <span class="absolute bottom-3 left-3 bg-black/50 text-white text-xs px-2 py-1 rounded-lg backdrop-blur-sm">{{ ucfirst($product->file_type) }}</span>
                    </div>
                </a>
                <div class="p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs text-gray-500">{{ $product->category->name }}</span>
                        <div class="flex items-center text-yellow-400">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <span class="text-xs text-gray-600 ml-1">{{ number_format($product->average_rating, 1) }}</span>
                        </div>
                    </div>
                    <a href="{{ route('products.show', $product) }}">
                        <h3 class="font-semibold text-gray-900 group-hover:text-[#FFC300] transition-colors line-clamp-1">{{ $product->title }}</h3>
                    </a>
                    <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100">
                        <span class="text-xs text-gray-500">by {{ $product->author->name }}</span>
                        <div class="text-right">
                            @if($product->sale_price)
                                <span class="text-xs text-gray-400 line-through">${{ number_format($product->price, 2) }}</span>
                            @endif
                            <span class="font-bold text-gray-900">${{ number_format($product->sale_price ?? $product->price, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="text-center mt-10">
            <a href="{{ route('products.index') }}" class="inline-flex items-center bg-black text-white px-8 py-3 rounded-xl font-semibold hover:bg-gray-800 transition-colors">
                Browse All Items
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>
    </div>
</section>

<!-- Top Authors -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold">Top <span class="text-[#FFC300]">Authors</span></h2>
            <p class="mt-4 text-gray-600 max-w-xl mx-auto">Meet our talented creators and explore their amazing work</p>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
            @foreach($topAuthors as $author)
            <div class="text-center group">
                <div class="w-20 h-20 bg-gradient-to-br from-[#FFC300] to-[#FFD633] rounded-full flex items-center justify-center mx-auto mb-3">
                    <span class="text-2xl font-bold text-black">{{ substr($author->name, 0, 1) }}</span>
                </div>
                <h4 class="font-semibold text-sm group-hover:text-[#FFC300] transition-colors">{{ $author->name }}</h4>
                <p class="text-xs text-gray-500">{{ $author->products_count }} items</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-20 bg-black">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-white">Ready to start <span class="text-[#FFC300]">creating</span>?</h2>
        <p class="mt-4 text-gray-400 max-w-xl mx-auto text-lg">Join thousands of creatives who trust CreativeMarket for premium digital resources.</p>
        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('register') }}" class="bg-[#FFC300] text-black px-8 py-3 rounded-xl font-bold hover:bg-[#FFD633] transition-colors">Get Started Free</a>
            <a href="{{ route('products.index') }}" class="border border-gray-600 text-white px-8 py-3 rounded-xl font-semibold hover:border-[#FFC300] transition-colors">Browse Marketplace</a>
        </div>
    </div>
</section>
@endsection
