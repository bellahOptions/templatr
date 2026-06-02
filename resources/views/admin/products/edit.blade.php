@extends('admin.layouts.admin')

@section('title', 'Edit Product - CreativeMarket')
@section('header', 'Edit Product')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white border border-gray-200 rounded-2xl p-8">
        <form method="POST" action="{{ route('admin.products.update', $product) }}" class="space-y-6" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input type="text" name="title" value="{{ old('title', $product->title) }}" required class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">
                    @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select name="category_id" required class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Author</label>
                    <select name="user_id" required class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">
                        @foreach($authors as $author)
                        <option value="{{ $author->id }}" {{ old('user_id', $product->user_id) == $author->id ? 'selected' : '' }}>{{ $author->name }}</option>
                        @endforeach
                    </select>
                    @error('user_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">File Type</label>
                    <select name="file_type" required class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">
                        @foreach($types as $key => $type)
                        <option value="{{ $key }}" {{ old('file_type', $product->file_type) == $key ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                    @error('file_type')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Price ($)</label>
                    <input type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}" required class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">
                    @error('price')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sale Price ($)</label>
                    <input type="number" step="0.01" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}" class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">
                    @error('sale_price')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
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
                @error('description')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Secure Image Uploads --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Thumbnail Image</label>

                    {{-- Existing thumbnail display --}}
                    @if($product->thumbnail)
                    <div class="mb-3 relative group">
                        <img src="{{ Storage::url($product->thumbnail) }}" class="w-full h-40 object-cover rounded-lg" alt="Current thumbnail">
                        <label class="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded-lg cursor-pointer hover:bg-red-600 transition-colors">
                            <input type="checkbox" name="remove_thumbnail" value="1" class="hidden">
                            <span class="flex items-center space-x-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                <span>Remove</span>
                            </span>
                        </label>
                    </div>
                    @endif

                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-[#FFC300] transition-colors cursor-pointer" onclick="document.getElementById('edit-thumbnail-input').click()">
                        <div id="edit-thumbnail-preview" class="hidden mb-3">
                            <img id="edit-thumbnail-preview-img" class="w-full h-40 object-cover rounded-lg" alt="New thumbnail preview">
                        </div>
                        <div id="edit-thumbnail-placeholder">
                            <svg class="w-10 h-10 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-sm text-gray-500">Replace thumbnail</p>
                            <p class="text-xs text-gray-400 mt-1">JPG, PNG, WebP. Max 2MB.<br>Recommended: 600×450px</p>
                        </div>
                    </div>
                    <input type="file" name="thumbnail" id="edit-thumbnail-input" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="previewImage(this, 'edit-thumbnail-preview', 'edit-thumbnail-placeholder', 'edit-thumbnail-preview-img')">
                    @error('thumbnail')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Preview Image</label>

                    @if($product->preview_image)
                    <div class="mb-3 relative group">
                        <img src="{{ Storage::url($product->preview_image) }}" class="w-full h-40 object-cover rounded-lg" alt="Current preview">
                        <label class="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded-lg cursor-pointer hover:bg-red-600 transition-colors">
                            <input type="checkbox" name="remove_preview" value="1" class="hidden">
                            <span class="flex items-center space-x-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                <span>Remove</span>
                            </span>
                        </label>
                    </div>
                    @endif

                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-[#FFC300] transition-colors cursor-pointer" onclick="document.getElementById('edit-preview-input').click()">
                        <div id="edit-preview-preview" class="hidden mb-3">
                            <img id="edit-preview-preview-img" class="w-full h-40 object-cover rounded-lg" alt="New preview">
                        </div>
                        <div id="edit-preview-placeholder">
                            <svg class="w-10 h-10 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-sm text-gray-500">Replace preview</p>
                            <p class="text-xs text-gray-400 mt-1">JPG, PNG, WebP. Max 5MB.<br>Recommended: 1200×900px</p>
                        </div>
                    </div>
                    <input type="file" name="preview_image" id="edit-preview-input" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="previewImage(this, 'edit-preview-preview', 'edit-preview-placeholder', 'edit-preview-preview-img')">
                    @error('preview_image')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Secure File Upload --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Product File (ZIP, PSD, etc.)</label>

                @if($product->file_path)
                <div class="mb-3 flex items-center justify-between bg-gray-50 rounded-xl p-4">
                    <div class="flex items-center space-x-3">
                        <svg class="w-8 h-8 text-[#FFC300]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-700">{{ basename($product->file_path) }}</p>
                            @if($product->file_size)
                            <p class="text-xs text-gray-500">{{ number_format($product->file_size / 1024, 1) }} KB</p>
                            @endif
                        </div>
                    </div>
                    <label class="bg-red-500 text-white text-xs px-3 py-2 rounded-lg cursor-pointer hover:bg-red-600 transition-colors flex items-center space-x-1">
                        <input type="checkbox" name="remove_file" value="1" class="hidden">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>Remove</span>
                    </label>
                </div>
                @endif

                <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-[#FFC300] transition-colors cursor-pointer" onclick="document.getElementById('edit-file-input').click()">
                    <div id="edit-file-info" class="hidden">
                        <div class="flex items-center justify-center space-x-3">
                            <svg class="w-8 h-8 text-[#FFC300]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <div id="edit-file-details" class="text-left">
                                <p id="edit-file-name" class="text-sm font-medium text-gray-700"></p>
                                <p id="edit-file-size-display" class="text-xs text-gray-500"></p>
                            </div>
                            <button type="button" onclick="event.stopPropagation();clearFile('edit-file-input', 'edit-file-info', 'edit-file-placeholder')" class="text-red-500 hover:text-red-700 p-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                    <div id="edit-file-placeholder">
                        <svg class="w-10 h-10 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <p class="text-sm text-gray-500">Upload new file</p>
                        <p class="text-xs text-gray-400 mt-1">ZIP, RAR, PSD, AI, SVG, MP3, WAV, MP4, TTF, OTF. Max 100MB.</p>
                    </div>
                </div>
                <input type="file" name="file_path" id="edit-file-input" accept=".zip,.rar,.tar,.gz,.psd,.ai,.svg,.mp3,.wav,.mp4,.ttf,.otf" class="hidden" onchange="handleFileSelect(this, 'edit-file-info', 'edit-file-placeholder', 'edit-file-name', 'edit-file-size-display')">
                @error('file_path')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tags (comma separated)</label>
                <input type="text" name="tags" value="{{ old('tags', is_array($product->tags) ? implode(',', $product->tags) : ($product->tags ?? '')) }}" class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">
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

<script>
function previewImage(input, previewId, placeholderId, imgId) {
    const preview = document.getElementById(previewId);
    const placeholder = document.getElementById(placeholderId);
    const img = document.getElementById(imgId);

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            img.src = e.target.result;
            preview.classList.remove('hidden');
            placeholder.classList.add('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function handleFileSelect(input, infoId, placeholderId, nameId, sizeId) {
    const info = document.getElementById(infoId);
    const placeholder = document.getElementById(placeholderId);
    const nameEl = document.getElementById(nameId);
    const sizeEl = document.getElementById(sizeId);

    if (input.files && input.files[0]) {
        const file = input.files[0];
        nameEl.textContent = file.name;
        sizeEl.textContent = formatFileSize(file.size);
        info.classList.remove('hidden');
        placeholder.classList.add('hidden');
    }
}

function clearFile(inputId, infoId, placeholderId) {
    document.getElementById(inputId).value = '';
    document.getElementById(infoId).classList.add('hidden');
    document.getElementById(placeholderId).classList.remove('hidden');
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}
</script>
@endsection
