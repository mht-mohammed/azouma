<?php

namespace App\Models;

use App\Enums\OperatingStatus;
use App\Enums\RestaurantStatus;
use App\Support\ArabicSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Restaurant extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'category_id',
        'area_id',
        'name',
        'slug',
        'description',
        'phone',
        'whatsapp',
        'address',
        'price_range',
        'latitude',
        'longitude',
        'status',
        'rejection_reason',
        'operating_status',
        'operating_status_updated_at',
        'is_verified',
        'last_verified_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => RestaurantStatus::class,
            'operating_status' => OperatingStatus::class,
            'price_range' => 'integer',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_verified' => 'boolean',
            'operating_status_updated_at' => 'datetime',
            'last_verified_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // Slug is generated once on creation so public links never break
        // when the name is edited later.
        static::creating(function (Restaurant $restaurant) {
            if (empty($restaurant->slug)) {
                $restaurant->slug = static::generateUniqueSlug($restaurant->name ?? '');
            }
        });
    }

    public static function generateUniqueSlug(string $name): string
    {
        $base = ArabicSlug::make($name);

        if ($base === '') {
            $base = 'restaurant-'.Str::lower(Str::random(6));
        }

        $slug = $base;
        $counter = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(RestaurantImage::class)->orderBy('sort_order');
    }

    public function openingHours(): HasMany
    {
        return $this->hasMany(OpeningHour::class)->orderBy('day_of_week');
    }

    public function coverImage(): HasOne
    {
        return $this->hasOne(RestaurantImage::class)->where('is_cover', true);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', RestaurantStatus::APPROVED);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('operating_status', OperatingStatus::OPEN);
    }

    public function scopeInCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeInArea(Builder $query, int $areaId): Builder
    {
        return $query->where('area_id', $areaId);
    }
}
