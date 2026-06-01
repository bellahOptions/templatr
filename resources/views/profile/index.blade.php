@extends('layouts.app')

@php use App\Helpers\CurrencyHelper; @endphp

@section('title', 'My Profile - CreativeMarket')

@section('content')
<section class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Profile Header -->
        <div class="bg-white border border-gray-200 rounded-2xl p-6 md:p-8 mb-8">
            <div class="flex flex-col sm:flex-row items-start space-y-4 sm:space-y-0 sm:space-x-6">
                <div class="w-20 h-20 bg-gradient-to-br from-[#FFC300] to-[#FFD633] rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="text-3xl font-bold text-black">{{ substr($user->name, 0, 1) }}</span>
                </div>
                <div class="flex-1">
                    <h1 class="text-2xl font-bold">{{ $user->name }}</h1>
                    <p class="text-gray-500">{{ $user->email }}</p>
                    <div class="flex flex-wrap items-center gap-3 mt-2">
                        <span class="px-3 py-1 bg-gray-100 rounded-full text-xs font-semibold capitalize">{{ $user->role }}</span>
                        <span class="text-sm text-gray-500">Member since {{ $user->created_at->format('M Y') }}</span>
                    </div>
                    @if($user->bio)
                    <p class="mt-3 text-gray-600 text-sm">{{ $user->bio }}</p>
                    @endif
                </div>
                <a href="{{ route('profile.edit') }}" class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-semibold hover:bg-gray-50 transition-colors flex-shrink-0">Edit Profile</a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Recent Orders -->
            <div class="bg-white border border-gray-200 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-bold text-lg">Recent Orders</h2>
                    <a href="{{ route('orders.index') }}" class="text-sm text-[#FFC300] hover:text-black font-semibold">View All</a>
                </div>
                @if($orders->isEmpty())
                <p class="text-gray-500 text-sm py-6 text-center">No orders yet.</p>
                @else
                <div class="space-y-3">
                    @foreach($orders as $order)
                    <a href="{{ route('orders.show', $order) }}" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div>
                            <p class="text-sm font-semibold">#{{ $order->order_number }}</p>
                            <p class="text-xs text-gray-500">{{ $order->created_at->format('M d, Y') }}</p>
                        </div>
                        <span class="font-bold text-sm">{{ CurrencyHelper::format($order->total_amount) }}</span>
                    </a>
                    @endforeach
                </div>
                @endif
            </div>

            <!-- My Products (if author) -->
            @if($user->isAuthor())
            <div class="bg-white border border-gray-200 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-bold text-lg">My Products</h2>
                    <span class="text-sm text-gray-500">{{ $user->products->count() }} items</span>
                </div>
                <div class="space-y-3">
                    @foreach($user->products()->with('category')->latest()->take(5)->get() as $product)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold truncate">{{ $product->title }}</p>
                            <p class="text-xs text-gray-500">{{ $product->category->name }}</p>
                        </div>
                        <div class="text-right flex-shrink-0 ml-3">
                            <span class="font-bold text-sm">{{ CurrencyHelper::format($product->sale_price ?? $product->price) }}</span>
                            <p class="text-xs text-gray-500">{{ number_format($product->download_count) }} downloads</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Balance (for authors) -->
        @if($user->isAuthor())
        <div class="mt-8 bg-gradient-to-r from-[#FFC300] to-[#FFD633] rounded-2xl p-6 md:p-8">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-black/70 font-medium text-sm">Available Balance</p>
                    <p class="text-3xl md:text-4xl font-bold text-black mt-1">{{ CurrencyHelper::format($user->balance) }}</p>
                    @if($user->paypal_email)
                    <p class="text-black/60 text-sm mt-1">Payout to: {{ $user->paypal_email }}</p>
                    @endif
                </div>
                <svg class="w-12 h-12 md:w-16 md:h-16 text-black/30" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                </svg>
            </div>
        </div>
        @endif
    </div>
</section>
@endsection
