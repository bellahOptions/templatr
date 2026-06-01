<div>
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
        @forelse($products as $product)
            <div class="group bg-white rounded-2xl overflow-hidden border border-gray-200 hover:border-[#FFC300] hover:shadow-xl transition-all duration-300 animate-fade-in" wire:key="{{ $product->id }}">
                <a href="{{ route('products.show', $product) }}" wire:navigate>
                    <div class="aspect-[4/3] bg-gradient-to-br from-gray-100 to-gray-200 relative overflow-hidden">
                        <div class="absolute inset-0 flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
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
                            <svg class="w-3.5 h-3.5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <span class="text-xs text-gray-500 ml-1">{{ number_format($product->average_rating, 1) }}</span>
                        </div>
                    </div>
                    <a href="{{ route('products.show', $product)" wire:navigate>
                        <h3 class="font-semibold text-gray-900 group-hover:text-[#FFC300] transition-colors line-clamp-1 text-sm">{{ $product->title }}</h3>
                    </a>
                    <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100">
                        <div class="flex items-center space-x-2">
                            <div class="w-6 h-6 bg-gray-200 rounded-full flex items-center justify-center">
                                <span class="text-[10px] font-bold">{{ substr($product->author->name, 0, 1) }}</span>
                            </div>
                            <span class="text-[11px] text-gray-500">{{ $product->author->name }}</span>
                        </div>
                        <div class="text-right">
                            @if($product->sale_price)
                                <span class="text-[11px] text-gray-400 line-through">${{ number_format($product->price, 2) }}</span>
                            @endif
                            <span class="font-bold text-sm">${{ number_format($product->sale_price ?? $product->price, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-16">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 mb-1">No items found</h3>
                <p class="text-gray-500 text-sm">Try adjusting your search or filter criteria</p>
                <button wire:click="$set('search', '')" class="inline-flex mt-4 text-sm text-[#FFC300] hover:text-black font-semibold">Clear all filters</button>
            </div>
        @endforelse
    </div>

    @if($products->hasPages())
        <div class="mt-10">
            {{ $products->onEachSide(1)->links() }}
        </div>
    @endif
</div>
