<?php

namespace Tests\Feature;

use App\Enums\FinanceStatus;
use App\Enums\LeadStatus;
use App\Enums\LeadType;
use App\Enums\Role;
use App\Enums\VehicleStatus;
use App\Models\Lead;
use App\Models\Setting;
use App\Models\User;
use App\Models\Vehicle;
use App\Support\Booking;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_login(): void
    {
        foreach (['/admin', '/admin/leads', '/admin/vehicles', '/admin/finance', '/admin/service', '/admin/settings'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
    }

    public function test_staff_sign_in_and_inactive_accounts_cannot(): void
    {
        $user = User::factory()->role(Role::Sales)->create(['email' => 'rep@hadeed.test']);
        $this->post('/login', ['email' => 'REP@hadeed.test', 'password' => 'password'])->assertRedirect('/admin');
        $this->post('/logout');

        $user->update(['active' => false]);
        $this->post('/login', ['email' => 'rep@hadeed.test', 'password' => 'password'])->assertSessionHasErrors('email');
        $this->actingAs($user)->get('/admin')->assertRedirect('/login');
    }

    public function test_dashboard_leads_with_overdue_leads(): void
    {
        $sales = $this->staff(Role::Sales);
        $old = Lead::factory()->type(LeadType::TestDrive)->create();
        $old->forceFill(['created_at' => now()->subHours(3)])->save();
        Lead::factory()->type(LeadType::TestDrive)->create(); // fresh, inside the window
        $hidden = Lead::factory()->type(LeadType::Service)->create();
        $hidden->forceFill(['created_at' => now()->subHours(5)])->save(); // service team's, not sales'

        $this->actingAs($sales)->get('/admin')->assertOk()
            ->assertSeeInOrder(['01', 'lead has waited more than 2 hours'], false)
            ->assertSee('Call them now');
        $this->actingAs($sales)->get('/admin/leads?overdue=1')->assertSee($old->name)->assertDontSee($hidden->name);
    }

    public function test_roles_only_see_their_lead_types(): void
    {
        $service = Lead::factory()->type(LeadType::Service)->create(['name' => 'Workshop Customer']);
        $drive = Lead::factory()->type(LeadType::TestDrive)->create(['name' => 'Showroom Customer']);
        $finance = Lead::factory()->type(LeadType::Finance)->create(['name' => 'Finance Customer', 'finance_status' => FinanceStatus::Received]);

        $this->actingAs($this->staff(Role::Sales))->get('/admin/leads')->assertSee('Showroom Customer')->assertSee('Finance Customer')->assertDontSee('Workshop Customer');
        $this->actingAs($this->staff(Role::Service))->get('/admin/leads')->assertSee('Workshop Customer')->assertDontSee('Showroom Customer')->assertDontSee('Finance Customer');
        $this->actingAs($this->staff(Role::Finance))->get('/admin/leads')->assertSee('Finance Customer')->assertDontSee('Showroom Customer');
        $this->actingAs($this->staff(Role::Admin))->get('/admin/leads')->assertSee('Workshop Customer')->assertSee('Showroom Customer')->assertSee('Finance Customer');

        $this->actingAs($this->staff(Role::Service))->get(route('admin.leads.show', $drive))->assertForbidden();
        $this->actingAs($this->staff(Role::Finance))->get(route('admin.leads.show', $service))->assertForbidden();
        $this->actingAs($this->staff(Role::Sales))->get(route('admin.leads.show', $finance))->assertOk();
    }

    public function test_area_access_by_role(): void
    {
        $matrix = [
            '/admin/service' => [Role::Admin, Role::Service],
            '/admin/finance' => [Role::Admin, Role::Finance],
            '/admin/vehicles' => [Role::Admin, Role::Sales],
            '/admin/settings' => [Role::Admin],
            '/admin/users' => [Role::Admin],
        ];
        foreach (Role::cases() as $role) {
            $user = $this->staff($role);
            foreach ($matrix as $url => $allowed) {
                $this->actingAs($user)->get($url)->assertStatus(in_array($role, $allowed, true) ? 200 : 403);
            }
        }
    }

    public function test_status_assignment_and_notes_build_a_timeline(): void
    {
        $sales = $this->staff(Role::Sales);
        $colleague = $this->staff(Role::Sales);
        $mechanic = $this->staff(Role::Service);
        $lead = Lead::factory()->type(LeadType::TestDrive)->create();

        $this->actingAs($sales)->patch(route('admin.leads.update', $lead), ['status' => 'appointment', 'assigned_to' => $colleague->id])->assertSessionHasNoErrors();
        $lead->refresh();
        $this->assertSame(LeadStatus::Appointment, $lead->status);
        $this->assertSame($colleague->id, $lead->assigned_to);
        $this->assertNotNull($lead->first_contacted_at);

        // A test drive can't be handed to the workshop: they couldn't see it.
        $this->actingAs($sales)->patch(route('admin.leads.update', $lead), ['assigned_to' => $mechanic->id])->assertSessionHasErrors('assigned_to');

        $this->actingAs($sales)->post(route('admin.leads.note', $lead), ['body' => 'Coming Friday at 10 with his wife.'])->assertSessionHasNoErrors();
        $this->assertSame(['Coming Friday at 10 with his wife.', 'Assigned to '.$colleague->name.'.', 'Status changed to Appointment.'], $lead->notes()->pluck('body')->take(3)->all());
        $this->actingAs($sales)->get(route('admin.leads.show', $lead))->assertSee('Coming Friday at 10 with his wife.');
    }

    public function test_logging_a_call_moves_a_new_lead_to_contacted(): void
    {
        $lead = Lead::factory()->type(LeadType::Enquiry)->create();
        $this->actingAs($this->staff(Role::Sales))->post(route('admin.leads.note', $lead), ['body' => 'Spoke to her', 'contacted' => '1']);
        $this->assertSame(LeadStatus::Contacted, $lead->fresh()->status);
        $this->assertNotNull($lead->fresh()->first_contacted_at);
    }

    public function test_csv_export_respects_role_and_neutralises_formulas(): void
    {
        Lead::factory()->type(LeadType::TestDrive)->create(['name' => '=HYPERLINK("http://evil")']);
        Lead::factory()->type(LeadType::Service)->create(['name' => 'Workshop Only']);

        $csv = $this->actingAs($this->staff(Role::Sales))->get('/admin/leads/export')->assertOk()->streamedContent();
        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertStringNotContainsString('Workshop Only', $csv);
        $this->assertStringStartsWith("\xEF\xBB\xBFReference,Received,Type", $csv);
    }

    public function test_service_calendar_shows_bookings_against_capacity(): void
    {
        Setting::put('service_daily_capacity', '3');
        $day = Booking::days()[1]->toDateString();
        Lead::factory()->type(LeadType::Service)->count(3)->create(['appointment_date' => $day, 'appointment_time' => '08:00', 'details' => ['registration' => 'A777', 'vehicle_make' => 'Suzuki', 'vehicle_model' => 'Jimny', 'service_type' => 'oil']]);

        $this->actingAs($this->staff(Role::Service))->get('/admin/service?day='.$day.'&month='.substr($day, 0, 7))
            ->assertOk()->assertSee('3 of 3 booked')->assertSee('data-full="true"', false)->assertSee('A777 · Suzuki Jimny')->assertSee('3 of 3 places booked');
    }

    public function test_finance_pipeline_moves_applications(): void
    {
        $lead = Lead::factory()->type(LeadType::Finance)->create(['finance_status' => FinanceStatus::Received]);
        $officer = $this->staff(Role::Finance);

        $this->actingAs($officer)->get('/admin/finance')->assertSee($lead->name);
        $this->actingAs($officer)->patch(route('admin.finance.update', $lead), ['finance_status' => 'pre_qualified'])->assertSessionHasNoErrors();
        $this->assertSame(FinanceStatus::PreQualified, $lead->fresh()->finance_status);
        $this->assertSame('Finance status: Pre-qualified.', $lead->notes()->first()->body);

        $this->actingAs($this->staff(Role::Sales))->patch(route('admin.finance.update', $lead), ['finance_status' => 'declined'])->assertForbidden();
    }

    public function test_inventory_crud_with_multiple_photos(): void
    {
        Storage::fake('uploads');
        $sales = $this->staff(Role::Sales);
        $data = ['condition' => 'used', 'status' => 'available', 'make' => 'Suzuki', 'model' => 'Vitara', 'year' => 2020, 'body_type' => 'SUV',
            'transmission' => 'automatic', 'mileage_km' => 41000, 'price' => 58900, 'features_text' => "- Reverse camera\nBluetooth\n", 'published' => '1', 'featured' => '1'];

        $this->actingAs($sales)->post('/admin/vehicles', $data + ['photos' => [UploadedFile::fake()->image('a.jpg', 1200, 800), UploadedFile::fake()->image('b.jpg', 1200, 800)]])->assertRedirect();
        $v = Vehicle::sole();
        $this->assertSame('2020-suzuki-vitara', $v->slug);
        $this->assertSame(['Reverse camera', 'Bluetooth'], $v->features);
        $this->assertCount(2, $v->photos);
        foreach ($v->photos as $p) {
            Storage::disk('uploads')->assertExists([$p->path, $p->thumb_path]);
            $this->assertStringEndsWith('.webp', $p->path);
            $this->assertSame(1200, $p->width); // under 1600px: kept at its own size, re-encoded as WebP
        }
        $this->get(route('inventory.show', $v))->assertSee('EC$58,900')->assertSee('vehicles/'.$v->id.'/', false);

        // Small photos are refused; the cover can be changed; a photo can be removed.
        $this->actingAs($sales)->put(route('admin.vehicles.update', $v), $data + ['photos' => [UploadedFile::fake()->image('tiny.jpg', 300, 200)]])->assertSessionHasErrors('photos.0');
        $second = $v->photos[1];
        $this->actingAs($sales)->patch(route('admin.vehicles.photos.cover', [$v, $second]));
        $this->assertTrue($v->fresh()->photos->first()->is($second));
        $this->actingAs($sales)->delete(route('admin.vehicles.photos.destroy', [$v, $second]));
        Storage::disk('uploads')->assertMissing([$second->path, $second->thumb_path]);
        $this->assertCount(1, $v->fresh()->photos);

        // Mark sold from the list, then delete.
        $this->actingAs($sales)->patch(route('admin.vehicles.status', $v), ['status' => 'sold']);
        $this->assertSame(VehicleStatus::Sold, $v->fresh()->status);
        $this->assertNotNull($v->fresh()->sold_at);
        $this->actingAs($sales)->delete(route('admin.vehicles.destroy', $v))->assertRedirect('/admin/vehicles');
        $this->assertSame(0, Vehicle::count());
    }

    public function test_settings_rate_and_capacity(): void
    {
        $admin = $this->staff(Role::Admin);
        $this->actingAs($admin)->put('/admin/settings', ['finance_indicative_rate' => '9.50', 'service_daily_capacity' => 6])->assertSessionHasNoErrors();
        Setting::flush();
        $this->assertSame(9.5, Setting::financeRate());
        $this->assertSame(6, Setting::serviceCapacity());

        $this->actingAs($admin)->put('/admin/settings', ['finance_indicative_rate' => '', 'service_daily_capacity' => 6]);
        Setting::flush();
        $this->assertNull(Setting::financeRate());
        $this->actingAs($admin)->put('/admin/settings', ['finance_indicative_rate' => '95', 'service_daily_capacity' => 0])->assertSessionHasErrors(['finance_indicative_rate', 'service_daily_capacity']);
    }

    public function test_admin_manages_staff_but_cannot_lock_themselves_out(): void
    {
        $admin = $this->staff(Role::Admin);
        $this->actingAs($admin)->post('/admin/users', ['name' => 'New Rep', 'email' => 'NEW@hadeed.test', 'role' => 'finance', 'password' => 'a-long-password'])->assertSessionHasNoErrors();
        $rep = User::where('email', 'new@hadeed.test')->sole();
        $this->assertSame(Role::Finance, $rep->role);

        $this->actingAs($admin)->patch(route('admin.users.update', $rep), ['role' => 'sales', 'active' => '0']);
        $this->assertFalse($rep->fresh()->active);
        $this->actingAs($admin)->patch(route('admin.users.update', $admin), ['role' => 'sales', 'active' => '1'])->assertSessionHasErrors('role');
        $this->assertSame(Role::Admin, $admin->fresh()->role);
    }

    public function test_demo_seeder_builds_a_usable_demo(): void
    {
        Storage::fake('uploads');
        Storage::fake('local');
        $this->seed(DemoSeeder::class);

        foreach (['admin', 'sales', 'service', 'finance'] as $r) {
            $this->assertTrue(User::where('email', "{$r}@hadeed.test")->exists());
        }
        $this->assertTrue(Vehicle::where('is_demo', false)->doesntExist());
        $this->assertTrue(Vehicle::whereNotNull('price')->doesntExist(), 'Demo stock must not invent prices');
        $this->assertGreaterThan(0, Lead::overdue()->count());
        $this->assertNull(Setting::financeRate(), 'No finance rate is invented');
        $this->actingAs(User::where('email', 'admin@hadeed.test')->sole())->get('/admin')->assertOk();
        $this->get('/pre-owned')->assertSee('Sample listing');
    }

    public function test_every_dashboard_link_opens_for_the_role_that_sees_it(): void
    {
        // Sales could see a finance tile linking to the finance board (403), and the
        // service calendar offered the service team a Settings link (403).
        Lead::factory()->type(LeadType::Finance)->create();
        Lead::factory()->type(LeadType::Service)->create(['appointment_date' => Booking::days()[0]->toDateString(), 'appointment_time' => '08:00']);
        foreach (Role::cases() as $role) {
            $user = $this->staff($role);
            foreach (['/admin', '/admin/service', '/admin/finance', '/admin/vehicles', '/admin/leads'] as $page) {
                $res = $this->actingAs($user)->get($page);
                if ($res->status() !== 200) {
                    continue;
                }
                preg_match_all('#href="'.preg_quote(url('/admin'), '#').'(/[a-z]+)?["?/]#', (string) $res->getContent(), $m);
                foreach (array_unique($m[1]) as $area) {
                    $this->actingAs($user)->get('/admin'.$area)->assertOk();
                }
            }
        }
    }

    public function test_service_calendar_ignores_impossible_dates(): void
    {
        $admin = $this->staff(Role::Admin);
        $this->actingAs($admin)->get('/admin/service?day=2026-13-45')->assertOk()->assertSee(today()->format('l j F'));
        $this->actingAs($admin)->get('/admin/service?day=2026-02-30&month=2026-13')->assertOk()->assertSee(today()->format('F Y'));
        $this->actingAs($admin)->get('/admin/service?day=2026-11-03')->assertOk()->assertSee('November 2026')->assertSee('Tuesday 3 November');
    }

    public function test_saving_an_unchanged_assignee_adds_no_timeline_note(): void
    {
        $admin = $this->staff(Role::Admin);
        $rep = $this->staff(Role::Sales);
        $lead = Lead::factory()->type(LeadType::Enquiry)->create(['assigned_to' => $rep->id]);

        // The Manage form always posts the select's value, as a string.
        foreach ([1, 2] as $i) {
            $this->actingAs($admin)->patch(route('admin.leads.update', $lead), ['status' => $lead->status->value, 'assigned_to' => (string) $rep->id])->assertSessionHasNoErrors();
        }
        $this->assertSame(0, $lead->notes()->where('kind', 'assign')->count());
    }

    public function test_previous_and_next_follow_the_inbox_order(): void
    {
        $admin = $this->staff(Role::Admin);
        $a = Lead::factory()->type(LeadType::Service)->create(['status' => LeadStatus::Contacted]);
        $b = Lead::factory()->type(LeadType::TestDrive)->create(['status' => LeadStatus::Contacted]);
        $c = Lead::factory()->type(LeadType::Service)->create(['status' => LeadStatus::Contacted]);
        $new = Lead::factory()->type(LeadType::Parts)->create();

        // Inbox order: new first, then newest first: $new, $c, $b, $a.
        $this->actingAs($admin)->get('/admin/leads')->assertSeeInOrder([$new->name, $c->name, $b->name, $a->name]);
        $html = (string) $this->actingAs($admin)->get(route('admin.leads.show', $b))->getContent();
        $this->assertStringContainsString('href="'.route('admin.leads.show', $c).'" class="a-btn a-btn-ghost" aria-label="Previous lead in the inbox"', $html);
        $this->assertStringContainsString('href="'.route('admin.leads.show', $a).'" class="a-btn a-btn-ghost" aria-label="Next lead in the inbox"', $html);
    }
}
