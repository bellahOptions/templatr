@extends('admin.layouts.admin')

@section('title', 'Edit Product - Templatr')
@section('header', 'Edit Product')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white border border-gray-200 rounded-2xl p-8">
        <form id="product-form" method="POST" action="{{ route('admin.products.update', $product) }}" class="space-y-6" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Pre-upload hidden fields --}}
            <input type="hidden" name="cloudinary_thumbnail_url" id="cloudinary_thumbnail_url">
            <input type="hidden" name="cloudinary_preview_url" id="cloudinary_preview_url">
            <input type="hidden" name="file_temp_id" id="file_temp_id">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input type="text" name="title" value="{{ old('title', $product->title) }}" required class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">
                    @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Category
                        <span class="text-xs font-normal text-gray-400 ml-1">— or create new</span>
                    </label>
                    @livewire('category-select-or-create', ['selected' => old('category_id', $product->category_id)])
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
                    <input type="number" step="1" name="price" value="{{ old('price', $product->price) }}" required class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">
                    @error('price')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sale Price ($)</label>
                    <input type="number" step="1" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}" class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">
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
                <div id="description-editor" class="rounded-xl border border-gray-300" style="min-height:180px"></div>
                <textarea name="description" id="description-textarea" class="hidden" required>{{ old('description', $product->description) }}</textarea>
                @error('description')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Image uploads --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                {{-- Thumbnail --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Thumbnail Image</label>

                    @if($product->thumbnail)
                    <div class="mb-3 relative group" id="existing-thumbnail">
                        <img src="{{ $product->thumbnail_url }}" class="w-full h-40 object-cover rounded-lg" alt="Current thumbnail">
                        <label class="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded-lg cursor-pointer hover:bg-red-600 transition-colors">
                            <input type="checkbox" name="remove_thumbnail" value="1" class="hidden">
                            <span class="flex items-center space-x-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                <span>Remove</span>
                            </span>
                        </label>
                    </div>
                    @endif

                    <div id="thumbnail-drop" class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-[#FFC300] transition-colors cursor-pointer" onclick="document.getElementById('thumbnail-input').click()">
                        <div id="thumbnail-preview" class="hidden mb-3">
                            <img id="thumbnail-preview-img" class="w-full h-40 object-cover rounded-lg" alt="New thumbnail preview">
                        </div>
                        <div id="thumbnail-placeholder">
                            <svg class="w-10 h-10 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-sm text-gray-500">Replace thumbnail</p>
                            <p class="text-xs text-gray-400 mt-1">JPG, PNG, WebP. Max 2MB.<br>Recommended: 600×450px</p>
                        </div>
                        <div id="thumbnail-uploading" class="hidden">
                            <div class="flex items-center justify-center space-x-2 mb-3">
                                <svg class="w-5 h-5 text-[#FFC300] animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span id="thumbnail-upload-label" class="text-sm font-medium text-gray-600">Uploading to Cloudinary…</span>
                            </div>
                            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div id="thumbnail-upload-bar" class="h-full bg-[#FFC300] rounded-full transition-all duration-300" style="width:0%"></div>
                            </div>
                            <p id="thumbnail-upload-pct" class="text-xs text-[#FFC300] font-semibold text-right mt-1">0%</p>
                        </div>
                    </div>
                    <input type="file" name="thumbnail" id="thumbnail-input" accept="image/jpeg,image/png,image/webp" class="hidden">
                    @error('thumbnail')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Preview Image --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Preview Image</label>

                    @if($product->preview_image)
                    <div class="mb-3 relative group" id="existing-preview">
                        <img src="{{ $product->preview_image_url }}" class="w-full h-40 object-cover rounded-lg" alt="Current preview">
                        <label class="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded-lg cursor-pointer hover:bg-red-600 transition-colors">
                            <input type="checkbox" name="remove_preview" value="1" class="hidden">
                            <span class="flex items-center space-x-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                <span>Remove</span>
                            </span>
                        </label>
                    </div>
                    @endif

                    <div id="preview-drop" class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-[#FFC300] transition-colors cursor-pointer" onclick="document.getElementById('preview-input').click()">
                        <div id="preview-preview" class="hidden mb-3">
                            <img id="preview-preview-img" class="w-full h-40 object-cover rounded-lg" alt="New preview">
                        </div>
                        <div id="preview-placeholder">
                            <svg class="w-10 h-10 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-sm text-gray-500">Replace preview</p>
                            <p class="text-xs text-gray-400 mt-1">JPG, PNG, WebP. Max 5MB.<br>Recommended: 1200×900px</p>
                        </div>
                        <div id="preview-uploading" class="hidden">
                            <div class="flex items-center justify-center space-x-2 mb-3">
                                <svg class="w-5 h-5 text-[#FFC300] animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span id="preview-upload-label" class="text-sm font-medium text-gray-600">Uploading to Cloudinary…</span>
                            </div>
                            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div id="preview-upload-bar" class="h-full bg-[#FFC300] rounded-full transition-all duration-300" style="width:0%"></div>
                            </div>
                            <p id="preview-upload-pct" class="text-xs text-[#FFC300] font-semibold text-right mt-1">0%</p>
                        </div>
                    </div>
                    <input type="file" name="preview_image" id="preview-input" accept="image/jpeg,image/png,image/webp" class="hidden">
                    @error('preview_image')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Product File --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Product File (ZIP, PSD, etc.)</label>

                @if($product->file_path)
                <div class="mb-3 flex items-center justify-between bg-gray-50 rounded-xl p-4" id="existing-file">
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

                <div id="file-drop-zone" class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-[#FFC300] transition-colors cursor-pointer" onclick="document.getElementById('file-input').click()">
                    <div id="file-info" class="hidden">
                        <div class="flex items-center justify-center space-x-3">
                            <svg class="w-8 h-8 text-[#FFC300]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <div id="file-details" class="text-left">
                                <p id="file-name" class="text-sm font-medium text-gray-700"></p>
                                <p id="file-size-display" class="text-xs text-gray-500"></p>
                            </div>
                            <button type="button" onclick="event.stopPropagation();clearFileUpload()" class="text-red-500 hover:text-red-700 p-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                    <div id="file-placeholder">
                        <svg class="w-10 h-10 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <p class="text-sm text-gray-500">Upload new file</p>
                        <p class="text-xs text-gray-400 mt-1">ZIP, RAR, PSD, AI, SVG, MP3, WAV, MP4, TTF, OTF. Max 100MB.</p>
                    </div>
                    <div id="file-uploading" class="hidden">
                        <div class="flex items-center justify-center space-x-2 mb-3">
                            <svg class="w-5 h-5 text-[#FFC300] animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span id="file-upload-label" class="text-sm font-medium text-gray-600">Uploading file…</span>
                        </div>
                        <div class="h-2.5 bg-gray-100 rounded-full overflow-hidden">
                            <div id="file-upload-bar" class="h-full bg-[#FFC300] rounded-full transition-all duration-200" style="width:0%"></div>
                        </div>
                        <p id="file-upload-pct" class="text-xs text-[#FFC300] font-semibold text-right mt-1">0%</p>
                    </div>
                </div>
                <input type="file" id="file-input" accept=".zip,.rar,.tar,.gz,.psd,.ai,.svg,.mp3,.wav,.mp4,.ttf,.otf" class="hidden">
                <p id="file-required-hint" class="text-xs text-amber-600 mt-1 hidden">Please wait for the file to finish uploading before submitting.</p>
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
                <label class="block text-sm font-medium text-gray-700 mb-1">Features</label>
                <textarea name="features" rows="6" class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]" placeholder="Enter one feature per line:&#10;Fully responsive design&#10;RTL language support&#10;One-click installation&#10;Lifetime updates">{{ old('features', $product->features ? implode("\n", $product->features) : '') }}</textarea>
                <p class="text-xs text-gray-400 mt-1">One feature per line. Displayed as a bullet list on the product page.</p>
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
                {{-- Edit: always enabled (no mandatory file upload) --}}
                <button id="submit-btn" type="submit" class="px-6 py-3 bg-black text-white rounded-xl text-sm font-semibold hover:bg-gray-800">
                    <span id="submit-btn-text">Update Product</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Form submit progress overlay --}}
<div id="progress-overlay" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-[999] flex items-center justify-center p-4">
    <div class="absolute top-0 left-0 right-0 h-1">
        <div id="top-bar" class="h-full bg-[#FFC300] transition-all duration-500" style="width:0%;box-shadow:0 0 10px #FFC300"></div>
    </div>
    <div class="bg-white rounded-2xl p-8 w-full max-w-sm shadow-2xl">
        <div class="flex justify-center mb-5">
            <div class="w-16 h-16 bg-[#FFC300]/10 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-[#FFC300] animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>
        <h3 class="text-xl font-bold text-center mb-1">Updating Product</h3>
        <p class="text-sm text-gray-500 text-center mb-5">Please wait — do not close this window</p>
        <div class="h-2.5 bg-gray-100 rounded-full overflow-hidden mb-1">
            <div id="modal-bar" class="h-full bg-[#FFC300] rounded-full transition-all duration-500" style="width:0%"></div>
        </div>
        <p id="modal-pct" class="text-xs font-semibold text-[#FFC300] text-right mb-5">0%</p>
        <div id="steps-list" class="space-y-3"></div>
    </div>
</div>

<script>
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024, sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content
        || document.querySelector('input[name="_token"]')?.value
        || '';
}

// ─── Track in-progress uploads so submit can be blocked ──────────────────────
let activeUploads = 0;

function uploadStarted() {
    activeUploads++;
    document.getElementById('submit-btn').disabled = true;
    document.getElementById('submit-btn').className = 'px-6 py-3 bg-gray-300 text-gray-500 rounded-xl text-sm font-semibold cursor-not-allowed transition-all duration-300';
    document.getElementById('submit-btn').title = 'Wait for upload to finish';
}

function uploadFinished() {
    activeUploads = Math.max(0, activeUploads - 1);
    if (activeUploads === 0) {
        document.getElementById('submit-btn').disabled = false;
        document.getElementById('submit-btn').className = 'px-6 py-3 bg-black text-white rounded-xl text-sm font-semibold hover:bg-gray-800 transition-all duration-300';
        document.getElementById('submit-btn').title = '';
    }
}

// ─── Image upload to Cloudinary ───────────────────────────────────────────────
function uploadImage(inputEl, type) {
    const file = inputEl.files[0];
    if (!file) return;

    const isThumb = type === 'thumbnail';
    const prefix  = isThumb ? 'thumbnail' : 'preview';

    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById(prefix + '-preview-img').src = e.target.result;
    };
    reader.readAsDataURL(file);

    document.getElementById(prefix + '-placeholder').classList.add('hidden');
    document.getElementById(prefix + '-preview').classList.add('hidden');
    document.getElementById(prefix + '-uploading').classList.remove('hidden');

    const bar   = document.getElementById(prefix + '-upload-bar');
    const pct   = document.getElementById(prefix + '-upload-pct');
    const label = document.getElementById(prefix + '-upload-label');

    uploadStarted();

    const xhr  = new XMLHttpRequest();
    const data = new FormData();
    data.append('image', file);
    data.append('type', type);
    data.append('_token', csrfToken());

    xhr.upload.addEventListener('progress', e => {
        if (!e.lengthComputable) return;
        const p = Math.round((e.loaded / e.total) * 80);
        bar.style.width = p + '%';
        pct.textContent = p + '%';
    });

    xhr.addEventListener('load', () => {
        if (xhr.status === 200) {
            const res = JSON.parse(xhr.responseText);
            bar.style.width = '100%';
            pct.textContent = '100%';
            label.textContent = 'Uploaded ✓';

            const hiddenKey = isThumb ? 'cloudinary_thumbnail_url' : 'cloudinary_preview_url';
            document.getElementById(hiddenKey).value = res.url;
            inputEl.value = '';

            setTimeout(() => {
                document.getElementById(prefix + '-uploading').classList.add('hidden');
                document.getElementById(prefix + '-preview').classList.remove('hidden');
                uploadFinished();
            }, 600);
        } else {
            uploadImageError(prefix, inputEl);
        }
    });

    xhr.addEventListener('error', () => uploadImageError(prefix, inputEl));

    xhr.open('POST', '{{ route('admin.uploads.image') }}');
    xhr.send(data);
}

function uploadImageError(prefix, inputEl) {
    inputEl.value = '';
    document.getElementById(prefix + '-uploading').classList.add('hidden');
    document.getElementById(prefix + '-placeholder').classList.remove('hidden');
    uploadFinished();
    alert('Image upload failed. Please try again.');
}

document.getElementById('thumbnail-input').addEventListener('change', function () {
    uploadImage(this, 'thumbnail');
});
document.getElementById('preview-input').addEventListener('change', function () {
    uploadImage(this, 'preview');
});

// ─── Product file upload ──────────────────────────────────────────────────────
function clearFileUpload() {
    document.getElementById('file-input').value = '';
    document.getElementById('file_temp_id').value = '';
    document.getElementById('file-info').classList.add('hidden');
    document.getElementById('file-uploading').classList.add('hidden');
    document.getElementById('file-placeholder').classList.remove('hidden');
    document.getElementById('file-required-hint').classList.add('hidden');
}

document.getElementById('file-input').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;

    document.getElementById('file_temp_id').value = '';
    document.getElementById('file-placeholder').classList.add('hidden');
    document.getElementById('file-info').classList.add('hidden');
    document.getElementById('file-uploading').classList.remove('hidden');
    document.getElementById('file-required-hint').classList.remove('hidden');

    const bar   = document.getElementById('file-upload-bar');
    const pct   = document.getElementById('file-upload-pct');
    const label = document.getElementById('file-upload-label');

    label.textContent = 'Uploading ' + file.name + '…';
    uploadStarted();

    const xhr  = new XMLHttpRequest();
    const data = new FormData();
    data.append('file', file);
    data.append('_token', csrfToken());

    xhr.upload.addEventListener('progress', e => {
        if (!e.lengthComputable) return;
        const p = Math.round((e.loaded / e.total) * 95);
        bar.style.width = p + '%';
        pct.textContent = p + '%';
    });

    xhr.addEventListener('load', () => {
        if (xhr.status === 200) {
            const res = JSON.parse(xhr.responseText);
            bar.style.width = '100%';
            pct.textContent = '100%';
            document.getElementById('file_temp_id').value = res.temp_id;
            this.value = '';

            setTimeout(() => {
                document.getElementById('file-uploading').classList.add('hidden');
                document.getElementById('file-required-hint').classList.add('hidden');
                document.getElementById('file-name').textContent = res.name;
                document.getElementById('file-size-display').textContent = formatFileSize(res.size);
                document.getElementById('file-info').classList.remove('hidden');
                uploadFinished();
            }, 400);
        } else {
            fileUploadError(this);
        }
    });

    xhr.addEventListener('error', () => fileUploadError(this));

    xhr.open('POST', '{{ route('admin.uploads.file') }}');
    xhr.send(data);
});

function fileUploadError(inputEl) {
    inputEl.value = '';
    document.getElementById('file-uploading').classList.add('hidden');
    document.getElementById('file-placeholder').classList.remove('hidden');
    document.getElementById('file-required-hint').classList.add('hidden');
    uploadFinished();
    alert('File upload failed. Please try again.');
}

// ─── Form submit progress overlay ────────────────────────────────────────────
(function () {
    const STEPS = [
        { label: 'Validating changes',  target: 20, ms: 700  },
        { label: 'Saving product data', target: 65, ms: 1200 },
        { label: 'Finalising',          target: 90, ms: 800  },
    ];

    const overlay   = document.getElementById('progress-overlay');
    const topBar    = document.getElementById('top-bar');
    const modalBar  = document.getElementById('modal-bar');
    const pctLabel  = document.getElementById('modal-pct');
    const stepsList = document.getElementById('steps-list');

    STEPS.forEach((s, i) => {
        const d = document.createElement('div');
        d.id = 'step-' + i;
        d.className = 'flex items-center gap-3 opacity-30 transition-all duration-300';
        d.innerHTML = `<div class="w-6 h-6 rounded-full bg-gray-100 flex-shrink-0 flex items-center justify-center step-icon">
            <span class="text-xs font-bold text-gray-400">${i + 1}</span></div>
            <span class="text-sm text-gray-600">${s.label}</span>`;
        stepsList.appendChild(d);
    });

    let pct = 0;

    function setPct(p) {
        pct = Math.min(Math.max(p, 0), 99);
        topBar.style.width = pct + '%';
        modalBar.style.width = pct + '%';
        pctLabel.textContent = Math.round(pct) + '%';
    }

    function setStep(i, state) {
        const el   = document.getElementById('step-' + i);
        if (!el) return;
        el.classList.remove('opacity-30');
        const icon = el.querySelector('.step-icon');
        if (state === 'done') {
            icon.className = 'w-6 h-6 rounded-full bg-green-500 flex-shrink-0 flex items-center justify-center step-icon';
            icon.innerHTML = `<svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>`;
        } else {
            icon.className = 'w-6 h-6 rounded-full bg-[#FFC300] flex-shrink-0 flex items-center justify-center step-icon';
            icon.innerHTML = `<span class="text-xs font-bold text-black">${i + 1}</span>`;
        }
    }

    function animateTo(from, to, dur, cb) {
        const t0 = performance.now();
        (function tick(now) {
            const t = Math.min((now - t0) / dur, 1);
            setPct(from + (to - from) * (1 - Math.pow(1 - t, 3)));
            t < 1 ? requestAnimationFrame(tick) : cb && cb();
        })(performance.now());
    }

    function runSteps() {
        let idx = 0;
        function next() {
            if (idx >= STEPS.length) return;
            setStep(idx, 'active');
            animateTo(pct, STEPS[idx].target, STEPS[idx].ms, function () {
                setStep(idx, 'done');
                idx++;
                setTimeout(next, 150);
            });
        }
        next();
    }

    document.getElementById('product-form').addEventListener('submit', function (e) {
        if (activeUploads > 0) {
            e.preventDefault();
            document.getElementById('file-required-hint').classList.remove('hidden');
            return;
        }
        document.getElementById('description-textarea').value = window._descQuill ? window._descQuill.root.innerHTML : '';
        overlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        setPct(5);
        setTimeout(runSteps, 200);
    });
})();
</script>
@endsection

@push('styles')
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<style>
    #description-editor .ql-editor { min-height: 160px; font-size: 14px; font-family: inherit; }
    #description-editor .ql-toolbar.ql-snow { border-top-left-radius: 0.75rem; border-top-right-radius: 0.75rem; background: #f9fafb; }
    #description-editor .ql-container.ql-snow { border-bottom-left-radius: 0.75rem; border-bottom-right-radius: 0.75rem; }
    #description-editor { border-radius: 0.75rem; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
(function () {
    var q = new Quill('#description-editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ header: [1, 2, 3, false] }],
                ['bold', 'italic', 'underline'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['link', 'clean']
            ]
        },
        placeholder: 'Describe your product…'
    });
    var existing = document.getElementById('description-textarea').value;
    if (existing) { q.root.innerHTML = existing; }
    window._descQuill = q;
})();
</script>
@endpush
