<div>
    <div class="bg-gradient-to-br from-[#FFC300]/10 via-yellow-50 to-white border border-[#FFC300]/20 rounded-2xl p-6 md:p-8">
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-[#FFC300]/20 rounded-full mb-4">
                <svg class="w-8 h-8 text-[#FFC300]" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900">Refer & Earn</h3>
            <p class="text-gray-500 mt-2 text-sm">Share your referral link and earn <strong class="text-[#FFC300]">10 coins</strong> (₦2,000) when your referral makes a purchase!</p>
        </div>

        <div class="bg-white rounded-xl p-4 border border-gray-200 mb-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-semibold text-gray-700">Your Referral Link</span>
                <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium">Active</span>
            </div>
            <div class="flex items-center space-x-2">
                <input type="text" readonly value="{{ $referralLink }}" class="flex-1 text-sm bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-700" />
                <button wire:click="copyLink" class="flex-shrink-0 bg-[#FFC300] hover:bg-black hover:text-white px-4 py-2 rounded-lg text-sm font-semibold transition-all">
                    @if($copied)
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Copied!
                        </span>
                    @else
                        Copy
                    @endif
                </button>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-4 mb-4">
            <div class="bg-white rounded-xl p-4 border border-gray-200 text-center">
                <div class="text-2xl font-bold text-gray-900">{{ $pendingCount }}</div>
                <div class="text-xs text-gray-500 mt-1">Pending</div>
            </div>
            <div class="bg-white rounded-xl p-4 border border-gray-200 text-center">
                <div class="text-2xl font-bold text-green-600">{{ $convertedCount }}</div>
                <div class="text-xs text-gray-500 mt-1">Converted</div>
            </div>
            <div class="bg-[#FFC300]/10 rounded-xl p-4 border border-[#FFC300]/20 text-center">
                <div class="text-2xl font-bold text-[#FFC300]">{{ $totalCoins }}</div>
                <div class="text-xs text-gray-500 mt-1">Coins Earned</div>
            </div>
        </div>

        @if($message)
            <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-2 mb-4 text-sm flex items-center">
                <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ $message }}
            </div>
        @endif

        <div class="bg-white rounded-xl p-4 border border-gray-200">
            <h4 class="text-sm font-semibold text-gray-700 mb-3">Invite by Email</h4>
            <div class="flex space-x-2">
                <input type="email" wire:model="email" placeholder="friend@email.com" class="flex-1 text-sm border border-gray-200 rounded-lg px-3 py-2" />
                <button wire:click="sendInvite" class="bg-[#FFC300] hover:bg-black hover:text-white px-4 py-2 rounded-lg text-sm font-semibold transition-all">Send Invite</button>
            </div>
            @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        @if(count($referrals) > 0)
            <div class="mt-6">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">Recent Referrals</h4>
                <div class="space-y-2">
                    @foreach(array_slice($referrals, 0, 5) as $ref)
                        <div class="bg-white rounded-lg py-2 px-3 border border-gray-100 flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                    <span class="text-xs font-bold">{{ substr($ref['email'] ?? 'N/A', 0, 1) }}</span>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-700">{{ $ref['email'] ?? 'Guest User' }}</span>
                                    <span class="text-xs text-gray-400 ml-2">{{ \Carbon\Carbon::parse($ref['created_at'])->diffForHumans() }}</span>
                                </div>
                            </div>
                            <span class="text-xs font-medium px-2 py-1 rounded-full 
                                @switch($ref['status'])
                                    @case('converted') bg-green-100 text-green-700 @break
                                    @case('purchased') bg-blue-100 text-blue-700 @break
                                    @case('joined') bg-yellow-100 text-yellow-700 @break
                                    @default bg-gray-100 text-gray-500
                                @endswitch">{{ ucfirst($ref['status']) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
