@extends('admin.layouts.admin')

@section('title', 'Edit Product - CreativeMarket')
@section('header', 'Edit Product')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white border border-gray-200 rounded-2xl p-8">
        <form method="POST" action="{{ route('admin.products.update', $product) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input type="text" name="title" value="{{ old('title', $product->title) }}" required class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select name="category_id" required class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Author</label>
                    <select name="user_id" required class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">
                        @foreach($authors as $author)
                        <option value="{{ $author->id }}" {{ old('user_id', $product->user_id) == $author->id ? 'selected' : '' }}>{{ $author->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">File Type</label>
                    <select name="file_type" required class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">
                        @foreach($types as $key => $type)
                        <option value="{{ $key }}" {{ old('file_type', $product->file_type) == $key ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Price ($)</label>
                    <input type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}" required class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sale Price ($)</label>
                    <input type="number" step="0.01" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}" class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Version</label>
                    <input type="text" name="version" value="{{ old('version', $product->version) }}" class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">File Size (bytes)</label>
                    <input type="number" name="file_size" value="{{ old('file_size', $product->file_size) }}" class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="5" required class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">{{ old('description', $product->description) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tags (comma separated)</label>
                <input type="text" name="tags" value="{{ old('tags', $product->tags ? implode(',', json_decode($product->tags, true) ?? []) : '') }}" class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Requirements</label>
                <input type="text" name="requirements" value="{{ old('requirements', $product->requirements) }}" class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Demo URL</label>
                <input type="url" name="demo_url" value="{{ old('demo_url', $product->demo_url) }}" class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">
            </div>

            <div class="flex items-center space-x-6">
                <label class="flex items-center">
                    <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }} class="rounded border-gray-300 text-[#FFC300] focus:ring-[#FFC300]">
                    <span class="ml-2 text-sm text-gray-700">Featured Product</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="is_published" value="1" {{ old('is_published', $product->is_published) ? 'checked' : '' }} class="rounded border-gray-300 text-[#FFC300] focus:ring-[#FFC300]">
                    <span class="ml-2 text-sm text-gray-700">Published</span>
                </label>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                <a href="{{ route('admin.products.index') }}" class="px-6 py-3 border border-gray-300 rounded-xl text-sm font-semibold hover:bg-gray-50">Cancel</a>
                <button type="submit" class="px-6 py-3 bg-black text-white rounded-xl text-sm font-semibold hover:bg-gray-800">Update Product</button>
            </div>
        </form>
    </div>
</div>
@endsection
