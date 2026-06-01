@extends('layouts.app')

@section('title', 'My Orders - CreativeMarket')

@section('content')
<section class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold">My <span class="text-[#FFC300]">Orders</span></h1>
                <p class="text-gray-600 mt-1">View your purchase history</p>
            </div>
            <a href="{{ route('products.index') }}" class="bg-black text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-800 transition-colors">
                Browse Items
            </a>
        </div>

        @if($orders->isEmpty())
        <div class="text-center py-20">
            <svg class="w-24 h-24 mx-auto text-gray-300 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">No orders yet</h2>
            <p class="text-gray-500 mb-6">You haven't made any purchases yet.</p>
            <a href="{{ route('products.index') }}" class="inline-flex items-center bg-black text-white px-6 py-3 rounded-xl font-semibold hover:bg-gray-800 transition-colors">
                Start Shopping
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>
        @else
        <div class="space-y-4">
            @foreach($orders as $order)
            <a href="{{ route('orders.show', $order) }}" class="block bg-white border border-gray-200 rounded-xl p-6 hover:border-[#FFC300] hover:shadow-md transition-all">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <span class="text-xs text-gray-500">Order #</span>
                        <span class="font-semibold text-sm">{{ $order->order_number }}</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <span class="text-xs text-gray-500">{{ $order->created_at->format('M d, Y') }}</span>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold
                            @if($order->status == 'completed') bg-green-100 text-green-700
                            @elseif($order->status == 'pending') bg-yellow-100 text-yellow-700
                            @else bg-red-100 text-red-700
                            @endif">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        @foreach($order->items as $item)
                        <div class="w-8 h-8 bg-gray-200 rounded-lg flex items-center justify-center">
                            <span class="text-xs font-bold">{{ substr($item->product->title, 0, 2) }}</span>
                        </div>
                        @endforeach
                        <span class="text-sm text-gray-600">{{ $order->items->count() }} item(s)</span>
                    </div>
                    <span class="font-bold text-lg">${{ number_format($order->total_amount, 2) }}</span>
                </div>
            </a>
            @endforeach
        </div>
        <div class="mt-8">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</section>
@endsection
