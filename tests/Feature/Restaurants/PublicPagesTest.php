<?php

namespace Tests\Feature\Restaurants;

use App\Enums\OperatingStatus;
use App\Models\Area;
use App\Models\Category;
use App\Models\OpeningHour;
use App\Models\Restaurant;
use App\Models\RestaurantImage;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    private function restaurantWithWeeklyHours(array $overrides = []): Restaurant
    {
        $restaurant = Restaurant::factory()->approved()->create();

        for ($day = 0; $day <= 6; $day++) {
            $dayData = $overrides[$day] ?? [
                'opens_at' => '09:00:00',
                'closes_at' => '23:00:00',
                'is_closed' => false,
            ];
            OpeningHour::factory()->create(array_merge(
                ['restaurant_id' => $restaurant->id, 'day_of_week' => $day],
                $dayData
            ));
        }

        return $restaurant->refresh();
    }

    public function test_list_shows_only_approved_restaurants(): void
    {
        $approved = Restaurant::factory()->approved()->create(['name' => 'مطعم معتمد ظاهر']);
        $pending = Restaurant::factory()->pending()->create(['name' => 'مطعم قيد المراجعة مخفي']);
        $rejected = Restaurant::factory()->rejected()->create(['name' => 'مطعم مرفوض مخفي']);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee($approved->name);
        $response->assertDontSee($pending->name);
        $response->assertDontSee($rejected->name);
    }

    public function test_list_filters_by_category(): void
    {
        $wanted = Category::factory()->create();
        $other = Category::factory()->create();
        $inside = Restaurant::factory()->approved()->create(['category_id' => $wanted->id, 'name' => 'داخل التصنيف']);
        $outside = Restaurant::factory()->approved()->create(['category_id' => $other->id, 'name' => 'خارج التصنيف']);

        $response = $this->get('/?category='.$wanted->id);

        $response->assertOk();
        $response->assertSee($inside->name);
        $response->assertDontSee($outside->name);
    }

    public function test_list_filters_by_area(): void
    {
        $wanted = Area::factory()->create();
        $other = Area::factory()->create();
        $inside = Restaurant::factory()->approved()->create(['area_id' => $wanted->id, 'name' => 'داخل المنطقة']);
        $outside = Restaurant::factory()->approved()->create(['area_id' => $other->id, 'name' => 'خارج المنطقة']);

        $response = $this->get('/?area='.$wanted->id);

        $response->assertOk();
        $response->assertSee($inside->name);
        $response->assertDontSee($outside->name);
    }

    public function test_list_searches_by_name(): void
    {
        $match = Restaurant::factory()->approved()->create(['name' => 'مشاوي الديوانية']);
        $noMatch = Restaurant::factory()->approved()->create(['name' => 'حلويات القمر']);

        $response = $this->get('/?q='.urlencode('مشاوي'));

        $response->assertOk();
        $response->assertSee($match->name);
        $response->assertDontSee($noMatch->name);
    }

    public function test_list_combines_all_filters(): void
    {
        $category = Category::factory()->create();
        $area = Area::factory()->create();
        $match = Restaurant::factory()->approved()->create([
            'category_id' => $category->id, 'area_id' => $area->id, 'name' => 'مشاوي الزاوية',
        ]);
        $wrongArea = Restaurant::factory()->approved()->create([
            'category_id' => $category->id, 'area_id' => Area::factory()->create()->id, 'name' => 'مشاوي البعيدة',
        ]);

        $response = $this->get('/?category='.$category->id.'&area='.$area->id.'&q='.urlencode('مشاوي'));

        $response->assertOk();
        $response->assertSee($match->name);
        $response->assertDontSee($wrongArea->name);
    }

    public function test_pagination_keeps_filters_in_links(): void
    {
        $category = Category::factory()->create();
        Restaurant::factory()->approved()->count(13)->create(['category_id' => $category->id]);

        $response = $this->get('/?category='.$category->id);

        $response->assertOk();
        $response->assertSee('category='.$category->id, false);
    }

    public function test_show_page_displays_approved_restaurant(): void
    {
        $restaurant = $this->restaurantWithWeeklyHours();
        RestaurantImage::factory()->cover()->create(['restaurant_id' => $restaurant->id, 'sort_order' => 0]);

        $this->travelTo(Carbon::parse('2026-10-05 12:00')); // Monday, inside 09:00–23:00

        $response = $this->get('/restaurants/'.$restaurant->slug);

        $response->assertOk();
        $response->assertSee($restaurant->name);
        $response->assertSee($restaurant->operating_status->label());
        $response->assertSee('مفتوح الآن');
        $response->assertDontSee('restaurant-map');
        $response->assertSee('tel:', false);
        $response->assertSee('https://wa.me/', false);
        $response->assertSee('السبت'); // week table starts Saturday
    }

    public function test_show_returns_404_for_pending_and_rejected(): void
    {
        $pending = Restaurant::factory()->pending()->create();
        $rejected = Restaurant::factory()->rejected()->create();

        $this->get('/restaurants/'.$pending->slug)->assertNotFound();
        $this->get('/restaurants/'.$rejected->slug)->assertNotFound();
    }

    public function test_list_page_has_no_n_plus_one_queries(): void
    {
        foreach (Restaurant::factory()->approved()->count(5)->create() as $restaurant) {
            RestaurantImage::factory()->cover()->create(['restaurant_id' => $restaurant->id]);
            RestaurantImage::factory()->create(['restaurant_id' => $restaurant->id]);
            $this->restaurantWithWeeklyHoursFor($restaurant);
        }

        $count = 0;
        DB::listen(function () use (&$count) {
            $count++;
        });

        $this->get('/')->assertOk();

        // ~7 fixed queries (filters, count + page, 3 eager loads).
        // Per-row queries would push this far higher.
        $this->assertLessThan(15, $count);
    }

    private function restaurantWithWeeklyHoursFor(Restaurant $restaurant): void
    {
        for ($day = 0; $day <= 6; $day++) {
            OpeningHour::factory()->create(['restaurant_id' => $restaurant->id, 'day_of_week' => $day]);
        }
    }

    public function test_is_open_now_inside_opening_hours(): void
    {
        $restaurant = $this->restaurantWithWeeklyHours();

        $this->travelTo(Carbon::parse('2026-10-05 12:00')); // Monday

        $this->assertTrue($restaurant->isOpenNow());
    }

    public function test_is_open_now_before_opening_time(): void
    {
        $restaurant = $this->restaurantWithWeeklyHours();

        $this->travelTo(Carbon::parse('2026-10-05 08:00'));

        $this->assertFalse($restaurant->isOpenNow());
    }

    public function test_is_open_now_on_closed_day(): void
    {
        // 2026-10-09 is a Friday (day 6).
        $restaurant = $this->restaurantWithWeeklyHours([
            6 => ['opens_at' => null, 'closes_at' => null, 'is_closed' => true],
        ]);

        $this->travelTo(Carbon::parse('2026-10-09 12:00'));

        $this->assertFalse($restaurant->isOpenNow());
    }

    public function test_is_open_now_for_overnight_shift_in_the_evening(): void
    {
        $restaurant = $this->restaurantWithWeeklyHours([
            2 => ['opens_at' => '18:00:00', 'closes_at' => '02:00:00', 'is_closed' => false],
        ]);

        $this->travelTo(Carbon::parse('2026-10-05 23:00')); // Monday night

        $this->assertTrue($restaurant->isOpenNow());
    }

    public function test_is_open_now_for_overnight_shift_after_midnight(): void
    {
        $restaurant = $this->restaurantWithWeeklyHours(array_fill(0, 7, [
            'opens_at' => '18:00:00', 'closes_at' => '02:00:00', 'is_closed' => false,
        ]));

        $this->travelTo(Carbon::parse('2026-10-06 01:00')); // Tuesday, still Monday's shift

        $this->assertTrue($restaurant->isOpenNow());
    }

    public function test_is_open_now_for_overnight_shift_at_midday(): void
    {
        $restaurant = $this->restaurantWithWeeklyHours(array_fill(0, 7, [
            'opens_at' => '18:00:00', 'closes_at' => '02:00:00', 'is_closed' => false,
        ]));

        $this->travelTo(Carbon::parse('2026-10-06 12:00'));

        $this->assertFalse($restaurant->isOpenNow());
    }

    public function test_whatsapp_link_is_normalized(): void
    {
        $restaurant = Restaurant::factory()->approved()->create([
            'phone' => '+970-59-0000010',
            'whatsapp' => '+970-59-0000010',
        ]);

        $this->assertSame('https://wa.me/970590000010', $restaurant->whatsappUrl());

        $restaurant->whatsapp = null;
        $this->assertNull($restaurant->whatsappUrl());
    }

    public function test_temporarily_closed_and_relocated_badges_show_arabic_labels(): void
    {
        $closed = Restaurant::factory()->approved()->create([
            'operating_status' => OperatingStatus::TEMPORARILY_CLOSED,
        ]);
        $moved = Restaurant::factory()->approved()->create([
            'operating_status' => OperatingStatus::RELOCATED,
        ]);

        $this->get('/restaurants/'.$closed->slug)->assertSee('مغلق مؤقتاً');
        $this->get('/restaurants/'.$moved->slug)->assertSee('انتقل إلى موقع جديد');
    }
}
