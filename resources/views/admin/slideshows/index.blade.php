@extends('admin.layouts.admin')

@section('title', 'Slideshows - Templatr')
@section('header', 'Hero Slideshow')

@section('content')
<div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
    <div class="p-6 border-b border-gray-200 flex justify-between items-center">
        <p class="text-sm text-gray-500">{{ $slideshows->count() }} {{ Str::plural('slide', $slideshows->count()) }}</p>
        <a href="{{ route('admin.slideshows.create') }}" class="bg-[#FFC300] text-black px-4 py-2 rounded-lg text-sm font-semibold hover:bg-[#FFD633]">+ Add Slide</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Slide</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">CTA</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Order</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($slideshows as $slide)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-4">
                            @if($slide->image_url)
                                <img src="{{ $slide->image_url }}" alt="{{ $slide->title }}" class="w-20 h-12 object-cover rounded-lg flex-shrink-0">
                            @else
                                <div class="w-20 h-12 bg-gray-100 rounded-lg flex-shrink-0 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            @endif
                            <div>
                                <p class="font-semibold text-sm">{{ $slide->title }}</p>
                                @if($slide->description)
                                    <p class="text-xs text-gray-500 line-clamp-1 mt-0.5 max-w-xs">{{ $slide->description }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @if($slide->cta_text)
                            <span class="text-sm font-medium">{{ $slide->cta_text }}</span>
                            @if($slide->cta_url)
                                <p class="text-xs text-gray-400 truncate max-w-[160px]">{{ $slide->cta_url }}</p>
                            @endif
                        @else
                            <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center text-sm text-gray-600">{{ $slide->sort_order }}</td>
                    <td class="px-6 py-4 text-center">
                        @if($slide->is_active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Active</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Inactive</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('admin.slideshows.edit', $slide) }}" class="text-sm text-[#FFC300] hover:text-black font-semibold mr-3">Edit</a>
                        <form method="POST" action="{{ route('admin.slideshows.destroy', $slide) }}" class="inline" onsubmit="return confirm('Delete this slide?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-red-500 hover:text-red-700 font-semibold">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-16 text-center text-gray-400">
                        <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <p class="text-sm">No slides yet. <a href="{{ route('admin.slideshows.create') }}" class="text-[#FFC300] font-semibold hover:underline">Add your first slide</a>.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('fab')
<a href="{{ route('admin.slideshows.create') }}"
   title="Add New Slide"
   class="fixed bottom-6 right-6 z-50 w-14 h-14 bg-[#FFC300] text-black rounded-full shadow-lg hover:bg-[#FFD633] hover:shadow-xl transition-all flex items-center justify-center group">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
    </svg>
    <span class="absolute right-16 bg-black text-white text-xs font-semibold px-3 py-1.5 rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
        New Slide
    </span>
</a>
@endpush
