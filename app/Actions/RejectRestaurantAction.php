<?php

namespace App\Actions;

use App\Enums\RestaurantStatus;
use App\Models\Restaurant;
use Illuminate\Support\Facades\DB;

class RejectRestaurantAction
{
    public function execute(Restaurant $restaurant, string $reason): void
    {
        DB::transaction(function () use ($restaurant, $reason) {
            $restaurant->update([
                'status' => RestaurantStatus::REJECTED,
                'rejection_reason' => $reason,
            ]);
        });
    }
}
