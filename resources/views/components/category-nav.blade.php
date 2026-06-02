@php
    $categories = \App\Models\Category::orderBy('order')->get();
@endphp

@if($categories->isNotEmpty())
<div class="bg-white border-b border-gray-200 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center space-x-1 overflow-x-auto scrollbar-hide py-2">
            <a href="{{ route('products.index') }}"
               class="flex-shrink-0 px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors whitespace-nowrap
               {{ request()->routeIs('products.index') && !request('category') ? 'bg-[#FFC300] text-black' : 'text-gray-600 hover:bg-gray-100 hover:text-black' }}">
                All Items
            </a>
            @foreach($categories as $cat)
            <a href="{{ route('products.index', ['category' => $cat->slug]) }}"
               class="flex-shrink-0 px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors whitespace-nowrap
               {{ request('category') === $cat->slug ? 'bg-[#FFC300] text-black' : 'text-gray-600 hover:bg-gray-100 hover:text-black' }}">
                {{ $cat->name }}
            </a>
            @endforeach
        </div>
    </div>
</div>
@endif
