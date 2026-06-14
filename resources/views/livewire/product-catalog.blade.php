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

                {{-- Categories --}}
                <div class="mb-6">
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Categories</h4>
                    <div class="space-y-1">
                        <button wire:click="setCategory('')" @click="filtersOpen = false" class="block w-full text-left text-sm px-3 py-2 rounded-lg transition-colors {{ $category === '' ? 'bg-[#FFC300]/10 text-[#CC9900] font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-black' }}">All Categories</button>
                        @foreach($categories as $cat)
                        <button wire:click="setCategory('{{ $cat->slug }}')" @click="filtersOpen = false" class="block w-full text-left text-sm px-3 py-2 rounded-lg transition-colors {{ $category === $cat->slug ? 'bg-[#FFC300]/10 text-[#CC9900] font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-black' }}">{{ $cat->name }}</button>
                        @endforeach
                    </div>
                </div>

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

                {{-- Categories --}}
                <div class="mb-6">
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Categories</h4>
                    <div class="space-y-1">
                        <button wire:click="setCategory('')" class="block w-full text-left text-sm px-3 py-2 rounded-lg transition-colors {{ $category === '' ? 'bg-[#FFC300]/10 text-[#CC9900] font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-black' }}">All Categories</button>
                        @foreach($categories as $cat)
                        <button wire:click="setCategory('{{ $cat->slug }}')" class="block w-full text-left text-sm px-3 py-2 rounded-lg transition-colors {{ $category === $cat->slug ? 'bg-[#FFC300]/10 text-[#CC9900] font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-black' }}">{{ $cat->name }}</button>
                        @endforeach
                    </div>
                </div>

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
                    <div
                        wire:key="product-{{ $product->id }}"
                        class="group bg-white rounded-2xl overflow-hidden border border-gray-200 hover:border-[#FFC300] hover:shadow-xl transition-all duration-300"
                    >
                        <a href="{{ route('products.show', $product) }}" wire:navigate>
                            <div class="aspect-[4/3] bg-gradient-to-br from-gray-100 to-gray-200 relative overflow-hidden">
                                @php
                                    $cardMedia = match(true) {
                                        (bool) $product->thumbnail && $product->thumbnail_is_video   => 'thumb-video',
                                        (bool) $product->thumbnail                                   => 'thumb-image',
                                        (bool) $product->preview_image && $product->preview_is_video => 'preview-video',
                                        (bool) $product->preview_image                               => 'preview-image',
                                        default                                                       => 'placeholder',
                                    };
                                @endphp
                                @switch($cardMedia)
                                    @case('thumb-video')
                                        <video src="{{ $product->thumbnail_url }}" class="w-full h-full object-cover" muted loop playsinline preload="metadata" onmouseenter="this.play()" onmouseleave="this.pause();this.currentTime=0"></video>
                                        @break
                                    @case('thumb-image')
                                        <img src="{{ $product->thumbnail_url }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="{{ $product->title }}" loading="lazy">
                                        @break
                                    @case('preview-video')
                                        <video src="{{ $product->preview_image_url }}" class="w-full h-full object-cover" muted loop playsinline preload="metadata" onmouseenter="this.play()" onmouseleave="this.pause();this.currentTime=0"></video>
                                        @break
                                    @case('preview-image')
                                        <img src="{{ $product->preview_image_url }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="{{ $product->title }}" loading="lazy">
                                        @break
                                    @default
                                        <div class="absolute inset-0 flex items-center justify-center opacity-30 grayscale">
                                            <img src="/templatr-logo.svg" class="w-20 h-auto" alt="Templatr" loading="lazy">
                                        </div>
                                @endswitch
                                @if($product->sale_price)
                                    <div class="absolute top-3 left-3 bg-red-500 text-white text-xs font-bold px-2.5 py-1 rounded-lg">SALE</div>
                                @endif
                                <span class="absolute top-3 right-3 bg-black/60 text-white text-[11px] px-2.5 py-1 rounded-lg backdrop-blur-sm font-medium">{{ ucfirst($product->file_type) }}</span>
                            </div>
                        </a>
                        <div class="p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[11px] text-gray-500 font-medium">{{ $product->category->name }}</span>
                                <div class="flex items-center">
                                    <svg class="w-3.5 h-3.5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    <span class="text-xs text-gray-500 ml-1">{{ number_format($product->average_rating, 1) }}</span>
                                </div>
                            </div>
                            <a href="{{ route('products.show', $product) }}" wire:navigate>
                                <h3 class="font-semibold text-gray-900 group-hover:text-[#FFC300] transition-colors line-clamp-1 text-sm">{{ $product->title }}</h3>
                            </a>
                            <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100">
                                <span class="text-[11px] text-gray-500 truncate max-w-[100px]">{{ $product->author->name ?? '—' }}</span>
                                <div class="text-right">
                                    @if($product->sale_price)
                                        <span class="text-[11px] text-gray-400 line-through">{{ $product->formatted_original_price }}</span>
                                    @endif
                                    <span class="font-bold text-sm">{{ $product->formatted_price }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
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
