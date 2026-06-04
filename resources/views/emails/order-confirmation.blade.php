@extends('emails.layout')

@section('title', 'Order Confirmation - Templatr')

@section('content')
    <div style="text-align:center;">
        <div style="font-size:56px; margin-bottom:16px;">✅</div>
        <h1>Order Confirmation</h1>
        <p>Hi <strong>{{ $user->name }}</strong>, this is a confirmation of your recent order on Templatr.</p>
    </div>

    <div class="info-card">
        <div class="info-row">
            <span class="label">Order #</span>
            <span class="value">{{ $orderNumber }}</span>
        </div>
        <div class="info-row">
            <span class="label">Date</span>
            <span class="value">{{ $orderDate ?? now()->format('F j, Y \a\t g:i A') }}</span>
        </div>
        <div class="info-row">
            <span class="label">Total</span>
            <span class="value">{{ class_exists(\App\Helpers\CurrencyHelper::class) ? \App\Helpers\CurrencyHelper::format((float) ($total ?? 0)) : '₦' . number_format((float) ($total ?? 0), 2) }}</span>
        </div>
        <div class="info-row">
            <span class="label">Status</span>
            <span class="value">
                <span class="status-badge status-completed">Completed</span>
            </span>
        </div>
    </div>

    @if(isset($items) && count($items) > 0)
    <h2 style="font-size:16px; font-weight:700; color:#111827; margin:0 0 12px;">Items</h2>
    <table class="items-table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>{{ $item['title'] ?? 'Product' }}</td>
                <td>{{ class_exists(\App\Helpers\CurrencyHelper::class) ? \App\Helpers\CurrencyHelper::format((float) ($item['price'] ?? 0)) : '₦' . number_format((float) ($item['price'] ?? 0), 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div style="text-align:center; margin-top: 16px;">
        <a href="{{ $orderUrl ?? '#' }}" class="btn-primary">View Order Details</a>
    </div>
@endsection

@section('footer_email', $user->email)
