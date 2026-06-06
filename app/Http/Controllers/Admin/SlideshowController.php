<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slideshow;
use App\Services\Media\CloudinaryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SlideshowController extends Controller
{
    public function index(): View
    {
        $slideshows = Slideshow::orderBy('sort_order')->orderBy('id')->get();

        return view('admin.slideshows.index', compact('slideshows'));
    }

    public function create(): View
    {
        return view('admin.slideshows.create');
    }

    public function store(Request $request, CloudinaryService $cloudinary): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'cta_text' => 'nullable|string|max:100',
            'cta_url' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $validated['image_url'] = $cloudinary->uploadImage($request->file('image'), 'slideshows', 1920, 1080);
        }

        unset($validated['image']);
        $validated['is_active'] = $request->boolean('is_active', true);

        Slideshow::create($validated);

        return redirect()->route('admin.slideshows.index')->with('success', 'Slide created successfully.');
    }

    public function edit(Slideshow $slideshow): View
    {
        return view('admin.slideshows.edit', compact('slideshow'));
    }

    public function update(Request $request, Slideshow $slideshow, CloudinaryService $cloudinary): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'cta_text' => 'nullable|string|max:100',
            'cta_url' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            if ($slideshow->image_url) {
                $cloudinary->delete($slideshow->image_url);
            }
            $validated['image_url'] = $cloudinary->uploadImage($request->file('image'), 'slideshows', 1920, 1080);
        }

        unset($validated['image']);
        $validated['is_active'] = $request->boolean('is_active', false);

        $slideshow->update($validated);

        return redirect()->route('admin.slideshows.index')->with('success', 'Slide updated successfully.');
    }

    public function destroy(Slideshow $slideshow, CloudinaryService $cloudinary): RedirectResponse
    {
        if ($slideshow->image_url) {
            $cloudinary->delete($slideshow->image_url);
        }

        $slideshow->delete();

        return redirect()->route('admin.slideshows.index')->with('success', 'Slide deleted successfully.');
    }
}
