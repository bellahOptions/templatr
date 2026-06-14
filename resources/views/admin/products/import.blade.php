@extends('admin.layouts.admin')

@section('title', 'Bulk Import Products - Templatr')
@section('header', 'Bulk Import Products')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Results panel --}}
    @if($importResults)
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-base font-bold">Import Results</h2>
            <div class="flex items-center gap-3 text-sm font-semibold">
                <span class="flex items-center gap-1.5 text-green-600">
                    <span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span>
                    {{ $importResults['created'] }} created
                </span>
                <span class="flex items-center gap-1.5 text-yellow-600">
                    <span class="w-2 h-2 rounded-full bg-yellow-400 inline-block"></span>
                    {{ $importResults['skipped'] }} skipped
                </span>
                <span class="flex items-center gap-1.5 text-red-500">
                    <span class="w-2 h-2 rounded-full bg-red-400 inline-block"></span>
                    {{ $importResults['failed'] }} failed
                </span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase w-14">Row</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Title</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase w-24">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Note</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($importResults['rows'] as $result)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-400 font-mono text-xs">{{ $result['row'] }}</td>
                        <td class="px-4 py-3 font-medium text-gray-800 truncate max-w-xs">{{ $result['title'] }}</td>
                        <td class="px-4 py-3">
                            @if($result['status'] === 'created')
                                <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">Created</span>
                            @elseif($result['status'] === 'skipped')
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-semibold">Skipped</span>
                            @else
                                <span class="px-2 py-1 bg-red-100 text-red-600 rounded-full text-xs font-semibold">Failed</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $result['reason'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-100 flex justify-end">
            <a href="{{ route('admin.products.index') }}" class="px-5 py-2.5 bg-black text-white rounded-xl text-sm font-semibold hover:bg-gray-800">
                View All Products
            </a>
        </div>
    </div>
    @endif

    {{-- Instructions --}}
    <div class="bg-[#FFC300]/10 border border-[#FFC300]/40 rounded-2xl p-6 space-y-3">
        <h3 class="font-bold text-sm text-gray-800">How bulk import works</h3>
        <ol class="text-sm text-gray-700 space-y-1.5 list-decimal list-inside">
            <li>
                <strong>Download the CSV template</strong> below, fill it in with your product data, and save as CSV.
            </li>
            <li>
                <strong>Upload product files</strong> via cPanel File Manager or FTP to:<br>
                <code class="text-xs bg-white border border-gray-200 px-2 py-0.5 rounded font-mono">storage/app/products/bulk/</code><br>
                Then reference each file by its filename (e.g. <code class="text-xs font-mono">my-kit.zip</code>) in the <code class="text-xs font-mono">file_name</code> column.
            </li>
            <li>
                <strong>Pre-upload images</strong> to Cloudinary and paste the HTTPS URLs into the <code class="text-xs font-mono">thumbnail</code> and <code class="text-xs font-mono">preview_image</code> columns.
            </li>
            <li>
                <strong>Upload your CSV</strong> using the form below. Products with a matching title are automatically skipped — no duplicates created.
            </li>
        </ol>
        <div class="pt-1">
            <a href="{{ route('admin.products.import.template') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Download CSV Template
            </a>
        </div>
    </div>

    {{-- Staging folder files --}}
    @if($stagingFiles->isNotEmpty())
    <div class="bg-white border border-gray-200 rounded-2xl p-6">
        <h3 class="font-bold text-sm text-gray-800 mb-3">Files in staging folder ({{ $stagingFiles->count() }})</h3>
        <div class="flex flex-wrap gap-2">
            @foreach($stagingFiles as $stagingFile)
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg text-xs font-mono">
                <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                {{ $stagingFile }}
            </span>
            @endforeach
        </div>
    </div>
    @else
    <div class="bg-white border border-gray-200 rounded-2xl p-6">
        <h3 class="font-bold text-sm text-gray-800 mb-1">Staging folder</h3>
        <p class="text-sm text-gray-500">
            No product files found in <code class="text-xs font-mono bg-gray-100 px-1.5 py-0.5 rounded">storage/app/products/bulk/</code>.
            Upload files there via cPanel or FTP before importing.
        </p>
    </div>
    @endif

    {{-- Upload form --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-8">
        <h2 class="text-base font-bold mb-6">Upload CSV File</h2>

        @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm space-y-1">
            @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
            @endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('admin.products.import.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- CSV file --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    CSV File <span class="text-red-500">*</span>
                </label>
                <input type="file" name="csv_file" accept=".csv,text/csv" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300] file:mr-4 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#FFC300] file:text-black hover:file:bg-[#FFD633] cursor-pointer">
                <p class="text-xs text-gray-400 mt-1">Max 5 MB. Must include columns: <code class="font-mono">title</code>, <code class="font-mono">price</code>, <code class="font-mono">file_type</code>.</p>
                @error('csv_file')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Defaults row --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Default Author
                        <span class="font-normal text-gray-400">(used when author_email is blank)</span>
                    </label>
                    <select name="default_author_id"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">
                        <option value="">— None —</option>
                        @foreach($authors as $author)
                        <option value="{{ $author->id }}" {{ old('default_author_id') == $author->id ? 'selected' : '' }}>
                            {{ $author->name }} ({{ $author->email }})
                        </option>
                        @endforeach
                    </select>
                    @error('default_author_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Default Category
                        <span class="font-normal text-gray-400">(used when category is blank)</span>
                    </label>
                    <select name="default_category_id"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">
                        <option value="">— None —</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('default_category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('default_category_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- CSV column reference --}}
            <details class="border border-gray-200 rounded-xl overflow-hidden">
                <summary class="px-5 py-3 text-sm font-semibold text-gray-700 cursor-pointer hover:bg-gray-50 select-none">
                    CSV Column Reference
                </summary>
                <div class="overflow-x-auto border-t border-gray-100">
                    <table class="w-full text-xs">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2.5 text-left font-semibold text-gray-500 uppercase">Column</th>
                                <th class="px-4 py-2.5 text-left font-semibold text-gray-500 uppercase">Required</th>
                                <th class="px-4 py-2.5 text-left font-semibold text-gray-500 uppercase">Description</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach([
                                ['title', true, 'Product title. Must be unique.'],
                                ['category', false, 'Category name (case-insensitive). Falls back to Default Category.'],
                                ['author_email', false, 'Author\'s email address. Falls back to Default Author.'],
                                ['price', true, 'Price as a positive integer (e.g. 2999 for ₦29.99 or $29.99).'],
                                ['sale_price', false, 'Sale price integer, must be less than price. Leave blank for no sale.'],
                                ['file_type', true, 'One of: graphic, template, audio, video, font, plugin, 3d'],
                                ['file_name', false, 'Filename in the staging folder (e.g. my-kit.zip). Extension must be allowed.'],
                                ['file_size', false, 'File size in MB. Auto-calculated from staged file if omitted.'],
                                ['thumbnail', false, 'Cloudinary HTTPS URL or relative storage path for the thumbnail.'],
                                ['preview_image', false, 'Cloudinary HTTPS URL or relative storage path for the preview image.'],
                                ['tags', false, 'Comma-separated tags: "ui,kit,design"'],
                                ['version', false, 'Version string, e.g. "1.0"'],
                                ['demo_url', false, 'HTTPS URL to a live demo.'],
                                ['requirements', false, 'Plain text requirements.'],
                                ['features', false, 'Pipe-separated feature list: "Dark mode|Light mode|Responsive"'],
                                ['is_featured', false, '1 to feature the product, 0 otherwise. Defaults to 0.'],
                                ['is_published', false, '1 to publish, 0 for draft. Defaults to 1 (published).'],
                                ['description', false, 'Plain text product description.'],
                            ] as [$col, $req, $desc])
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2.5 font-mono text-gray-800">{{ $col }}</td>
                                <td class="px-4 py-2.5">
                                    @if($req)
                                    <span class="text-red-500 font-semibold">Yes</span>
                                    @else
                                    <span class="text-gray-400">No</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-gray-600">{{ $desc }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>

            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                <a href="{{ route('admin.products.index') }}" class="px-6 py-3 border border-gray-300 rounded-xl text-sm font-semibold hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit"
                    class="px-6 py-3 bg-[#FFC300] text-black rounded-xl text-sm font-semibold hover:bg-[#FFD633] transition-colors">
                    Import Products
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
