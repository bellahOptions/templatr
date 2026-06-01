<?php

namespace App\Livewire;

use App\Models\Referral;
use Livewire\Component;

class ReferralLink extends Component
{
    public string $referralLink = '';
    public array $referrals = [];
    public int $totalCoins = 0;
    public int $pendingCount = 0;
    public int $convertedCount = 0;
    public string $email = '';
    public bool $copied = false;
    public string $message = '';

    protected $rules = [
        'email' => 'required|email',
    ];

    public function mount()
    {
        if (auth()->check()) {
            $user = auth()->user();
            $this->referralLink = $user->referral_link;
            $this->totalCoins = $user->coins;
            
            $referralQuery = Referral::byReferrer($user->id);
            $this->referrals = $referralQuery->latest()->get()->toArray();
            $this->pendingCount = (clone $referralQuery)->where('status', 'pending')->count();
            $this->convertedCount = (clone $referralQuery)->whereIn('status', ['purchased', 'converted'])->count();
        }
    }

    public function copyLink()
    {
        $this->copied = true;
        $this->dispatch('linkCopied');
    }

    public function sendInvite()
    {
        $this->validate();

        Referral::create([
            'referrer_id' => auth()->id(),
            'email' => $this->email,
            'code' => auth()->user()->referral_code,
            'status' => 'pending',
        ]);

        $this->email = '';
        $this->message = 'Invitation sent successfully!';
        $this->mount(); // Refresh

        $this->dispatch('inviteSent');
    }

    public function render()
    {
        return view('livewire.referral-link');
    }
}
