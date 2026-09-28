@php
    use App\Helpers\CurrencyHelper;
    use Illuminate\Support\Str;
    $activeFilterCount = (int)($category !== '') + (int)($type !== '') + (int)($minPrice !== '' || $maxPrice !== '');
@endphp
<div x-data="{ filtersOpen: false }">

    {{-- ── Mobile: filter toggle + sort ─────────────────────────────────────── --}}
    <div class="flex items-center gap-3 mb-6 lg:hidden">
        <button
            @click="filtersOpen = true"
            class="flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-medium hover:border-[#FFC300] transition-colors"
            aria-label="Open filters"
        >
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
            </svg>
            Filters
            @if($activeFilterCount > 0)
                <span class="bg-[#FFC300] text-black text-[10px] font-bold rounded-full w-5 h-5 flex items-center justify-center leading-none">{{ $activeFilterCount }}</span>
            @endif
        </button>
        <div class="flex-1">
            <select wire:model.live="sort" class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FFC300] bg-white">
                <option value="">Sort: Latest</option>
                <option value="price_asc">Price: Low to High</option>
                <option value="price_desc">Price: High to Low</option>
                <option value="popular">Most Popular</option>
            </select>
        </div>
    </div>

    {{-- ── Mobile filter slide-out panel ────────────────────────────────────── --}}
    <div
        x-show="filtersOpen"
        x-cloak
        class="fixed inset-0 z-50 flex lg:hidden"
        aria-modal="true"
        role="dialog"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div @click="filtersOpen = false" class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
        <div
            class="relative ml-auto w-80 max-w-[90vw] h-full bg-white overflow-y-auto shadow-2xl"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
        >
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-semibold text-lg">Filters</h3>
                    <button @click="filtersOpen = false" class="text-gray-400 hover:text-gray-700 transition-colors p-1" aria-label="Close filters">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Categories — hidden on category landing pages, where the path already scopes the list --}}
                @if($lockedCategory === '')
                <div class="mb-6">
                    <h4 class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-3">Categories</h4>
                    <div class="space-y-1">
                        <button wire:click="setCategory('')" @click="filtersOpen = false" class="block w-full text-left text-sm px-3 py-2 rounded-lg transition-colors {{ $activeCategory === '' ? 'bg-[#FFC300]/15 text-gray-900 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-black' }}">All Categories</button>
                        @foreach($categories as $cat)
                        <button wire:click="setCategory('{{ $cat->slug }}')" @click="filtersOpen = false" class="block w-full text-left text-sm px-3 py-2 rounded-lg transition-colors {{ $activeCategory === $cat->slug ? 'bg-[#FFC300]/15 text-gray-900 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-black' }}">{{ $cat->name }}</button>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- File Type --}}
                <div class="mb-6">
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">File Type</h4>
                    <div class="space-y-1">
                        <button wire:click="setType('')" @click="filtersOpen = false" class="block w-full text-left text-sm px-3 py-2 rounded-lg transition-colors {{ $type === '' ? 'bg-[#FFC300]/10 text-[#CC9900] font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-black' }}">All Types</button>
                        @foreach($types as $key => $label)
                        <button wire:click="setType('{{ $key }}')" @click="filtersOpen = false" class="block w-full text-left text-sm px-3 py-2 rounded-lg transition-colors {{ $type === $key ? 'bg-[#FFC300]/10 text-[#CC9900] font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-black' }}">{{ $label }}</button>
                        @endforeach
                    </div>
                </div>

                {{-- Price Range --}}
                <div class="mb-6">
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Price Range ({{ CurrencyHelper::SYMBOL }})</h4>
                    <div class="flex gap-2 mb-2">
                        <input type="number" wire:model="minPrice" placeholder="Min" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">
                        <input type="number" wire:model="maxPrice" placeholder="Max" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">
                    </div>
                    <button wire:click="applyPriceFilter" @click="filtersOpen = false" class="w-full bg-black text-white text-sm py-2.5 rounded-lg font-medium hover:bg-gray-800 transition-colors">Apply</button>
                </div>

                @if($activeFilterCount > 0)
                <button wire:click="clearFilters" @click="filtersOpen = false" class="w-full text-sm text-center text-gray-400 hover:text-black underline transition-colors">
                    Clear all filters
                </button>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Main layout ──────────────────────────────────────────────────────── --}}
    <div class="lg:grid lg:grid-cols-4 lg:gap-8">

        {{-- Desktop Sidebar --}}
        <div class="hidden lg:block">
            <div class="bg-white border border-gray-200 rounded-2xl p-6 sticky top-24">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-semibold text-lg">Filters</h3>
                    @if($activeFilterCount > 0)
                        <button wire:click="clearFilters" class="text-xs text-gray-400 hover:text-black underline transition-colors">Clear all</button>
                    @endif
                </div>

                {{-- Categories — hidden on category landing pages, where the path already scopes the list --}}
                @if($lockedCategory === '')
                <div class="mb-6">
                    <h4 class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-3">Categories</h4>
                    <div class="space-y-1">
                        <button wire:click="setCategory('')" class="block w-full text-left text-sm px-3 py-2 rounded-lg transition-colors {{ $activeCategory === '' ? 'bg-[#FFC300]/15 text-gray-900 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-black' }}">All Categories</button>
                        @foreach($categories as $cat)
                        <button wire:click="setCategory('{{ $cat->slug }}')" class="block w-full text-left text-sm px-3 py-2 rounded-lg transition-colors {{ $activeCategory === $cat->slug ? 'bg-[#FFC300]/15 text-gray-900 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-black' }}">{{ $cat->name }}</button>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- File Type --}}
                <div class="mb-6">
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">File Type</h4>
                    <div class="space-y-1">
                        <button wire:click="setType('')" class="block w-full text-left text-sm px-3 py-2 rounded-lg transition-colors {{ $type === '' ? 'bg-[#FFC300]/10 text-[#CC9900] font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-black' }}">All Types</button>
                        @foreach($types as $key => $label)
                        <button wire:click="setType('{{ $key }}')" class="block w-full text-left text-sm px-3 py-2 rounded-lg transition-colors {{ $type === $key ? 'bg-[#FFC300]/10 text-[#CC9900] font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-black' }}">{{ $label }}</button>
                        @endforeach
                    </div>
                </div>

                {{-- Price Range --}}
                <div>
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Price Range ({{ CurrencyHelper::SYMBOL }})</h4>
                    <div class="flex gap-2 mb-2">
                        <input type="number" wire:model="minPrice" placeholder="Min" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">
                        <input type="number" wire:model="maxPrice" placeholder="Max" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">
                    </div>
                    <button wire:click="applyPriceFilter" class="w-full bg-black text-white text-sm py-2.5 rounded-lg font-medium hover:bg-gray-800 transition-colors">Apply</button>
                </div>
            </div>
        </div>

        {{-- ── Product Grid ──────────────────────────────────────────────────── --}}
        <div class="lg:col-span-3">

            {{-- Sort bar + result count (desktop) --}}
            <div class="hidden lg:flex items-center justify-between mb-6 gap-3">
                <p class="text-sm text-gray-500">
                    Showing <span class="font-semibold text-gray-900">{{ min($perPage, $total) }}</span> of
                    <span class="font-semibold text-gray-900">{{ $total }}</span> {{ Str::plural('item', $total) }}
                </p>
                <select wire:model.live="sort" class="text-sm border border-gray-300 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FFC300] bg-white">
                    <option value="">Sort: Latest</option>
                    <option value="price_asc">Price: Low to High</option>
                    <option value="price_desc">Price: High to Low</option>
                    <option value="popular">Most Popular</option>
                </select>
            </div>

            {{-- Mobile result count --}}
            <p class="text-sm text-gray-500 mb-4 lg:hidden">
                <span class="font-semibold text-gray-900">{{ $total }}</span> {{ Str::plural('item', $total) }} found
            </p>

            @if($products->isEmpty())
                <div class="text-center py-20">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">No items found</h3>
                    <p class="text-gray-500 text-sm">Try adjusting your search or filter criteria</p>
                    <button wire:click="clearFilters" class="inline-flex mt-4 text-sm text-[#FFC300] hover:text-black font-semibold transition-colors">Clear all filters</button>
                </div>
            @else
                {{-- Product grid --}}
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5 transition-opacity duration-150"
                    wire:loading.class="opacity-50"
                    wire:target="setCategory,setType,applyPriceFilter,clearFilters,sort,search"
                >
                    @foreach($products as $product)
                        <x-product-card :product="$product" wire:key="product-{{ $product->id }}" />
                    @endforeach
                </div>

                {{-- Loading skeleton shown while loadMore fires --}}
                <div wire:loading wire:target="loadMore" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5 mt-5">
                    @for($i = 0; $i < 3; $i++)
                    <div class="bg-white rounded-2xl overflow-hidden border border-gray-200 animate-pulse">
                        <div class="aspect-[4/3] bg-gray-200"></div>
                        <div class="p-4 space-y-2">
                            <div class="h-3 bg-gray-200 rounded w-24"></div>
                            <div class="h-4 bg-gray-200 rounded w-full"></div>
                            <div class="h-3 bg-gray-200 rounded w-16 mt-3"></div>
                        </div>
                    </div>
                    @endfor
                </div>

                @if($hasMore)
                    {{-- IntersectionObserver sentinel — wire:key forces Alpine re-init on each loadMore --}}
                    <div
                        wire:key="sentinel-{{ $perPage }}"
                        x-data
                        x-init="
                            if (!('IntersectionObserver' in window)) return;
                            let el = $el;
                            setTimeout(function() {
                                let obs = new IntersectionObserver(function(entries) {
                                    if (!entries[0].isIntersecting) return;
                                    obs.disconnect();
                                    $wire.loadMore();
                                }, { rootMargin: '400px 0px' });
                                obs.observe(el);
                            }, 200);
                        "
                        class="h-4 mt-8"
                        aria-hidden="true"
                    ></div>
                    {{-- Fallback button for browsers without IntersectionObserver --}}
                    <div x-data="{ hio: 'IntersectionObserver' in window }" x-show="!hio" x-cloak class="text-center mt-6">
                        <button
                            wire:click="loadMore"
                            wire:loading.attr="disabled"
                            wire:target="loadMore"
                            class="bg-black text-white px-8 py-3 rounded-xl font-semibold hover:bg-gray-800 transition-colors text-sm disabled:opacity-50"
                        >
                            <span wire:loading.remove wire:target="loadMore">Load More</span>
                            <span wire:loading wire:target="loadMore">Loading...</span>
                        </button>
                    </div>
                @else
                    <div class="text-center mt-10 py-6 border-t border-gray-100">
                        <p class="text-sm text-gray-400">You've seen all {{ $total }} {{ Str::plural('item', $total) }}</p>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
