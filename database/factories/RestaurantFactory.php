<?php

namespace Database\Factories;

use App\Enums\OperatingStatus;
use App\Enums\RestaurantStatus;
use App\Enums\UserRole;
use App\Models\Area;
use App\Models\Category;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Restaurant>
 */
class RestaurantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'owner_id' => User::factory()->state(['role' => UserRole::OWNER]),
            'category_id' => Category::factory(),
            'area_id' => Area::factory(),
            'name' => 'مطعم '.$this->faker->word().' '.$this->faker->unique()->numberBetween(100, 999).' - تجريبي',
            'description' => $this->faker->sentence(),
            // Clearly fake phone numbers (00000X range) for demo data only.
            'phone' => '+970-59-0000'.$this->faker->numberBetween(100, 999),
            'whatsapp' => '+970-59-0000'.$this->faker->numberBetween(100, 999),
            'address' => 'غزة - '.$this->faker->streetAddress(),
            'price_range' => $this->faker->randomElement([1, 2, 3, null]),
            // Coordinates roughly inside Gaza City.
            'latitude' => $this->faker->randomFloat(7, 31.45, 31.55),
            'longitude' => $this->faker->randomFloat(7, 34.43, 34.48),
            'status' => RestaurantStatus::PENDING,
            'operating_status' => OperatingStatus::OPEN,
            'operating_status_updated_at' => now(),
            'is_verified' => false,
            'last_verified_at' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RestaurantStatus::APPROVED,
            'rejection_reason' => null,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RestaurantStatus::PENDING,
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RestaurantStatus::REJECTED,
            'rejection_reason' => 'بيانات غير مكتملة - بيانات تجريبية',
        ]);
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RestaurantStatus::APPROVED,
            'is_verified' => true,
            'last_verified_at' => now(),
        ]);
    }
}
