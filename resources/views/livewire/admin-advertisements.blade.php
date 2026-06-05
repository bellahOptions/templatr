<div>
    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-xl font-bold text-gray-900">Advertisements</h2>
        <button wire:click="create" class="bg-[#FFC300] hover:bg-black hover:text-white px-4 py-2 rounded-lg text-sm font-semibold transition-all flex items-center">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Ad
        </button>
    </div>

    @if(session('message'))
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 mb-6 text-sm">{{ session('message') }}</div>
    @endif

    {{-- View Details Panel --}}
    @if($viewingId)
        @php $viewed = $ads->find($viewingId) ?? \App\Models\Advertisement::find($viewingId) @endphp
        @if($viewed)
        <div class="bg-white rounded-2xl p-6 border border-gray-200 mb-6">
            <div class="flex items-start justify-between mb-4">
                <h3 class="text-lg font-semibold">Advertisement Details</h3>
                <button wire:click="closeView" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($viewed->image_url)
                    <div class="md:col-span-2">
                        <img src="{{ $viewed->image_url }}" alt="{{ $viewed->title }}" class="w-full max-h-48 object-cover rounded-xl" />
                    </div>
                @endif
                <div>
                    <p class="text-xs text-gray-400 font-medium uppercase mb-1">Title</p>
                    <p class="text-sm font-semibold text-gray-900">{{ $viewed->title }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-medium uppercase mb-1">Position / Type</p>
                    <p class="text-sm text-gray-700 capitalize">{{ $viewed->position }} / {{ $viewed->type }}</p>
                </div>
                @if($viewed->description)
                    <div class="md:col-span-2">
                        <p class="text-xs text-gray-400 font-medium uppercase mb-1">Description</p>
                        <p class="text-sm text-gray-600">{{ $viewed->description }}</p>
                    </div>
                @endif
                @if($viewed->link_url)
                    <div class="md:col-span-2">
                        <p class="text-xs text-gray-400 font-medium uppercase mb-1">Link URL</p>
                        <a href="{{ $viewed->link_url }}" target="_blank" rel="noopener" class="text-sm text-[#FFC300] hover:underline break-all">{{ $viewed->link_url }}</a>
                    </div>
                @endif
                <div>
                    <p class="text-xs text-gray-400 font-medium uppercase mb-1">Status</p>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $viewed->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $viewed->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
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
            <h3 class="text-lg font-semibold mb-4">{{ $editingId ? 'Edit' : 'Create' }} Advertisement</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input type="text" wire:model="title" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" />
                    @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea wire:model="description" rows="2" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Image URL</label>
                    <input type="url" wire:model="image_url" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" placeholder="https://..." />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Link URL <span class="text-gray-400 font-normal">(must start with https://)</span></label>
                    <input type="url" wire:model="link_url" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" placeholder="https://..." />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                    <select wire:model="position" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                        <option value="banner">Banner (Top)</option>
                        <option value="sidebar">Sidebar</option>
                        <option value="inline">Inline (Between content)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select wire:model="type" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                        <option value="banner">Banner</option>
                        <option value="promo">Promo</option>
                        <option value="announcement">Announcement</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                    <input type="number" wire:model="sort_order" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" />
                </div>
                <div class="flex items-center pt-6">
                    <label class="flex items-center">
                        <input type="checkbox" wire:model="is_active" class="rounded border-gray-300 text-[#FFC300] focus:ring-[#FFC300]" />
                        <span class="ml-2 text-sm text-gray-700">Active</span>
                    </label>
                </div>
            </div>
            <div class="mt-4 flex space-x-3">
                <button wire:click="save" class="bg-[#FFC300] hover:bg-black hover:text-white px-5 py-2 rounded-lg text-sm font-semibold transition-all">{{ $editingId ? 'Update' : 'Create' }}</button>
                <button wire:click="$set('showForm', false)" class="px-5 py-2 rounded-lg text-sm font-medium text-gray-500 hover:text-gray-700 border border-gray-200">Cancel</button>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase px-4 py-3">Title</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase px-4 py-3">Position</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase px-4 py-3">Type</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase px-4 py-3">Status</th>
                    <th class="text-right text-xs font-semibold text-gray-500 uppercase px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($ads as $ad)
                    <tr wire:click="view({{ $ad->id }})" class="hover:bg-gray-50 cursor-pointer">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $ad->title }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500 capitalize">{{ $ad->position }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500 capitalize">{{ $ad->type }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $ad->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $ad->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right" wire:click.stop>
                            <button wire:click="edit({{ $ad->id }})" class="text-[#FFC300] hover:text-black text-sm font-medium mr-3">Edit</button>
                            <button wire:click="delete({{ $ad->id }})" class="text-red-400 hover:text-red-600 text-sm font-medium" onclick="return confirm('Delete this advertisement?')">Delete</button>
                        </td>
                    </tr>
                @endforeach
                @if($ads->isEmpty())
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500 text-sm">No advertisements yet</td></tr>
                @endif
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $ads->links() }}</div>
</div>
