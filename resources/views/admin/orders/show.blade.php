@extends('admin.layouts.admin')

@section('title', 'Order Details - CreativeMarket')
@section('header', 'Order #' . $order->order_number)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Order Items -->
    <div class="lg:col-span-2 bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h2 class="font-bold">Order Items</h2>
        </div>
        <div class="divide-y divide-gray-100">
            @foreach($order->items as $item)
            <div class="flex items-center justify-between p-6">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center">
                        <span class="text-sm font-bold">{{ substr($item->product->title, 0, 2) }}</span>
                    </div>
                    <div>
                        <p class="font-semibold text-sm">{{ $item->product->title }}</p>
                        <p class="text-xs text-gray-500">by {{ $item->product->author->name }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="font-bold text-sm">${{ number_format($item->price, 2) }}</span>
                    <p class="text-xs text-gray-500">Author earns: ${{ number_format($item->author_earnings, 2) }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Order Details -->
    <div class="bg-white border border-gray-200 rounded-2xl p-6">
        <h2 class="font-bold mb-6">Order Details</h2>
        
        <div class="space-y-4">
            <div>
                <span class="text-xs text-gray-500 uppercase tracking-wider">Customer</span>
                <p class="font-semibold mt-1">{{ $order->user->name }}</p>
                <p class="text-sm text-gray-500">{{ $order->user->email }}</p>
            </div>
            
            <div>
                <span class="text-xs text-gray-500 uppercase tracking-wider">Order Date</span>
                <p class="font-semibold mt-1">{{ $order->created_at->format('M d, Y \a\t g:i A') }}</p>
            </div>

            <div>
                <span class="text-xs text-gray-500 uppercase tracking-wider">Total Amount</span>
                <p class="text-2xl font-bold text-[#FFC300] mt-1">${{ number_format($order->total_amount, 2) }}</p>
            </div>

            <hr class="border-gray-200">

            <!-- Update Status -->
            <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="space-y-4">
                @csrf
                @method('PUT')
                
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Order Status</label>
                    <select name="status" class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">
                        <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Payment Status</label>
                    <select name="payment_status" class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">
                        <option value="unpaid" {{ $order->payment_status == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        <option value="paid" {{ $order->payment_status == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="refunded" {{ $order->payment_status == 'refunded' ? 'selected' : '' }}>Refunded</option>
                    </select>
                </div>

                <button type="submit" class="w-full bg-black text-white py-3 rounded-xl text-sm font-semibold hover:bg-gray-800 transition-colors">
                    Update Status
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
