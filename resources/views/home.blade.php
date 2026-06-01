@extends('layouts.app')

@php use App\Helpers\CurrencyHelper; @endphp

@section('content')
<style>
    /* ========== Animations ========== */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    @keyframes slideInLeft {
        from { opacity: 0; transform: translateX(-40px); }
        to { opacity: 1; transform: translateX(0); }
    }
    @keyframes slideInRight {
        from { opacity: 0; transform: translateX(40px); }
        to { opacity: 1; transform: translateX(0); }
    }
    @keyframes scaleIn {
        from { opacity: 0; transform: scale(0.9); }
        to { opacity: 1; transform: scale(1); }
    }
    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
    @keyframes shimmer {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }
    @keyframes pulseGlow {
        0%, 100% { box-shadow: 0 0 20px rgba(255, 195, 0, 0.2); }
        50% { box-shadow: 0 0 40px rgba(255, 195, 0, 0.4); }
    }
    @keyframes gradientShift {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    .animate-fade-in-up { animation: fadeInUp 0.8s ease-out forwards; }
    .animate-fade-in { animation: fadeIn 1s ease-out forwards; }
    .animate-slide-in-left { animation: slideInLeft 0.8s ease-out forwards; }
    .animate-slide-in-right { animation: slideInRight 0.8s ease-out forwards; }
    .animate-scale-in { animation: scaleIn 0.6s ease-out forwards; }
    .animate-float { animation: float 3s ease-in-out infinite; }
    .animate-shimmer { background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent); background-size: 200% 100%; animation: shimmer 2s infinite; }
    .animate-gradient { background-size: 200% 200%; animation: gradientShift 3s ease infinite; }
    .animate-pulse-glow { animation: pulseGlow 2s ease-in-out infinite; }

    .stagger-1 { animation-delay: 0.1s; animation-fill-mode: both; }
    .stagger-2 { animation-delay: 0.2s; animation-fill-mode: both; }
    .stagger-3 { animation-delay: 0.3s; animation-fill-mode: both; }
    .stagger-4 { animation-delay: 0.4s; animation-fill-mode: both; }
    .stagger-5 { animation-delay: 0.5s; animation-fill-mode: both; }
    .stagger-6 { animation-delay: 0.6s; animation-fill-mode: both; }

    .reveal {
        opacity: 0;
        transform: translateY(30px);
        transition: all 0.8s ease-out;
    }
    .reveal.visible {
        opacity: 1;
        transform: translateY(0);
    }

    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>

<!-- Hero Section -->
<section class="relative bg-black text-white overflow-hidden min-h-[80vh] flex items-center">
    <div class="absolute inset-0 bg-gradient-to-br from-[#FFC300]/10 via-black to-black animate-gradient"></div>
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: url(&quot;data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23FFC300' fill-opacity='0.4'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E&quot;)"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-28 lg:py-36">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <div class="inline-flex items-center px-4 py-1.5 bg-[#FFC300]/10 rounded-full text-[#FFC300] text-xs font-semibold mb-6 border border-[#FFC300]/20 animate-fade-in">
                    <span class="w-2 h-2 bg-[#FFC300] rounded-full mr-2 animate-pulse"></span>
                    A Product of <strong class="ml-1">Bellah Options</strong>
                </div>
                <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-bold tracking-tight leading-[1.1] animate-fade-in-up">
                    Get Premium Creative & 
                    <span class="text-[#FFC300]">Web Resources</span>
                    <br>for as Affordable as 
                    <span class="text-[#FFC300]">{{ CurrencyHelper::formatInt(3000) }}</span>
                </h1>
                <p class="mt-6 text-base sm:text-lg md:text-xl text-gray-400 leading-relaxed max-w-xl animate-fade-in-up stagger-2">
                    Unlock thousands of premium WordPress themes, plugins, design templates, and digital assets crafted by world-class creators. Start building your dream projects today without breaking the bank.
                </p>
                <div class="mt-8 flex flex-wrap items-center gap-4 animate-fade-in-up stagger-3">
                    <a href="{{ route('products.index') }}" class="bg-[#FFC300] text-black px-8 py-4 rounded-xl text-base font-bold hover:bg-[#FFD633] transition-all transform hover:scale-105 shadow-lg shadow-[#FFC300]/25 w-full sm:w-auto text-center animate-pulse-glow">
                        Explore Marketplace
                    </a>
                    <a href="#featured" class="border border-gray-700 text-white px-8 py-4 rounded-xl text-base font-semibold hover:border-[#FFC300] hover:text-[#FFC300] transition-all w-full sm:w-auto text-center">
                        View Featured Items
                    </a>
                </div>
                <div class="mt-8 flex items-center space-x-6 text-sm text-gray-500 animate-fade-in-up stagger-4">
                    <span class="flex items-center"><svg class="w-4 h-4 text-green-400 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Instant Download</span>
                    <span class="flex items-center"><svg class="w-4 h-4 text-green-400 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Lifetime Updates</span>
                    <span class="flex items-center"><svg class="w-4 h-4 text-green-400 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Secure Payment</span>
                </div>
            </div>
            <div class="hidden lg:flex items-center justify-center animate-fade-in stagger-3">
                <div class="relative">
                    <div class="w-80 h-80 rounded-full bg-gradient-to-br from-[#FFC300]/30 via-[#FFC300]/10 to-transparent animate-float"></div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-black/60 backdrop-blur-xl rounded-2xl p-5 border border-gray-800 text-center">
                                <div class="text-3xl font-bold text-[#FFC300]">{{ \App\Models\Product::count() }}+</div>
                                <div class="text-xs text-gray-400 mt-1">Premium Items</div>
                            </div>
                            <div class="bg-black/60 backdrop-blur-xl rounded-2xl p-5 border border-gray-800 text-center">
                                <div class="text-3xl font-bold text-[#FFC300]">{{ \App\Models\User::count() }}+</div>
                                <div class="text-xs text-gray-400 mt-1">Happy Users</div>
                            </div>
                            <div class="bg-black/60 backdrop-blur-xl rounded-2xl p-5 border border-gray-800 text-center">
                                <div class="text-3xl font-bold text-[#FFC300]">{{ \App\Models\User::authors()->count() }}+</div>
                                <div class="text-xs text-gray-400 mt-1">Expert Authors</div>
                            </div>
                            <div class="bg-black/60 backdrop-blur-xl rounded-2xl p-5 border border-gray-800 text-center">
                                <div class="text-3xl font-bold text-[#FFC300]">{{ number_format(\App\Models\Product::sum('download_count')) }}+</div>
                                <div class="text-xs text-gray-400 mt-1">Downloads</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

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

<!-- Why Templatr Section (NEW - Replaces Pricing Section) -->
<section class="py-16 md:py-20 bg-white overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 reveal">
            <h2 class="text-3xl md:text-4xl font-bold">Why Choose <span class="text-[#FFC300]">Templatr</span>?</h2>
            <p class="mt-4 text-gray-500 max-w-2xl mx-auto">Templatr is a product of <a href="https://www.bellahoptions.com" target="_blank" class="text-[#FFC300] hover:text-black font-semibold">Bellah Options</a> — your trusted source for premium digital resources at prices that won't break the bank.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="group bg-white border border-gray-200 rounded-2xl p-6 hover:border-[#FFC300] hover:shadow-xl transition-all duration-500 reveal stagger-1">
                <div class="w-14 h-14 bg-[#FFC300]/10 rounded-2xl flex items-center justify-center mb-4 group-hover:bg-[#FFC300] group-hover:scale-110 transition-all duration-300">
                    <svg class="w-7 h-7 text-[#FFC300] group-hover:text-black transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="font-bold text-lg mb-2">Affordable Pricing</h3>
                <p class="text-gray-500 text-sm leading-relaxed">Get premium resources starting from as low as {{ CurrencyHelper::formatInt(3000) }}. No hidden fees, no subscriptions — just one-time payments.</p>
            </div>
            <div class="group bg-white border border-gray-200 rounded-2xl p-6 hover:border-[#FFC300] hover:shadow-xl transition-all duration-500 reveal stagger-2">
                <div class="w-14 h-14 bg-[#FFC300]/10 rounded-2xl flex items-center justify-center mb-4 group-hover:bg-[#FFC300] group-hover:scale-110 transition-all duration-300">
                    <svg class="w-7 h-7 text-[#FFC300] group-hover:text-black transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <h3 class="font-bold text-lg mb-2">Secure Payments</h3>
                <p class="text-gray-500 text-sm leading-relaxed">Pay securely via Paystack, Flutterwave, or Interswitch. Your transactions are 100% safe and encrypted.</p>
            </div>
            <div class="group bg-white border border-gray-200 rounded-2xl p-6 hover:border-[#FFC300] hover:shadow-xl transition-all duration-500 reveal stagger-3">
                <div class="w-14 h-14 bg-[#FFC300]/10 rounded-2xl flex items-center justify-center mb-4 group-hover:bg-[#FFC300] group-hover:scale-110 transition-all duration-300">
                    <svg class="w-7 h-7 text-[#FFC300] group-hover:text-black transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                </div>
                <h3 class="font-bold text-lg mb-2">Instant Downloads</h3>
                <p class="text-gray-500 text-sm leading-relaxed">Get instant access to your purchased resources. Download and start using them immediately in your projects.</p>
            </div>
            <div class="group bg-white border border-gray-200 rounded-2xl p-6 hover:border-[#FFC300] hover:shadow-xl transition-all duration-500 reveal stagger-4">
                <div class="w-14 h-14 bg-[#FFC300]/10 rounded-2xl flex items-center justify-center mb-4 group-hover:bg-[#FFC300] group-hover:scale-110 transition-all duration-300">
                    <svg class="w-7 h-7 text-[#FFC300] group-hover:text-black transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <h3 class="font-bold text-lg mb-2">Community & Support</h3>
                <p class="text-gray-500 text-sm leading-relaxed">Join a thriving community of creatives. Get support from our team and connect with fellow creators.</p>
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
                        <div class="absolute inset-0 flex items-center justify-center">
                            <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        @if($product->sale_price)
                        <div class="absolute top-3 left-3 bg-red-500 text-white text-xs font-bold px-2.5 py-1 rounded-lg">SALE</div>
                        @endif
                        <span class="absolute top-3 right-3 bg-black/60 text-white text-[11px] px-2.5 py-1 rounded-lg backdrop-blur-sm font-medium">{{ ucfirst($product->file_type) }}</span>
                    </div>
                </a>
                <div class="p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] text-gray-500 font-medium">{{ $product->category->name }}</span>
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
                                <span class="text-[10px] font-bold">{{ substr($product->author->name, 0, 1) }}</span>
                            </div>
                            <span class="text-[11px] text-gray-500">{{ $product->author->name }}</span>
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
                        <div class="absolute inset-0 flex items-center justify-center">
                            <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <span class="absolute top-3 left-3 bg-[#FFC300] text-black text-xs font-bold px-2.5 py-1 rounded-lg">NEW</span>
                        <span class="absolute bottom-3 left-3 bg-black/60 text-white text-[11px] px-2.5 py-1 rounded-lg backdrop-blur-sm font-medium">{{ ucfirst($product->file_type) }}</span>
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
                        <span class="text-[11px] text-gray-500">by {{ $product->author->name }}</span>
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

<!-- Top Authors -->
<section class="py-16 md:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 reveal">
            <h2 class="text-3xl md:text-4xl font-bold">Top <span class="text-[#FFC300]">Authors</span></h2>
