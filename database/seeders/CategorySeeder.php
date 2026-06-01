<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'WordPress Themes', 'slug' => 'wordpress-themes', 'description' => 'Premium WordPress themes for every need', 'icon' => 'wordpress', 'order' => 1],
            ['name' => 'HTML Templates', 'slug' => 'html-templates', 'description' => 'Responsive HTML website templates', 'icon' => 'code', 'order' => 2],
            ['name' => 'Graphics', 'slug' => 'graphics', 'description' => 'Stock photos, illustrations, and graphics', 'icon' => 'image', 'order' => 3],
            ['name' => 'UI Kits', 'slug' => 'ui-kits', 'description' => 'User interface design kits and templates', 'icon' => 'palette', 'order' => 4],
            ['name' => 'Fonts', 'slug' => 'fonts', 'description' => 'Professional fonts and typography', 'icon' => 'font', 'order' => 5],
            ['name' => 'Audio', 'slug' => 'audio', 'description' => 'Music, sound effects and audio templates', 'icon' => 'music', 'order' => 6],
            ['name' => 'Video', 'slug' => 'video', 'description' => 'Video templates, stock footage and motion graphics', 'icon' => 'video', 'order' => 7],
            ['name' => 'Plugins', 'slug' => 'plugins', 'description' => 'WordPress plugins, extensions and add-ons', 'icon' => 'puzzle', 'order' => 8],
            ['name' => '3D Assets', 'slug' => '3d-assets', 'description' => '3D models, textures and renderings', 'icon' => 'cube', 'order' => 9],
            ['name' => 'Print Templates', 'slug' => 'print-templates', 'description' => 'Flyers, brochures, business cards and more', 'icon' => 'file-text', 'order' => 10],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
