<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

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
            'price' => 'required|integer|min:1',
            'sale_price' => 'nullable|integer|min:1|lt:price',
            'file_type' => 'required|string',
            'file_size' => 'nullable|integer',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'tags' => 'nullable|string',
            'demo_url' => 'nullable|url',
            'version' => 'nullable|string|max:50',
            'requirements' => 'nullable|string',
            'features' => 'nullable|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'preview_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'file_path' => 'nullable|file|mimes:zip,rar,tar,gz,psd,ai,svg,mp3,wav,mp4,ttf,otf|max:102400',
        ], [
            'sale_price.lt' => 'The sale price must be less than the regular price.',
        ]);

        $validated['slug'] = Str::slug($validated['title']).'-'.Str::random(5);
        $validated['tags'] = $request->tags ? json_encode(explode(',', $request->tags)) : null;
        $validated['features'] = $request->features
            ? array_values(array_filter(array_map('trim', explode("\n", $request->features))))
            : null;

        // Handle pre-uploaded Cloudinary thumbnail URL
        if ($request->filled('cloudinary_thumbnail_url')) {
            $validated['thumbnail'] = $request->cloudinary_thumbnail_url;
        } elseif ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $this->uploadOptimizedImage($request->file('thumbnail'), 'products/thumbnails', 600, 450);
        }

        // Handle pre-uploaded Cloudinary preview URL
        if ($request->filled('cloudinary_preview_url')) {
            $validated['preview_image'] = $request->cloudinary_preview_url;
        } elseif ($request->hasFile('preview_image')) {
            $validated['preview_image'] = $this->uploadOptimizedImage($request->file('preview_image'), 'products/previews', 1200, 900);
        }

        // Handle pre-uploaded product file (temp storage)
        if ($request->filled('file_temp_id')) {
            $tempData = session('upload_temp_'.$request->file_temp_id);
            if ($tempData) {
                $ext = pathinfo($tempData['original_name'], PATHINFO_EXTENSION);
                $sanitizedName = Str::slug(pathinfo($tempData['original_name'], PATHINFO_FILENAME)).'-'.Str::random(6).'.'.$ext;
                $finalPath = 'products/files/'.$sanitizedName;
                Storage::disk('public')->move($tempData['path'], $finalPath);
                $validated['file_path'] = $finalPath;
                if (empty($validated['file_size'])) {
                    $validated['file_size'] = $tempData['size'];
                }
                session()->forget('upload_temp_'.$request->file_temp_id);
            }
        } elseif ($request->hasFile('file_path')) {
            $file = $request->file('file_path');
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $sanitizedName = Str::slug($originalName).'-'.Str::random(6).'.'.$file->getClientOriginalExtension();
            $validated['file_path'] = $file->storeAs('products/files', $sanitizedName, 'public');
            if (empty($validated['file_size'])) {
                $validated['file_size'] = $file->getSize();
            }
        }

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
            'price' => 'required|integer|min:1',
            'sale_price' => 'nullable|integer|min:1|lt:price',
            'file_type' => 'required|string',
            'file_size' => 'nullable|integer',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'tags' => 'nullable|string',
            'demo_url' => 'nullable|url',
            'version' => 'nullable|string|max:50',
            'requirements' => 'nullable|string',
            'features' => 'nullable|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'preview_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'file_path' => 'nullable|file|mimes:zip,rar,tar,gz,psd,ai,svg,mp3,wav,mp4,ttf,otf|max:102400',
            'remove_thumbnail' => 'nullable|boolean',
            'remove_preview' => 'nullable|boolean',
            'remove_file' => 'nullable|boolean',
        ], [
            'sale_price.lt' => 'The sale price must be less than the regular price.',
        ]);

        $validated['tags'] = $request->tags ? json_encode(explode(',', $request->tags)) : null;
        $validated['features'] = $request->features
            ? array_values(array_filter(array_map('trim', explode("\n", $request->features))))
            : null;

        // Handle thumbnail removal
        if ($request->boolean('remove_thumbnail') && $product->thumbnail) {
            Storage::disk('public')->delete($product->thumbnail);
            $validated['thumbnail'] = null;
        }

        // Handle preview removal
        if ($request->boolean('remove_preview') && $product->preview_image) {
            Storage::disk('public')->delete($product->preview_image);
            $validated['preview_image'] = null;
        }

        // Handle file removal
        if ($request->boolean('remove_file') && $product->file_path) {
            Storage::disk('public')->delete($product->file_path);
            $validated['file_path'] = null;
        }

        // Handle new thumbnail (Cloudinary pre-upload or direct)
        if ($request->filled('cloudinary_thumbnail_url')) {
            if ($product->thumbnail && ! str_starts_with($product->thumbnail, 'http')) {
                Storage::disk('public')->delete($product->thumbnail);
            }
            $validated['thumbnail'] = $request->cloudinary_thumbnail_url;
        } elseif ($request->hasFile('thumbnail')) {
            if ($product->thumbnail && ! str_starts_with($product->thumbnail, 'http')) {
                Storage::disk('public')->delete($product->thumbnail);
            }
            $validated['thumbnail'] = $this->uploadOptimizedImage($request->file('thumbnail'), 'products/thumbnails', 600, 450);
        }

        // Handle new preview image (Cloudinary pre-upload or direct)
        if ($request->filled('cloudinary_preview_url')) {
            if ($product->preview_image && ! str_starts_with($product->preview_image, 'http')) {
                Storage::disk('public')->delete($product->preview_image);
            }
            $validated['preview_image'] = $request->cloudinary_preview_url;
        } elseif ($request->hasFile('preview_image')) {
            if ($product->preview_image && ! str_starts_with($product->preview_image, 'http')) {
                Storage::disk('public')->delete($product->preview_image);
            }
            $validated['preview_image'] = $this->uploadOptimizedImage($request->file('preview_image'), 'products/previews', 1200, 900);
        }

        // Handle new product file (pre-uploaded temp or direct)
        if ($request->filled('file_temp_id')) {
            $tempData = session('upload_temp_'.$request->file_temp_id);
            if ($tempData) {
                if ($product->file_path) {
                    Storage::disk('public')->delete($product->file_path);
                }
                $ext = pathinfo($tempData['original_name'], PATHINFO_EXTENSION);
                $sanitizedName = Str::slug(pathinfo($tempData['original_name'], PATHINFO_FILENAME)).'-'.Str::random(6).'.'.$ext;
                $finalPath = 'products/files/'.$sanitizedName;
                Storage::disk('public')->move($tempData['path'], $finalPath);
                $validated['file_path'] = $finalPath;
                if (empty($validated['file_size'])) {
                    $validated['file_size'] = $tempData['size'];
                }
                session()->forget('upload_temp_'.$request->file_temp_id);
            }
        } elseif ($request->hasFile('file_path')) {
            if ($product->file_path) {
                Storage::disk('public')->delete($product->file_path);
            }
            $file = $request->file('file_path');
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $sanitizedName = Str::slug($originalName).'-'.Str::random(6).'.'.$file->getClientOriginalExtension();
            $validated['file_path'] = $file->storeAs('products/files', $sanitizedName, 'public');
            if (empty($validated['file_size'])) {
                $validated['file_size'] = $file->getSize();
            }
        }

        $product->update($validated);

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        // Clean up associated files
        if ($product->thumbnail) {
            Storage::disk('public')->delete($product->thumbnail);
        }
        if ($product->preview_image) {
            Storage::disk('public')->delete($product->preview_image);
        }
        if ($product->file_path) {
            Storage::disk('public')->delete($product->file_path);
        }

        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }

    /**
     * Upload and optimize an image with secure validation.
     */
    private function uploadOptimizedImage($file, string $path, int $width, int $height): string
    {
        // Generate a secure, unique filename
        $filename = Str::random(20).'.webp';
        $storagePath = "{$path}/{$filename}";

        try {
            // Use Intervention Image for optimization (if installed)
            if (class_exists('Intervention\Image\ImageManager')) {
                $manager = new ImageManager(new Driver);
                $image = $manager->read($file->getRealPath());

                // Resize maintaining aspect ratio, crop to fit
                $image->cover($width, $height);

                // Encode as WebP for optimal compression
                $encoded = $image->toWebp(80);

                Storage::disk('public')->put($storagePath, $encoded);
            } else {
                // Fallback: store original with sanitized name
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $sanitizedName = Str::slug($originalName).'-'.Str::random(8).'.'.$file->getClientOriginalExtension();
                $storagePath = "{$path}/{$sanitizedName}";
                $file->storeAs($path, basename($storagePath), 'public');
            }

            return $storagePath;
        } catch (\Exception $e) {
            // Fallback to simple store
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $sanitizedName = Str::slug($originalName).'-'.Str::random(8).'.'.$file->getClientOriginalExtension();
            $storagePath = "{$path}/{$sanitizedName}";
            $file->storeAs($path, basename($storagePath), 'public');

            return $storagePath;
        }
    }
}
