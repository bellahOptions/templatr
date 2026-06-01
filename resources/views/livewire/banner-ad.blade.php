<div>
    @if($ad)
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#FFC300]/10 via-yellow-50 to-[#FFC300]/5 border border-[#FFC300]/20 {{ $position === 'banner' ? 'w-full' : '' }}">
            @if($ad['image_url'])
                <a href="{{ $ad['link_url'] ?? '#' }}" target="_blank" class="block">
                    <img src="{{ $ad['image_url'] }}" alt="{{ $ad['title'] }}" class="w-full h-auto object-cover" />
                </a>
            @else
                <a href="{{ $ad['link_url'] ?? '#' }}" target="_blank" class="block p-6 text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-[#FFC300]/20 rounded-full mb-3">
                        <svg class="w-6 h-6 text-[#FFC300]" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M21 6h-2v3h-2V6h-2v3h-2V6h-2v3H9V6H7v3H5V6H3v12h18V6z"/>
                        </svg>
                    </div>
                    <h4 class="text-lg font-bold text-gray-900">{{ $ad['title'] }}</h4>
                    @if($ad['description'])
                        <p class="text-sm text-gray-500 mt-1">{{ $ad['description'] }}</p>
                    @endif
                    @if($ad['link_url'])
                        <span class="inline-flex items-center text-sm font-semibold text-[#FFC300] hover:text-black mt-3 transition-colors">
                            Learn More <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </span>
                    @endif
                </a>
            @endif
        </div>
    @endif
</div>
