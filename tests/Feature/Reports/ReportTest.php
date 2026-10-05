<?php

namespace Tests\Feature\Reports;

use App\Enums\ReportReason;
use App\Enums\ReportStatus;
use App\Enums\UserRole;
use App\Models\Report;
use App\Models\Restaurant;
use App\Models\User;
use App\Notifications\RestaurantApproved;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_submit_a_valid_report(): void
    {
        $restaurant = Restaurant::factory()->approved()->create();

        $response = $this->post('/restaurants/'.$restaurant->slug.'/reports', [
            'reason' => 'closed',
            'message' => 'المطعم مغلق منذ أيام.',
            'reporter_contact' => null,
            'website' => '',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertSame(1, $restaurant->reports()->count());
        $this->assertSame(ReportStatus::NEW, $restaurant->reports()->first()->status);
        $this->assertSame(ReportReason::CLOSED, $restaurant->reports()->first()->reason);
    }

    public function test_report_validation_requires_reason_and_message(): void
    {
        $restaurant = Restaurant::factory()->approved()->create();

        $this->post('/restaurants/'.$restaurant->slug.'/reports', [
            'message' => 'بدون سبب.',
        ])->assertSessionHasErrors('reason');

        $this->post('/restaurants/'.$restaurant->slug.'/reports', [
            'reason' => 'other',
        ])->assertSessionHasErrors('message');
    }

    public function test_honeypot_blocks_bots(): void
    {
        $restaurant = Restaurant::factory()->approved()->create();

        $this->post('/restaurants/'.$restaurant->slug.'/reports', [
            'reason' => 'other',
            'message' => 'رسالة بوت.',
            'website' => 'spam-bot-filled-this',
        ])->assertSessionHasErrors('website');

        $this->assertSame(0, $restaurant->reports()->count());
    }

    public function test_reports_are_rejected_for_non_approved_restaurants(): void
    {
        $pending = Restaurant::factory()->pending()->create();

        $this->post('/restaurants/'.$pending->slug.'/reports', [
            'reason' => 'other',
            'message' => 'محاولة.',
        ])->assertForbidden();
    }

    public function test_report_form_is_rate_limited(): void
    {
        $restaurant = Restaurant::factory()->approved()->create();

        $data = ['reason' => 'other', 'message' => 'بلاغ متكرر.'];

        for ($i = 0; $i < 10; $i++) {
            $this->post('/restaurants/'.$restaurant->slug.'/reports', $data)->assertRedirect();
        }

        $this->post('/restaurants/'.$restaurant->slug.'/reports', $data)->assertStatus(429);
    }

    public function test_admin_can_view_and_resolve_reports(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $report = Report::factory()->create();

        $this->actingAs($admin)->get('/admin/reports')->assertOk();
        $this->actingAs($admin)->get('/admin/reports/'.$report->id)->assertOk();

        $this->actingAs($admin)->post('/admin/reports/'.$report->id.'/resolve')
            ->assertRedirect(route('admin.reports.index'));

        $this->assertSame(ReportStatus::RESOLVED, $report->fresh()->status);
    }

    public function test_owner_notifications_page_and_mark_as_read(): void
    {
        config()->set('queue.default', 'sync');

        $owner = User::factory()->create(['role' => UserRole::OWNER]);
        $restaurant = Restaurant::factory()->approved()->create(['owner_id' => $owner->id]);
        $owner->notify(new RestaurantApproved($restaurant));

        $this->assertSame(1, $owner->unreadNotifications()->count());

        $response = $this->actingAs($owner)->get('/owner/notifications');
        $response->assertOk();
        $response->assertSee($restaurant->name);

        $notification = $owner->notifications()->first();
        $this->actingAs($owner)->patch('/owner/notifications/'.$notification->id)
            ->assertRedirect();

        $this->assertSame(0, $owner->fresh()->unreadNotifications()->count());
    }

    public function test_owner_cannot_read_another_users_notification(): void
    {
        config()->set('queue.default', 'sync');

        $owner = User::factory()->create(['role' => UserRole::OWNER]);
        $other = User::factory()->create(['role' => UserRole::OWNER]);
        $restaurant = Restaurant::factory()->approved()->create(['owner_id' => $other->id]);
        $other->notify(new RestaurantApproved($restaurant));

        $notification = $other->notifications()->first();

        $this->actingAs($owner)->patch('/owner/notifications/'.$notification->id)->assertNotFound();
    }
}
