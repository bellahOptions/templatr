<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\Storage\ProductFileStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProductController extends Controller
{
    public function __construct(protected ProductFileStorage $fileStorage) {}

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
            'title' => 'required|string|max:255|unique:products,title',
            'description' => 'required|string',
            'price' => 'required|integer|min:1',
            'sale_price' => 'nullable|integer|min:1|lt:price',
            'file_type' => 'required|string',
            'file_size' => 'nullable|numeric|min:0',
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
            'title.unique' => 'A product with this title already exists.',
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

        // Handle pre-uploaded product file (staged in the private temp area)
        if ($request->filled('file_temp_id')) {
            $tempData = $this->pullStagedUpload((string) $request->file_temp_id);
            if ($tempData) {
                // Prevent duplicate file upload
                if (Product::where('original_file_name', $tempData['original_name'])->exists()) {
                    return back()->withErrors(['file_path' => 'A product with this file already exists.'])->withInput();
                }

                if (! $this->storeStagedFile($validated, $tempData)) {
                    return back()->withErrors(['file_path' => 'The uploaded file could not be found. Please upload it again.'])->withInput();
                }
            }
        } elseif ($request->hasFile('file_path')) {
            $file = $request->file('file_path');
            $clientName = $file->getClientOriginalName();

            // Prevent duplicate file upload
            if (Product::where('original_file_name', $clientName)->exists()) {
                return back()->withErrors(['file_path' => 'A product with this file already exists.'])->withInput();
            }

            $stored = $this->storeUploadedOriginal($file, $clientName);

            $validated['file_path'] = $stored['path'];
            $validated['storage_disk'] = $stored['disk'];
            $validated['original_file_name'] = $clientName;
            if (empty($validated['file_size'])) {
                $validated['file_size'] = round($file->getSize() / 1048576, 2);
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
            'title' => 'required|string|max:255|unique:products,title,'.$product->id,
            'description' => 'required|string',
            'price' => 'required|integer|min:1',
            'sale_price' => 'nullable|integer|min:1|lt:price',
            'file_type' => 'required|string',
            'file_size' => 'nullable|numeric|min:0',
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
            'title.unique' => 'A product with this title already exists.',
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
            $this->deleteOriginal($product);
            $validated['file_path'] = null;
            $validated['storage_disk'] = null;
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
            $tempData = $this->pullStagedUpload((string) $request->file_temp_id);
            if ($tempData) {
                $this->deleteOriginal($product);
                $this->storeStagedFile($validated, $tempData);
            }
        } elseif ($request->hasFile('file_path')) {
            $this->deleteOriginal($product);
            $file = $request->file('file_path');
            $stored = $this->storeUploadedOriginal($file, $file->getClientOriginalName());

            $validated['file_path'] = $stored['path'];
            $validated['storage_disk'] = $stored['disk'];
            if (empty($validated['file_size'])) {
                $validated['file_size'] = round($file->getSize() / 1048576, 2);
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

        $this->deleteOriginal($product);

        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }

    /**
     * Store an uploaded purchased original on the private disk.
     *
     * @return array{path: string, disk: string}
     */
    protected function storeUploadedOriginal(UploadedFile $file, string $clientName): array
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: 'bin');

        return $this->fileStorage->storePrivate($file->getRealPath(), $clientName, $extension);
    }

    /**
     * Adopt a temp/chunk upload that was staged on the private disk.
     *
     * @param  array<string, mixed>  $tempData
     */
    protected function storeStagedFile(array &$validated, array $tempData): bool
    {
        $sourcePath = $this->fileStorage->safePath($tempData['path'] ?? null);
        $disk = (string) ($tempData['disk'] ?? $this->fileStorage->privateDiskName());
        $originalName = (string) ($tempData['original_name'] ?? 'asset');

        if (! $sourcePath || ! Storage::disk($disk)->exists($sourcePath)) {
            return false;
        }

        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION) ?: pathinfo($sourcePath, PATHINFO_EXTENSION) ?: 'bin');

        $stored = $this->fileStorage->adoptIntoPrivate($disk, $sourcePath, $originalName, $extension);

        $validated['file_path'] = $stored['path'];
        $validated['storage_disk'] = $stored['disk'];
        $validated['original_file_name'] = $originalName;

        if (empty($validated['file_size']) && isset($tempData['size'])) {
            $validated['file_size'] = round(((int) $tempData['size']) / 1048576, 2);
        }

        return true;
    }

    /**
     * Read and consume a staged upload record from the current session.
     *
     * @return array<string, mixed>|null
     */
    protected function pullStagedUpload(string $tempId): ?array
    {
        $key = 'upload_temp_'.$tempId;
        $data = session($key);

        if (! is_array($data)) {
            return null;
        }

        session()->forget($key);

        return $data;
    }

    /**
     * Delete a product's original from whichever disk actually holds it.
     */
    protected function deleteOriginal(Product $product): void
    {
        if (! $product->file_path) {
            return;
        }

        $diskName = $this->fileStorage->diskNameFor($product);
        $path = $this->fileStorage->safePath($product->file_path);

        if ($diskName && $path) {
            Storage::disk($diskName)->delete($path);
        }
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
