<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\UserActivity;
use Livewire\Component;

class ProductSuggestions extends Component
{
    public int $limit = 8;
    public array $products = [];

    public function mount()
    {
        $user = auth()->user();
        $this->products = UserActivity::getRecommendationsForUser($user, $this->limit)
            ->toArray();
    }

    public function render()
    {
        return view('livewire.product-suggestions');
    }
}
