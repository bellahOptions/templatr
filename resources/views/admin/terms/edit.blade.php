@extends('admin.layouts.admin')

@section('title', 'Terms of Service - Templatr Admin')
@section('header', 'Terms of Service')

@push('styles')
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<style>
    .ql-editor { min-height: 420px; font-size: 14px; font-family: inherit; }
    .ql-toolbar.ql-snow { border-top-left-radius: 0.75rem; border-top-right-radius: 0.75rem; border-color: #d1d5db; background: #f9fafb; }
    .ql-container.ql-snow { border-bottom-left-radius: 0.75rem; border-bottom-right-radius: 0.75rem; border-color: #d1d5db; }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-sm text-gray-500 mt-1">
                @if($terms)
                    Last updated {{ $terms->updated_at->format('F j, Y \a\t g:ia') }}
                    @if($terms->updater) by {{ $terms->updater->name }}@endif
                @else
                    No terms published yet.
                @endif
            </p>
        </div>
        <a href="{{ route('terms.show') }}" target="_blank" class="flex items-center space-x-1.5 text-sm text-gray-500 hover:text-gray-800 border border-gray-200 rounded-lg px-3 py-2 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            <span>View Public Page</span>
        </a>
    </div>

    <div class="bg-white border border-gray-200 rounded-2xl p-8">
        <form method="POST" action="{{ route('admin.terms.update') }}" id="terms-form">
            @csrf
            @method('PUT')

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Terms Content</label>
                <div id="terms-editor"></div>
                <textarea name="content" id="terms-content" class="hidden">{{ old('content', $terms?->content) }}</textarea>
                @error('content')
                    <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                <div class="flex items-center space-x-2 text-sm text-amber-600 bg-amber-50 border border-amber-100 rounded-lg px-3 py-2">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Saving will send an email notification to all registered users.</span>
                </div>
                <button type="submit" class="bg-[#FFC300] text-black font-semibold px-6 py-2.5 rounded-xl hover:bg-[#E6B000] transition-colors text-sm">
                    Save &amp; Notify Users
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
(function () {
    var quill = new Quill('#terms-editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ header: [1, 2, 3, false] }],
                ['bold', 'italic', 'underline'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['link'],
                ['clean']
            ]
        },
        placeholder: 'Write your terms of service here…'
    });

    var existing = document.getElementById('terms-content').value;
    if (existing) {
        quill.root.innerHTML = existing;
    }

    document.getElementById('terms-form').addEventListener('submit', function () {
        document.getElementById('terms-content').value = quill.root.innerHTML;
    });
})();
</script>
@endpush
