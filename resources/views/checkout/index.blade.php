@extends('layouts.app')

@section('title', 'Checkout - CreativeMarket')

@section('content')
<section class="py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold">Check<span class="text-[#FFC300]">out</span></h1>
            <p class="mt-2 text-gray-600">Complete your purchase</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl p-8">
            <!-- Order Summary -->
            <h2 class="text-xl font-bold mb-6">Order Summary</h2>
            <div class="space-y-4 mb-6">
                @foreach($products as $product)
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gray-200 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-sm">{{ $product->title }}</p>
                            <p class="text-xs text-gray-500">{{ $product->category->name }}</p>
                        </div>
                    </div>
                    <span class="font-bold">${{ number_format($product->sale_price ?? $product->price, 2) }}</span>
                </div>
                @endforeach
            </div>

            <hr class="border-gray-200 my-6">

            <!-- Total -->
            <div class="flex items-center justify-between mb-8">
                <span class="text-lg font-bold">Total</span>
                <span class="text-3xl font-bold text-[#FFC300]">${{ number_format($total, 2) }}</span>
            </div>

            <!-- Payment Button -->
            <form method="POST" action="{{ route('checkout.process') }}">
                @csrf
                <button type="submit" class="w-full bg-black text-white py-4 rounded-xl font-bold text-lg hover:bg-gray-800 transition-colors flex items-center justify-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span>Complete Purchase</span>
                </button>
            </form>

            <p class="text-xs text-gray-500 text-center mt-4">
                By completing this purchase you agree to our Terms of Service.
            </p>
        </div>
    </div>
</section>
@endsection
