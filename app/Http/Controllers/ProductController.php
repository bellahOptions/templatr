<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::published()->with(['category', 'author']);

        // Category filter
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Type filter
        if ($request->filled('type')) {
            $query->where('file_type', $request->type);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('tags', 'like', "%{$search}%");
            });
        }

        // Price range
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Sort
        switch ($request->sort) {
            case 'price_asc':
                $query->orderBy('price');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'popular':
                $query->orderBy('download_count', 'desc');
                break;
            case 'rating':
                $query->withAvg('reviews', 'rating')->orderBy('reviews_avg_rating', 'desc');
                break;
            default:
                $query->latest();
        }

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::orderBy('order')->get();
        $types = ['graphic' => 'Graphics', 'template' => 'Templates', 'audio' => 'Audio', 'video' => 'Video', 'font' => 'Fonts', 'plugin' => 'Plugins', '3d' => '3D Assets'];

        return view('products.index', compact('products', 'categories', 'types'));
    }

    public function show(Product $product)
    {
        if (!$product->is_published) {
            abort(404);
        }

        $product->increment('view_count');
        $relatedProducts = Product::published()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['category', 'author'])
            ->latest()
            ->take(4)
            ->get();

        $reviews = $product->reviews()->with('user')->latest()->get();
        $userReview = Auth::check() ? $product->reviews()->where('user_id', Auth::id())->first() : null;
        $isInWishlist = Auth::check() ? Auth::user()->wishlists()->where('product_id', $product->id)->exists() : false;
        $hasPurchased = Auth::check() ? Auth::user()->orders()->whereHas('items', function ($q) use ($product) {
            $q->where('product_id', $product->id);
        })->where('payment_status', 'paid')->exists() : false;

        return view('products.show', compact('product', 'relatedProducts', 'reviews', 'userReview', 'isInWishlist', 'hasPurchased'));
    }

    public function download(Product $product)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $hasPurchased = Auth::user()->orders()->whereHas('items', function ($q) use ($product) {
            $q->where('product_id', $product->id);
        })->where('payment_status', 'paid')->exists();

        if (!$hasPurchased && !Auth::user()->isAdmin()) {
            return back()->with('error', 'You need to purchase this item first.');
        }

        $product->increment('download_count');

        // In a real app, this would return the actual file
        return back()->with('success', 'Download started!');
    }

    public function storeReview(Request $request, Product $product)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
        ]);

        $hasPurchased = Auth::user()->orders()->whereHas('items', function ($q) use ($product) {
            $q->where('product_id', $product->id);
        })->where('payment_status', 'paid')->exists();

        if (!$hasPurchased) {
            return back()->with('error', 'You can only review items you have purchased.');
        }

        Review::updateOrCreate(
            ['product_id' => $product->id, 'user_id' => Auth::id()],
            ['rating' => $validated['rating'], 'review' => $validated['review']]
        );

        return back()->with('success', 'Review submitted successfully!');
    }
}
