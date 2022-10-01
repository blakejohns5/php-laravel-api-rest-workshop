<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product; 
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = Category::factory(10)->create();
        foreach ($categories as $category) {
            Product::factory(5)->create([
                'category_id' => $category->id,
            ]);
        }
    }
}
