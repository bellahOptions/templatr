<?php

namespace App\Livewire;

use App\Models\Advertisement;
use Livewire\Component;

class AdvertisementPopupModal extends Component
{
    public ?array $activeAd = null;

    public bool $show = false;

    public function mount()
    {
        $ad = Advertisement::active()->position('popup')->first();

        if (! $ad) {
            return;
        }

        $sessionKey = 'ad_popup_shown_'.$ad->id;

        if (session()->has($sessionKey)) {
            return;
        }

        $this->activeAd = $ad->toArray();
        $this->show = true;

        session([$sessionKey => true]);
    }

    public function dismiss()
    {
        $this->show = false;
    }

    public function render()
    {
        return view('livewire.advertisement-popup-modal');
    }
}
