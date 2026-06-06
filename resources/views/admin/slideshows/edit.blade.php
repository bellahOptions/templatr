@extends('admin.layouts.admin')

@section('title', 'Edit Slide - Templatr')
@section('header', 'Edit Slide')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white border border-gray-200 rounded-2xl p-8">
        <form method="POST" action="{{ route('admin.slideshows.update', $slideshow) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Background Image</label>
                @if($slideshow->image_url)
                    <div class="mb-3 relative inline-block">
                        <img src="{{ $slideshow->image_url }}" alt="Current slide image" class="w-full max-w-md h-40 object-cover rounded-xl border border-gray-200">
                        <span class="absolute top-2 left-2 bg-black/60 text-white text-xs px-2 py-0.5 rounded-lg backdrop-blur-sm">Current image</span>
                    </div>
                @endif
                <input type="file" name="image" accept="image/jpeg,image/png,image/jpg,image/webp"
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300] file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#FFC300] file:text-black hover:file:bg-[#FFD633]">
                <p class="text-xs text-gray-400 mt-1">{{ $slideshow->image_url ? 'Upload a new image to replace the current one.' : 'Recommended: 1920×1080px, max 5MB (JPEG, PNG, WebP)' }}</p>
                @error('image')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', $slideshow->title) }}" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="3"
                          class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">{{ old('description', $slideshow->description) }}</textarea>
                @error('description')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CTA Button Text</label>
                    <input type="text" name="cta_text" value="{{ old('cta_text', $slideshow->cta_text) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]"
                           placeholder="e.g. Explore Now">
                    @error('cta_text')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CTA URL</label>
                    <input type="text" name="cta_url" value="{{ old('cta_url', $slideshow->cta_url) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]"
                           placeholder="/products">
                    @error('cta_url')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $slideshow->sort_order) }}" min="0"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">
                    <p class="text-xs text-gray-400 mt-1">Lower number appears first</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <label class="flex items-center gap-3 mt-3 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $slideshow->is_active) ? 'checked' : '' }}
                               class="w-5 h-5 rounded border-gray-300 text-[#FFC300] focus:ring-[#FFC300]">
                        <span class="text-sm font-medium text-gray-700">Active (visible on site)</span>
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                <a href="{{ route('admin.slideshows.index') }}" class="px-6 py-3 border border-gray-300 rounded-xl text-sm font-semibold hover:bg-gray-50">Cancel</a>
                <button type="submit" class="px-6 py-3 bg-black text-white rounded-xl text-sm font-semibold hover:bg-gray-800">Update Slide</button>
            </div>
        </form>
    </div>
</div>
@endsection
