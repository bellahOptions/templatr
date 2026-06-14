<div
    x-data="{ open: false }"
    class="relative w-full"
    @click.away="open = false"
    @keydown.escape.window="open = false"
>
    <div class="flex">
        <div class="flex-1 relative">
            <input
                type="text"
                wire:model.live.debounce.300ms="query"
                @focus="open = true"
                placeholder="Search items, authors, or categories..."
                autocomplete="off"
                class="w-full px-4 py-3.5 bg-gray-800 pl-12 rounded-l-xl text-gray-300 focus:outline-none focus:ring-2 focus:ring-[#FFC300] text-sm"
            >
            <svg class="absolute left-4 top-4 w-5 h-5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
        <a
            href="{{ $query !== '' ? route('products.index', ['search' => $query]) : route('products.index') }}"
            class="bg-[#FFC300] text-black px-6 rounded-r-xl font-semibold hover:bg-[#FFD633] transition-colors text-sm flex items-center whitespace-nowrap"
            wire:navigate
        >
            Search
        </a>
    </div>

    {{-- Dropdown --}}
    <div
        x-show="open && $wire.query.length >= 2"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="absolute top-full left-0 right-0 mt-2 bg-white rounded-2xl shadow-2xl border border-gray-200 z-50 overflow-hidden"
    >
        {{-- Loading indicator --}}
        <div wire:loading wire:target="query" class="flex items-center gap-2 px-5 py-4 text-sm text-gray-400">
            <svg class="w-4 h-4 animate-spin text-[#FFC300]" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Searching...
        </div>

        <div wire:loading.remove wire:target="query">
            @if(count($suggestions) > 0 || $results->isNotEmpty())
                {{-- Suggestions --}}
                @if(count($suggestions) > 0)
                <div class="p-3 border-b border-gray-100">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest px-2 mb-2">Suggestions</p>
                    @foreach($suggestions as $suggestion)
                    <a
                        href="{{ route('products.index', ['search' => $suggestion]) }}"
                        wire:navigate
                        class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50 group transition-colors"
                    >
                        <svg class="w-4 h-4 text-gray-300 flex-shrink-0 group-hover:text-[#FFC300] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <span class="text-sm text-gray-700 group-hover:text-black truncate transition-colors">{{ $suggestion }}</span>
                    </a>
                    @endforeach
                </div>
                @endif

                {{-- Live product results --}}
                @if($results->isNotEmpty())
                <div class="p-3 border-b border-gray-100">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest px-2 mb-2">Products</p>
                    @foreach($results as $result)
                    <a
                        href="{{ route('products.show', $result) }}"
                        wire:navigate
                        class="flex items-center gap-3 px-2 py-2.5 rounded-lg hover:bg-gray-50 group transition-colors"
                    >
                        <div class="w-12 h-12 rounded-xl overflow-hidden bg-gray-100 flex-shrink-0">
                            @if($result->thumbnail_url)
                                <img src="{{ $result->thumbnail_url }}" class="w-full h-full object-cover" alt="{{ $result->title }}" loading="lazy">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate group-hover:text-[#CC9900] transition-colors">{{ $result->title }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $result->category->name }}</p>
                        </div>
                        <span class="text-sm font-bold text-gray-900 flex-shrink-0 ml-2">{{ $result->formatted_price }}</span>
                    </a>
                    @endforeach
                </div>
                @endif

                {{-- View all results --}}
                <div class="px-5 py-3">
                    <a
                        href="{{ route('products.index', ['search' => $query]) }}"
                        wire:navigate
                        class="text-sm text-[#FFC300] hover:text-black font-semibold transition-colors"
                    >
                        View all results for "{{ $query }}" &rarr;
                    </a>
                </div>
            @else
                <div class="px-4 py-8 text-center">
                    <svg class="w-10 h-10 mx-auto text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-gray-500">No results found for "<span class="font-medium">{{ $query }}</span>"</p>
                    <a href="{{ route('products.index') }}" wire:navigate class="mt-2 inline-block text-sm text-[#FFC300] hover:text-black font-medium transition-colors">Browse all items</a>
                </div>
            @endif
        </div>
    </div>
</div>
