@extends('layouts.app')

@php use App\Helpers\CurrencyHelper; @endphp

@section('title', 'Order Confirmed - CreativeMarket')

@section('content')
<section class="py-16 md:py-20">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h1 class="text-4xl font-bold mb-4">Order <span class="text-green-500">Confirmed</span>!</h1>
        <p class="text-xl text-gray-600 mb-2">Thank you for your purchase!</p>
        <p class="text-gray-500 mb-8">Your order number is: <span class="font-semibold text-gray-900">{{ $order->order_number }}</span></p>

        <div class="bg-white border border-gray-200 rounded-2xl p-8 text-left mb-8">
            <h2 class="text-lg font-bold mb-4">Order Details</h2>
            <div class="space-y-3">
                @foreach($order->items as $item)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                    <div class="flex items-center space-x-3 min-w-0">
                        <div class="w-10 h-10 bg-gray-200 rounded-lg flex items-center justify-center flex-shrink-0">
                            <span class="text-xs font-bold">{{ substr($item->product->title, 0, 2) }}</span>
                        </div>
                        <div class="min-w-0">
                            <p class="font-semibold text-sm truncate">{{ $item->product->title }}</p>
                            <p class="text-xs text-gray-500">by {{ $item->product->author->name }}</p>
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0 ml-3">
                        <span class="font-bold">{{ CurrencyHelper::format($item->price) }}</span>
                        <div>
                            <form method="POST" action="{{ route('products.download', $item->product) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-xs text-[#FFC300] hover:text-black font-semibold">Download</button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <hr class="border-gray-200 my-4">
            <div class="flex justify-between font-bold text-lg">
                <span>Total Paid</span>
                <span class="text-[#FFC300]">{{ CurrencyHelper::format($order->total_amount) }}</span>
            </div>
            <div class="flex justify-between text-sm mt-2">
                <span class="text-gray-500">Payment Method</span>
                <span class="font-semibold capitalize">{{ $order->payment_method }}</span>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('orders.index') }}" class="bg-black text-white px-6 py-3 rounded-xl font-semibold hover:bg-gray-800 transition-colors w-full sm:w-auto text-center">
                View My Orders
            </a>
            <a href="{{ route('products.index') }}" class="border border-gray-300 text-gray-700 px-6 py-3 rounded-xl font-semibold hover:bg-gray-50 transition-colors w-full sm:w-auto text-center">
                Continue Shopping
            </a>
        </div>
    </div>
</section>
@endsection
