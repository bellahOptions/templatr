@extends('admin.layouts.admin')

@php use App\Helpers\CurrencyHelper; @endphp

@section('title', 'Manage Products - Templatr')
@section('header', 'Products')

@section('content')
<div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
    <div class="p-6 border-b border-gray-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <form method="GET" action="{{ route('admin.products.index') }}" class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products..." class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">
            <select name="category" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">
                <option value="">All Status</option>
                <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-black text-white rounded-lg text-sm font-semibold hover:bg-gray-800">Filter</button>
        </form>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.products.import') }}" class="border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-50 whitespace-nowrap">Bulk Import</a>
            <a href="{{ route('admin.products.create') }}" class="bg-[#FFC300] text-black px-4 py-2 rounded-lg text-sm font-semibold hover:bg-[#FFD633] whitespace-nowrap">+ Add Product</a>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Author</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Sales</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($products as $product)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-3">
                            @if($product->thumbnail)
                            <img src="{{ $product->thumbnail_url }}" class="w-10 h-10 rounded-xl object-cover flex-shrink-0" alt="{{ $product->title }}">
                            @else
                            <div class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                <span class="text-xs font-bold">{{ substr($product->title, 0, 2) }}</span>
                            </div>
                            @endif
                            <div class="min-w-0">
                                <p class="text-sm font-semibold truncate">{{ $product->title }}</p>
                                <p class="text-xs text-gray-500">{{ $product->file_type }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $product->category?->name ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $product->author?->name ?? '—' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($product->sale_price)
                            <span class="text-sm line-through text-gray-400">{{ CurrencyHelper::format($product->price) }}</span>
                            <span class="text-sm font-bold text-red-600">{{ CurrencyHelper::format($product->sale_price) }}</span>
                        @else
                            <span class="text-sm font-bold">{{ CurrencyHelper::format($product->price) }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ number_format($product->download_count) }}</td>
                    <td class="px-6 py-4">
                        @if($product->is_featured)
                        <span class="px-2 py-1 bg-[#FFC300]/20 text-[#CC9900] rounded-full text-xs font-semibold">Featured</span>
                        @endif
                        <span class="px-2 py-1 {{ $product->is_published ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }} rounded-full text-xs font-semibold">
                            {{ $product->is_published ? 'Published' : 'Draft' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right whitespace-nowrap">
                        <a href="{{ route('admin.products.edit', $product) }}" class="text-sm text-[#FFC300] hover:text-black font-semibold mr-3">Edit</a>
                        <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="inline" data-delete-form>
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-red-500 hover:text-red-700 font-semibold">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($products->isEmpty())
    <div class="text-center py-12">
        <p class="text-gray-500">No products found.</p>
    </div>
    @endif
    <div class="p-4 border-t border-gray-200">
        {{ $products->links() }}
    </div>
</div>
@endsection

{{-- Delete progress overlay --}}
<div id="delete-overlay" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-[999] flex items-center justify-center p-4">
    <div class="absolute top-0 left-0 right-0 h-1">
        <div id="delete-top-bar" class="h-full bg-red-500 transition-all duration-500" style="width:0%;box-shadow:0 0 10px #ef4444"></div>
    </div>
    <div class="bg-white rounded-2xl p-8 w-full max-w-xs shadow-2xl text-center">
        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-red-500 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
        <h3 class="text-lg font-bold mb-1">Deleting Product</h3>
        <p class="text-sm text-gray-500 mb-5">Removing files and records…</p>
        <div class="h-2.5 bg-gray-100 rounded-full overflow-hidden mb-1">
            <div id="delete-modal-bar" class="h-full bg-red-500 rounded-full transition-all duration-500" style="width:0%"></div>
        </div>
        <p id="delete-pct" class="text-xs font-semibold text-red-500 text-right mt-1">0%</p>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const overlay  = document.getElementById('delete-overlay');
    const topBar   = document.getElementById('delete-top-bar');
    const modalBar = document.getElementById('delete-modal-bar');
    const pctLabel = document.getElementById('delete-pct');

    function setPct(p) {
        topBar.style.width   = p + '%';
        modalBar.style.width = p + '%';
        pctLabel.textContent = Math.round(p) + '%';
    }

    function animateTo(from, to, dur, cb) {
        const t0 = performance.now();
        (function tick(now) {
            const t = Math.min((now - t0) / dur, 1);
            setPct(from + (to - from) * (1 - Math.pow(1 - t, 3)));
            t < 1 ? requestAnimationFrame(tick) : cb && cb();
        })(performance.now());
    }

    document.querySelectorAll('form[data-delete-form]').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!confirm('Delete this product? This cannot be undone.')) return;
            overlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            setPct(5);
            animateTo(5, 40, 500, function () {
                animateTo(40, 75, 600, function () {
                    animateTo(75, 92, 400, function () {
                        form.submit();
                    });
                });
            });
        });
    });
})();
</script>
@endpush

@push('fab')
<a href="{{ route('admin.products.create') }}"
   title="Add New Product"
   class="fixed bottom-6 right-6 z-50 w-14 h-14 bg-[#FFC300] text-black rounded-full shadow-lg hover:bg-[#FFD633] hover:shadow-xl transition-all flex items-center justify-center group">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
    </svg>
    <span class="absolute right-16 bg-black text-white text-xs font-semibold px-3 py-1.5 rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
        New Product
    </span>
</a>
@endpush
