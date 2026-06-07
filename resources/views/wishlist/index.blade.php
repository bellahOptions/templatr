@extends('layouts.app')

@php use App\Helpers\CurrencyHelper; @endphp

@section('title', 'My Wishlist - Templatr')
@section('robots', 'noindex, nofollow')

@section('content')
<section class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold">My <span class="text-[#FFC300]">Wishlist</span></h1>
                <p class="text-gray-600 mt-1">Items you've saved for later</p>
            </div>
            <a href="{{ route('products.index') }}" class="bg-black text-white px-4 py-2.5 rounded-lg text-sm font-semibold hover:bg-gray-800 transition-colors">
                Browse Items
            </a>
        </div>

        @if($wishlists->isEmpty())
        <div class="text-center py-20">
            <svg class="w-24 h-24 mx-auto text-gray-300 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Your wishlist is empty</h2>
            <p class="text-gray-500 mb-6">Save items you love to your wishlist.</p>
            <a href="{{ route('products.index') }}" class="inline-flex items-center bg-black text-white px-6 py-3 rounded-xl font-semibold hover:bg-gray-800 transition-colors">
                Browse Items
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($wishlists as $wishlist)
            <div class="group bg-white rounded-2xl overflow-hidden border border-gray-200 hover:border-[#FFC300] hover:shadow-lg transition-all duration-300">
                <a href="{{ route('products.show', $wishlist->product) }}">
                    <div class="aspect-[4/3] bg-gradient-to-br from-gray-100 to-gray-200 relative">
                        <div class="absolute inset-0 flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <form method="POST" action="{{ route('wishlist.remove', $wishlist->product) }}" class="absolute top-3 right-3">
                            @csrf
                            <button type="submit" class="bg-white/90 p-2 rounded-full hover:bg-red-50 transition-colors shadow-sm">
                                <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </a>
                <div class="p-4">
                    <a href="{{ route('products.show', $wishlist->product) }}">
                        <h3 class="font-semibold text-sm line-clamp-1 group-hover:text-[#FFC300] transition-colors">{{ $wishlist->product->title }}</h3>
                    </a>
                    <div class="flex items-center justify-between mt-2">
                        <span class="text-sm text-gray-500">{{ $wishlist->product->category->name }}</span>
                        <div class="text-right">
                            @if($wishlist->product->sale_price)
                                <span class="text-xs text-gray-400 line-through">{{ CurrencyHelper::format($wishlist->product->price) }}</span>
                            @endif
                            <span class="font-bold">{{ CurrencyHelper::format($wishlist->product->sale_price ?? $wishlist->product->price) }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</section>
@endsection
