@extends('admin.layouts.admin')

@php use App\Helpers\CurrencyHelper; @endphp

@section('title', 'Admin Dashboard - CreativeMarket')
@section('header', 'Dashboard')

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
    <div class="bg-white rounded-2xl border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm text-gray-500 font-medium">Total Products</span>
            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
        </div>
        <p class="text-3xl font-bold">{{ $totalProducts }}</p>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm text-gray-500 font-medium">Total Orders</span>
            <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            </div>
        </div>
        <p class="text-3xl font-bold">{{ $totalOrders }}</p>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm text-gray-500 font-medium">Total Users</span>
            <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
            </div>
        </div>
        <p class="text-3xl font-bold">{{ $totalUsers }}</p>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm text-gray-500 font-medium">Total Revenue</span>
            <div class="w-10 h-10 bg-yellow-100 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <p class="text-3xl font-bold">{{ CurrencyHelper::format($totalRevenue) }}</p>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm text-gray-500 font-medium">Monthly Revenue</span>
            <div class="w-10 h-10 bg-pink-100 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
        </div>
        <p class="text-3xl font-bold">{{ CurrencyHelper::format($monthlyRevenue) }}</p>
        <p class="text-xs text-gray-500 mt-1">This month</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Recent Orders -->
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="font-bold">Recent Orders</h2>
            <a href="{{ route('admin.orders.index') }}" class="text-sm text-[#FFC300] hover:text-black font-semibold">View All</a>
        </div>
        <div class="divide-y divide-gray-100">
            @foreach($recentOrders as $order)
            <a href="{{ route('admin.orders.show', $order) }}" class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition-colors">
                <div>
                    <p class="text-sm font-semibold">#{{ $order->order_number }}</p>
                    <p class="text-xs text-gray-500">{{ $order->user->name }} · {{ $order->created_at->diffForHumans() }}</p>
                </div>
                <div class="text-right">
                    <span class="font-bold text-sm">{{ CurrencyHelper::format($order->total_amount) }}</span>
                    <p class="text-xs">
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

    <!-- Top Products -->
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
