<?php

namespace Database\Factories;

use App\Models\Area;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Area>
 */
class AreaFactory extends Factory
{
    public function definition(): array
    {
        $number = $this->faker->unique()->numberBetween(1000, 9999);

        return [
            'name_ar' => 'حي '.$number,
            'slug' => 'area-'.$number,
        ];
    }
}
