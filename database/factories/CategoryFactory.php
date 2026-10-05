<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $number = $this->faker->unique()->numberBetween(1000, 9999);

        return [
            'name_ar' => 'تصنيف '.$number,
            'slug' => 'category-'.$number,
        ];
    }
}
