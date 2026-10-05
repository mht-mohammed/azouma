<?php

namespace App\Actions;

use App\Models\Restaurant;
use Illuminate\Support\Facades\DB;

class VerifyRestaurantAction
{
    public function execute(Restaurant $restaurant): void
    {
        DB::transaction(function () use ($restaurant) {
            $restaurant->update([
                'is_verified' => true,
                'last_verified_at' => now(),
            ]);
        });
    }
}
