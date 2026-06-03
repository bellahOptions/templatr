@extends('layouts.app')

@section('title', 'Payouts - Templatr Affiliate')

@section('content')
<div class="min-h-screen bg-gray-50 py-10">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Payouts</h1>
                <p class="text-gray-500 mt-2">Your coin redemption history</p>
            </div>
            <a href="{{ route('affiliate.index') }}" class="text-sm font-medium text-[#FFC300] hover:text-black transition-colors">← Back to Dashboard</a>
        </div>

        {{-- Request Payout Form --}}
        @auth
        @if(auth()->user()->coins >= 10)
        <div class="bg-white rounded-2xl p-6 border border-gray-200 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Request a Payout</h2>
            <form method="POST" action="{{ route('affiliate.request-payout') }}">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Coins to Redeem</label>
                        <input type="number" name="coins" min="10" max="{{ auth()->user()->coins }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" value="{{ min(50, auth()->user()->coins) }}" required />
                        <p class="text-xs text-gray-400 mt-1">Available: {{ auth()->user()->coins }} coins (min: 10)</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Value</label>
                        <input type="text" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50" readonly value="₦{{ number_format(App\Models\AffiliatePayout::calculateAmount(min(50, auth()->user()->coins)), 2) }}" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                        <select name="payment_method" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" required>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="paypal">PayPal</option>
                            <option value="mobile_money">Mobile Money</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Details</label>
                        <input type="text" name="payment_details" placeholder="Account number, email, or phone" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" required />
                    </div>
                </div>
                <button type="submit" class="mt-4 bg-[#FFC300] hover:bg-black hover:text-white px-6 py-2.5 rounded-xl text-sm font-semibold transition-all">
                    Request Payout
                </button>
            </form>
        </div>
        @endif
        @endauth

        {{-- Payout History --}}
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="p-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Payout History</h3>
            </div>
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-4 py-3">Coins</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-4 py-3">Amount</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-4 py-3">Method</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-4 py-3">Status</th>
                        <th class="text-right text-xs font-semibold text-gray-500 uppercase px-4 py-3">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($payouts as $payout)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm font-semibold">{{ $payout->coins_redeemed }}</td>
                        <td class="px-4 py-3 text-sm font-semibold">₦{{ number_format($payout->amount, 2) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500 capitalize">{{ str_replace('_', ' ', $payout->payment_method) }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium 
                                @switch($payout->status)
                                    @case('completed') bg-green-100 text-green-700 @break
                                    @case('processing') bg-blue-100 text-blue-700 @break
                                    @case('cancelled') bg-red-100 text-red-700 @break
                                    @default bg-yellow-100 text-yellow-700
                                @endswitch">{{ ucfirst($payout->status) }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500 text-right">{{ $payout->created_at->format('M d, Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500 text-sm">No payouts yet. Start referring friends to earn coins!</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payouts->hasPages())
        <div class="mt-4">{{ $payouts->links() }}</div>
        @endif
    </div>
</div>
@endsection
