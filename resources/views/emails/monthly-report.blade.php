@extends('emails.layout')

@section('title', 'Monthly Sales Report - Templatr')

@section('content')
    <div style="text-align:center;">
        <div style="font-size:56px; margin-bottom:16px;">📊</div>
        <h1>Monthly Sales Report</h1>
        <p>Hi <strong>{{ $user->name }}</strong>, here is your sales summary for <strong>{{ $month }}</strong>:</p>
    </div>

    <div class="info-card">
        <div class="info-row">
            <span class="label">Total Revenue</span>
            <span class="value">{{ $totalRevenue }}</span>
        </div>
        <div class="info-row">
            <span class="label">Total Orders</span>
            <span class="value">{{ $totalOrders }}</span>
        </div>
        <div class="info-row">
            <span class="label">Products Sold</span>
            <span class="value">{{ $productsSold }}</span>
        </div>
        <div class="info-row">
            <span class="label">New Users</span>
            <span class="value">{{ $newUsers }}</span>
        </div>
        <div class="info-row">
            <span class="label">New Products</span>
            <span class="value">{{ $newProducts }}</span>
        </div>
        @if(isset($revenueChange))
        <div class="info-row" style="border-top:1px solid #e5e7eb; padding-top:10px; margin-top:4px;">
            <span class="label">Revenue vs Last Month</span>
            <span class="value" style="color:{{ $revenueDirection === 'increase' ? '#059669' : '#dc2626' }};">
                {{ $revenueChange }}% {{ ucfirst($revenueDirection) }}
            </span>
        </div>
        @endif
    </div>

    @if(!empty($topProducts))
    <h2 style="font-size:16px; font-weight:700; color:#111827; margin:24px 0 12px;">Top Selling Products</h2>
    <table class="items-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Product</th>
                <th>Sales</th>
                <th>Revenue</th>
            </tr>
        </thead>
        <tbody>
            @foreach($topProducts as $index => $product)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td><strong>{{ $product['title'] }}</strong></td>
                <td>{{ $product['sales'] }}</td>
                <td>{{ $product['revenue'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div style="text-align:center; margin-top: 24px;">
        <a href="{{ $actionUrl }}" class="btn-primary">View Full Dashboard</a>
    </div>

    <p style="margin-top:24px; font-size:13px; color:#9ca3af;">Thank you for using Templatr!</p>
@endsection

@section('footer_email', 'admin')
