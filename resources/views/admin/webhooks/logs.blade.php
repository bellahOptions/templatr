@extends('admin.layouts.admin')

@section('title', 'Webhook Logs - Admin')
@section('header', 'Webhook Logs: ' . $webhook->name)

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.webhooks.index') }}" class="text-sm text-gray-500 hover:text-[#FFC300]">&larr; Back to Webhooks</a>
    <div class="mt-2 flex items-center space-x-4">
        <code class="text-sm bg-gray-100 px-3 py-1.5 rounded">{{ $webhook->url }}</code>
        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $webhook->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
            {{ $webhook->is_active ? 'Active' : 'Inactive' }}
        </span>
    </div>
</div>

@if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm mb-6">
        {{ session('success') }}
    </div>
@endif

<div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
    @if($logs->isEmpty())
        <div class="text-center py-12">
            <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-1">No logs yet</h3>
            <p class="text-sm text-gray-500">Send a test ping to see delivery logs.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Event</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Response</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Sent At</th>
                        <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <code class="text-sm font-mono">{{ $log->event }}</code>
                        </td>
                        <td class="px-6 py-4">
                            @if($log->status === 'success')
                                <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-semibold">Success</span>
                            @elseif($log->status === 'failed')
                                <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-xs font-semibold">Failed</span>
                            @else
                                <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full text-xs font-semibold">Pending</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($log->response_code)
                                <span class="text-sm font-mono">{{ $log->response_code }}</span>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-600">{{ $log->created_at->format('M j, g:i A') }}</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button type="button" onclick="toggleDetails({{ $log->id }})"
                                    class="text-xs text-blue-600 hover:text-blue-800 font-semibold">
                                Details
                            </button>
                            @if($log->status === 'failed')
                                <form method="POST" action="{{ route('admin.webhooks.retry', $log) }}" class="inline ml-2">
                                    @csrf
                                    <button type="submit" class="text-xs text-[#FFC300] hover:text-black font-semibold">
                                        Retry
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    <tr id="details-{{ $log->id }}" class="hidden">
                        <td colspan="5" class="px-6 py-4 bg-gray-50">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <h4 class="text-xs font-semibold text-gray-500 mb-2">Request Payload</h4>
                                    <pre class="text-xs bg-gray-900 text-green-400 p-3 rounded-xl overflow-x-auto max-h-48"><code>{{ json_encode($log->request_payload, JSON_PRETTY_PRINT) }}</code></pre>
                                </div>
                                <div>
                                    <h4 class="text-xs font-semibold text-gray-500 mb-2">Response</h4>
                                    <pre class="text-xs bg-gray-900 text-green-400 p-3 rounded-xl overflow-x-auto max-h-48"><code>{{ json_encode($log->response, JSON_PRETTY_PRINT) }}</code></pre>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $logs->links() }}
        </div>
    @endif
</div>

<script>
function toggleDetails(id) {
    const row = document.getElementById('details-' + id);
    row.classList.toggle('hidden');
}
</script>
@endsection
