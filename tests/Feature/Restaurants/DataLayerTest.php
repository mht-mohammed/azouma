<?php

namespace Tests\Feature\Restaurants;

use App\Enums\OperatingStatus;
use App\Enums\RestaurantStatus;
use App\Enums\UserRole;
use App\Enums\Weekday;
use App\Models\Area;
use App\Models\Category;
use App\Models\OpeningHour;
use App\Models\Restaurant;
use App\Models\RestaurantImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataLayerTest extends TestCase
{
    use RefreshDatabase;

    public function test_restaurant_belongs_to_owner_category_and_area(): void
    {
        $restaurant = Restaurant::factory()->approved()->create();

        $this->assertInstanceOf(User::class, $restaurant->owner);
        $this->assertInstanceOf(Category::class, $restaurant->category);
        $this->assertInstanceOf(Area::class, $restaurant->area);
        $this->assertSame(UserRole::OWNER, $restaurant->owner->role);
    }

    public function test_restaurant_has_images_opening_hours_and_cover(): void
    {
        $restaurant = Restaurant::factory()->approved()->create();
        RestaurantImage::factory()->cover()->create(['restaurant_id' => $restaurant->id, 'sort_order' => 0]);
        RestaurantImage::factory()->create(['restaurant_id' => $restaurant->id, 'sort_order' => 1]);
        OpeningHour::factory()->create(['restaurant_id' => $restaurant->id, 'day_of_week' => Weekday::SATURDAY]);

        $this->assertCount(2, $restaurant->images);
        $this->assertCount(1, $restaurant->openingHours);
        $this->assertTrue($restaurant->coverImage->is_cover);
        $this->assertInstanceOf(Weekday::class, $restaurant->openingHours->first()->day_of_week);
    }

    public function test_category_area_and_user_have_many_restaurants(): void
    {
        $category = Category::factory()->create();
        $area = Area::factory()->create();
        $owner = User::factory()->create(['role' => UserRole::OWNER]);

        Restaurant::factory()->count(2)->create([
            'owner_id' => $owner->id,
            'category_id' => $category->id,
            'area_id' => $area->id,
        ]);

        $this->assertCount(2, $category->restaurants);
        $this->assertCount(2, $area->restaurants);
        $this->assertCount(2, $owner->restaurants);
    }

    public function test_approved_scope_only_returns_approved(): void
    {
        Restaurant::factory()->approved()->create();
        Restaurant::factory()->pending()->create();
        Restaurant::factory()->rejected()->create();

        $results = Restaurant::approved()->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->first()->status === RestaurantStatus::APPROVED);
    }

    public function test_open_scope_only_returns_open_operating_status(): void
    {
        Restaurant::factory()->approved()->create(['operating_status' => OperatingStatus::OPEN]);
        Restaurant::factory()->approved()->create(['operating_status' => OperatingStatus::TEMPORARILY_CLOSED]);
        Restaurant::factory()->approved()->create(['operating_status' => OperatingStatus::RELOCATED]);

        $this->assertCount(1, Restaurant::open()->get());
    }

    public function test_in_category_and_in_area_scopes_filter(): void
    {
        $wantedCategory = Category::factory()->create();
        $otherCategory = Category::factory()->create();
        $wantedArea = Area::factory()->create();
        $otherArea = Area::factory()->create();

        $wanted = Restaurant::factory()->approved()->create([
            'category_id' => $wantedCategory->id,
            'area_id' => $wantedArea->id,
        ]);
        Restaurant::factory()->approved()->create([
            'category_id' => $otherCategory->id,
            'area_id' => $otherArea->id,
        ]);

        $this->assertTrue(Restaurant::inCategory($wantedCategory->id)->get()->contains($wanted));
        $this->assertCount(1, Restaurant::inCategory($wantedCategory->id)->get());
        $this->assertTrue(Restaurant::inArea($wantedArea->id)->get()->contains($wanted));
        $this->assertCount(1, Restaurant::inArea($wantedArea->id)->get());
    }

    public function test_enum_casts_and_arabic_labels(): void
    {
        $restaurant = Restaurant::factory()->verified()->create([
            'operating_status' => OperatingStatus::TEMPORARILY_CLOSED,
        ]);

        $this->assertInstanceOf(RestaurantStatus::class, $restaurant->status);
        $this->assertInstanceOf(OperatingStatus::class, $restaurant->operating_status);
        $this->assertNotEmpty($restaurant->status->label());
        $this->assertNotEmpty($restaurant->operating_status->label());

        $admin = User::factory()->create(['role' => UserRole::ADMIN]);

        $this->assertInstanceOf(UserRole::class, $admin->role);
        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isOwner());
        $this->assertNotEmpty(Weekday::SATURDAY->label());
    }

    public function test_slug_generated_from_arabic_name(): void
    {
        $restaurant = Restaurant::factory()->create(['name' => 'مطعم الشاطئ']);

        $this->assertSame('مطعم-الشاطئ', $restaurant->slug);
        $this->assertSame('slug', $restaurant->getRouteKeyName());
    }

    public function test_slug_handles_mixed_arabic_and_latin(): void
    {
        $restaurant = Restaurant::factory()->create(['name' => 'مطعم Burger 12']);

        $this->assertSame('مطعم-burger-12', $restaurant->slug);
    }

    public function test_slug_strips_diacritics(): void
    {
        $plain = Restaurant::factory()->create(['name' => 'مطعم الشاطئ فرع 1']);
        $withDiacritics = Restaurant::factory()->create(['name' => 'مَطْعَمُ الشَّاطِئ فرع 2']);

        // Same letters produce the same base slug (different unique names here).
        $this->assertStringStartsWith('مطعم-الشاطئ', $plain->slug);
        $this->assertStringStartsWith('مطعم-الشاطئ', $withDiacritics->slug);
    }

    public function test_duplicate_names_get_numeric_suffix(): void
    {
        $first = Restaurant::factory()->create(['name' => 'مطعم الشاطئ']);
        $second = Restaurant::factory()->create(['name' => 'مطعم الشاطئ']);

        $this->assertSame('مطعم-الشاطئ', $first->slug);
        $this->assertSame('مطعم-الشاطئ-2', $second->slug);
    }

    public function test_empty_slug_falls_back_to_restaurant_prefix(): void
    {
        $restaurant = Restaurant::factory()->create(['name' => '!!!']);

        $this->assertStringStartsWith('restaurant-', $restaurant->slug);
    }

    public function test_slug_does_not_change_when_name_is_updated(): void
    {
        $restaurant = Restaurant::factory()->create(['name' => 'مطعم الشاطئ']);
        $originalSlug = $restaurant->slug;

        $restaurant->update(['name' => 'اسم جديد تماما']);

        $this->assertSame($originalSlug, $restaurant->fresh()->slug);
    }

    public function test_seeder_creates_expected_demo_data(): void
    {
        $this->seed();

        $this->assertSame(6, Category::count());
        $this->assertSame(8, Area::count());
        $this->assertSame(4, User::count());
        $this->assertSame(1, User::where('role', UserRole::ADMIN)->count());
        $this->assertSame(2, User::where('role', UserRole::OWNER)->count());
        $this->assertSame(1, User::where('role', UserRole::CUSTOMER)->count());

        $this->assertSame(12, Restaurant::count());
        $this->assertSame(9, Restaurant::where('status', RestaurantStatus::APPROVED)->count());
        $this->assertSame(2, Restaurant::where('status', RestaurantStatus::PENDING)->count());
        $this->assertSame(1, Restaurant::where('status', RestaurantStatus::REJECTED)->count());
        $this->assertSame(1, Restaurant::where('operating_status', OperatingStatus::TEMPORARILY_CLOSED)->count());
        $this->assertSame(1, Restaurant::where('operating_status', OperatingStatus::RELOCATED)->count());
        $this->assertSame(3, Restaurant::where('is_verified', true)->count());

        $rejected = Restaurant::where('status', RestaurantStatus::REJECTED)->first();
        $this->assertNotEmpty($rejected->rejection_reason);

        // Every restaurant has a full week of hours, 2+ images and exactly one cover.
        foreach (Restaurant::with(['images', 'openingHours'])->get() as $restaurant) {
            $this->assertCount(7, $restaurant->openingHours);
            $this->assertGreaterThanOrEqual(2, $restaurant->images->count());
            $this->assertCount(1, $restaurant->images->where('is_cover', true));
        }

        // One restaurant closes after midnight (overnight hours).
        $this->assertTrue(
            OpeningHour::whereColumn('closes_at', '<', 'opens_at')->exists(),
            'Expected one overnight shift where closes_at is earlier than opens_at.'
        );

        // Some restaurants have a closed day.
        $this->assertTrue(OpeningHour::where('is_closed', true)->exists());
    }
}
