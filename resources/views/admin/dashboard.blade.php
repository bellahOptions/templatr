@extends('admin.layouts.admin')

@php use App\Helpers\CurrencyHelper; @endphp

@section('title', 'Admin Dashboard - Templatr')
@section('header', 'Dashboard')

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 mb-8">
    <div class="bg-white rounded-2xl border border-gray-200 p-5">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs text-gray-500 font-medium">Total Products</span>
            <div class="w-9 h-9 bg-blue-100 rounded-xl flex items-center justify-center">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
        </div>
        <p class="text-2xl font-bold">{{ number_format($totalProducts) }}</p>
        <p class="text-[10px] text-gray-400 mt-0.5">{{ \App\Models\Category::count() }} categories</p>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 p-5">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs text-gray-500 font-medium">Total Orders</span>
            <div class="w-9 h-9 bg-green-100 rounded-xl flex items-center justify-center">
                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            </div>
        </div>
        <p class="text-2xl font-bold">{{ number_format($totalOrders) }}</p>
        <p class="text-[10px] text-gray-400 mt-0.5">{{ $monthlyRevenue > 0 ? CurrencyHelper::format($monthlyRevenue) . ' this month' : 'No revenue this month' }}</p>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 p-5">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs text-gray-500 font-medium">Total Users</span>
            <div class="w-9 h-9 bg-purple-100 rounded-xl flex items-center justify-center">
                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
            </div>
        </div>
        <p class="text-2xl font-bold">{{ number_format($totalUsers) }}</p>
        <p class="text-[10px] text-gray-400 mt-0.5">{{ number_format($totalAuthors) }} authors</p>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 p-5">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs text-gray-500 font-medium">Total Revenue</span>
            <div class="w-9 h-9 bg-yellow-100 rounded-xl flex items-center justify-center">
                <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <p class="text-2xl font-bold">{{ CurrencyHelper::format($totalRevenue) }}</p>
        <p class="text-[10px] text-gray-400 mt-0.5">Lifetime revenue</p>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 p-5">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs text-gray-500 font-medium">Live Users</span>
            <div class="w-9 h-9 bg-pink-100 rounded-xl flex items-center justify-center">
                <svg class="w-4 h-4 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
        </div>
        <p class="text-2xl font-bold">{{ number_format($liveUsers ?: $recentActiveUsers) }}</p>
        <p class="text-[10px] text-gray-400 mt-0.5">Online now (15 min)</p>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 p-5">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs text-gray-500 font-medium">Pending Reviews</span>
            <div class="w-9 h-9 bg-amber-100 rounded-xl flex items-center justify-center">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
            </div>
        </div>
        <p class="text-2xl font-bold">{{ number_format($pendingReviews) }}</p>
        <p class="text-[10px] text-gray-400 mt-0.5">Awaiting approval</p>
    </div>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    {{-- Revenue Chart --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-bold">Revenue <span class="text-gray-500 font-normal text-sm">(12 months)</span></h2>
        </div>
        <div class="relative h-48">
            <div class="absolute inset-0 flex items-end space-x-1.5">
                @foreach($revenueChart as $item)
                <div class="flex-1 flex flex-col items-center justify-end h-full">
                    <div class="w-full bg-[#FFC300]/80 hover:bg-[#FFC300] rounded-t-lg transition-all duration-300 group relative"
                         style="height: {{ max(4, $item['revenue'] > 0 ? ($item['revenue'] / max(array_column($revenueChart, 'revenue') ?: [1])) * 100 : 0) }}%">
                        <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-black text-white text-[10px] px-2 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                            {{ CurrencyHelper::format($item['revenue']) }}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        <div class="flex justify-between mt-2 text-[10px] text-gray-400">
            @foreach($revenueChart as $item)
            <span>{{ substr($item['month'], 0, 3) }}</span>
            @endforeach
        </div>
    </div>

    {{-- Orders Chart --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-bold">Orders <span class="text-gray-500 font-normal text-sm">(30 days)</span></h2>
        </div>
        <div class="relative h-48">
            <div class="absolute inset-0 flex items-end space-x-1">
                @foreach($ordersChart as $item)
                <div class="flex-1 flex flex-col items-center justify-end h-full">
                                        <div class="w-full bg-blue-500/70 hover:bg-blue-500 rounded-t-lg transition-all duration-300 group relative"
                         style="height: {{ max(4, $item['count'] > 0 ? ($item['count'] / max(array_column($ordersChart, 'count') ?: [1])) * 100 : 0) }}%">
                        <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-black text-white text-[10px] px-2 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                            {{ $item['count'] }}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        <div class="flex justify-between mt-2 text-[10px] text-gray-400">
            @foreach($ordersChart as $item)
            <span>{{ $item['day'] }}</span>
            @endforeach
        </div>
    </div>

    {{-- User Growth Chart --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-bold">User Growth <span class="text-gray-500 font-normal text-sm">(16 weeks)</span></h2>
        </div>
        <div class="relative h-36">
            <div class="absolute inset-0 flex items-end space-x-1.5">
                @foreach($userChart as $item)
                <div class="flex-1 flex flex-col items-center justify-end h-full">
                    <div class="w-full bg-purple-400/70 hover:bg-purple-500 rounded-t-lg transition-all duration-300 group relative"
                         style="height: {{ max(4, $item['count'] > 0 ? ($item['count'] / max(array_column($userChart, 'count') ?: [1])) * 100 : 0) }}%">
                        <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-black text-white text-[10px] px-2 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                            {{ $item['count'] }} new
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        <div class="flex justify-between mt-2 text-[10px] text-gray-400 overflow-hidden">
            @foreach($userChart as $index => $item)
            @if($index % 4 === 0 || $loop->last)
            <span>{{ substr($item['week'], 0, 5) }}</span>
            @else
            <span></span>
            @endif
            @endforeach
        </div>
    </div>

    {{-- Paystack Balance + Payment Stats --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-bold">Paystack Balance</h2>
            <span class="text-xs text-gray-500">Auto-refreshes every 5 min</span>
        </div>
        @if(isset($paystackBalance['error']))
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-sm text-yellow-700">
            <p class="font-semibold">Paystack not connected</p>
            <p class="text-xs mt-1">Set <code class="bg-yellow-100 px-1 rounded">PAYSTACK_SECRET_KEY</code> in .env to see balance.</p>
        </div>
        @else
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div class="bg-gray-50 rounded-xl p-4">
                <p class="text-xs text-gray-500">Available Balance</p>
                <p class="text-2xl font-bold text-green-600">{{ CurrencyHelper::format($paystackBalance['available']) }}</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-4">
                <p class="text-xs text-gray-500">Ledger Balance</p>
                <p class="text-2xl font-bold">{{ CurrencyHelper::format($paystackBalance['ledger']) }}</p>
            </div>
        </div>
        @if(($paystackBalance['pending'] ?? 0) > 0)
        <div class="bg-amber-50 rounded-xl p-3 text-sm">
            <p class="font-semibold text-amber-700">{{ CurrencyHelper::format($paystackBalance['pending']) }} pending settlement</p>
        </div>
        @endif
        @endif

        <hr class="border-gray-200 my-4">
        <h3 class="text-sm font-semibold mb-3">Payment Gateway Usage</h3>

        <div class="space-y-2">
            @foreach(['paystack' => 'Paystack', 'flutterwave' => 'Flutterwave', 'interswitch' => 'Interswitch', 'direct' => 'Direct'] as $key => $label)
            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600">{{ $label }}</span>
                <div class="flex items-center space-x-2">
                    <div class="w-24 bg-gray-200 rounded-full h-2">
                        <div class="bg-[#FFC300] rounded-full h-2" style="width: {{ $totalOrders > 0 ? ($paymentStats[$key] / max($totalOrders, 1)) * 100 : 0 }}%"></div>
                    </div>
                    <span class="text-xs font-semibold text-gray-600 w-12 text-right">{{ $paymentStats[$key] }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Flutterwave Balance --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-orange-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/></svg>
                </div>
                <h2 class="font-bold">Flutterwave Balance</h2>
            </div>
            <span class="text-xs text-gray-500">Auto-refreshes every 5 min</span>
        </div>
        @if(isset($flutterwaveBalance['error']))
        <div class="bg-orange-50 border border-orange-200 rounded-xl p-4 text-sm text-orange-700">
            <p class="font-semibold">Flutterwave not connected</p>
            <p class="text-xs mt-1">Set <code class="bg-orange-100 px-1 rounded">FLW_SECRET_KEY</code> in .env to see balance.</p>
        </div>
        @else
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-gray-50 rounded-xl p-4">
                <p class="text-xs text-gray-500">Available Balance</p>
                <p class="text-2xl font-bold text-green-600">{{ CurrencyHelper::format($flutterwaveBalance['available']) }}</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-4">
                <p class="text-xs text-gray-500">Ledger Balance</p>
                <p class="text-2xl font-bold">{{ CurrencyHelper::format($flutterwaveBalance['ledger'] ?? 0) }}</p>
            </div>
        </div>
        <p class="text-xs text-gray-400 mt-3 text-center">Currency: {{ $flutterwaveBalance['currency'] ?? 'NGN' }}</p>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
    {{-- Most Viewed Products --}}
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="font-bold text-sm">Most Viewed Products</h2>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($mostViewed as $product)
            <div class="flex items-center justify-between px-6 py-3">
                <div class="flex items-center space-x-3 min-w-0">
                    <span class="text-xs text-gray-400 font-mono w-6">{{ $loop->iteration }}.</span>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold truncate">{{ $product->title }}</p>
                        <p class="text-[10px] text-gray-500">{{ $product->category->name }}</p>
                    </div>
                </div>
                <span class="text-sm font-bold flex-shrink-0 ml-2">{{ number_format($product->view_count) }}</span>
            </div>
            @empty
            <p class="text-gray-500 text-sm text-center py-6">No products yet.</p>
            @endforelse
        </div>
    </div>

    {{-- Most Purchased Products --}}
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="font-bold text-sm">Most Purchased Products</h2>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($mostPurchased as $product)
            <div class="flex items-center justify-between px-6 py-3">
                <div class="flex items-center space-x-3 min-w-0">
                    <span class="text-xs text-gray-400 font-mono w-6">{{ $loop->iteration }}.</span>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold truncate">{{ $product->title }}</p>
                        <p class="text-[10px] text-gray-500">{{ $product->category->name }}</p>
                    </div>
                </div>
                <span class="text-sm font-bold text-green-600 flex-shrink-0 ml-2">{{ number_format($product->order_items_count) }}</span>
            </div>
            @empty
            <p class="text-gray-500 text-sm text-center py-6">No purchases yet.</p>
            @endforelse
        </div>
    </div>

    {{-- Category Stats --}}
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="font-bold text-sm">Categories</h2>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($categoryStats as $cat)
            <div class="flex items-center justify-between px-6 py-3">
                <div class="flex items-center space-x-2 min-w-0">
                    <div class="w-2 h-2 rounded-full bg-[#FFC300] flex-shrink-0"></div>
                    <span class="text-sm font-medium truncate">{{ $cat->name }}</span>
                </div>
                <span class="text-sm font-semibold flex-shrink-0 ml-2">{{ number_format($cat->products_count) }}</span>
            </div>
            @empty
            <p class="text-gray-500 text-sm text-center py-6">No categories.</p>
            @endforelse
            <a href="{{ route('admin.categories.index') }}" class="block px-6 py-3 text-center text-xs text-[#FFC300] hover:text-black font-semibold">
                Manage Categories →
            </a>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    {{-- Recent Orders --}}
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="font-bold">Recent Orders</h2>
            <a href="{{ route('admin.orders.index') }}" class="text-sm text-[#FFC300] hover:text-black font-semibold">View All</a>
        </div>
        <div class="divide-y divide-gray-100">
            @foreach($recentOrders as $order)
            <a href="{{ route('admin.orders.show', $order) }}" class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition-colors">
                <div class="flex items-center space-x-3 min-w-0">
                    <div class="w-9 h-9 bg-gray-100 rounded-xl flex items-center justify-center flex-shrink-0">
                        <span class="text-xs font-bold">#{{ substr($order->order_number, -4) }}</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold truncate">#{{ $order->order_number }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $order->user?->name ?? $order->guest_name ?? 'Guest' }} · {{ $order->created_at->diffForHumans() }}</p>
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
            @endforeach
            @if($recentOrders->isEmpty())
            <p class="text-gray-500 text-sm text-center py-8">No orders yet.</p>
            @endif
        </div>
    </div>

    {{-- Top Downloaded Products --}}
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="font-bold">Top Downloaded Products</h2>
        </div>
        <div class="divide-y divide-gray-100">
            @foreach($topProducts as $product)
            <div class="flex items-center justify-between px-6 py-4">
                <div class="flex items-center space-x-3 min-w-0">
                    <div class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center flex-shrink-0">
                        <span class="text-xs font-bold">{{ substr($product->title, 0, 2) }}</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold truncate">{{ $product->title }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $product->category->name }}</p>
                    </div>
                </div>
                <div class="text-right flex-shrink-0 ml-3">
                    <span class="font-bold text-sm">{{ number_format($product->download_count) }}</span>
                    <p class="text-xs text-gray-500">downloads</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
