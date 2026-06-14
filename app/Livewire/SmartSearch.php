<?php

namespace App\Livewire;

use App\Models\Product;
use Illuminate\Support\Collection;
use Livewire\Component;

class SmartSearch extends Component
{
    public string $query = '';

    public function mount(): void
    {
        $this->query = request()->string('search')->value();
    }

    public function render()
    {
        $suggestions = [];
        $results = new Collection;

        if (mb_strlen(trim($this->query)) >= 2) {
            $suggestions = Product::published()
                ->where('title', 'like', '%'.$this->query.'%')
                ->orderBy('download_count', 'desc')
                ->limit(5)
                ->pluck('title')
                ->toArray();

            $q = $this->query;
            $results = Product::published()
                ->where(function ($query) use ($q) {
                    $query->where('title', 'like', "%{$q}%")
                        ->orWhere('tags', 'like', "%{$q}%");
                })
                ->with('category')
                ->orderBy('download_count', 'desc')
                ->limit(4)
                ->get();
        }

        return view('livewire.smart-search', compact('suggestions', 'results'));
    }
}
