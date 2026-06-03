@extends('admin.layouts.admin')

@section('title', 'Webhooks - Admin')
@section('header', 'Webhooks')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <p class="text-gray-600">Manage webhook endpoints for event notifications.</p>
    <a href="{{ route('admin.webhooks.create') }}"
       class="inline-flex items-center px-4 py-2 bg-black text-white rounded-xl text-sm font-semibold hover:bg-gray-800 transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Add Webhook
    </a>
</div>

@if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm mb-6">
        {{ session('success') }}
    </div>
@endif

<div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
    @if($webhooks->isEmpty())
        <div class="text-center py-12">
            <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-1">No webhooks yet</h3>
            <p class="text-sm text-gray-500 mb-4">Create a webhook to receive event notifications.</p>
            <a href="{{ route('admin.webhooks.create') }}"
               class="inline-flex items-center px-4 py-2 bg-[#FFC300] text-black rounded-xl text-sm font-semibold hover:bg-[#E6B000] transition-colors">
                Create Your First Webhook
            </a>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Name</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">URL</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Events</th>
                        <th class="text-center px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($webhooks as $webhook)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <p class="text-sm font-semibold">{{ $webhook->name }}</p>
                            <p class="text-xs text-gray-500">Created {{ $webhook->created_at->diffForHumans() }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <code class="text-xs bg-gray-100 px-2 py-1 rounded">{{ Str::limit($webhook->url, 40) }}</code>
                        </td>
                        <td class="px-6 py-4">
                            @if($webhook->events)
                                <div class="flex flex-wrap gap-1">
                                    @foreach($webhook->events as $event)
                                        <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full text-[10px] font-semibold">{{ $event }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-xs text-gray-400">All events</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($webhook->is_active)
                                <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-semibold">Active</span>
                            @else
                                <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-xs font-semibold">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('admin.webhooks.logs', $webhook) }}"
                                   class="px-3 py-1.5 text-xs font-semibold text-gray-600 hover:text-black transition-colors"
                                   title="View Logs">
                                    Logs
                                </a>
                                <form method="POST" action="{{ route('admin.webhooks.test', $webhook) }}" class="inline">
                                    @csrf
                                    <button type="submit"
                                            class="px-3 py-1.5 text-xs font-semibold text-blue-600 hover:text-blue-800 transition-colors"
                                            title="Test Webhook">
                                        Test
                                    </button>
                                </form>
                                <a href="{{ route('admin.webhooks.edit', $webhook) }}"
                                   class="px-3 py-1.5 text-xs font-semibold text-[#FFC300] hover:text-black transition-colors">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('admin.webhooks.destroy', $webhook) }}"
                                      onsubmit="return confirm('Are you sure you want to delete this webhook?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="px-3 py-1.5 text-xs font-semibold text-red-500 hover:text-red-700 transition-colors">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<div class="mt-8 bg-white border border-gray-200 rounded-2xl p-6">
    <h3 class="font-bold text-sm mb-4">Available Webhook Events</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        <div class="bg-gray-50 rounded-xl p-3">
            <p class="text-sm font-semibold">order.paid</p>
            <p class="text-xs text-gray-500 mt-0.5">Fires when an order is successfully paid</p>
        </div>
        <div class="bg-gray-50 rounded-xl p-3">
            <p class="text-sm font-semibold">user.registered</p>
            <p class="text-xs text-gray-500 mt-0.5">Fires when a new user registers</p>
        </div>
        <div class="bg-gray-50 rounded-xl p-3">
            <p class="text-sm font-semibold">user.verified</p>
            <p class="text-xs text-gray-500 mt-0.5">Fires when a user verifies their email</p>
        </div>
    </div>
</div>
@endsection
