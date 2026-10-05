<?php

namespace App\Models;

use App\Enums\OperatingStatus;
use App\Enums\RestaurantStatus;
use App\Support\ArabicSlug;
use Carbon\Carbon;
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

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        return $query->where('name', 'like', "%{$term}%");
    }

    /**
     * Is the restaurant open right now in Gaza time?
     *
     * Handles closed days and shifts that end after midnight
     * (a closing time earlier than the opening time).
     */
    public function isOpenNow(?Carbon $now = null): bool
    {
        $now ??= now();

        // Carbon: Sunday=0 … Saturday=6. Ours: Saturday=0 … Friday=6.
        $dayNumber = ($now->dayOfWeek + 1) % 7;

        $today = $this->openingHours->first(
            fn (OpeningHour $hour) => $hour->day_of_week->value === $dayNumber
        );

        if (! $today || $today->is_closed || ! $today->opens_at || ! $today->closes_at) {
            return false;
        }

        $time = $now->format('H:i:s');
        $opens = substr((string) $today->opens_at, 0, 8);
        $closes = substr((string) $today->closes_at, 0, 8);

        if ($closes <= $opens) {
            return $time >= $opens || $time < $closes;
        }

        return $time >= $opens && $time < $closes;
    }

    /**
     * When the operating status was last changed (falls back to updated_at).
     */
    public function statusUpdatedAt(): Carbon
    {
        return $this->operating_status_updated_at ?? $this->updated_at;
    }

    public function coverUrl(): string
    {
        return $this->coverImage?->url ?? asset('images/placeholder-restaurant.svg');
    }

    /**
     * Click-to-chat link. Keeps digits only so stored formats like
     * "+970-59-0000010" or "00970…" both work.
     */
    public function whatsappUrl(): ?string
    {
        $digits = (string) preg_replace('/\D/', '', $this->whatsapp ?? '');
        $digits = (string) preg_replace('/^00/', '', $digits);

        return $digits === '' ? null : 'https://wa.me/'.$digits;
    }
}
