<?php

namespace Database\Factories;

use App\Models\Slideshow;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Slideshow>
 */
class SlideshowFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'cta_text' => $this->faker->randomElement(['Explore Now', 'Shop Now', 'Browse Collection', 'Get Started']),
            'cta_url' => '/products',
            'image_url' => null,
            'sort_order' => $this->faker->numberBetween(0, 10),
            'is_active' => true,
        ];
    }
}
