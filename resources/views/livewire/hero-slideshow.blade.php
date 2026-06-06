@php use App\Helpers\CurrencyHelper; @endphp

@if($slides->isEmpty())
{{-- Static fallback hero when no slides are configured --}}
<section class="relative bg-black text-white overflow-hidden min-h-[80vh] flex items-center">
    <div class="absolute inset-0 bg-gradient-to-br from-[#FFC300]/10 via-black to-black animate-gradient"></div>
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: url(&quot;data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23FFC300' fill-opacity='0.4'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E&quot;)"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-28 lg:py-36">
        <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-bold tracking-tight leading-[1.1] animate-fade-in-up">
            Get Premium Creative &
            <span class="text-[#FFC300]">Web Resources</span>
            <br>for as Affordable as
            <span class="text-[#FFC300]">{{ CurrencyHelper::formatInt(3000) }}</span>
        </h1>
        <p class="mt-6 text-base sm:text-lg md:text-xl text-gray-400 leading-relaxed max-w-xl animate-fade-in-up stagger-2">
            Unlock thousands of premium WordPress themes, plugins, design templates, and digital assets crafted by world-class creators.
        </p>
        <div class="mt-8 flex flex-wrap items-center gap-4 animate-fade-in-up stagger-3">
            <a href="{{ route('products.index') }}" class="bg-[#FFC300] text-black px-8 py-4 rounded-xl text-base font-bold hover:bg-[#FFD633] transition-all transform hover:scale-105 shadow-lg shadow-[#FFC300]/25 w-full sm:w-auto text-center animate-pulse-glow">
                Explore Marketplace
            </a>
            <a href="#featured" class="border border-gray-700 text-white px-8 py-4 rounded-xl text-base font-semibold hover:border-[#FFC300] hover:text-[#FFC300] transition-all w-full sm:w-auto text-center">
                View Featured Items
            </a>
        </div>
        <div class="mt-8 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-gray-500 animate-fade-in-up stagger-4">
            <span class="flex items-center"><svg class="w-4 h-4 text-green-400 mr-1.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Instant Download</span>
            <span class="flex items-center"><svg class="w-4 h-4 text-green-400 mr-1.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Lifetime Updates</span>
            <span class="flex items-center"><svg class="w-4 h-4 text-green-400 mr-1.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Secure Payment</span>
        </div>
    </div>
</section>
@else
{{-- Dynamic slideshow --}}
<div
    x-data="{
        current: 0,
        total: {{ $slides->count() }},
        autoplayInterval: null,
        paused: false,
        init() {
            this.startAutoplay();
        },
        startAutoplay() {
            this.autoplayInterval = setInterval(() => {
                if (!this.paused) { this.next(); }
            }, 6000);
        },
        next() {
            this.current = (this.current + 1) % this.total;
        },
        prev() {
            this.current = (this.current - 1 + this.total) % this.total;
        },
        goTo(index) {
            this.current = index;
        }
    }"
    class="relative bg-black text-white overflow-hidden min-h-[80vh] flex items-center"
    @mouseenter="paused = true"
    @mouseleave="paused = false"
>
    {{-- Slides --}}
    @foreach($slides as $index => $slide)
    <div
        x-show="current === {{ $index }}"
        x-transition:enter="transition-opacity duration-700 ease-in-out"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity duration-700 ease-in-out"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="absolute inset-0"
        style="display: none;"
    >
        @if($slide->image_url)
            <img src="{{ $slide->image_url }}"
                 alt="{{ $slide->title }}"
                 class="w-full h-full object-cover">
        @endif
        <div class="absolute inset-0 bg-gradient-to-br from-black/70 via-black/50 to-black/70"></div>
        <div class="absolute inset-0 opacity-[0.03]" style="background-image: url(&quot;data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23FFC300' fill-opacity='0.4'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E&quot;)"></div>
    </div>
    @endforeach

    {{-- Content --}}
    <div class="relative w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-28 lg:py-36">
        @foreach($slides as $index => $slide)
        <div
            x-show="current === {{ $index }}"
            x-transition:enter="transition duration-700 ease-out"
            x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            style="display: none;"
        >
            <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-bold tracking-tight leading-[1.1]">
                {{ $slide->title }}
            </h1>
            @if($slide->description)
            <p class="mt-6 text-base sm:text-lg md:text-xl text-gray-300 leading-relaxed max-w-xl">
                {{ $slide->description }}
            </p>
            @endif
            @if($slide->cta_text && $slide->cta_url)
            <div class="mt-8 flex flex-wrap items-center gap-4">
                <a href="{{ $slide->cta_url }}" class="bg-[#FFC300] text-black px-8 py-4 rounded-xl text-base font-bold hover:bg-[#FFD633] transition-all transform hover:scale-105 shadow-lg shadow-[#FFC300]/25 w-full sm:w-auto text-center animate-pulse-glow">
                    {{ $slide->cta_text }}
                </a>
                <a href="#featured" class="border border-gray-500 text-white px-8 py-4 rounded-xl text-base font-semibold hover:border-[#FFC300] hover:text-[#FFC300] transition-all w-full sm:w-auto text-center">
                    View Featured Items
                </a>
            </div>
            @endif
        </div>
        @endforeach
    </div>

    @if($slides->count() > 1)
    {{-- Prev / Next arrows --}}
    <button @click="prev()"
            class="absolute left-4 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-black/40 hover:bg-black/70 backdrop-blur-sm text-white flex items-center justify-center transition-all hover:scale-110 border border-white/10"
            aria-label="Previous slide">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    </button>
    <button @click="next()"
            class="absolute right-4 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-black/40 hover:bg-black/70 backdrop-blur-sm text-white flex items-center justify-center transition-all hover:scale-110 border border-white/10"
            aria-label="Next slide">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </button>

    {{-- Dot indicators --}}
    <div class="absolute bottom-6 left-1/2 -translate-x-1/2 z-20 flex items-center gap-2">
        @foreach($slides as $index => $slide)
        <button
            @click="goTo({{ $index }})"
            :class="current === {{ $index }} ? 'bg-[#FFC300] w-6' : 'bg-white/40 hover:bg-white/70 w-2'"
            class="h-2 rounded-full transition-all duration-300"
            aria-label="Go to slide {{ $index + 1 }}"
        ></button>
        @endforeach
    </div>
    @endif
</div>
@endif
