@extends('layouts.app')

@php use App\Helpers\CurrencyHelper; @endphp

@section('title', 'My Dashboard - Templatr')
@section('robots', 'noindex, nofollow')

@section('content')
<section class="py-8 md:py-12 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Welcome + Quick Links -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold">Welcome back, <span class="text-[#FFC300]">{{ $user->name }}</span></h1>
                <p class="text-gray-500 mt-1">Here's what's happening with your account.</p>
            </div>
            <div class="flex items-center space-x-3 mt-4 md:mt-0">
                <a href="{{ route('profile.edit') }}" class="border border-gray-300 text-gray-700 px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-gray-50 transition-colors">
                    Edit Profile
                </a>
                <a href="{{ route('products.index') }}" class="bg-[#FFC300] text-black px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-[#FFD633] transition-colors">
                    Browse Marketplace
                </a>
            </div>
        </div>

        <!-- Unread Notifications Alert -->
        @if($unreadNotifications->isNotEmpty())
        <div class="mb-8 space-y-2">
            @foreach($unreadNotifications as $notif)
            <div class="bg-white border border-blue-200 rounded-xl px-5 py-4 flex items-start justify-between shadow-sm">
                <div class="flex items-start space-x-3">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ $notif->title }}</p>
                        <p class="text-xs text-gray-600 mt-0.5">{{ $notif->message }}</p>
                    </div>
                </div>
                @if($notif->action_url)
                <a href="{{ $notif->action_url }}" class="text-xs text-blue-600 hover:text-blue-800 font-semibold flex-shrink-0 ml-4 whitespace-nowrap">
                    {{ $notif->action_text ?: 'View' }} →
                </a>
                @endif
            </div>
            @endforeach
        </div>
        @endif

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-gray-500 font-medium">Orders</span>
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </div>
                </div>
                <p class="text-2xl font-bold">{{ $totalOrders }}</p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-gray-500 font-medium">Total Spent</span>
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <p class="text-2xl font-bold">{{ CurrencyHelper::format($totalSpent) }}</p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-gray-500 font-medium">Downloads</span>
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    </div>
                </div>
                <p class="text-2xl font-bold">{{ $totalDownloads }}</p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-gray-500 font-medium">Wishlist</span>
                    <div class="w-8 h-8 bg-pink-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    </div>
                </div>
                <p class="text-2xl font-bold">{{ $wishlistCount }}</p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-gray-500 font-medium">Referral Coins</span>
                    <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <p class="text-2xl font-bold">{{ number_format($coins) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">₦{{ number_format($pendingCommission, 2) }} pending</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Downloadable Items -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Recent Orders -->
                <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h2 class="font-bold">Recent Orders</h2>
                        <a href="{{ route('orders.index') }}" class="text-sm text-[#FFC300] hover:text-black font-semibold">View All</a>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @forelse($recentOrders as $order)
                        <a href="{{ route('orders.show', $order) }}" class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center space-x-3 min-w-0">
                                <div class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <span class="text-xs font-bold">#{{ substr($order->order_number, -4) }}</span>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold truncate">{{ $order->order_number }}</p>
                                    <p class="text-xs text-gray-500">{{ $order->items->count() }} item(s) · {{ $order->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0 ml-3">
                                <span class="font-bold text-sm">{{ CurrencyHelper::format($order->total_amount) }}</span>
                                <p>
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                                        @if($order->status == 'completed') bg-green-100 text-green-700
                                        @elseif($order->status == 'pending') bg-yellow-100 text-yellow-700
                                        @else bg-red-100 text-red-700
                                        @endif">{{ ucfirst($order->status) }}</span>
                                </p>
                            </div>
                        </a>
                        @empty
                        <p class="text-gray-500 text-sm text-center py-8">No orders yet. <a href="{{ route('products.index') }}" class="text-[#FFC300] font-semibold">Start shopping</a></p>
                        @endforelse
                    </div>
                </div>

                <!-- Downloadable Items -->
                <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h2 class="font-bold">Your Downloads</h2>
                        <span class="text-xs text-gray-500">{{ $downloadableItems->count() }} available</span>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @forelse($downloadableItems as $item)
                        <div class="flex items-center justify-between px-6 py-4">
                            <div class="flex items-center space-x-3 min-w-0">
                                <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold truncate">{{ $item->product->title }}</p>
                                    <p class="text-xs text-gray-500">{{ $item->remaining_downloads }} download(s) left</p>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('products.download', $item->product) }}" class="flex-shrink-0 ml-3">
                                @csrf
                                <button type="submit" class="bg-black text-white px-4 py-2 rounded-lg text-xs font-semibold hover:bg-gray-800 transition-colors">
                                    Download
                                </button>
                            </form>
                        </div>
                        @empty
                        <p class="text-gray-500 text-sm text-center py-8">
                            @if($expiredItems->isNotEmpty())
                                All your downloads have expired (max 2 per item).
                            @else
                                No purchased items yet. <a href="{{ route('products.index') }}" class="text-[#FFC300] font-semibold">Browse marketplace</a>
                            @endif
                        </p>
                        @endforelse
                    </div>
                    @if($expiredItems->isNotEmpty())
                    <div class="px-6 py-3 bg-gray-50 border-t border-gray-100">
                        <p class="text-xs text-gray-500">{{ $expiredItems->count() }} item(s) have reached their download limit.</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Profile Card -->
                <div class="bg-white border border-gray-200 rounded-2xl p-6 text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-[#FFC300] to-[#FFD633] rounded-full flex items-center justify-center mx-auto mb-3">
                        <span class="text-xl font-bold text-black">{{ substr($user->name, 0, 1) }}</span>
                    </div>
                    <h3 class="font-bold text-lg">{{ $user->name }}</h3>
                    <p class="text-xs text-gray-500">{{ $user->email }}</p>
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <div class="bg-gray-50 rounded-xl p-3">
                            <p class="text-lg font-bold">{{ $referralCount }}</p>
                            <p class="text-xs text-gray-500">Referrals</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-3">
                            <p class="text-lg font-bold">{{ number_format($coins) }}</p>
                            <p class="text-xs text-gray-500">Coins</p>
                        </div>
                    </div>
                    <a href="{{ route('affiliate.index') }}" class="mt-4 block w-full border border-gray-300 text-gray-700 px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-gray-50 transition-colors">
                        Referral Program →
                    </a>
                </div>

                <!-- Wishlist Preview -->
                <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="font-semibold text-sm">Wishlist</h3>
                        <a href="{{ route('wishlist.index') }}" class="text-xs text-[#FFC300] hover:text-black font-semibold">View All</a>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @forelse($wishlistItems as $wish)
                        <a href="{{ route('products.show', $wish->product) }}" class="flex items-center space-x-3 px-5 py-3 hover:bg-gray-50 transition-colors">
                            <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <span class="text-xs font-bold">{{ substr($wish->product->title, 0, 2) }}</span>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium truncate">{{ $wish->product->title }}</p>
                                <p class="text-xs text-gray-500">{{ CurrencyHelper::format($wish->product->sale_price ?? $wish->product->price) }}</p>
                            </div>
                        </a>
                        @empty
                        <p class="text-gray-500 text-xs text-center py-6">No items in wishlist</p>
                        @endforelse
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="bg-white border border-gray-200 rounded-2xl p-5">
                    <h3 class="font-semibold text-sm mb-3">Quick Links</h3>
                    <div class="space-y-2">
                        <a href="{{ route('orders.index') }}" class="flex items-center space-x-3 text-sm text-gray-600 hover:text-black transition-colors p-2 rounded-lg hover:bg-gray-50">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                            <span>Order History</span>
                        </a>
                        <a href="{{ route('profile.edit') }}" class="flex items-center space-x-3 text-sm text-gray-600 hover:text-black transition-colors p-2 rounded-lg hover:bg-gray-50">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span>Edit Profile</span>
                        </a>
                        <a href="{{ route('wishlist.index') }}" class="flex items-center space-x-3 text-sm text-gray-600 hover:text-black transition-colors p-2 rounded-lg hover:bg-gray-50">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            <span>Wishlist</span>
                        </a>
                        <a href="{{ route('affiliate.index') }}" class="flex items-center space-x-3 text-sm text-gray-600 hover:text-black transition-colors p-2 rounded-lg hover:bg-gray-50">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Affiliate Program</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
