<?php

namespace Database\Factories;

use App\Enums\ReportReason;
use App\Enums\ReportStatus;
use App\Models\Report;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Report>
 */
class ReportFactory extends Factory
{
    public function definition(): array
    {
        return [
            'restaurant_id' => Restaurant::factory(),
            'reason' => $this->faker->randomElement(ReportReason::cases()),
            'message' => 'معلومات تحتاج مراجعة - بلاغ تجريبي: '.$this->faker->sentence(),
            'reporter_contact' => null,
            'status' => ReportStatus::NEW,
        ];
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReportStatus::RESOLVED,
        ]);
    }
}
