<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    // NOTE: no WithoutModelEvents here — Restaurant slugs are generated
    // by the model's "creating" event, which must stay enabled while seeding.

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            AreaSeeder::class,
            DemoUserSeeder::class,
            RestaurantSeeder::class,
        ]);
    }
}
