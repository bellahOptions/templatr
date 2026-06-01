₦@extends('admin.layouts.admin')

@section('title', 'Reviews - CreativeMarket')
@section('header', 'Reviews')

@section('content')
<div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
    <div class="p-6 border-b border-gray-200">
        <form method="GET" class="flex gap-3">
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]">
                <option value="">All Status</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-black text-white rounded-lg text-sm font-semibold hover:bg-gray-800">Filter</button>
        </form>
    </div>
    <div class="divide-y divide-gray-100">
        @foreach($reviews as $review)
        <div class="p-6">
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                        <span class="font-bold text-sm">{{ substr($review->user->name, 0, 1) }}</span>
                    </div>
                    <div>
                        <p class="font-semibold text-sm">{{ $review->user->name }}</p>
                        <div class="flex items-center">
                            @for($i = 1; $i <= 5; $i++)
                            <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            @endfor
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="text-xs text-gray-400">{{ $review->created_at->diffForHumans() }}</span>
                    @if(!$review->is_approved)
                    <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-semibold">Pending</span>
                    @endif
                </div>
            </div>
            <p class="text-sm text-gray-600 mb-2">On: <a href="{{ route('products.show', $review->product) }}" class="text-[#FFC300] hover:text-black font-semibold">{{ $review->product->title }}</a></p>
            @if($review->review)
            <p class="text-gray-700 text-sm bg-gray-50 rounded-xl p-4">{{ $review->review }}</p>
            @endif
            <div class="flex items-center space-x-3 mt-3">
                @if(!$review->is_approved)
                <form method="POST" action="{{ route('admin.reviews.approve', $review) }}" class="inline">
                    @csrf
                    <button type="submit" class="text-sm text-green-600 hover:text-green-800 font-semibold">Approve</button>
                </form>
                <form method="POST" action="{{ route('admin.reviews.reject', $review) }}" class="inline">
                    @csrf
                    <button type="submit" class="text-sm text-red-500 hover:text-red-700 font-semibold">Reject</button>
                </form>
                @else
                <span class="text-sm text-green-600 font-semibold">✓ Approved</span>
                @endif
                <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" class="inline" onsubmit="return confirm('Delete this review?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm text-red-500 hover:text-red-700 font-semibold">Delete</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @if($reviews->isEmpty())
    <div class="text-center py-12">
        <p class="text-gray-500">No reviews found.</p>
    </div>
    @endif
    <div class="p-4 border-t border-gray-200">
        {{ $reviews->links() }}
    </div>
</div>
@endsection
