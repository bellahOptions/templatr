@extends('layouts.app')

@section('title', 'Shopping Cart - CreativeMarket')

@section('content')
<section class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold">Shopping <span class="text-[#FFC300]">Cart</span></h1>
            <p class="mt-2 text-gray-600">{{ count($cart) }} item(s) in your cart</p>
        </div>

        @if(empty($cart))
        <div class="text-center py-20">
            <svg class="w-24 h-24 mx-auto text-gray-300 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
            </svg>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Your cart is empty</h2>
            <p class="text-gray-500 mb-6">Looks like you haven't added any items yet.</p>
            <a href="{{ route('products.index') }}" class="inline-flex items-center bg-black text-white px-6 py-3 rounded-xl font-semibold hover:bg-gray-800 transition-colors">
                Browse Items
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>
        @else
        <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
            <div class="divide-y divide-gray-200">
                @foreach($products as $product)
                <div class="flex items-center p-6">
                    <div class="w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <div class="ml-4 flex-1">
                        <a href="{{ route('products.show', $product) }}" class="font-semibold hover:text-[#FFC300] transition-colors">{{ $product->title }}</a>
                        <p class="text-sm text-gray-500">{{ $product->category->name }}</p>
                    </div>
                    <div class="text-right flex-shrink-0 ml-4">
                        <div class="font-bold text-lg">${{ number_format($product->sale_price ?? $product->price, 2) }}</div>
                        <form method="POST" action="{{ route('cart.remove', $product) }}">
                            @csrf
                            <button type="submit" class="text-sm text-red-500 hover:text-red-700 mt-1">Remove</button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Summary -->
        <div class="mt-8 bg-white border border-gray-200 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <span class="text-gray-600">Subtotal</span>
                <span class="font-semibold">${{ number_format($total, 2) }}</span>
            </div>
            <div class="flex items-center justify-between mb-4">
                <span class="text-gray-600">Tax</span>
                <span class="font-semibold">$0.00</span>
            </div>
            <hr class="border-gray-200 mb-4">
            <div class="flex items-center justify-between mb-6">
                <span class="text-lg font-bold">Total</span>
                <span class="text-2xl font-bold text-[#FFC300]">${{ number_format($total, 2) }}</span>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="{{ route('products.index') }}" class="flex-1 text-center border border-gray-300 text-gray-700 py-3 rounded-xl font-semibold hover:bg-gray-50 transition-colors">
                    Continue Shopping
                </a>
                <a href="{{ route('checkout.index') }}" class="flex-1 text-center bg-black text-white py-3 rounded-xl font-bold hover:bg-gray-800 transition-colors">
                    Proceed to Checkout
                </a>
            </div>
            <form method="POST" action="{{ route('cart.clear') }}" class="mt-3 text-center">
                @csrf
                <button type="submit" class="text-sm text-gray-500 hover:text-red-500">Clear Cart</button>
            </form>
        </div>
        @endif
    </div>
</section>
@endsection
