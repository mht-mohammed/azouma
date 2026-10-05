<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Area extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_ar',
        'slug',
    ];

    protected static function booted(): void
    {
        // Keep the cached filter list fresh on every change.
        static::saved(fn () => Cache::forget('areas:list'));
        static::deleted(fn () => Cache::forget('areas:list'));
    }

    /**
     * Alphabetical list for filter dropdowns, cached until an
     * area is created, updated, or deleted.
     */
    public static function orderedList()
    {
        return Cache::rememberForever('areas:list', fn () => static::orderBy('name_ar')->get());
    }

    public function restaurants(): HasMany
    {
        return $this->hasMany(Restaurant::class);
    }
}
