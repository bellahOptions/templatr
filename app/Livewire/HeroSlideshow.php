<?php

namespace App\Livewire;

use App\Models\Slideshow;
use Illuminate\Support\Collection;
use Livewire\Component;

class HeroSlideshow extends Component
{
    public Collection $slides;

    public function mount(): void
    {
        $this->slides = Slideshow::active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function render()
    {
        return view('livewire.hero-slideshow');
    }
}
