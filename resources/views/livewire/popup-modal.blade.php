<div>
    @if($show && $activePopup)
        <div x-data="{ 
            show: true,
            triggerDelay: {{ $activePopup['trigger_delay'] ?? 5 }},
            init() {
                if (this.triggerDelay > 0) {
                    setTimeout(() => { this.show = true; }, this.triggerDelay * 1000);
                }
            }
        }" 
        x-show="show" 
        x-cloak
        class="fixed inset-0 z-[100] flex items-center justify-center p-4"
        @keydown.escape.window="show = false">
            
            {{-- Backdrop --}}
            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="show = false"></div>
            
            {{-- Modal --}}
            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="relative bg-white rounded-3xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto z-10">
                
                {{-- Image --}}
                @if($activePopup['image_url'])
                    <div class="aspect-[16/9] bg-gradient-to-br from-gray-100 to-gray-200 rounded-t-3xl overflow-hidden">
                        <img src="{{ $activePopup['image_url'] }}" alt="{{ $activePopup['title'] }}" class="w-full h-full object-cover" />
                    </div>
                @else
                    <div class="aspect-[16/9] bg-gradient-to-br from-[#FFC300]/20 to-yellow-100 rounded-t-3xl flex items-center justify-center">
                        <svg class="w-16 h-16 text-[#FFC300]" viewBox="0 0 88 93" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M88 7.16H0V93.16H36V51.16H44H52V93.16H88V7.16Z"/>
                        </svg>
                    </div>
                @endif
                
                {{-- Content --}}
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-3">{{ $activePopup['title'] }}</h3>
                    <div class="text-gray-600 text-sm leading-relaxed">{!! nl2br(e($activePopup['content'])) !!}</div>
                    
                    <div class="mt-6 flex items-center space-x-3">
                        @if($activePopup['button_url'])
                            <a href="{{ $activePopup['button_url'] }}" wire:click="trackClick" class="inline-flex items-center bg-[#FFC300] hover:bg-black hover:text-white px-6 py-2.5 rounded-xl text-sm font-semibold transition-all">
                                {{ $activePopup['button_text'] ?? 'Learn More' }}
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                </svg>
                            </a>
                        @endif
                        <button wire:click="dismiss" @click="show = false" class="text-sm text-gray-400 hover:text-gray-700 font-medium">No thanks</button>
                    </div>
                </div>
                
                {{-- Close --}}
                <button wire:click="dismiss" @click="show = false" class="absolute top-4 right-4 w-8 h-8 bg-black/30 hover:bg-black/50 text-white rounded-full flex items-center justify-center transition-colors backdrop-blur-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    @endif
</div>
