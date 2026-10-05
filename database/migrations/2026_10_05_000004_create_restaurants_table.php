<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('area_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('address')->nullable();
            $table->unsignedTinyInteger('price_range')->nullable();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('status')->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->string('operating_status')->default('open');
            $table->timestamp('operating_status_updated_at')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamps();

            // category_id and area_id are already indexed by their foreign keys.
            $table->index('status');
            $table->index('operating_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurants');
    }
};
