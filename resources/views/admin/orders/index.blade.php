@extends('admin.layouts.admin')

@php use App\Helpers\CurrencyHelper; @endphp

@section('title', 'Orders - CreativeMarket')
@section('header', 'Orders')

@section('content')
<div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
    <div class="p-6 border-b border-gray-200">
        <form method="GET" class="flex gap-3">
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
            <select name="payment_status" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">
                <option value="">Payment Status</option>
                <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                <option value="refunded" {{ request('payment_status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-black text-white rounded-lg text-sm font-semibold hover:bg-gray-800">Filter</button>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Order</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Items</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Payment</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($orders as $order)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-semibold">#{{ $order->order_number }}</td>
                    <td class="px-6 py-4 text-sm">{{ $order->user->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $order->items_count ?? $order->items->count() ?? 0 }}</td>
                    <td class="px-6 py-4 text-sm font-bold whitespace-nowrap">{{ CurrencyHelper::format($order->total_amount) }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded-full text-xs font-semibold
                            @if($order->status == 'completed') bg-green-100 text-green-700
                            @elseif($order->status == 'pending') bg-yellow-100 text-yellow-700
                            @else bg-red-100 text-red-700
                            @endif">{{ ucfirst($order->status) }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded-full text-xs font-semibold
                            @if($order->payment_status == 'paid') bg-green-100 text-green-700
                            @elseif($order->payment_status == 'unpaid') bg-yellow-100 text-yellow-700
                            @else bg-red-100 text-red-700
                            @endif">{{ ucfirst($order->payment_status) }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap">{{ $order->created_at->format('M d, Y') }}</td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('admin.orders.show', $order) }}" class="text-sm text-[#FFC300] hover:text-black font-semibold">View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($orders->isEmpty())
    <div class="text-center py-12">
        <p class="text-gray-500">No orders found.</p>
    </div>
    @endif
    <div class="p-4 border-t border-gray-200">
        {{ $orders->links() }}
    </div>
</div>
@endsection
