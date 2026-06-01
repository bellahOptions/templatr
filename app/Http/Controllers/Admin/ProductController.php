<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'author']);

        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        if ($request->filled('status')) {
            $query->where('is_published', $request->status === 'published');
        }

        $products = $query->latest()->paginate(15);
        $categories = Category::orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $authors = User::authors()->get();
        $types = ['graphic' => 'Graphics', 'template' => 'Templates', 'audio' => 'Audio', 'video' => 'Video', 'font' => 'Fonts', 'plugin' => 'Plugins', '3d' => '3D Assets'];

        return view('admin.products.create', compact('categories', 'authors', 'types'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'file_type' => 'required|string',
            'file_size' => 'nullable|integer',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'tags' => 'nullable|string',
            'demo_url' => 'nullable|url',
            'version' => 'nullable|string|max:50',
            'requirements' => 'nullable|string',
        ]);

        $validated['slug'] = Str::slug($validated['title']) . '-' . Str::random(5);
        $validated['tags'] = $request->tags ? json_encode(explode(',', $request->tags)) : null;

        Product::create($validated);

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        $authors = User::authors()->get();
        $types = ['graphic' => 'Graphics', 'template' => 'Templates', 'audio' => 'Audio', 'video' => 'Video', 'font' => 'Fonts', 'plugin' => 'Plugins', '3d' => '3D Assets'];

        return view('admin.products.edit', compact('product', 'categories', 'authors', 'types'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'file_type' => 'required|string',
            'file_size' => 'nullable|integer',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'tags' => 'nullable|string',
            'demo_url' => 'nullable|url',
            'version' => 'nullable|string|max:50',
            'requirements' => 'nullable|string',
        ]);

        $validated['tags'] = $request->tags ? json_encode(explode(',', $request->tags)) : null;

        $product->update($validated);

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }
}
