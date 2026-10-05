<?php

namespace Database\Factories;

use App\Enums\Weekday;
use App\Models\OpeningHour;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OpeningHour>
 */
class OpeningHourFactory extends Factory
{
    public function definition(): array
    {
        return [
            'restaurant_id' => Restaurant::factory(),
            'day_of_week' => $this->faker->randomElement(Weekday::cases()),
            'opens_at' => '09:00:00',
            'closes_at' => '23:00:00',
            'is_closed' => false,
        ];
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'opens_at' => null,
            'closes_at' => null,
            'is_closed' => true,
        ]);
    }

    public function overnight(): static
    {
        return $this->state(fn (array $attributes) => [
            'opens_at' => '18:00:00',
            'closes_at' => '02:00:00',
            'is_closed' => false,
        ]);
    }
}
