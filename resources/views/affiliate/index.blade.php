@extends('layouts.app')

@section('title', 'Affiliate Dashboard - Templatr')
@section('robots', 'noindex, nofollow')

@section('content')
<div class="min-h-screen bg-gray-50 py-10">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Affiliate Dashboard</h1>
            <p class="text-gray-500 mt-2">Refer friends and earn coins. Every coin is worth real value!</p>
        </div>

        {{-- Stats Overview --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-2xl p-5 border border-gray-200">
                <p class="text-sm text-gray-500">Your Coins</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($stats['total_coins']) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-gray-200">
                <p class="text-sm text-gray-500">Coin Value</p>
                <p class="text-2xl font-bold text-[#FFC300] mt-1">₦{{ number_format($stats['total_value'], 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-gray-200">
                <p class="text-sm text-gray-500">Referrals</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['referral_count'] }}</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-gray-200">
                <p class="text-sm text-gray-500">Converted</p>
                <p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['converted_count'] }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Referral Link Component --}}
            <div class="lg:col-span-2">
                @livewire('referral-link')
            </div>

            {{-- Quick Actions / Info --}}
            <div class="space-y-6">
                <div class="bg-gradient-to-br from-[#FFC300]/10 to-yellow-50 border border-[#FFC300]/20 rounded-2xl p-6">
                    <h3 class="font-semibold text-gray-900 mb-3">How It Works</h3>
                    <ul class="space-y-3">
                        <li class="flex items-start space-x-3">
                            <span class="w-6 h-6 bg-[#FFC300]/20 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <span class="text-xs font-bold text-[#FFC300]">1</span>
                            </span>
                            <span class="text-sm text-gray-600">Share your unique referral link with friends</span>
                        </li>
                        <li class="flex items-start space-x-3">
                            <span class="w-6 h-6 bg-[#FFC300]/20 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <span class="text-xs font-bold text-[#FFC300]">2</span>
                            </span>
                            <span class="text-sm text-gray-600">They sign up and make their first purchase</span>
                        </li>
                        <li class="flex items-start space-x-3">
                            <span class="w-6 h-6 bg-[#FFC300]/20 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <span class="text-xs font-bold text-[#FFC300]">3</span>
                            </span>
                            <span class="text-sm text-gray-600">You earn <strong>10 coins</strong> (₦2,000) per referral</span>
                        </li>
                    </ul>
                </div>

                <div class="bg-white rounded-2xl p-6 border border-gray-200">
                    <h3 class="font-semibold text-gray-900 mb-3">Coin Value</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">1 Coin</span>
                            <span class="text-sm font-semibold">= ₦200</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">10 Coins</span>
                            <span class="text-sm font-semibold">= ₦2,000</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">50 Coins</span>
                            <span class="text-sm font-semibold">= ₦10,000</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">100 Coins</span>
                            <span class="text-sm font-semibold">= ₦20,000</span>
                        </div>
                    </div>
                    <a href="{{ route('affiliate.payouts') }}" class="mt-4 block text-center bg-[#FFC300] hover:bg-black hover:text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition-all">
                        Request Payout
                    </a>
                </div>
            </div>
        </div>

        {{-- Referral History --}}
        @if($referrals->isNotEmpty())
        <div class="mt-10">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Referral History</h2>
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left text-xs font-semibold text-gray-500 uppercase px-4 py-3">Email / User</th>
                            <th class="text-left text-xs font-semibold text-gray-500 uppercase px-4 py-3">Status</th>
                            <th class="text-left text-xs font-semibold text-gray-500 uppercase px-4 py-3">Coins Earned</th>
                            <th class="text-right text-xs font-semibold text-gray-500 uppercase px-4 py-3">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($referrals as $referral)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $referral->email ?? $referral->referredUser?->name ?? 'N/A' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                    @switch($referral->status)
                                        @case('converted') bg-green-100 text-green-700 @break
                                        @case('purchased') bg-blue-100 text-blue-700 @break
                                        @case('joined') bg-yellow-100 text-yellow-700 @break
                                        @default bg-gray-100 text-gray-500
                                    @endswitch">{{ ucfirst($referral->status) }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm font-semibold">{{ $referral->coins_earned }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500 text-right">{{ $referral->created_at->format('M d, Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $referrals->links() }}</div>
        </div>
        @endif
    </div>
</div>
@endsection
