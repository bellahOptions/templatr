@extends('layouts.app')

@php use App\Helpers\CurrencyHelper; @endphp

@section('title', 'Checkout - Templatr')
@section('robots', 'noindex, nofollow')

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

            {{-- Guest Info Form --}}
            <div id="guest-info-section" class="mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold">Your Information</h2>
                    @auth
                    <div class="flex items-center space-x-2 text-sm text-green-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <span>Signed in as <strong>{{ Auth::user()->name }}</strong></span>
                    </div>
                    @endauth
                </div>

                @guest
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-4 text-sm text-amber-800">
                    <div class="flex items-start space-x-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="font-semibold">Checking out as a guest?</p>
                            <p class="mt-1">You are about to make a purchase as a guest. Please provide your details below so we can send you your order receipt and download links.</p>
                            <a href="{{ route('login') }}" class="inline-flex items-center mt-2 text-amber-900 font-semibold hover:text-black transition-colors text-xs">
                                <span>Already have an account?</span>
                                <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="guest_name" form="paymentForm" value="{{ old('guest_name', session('guest_data.guest_name')) }}" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]"
                            placeholder="e.g. John Doe">
                        @error('guest_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="guest_email" form="paymentForm" value="{{ old('guest_email', session('guest_data.guest_email')) }}" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]"
                            placeholder="e.g. john@example.com">
                        @error('guest_email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Phone Number <span class="text-red-500">*</span>
                        </label>
                        <input type="tel" name="guest_phone" form="paymentForm" value="{{ old('guest_phone', session('guest_data.guest_phone')) }}" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]"
                            placeholder="e.g. +234 801 234 5678">
                        @error('guest_phone')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
                @endguest
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
                            <div class="flex items-center justify-center">
                                <img src="{{ asset('paystack-logo.png') }}" alt="Paystack" class="h-6 w-auto">
                            </div>
                            <p class="text-xs text-gray-500">Card, Transfer, USSD</p>
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

            <!-- Terms acceptance -->
            @guest
            <div class="mb-5">
                <label class="flex items-start space-x-3 cursor-pointer group">
                    <input type="checkbox" name="terms_accepted" form="paymentForm" id="terms_accepted" value="1"
                        class="mt-0.5 w-4 h-4 rounded border-gray-300 text-black focus:ring-[#FFC300] focus:ring-2 cursor-pointer flex-shrink-0"
                        {{ old('terms_accepted') ? 'checked' : '' }}>
                    <span class="text-sm text-gray-600 leading-snug">
                        I have read and agree to the
                        <a href="{{ route('terms.show') }}" target="_blank" rel="noopener noreferrer"
                           class="font-semibold text-gray-900 underline underline-offset-2 hover:text-[#FFC300] transition-colors">
                            Terms of Service
                        </a>.
                        I understand this is a digital product purchase with no physical delivery.
                    </span>
                </label>
                @error('terms_accepted')
                    <p class="text-red-500 text-xs mt-1.5 ml-7">{{ $message }}</p>
                @enderror
            </div>
            @endguest

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
                By completing this purchase you agree to our
                <a href="{{ route('terms.show') }}" target="_blank" rel="noopener noreferrer" class="underline underline-offset-2 hover:text-gray-800">Terms of Service</a>.
                All transactions are securely processed in Nigerian Naira ({{ CurrencyHelper::SYMBOL }}).
            </p>
        </div>
    </div>
</section>

{{-- Payment progress overlay --}}
<div id="payment-overlay" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-[999] flex items-center justify-center p-4">
    <div class="absolute top-0 left-0 right-0 h-1">
        <div id="pay-top-bar" class="h-full bg-[#FFC300] transition-all duration-500" style="width:0%;box-shadow:0 0 10px #FFC300"></div>
    </div>
    <div class="bg-white rounded-2xl p-8 w-full max-w-sm shadow-2xl">
        <div class="flex justify-center mb-5">
            <div class="w-16 h-16 bg-[#FFC300]/10 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-[#FFC300] animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>
        <h3 class="text-xl font-bold text-center mb-1">Processing Payment</h3>
        <p class="text-sm text-gray-500 text-center mb-5">Connecting securely — do not close this window</p>
        <div class="h-2.5 bg-gray-100 rounded-full overflow-hidden mb-1">
            <div id="pay-modal-bar" class="h-full bg-[#FFC300] rounded-full transition-all duration-500" style="width:0%"></div>
        </div>
        <p id="pay-pct" class="text-xs font-semibold text-[#FFC300] text-right mb-5">0%</p>
        <div id="pay-steps" class="space-y-3"></div>
    </div>
</div>

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

    // Payment progress overlay
    (function () {
        const STEPS = [
            { label: 'Verifying your order',        target: 25, ms: 700  },
            { label: 'Connecting to payment gateway', target: 65, ms: 900 },
            { label: 'Redirecting securely…',        target: 90, ms: 600  },
        ];

        const overlay   = document.getElementById('payment-overlay');
        const topBar    = document.getElementById('pay-top-bar');
        const modalBar  = document.getElementById('pay-modal-bar');
        const pctLabel  = document.getElementById('pay-pct');
        const stepsList = document.getElementById('pay-steps');

        STEPS.forEach((s, i) => {
            const d = document.createElement('div');
            d.id = 'pay-step-' + i;
            d.className = 'flex items-center gap-3 opacity-30 transition-all duration-300';
            d.innerHTML = `<div class="w-6 h-6 rounded-full bg-gray-100 flex-shrink-0 flex items-center justify-center step-icon">
                <span class="text-xs font-bold text-gray-400">${i + 1}</span></div>
                <span class="text-sm text-gray-600">${s.label}</span>`;
            stepsList.appendChild(d);
        });

        let pct = 0;

        function setPct(p) {
            pct = Math.min(Math.max(p, 0), 99);
            topBar.style.width   = pct + '%';
            modalBar.style.width = pct + '%';
            pctLabel.textContent = Math.round(pct) + '%';
        }

        function setStep(i, state) {
            const el   = document.getElementById('pay-step-' + i);
            if (!el) return;
            el.classList.remove('opacity-30');
            const icon = el.querySelector('.step-icon');
            if (state === 'done') {
                icon.className = 'w-6 h-6 rounded-full bg-green-500 flex-shrink-0 flex items-center justify-center step-icon';
                icon.innerHTML = `<svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>`;
            } else {
                icon.className = 'w-6 h-6 rounded-full bg-[#FFC300] flex-shrink-0 flex items-center justify-center step-icon';
                icon.innerHTML = `<span class="text-xs font-bold text-black">${i + 1}</span>`;
            }
        }

        function animateTo(from, to, dur, cb) {
            const t0 = performance.now();
            (function tick(now) {
                const t = Math.min((now - t0) / dur, 1);
                setPct(from + (to - from) * (1 - Math.pow(1 - t, 3)));
                t < 1 ? requestAnimationFrame(tick) : cb && cb();
            })(performance.now());
        }

        function runSteps() {
            let idx = 0;
            function next() {
                if (idx >= STEPS.length) return;
                setStep(idx, 'active');
                animateTo(pct, STEPS[idx].target, STEPS[idx].ms, function () {
                    setStep(idx, 'done');
                    idx++;
                    setTimeout(next, 150);
                });
            }
            next();
        }

        document.getElementById('paymentForm').addEventListener('submit', function () {
            overlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            setPct(5);
            setTimeout(runSteps, 200);
        });
    })();
</script>
@endpush
@endsection
