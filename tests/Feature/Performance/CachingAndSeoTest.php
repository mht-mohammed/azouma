<?php

namespace Tests\Feature\Performance;

use App\Enums\UserRole;
use App\Models\Area;
use App\Models\Category;
use App\Models\OpeningHour;
use App\Models\Restaurant;
use App\Models\RestaurantImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CachingAndSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_and_area_lists_are_cached_and_invalidated(): void
    {
        Category::factory()->count(3)->create();
        Area::factory()->count(2)->create();
        $first = Category::orderedList();
        $this->assertTrue(Cache::has('categories:list'));

        // Second call serves the cache (same instance count, no new query needed).
        $this->assertCount($first->count(), Category::orderedList());

        Category::first()->update(['name_ar' => 'اسم محدث للكاش']);

        $this->assertFalse(Cache::has('categories:list'));
        $this->assertTrue(Category::orderedList()->contains('name_ar', 'اسم محدث للكاش'));

        Area::orderedList();
        $this->assertTrue(Cache::has('areas:list'));
        Area::first()->delete();
        $this->assertFalse(Cache::has('areas:list'));
    }

    public function test_admin_pending_page_has_no_n_plus_one(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        foreach (Restaurant::factory()->pending()->count(4)->create() as $restaurant) {
            RestaurantImage::factory()->cover()->create(['restaurant_id' => $restaurant->id]);
        }

        $count = 0;
        DB::listen(function () use (&$count) {
            $count++;
        });

        $this->actingAs($admin)->get('/admin/restaurants/pending')->assertOk();

        $this->assertLessThan(15, $count);
    }

    public function test_owner_dashboard_has_no_n_plus_one(): void
    {
        $owner = User::factory()->create(['role' => UserRole::OWNER]);
        $restaurant = Restaurant::factory()->approved()->create(['owner_id' => $owner->id]);
        RestaurantImage::factory()->count(2)->create(['restaurant_id' => $restaurant->id]);
        for ($day = 0; $day <= 6; $day++) {
            OpeningHour::factory()->create(['restaurant_id' => $restaurant->id, 'day_of_week' => $day]);
        }

        $count = 0;
        DB::listen(function () use (&$count) {
            $count++;
        });

        $this->actingAs($owner)->get('/owner')->assertOk();

        $this->assertLessThan(15, $count);
    }

    public function test_sitemap_lists_only_approved_restaurants(): void
    {
        $approved = Restaurant::factory()->approved()->create();
        $pending = Restaurant::factory()->pending()->create();

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/xml; charset=UTF-8');
        $response->assertSee(route('restaurants.show', $approved), false);
        $response->assertDontSee($pending->slug);
    }

    public function test_robots_blocks_private_areas(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertSee('Disallow: /admin/', false);
        $response->assertSee('Disallow: /owner/', false);
        $response->assertSee('/sitemap.xml', false);
    }

    public function test_details_page_has_meta_and_structured_data(): void
    {
        $restaurant = Restaurant::factory()->approved()->create();
        OpeningHour::factory()->create([
            'restaurant_id' => $restaurant->id,
            'day_of_week' => 0,
            'opens_at' => '09:00:00',
            'closes_at' => '23:00:00',
            'is_closed' => false,
        ]);

        $response = $this->get('/restaurants/'.$restaurant->slug);

        $response->assertOk();
        $response->assertSee('<meta property="og:title"', false);
        $response->assertSee('<meta name="twitter:card"', false);
        $response->assertSee('<link rel="canonical"', false);
        $response->assertSee('application/ld+json', false);
        $response->assertSee('"@type":"Restaurant"', false);
        $response->assertSee('OpeningHoursSpecification', false);
    }

    public function test_pages_are_arabic_rtl(): void
    {
        $this->get('/')->assertSee('<html lang="ar" dir="rtl">', false);
        $this->get('/login')->assertSee('دخول');
    }

    public function test_custom_404_page_is_arabic(): void
    {
        $this->get('/restaurants/no-such-slug')->assertNotFound();
        $this->get('/restaurants/no-such-slug')->assertSee('غير موجودة');
    }
}
