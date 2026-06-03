@extends('admin.layouts.admin')

@section('title', 'Edit Webhook - Admin')
@section('header', 'Edit Webhook')

@section('content')
<div class="max-w-2xl">
    <a href="{{ route('admin.webhooks.index') }}" class="text-sm text-gray-500 hover:text-[#FFC300] mb-4 inline-block">&larr; Back to Webhooks</a>

    <div class="bg-white border border-gray-200 rounded-2xl p-8">
        <form method="POST" action="{{ route('admin.webhooks.update', $webhook) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Webhook Name</label>
                <input id="name" type="text" name="name" value="{{ old('name', $webhook->name) }}" required
                       class="block w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300] @error('name') border-red-500 @enderror">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="url" class="block text-sm font-medium text-gray-700 mb-1">Endpoint URL</label>
                <input id="url" type="url" name="url" value="{{ old('url', $webhook->url) }}" required
                       class="block w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300] @error('url') border-red-500 @enderror">
                @error('url') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="secret" class="block text-sm font-medium text-gray-700 mb-1">Secret Key <span class="text-gray-400">(optional)</span></label>
                <input id="secret" type="text" name="secret" value="{{ old('secret', $webhook->secret) }}"
                       class="block w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300] @error('secret') border-red-500 @enderror">
                @error('secret') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                <p class="text-xs text-gray-400 mt-1">Leave blank to keep existing. A SHA256 HMAC signature will be sent in the <code class="bg-gray-100 px-1 rounded">X-Webhook-Signature</code> header.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Subscribe to Events</label>
                <p class="text-xs text-gray-400 mb-3">Leave all unchecked to receive all events.</p>
                <div class="space-y-2">
                    @php $selectedEvents = old('events', $webhook->events ?? []); @endphp
                    <label class="flex items-center space-x-3">
                        <input type="checkbox" name="events[]" value="order.paid"
                               class="rounded border-gray-300 text-[#FFC300] focus:ring-[#FFC300]"
                               {{ in_array('order.paid', $selectedEvents) ? 'checked' : '' }}>
                        <span class="text-sm text-gray-700">order.paid</span>
                        <span class="text-xs text-gray-400">— When an order is successfully paid</span>
                    </label>
                    <label class="flex items-center space-x-3">
                        <input type="checkbox" name="events[]" value="user.registered"
                               class="rounded border-gray-300 text-[#FFC300] focus:ring-[#FFC300]"
                               {{ in_array('user.registered', $selectedEvents) ? 'checked' : '' }}>
                        <span class="text-sm text-gray-700">user.registered</span>
                        <span class="text-xs text-gray-400">— When a new user registers</span>
                    </label>
                    <label class="flex items-center space-x-3">
                        <input type="checkbox" name="events[]" value="user.verified"
                               class="rounded border-gray-300 text-[#FFC300] focus:ring-[#FFC300]"
                               {{ in_array('user.verified', $selectedEvents) ? 'checked' : '' }}>
                        <span class="text-sm text-gray-700">user.verified</span>
                        <span class="text-xs text-gray-400">— When a user verifies their email</span>
                    </label>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <input type="checkbox" name="is_active" value="1" id="is_active"
                       class="rounded border-gray-300 text-[#FFC300] focus:ring-[#FFC300]"
                       {{ $webhook->is_active ? 'checked' : '' }}>
                <label for="is_active" class="text-sm font-medium text-gray-700">Active</label>
            </div>

            <div class="flex items-center space-x-4 pt-4 border-t border-gray-100">
                <button type="submit" class="px-6 py-3 bg-black text-white rounded-xl text-sm font-semibold hover:bg-gray-800 transition-colors">
                    Update Webhook
                </button>
                <a href="{{ route('admin.webhooks.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
