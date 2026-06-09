<div>
    @if($show && $activeAd)
        <div
            x-data="{ visible: false }"
            x-init="setTimeout(() => visible = true, 800)"
            x-show="visible"
            x-cloak
            class="fixed inset-0 z-[110] flex items-center justify-center p-4"
            @keydown.escape.window="visible = false; $wire.dismiss()">

            {{-- Backdrop --}}
            <div
                x-show="visible"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/60 backdrop-blur-sm"
                @click="visible = false; $wire.dismiss()">
            </div>

            {{-- Modal --}}
            <div
                x-show="visible"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-90"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-90"
                class="relative bg-white rounded-3xl shadow-2xl max-w-md w-full overflow-hidden z-10">

                {{-- Image banner --}}
                @if($activeAd['image_url'])
                    <div class="aspect-[16/7] overflow-hidden">
                        @if($activeAd['link_url'])
                            <a href="{{ $activeAd['link_url'] }}" target="_blank" rel="noopener noreferrer">
                                <img src="{{ $activeAd['image_url'] }}" alt="{{ $activeAd['title'] }}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-500">
                            </a>
                        @else
                            <img src="{{ $activeAd['image_url'] }}" alt="{{ $activeAd['title'] }}" class="w-full h-full object-cover">
                        @endif
                    </div>
                @else
                    <div class="aspect-[16/7] bg-gradient-to-br from-[#FFC300]/30 via-yellow-50 to-[#FFC300]/10 flex items-center justify-center">
                        <svg class="w-16 h-16 text-[#FFC300]" viewBox="0 0 88 93" fill="currentColor">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M88 7.16H0V93.16H36V51.16H44H52V93.16H88V7.16Z"/>
                        </svg>
                    </div>
                @endif

                {{-- Content --}}
                <div class="p-6">
                    <div class="flex items-start justify-between mb-1">
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#FFC300] uppercase tracking-wider">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#FFC300] animate-pulse"></span>
                            Advertisement
                        </span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mt-2 mb-2">{{ $activeAd['title'] }}</h3>
                    @if($activeAd['description'])
                        <p class="text-gray-500 text-sm leading-relaxed">{{ $activeAd['description'] }}</p>
                    @endif

                    <div class="mt-5 flex items-center gap-3">
                        @if($activeAd['link_url'])
                            <a href="{{ $activeAd['link_url'] }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-2 bg-[#FFC300] hover:bg-black hover:text-white text-black px-6 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200">
                                Learn More
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                </svg>
                            </a>
                        @endif
                        <button @click="visible = false; $wire.dismiss()"
                            class="text-sm text-gray-400 hover:text-gray-700 font-medium transition-colors">
                            No thanks
                        </button>
                    </div>
                </div>

                {{-- Close button --}}
                <button @click="visible = false; $wire.dismiss()"
                    class="absolute top-3 right-3 w-8 h-8 bg-black/30 hover:bg-black/60 text-white rounded-full flex items-center justify-center transition-colors backdrop-blur-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    @endif
</div>
