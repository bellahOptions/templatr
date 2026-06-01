<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Review;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin',
            'email' => 'admin@creativemarket.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'bio' => 'Platform administrator',
        ]);

        // Create author users
        $authors = [];
        $authorNames = [
            ['name' => 'CreativeStudio', 'email' => 'author1@creativemarket.com'],
            ['name' => 'DesignMaster', 'email' => 'author2@creativemarket.com'],
            ['name' => 'PixelPerfect', 'email' => 'author3@creativemarket.com'],
            ['name' => 'ArtVision', 'email' => 'author4@creativemarket.com'],
            ['name' => 'ThemeCraft', 'email' => 'author5@creativemarket.com'],
        ];

        foreach ($authorNames as $authorData) {
            $authors[] = User::create([
                'name' => $authorData['name'],
                'email' => $authorData['email'],
                'password' => Hash::make('password'),
                'role' => 'author',
                'bio' => 'Professional digital creator',
                'paypal_email' => $authorData['email'],
            ]);
        }

        // Create regular users
        User::create([
            'name' => 'John Doe',
            'email' => 'user@creativemarket.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Seed categories
        $this->call(CategorySeeder::class);

        $categories = Category::all();

        // Seed products
        $productData = [
            [
                'title' => 'Modern Business Pro - WordPress Theme',
                'description' => 'A powerful and versatile WordPress theme designed for modern businesses, agencies, and startups. Features include drag-and-drop page builder, multiple demos, WooCommerce support, and responsive design.',
                'price' => 59.00,
                'sale_price' => 39.00,
                'file_type' => 'template',
                'file_size' => 5242880,
                'is_featured' => true,
                'tags' => 'wordpress, business, modern, responsive, woocommerce',
                'version' => '2.5.0',
                'requirements' => 'WordPress 5.0+, PHP 7.4+',
            ],
            [
                'title' => 'Ultimate UI Dashboard Kit',
                'description' => 'Complete UI dashboard kit with 100+ components, 50+ pages, and dark mode support. Built with Figma and includes all source files.',
                'price' => 45.00,
                'sale_price' => null,
                'file_type' => 'graphic',
                'file_size' => 2097152,
                'is_featured' => true,
                'tags' => 'ui, dashboard, figma, components, design',
                'version' => '3.0.0',
                'requirements' => 'Figma (any version)',
            ],
            [
                'title' => 'Premium Icon Set - 5000+ Icons',
                'description' => 'A massive collection of 5000+ premium icons in multiple formats including SVG, PNG, and WebFont. Perfect for web and mobile applications.',
                'price' => 29.00,
                'sale_price' => 19.00,
                'file_type' => 'graphic',
                'file_size' => 15728640,
                'is_featured' => true,
                'tags' => 'icons, svg, webfont, design, ui',
                'version' => '2.0.0',
            ],
            [
                'title' => 'E-commerce HTML Template',
                'description' => 'A fully responsive e-commerce HTML template with 20+ pages, product listings, cart, checkout, and admin panel. Built with Bootstrap 5.',
                'price' => 35.00,
                'sale_price' => null,
                'file_type' => 'template',
                'file_size' => 4194304,
                'is_featured' => true,
                'tags' => 'html, ecommerce, bootstrap, template, responsive',
                'version' => '1.5.0',
            ],
            [
                'title' => 'Cinematic Video Intro Pack',
                'description' => 'Professional cinematic video intro templates with stunning visual effects. Compatible with After Effects and Premiere Pro.',
                'price' => 49.00,
                'sale_price' => 34.00,
                'file_type' => 'video',
                'file_size' => 104857600,
                'is_featured' => true,
                'tags' => 'video, intro, cinematic, after-effects, premiere',
                'version' => '1.0.0',
            ],
            [
                'title' => 'Elegant Serif Font Family',
                'description' => 'A beautiful serif font family with 12 weights, italic variants, and extended character support. Perfect for branding and editorial design.',
                'price' => 39.00,
                'sale_price' => null,
                'file_type' => 'font',
                'file_size' => 2097152,
                'is_featured' => false,
                'tags' => 'font, serif, typography, elegant, branding',
                'version' => '1.2.0',
            ],
            [
                'title' => 'SaaS Landing Page Kit',
                'description' => 'Modern SaaS landing page template with conversion-optimized sections. Includes Figma, Sketch, and HTML files.',
                'price' => 25.00,
                'sale_price' => 18.00,
                'file_type' => 'template',
                'file_size' => 3145728,
                'is_featured' => true,
                'tags' => 'saas, landing, template, figma, html',
                'version' => '2.0.0',
            ],
            [
                'title' => 'Ambient Electronic Music Pack',
                'description' => 'Royalty-free ambient electronic music tracks perfect for videos, podcasts, and games. 20 tracks included in WAV and MP3 formats.',
                'price' => 55.00,
                'sale_price' => null,
                'file_type' => 'audio',
                'file_size' => 262144000,
                'is_featured' => false,
                'tags' => 'music, ambient, electronic, royalty-free, audio',
                'version' => '1.0.0',
            ],
            [
                'title' => 'WooCommerce Product Plugin',
                'description' => 'Advanced WooCommerce plugin for product variations, custom fields, and dynamic pricing. Includes detailed documentation and 6 months support.',
                'price' => 69.00,
                'sale_price' => 49.00,
                'file_type' => 'plugin',
                'file_size' => 1048576,
                'is_featured' => true,
                'tags' => 'woocommerce, plugin, wordpress, ecommerce, products',
                'version' => '3.1.0',
                'requirements' => 'WordPress 5.5+, WooCommerce 5.0+, PHP 7.4+',
            ],
            [
                'title' => '3D Character Model - Fantasy Knight',
                'description' => 'Highly detailed 3D character model of a fantasy knight. Fully rigged and textured. Compatible with Blender, Maya, and Unity.',
                'price' => 89.00,
                'sale_price' => null,
                'file_type' => '3d',
                'file_size' => 52428800,
                'is_featured' => false,
                'tags' => '3d, character, knight, fantasy, model',
                'version' => '1.0.0',
            ],
            [
                'title' => 'Brand Identity Template Pack',
                'description' => 'Complete brand identity templates including logo mockups, business cards, letterheads, and social media kits. Editable in Illustrator and Photoshop.',
                'price' => 32.00,
                'sale_price' => 22.00,
                'file_type' => 'graphic',
                'file_size' => 8388608,
                'is_featured' => false,
                'tags' => 'brand, identity, logo, business-card, template',
                'version' => '2.1.0',
            ],
            [
                'title' => 'React Native Mobile App Template',
                'description' => 'Production-ready React Native template for e-commerce mobile apps. Includes authentication, product listings, cart, and payment integration.',
                'price' => 75.00,
                'sale_price' => null,
                'file_type' => 'template',
                'file_size' => 6291456,
                'is_featured' => true,
                'tags' => 'react-native, mobile, app, ecommerce, template',
                'version' => '1.0.0',
                'requirements' => 'Node.js 14+, React Native 0.64+',
            ],
        ];

        foreach ($productData as $index => $data) {
            $category = $categories[$index % count($categories)];
            $author = $authors[$index % count($authors)];

            Product::create([
                'category_id' => $category->id,
                'user_id' => $author->id,
                'title' => $data['title'],
                'slug' => Str::slug($data['title']) . '-' . Str::random(4),
                'description' => $data['description'],
                'price' => $data['price'],
                'sale_price' => $data['sale_price'],
                'file_type' => $data['file_type'],
                'file_size' => $data['file_size'],
                'is_featured' => $data['is_featured'],
                'is_published' => true,
                'tags' => $data['tags'] ? json_encode(explode(', ', $data['tags'])) : null,
                'version' => $data['version'] ?? '1.0.0',
                'requirements' => $data['requirements'] ?? null,
                'download_count' => rand(50, 5000),
                'view_count' => rand(100, 50000),
            ]);
        }

        // Create some reviews
        $products = Product::all();
        $users = User::where('role', 'user')->get();

        $reviewTexts = [
            'Amazing product! Exactly what I needed for my project.',
            'Great quality and excellent documentation. Highly recommended!',
            'Good value for money. The design is clean and professional.',
            'Perfect! The support team was very helpful with my questions.',
            'This is a fantastic resource. Will definitely purchase more from this author.',
            'Very well crafted and easy to use. Saved me hours of work.',
            'The quality exceeds my expectations. Five stars!',
            'Works perfectly. The code is clean and well-organized.',
            'Beautiful design. It looks exactly like the preview images.',
            'Excellent product with great attention to detail.',
        ];

        $usedCombinations = [];
        foreach ($products as $product) {
            $reviewCount = rand(2, 5);
            $availableUsers = $users->shuffle();
            $count = min($reviewCount, $availableUsers->count());
            for ($i = 0; $i < $count; $i++) {
                $user = $availableUsers[$i];
                $key = $product->id . '-' . $user->id;
                if (isset($usedCombinations[$key])) continue;
                $usedCombinations[$key] = true;
                Review::create([
                    'product_id' => $product->id,
                    'user_id' => $user->id,
                    'rating' => rand(3, 5),
                    'review' => $reviewTexts[array_rand($reviewTexts)],
                    'is_approved' => true,
                ]);
            }
        }
    }
}
