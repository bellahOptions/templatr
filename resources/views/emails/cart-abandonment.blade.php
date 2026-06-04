@extends('emails.layout')

@section('title', 'Complete Your Purchase - Templatr')

@section('content')
    <div style="text-align:center;">
        <div style="font-size:56px; margin-bottom:16px;">🛒</div>
        <h1>You left something behind!</h1>
        <p>Hi <strong>{{ $user->name }}</strong>, you have items waiting in your cart. Don't miss out on these amazing products!</p>
    </div>

    @if(isset($items) && count($items) > 0)
    <table class="items-table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Type</th>
                <th>Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td><strong>{{ $item['title'] ?? 'Product' }}</strong></td>
                <td>{{ $item['type'] ?? 'Digital' }}</td>
                <td>
                    @php
                        $price = $item['price'] ?? 0;
                        echo class_exists(\App\Helpers\CurrencyHelper::class)
                            ? \App\Helpers\CurrencyHelper::format((float) $price)
                            : '₦' . number_format((float) $price, 2);
                    @endphp
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div style="text-align:center;">
        <a href="{{ route('cart.index') }}" class="btn-primary">Complete Your Purchase</a>
    </div>

    <p style="margin-top:24px; font-size:13px; color:#9ca3af; text-align:center;">
        Your cart will be saved for 7 days. Items may sell out, so don't wait too long!
    </p>
@endsection

@section('footer_email', $user->email)
