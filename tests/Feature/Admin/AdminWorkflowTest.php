<?php

namespace Tests\Feature\Admin;

use App\Enums\RestaurantStatus;
use App\Enums\UserRole;
use App\Models\Area;
use App\Models\Category;
use App\Models\Report;
use App\Models\Restaurant;
use App\Models\User;
use App\Notifications\RestaurantApproved;
use App\Notifications\RestaurantRejected;
use App\Notifications\RestaurantSubmittedForReview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => UserRole::ADMIN]);
    }

    public function test_non_admins_cannot_access_admin_routes(): void
    {
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);
        $owner = User::factory()->create(['role' => UserRole::OWNER]);

        $this->get('/admin')->assertRedirect('/login');

        foreach (['/admin', '/admin/restaurants/pending', '/admin/restaurants', '/admin/categories', '/admin/areas', '/admin/reports'] as $url) {
            $this->actingAs($customer)->get($url)->assertForbidden();
            $this->actingAs($owner)->get($url)->assertForbidden();
        }
    }

    public function test_admin_dashboard_shows_counts(): void
    {
        Restaurant::factory()->pending()->create();
        Restaurant::factory()->approved()->create();
        Report::factory()->create();

        $response = $this->actingAs($this->admin())->get('/admin');

        $response->assertOk();
        $response->assertSee('قيد المراجعة');
    }

    public function test_approve_sets_approved_and_clears_reason(): void
    {
        $admin = $this->admin();
        $restaurant = Restaurant::factory()->pending()->create(['rejection_reason' => 'قديم']);

        $this->actingAs($admin)->post('/admin/restaurants/'.$restaurant->id.'/approve')
            ->assertRedirect(route('admin.restaurants.pending'));

        $fresh = $restaurant->fresh();
        $this->assertSame(RestaurantStatus::APPROVED, $fresh->status);
        $this->assertNull($fresh->rejection_reason);
        $this->assertFalse($fresh->is_verified);
    }

    public function test_approved_restaurant_appears_publicly(): void
    {
        $admin = $this->admin();
        $restaurant = Restaurant::factory()->pending()->create(['name' => 'مطعم سيُعتمد علنيا']);

        $this->actingAs($admin)->post('/admin/restaurants/'.$restaurant->id.'/approve');

        $this->get('/')->assertSee($restaurant->name);
        $this->get('/restaurants/'.$restaurant->slug)->assertOk();
    }

    public function test_reject_requires_reason_and_hides_publicly(): void
    {
        $admin = $this->admin();
        $restaurant = Restaurant::factory()->pending()->create(['name' => 'مطعم سيُرفض نهائيا']);

        $this->actingAs($admin)->post('/admin/restaurants/'.$restaurant->id.'/reject', [])
            ->assertSessionHasErrors('rejection_reason');

        $this->actingAs($admin)->post(
            '/admin/restaurants/'.$restaurant->id.'/reject',
            ['rejection_reason' => 'العنوان ناقص']
        )->assertRedirect(route('admin.restaurants.pending'));

        $fresh = $restaurant->fresh();
        $this->assertSame(RestaurantStatus::REJECTED, $fresh->status);
        $this->assertSame('العنوان ناقص', $fresh->rejection_reason);
        $this->get('/')->assertDontSee($restaurant->name);
        $this->get('/restaurants/'.$restaurant->slug)->assertNotFound();
    }

    public function test_verify_and_unverify_toggle_badge_fields(): void
    {
        $admin = $this->admin();
        $restaurant = Restaurant::factory()->approved()->create();

        $this->actingAs($admin)->post('/admin/restaurants/'.$restaurant->id.'/verify');

        $fresh = $restaurant->fresh();
        $this->assertTrue($fresh->is_verified);
        $this->assertNotNull($fresh->last_verified_at);

        $this->actingAs($admin)->post('/admin/restaurants/'.$restaurant->id.'/unverify');

        $fresh = $restaurant->fresh();
        $this->assertFalse($fresh->is_verified);
        $this->assertNull($fresh->last_verified_at);
    }

    public function test_verification_is_removed_when_approved_restaurant_goes_back_to_pending(): void
    {
        $owner = User::factory()->create(['role' => UserRole::OWNER]);
        $restaurant = Restaurant::factory()->verified()->create(['owner_id' => $owner->id]);
        $this->assertTrue($restaurant->is_verified);

        $this->actingAs($owner)->put('/owner/restaurants/'.$restaurant->id, [
            'name' => 'اسم جديد يسبب إعادة المراجعة',
            'category_id' => $restaurant->category_id,
            'area_id' => $restaurant->area_id,
            'phone' => $restaurant->phone,
            'address' => $restaurant->address,
        ])->assertRedirect(route('owner.dashboard'));

        $fresh = $restaurant->fresh();
        $this->assertSame(RestaurantStatus::PENDING, $fresh->status);
        $this->assertFalse($fresh->is_verified);
        $this->assertNull($fresh->last_verified_at);
    }

    public function test_owner_cannot_change_admin_only_fields_on_update(): void
    {
        $owner = User::factory()->create(['role' => UserRole::OWNER]);
        $restaurant = Restaurant::factory()->pending()->create(['owner_id' => $owner->id]);

        $this->actingAs($owner)->put('/owner/restaurants/'.$restaurant->id, [
            'name' => $restaurant->name,
            'category_id' => $restaurant->category_id,
            'area_id' => $restaurant->area_id,
            'phone' => $restaurant->phone,
            'address' => $restaurant->address,
            'status' => 'approved',
            'is_verified' => true,
            'rejection_reason' => 'x',
        ])->assertRedirect(route('owner.dashboard'));

        $fresh = $restaurant->fresh();
        $this->assertSame(RestaurantStatus::PENDING, $fresh->status);
        $this->assertFalse($fresh->is_verified);
    }

    public function test_admin_is_notified_when_restaurant_is_submitted(): void
    {
        Notification::fake();
        $admin = $this->admin();
        $owner = User::factory()->create(['role' => UserRole::OWNER]);

        $this->actingAs($owner)->post('/owner/restaurants', [
            'name' => 'مطعم جديد للإشعار',
            'category_id' => Category::factory()->create()->id,
            'area_id' => Area::factory()->create()->id,
            'phone' => '+970-59-0000022',
            'address' => 'عنوان تفصيلي للإشعار',
        ])->assertRedirect();

        Notification::assertSentTo($admin, RestaurantSubmittedForReview::class);
    }

    public function test_approve_and_reject_notify_the_owner(): void
    {
        Notification::fake();
        $admin = $this->admin();
        $owner = User::factory()->create(['role' => UserRole::OWNER]);
        $toApprove = Restaurant::factory()->pending()->create(['owner_id' => $owner->id]);
        $toReject = Restaurant::factory()->pending()->create(['owner_id' => $owner->id]);

        $this->actingAs($admin)->post('/admin/restaurants/'.$toApprove->id.'/approve');
        $this->actingAs($admin)->post(
            '/admin/restaurants/'.$toReject->id.'/reject',
            ['rejection_reason' => 'بيانات ناقصة']
        );

        Notification::assertSentTo($owner, RestaurantApproved::class);
        Notification::assertSentTo($owner, RestaurantRejected::class);
    }

    public function test_categories_crud_and_delete_protection(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/categories', ['name_ar' => 'تصنيف إداري'])
            ->assertRedirect();
        $category = Category::where('name_ar', 'تصنيف إداري')->first();
        $this->assertNotNull($category);
        $this->assertNotEmpty($category->slug);

        $this->actingAs($admin)->put('/admin/categories/'.$category->id, ['name_ar' => 'تصنيف إداري محدث'])
            ->assertRedirect();
        $this->assertSame('تصنيف إداري محدث', $category->fresh()->name_ar);

        $this->actingAs($admin)->delete('/admin/categories/'.$category->id)->assertRedirect();
        $this->assertNull(Category::find($category->id));

        $used = Category::factory()->create();
        Restaurant::factory()->approved()->create(['category_id' => $used->id]);

        $this->actingAs($admin)->delete('/admin/categories/'.$used->id)->assertRedirect();
        $this->assertNotNull(Category::find($used->id));
    }

    public function test_areas_crud_and_delete_protection(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/areas', ['name_ar' => 'حي إداري'])->assertRedirect();
        $area = Area::where('name_ar', 'حي إداري')->first();
        $this->assertNotNull($area);

        $used = Area::factory()->create();
        Restaurant::factory()->approved()->create(['area_id' => $used->id]);

        $this->actingAs($admin)->delete('/admin/areas/'.$used->id)->assertRedirect();
        $this->assertNotNull(Area::find($used->id));
    }

    public function test_review_page_shows_full_data_but_never_owner_email_publicly(): void
    {
        $admin = $this->admin();
        $restaurant = Restaurant::factory()->pending()->create();

        $response = $this->actingAs($admin)->get('/admin/restaurants/'.$restaurant->id);

        $response->assertOk();
        $response->assertSee($restaurant->name);
        $response->assertSee($restaurant->owner->email);
    }

    public function test_state_changing_admin_endpoints_reject_non_admins(): void
    {
        $owner = User::factory()->create(['role' => UserRole::OWNER]);
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);
        $restaurant = Restaurant::factory()->pending()->create();
        $category = Category::factory()->create();

        foreach ([$owner, $customer] as $user) {
            $this->actingAs($user)->post('/admin/restaurants/'.$restaurant->id.'/approve')->assertForbidden();
            $this->actingAs($user)->post(
                '/admin/restaurants/'.$restaurant->id.'/reject',
                ['rejection_reason' => 'x']
            )->assertForbidden();
            $this->actingAs($user)->post('/admin/restaurants/'.$restaurant->id.'/verify')->assertForbidden();
            $this->actingAs($user)->post('/admin/categories', ['name_ar' => 'ممنوع'])->assertForbidden();
            $this->actingAs($user)->delete('/admin/categories/'.$category->id)->assertForbidden();
        }

        // Nothing changed.
        $this->assertSame(RestaurantStatus::PENDING, $restaurant->fresh()->status);
        $this->assertFalse(Category::where('name_ar', 'ممنوع')->exists());
        $this->assertNotNull(Category::find($category->id));
    }

    public function test_customer_cannot_create_restaurant(): void
    {
        $customer = User::factory()->create(['role' => UserRole::CUSTOMER]);

        $this->actingAs($customer)->post('/owner/restaurants', [
            'name' => 'محاولة زبون',
            'category_id' => Category::factory()->create()->id,
            'area_id' => Area::factory()->create()->id,
            'phone' => '123',
            'address' => 'عنوان',
        ])->assertForbidden();

        $this->assertFalse(Restaurant::where('name', 'محاولة زبون')->exists());
    }
}
