<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Referral;
use App\Models\AffiliatePayout;
use Livewire\Component;
use Livewire\WithPagination;

class AdminAffiliates extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $payoutUserId = null;
    public int $payoutCoins = 0;
    public bool $showPayoutForm = false;

    public function selectUser($userId)
    {
        $user = User::find($userId);
        if ($user && $user->coins > 0) {
            $this->payoutUserId = $user->id;
            $this->payoutCoins = $user->coins;
            $this->showPayoutForm = true;
        }
    }

    public function processPayout()
    {
        $user = User::find($this->payoutUserId);
        if (!$user || $user->coins < $this->payoutCoins) {
            session()->flash('error', 'Insufficient coins.');
            return;
        }

        $amount = AffiliatePayout::calculateAmount($this->payoutCoins);

        AffiliatePayout::create([
            'user_id' => $user->id,
            'coins_redeemed' => $this->payoutCoins,
            'amount' => $amount,
            'status' => 'completed',
            'processed_at' => now(),
        ]);

        $user->decrement('coins', $this->payoutCoins);
        $user->increment('pending_commission', $amount);

        $this->showPayoutForm = false;
        $this->payoutUserId = null;
        $this->payoutCoins = 0;

        session()->flash('message', 'Payout of ₦' . number_format($amount, 2) . ' processed successfully.');
    }

    public function render()
    {
        $users = User::where('coins', '>', 0)
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('name', 'LIKE', "%{$this->search}%")
                      ->orWhere('email', 'LIKE', "%{$this->search}%");
                });
            })
            ->orderBy('coins', 'DESC')
            ->paginate(10);

        $payouts = AffiliatePayout::with('user')->latest()->take(10)->get();
        $totalCoins = User::sum('coins');
        $totalPayouts = AffiliatePayout::sum('amount');

        return view('livewire.admin-affiliates', [
            'users' => $users,
            'payouts' => $payouts,
            'totalCoins' => $totalCoins,
            'totalPayouts' => $totalPayouts,
        ]);
    }
}
