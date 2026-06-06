<div>
    {{-- Hidden input carries the selected value into the parent form submission --}}
    <input type="hidden" name="category_id" value="{{ $categoryId }}">

    @if(!$showForm)
        <div class="flex items-center space-x-2">
            <select
                wire:model.live="categoryId"
                class="flex-1 px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]"
            >
                <option value="">— Select a category —</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            <button
                type="button"
                wire:click="$set('showForm', true)"
                class="flex-shrink-0 px-3 py-3 bg-gray-100 hover:bg-[#FFC300] text-gray-600 hover:text-black rounded-xl text-sm font-semibold transition-colors flex items-center space-x-1"
                title="Create new category"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span class="hidden sm:inline">New</span>
            </button>
        </div>
    @else
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 space-y-3">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-gray-800">New Category</p>
                <button
                    type="button"
                    wire:click="$set('showForm', false)"
                    class="text-xs text-gray-400 hover:text-gray-600 transition-colors"
                >Cancel</button>
            </div>
            <div>
                <input
                    type="text"
                    wire:model.live="newName"
                    placeholder="Category name"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#FFC300]"
                    autofocus
                >
                @error('newName') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <input
                    type="text"
                    wire:model="newSlug"
                    placeholder="slug-auto-generated"
                    class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm text-gray-500 focus:outline-none focus:ring-2 focus:ring-[#FFC300] bg-white"
                >
                <p class="text-xs text-gray-400 mt-1">URL slug — auto-generated from name</p>
            </div>
            <button
                type="button"
                wire:click="createCategory"
                class="w-full bg-[#FFC300] text-black py-2.5 rounded-lg text-sm font-bold hover:bg-[#FFD633] transition-colors"
            >
                Create &amp; Select
            </button>
        </div>
    @endif
</div>
