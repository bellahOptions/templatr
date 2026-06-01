<?php

namespace App\Livewire;

use App\Models\Advertisement;
use Livewire\Component;

class BannerAd extends Component
{
    public string $position = 'banner';
    public ?array $ad = null;

    public function mount(string $position = 'banner')
    {
        $this->position = $position;
        $ad = Advertisement::active()->position($position)->first();
        if ($ad) {
            $this->ad = $ad->toArray();
        }
    }

    public function render()
    {
        return view('livewire.banner-ad');
    }
}
