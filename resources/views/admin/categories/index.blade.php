@extends('admin.layouts.admin')

@section('title', 'Categories - Templatr')
@section('header', 'Categories')

@section('content')
<div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
    <div class="p-6 border-b border-gray-200 flex justify-between items-center">
        <p class="text-sm text-gray-500">{{ $categories->count() }} categories</p>
        <a href="{{ route('admin.categories.create') }}" class="bg-[#FFC300] text-black px-4 py-2 rounded-lg text-sm font-semibold hover:bg-[#FFD633]">+ Add Category</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Slug</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Products</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Order</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($categories as $category)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <span class="font-semibold text-sm">{{ $category->name }}</span>
                        @if($category->description)
                        <p class="text-xs text-gray-500">{{ $category->description }}</p>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $category->slug }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $category->products_count }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $category->order }}</td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('admin.categories.edit', $category) }}" class="text-sm text-[#FFC300] hover:text-black font-semibold mr-3">Edit</a>
                        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="inline" onsubmit="return confirm('Delete this category?')">
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
</div>
@endsection

@push('fab')
<a href="{{ route('admin.categories.create') }}"
   title="Add New Category"
   class="fixed bottom-6 right-6 z-50 w-14 h-14 bg-[#FFC300] text-black rounded-full shadow-lg hover:bg-[#FFD633] hover:shadow-xl transition-all flex items-center justify-center group">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
    </svg>
    <span class="absolute right-16 bg-black text-white text-xs font-semibold px-3 py-1.5 rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
        New Category
    </span>
</a>
@endpush
