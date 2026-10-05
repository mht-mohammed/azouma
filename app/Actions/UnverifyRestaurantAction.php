<?php

namespace App\Actions;

use App\Models\Restaurant;
use Illuminate\Support\Facades\DB;

class UnverifyRestaurantAction
{
    public function execute(Restaurant $restaurant): void
    {
        DB::transaction(function () use ($restaurant) {
            $restaurant->update([
                'is_verified' => false,
                'last_verified_at' => null,
            ]);
        });
    }
}
