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
                        @if($item->isDownloadable())
                        <div>
                            @if($order->user_id)
                            <form method="POST" action="{{ route('products.download', $item->product) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-xs text-[#FFC300] hover:text-black font-semibold">
                                    Download ({{ $item->remaining_downloads }} left)
                                </button>
                            </form>
                            @else
                            <a href="{{ route('products.download', ['product' => $item->product, 'token' => session('download_token_' . $item->id)]) }}"
                               class="text-xs text-[#FFC300] hover:text-black font-semibold">
                                Download (1 download only)
                            </a>
                            @endif
                        </div>
                        @else
                        <div>
                            <span class="text-xs text-red-500 font-semibold">Download limit reached</span>
                        </div>
                        @endif
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
                <span class="text-gray-500">Payment Status</span>
                <span class="font-semibold text-green-600">{{ ucfirst($order->payment_status) }}</span>
            </div>
            <div class="flex justify-between text-sm mt-1">
                <span class="text-gray-500">Payment Method</span>
                <span class="font-semibold capitalize">{{ $order->payment_method }}</span>
            </div>

            @if(!$order->user_id)
            <div class="mt-4 p-4 bg-amber-50 border border-amber-200 rounded-xl">
                <div class="flex items-start space-x-3">
                    <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="text-sm text-amber-800">
                        <p class="font-semibold">Guest Download Notice</p>
                        <p class="mt-1">You can download each item <strong>only once</strong>. Please save your files carefully. A download link was also sent to <strong>{{ $order->guest_email }}</strong>.</p>
                    </div>
                </div>
            </div>
            @else
            <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                <div class="flex items-start space-x-3">
                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="text-sm text-blue-800">
                        <p class="font-semibold">Download Info</p>
                        <p class="mt-1">You can download each item up to <strong>2 times</strong>. You can always return to your <a href="{{ route('orders.index') }}" class="text-blue-600 underline font-semibold">order history</a> to download your items again.</p>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            @if($order->user_id)
            <a href="{{ route('orders.index') }}" class="bg-black text-white px-6 py-3 rounded-xl font-semibold hover:bg-gray-800 transition-colors w-full sm:w-auto text-center">
                View My Orders
            </a>
            @endif
            <a href="{{ route('products.index') }}" class="border border-gray-300 text-gray-700 px-6 py-3 rounded-xl font-semibold hover:bg-gray-50 transition-colors w-full sm:w-auto text-center">
                Continue Shopping
            </a>
        </div>
    </div>
</section>
@endsection
