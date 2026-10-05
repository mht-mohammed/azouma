<?php

namespace Tests\Feature\Owner;

use App\Enums\OperatingStatus;
use App\Enums\RestaurantStatus;
use App\Enums\UserRole;
use App\Models\Area;
use App\Models\Category;
use App\Models\Restaurant;
use App\Models\RestaurantImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OwnerDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        return User::factory()->create(['role' => UserRole::OWNER]);
    }

    private function restaurantData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'مطعم المالك التجريبي',
            'category_id' => Category::factory()->create()->id,
            'area_id' => Area::factory()->create()->id,
            'description' => 'وصف تجريبي',
            'phone' => '+970-59-0000011',
            'whatsapp' => '+970-59-0000011',
            'address' => 'غزة - حي تجريبي - شارع تجريبي 5 - بجانب علامة واضحة',
            'price_range' => 2,
        ], $overrides);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/owner')->assertRedirect('/login');
    }

    public function test_customer_cannot_access_owner_routes(): void
    {
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);

        $this->actingAs($customer)->get('/owner')->assertForbidden();
        $this->actingAs($customer)->get('/owner/restaurants/create')->assertForbidden();
    }

    public function test_owner_registration_page_can_be_rendered(): void
    {
        $this->get('/owner/register')->assertOk();
    }

    public function test_owner_registration_creates_owner_role(): void
    {
        $response = $this->post('/owner/register', [
            'name' => 'صاحب جديد',
            'email' => 'newowner@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('owner.dashboard'));
        $this->assertSame(UserRole::OWNER, User::where('email', 'newowner@example.com')->first()->role);
    }

    public function test_normal_registration_creates_customer_role(): void
    {
        $response = $this->post('/register', [
            'name' => 'زبون جديد',
            'email' => 'newcustomer@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertSame(UserRole::CUSTOMER, User::where('email', 'newcustomer@example.com')->first()->role);
    }

    public function test_login_redirects_owner_to_dashboard(): void
    {
        $owner = $this->owner();

        $this->post('/login', ['email' => $owner->email, 'password' => 'password'])
            ->assertRedirect(route('owner.dashboard'));
    }

    public function test_login_redirects_admin_to_placeholder(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);

        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.placeholder'));
    }

    public function test_login_redirects_customer_to_home(): void
    {
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);

        $this->post('/login', ['email' => $customer->email, 'password' => 'password'])
            ->assertRedirect(route('home'));
    }

    public function test_admin_placeholder_access(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);

        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($this->owner())->get('/admin')->assertForbidden();
    }

    public function test_owner_without_restaurant_is_prompted_to_create(): void
    {
        $response = $this->actingAs($this->owner())->get('/owner');

        $response->assertOk();
        $response->assertSee('لا يوجد لديك مطعم بعد');
    }

    public function test_creating_restaurant_sets_pending_and_ignores_injected_status(): void
    {
        $owner = $this->owner();

        $response = $this->actingAs($owner)->post(
            '/owner/restaurants',
            $this->restaurantData([
                'status' => 'approved',
                'is_verified' => true,
            ])
        );

        $response->assertRedirect();
        $restaurant = $owner->restaurants()->first();
        $this->assertSame(RestaurantStatus::PENDING, $restaurant->status);
        $this->assertFalse($restaurant->is_verified);
    }

    public function test_owner_cannot_create_second_restaurant(): void
    {
        $owner = $this->owner();
        Restaurant::factory()->approved()->create(['owner_id' => $owner->id]);

        $this->actingAs($owner)->get('/owner/restaurants/create')->assertForbidden();
        $this->actingAs($owner)->post('/owner/restaurants', $this->restaurantData())->assertForbidden();
    }

    public function test_owner_cannot_view_or_edit_another_owners_restaurant(): void
    {
        $other = Restaurant::factory()->approved()->create();

        $this->actingAs($this->owner())->get('/owner/restaurants/'.$other->id.'/edit')->assertNotFound();
        $this->actingAs($this->owner())->put(
            '/owner/restaurants/'.$other->id,
            $this->restaurantData()
        )->assertForbidden();
    }

    public function test_admin_policy_allows_anything(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $restaurant = Restaurant::factory()->approved()->create();

        $this->assertTrue(Gate::forUser($admin)->allows('update', $restaurant));
        $this->assertTrue(Gate::forUser($admin)->allows('delete', $restaurant));
    }

    public function test_critical_edit_sends_approved_restaurant_back_to_pending(): void
    {
        $owner = $this->owner();
        $restaurant = Restaurant::factory()->approved()->create(['owner_id' => $owner->id]);

        $response = $this->actingAs($owner)->put(
            '/owner/restaurants/'.$restaurant->id,
            $this->restaurantData(['name' => 'اسم جديد تماما'])
        );

        $response->assertRedirect(route('owner.dashboard'));
        $response->assertSessionHas('success');
        $this->assertSame(RestaurantStatus::PENDING, $restaurant->fresh()->status);
        $this->assertSame('اسم جديد تماما', $restaurant->fresh()->name);
    }

    public function test_non_critical_edit_keeps_approved_status(): void
    {
        $owner = $this->owner();
        $restaurant = Restaurant::factory()->approved()->create(['owner_id' => $owner->id]);

        $this->actingAs($owner)->put(
            '/owner/restaurants/'.$restaurant->id,
            $this->restaurantData([
                'name' => $restaurant->name,
                'category_id' => $restaurant->category_id,
                'area_id' => $restaurant->area_id,
                'phone' => $restaurant->phone,
                'description' => 'وصف محدث فقط',
            ])
        )->assertRedirect(route('owner.dashboard'));

        $this->assertSame(RestaurantStatus::APPROVED, $restaurant->fresh()->status);
    }

    public function test_operating_status_update_sets_timestamp_without_reapproval(): void
    {
        $owner = $this->owner();
        $restaurant = Restaurant::factory()->approved()->create([
            'owner_id' => $owner->id,
            'operating_status' => OperatingStatus::OPEN,
        ]);

        $this->travelTo(now()->addHour());

        $this->actingAs($owner)->patch(
            '/owner/restaurants/'.$restaurant->id.'/status',
            ['operating_status' => 'temporarily_closed']
        )->assertRedirect(route('owner.dashboard'));

        $fresh = $restaurant->fresh();
        $this->assertSame(OperatingStatus::TEMPORARILY_CLOSED, $fresh->operating_status);
        $this->assertSame(RestaurantStatus::APPROVED, $fresh->status);
        $this->assertEquals(now()->format('Y-m-d H:i'), $fresh->operating_status_updated_at->format('Y-m-d H:i'));
    }

    public function test_opening_hours_validation_requires_times_for_open_days(): void
    {
        $owner = $this->owner();
        $restaurant = Restaurant::factory()->approved()->create(['owner_id' => $owner->id]);

        $hours = [];
        for ($day = 0; $day <= 6; $day++) {
            $hours[$day] = ['day_of_week' => $day, 'is_closed' => '0', 'opens_at' => '', 'closes_at' => ''];
        }

        $this->actingAs($owner)->put(
            '/owner/restaurants/'.$restaurant->id.'/hours',
            ['hours' => $hours]
        )->assertSessionHasErrors();
    }

    public function test_opening_hours_are_saved_for_all_days(): void
    {
        $owner = $this->owner();
        $restaurant = Restaurant::factory()->approved()->create(['owner_id' => $owner->id]);

        $hours = [];
        for ($day = 0; $day <= 6; $day++) {
            $hours[$day] = $day === 6
                ? ['day_of_week' => $day, 'is_closed' => '1', 'opens_at' => '', 'closes_at' => '']
                : ['day_of_week' => $day, 'is_closed' => '0', 'opens_at' => '09:00', 'closes_at' => '23:00'];
        }

        $this->actingAs($owner)->put(
            '/owner/restaurants/'.$restaurant->id.'/hours',
            ['hours' => $hours]
        )->assertRedirect(route('owner.dashboard'));

        $this->assertSame(7, $restaurant->openingHours()->count());
        $friday = $restaurant->openingHours()->where('day_of_week', 6)->first();
        $this->assertTrue($friday->is_closed);
        $this->assertNull($friday->opens_at);
    }

    public function test_image_upload_stores_file_and_sets_first_as_cover(): void
    {
        Storage::fake('public');
        $owner = $this->owner();
        $restaurant = Restaurant::factory()->approved()->create(['owner_id' => $owner->id]);

        $this->actingAs($owner)->post(
            '/owner/restaurants/'.$restaurant->id.'/images',
            ['images' => [UploadedFile::fake()->image('cover.jpg')]]
        )->assertRedirect();

        $image = $restaurant->images()->first();
        $this->assertNotNull($image);
        $this->assertTrue($image->is_cover);
        Storage::disk('public')->assertExists(str_replace('storage/', '', $image->path));
    }

    public function test_image_upload_rejects_bad_type_oversize_and_too_many(): void
    {
        Storage::fake('public');
        $owner = $this->owner();
        $restaurant = Restaurant::factory()->approved()->create(['owner_id' => $owner->id]);

        $this->actingAs($owner)->post(
            '/owner/restaurants/'.$restaurant->id.'/images',
            ['images' => [UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf')]]
        )->assertSessionHasErrors('images.0');

        $this->actingAs($owner)->post(
            '/owner/restaurants/'.$restaurant->id.'/images',
            ['images' => [UploadedFile::fake()->image('big.jpg')->size(5000)]]
        )->assertSessionHasErrors('images.0');

        RestaurantImage::factory()->count(9)->create(['restaurant_id' => $restaurant->id]);
        $this->actingAs($owner)->post(
            '/owner/restaurants/'.$restaurant->id.'/images',
            ['images' => [
                UploadedFile::fake()->image('a.jpg'),
                UploadedFile::fake()->image('b.jpg'),
            ]]
        )->assertSessionHasErrors('images');
    }

    public function test_deleting_cover_image_promotes_another_and_removes_file(): void
    {
        Storage::fake('public');
        $owner = $this->owner();
        $restaurant = Restaurant::factory()->approved()->create(['owner_id' => $owner->id]);

        $this->actingAs($owner)->post(
            '/owner/restaurants/'.$restaurant->id.'/images',
            ['images' => [
                UploadedFile::fake()->image('a.jpg'),
                UploadedFile::fake()->image('b.jpg'),
            ]]
        );

        $cover = $restaurant->images()->where('is_cover', true)->first();
        $coverRelativePath = str_replace('storage/', '', $cover->path);

        $this->actingAs($owner)->delete(
            '/owner/restaurants/'.$restaurant->id.'/images/'.$cover->id
        )->assertRedirect();

        Storage::disk('public')->assertMissing($coverRelativePath);
        $this->assertSame(1, $restaurant->images()->count());
        $this->assertSame(1, $restaurant->images()->where('is_cover', true)->count());
    }

    public function test_setting_cover_keeps_exactly_one_cover(): void
    {
        $owner = $this->owner();
        $restaurant = Restaurant::factory()->approved()->create(['owner_id' => $owner->id]);
        $first = RestaurantImage::factory()->cover()->create(['restaurant_id' => $restaurant->id]);
        $second = RestaurantImage::factory()->create(['restaurant_id' => $restaurant->id]);

        $this->actingAs($owner)->patch(
            '/owner/restaurants/'.$restaurant->id.'/images/'.$second->id.'/cover'
        )->assertRedirect();

        $this->assertSame(1, $restaurant->images()->where('is_cover', true)->count());
        $this->assertTrue($second->fresh()->is_cover);
        $this->assertFalse($first->fresh()->is_cover);
    }

    public function test_moving_image_swaps_sort_order(): void
    {
        $owner = $this->owner();
        $restaurant = Restaurant::factory()->approved()->create(['owner_id' => $owner->id]);
        $first = RestaurantImage::factory()->create(['restaurant_id' => $restaurant->id, 'sort_order' => 0]);
        $second = RestaurantImage::factory()->create(['restaurant_id' => $restaurant->id, 'sort_order' => 1]);

        $this->actingAs($owner)->patch(
            '/owner/restaurants/'.$restaurant->id.'/images/'.$second->id.'/move',
            ['direction' => 'up']
        )->assertRedirect();

        $this->assertSame(1, $first->fresh()->sort_order);
        $this->assertSame(0, $second->fresh()->sort_order);
    }

    public function test_dashboard_shows_rejection_reason(): void
    {
        $owner = $this->owner();
        Restaurant::factory()->rejected()->create(['owner_id' => $owner->id]);

        $response = $this->actingAs($owner)->get('/owner');

        $response->assertOk();
        $response->assertSee('مرفوض');
        $response->assertSee($owner->restaurants()->first()->rejection_reason);
    }
}
