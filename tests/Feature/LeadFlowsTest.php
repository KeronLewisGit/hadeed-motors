<?php

namespace Tests\Feature;

use App\Enums\FinanceStatus;
use App\Enums\LeadStatus;
use App\Enums\LeadType;
use App\Models\Lead;
use App\Models\Setting;
use App\Models\Vehicle;
use App\Notifications\LeadReceived;
use App\Notifications\NewLead;
use App\Support\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LeadFlowsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['hadeed.notify' => 'sales@example.com', 'hadeed.closed_dates' => []]);
        Notification::fake();
        Storage::fake('local');
    }

    private function bookableDay(int $index = 0): string
    {
        return Booking::days()[$index]->toDateString();
    }

    private function assertEmailed(string $reference): void
    {
        Notification::assertSentTo(new AnonymousNotifiable, NewLead::class, fn ($n, $c, $to) => $to->routes['mail'] === ['sales@example.com'] && $n->lead->reference === $reference);
        Notification::assertSentTo(new AnonymousNotifiable, LeadReceived::class, fn ($n, $c, $to) => $to->routes['mail'] === 'shanice@example.com' && $n->lead->reference === $reference);
    }

    // ------------------------------------------------------------ test drive

    /** @return array<string, mixed> */
    private function driveData(array $overrides = []): array
    {
        return array_merge($this->contact(), ['interest' => 'm:jimny', 'appointment_date' => $this->bookableDay(), 'appointment_time' => '10:00', 'licence' => '1'], $overrides);
    }

    public function test_test_drive_booking_gets_a_reference_and_emails(): void
    {
        $response = $this->post('/test-drive', $this->driveData());

        $lead = Lead::sole();
        $this->assertMatchesRegularExpression('/^TD-\d{2}-\d{5}$/', $lead->reference);
        $this->assertSame(LeadType::TestDrive, $lead->type);
        $this->assertSame(LeadStatus::New, $lead->status);
        $this->assertSame('jimny', $lead->model_key);
        $this->assertSame('10:00', $lead->appointment_time);
        $this->assertSame('2684641234', $lead->phone_digits);
        $response->assertRedirect('/thanks');
        $this->get('/thanks')->assertSee($lead->reference)->assertSee('call to confirm your test drive');
        $this->assertEmailed($lead->reference);
        $this->assertSame('Received from the website about Suzuki Jimny.', $lead->notes()->sole()->body);
    }

    public function test_test_drive_on_a_stock_unit(): void
    {
        $v = Vehicle::factory()->create();
        $this->post('/test-drive', $this->driveData(['interest' => 'v:'.$v->id]))->assertRedirect('/thanks');
        $this->assertSame($v->id, Lead::sole()->vehicle_id);
    }

    public function test_test_drive_rejects_weekends_closed_days_and_unknown_cars(): void
    {
        $saturday = Carbon::parse('next saturday')->toDateString();
        $this->post('/test-drive', $this->driveData(['appointment_date' => $saturday]))->assertSessionHasErrors('appointment_date');
        $this->post('/test-drive', $this->driveData(['appointment_date' => today()->toDateString()]))->assertSessionHasErrors('appointment_date');
        $holiday = $this->bookableDay();
        config(['hadeed.closed_dates' => [$holiday]]);
        $this->post('/test-drive', $this->driveData(['appointment_date' => $holiday]))->assertSessionHasErrors('appointment_date');
        $this->post('/test-drive', $this->driveData(['appointment_date' => $this->bookableDay(1), 'interest' => 'v:999']))->assertSessionHasErrors('interest');
        $this->post('/test-drive', $this->driveData(['appointment_date' => $this->bookableDay(1), 'licence' => null]))->assertSessionHasErrors('licence');
        $this->assertSame(0, Lead::count());
    }

    public function test_friday_slots_end_an_hour_before_closing(): void
    {
        $friday = collect(Booking::days())->first(fn ($d) => $d->isFriday())->toDateString();
        // Friday closes at 16:00, so 15:00 is the last start; 16:00 is never offered on any day.
        $this->post('/test-drive', $this->driveData(['appointment_date' => $friday, 'appointment_time' => '15:00']))->assertRedirect('/thanks');
        $this->assertSame(['09:00', '10:00', '11:00', '13:00', '14:00', '15:00'], Booking::testDriveTimes(Carbon::parse($friday)));
    }

    public function test_a_test_drive_slot_fills_up(): void
    {
        config(['hadeed.booking.test_drives_per_slot' => 2]);
        $this->post('/test-drive', $this->driveData())->assertRedirect('/thanks');
        $this->post('/test-drive', $this->driveData(['phone' => '268-555-0001']))->assertRedirect('/thanks');
        $this->post('/test-drive', $this->driveData(['phone' => '268-555-0002']))->assertSessionHasErrors('appointment_time');
        $this->assertSame(2, Lead::count());

        // The form stops offering the full slot.
        $this->get('/test-drive')->assertSee(str_replace('"', '\\u0022', '"'.$this->bookableDay().'":["09:00","11:00"'), false);

        // A lost booking frees its place.
        Lead::first()->update(['status' => LeadStatus::Lost]);
        $this->post('/test-drive', $this->driveData(['phone' => '268-555-0003']))->assertRedirect('/thanks');
    }

    // ------------------------------------------------------------ service

    /** @return array<string, mixed> */
    private function service(array $overrides = []): array
    {
        return array_merge($this->contact(), [
            'registration' => 'a 12345', 'vehicle_make' => 'Suzuki', 'vehicle_model' => 'Swift', 'vehicle_year' => 2019, 'mileage' => 48000,
            'service_type' => 'scheduled', 'appointment_date' => $this->bookableDay(), 'appointment_time' => '08:30', 'waiting' => '1',
        ], $overrides);
    }

    public function test_service_booking_records_vehicle_and_slot(): void
    {
        $this->post('/service', $this->service())->assertRedirect('/thanks');

        $lead = Lead::sole();
        $this->assertMatchesRegularExpression('/^SV-\d{2}-\d{5}$/', $lead->reference);
        $this->assertSame('A 12345', $lead->detail('registration'));
        $this->assertSame(48000, $lead->detail('mileage'));
        $this->assertTrue($lead->detail('waiting'));
        $this->assertSame($this->bookableDay(), $lead->appointment_date->toDateString());
        $this->assertEmailed($lead->reference);
    }

    public function test_service_capacity_per_day_is_enforced(): void
    {
        Setting::put('service_daily_capacity', '2');
        $day = $this->bookableDay();

        $this->post('/service', $this->service(['phone' => '268-555-0001']))->assertRedirect('/thanks');
        $this->post('/service', $this->service(['phone' => '268-555-0002']))->assertRedirect('/thanks');
        $this->assertSame(0, Booking::serviceRemaining($day));
        $this->get('/service')->assertSee('Fully booked');

        $this->post('/service', $this->service(['phone' => '268-555-0003']))->assertSessionHasErrors(['appointment_date' => 'That day is fully booked. Please choose another day.']);
        $this->assertSame(2, Lead::count());

        // Another day still has room, and a cancelled (lost) booking gives its place back.
        $this->post('/service', $this->service(['phone' => '268-555-0004', 'appointment_date' => $this->bookableDay(1)]))->assertRedirect('/thanks');
        Lead::where('type', LeadType::Service)->whereDate('appointment_date', $day)->first()->update(['status' => LeadStatus::Lost]);
        $this->assertSame(1, Booking::serviceRemaining($day));
        $this->post('/service', $this->service(['phone' => '268-555-0005']))->assertRedirect('/thanks');
    }

    public function test_service_validation(): void
    {
        $this->post('/service', $this->service(['service_type' => 'other', 'message' => '']))->assertSessionHasErrors('message');
        $this->post('/service', $this->service(['registration' => '<b>']))->assertSessionHasErrors('registration');
        $this->post('/service', $this->service(['appointment_time' => '17:30']))->assertSessionHasErrors('appointment_time');
        $this->post('/service', $this->service(['email' => '']))->assertSessionHasErrors('email');
    }

    // ------------------------------------------------------------ parts

    public function test_parts_request_checks_the_vin_and_keeps_the_photo_private(): void
    {
        $data = array_merge($this->contact(), ['vehicle_make' => 'Suzuki', 'vehicle_model' => 'Swift', 'part' => 'Front brake pads', 'quantity' => 2]);

        $this->post('/parts', $data + ['vin' => 'JS3TD-ABC'])->assertSessionHasErrors('vin');
        $this->post('/parts', $data + ['vin' => 'JS3TDO2V1Q4100001'])->assertSessionHasErrors('vin'); // contains O and Q

        $this->post('/parts', $data + ['vin' => 'js3td 62v1 y4100001', 'photo' => UploadedFile::fake()->image('pads.jpg')])->assertRedirect('/thanks');
        $lead = Lead::sole();
        $this->assertMatchesRegularExpression('/^PT-/', $lead->reference);
        $this->assertSame('JS3TD62V1Y4100001', $lead->detail('vin'));
        $file = $lead->files()->sole();
        $this->assertSame('part', $file->kind);
        Storage::disk('local')->assertExists($file->path);
        $this->assertStringStartsWith('leads/'.$lead->id.'/', $file->path);
        $this->assertEmailed($lead->reference);
    }

    // ------------------------------------------------------------ trade-in

    public function test_trade_in_with_photos(): void
    {
        $data = array_merge($this->contact(), ['vehicle_make' => 'Toyota', 'vehicle_model' => 'Corolla', 'vehicle_year' => 2015, 'mileage' => 120000, 'condition' => 'good', 'interest' => 'm:xl7']);

        $this->post('/trade-in', $data + ['photos' => array_fill(0, 7, UploadedFile::fake()->image('c.jpg'))])->assertSessionHasErrors('photos');
        $this->post('/trade-in', $data + ['photos' => [UploadedFile::fake()->create('virus.php', 10, 'text/x-php')]])->assertSessionHasErrors('photos.0');

        $this->post('/trade-in', $data + ['photos' => [UploadedFile::fake()->image('front.jpg'), UploadedFile::fake()->image('back.jpg')]])->assertRedirect('/thanks');
        $lead = Lead::sole();
        $this->assertMatchesRegularExpression('/^TI-/', $lead->reference);
        $this->assertSame('xl7', $lead->model_key);
        $this->assertSame(2, $lead->files()->where('kind', 'trade_in')->count());
        $this->assertEmailed($lead->reference);
    }

    // ------------------------------------------------------------ finance

    /** @return array<string, mixed> */
    private function finance(array $overrides = []): array
    {
        return array_merge($this->contact(), [
            'residency' => 'citizen', 'employment' => 'employed', 'employer' => 'Mount St John\'s Medical Centre', 'years_employed' => '3_5',
            'income_band' => '5_8', 'deposit' => 15000, 'term' => '60', 'interest' => 'm:grand-vitara', 'consent' => '1',
        ], $overrides);
    }

    public function test_finance_pre_qualification_stores_documents_privately(): void
    {
        $this->post('/finance/apply', $this->finance([
            'id_document' => UploadedFile::fake()->create('passport.pdf', 200, 'application/pdf'),
            'payslips' => [UploadedFile::fake()->create('may.pdf', 100, 'application/pdf'), UploadedFile::fake()->image('june.jpg')],
        ]))->assertRedirect('/thanks');

        $lead = Lead::sole();
        $this->assertMatchesRegularExpression('/^FA-/', $lead->reference);
        $this->assertSame(FinanceStatus::Received, $lead->finance_status);
        $this->assertNotNull($lead->consented_at);
        $this->assertSame('5_8', $lead->detail('income_band'));
        $this->assertSame(15000, $lead->detail('deposit'));
        $this->assertSame(['id', 'payslip', 'payslip'], $lead->files()->orderBy('id')->pluck('kind')->all());
        foreach ($lead->files as $f) {
            Storage::disk('local')->assertExists($f->path);
            $this->assertFileDoesNotExist(public_path($f->path));
        }
        $this->assertEmailed($lead->reference);
        // Office email never carries the financial details.
        Notification::assertSentTo(new AnonymousNotifiable, NewLead::class, function (NewLead $n) {
            $text = implode(' ', $n->toMail(new AnonymousNotifiable)->introLines);

            return ! str_contains($text, '5,000') && ! str_contains($text, 'Medical') && str_contains($text, 'only in the dashboard');
        });
    }

    public function test_finance_requires_explicit_consent(): void
    {
        $this->post('/finance/apply', $this->finance(['consent' => null]))->assertSessionHasErrors('consent');
        $this->assertSame(0, Lead::count());
    }

    public function test_finance_form_never_asks_for_and_refuses_bank_numbers(): void
    {
        $html = (string) $this->get('/finance/apply')->getContent();
        foreach (['account_number', 'routing', 'card_number', 'name="pin"', 'name="ssn"'] as $field) {
            $this->assertStringNotContainsString($field, $html);
        }
        $this->post('/finance/apply', $this->finance(['account_number' => '1234567890']))->assertSessionHasErrors('message');
        $this->assertSame(0, Lead::count());
    }

    public function test_finance_validation(): void
    {
        $this->post('/finance/apply', $this->finance(['employer' => '']))->assertSessionHasErrors('employer');
        $this->post('/finance/apply', $this->finance(['employment' => 'retired', 'employer' => '']))->assertSessionHasNoErrors();
        $this->post('/finance/apply', $this->finance(['income_band' => 'millions']))->assertSessionHasErrors('income_band');
        $this->post('/finance/apply', $this->finance(['id_document' => UploadedFile::fake()->create('x.exe', 10)]))->assertSessionHasErrors('id_document');
    }

    // ------------------------------------------------------------ enquiry + shared

    public function test_contact_enquiry(): void
    {
        $this->post('/contact', $this->contact(['topic' => 'callback', 'message' => 'Please call about the Fronx', 'interest' => 'm:fronx']))->assertRedirect('/thanks');
        $lead = Lead::sole();
        $this->assertMatchesRegularExpression('/^EN-/', $lead->reference);
        $this->assertSame('fronx', $lead->model_key);
    }

    public function test_honeypot_keeps_nothing_but_looks_successful(): void
    {
        $this->post('/test-drive', $this->driveData(['website' => 'http://spam.example']))->assertRedirect('/thanks');
        $this->assertSame(0, Lead::count());
        Notification::assertNothingSent();
    }

    public function test_thanks_without_a_submission_goes_home(): void
    {
        $this->get('/thanks')->assertRedirect('/');
    }

    public function test_forms_are_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/contact', $this->contact(['topic' => 'sales', 'message' => 'Hi '.$i]));
        }
        $this->post('/contact', $this->contact(['topic' => 'sales', 'message' => 'Too many']))->assertStatus(429);
    }
}
