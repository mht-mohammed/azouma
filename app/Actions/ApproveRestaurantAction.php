<?php

namespace App\Actions;

use App\Enums\RestaurantStatus;
use App\Models\Restaurant;
use Illuminate\Support\Facades\DB;

class ApproveRestaurantAction
{
    public function execute(Restaurant $restaurant): void
    {
        DB::transaction(function () use ($restaurant) {
            $restaurant->update([
                'status' => RestaurantStatus::APPROVED,
                'rejection_reason' => null,
            ]);
        });
    }
}
