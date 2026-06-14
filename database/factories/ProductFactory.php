<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->words(3, true);

        return [
            'category_id' => fn () => Category::create([
                'name' => fake()->unique()->word(),
                'slug' => Str::slug(fake()->unique()->word()),
            ]),
            'user_id' => User::factory(),
            'title' => ucwords($title),
            'slug' => Str::slug($title).'-'.Str::random(5),
            'description' => fake()->paragraph(),
            'price' => fake()->numberBetween(500, 9999),
            'sale_price' => null,
            'file_type' => fake()->randomElement(['graphic', 'template', 'audio', 'video', 'font', 'plugin', '3d']),
            'is_published' => true,
            'is_featured' => false,
        ];
    }
}
