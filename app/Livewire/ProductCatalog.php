<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use Livewire\Component;

class ProductCatalog extends Component
{
    public string $search = '';

    public string $category = '';

    public string $type = '';

    public string $sort = '';

    public string $minPrice = '';

    public string $maxPrice = '';

    public int $perPage = 12;

    /**
     * Set when the catalogue is mounted on a category landing page.
     * The category then comes from the URL path and cannot be changed here.
     */
    public string $lockedCategory = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'category' => ['except' => ''],
        'type' => ['except' => ''],
        'sort' => ['except' => ''],
        'minPrice' => ['except' => '', 'as' => 'min_price'],
        'maxPrice' => ['except' => '', 'as' => 'max_price'],
    ];

    public function mount(string $category = ''): void
    {
        if ($category !== '') {
            $this->category = $category;
            $this->lockedCategory = $category;
        }
    }

    public function updatingSearch(): void
    {
        $this->perPage = 12;
    }

    public function updatingCategory(): void
    {
        $this->perPage = 12;
    }

    public function updatingType(): void
    {
        $this->perPage = 12;
    }

    public function updatingSort(): void
    {
        $this->perPage = 12;
    }

    public function loadMore(): void
    {
        $this->perPage += 12;
    }

    public function setCategory(string $slug): void
    {
        $this->category = $this->category === $slug ? '' : $slug;
        $this->perPage = 12;
    }

    /**
     * The category currently filtering the catalogue — either locked from the
     * route (category landing page) or chosen through the filters UI.
     */
    public function activeCategory(): string
    {
        return $this->lockedCategory !== '' ? $this->lockedCategory : $this->category;
    }

    public function setType(string $fileType): void
    {
        $this->type = $this->type === $fileType ? '' : $fileType;
        $this->perPage = 12;
    }

    public function applyPriceFilter(): void
    {
        $this->perPage = 12;
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->category = '';
        $this->type = '';
        $this->sort = '';
        $this->minPrice = '';
        $this->maxPrice = '';
        $this->perPage = 12;
    }

    public function render()
    {
        $query = Product::published();

        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('tags', 'like', "%{$search}%");
            });
        }

        if ($this->activeCategory() !== '') {
            $category = $this->activeCategory();
            $query->whereHas('category', fn ($q) => $q->where('slug', $category));
        }

        if ($this->type !== '') {
            $query->where('file_type', $this->type);
        }

        if ($this->minPrice !== '') {
            $min = (int) $this->minPrice;
            $query->where(function ($q) use ($min) {
                $q->where('sale_price', '>=', $min)
                    ->orWhere(function ($q2) use ($min) {
                        $q2->whereNull('sale_price')->where('price', '>=', $min);
                    });
            });
        }

        if ($this->maxPrice !== '') {
            $max = (int) $this->maxPrice;
            $query->where(function ($q) use ($max) {
                $q->where('sale_price', '<=', $max)
                    ->orWhere(function ($q2) use ($max) {
                        $q2->whereNull('sale_price')->where('price', '<=', $max);
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
                $query->orderBy('download_count', 'desc');
                break;
            default:
                $query->latest();
        }

        $total = (clone $query)->count();
        $products = $query->withStats()->with(['category', 'author'])->take($this->perPage)->get();
        $hasMore = $products->count() < $total;

        $categories = Category::withCount([
            'products' => fn ($q) => $q->where('is_published', true),
        ])->orderBy('order')->get();
        $types = [
            'graphic' => 'Graphics',
            'template' => 'Templates',
            'audio' => 'Audio',
            'video' => 'Video',
            'font' => 'Fonts',
            'plugin' => 'Plugins',
            '3d' => '3D Assets',
        ];
        $activeCategory = $this->activeCategory();

        return view('livewire.product-catalog', compact('products', 'total', 'hasMore', 'categories', 'types', 'activeCategory'));
    }
}
