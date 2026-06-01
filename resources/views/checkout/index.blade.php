@extends('layouts.app')

@php use App\Helpers\CurrencyHelper; @endphp

@section('title', 'Checkout - CreativeMarket')

@section('content')
<section class="py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold">Check<span class="text-[#FFC300]">out</span></h1>
            <p class="mt-2 text-gray-600">Complete your purchase securely</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl p-8">
            <!-- Order Summary -->
            <h2 class="text-xl font-bold mb-6">Order Summary</h2>
            <div class="space-y-4 mb-6">
                @foreach($products as $product)
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                    <div class="flex items-center space-x-3 min-w-0">
                        <div class="w-10 h-10 bg-gray-200 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="font-semibold text-sm truncate">{{ $product->title }}</p>
                            <p class="text-xs text-gray-500">{{ $product->category->name }}</p>
                        </div>
                    </div>
                    <span class="font-bold flex-shrink-0 ml-4">{{ CurrencyHelper::format($product->sale_price ?? $product->price) }}</span>
                </div>
                @endforeach
            </div>

            <hr class="border-gray-200 my-6">

            <!-- Total -->
            <div class="flex items-center justify-between mb-8">
                <span class="text-lg font-bold">Total</span>
                <span class="text-3xl font-bold text-[#FFC300]">{{ CurrencyHelper::format($total) }}</span>
            </div>

            <!-- Payment Gateways -->
            <div class="mb-6">
                <p class="text-sm font-medium text-gray-700 mb-3">Payment Method</p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
                    @if(isset($availableGateways['paystack']))
                    <label class="relative flex items-center p-4 border-2 rounded-xl cursor-pointer transition-all hover:border-[#FFC300] has-[:checked]:border-[#FFC300] has-[:checked]:bg-[#FFC300]/5">
                        <input type="radio" name="payment_method" form="paymentForm" value="paystack" class="absolute opacity-0" {{ !isset($availableGateways['flutterwave']) && !isset($availableGateways['interswitch']) ? 'checked' : 'checked' }}>
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                <span class="text-green-700 font-bold text-xs">P</span>
                            </div>
                            <div>
                                <span class="font-semibold text-sm">Paystack</span>
                                <p class="text-xs text-gray-500">Card, Transfer, USSD</p>
                            </div>
                        </div>
                    </label>
                    @endif

                    @if(isset($availableGateways['flutterwave']))
                    <label class="relative flex items-center p-4 border-2 rounded-xl cursor-pointer transition-all hover:border-[#FFC300] has-[:checked]:border-[#FFC300] has-[:checked]:bg-[#FFC300]/5">
                        <input type="radio" name="payment_method" form="paymentForm" value="flutterwave" class="absolute opacity-0" {{ !isset($availableGateways['paystack']) && !isset($availableGateways['interswitch']) ? 'checked' : '' }}>
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <span class="text-blue-700 font-bold text-xs">F</span>
                            </div>
                            <div>
                                <span class="font-semibold text-sm">Flutterwave</span>
                                <p class="text-xs text-gray-500">Card, Bank, Mobile</p>
                            </div>
                        </div>
                    </label>
                    @endif

                    @if(isset($availableGateways['interswitch']))
                    <label class="relative flex items-center p-4 border-2 rounded-xl cursor-pointer transition-all hover:border-[#FFC300] has-[:checked]:border-[#FFC300] has-[:checked]:bg-[#FFC300]/5">
                        <input type="radio" name="payment_method" form="paymentForm" value="interswitch" class="absolute opacity-0" {{ !isset($availableGateways['paystack']) && !isset($availableGateways['flutterwave']) ? 'checked' : '' }}>
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                <span class="text-purple-700 font-bold text-xs">I</span>
                            </div>
                            <div>
                                <span class="font-semibold text-sm">Interswitch</span>
                                <p class="text-xs text-gray-500">Card, Transfer, Mobile</p>
                            </div>
                        </div>
                    </label>
                    @endif

                    @if(!isset($availableGateways) || empty($availableGateways))
                    <div class="col-span-full p-4 bg-yellow-50 border border-yellow-200 rounded-xl text-yellow-700 text-sm">
                        No payment gateway is currently configured. Your order will be processed as a direct purchase.
                    </div>
                    @endif
                </div>
            </div>

            <!-- Payment Button -->
            <form id="paymentForm" method="POST" action="{{ route('checkout.process') }}">
                @csrf
                <input type="hidden" name="payment_method" value="direct" id="paymentMethodInput">
                <button type="submit" class="w-full bg-black text-white py-4 rounded-xl font-bold text-lg hover:bg-gray-800 transition-colors flex items-center justify-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span>Pay {{ CurrencyHelper::format($total) }}</span>
                </button>
            </form>

            <div class="flex items-center justify-center space-x-4 mt-6">
                <div class="flex space-x-2">
                    <div class="bg-gray-100 rounded-lg px-3 py-1.5 text-xs font-semibold text-gray-600">Visa</div>
                    <div class="bg-gray-100 rounded-lg px-3 py-1.5 text-xs font-semibold text-gray-600">Mastercard</div>
                    <div class="bg-gray-100 rounded-lg px-3 py-1.5 text-xs font-semibold text-gray-600">Verve</div>
                    <div class="bg-gray-100 rounded-lg px-3 py-1.5 text-xs font-semibold text-gray-600">Bank</div>
                </div>
            </div>

            <p class="text-xs text-gray-500 text-center mt-4">
                By completing this purchase you agree to our Terms of Service. All transactions are securely processed in Nigerian Naira ({{ CurrencyHelper::SYMBOL }}).
            </p>
        </div>
    </div>
</section>

@push('scripts')
<script>
    // Update hidden input with selected payment method
    document.querySelectorAll('input[name="payment_method"]').forEach(input => {
        input.addEventListener('change', function() {
            document.getElementById('paymentMethodInput').value = this.value;
        });
    });
    
    // Set default payment method
    const checkedInput = document.querySelector('input[name="payment_method"]:checked');
    if (checkedInput) {
        document.getElementById('paymentMethodInput').value = checkedInput.value;
    }
</script>
@endpush
@endsection
