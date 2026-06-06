<div>
    {{-- Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-2xl p-6 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Coins Issued</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($totalCoins) }}</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600" viewBox="0 0 88 93" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M88 7.16H0V93.16H36V51.16H44H52V93.16H88V7.16Z"/></svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-6 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Payouts (₦)</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">₦{{ number_format($totalPayouts, 2) }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-6 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Active Affiliates</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ $users->total() }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
            </div>
        </div>
    </div>

    @if(session('message'))
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 mb-6 text-sm">{{ session('message') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-6 text-sm">{{ session('error') }}</div>
    @endif

    @if($showPayoutForm)
        <div class="bg-white rounded-2xl p-6 border border-gray-200 mb-6">
            <h3 class="text-lg font-semibold mb-4">Process Payout</h3>
            <p class="text-sm text-gray-500 mb-4">User: <strong>{{ $payoutUser?->name }}</strong> — Available Coins: <strong>{{ $payoutUser?->coins }}</strong></p>
            <div class="flex items-center space-x-3">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Coins to Convert</label>
                    <input type="number" wire:model="payoutCoins" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" />
                </div>
                <div class="pt-5">
                    <p class="text-sm text-gray-500">= ₦{{ number_format($payoutCoins * 200, 2) }}</p>
                </div>
                <div class="pt-5">
                    <button wire:click="processPayout" class="bg-[#FFC300] hover:bg-black hover:text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition-all">Process Payout</button>
                    <button wire:click="$set('showPayoutForm', false)" class="ml-2 px-5 py-2.5 rounded-lg text-sm font-medium text-gray-500 border border-gray-200">Cancel</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Users with Coins --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-8">
        <div class="p-4 border-b border-gray-100">
            <input type="text" wire:model.live="search" placeholder="Search affiliates..." class="w-full max-w-sm border border-gray-200 rounded-lg px-3 py-2 text-sm" />
        </div>
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase px-4 py-3">User</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase px-4 py-3">Coins</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase px-4 py-3">Value (₦)</th>
                    <th class="text-right text-xs font-semibold text-gray-500 uppercase px-4 py-3">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                                    <span class="text-xs font-bold">{{ substr($user->name, 0, 1) }}</span>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-900">{{ $user->name }}</span>
                                    <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ number_format($user->coins) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">₦{{ number_format($user->coins * 200, 2) }}</td>
                        <td class="px-4 py-3 text-right">
                            <button wire:click="selectUser({{ $user->id }})" class="text-[#FFC300] hover:text-black text-sm font-medium">Process Payout</button>
                        </td>
                    </tr>
                @endforeach
                @if($users->isEmpty())
                    <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500 text-sm">No affiliates with coins yet</td></tr>
                @endif
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $users->links() }}</div>

    {{-- Recent Payouts --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="p-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-900">Recent Payouts</h3>
        </div>
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase px-4 py-3">User</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase px-4 py-3">Coins</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase px-4 py-3">Amount</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase px-4 py-3">Status</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase px-4 py-3">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($payouts as $payout)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $payout->user?->name ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $payout->coins_redeemed }}</td>
                        <td class="px-4 py-3 text-sm font-semibold">₦{{ number_format($payout->amount, 2) }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $payout->status === 'completed' ? 'bg-green-100 text-green-700' : ($payout->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-500') }}">
                                {{ ucfirst($payout->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $payout->created_at->format('M d, Y') }}</td>
                    </tr>
                @endforeach
                @if($payouts->isEmpty())
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500 text-sm">No payouts yet</td></tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
