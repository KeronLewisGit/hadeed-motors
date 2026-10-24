<?php

namespace Database\Seeders;

use App\Enums\Condition;
use App\Enums\FinanceStatus;
use App\Enums\LeadStatus;
use App\Enums\LeadType;
use App\Enums\Role;
use App\Enums\Transmission;
use App\Enums\VehicleStatus;
use App\Models\Lead;
use App\Models\User;
use App\Models\Vehicle;
use App\Support\Booking;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Pitch demo and local work: php artisan migrate:fresh --seeder=DemoSeeder
 *
 * Every vehicle here is flagged is_demo and shows a "Sample listing" badge. The makes
 * and models are ones Hadeed Motors sells or has sold (hadeedmotors.com); mileages,
 * colours and the people in the leads are made up. No prices, rates or stock levels
 * are invented: every demo car is "Call for price" until the dealership enters its own.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $users = collect([
            ['admin@hadeed.test', 'Demo Admin', Role::Admin],
            ['sales@hadeed.test', 'Demo Sales', Role::Sales],
            ['sales2@hadeed.test', 'Second Salesperson', Role::Sales],
            ['service@hadeed.test', 'Demo Service Advisor', Role::Service],
            ['finance@hadeed.test', 'Demo Finance Officer', Role::Finance],
        ])->mapWithKeys(fn (array $u) => [$u[0] => User::updateOrCreate(['email' => $u[0]], [
            'name' => $u[1], 'role' => $u[2], 'active' => true, 'password' => Hash::make('password'),
        ])]);

        $vehicles = $this->vehicles();
        $this->leads($users->all(), $vehicles);
    }

    /** @return list<Vehicle> */
    private function vehicles(): array
    {
        $rows = [
            ['used', 2022, 'Nissan', 'Navara', 'VE', 'Pickup', 'automatic', 'Diesel', '2.5 litre', '4WD', 41200, 'Copper red', 5, true, 'used-navara'],
            ['used', 2019, 'Nissan', 'X-Trail', null, 'SUV', 'automatic', 'Petrol', '2.5 litre', '2WD', 68300, 'Bronze', 7, true, 'used-xtrail'],
            ['used', 2019, 'Suzuki', 'Vitara', 'GLX', 'SUV', 'automatic', 'Petrol', '1.6 litre', '2WD', 52900, 'White', 5, true, 'used-vitara', 'used-vitara-interior'],
            ['used', 2018, 'Suzuki', 'Swift', 'GL', 'Hatchback', 'manual', 'Petrol', '1.2 litre', '2WD', 74100, 'White', 5, false, 'used-swift'],
            ['used', 2021, 'Nissan', 'Almera', null, 'Sedan', 'automatic', 'Petrol', '1.0 litre turbo', '2WD', 33800, 'Gun metallic', 5, false, 'used-almera'],
            ['used', 2021, 'Nissan', 'Kicks', 'SV', 'Crossover', 'automatic', 'Petrol', '1.6 litre', '2WD', 29600, 'Grey', 5, true, 'used-kicks'],
            ['new', (int) now()->year, 'Suzuki', 'Jimny', 'GL', 'SUV', 'manual', 'Petrol', '1.5 litre', '4WD', null, 'Chiffon ivory', 4, false, 'model-jimny', 'model-jimny-trail', 'model:jimny'],
            ['new', (int) now()->year, 'Nissan', 'Frontier', 'PRO-4X', 'Pickup', 'automatic', 'Diesel', '2.5 litre', '4WD', null, 'Boulder grey', 5, false, 'model-frontier', 'model-frontier-interior', 'model:frontier'],
            ['new', (int) now()->year, 'Suzuki', 'Fronx', 'GL', 'Crossover', 'automatic', 'Petrol', '1.5 litre', '2WD', null, 'White pearl', 5, false, 'model-fronx', 'model-fronx-interior', 'model:fronx'],
        ];

        $out = [];
        foreach ($rows as $i => $r) {
            $modelKey = null;
            $photos = [];
            foreach (array_slice($r, 14) as $extra) {
                if (str_starts_with((string) $extra, 'model:')) {
                    $modelKey = substr((string) $extra, 6);
                } else {
                    $photos[] = $extra;
                }
            }
            $vehicle = Vehicle::updateOrCreate(['slug' => 'sample-'.Str::slug("{$r[1]} {$r[2]} {$r[3]}")], [
                'stock_no' => sprintf('DEMO-%03d', $i + 1),
                'condition' => Condition::from($r[0]),
                'status' => $i === 4 ? VehicleStatus::Reserved : VehicleStatus::Available,
                'make' => $r[2], 'model' => $r[3], 'trim' => $r[4], 'year' => $r[1], 'body_type' => $r[5],
                'transmission' => Transmission::from($r[6]), 'fuel' => $r[7], 'engine' => $r[8], 'drive' => $r[9],
                'mileage_km' => $r[10], 'colour' => $r[11], 'seats' => $r[12], 'featured' => $r[13], 'model_key' => $modelKey,
                'price' => null,
                'description' => 'Sample listing for the website preview. The dealership replaces this with real stock, photos and prices in the dashboard.',
                'features' => $r[0] === 'new' ? null : ['Air conditioning', 'Power windows', 'Reverse camera', 'Bluetooth'],
                'published' => true,
                'is_demo' => true,
            ]);
            if ($vehicle->photos()->doesntExist()) {
                foreach ($photos as $sort => $key) {
                    $source = public_path("img/{$key}-1920.webp");
                    if (! File::exists($source)) {
                        continue;
                    }
                    $path = "vehicles/{$vehicle->id}/{$key}.webp";
                    $thumb = "vehicles/{$vehicle->id}/{$key}-thumb.webp";
                    Storage::disk('uploads')->put($path, File::get($source));
                    Storage::disk('uploads')->put($thumb, File::get(public_path("img/{$key}-960.webp")));
                    $vehicle->photos()->create(['path' => $path, 'thumb_path' => $thumb, 'width' => 1920, 'height' => 1280, 'sort' => $sort, 'credit' => $key]);
                }
            }
            $out[] = $vehicle;
        }

        return $out;
    }

    /**
     * @param  array<string, User>  $users
     * @param  list<Vehicle>  $vehicles
     */
    private function leads(array $users, array $vehicles): void
    {
        if (Lead::exists()) {
            return;
        }
        mt_srand(1972);
        $first = ['Shanice', 'Kareem', 'Denise', 'Jermaine', 'Tricia', 'Andre', 'Keisha', 'Marlon', 'Simone', 'Rohan', 'Natalie', 'Dwayne', 'Alicia', 'Calvin', 'Monique', 'Tevin', 'Janelle', 'Kevin', 'Petra', 'Shawn'];
        $last = ['Joseph', 'Thomas', 'Francis', 'Samuel', 'Henry', 'Christian', 'Roberts', 'Benjamin', 'Simon', 'Jacobs', 'Edwards', 'Charles', 'Michael', 'Isaac', 'Matthew'];
        $sales = [$users['sales@hadeed.test'], $users['sales2@hadeed.test']];
        $service = $users['service@hadeed.test'];
        $finance = $users['finance@hadeed.test'];
        $serviceDays = Booking::days();

        $make = function (LeadType $type, Carbon $at, array $extra = []) use ($first, $last): Lead {
            $name = $first[array_rand($first)].' '.$last[array_rand($last)];
            $lead = Lead::create(array_merge([
                'type' => $type,
                'status' => LeadStatus::New,
                'name' => $name,
                'phone' => '268-'.mt_rand(460, 789).'-'.str_pad((string) mt_rand(0, 9999), 4, '0', STR_PAD_LEFT),
                'email' => strtolower(str_replace(' ', '.', $name)).'@example.com',
                'contact_pref' => Arr::random(['phone', 'phone', 'whatsapp', 'email']),
                'source_url' => url('/'),
            ], $extra));
            $lead->forceFill(['created_at' => $at, 'updated_at' => $at])->saveQuietly();
            $lead->forceFill(['reference' => sprintf('%s-%s-%05d', $type->prefix(), $at->format('y'), $lead->id)])->saveQuietly();
            $lead->notes()->create(['kind' => 'system', 'body' => 'Received from the website'.($lead->subject() ? ' about '.$lead->subject() : '').'.'])
                ->forceFill(['created_at' => $at])->saveQuietly();

            return $lead;
        };

        $progress = function (Lead $lead, User $by, LeadStatus $to, int $minutes): void {
            $at = $lead->created_at->copy()->addMinutes($minutes);
            $lead->forceFill(['status' => $to, 'status_changed_at' => $at, 'first_contacted_at' => $at, 'assigned_to' => $by->id])->saveQuietly();
            $lead->notes()->create(['user_id' => $by->id, 'kind' => 'assign', 'body' => "Assigned to {$by->name}."])->forceFill(['created_at' => $at])->saveQuietly();
            $lead->notes()->create(['user_id' => $by->id, 'kind' => 'note', 'body' => Arr::random([
                'Called and spoke to the customer. Booked in for Friday morning.',
                'WhatsApp sent with brochure and directions.',
                'Left a voicemail, will try again after 4.',
                'Customer wants to compare with a pre-owned unit first.',
            ])])->forceFill(['created_at' => $at])->saveQuietly();
            $lead->notes()->create(['user_id' => $by->id, 'kind' => 'status', 'body' => 'Status changed to '.$to->label().'.'])->forceFill(['created_at' => $at])->saveQuietly();
        };

        $newStock = array_values(array_filter($vehicles, fn (Vehicle $v) => $v->condition === Condition::New));
        $usedStock = array_values(array_filter($vehicles, fn (Vehicle $v) => $v->condition === Condition::Used));
        $models = array_keys((array) config('hadeed.models'));

        // Thirty days of history.
        for ($d = 29; $d >= 1; $d--) {
            $day = today()->subDays($d)->setTime(mt_rand(8, 18), mt_rand(0, 59));
            for ($n = 0, $count = mt_rand(1, 4); $n < $count; $n++) {
                $at = $day->copy()->addMinutes($n * 47);
                $type = Arr::random([LeadType::TestDrive, LeadType::TestDrive, LeadType::Service, LeadType::Service, LeadType::Parts, LeadType::TradeIn, LeadType::Finance, LeadType::Enquiry]);
                $lead = $make($type, $at, $this->payload($type, $usedStock, $newStock, $models, $at));
                $who = match ($type) {
                    LeadType::Service, LeadType::Parts => $service,
                    LeadType::Finance => $finance,
                    default => $sales[array_rand($sales)],
                };
                $to = Arr::random([LeadStatus::Contacted, LeadStatus::Appointment, LeadStatus::Negotiating, LeadStatus::Sold, LeadStatus::Sold, LeadStatus::Lost]);
                $progress($lead, $who, $to, mt_rand(12, 300));
                if ($type === LeadType::Finance) {
                    $lead->forceFill(['finance_status' => Arr::random([FinanceStatus::Reviewing, FinanceStatus::NeedsDocuments, FinanceStatus::PreQualified, FinanceStatus::PreQualified, FinanceStatus::Declined])])->saveQuietly();
                }
            }
        }

        // Today: some fresh, some overdue (older than two hours and nobody has called).
        foreach ([[LeadType::TestDrive, 26], [LeadType::Finance, 5], [LeadType::TradeIn, 3.5], [LeadType::TestDrive, 2.6], [LeadType::Enquiry, 1.2], [LeadType::Service, 0.6], [LeadType::TestDrive, 0.2], [LeadType::Parts, 4]] as [$type, $hoursAgo]) {
            $at = now()->subMinutes((int) ($hoursAgo * 60));
            $make($type, $at, $this->payload($type, $usedStock, $newStock, $models, $at));
        }

        // Upcoming service bookings so the workshop calendar has something in it.
        foreach (array_slice($serviceDays, 0, 10) as $i => $day) {
            for ($n = 0, $count = [6, 8, 3, 5, 2, 7, 1, 4, 2, 3][$i]; $n < $count; $n++) {
                $at = now()->subDays(mt_rand(1, 14))->setTime(mt_rand(8, 16), mt_rand(0, 59));
                $lead = $make(LeadType::Service, $at, array_merge($this->payload(LeadType::Service, $usedStock, $newStock, $models, $at), [
                    'appointment_date' => $day->toDateString(),
                    'appointment_time' => Arr::random((array) config('hadeed.booking.service_dropoff_slots')),
                ]));
                $progress($lead, $service, LeadStatus::Appointment, mt_rand(15, 120));
            }
        }

        // Finance files are placeholders, clearly marked, so the document permissions can be demonstrated.
        foreach (Lead::where('type', LeadType::Finance)->get() as $lead) {
            foreach (['id' => 'Photo ID', 'payslip' => 'Pay slip'] as $kind => $label) {
                $path = "leads/{$lead->id}/demo-{$kind}.pdf";
                Storage::disk('local')->put($path, $this->placeholderPdf("SAMPLE {$label} for {$lead->reference}. Not a real document."));
                $lead->files()->create(['kind' => $kind, 'path' => $path, 'original_name' => "sample-{$kind}.pdf", 'mime' => 'application/pdf', 'size' => 600]);
            }
        }
    }

    /**
     * @param  list<Vehicle>  $used
     * @param  list<Vehicle>  $new
     * @param  list<string>  $models
     * @return array<string, mixed>
     */
    private function payload(LeadType $type, array $used, array $new, array $models, Carbon $at): array
    {
        $makes = [['Nissan', 'X-Trail'], ['Suzuki', 'Swift'], ['Toyota', 'Corolla'], ['Suzuki', 'Vitara'], ['Nissan', 'Navara'], ['Honda', 'Fit'], ['Suzuki', 'Jimny']];
        [$mk, $md] = $makes[array_rand($makes)];

        return match ($type) {
            LeadType::TestDrive => array_merge(
                mt_rand(0, 1) ? ['vehicle_id' => $used[array_rand($used)]->id] : ['model_key' => $models[array_rand($models)]],
                ['appointment_date' => $this->weekday($at->copy()->addDays(mt_rand(1, 6))), 'appointment_time' => Arr::random(['09:00', '10:00', '11:00', '13:00', '14:00']), 'details' => ['licence_confirmed' => true]],
            ),
            LeadType::Service => [
                'appointment_date' => $this->weekday($at->copy()->addDays(mt_rand(1, 8))), 'appointment_time' => '08:00',
                'details' => ['registration' => 'A'.mt_rand(10000, 99999), 'vehicle_make' => $mk, 'vehicle_model' => $md, 'vehicle_year' => mt_rand(2012, 2024),
                    'mileage' => mt_rand(8, 160) * 1000, 'service_type' => Arr::random(array_keys((array) config('hadeed.service_types'))), 'waiting' => (bool) mt_rand(0, 1)],
            ],
            LeadType::Parts => ['details' => ['vin' => 'JS3TD'.strtoupper(substr(md5((string) mt_rand()), 0, 12)), 'vehicle_make' => $mk, 'vehicle_model' => $md,
                'vehicle_year' => mt_rand(2010, 2024), 'part' => Arr::random(['Front brake pads', 'Cabin air filter', 'Wiper blades', 'Side mirror glass, driver side', 'Oil filter and drain plug washer']), 'quantity' => 1]],
            LeadType::TradeIn => [
                'model_key' => $models[array_rand($models)],
                'details' => ['vehicle_make' => $mk, 'vehicle_model' => $md, 'vehicle_year' => mt_rand(2008, 2021), 'mileage' => mt_rand(40, 200) * 1000,
                    'transmission' => 'automatic', 'condition' => Arr::random(['good', 'fair', 'excellent']), 'owed' => (bool) mt_rand(0, 1)],
            ],
            LeadType::Finance => [
                'model_key' => $models[array_rand($models)], 'consented_at' => $at, 'finance_status' => FinanceStatus::Received,
                'details' => ['residency' => Arr::random(['citizen', 'citizen', 'resident', 'work_permit']), 'employment' => Arr::random(['employed', 'employed', 'self_employed']),
                    'employer' => Arr::random(['Government of Antigua and Barbuda', 'A hotel in Dickenson Bay', 'Self']), 'job_title' => Arr::random(['Teacher', 'Supervisor', 'Nurse', 'Accounts clerk']),
                    'years_employed' => Arr::random(['1_3', '3_5', 'o5']), 'income_band' => Arr::random(['3_5', '5_8', '8_12']), 'deposit' => mt_rand(5, 30) * 1000, 'term' => Arr::random([48, 60, 72])],
            ],
            LeadType::Enquiry => ['message' => Arr::random(['Do you have the Jimny in stock in white?', 'Please call me back about the S-Presso.', 'What are your service department hours on Fridays?', 'Is the Frontier available in manual?']),
                'details' => ['topic' => Arr::random(['sales', 'callback', 'service'])]],
        };
    }

    /** The same day if the showroom is open, otherwise the next weekday. */
    private function weekday(Carbon $day): string
    {
        while ($day->isWeekend()) {
            $day->addDay();
        }

        return $day->toDateString();
    }

    private function placeholderPdf(string $text): string
    {
        $stream = 'BT /F1 14 Tf 60 760 Td ('.addcslashes($text, '()\\').') Tj ET';
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
            '<< /Length '.strlen($stream)." >>\nstream\n{$stream}\nendstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];
        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $i => $obj) {
            $offsets[] = strlen($pdf);
            $pdf .= ($i + 1)." 0 obj\n{$obj}\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";
        foreach ($offsets as $o) {
            $pdf .= sprintf("%010d 00000 n \n", $o);
        }

        return $pdf.'trailer << /Size '.(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
    }
}
