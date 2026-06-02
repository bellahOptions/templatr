<?php

namespace App\Http\Controllers;

use App\Models\AffiliatePayout;
use App\Models\Referral;
use Illuminate\Http\Request;

class AffiliateController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $referrals = Referral::byReferrer($user->id)->latest()->paginate(10);
        
        $stats = [
            'total_coins' => $user->coins,
            'total_value' => AffiliatePayout::coinsToCurrency($user->coins),
            'pending_commission' => $user->pending_commission,
            'referral_count' => Referral::byReferrer($user->id)->count(),
            'converted_count' => Referral::byReferrer($user->id)->whereIn('status', ['purchased', 'converted'])->count(),
        ];

        return view('affiliate.index', compact('referrals', 'stats'));
    }

    public function payouts()
    {
        $payouts = AffiliatePayout::where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('affiliate.payouts', compact('payouts'));
    }

    public function requestPayout(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'coins' => 'required|integer|min:10|max:' . $user->coins,
            'payment_method' => 'required|string',
            'payment_details' => 'required|string',
        ]);

        $amount = AffiliatePayout::calculateAmount($request->coins);

        AffiliatePayout::create([
            'user_id' => $user->id,
            'coins_redeemed' => $request->coins,
            'amount' => $amount,
            'payment_method' => $request->payment_method,
            'payment_details' => $request->payment_details,
            'status' => 'pending',
        ]);

        $user->decrement('coins', $request->coins);

        return redirect()->route('affiliate.payouts')
            ->with('success', 'Payout request submitted! You will be notified when it is processed.');
    }
}
