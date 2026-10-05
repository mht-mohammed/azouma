<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Locations are conveyed by the owner's written address (maps are
     * not useful in Gaza right now), so coordinates become optional.
     *
     * Re-adds the columns instead of using change() so the migration
     * works without doctrine/dbal on both MySQL and SQLite.
     */
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });

        Schema::table('restaurants', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('price_range');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });

        Schema::table('restaurants', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->after('price_range');
            $table->decimal('longitude', 10, 7)->after('latitude');
        });
    }
};
