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
        <a href="{{ route('admin.products.create') }}" class="bg-[#FFC300] text-black px-4 py-2 rounded-lg text-sm font-semibold hover:bg-[#FFD633] whitespace-nowrap">+ Add Product</a>
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
                            <img src="{{ Storage::url($product->thumbnail) }}" class="w-10 h-10 rounded-xl object-cover flex-shrink-0" alt="{{ $product->title }}">
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
                        <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="inline" onsubmit="return confirm('Delete this product?')">
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
