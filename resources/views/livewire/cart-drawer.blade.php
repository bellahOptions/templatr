<div
    x-on:cart-updated.window="$wire.open()"
    x-on:open-cart-drawer.window="$wire.open()"
>
    {{-- Backdrop --}}
    <div
        x-show="@js($open)"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/50 z-40"
        wire:click="close"
        style="{{ $open ? '' : 'display:none;' }}"
    ></div>

    {{-- Drawer panel --}}
    <div
        class="fixed inset-y-0 right-0 z-50 flex flex-col w-full sm:w-96 bg-white shadow-2xl transition-transform duration-300 {{ $open ? 'translate-x-0' : 'translate-x-full' }}"
        role="dialog"
        aria-modal="true"
    >
        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 bg-white">
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5 text-[#FFC300]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                </svg>
                <h2 class="font-bold text-gray-900">Cart <span class="text-sm font-normal text-gray-500">({{ count($items) }})</span></h2>
            </div>
            <button wire:click="close" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Item list --}}
        <div class="flex-1 overflow-y-auto px-5 py-4">
            @if(empty($items))
                <div class="flex flex-col items-center justify-center h-full py-16 text-center">
                    <svg class="w-16 h-16 text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <p class="text-gray-700 font-semibold">Your cart is empty</p>
                    <p class="text-sm text-gray-400 mt-1 mb-5">Browse our collection and add templates you love.</p>
                    <a href="{{ route('products.index') }}" wire:click="close" class="bg-[#FFC300] text-black px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-[#FFD633] transition-colors">
                        Browse Templates
                    </a>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($items as $productId => $item)
                    <div class="flex items-start space-x-3 bg-gray-50 rounded-xl p-3 group">
                        <div class="w-14 h-14 rounded-lg overflow-hidden flex-shrink-0 bg-gray-200">
                            @if(!empty($item['thumbnail']))
                                <img
                                    src="{{ \App\Helpers\CloudinaryHelper::imageUrl($item['thumbnail'], 100) }}"
                                    class="w-full h-full object-cover"
                                    alt="{{ $item['title'] }}"
                                    loading="lazy">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('products.show', $item['slug']) }}"
                               wire:click="close"
                               class="text-sm font-semibold text-gray-900 hover:text-[#FFC300] transition-colors line-clamp-2 block">
                                {{ $item['title'] }}
                            </a>
                            <p class="text-[#FFC300] font-bold text-sm mt-1">{{ \App\Helpers\CurrencyHelper::format($item['price']) }}</p>
                        </div>
                        <button
                            wire:click="removeItem({{ $productId }})"
                            class="p-1 rounded text-gray-300 hover:text-red-500 hover:bg-red-50 transition-colors flex-shrink-0"
                            title="Remove">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    @endforeach

                    {{-- Upsell nudge --}}
                    <div class="bg-gradient-to-r from-[#FFC300]/10 to-[#FFD633]/10 border border-[#FFC300]/30 rounded-xl p-3.5 text-center mt-4">
                        <p class="text-sm font-semibold text-gray-800">Love what you see?</p>
                        <p class="text-xs text-gray-500 mt-0.5">Explore more premium templates to complete your project.</p>
                        <a href="{{ route('products.index') }}"
                           wire:click="close"
                           class="mt-2.5 inline-block text-xs font-bold text-[#CC9900] hover:text-black transition-colors underline-offset-2 hover:underline">
                            Browse more →
                        </a>
                    </div>
                </div>
            @endif
        </div>

        {{-- Footer with total + CTA --}}
        @if(!empty($items))
        <div class="border-t border-gray-200 px-5 py-4 space-y-3 bg-white">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500 font-medium">Subtotal ({{ count($items) }} items)</span>
                <span class="text-lg font-bold text-gray-900">{{ \App\Helpers\CurrencyHelper::format($total) }}</span>
            </div>
            <a href="{{ route('checkout.index') }}"
               class="w-full bg-black text-white py-3.5 rounded-xl font-bold text-sm hover:bg-gray-800 active:scale-[0.98] transition-all flex items-center justify-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Checkout Now</span>
            </a>
            <a href="{{ route('cart.index') }}"
               wire:click="close"
               class="w-full block text-center text-sm text-gray-400 hover:text-gray-700 transition-colors">
                View full cart
            </a>
        </div>
        @endif
    </div>
</div>
