<?php

namespace App\Actions;

use App\Enums\RestaurantStatus;
use App\Models\Restaurant;
use Illuminate\Support\Facades\DB;

class UpdateRestaurantAction
{
    /**
     * Fields that force an approved restaurant back to pending review.
     *
     * @var array<int, string>
     */
    private const CRITICAL_FIELDS = [
        'name',
        'category_id',
        'area_id',
        'latitude',
        'longitude',
        'phone',
    ];

    /**
     * Update the restaurant. Returns true when the edit triggered re-approval.
     */
    public function execute(Restaurant $restaurant, array $data): bool
    {
        $needsReapproval = $restaurant->status === RestaurantStatus::APPROVED
            && $this->criticalFieldsChanged($restaurant, $data);

        DB::transaction(function () use ($restaurant, $data, $needsReapproval) {
            $restaurant->update(array_merge(
                $data,
                $needsReapproval
                    ? ['status' => RestaurantStatus::PENDING, 'rejection_reason' => null]
                    : []
            ));
        });

        return $needsReapproval;
    }

    private function criticalFieldsChanged(Restaurant $restaurant, array $data): bool
    {
        foreach (self::CRITICAL_FIELDS as $field) {
            if (! array_key_exists($field, $data)) {
                continue;
            }

            if ($this->valuesDiffer($restaurant->getAttribute($field), $data[$field])) {
                return true;
            }
        }

        return false;
    }

    private function valuesDiffer(mixed $current, mixed $incoming): bool
    {
        // Coordinates come from text inputs, so compare numerically
        // ("31.5186" must equal the stored 31.5186000).
        if (is_numeric($current) || is_numeric($incoming)) {
            return abs((float) $current - (float) $incoming) > 0.0000001;
        }

        return (string) $current !== (string) $incoming;
    }
}
