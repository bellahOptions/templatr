<div>
    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-xl font-bold text-gray-900">Notifications</h2>
        <button wire:click="create" class="bg-[#FFC300] hover:bg-black hover:text-white px-4 py-2 rounded-lg text-sm font-semibold transition-all flex items-center">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Notification
        </button>
    </div>

    @if(session('message'))
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 mb-6 text-sm">{{ session('message') }}</div>
    @endif

    {{-- View Details Panel --}}
    @if($viewingId)
        @php $viewed = $notifications->find($viewingId) ?? \App\Models\Notification::find($viewingId) @endphp
        @if($viewed)
        <div class="bg-white rounded-2xl p-6 border border-gray-200 mb-6">
            <div class="flex items-start justify-between mb-4">
                <h3 class="text-lg font-semibold">Notification Details</h3>
                <button wire:click="closeView" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="space-y-3">
                <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        @switch($viewed->type)
                            @case('success') bg-green-100 text-green-700 @break
                            @case('warning') bg-yellow-100 text-yellow-700 @break
                            @case('alert') bg-red-100 text-red-700 @break
                            @default bg-blue-100 text-blue-700
                        @endswitch">{{ ucfirst($viewed->type) }}</span>
                    <span class="text-xs text-gray-400">{{ $viewed->created_at->diffForHumans() }}</span>
                </div>
                <h4 class="text-base font-semibold text-gray-900">{{ $viewed->title }}</h4>
                <p class="text-sm text-gray-600 leading-relaxed">{{ $viewed->message }}</p>
                @if($viewed->action_url)
                    <p class="text-sm"><span class="font-medium text-gray-700">Action URL:</span>
                        <a href="{{ $viewed->action_url }}" target="_blank" rel="noopener" class="text-[#FFC300] hover:underline break-all ml-1">{{ $viewed->action_url }}</a>
                    </p>
                @endif
                <p class="text-sm text-gray-500">Recipients: {{ $viewed->is_global ? 'All Users' : ($viewed->user?->name ?? 'N/A') }}</p>
            </div>
            <div class="mt-4 flex space-x-3">
                <button wire:click="edit({{ $viewed->id }})" class="bg-[#FFC300] hover:bg-black hover:text-white px-4 py-2 rounded-lg text-sm font-semibold transition-all">Edit</button>
                <button wire:click="closeView" class="px-4 py-2 rounded-lg text-sm font-medium text-gray-500 hover:text-gray-700 border border-gray-200">Close</button>
            </div>
        </div>
        @endif
    @endif

    @if($showForm)
        <div class="bg-white rounded-2xl p-6 border border-gray-200 mb-6">
            <h3 class="text-lg font-semibold mb-4">{{ $editingId ? 'Edit' : 'Create' }} Notification</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input type="text" wire:model="title" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" />
                    @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                    <textarea wire:model="message" rows="3" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></textarea>
                    @error('message') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select wire:model="type" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                        <option value="info">Info</option>
                        <option value="success">Success</option>
                        <option value="warning">Warning</option>
                        <option value="alert">Alert</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Action URL <span class="text-gray-400 font-normal">(optional, must start with https://)</span></label>
                    <input type="url" wire:model="action_url" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" placeholder="https://..." />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Action Text</label>
                    <input type="text" wire:model="action_text" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" placeholder="Learn More" />
                </div>
                <div class="flex items-center space-x-3 pt-6">
                    <label class="flex items-center">
                        <input type="checkbox" wire:model="is_global" class="rounded border-gray-300 text-[#FFC300] focus:ring-[#FFC300]" />
                        <span class="ml-2 text-sm text-gray-700">Global notification (all users)</span>
                    </label>
                </div>
            </div>
            <div class="mt-4 flex space-x-3">
                <button wire:click="save" class="bg-[#FFC300] hover:bg-black hover:text-white px-5 py-2 rounded-lg text-sm font-semibold transition-all">
                    {{ $editingId ? 'Update' : 'Create' }}
                </button>
                <button wire:click="$set('showForm', false)" class="px-5 py-2 rounded-lg text-sm font-medium text-gray-500 hover:text-gray-700 border border-gray-200">Cancel</button>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase px-4 py-3">Type</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase px-4 py-3">Title</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase px-4 py-3">Recipients</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase px-4 py-3">Status</th>
                    <th class="text-right text-xs font-semibold text-gray-500 uppercase px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($notifications as $notif)
                    <tr wire:click="view({{ $notif->id }})" class="hover:bg-gray-50 cursor-pointer">
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                @switch($notif->type)
                                    @case('success') bg-green-100 text-green-700 @break
                                    @case('warning') bg-yellow-100 text-yellow-700 @break
                                    @case('alert') bg-red-100 text-red-700 @break
                                    @default bg-blue-100 text-blue-700
                                @endswitch">{{ ucfirst($notif->type) }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm font-medium text-gray-900">{{ $notif->title }}</span>
                            <p class="text-xs text-gray-500 line-clamp-1">{{ $notif->message }}</p>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $notif->is_global ? 'All Users' : ($notif->user?->name ?? 'N/A') }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $notif->is_read ? 'bg-gray-100 text-gray-500' : 'bg-[#FFC300]/20 text-[#CC9900]' }}">
                                {{ $notif->is_read ? 'Read' : 'Unread' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right" wire:click.stop>
                            <button wire:click="edit({{ $notif->id }})" class="text-[#FFC300] hover:text-black text-sm font-medium mr-3">Edit</button>
                            <button wire:click="delete({{ $notif->id }})" class="text-red-400 hover:text-red-600 text-sm font-medium" onclick="return confirm('Delete this notification?')">Delete</button>
                        </td>
                    </tr>
                @endforeach
                @if($notifications->isEmpty())
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500 text-sm">No notifications yet</td></tr>
                @endif
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $notifications->links() }}</div>
</div>
