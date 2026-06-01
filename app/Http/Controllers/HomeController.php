<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;

class HomeController extends Controller
{
    public function index()
    {
        $featuredProducts = Product::published()->featured()->with(['category', 'author'])->latest()->take(8)->get();
        $newProducts = Product::published()->with(['category', 'author'])->latest()->take(12)->get();
        $categories = Category::orderBy('order')->get();
        $topAuthors = \App\Models\User::authors()->withCount('products')->having('products_count', '>', 0)->orderBy('products_count', 'desc')->take(6)->get();

        return view('home', compact('featuredProducts', 'newProducts', 'categories', 'topAuthors'));
    }
}
