<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurant_images', function (Blueprint $table) {
            $table->string('thumbnail_path')->nullable()->after('path');
            $table->unsignedInteger('width')->nullable()->after('sort_order');
            $table->unsignedInteger('height')->nullable()->after('width');
        });
    }

    public function down(): void
    {
        Schema::table('restaurant_images', function (Blueprint $table) {
            $table->dropColumn(['thumbnail_path', 'width', 'height']);
        });
    }
};
