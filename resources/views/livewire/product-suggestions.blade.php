<div>
    @if(!empty($products))
        <section class="py-16 md:py-20 bg-gradient-to-b from-white to-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold">
                        Recommended 
                        <span class="text-[#FFC300]">For You</span>
                    </h2>
                    <p class="mt-4 text-gray-500 max-w-xl mx-auto">Based on your browsing history and preferences</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    @foreach($products as $product)
                        <div class="group bg-white rounded-2xl overflow-hidden border border-gray-200 hover:border-[#FFC300] hover:shadow-xl transition-all duration-300 animate-fade-in" wire:key="suggest-{{ $product['id'] }}">
                            <a href="{{ route('products.show', $product['id']) }}" wire:navigate>
                                <div class="aspect-[4/3] bg-gradient-to-br from-gray-100 to-gray-200 relative overflow-hidden">
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                        </svg>
                                    </div>
                                    @if(isset($product['sale_price']) && $product['sale_price'])
                                        <div class="absolute top-3 left-3 bg-red-500 text-white text-xs font-bold px-2.5 py-1 rounded-lg">SALE</div>
                                    @endif
                                </div>
                            </a>
                            <div class="p-4">
                                <span class="text-[11px] text-gray-500 font-medium">{{ $product['category']['name'] ?? '' }}</span>
                                <a href="{{ route('products.show', $product['id']) }}" wire:navigate>
                                    <h3 class="font-semibold text-gray-900 group-hover:text-[#FFC300] transition-colors line-clamp-1 text-sm mt-1">{{ $product['title'] }}</h3>
                                </a>
                                <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100">
                                    <span class="text-[11px] text-gray-500">{{ $product['author']['name'] ?? '' }}</span>
                                    <span class="font-bold text-sm">{{ \App\Helpers\CurrencyHelper::format($product['sale_price'] ?? $product['price']) }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</div>
