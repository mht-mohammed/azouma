<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RestaurantImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'path',
        'is_cover',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_cover' => 'boolean',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * Public URL for the image, or a local SVG placeholder
     * when the file does not exist (seeded paths are placeholders).
     */
    public function getUrlAttribute(): string
    {
        if ($this->path && file_exists(public_path($this->path))) {
            return asset($this->path);
        }

        return asset('images/placeholder-restaurant.svg');
    }
}
