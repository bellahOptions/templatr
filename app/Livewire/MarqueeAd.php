<?php

namespace App\Livewire;

use App\Models\Advertisement;
use Livewire\Component;

class MarqueeAd extends Component
{
    public function render()
    {
        $ads = Advertisement::active()->position('marquee')->get();

        return view('livewire.marquee-ad', compact('ads'));
    }
}
