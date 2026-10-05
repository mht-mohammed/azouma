<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_ar',
        'slug',
    ];

    protected static function booted(): void
    {
        // Keep the cached filter list fresh on every change.
        static::saved(fn () => Cache::forget('categories:list'));
        static::deleted(fn () => Cache::forget('categories:list'));
    }

    /**
     * Alphabetical list for filter dropdowns, cached until a
     * category is created, updated, or deleted.
     */
    public static function orderedList()
    {
        return Cache::rememberForever('categories:list', fn () => static::orderBy('name_ar')->get());
    }

    public function restaurants(): HasMany
    {
        return $this->hasMany(Restaurant::class);
    }
}
