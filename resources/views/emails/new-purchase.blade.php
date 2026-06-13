@extends('emails.layout')

@section('title', 'New Purchase - Templatr')

@section('content')
    <div style="text-align:center;">
        <div style="font-size:56px; margin-bottom:16px;">💰</div>
        <h1>New Purchase Received!</h1>
        <p>Hi <strong>{{ $recipientName }}</strong>, a new purchase has been made on Templatr.</p>
    </div>

    <div class="info-card">
        <div class="info-row">
            <span class="label">Customer</span>
            <span class="value">{{ $customerName }} ({{ $customerEmail }})</span>
        </div>
        <div class="info-row">
            <span class="label">Order Number</span>
            <span class="value">{{ $orderNumber }}</span>
        </div>
        <div class="info-row">
            <span class="label">Amount</span>
            <span class="value">{{ $amount }}</span>
        </div>
        <div class="info-row">
            <span class="label">Items</span>
            <span class="value">{{ $items }}</span>
        </div>
        <div class="info-row">
            <span class="label">Payment Method</span>
            <span class="value">{{ $paymentMethod }}</span>
        </div>
        <div class="info-row">
            <span class="label">Date</span>
            <span class="value">{{ $orderDate }}</span>
        </div>
    </div>

    <div style="text-align:center; margin-top: 16px;">
        <a href="{{ $actionUrl }}" class="btn-primary">View Order</a>
    </div>

    <p style="margin-top:24px; font-size:13px; color:#9ca3af;">Thank you for managing Templatr!</p>
@endsection

@section('footer_email', 'admin')
