<div>
    @if($ads->isNotEmpty())
        <div
            class="w-full bg-black overflow-hidden relative"
            style="height: 44px;"
            x-data="{
                speed: 80,
                init() {
                    this.setDuration();
                    window.addEventListener('resize', () => this.setDuration());
                },
                setDuration() {
                    const track = this.$refs.track;
                    if (!track) return;
                    const halfWidth = track.scrollWidth / 2;
                    const duration = halfWidth / this.speed;
                    track.style.animationDuration = duration + 's';
                }
            }"
        >
            {{-- Gradient fade on edges --}}
            <div class="absolute left-0 top-0 bottom-0 w-16 z-10 pointer-events-none"
                 style="background: linear-gradient(to right, #000 0%, transparent 100%);"></div>
            <div class="absolute right-0 top-0 bottom-0 w-16 z-10 pointer-events-none"
                 style="background: linear-gradient(to left, #000 0%, transparent 100%);"></div>

            <div x-ref="track" class="marquee-track flex items-center h-full" style="width: max-content; animation-duration: 30s;">
                {{-- Render twice for seamless infinite loop --}}
                @foreach([1, 2] as $_)
                    <div class="flex items-center gap-0 shrink-0">
                        @foreach($ads as $ad)
                            <a href="{{ $ad->link_url ?: '#' }}"
                               @if($ad->link_url) target="_blank" rel="noopener noreferrer" @endif
                               class="flex items-center gap-3 px-6 py-1 group shrink-0 {{ $ad->link_url ? 'cursor-pointer' : 'cursor-default pointer-events-none' }}">

                                @if($ad->image_url)
                                    <img src="{{ $ad->image_url }}" alt="{{ $ad->title }}"
                                         class="h-6 w-auto rounded object-cover shrink-0 ring-1 ring-[#FFC300]/30">
                                @else
                                    <span class="w-2 h-2 rounded-full bg-[#FFC300] shrink-0 animate-pulse"></span>
                                @endif

                                <span class="text-white text-sm font-medium whitespace-nowrap group-hover:text-[#FFC300] transition-colors duration-200">
                                    {{ $ad->title }}
                                </span>

                                @if($ad->description)
                                    <span class="text-gray-400 text-xs whitespace-nowrap hidden sm:inline">
                                        — {{ Str::limit($ad->description, 60) }}
                                    </span>
                                @endif

                                {{-- Separator --}}
                                <span class="text-[#FFC300]/40 text-lg font-light ml-2 shrink-0">✦</span>
                            </a>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>

        @push('styles')
        <style>
            .marquee-track {
                animation: marquee-scroll linear infinite;
            }

            .marquee-track:hover {
                animation-play-state: paused;
            }

            @keyframes marquee-scroll {
                0%   { transform: translateX(-50%); }
                100% { transform: translateX(0); }
            }
        </style>
        @endpush
    @endif
</div>
