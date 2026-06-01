@extends('layouts.app')

@section('title', 'Order Details - CreativeMarket')

@section('content')
<section class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <div class="flex items-center space-x-2 text-sm mb-8">
            <a href="{{ route('orders.index') }}" class="text-gray-500 hover:text-[#FFC300]">My Orders</a>
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-900 font-medium">{{ $order->order_number }}</span>
        </div>

        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold">Order <span class="text-[#FFC300]">Details</span></h1>
                <p class="text-gray-600 mt-1">Placed on {{ $order->created_at->format('F d, Y \a\t g:i A') }}</p>
            </div>
            <span class="px-4 py-2 rounded-full text-sm font-semibold
                @if($order->status == 'completed') bg-green-100 text-green-700
                @elseif($order->status == 'pending') bg-yellow-100 text-yellow-700
                @else bg-red-100 text-red-700
                @endif">
                {{ ucfirst($order->status) }}
            </span>
        </div>

        <!-- Order Items -->
        <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden mb-8">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h2 class="font-semibold">Items Purchased</h2>
            </div>
            <div class="divide-y divide-gray-200">
                @foreach($order->items as $item)
                <div class="flex items-center justify-between p-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-16 h-16 bg-gradient-to-br from-gray-100 to-gray-200 rounded-xl flex items-center justify-center">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <div>
                            <a href="{{ route('products.show', $item->product) }}" class="font-semibold hover:text-[#FFC300] transition-colors">{{ $item->product->title }}</a>
                            <p class="text-sm text-gray-500">by {{ $item->product->author->name }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="font-bold">${{ number_format($item->price, 2) }}</span>
                        <div>
                            <a href="{{ route('products.download', $item->product) }}" class="text-xs text-[#FFC300] hover:text-black font-semibold">Download</a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Order Summary -->
        <div class="bg-white border border-gray-200 rounded-2xl p-6">
            <h2 class="font-semibold mb-4">Payment Summary</h2>
            <div class="space-y-3">
                <div class="flex justify-between text-sm text-gray-600">
                    <span>Subtotal</span>
                    <span>${{ number_format($order->total_amount, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm text-gray-600">
                    <span>Tax</span>
                    <span>$0.00</span>
                </div>
                <hr class="border-gray-200">
                <div class="flex justify-between font-bold text-lg">
                    <span>Total</span>
                    <span class="text-[#FFC300]">${{ number_format($order->total_amount, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Payment Status</span>
                    <span class="font-semibold {{ $order->payment_status == 'paid' ? 'text-green-600' : 'text-red-600' }}">
                        {{ ucfirst($order->payment_status) }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
