@extends('emails.layout')

@section('title', 'Order Receipt - Templatr')

@section('content')
    <div style="text-align:center;">
        <div style="font-size:56px; margin-bottom:16px;">🎉</div>
        <h1>Order Confirmed!</h1>
        <p>Thank you, <strong>{{ $order->customer_name }}</strong>! Your order has been placed successfully.</p>
    </div>

    <div class="info-card">
        <div class="info-row">
            <span class="label">Order #</span>
            <span class="value">{{ $order->order_number }}</span>
        </div>
        <div class="info-row">
            <span class="label">Date</span>
            <span class="value">{{ $order->created_at->format('F j, Y \a\t g:i A') }}</span>
        </div>
        <div class="info-row">
            <span class="label">Customer</span>
            <span class="value">{{ $order->customer_name }}</span>
        </div>
        <div class="info-row">
            <span class="label">Email</span>
            <span class="value">{{ $order->customer_email }}</span>
        </div>
        <div class="info-row">
            <span class="label">Status</span>
            <span class="value">
                <span class="status-badge status-{{ $order->status === 'completed' ? 'completed' : ($order->status === 'pending' ? 'pending' : 'failed') }}">
                    {{ ucfirst($order->status) }}
                </span>
            </span>
        </div>
        <div class="info-row">
            <span class="label">Payment</span>
            <span class="value">{{ ucfirst($order->payment_method) }} — {{ ucfirst($order->payment_status) }}</span>
        </div>
    </div>

    <h2 style="font-size:16px; font-weight:700; color:#111827; margin:0 0 12px;">Items Purchased</h2>
    <table class="items-table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Type</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>
                    <strong>{{ $item->product->title }}</strong>
                    @if($item->product->version)
                        <br><span style="font-size:12px; color:#9ca3af;">v{{ $item->product->version }}</span>
                    @endif
                </td>
                <td>{{ ucfirst($item->product->file_type) }}</td>
                <td>{{ App\Helpers\CurrencyHelper::format($item->price) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-row">
        <span>Total Paid</span>
        <span>{{ App\Helpers\CurrencyHelper::format($order->total_amount) }}</span>
    </div>

    <div style="text-align:center; margin-top: 24px;">
        <a href="{{ route('orders.show', $order) }}" class="btn-primary">
            <svg style="display:inline; width:18px; height:18px; vertical-align:middle; margin-right:8px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Download Your Items
        </a>
    </div>

    <div style="margin-top:32px; padding-top:24px; border-top:1px solid #e5e7eb;">
        <p style="font-size:14px; color:#6b7280; margin:0;">
            Need help? <a href="mailto:support@bellahoptions.com" style="color:#f59e0b;">Contact Support</a>
        </p>
    </div>
@endsection

@section('footer_email', $order->customer_email)
