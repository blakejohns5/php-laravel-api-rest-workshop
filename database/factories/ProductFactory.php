<?php

namespace Database\Factories;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'description' => $this->faker->text(150),
            'price_in_cents' => $this->faker->randomNumber(3),
            // 'category_id' => Category::factory(),  // this way a category would be created for each product created
            // We can do it this way, or manually add a category for each product, by adding it to CategorySeeder (see CategorySeeder.php)
        ];
    }
}
