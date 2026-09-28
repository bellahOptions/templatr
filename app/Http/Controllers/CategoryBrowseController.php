<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;

class CategoryBrowseController extends Controller
{
    /**
     * SEO landing page for a single category.
     */
    public function show(Category $category)
    {
        $baseQuery = fn () => Product::published()->where('category_id', $category->id);

        $productCount = $baseQuery()->count();

        $topProducts = $baseQuery()
            ->withStats()
            ->with(['author'])
            ->orderByDesc('download_count')
            ->take(8)
            ->get();

        $relatedCategories = Category::withCount([
            'products' => fn ($query) => $query->where('is_published', true),
        ])->orderBy('order')->get();

        return view('category.show', compact(
            'category',
            'productCount',
            'topProducts',
            'relatedCategories'
        ));
    }
}
