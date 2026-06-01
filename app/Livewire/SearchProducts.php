<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class SearchProducts extends Component
{
    use WithPagination;

    public string $search = '';
    public ?string $category = null;
    public ?string $type = null;
    public ?string $sort = '';
    public ?int $minPrice = null;
    public ?int $maxPrice = null;

    protected $queryString = ['search', 'category', 'type', 'sort', 'minPrice', 'maxPrice'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategory()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Product::query()->where('is_published', true);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'LIKE', "%{$this->search}%")
                  ->orWhere('description', 'LIKE', "%{$this->search}%")
                  ->orWhere('tags', 'LIKE', "%{$this->search}%");
            });
        }

        if ($this->category) {
            $query->whereHas('category', fn($q) => $q->where('slug', $this->category));
        }

        if ($this->type) {
            $query->where('file_type', $this->type);
        }

        if ($this->minPrice) {
            $query->where(function ($q) {
                $q->where('sale_price', '>=', $this->minPrice)
                  ->orWhere(function ($q2) {
                      $q2->whereNull('sale_price')->where('price', '>=', $this->minPrice);
                  });
            });
        }

        if ($this->maxPrice) {
            $query->where(function ($q) {
                $q->where('sale_price', '<=', $this->maxPrice)
                  ->orWhere(function ($q2) {
                      $q2->whereNull('sale_price')->where('price', '<=', $this->maxPrice);
                  });
            });
        }

        switch ($this->sort) {
            case 'price_asc':
                $query->orderByRaw('COALESCE(sale_price, price) ASC');
                break;
            case 'price_desc':
                $query->orderByRaw('COALESCE(sale_price, price) DESC');
                break;
            case 'popular':
                $query->orderBy('download_count', 'DESC');
                break;
            default:
                $query->latest();
        }

        $products = $query->paginate(12);

        return view('livewire.search-products', [
            'products' => $products,
        ]);
    }
}
