<?php

namespace App\Http\Controllers;

use App\Enums\LeadStatus;
use App\Enums\LeadType;
use App\Models\Lead;
use App\Models\Vehicle;
use App\Support\Booking;
use App\Support\LeadIntake;
use App\Support\Lineup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Illuminate\View\View;

/**
 * The five booking/request flows plus the contact form. Each one validates,
 * hands over to LeadIntake (reference + emails) and lands on /thanks.
 */
class LeadFormController extends Controller
{
    /** Photos people take on their phones. HEIC is what iPhones save by default. */
    private const string PHOTO_MIMES = 'mimes:jpg,jpeg,png,webp,heic,heif';

    private const string DOC_MIMES = 'mimes:pdf,jpg,jpeg,png,heic,heif';

    // ---------------------------------------------------------------- test drive

    public function testDrive(Request $request): View
    {
        $vehicle = Vehicle::available()->where('slug', input_string($request->query('vehicle')))->first();
        $model = array_key_exists(input_string($request->query('model')), Lineup::options()) ? input_string($request->query('model')) : null;

        return view('pages.forms.test-drive', [
            'vehicle' => $vehicle,
            'model' => $model,
            'interests' => $this->interestOptions('Not sure yet, help me choose'),
            'days' => Booking::days(),
            'slots' => $this->slotGrid(),
        ]);
    }

    public function storeTestDrive(Request $request): RedirectResponse
    {
        if (LeadIntake::isBot($request)) {
            return to_route('thanks');
        }
        $data = $request->validate($this->contactRules() + [
            'interest' => ['required', 'string', 'max:60', function (string $attr, mixed $value, \Closure $fail) {
                if (! $this->parseInterest(input_string($value))) {
                    $fail('Choose a vehicle from the list.');
                }
            }],
            'appointment_date' => ['required', 'date_format:Y-m-d', function (string $attr, mixed $value, \Closure $fail) {
                if (! Booking::isBookableDay((string) $value)) {
                    $fail('We are closed that day. Choose a weekday in the next '.config('hadeed.booking.days_ahead').' days.');
                }
            }],
            'appointment_time' => ['required', Rule::in((array) config('hadeed.booking.test_drive_slots'))],
            'licence' => ['accepted'],
            'message' => ['nullable', 'string', 'max:1500'],
        ], ['licence.accepted' => 'Please confirm you hold a valid driving licence.', 'interest.required' => 'Choose the vehicle you would like to drive.']);

        $day = Carbon::parse($data['appointment_date']);
        if (! in_array($data['appointment_time'], Booking::testDriveTimes($day), true)) {
            return back()->withInput()->withErrors(['appointment_time' => 'That time is after closing on '.$day->format('l').'s. Choose an earlier time.']);
        }

        return Cache::lock('test-drive:'.$data['appointment_date'].$data['appointment_time'], 10)->block(5, function () use ($data, $request) {
            if (Booking::testDriveFull($data['appointment_date'], $data['appointment_time'])) {
                return back()->withInput()->withErrors(['appointment_time' => 'That time has just been booked. Please choose another.']);
            }
            [$vehicleId, $modelKey] = $this->parseInterest($data['interest']) ?? [null, null];
            LeadIntake::create(LeadType::TestDrive, $this->contactFields($data) + [
                'vehicle_id' => $vehicleId,
                'model_key' => $modelKey,
                'appointment_date' => $data['appointment_date'],
                'appointment_time' => $data['appointment_time'],
                'message' => $data['message'] ?? null,
                'details' => ['licence_confirmed' => true],
            ], [], $request);

            return to_route('thanks');
        });
    }

    // ---------------------------------------------------------------- service

    public function storeService(Request $request): RedirectResponse
    {
        if (LeadIntake::isBot($request)) {
            return to_route('thanks');
        }
        $data = $request->validate($this->contactRules() + [
            'registration' => ['required', 'string', 'max:12', 'regex:/^[A-Za-z0-9 \-]+$/'],
            'vehicle_make' => ['required', 'string', 'max:40'],
            'vehicle_model' => ['required', 'string', 'max:60'],
            'vehicle_year' => ['nullable', 'integer', 'min:1970', 'max:'.(now()->year + 1)],
            'mileage' => ['required', 'integer', 'min:0', 'max:1500000'],
            'service_type' => ['required', Rule::in(array_keys((array) config('hadeed.service_types')))],
            'appointment_date' => ['required', 'date_format:Y-m-d', function (string $attr, mixed $value, \Closure $fail) {
                if (! Booking::isBookableDay((string) $value)) {
                    $fail('The workshop is closed that day. Choose a weekday in the next '.config('hadeed.booking.days_ahead').' days.');
                }
            }],
            'appointment_time' => ['required', Rule::in((array) config('hadeed.booking.service_dropoff_slots'))],
            'waiting' => ['nullable', 'boolean'],
            'message' => ['nullable', 'required_if:service_type,other', 'string', 'max:1500'],
        ], [
            'registration.regex' => 'Use letters and numbers only, as on your licence plate.',
            'message.required_if' => 'Tell us what you need done.',
        ], ['registration' => 'licence plate', 'vehicle_make' => 'make', 'vehicle_model' => 'model']);

        // The day's capacity is checked and claimed under a lock, so two people can't take the last place.
        return Cache::lock('service-day:'.$data['appointment_date'], 10)->block(5, function () use ($data, $request) {
            if (Booking::serviceRemaining($data['appointment_date']) < 1) {
                return back()->withInput()->withErrors(['appointment_date' => 'That day is fully booked. Please choose another day.'])->withFragment('book');
            }
            LeadIntake::create(LeadType::Service, $this->contactFields($data) + [
                'appointment_date' => $data['appointment_date'],
                'appointment_time' => $data['appointment_time'],
                'message' => $data['message'] ?? null,
                'details' => [
                    'registration' => strtoupper(trim($data['registration'])),
                    'vehicle_make' => $data['vehicle_make'],
                    'vehicle_model' => $data['vehicle_model'],
                    'vehicle_year' => $data['vehicle_year'] ?? null,
                    'mileage' => (int) $data['mileage'],
                    'service_type' => $data['service_type'],
                    'waiting' => (bool) ($data['waiting'] ?? false),
                ],
            ], [], $request);

            return to_route('thanks');
        });
    }

    // ---------------------------------------------------------------- parts

    public function parts(): View
    {
        return view('pages.forms.parts');
    }

    public function storeParts(Request $request): RedirectResponse
    {
        if (LeadIntake::isBot($request)) {
            return to_route('thanks');
        }
        $request->merge(['vin' => strtoupper(str_replace([' ', '-'], '', input_string($request->input('vin'))))]);
        $data = $request->validate($this->contactRules() + [
            // 17 characters, never I, O or Q (ISO 3779).
            'vin' => ['required', 'string', 'regex:/^[A-HJ-NPR-Z0-9]{17}$/'],
            'vehicle_make' => ['required', 'string', 'max:40'],
            'vehicle_model' => ['required', 'string', 'max:60'],
            'vehicle_year' => ['nullable', 'integer', 'min:1970', 'max:'.(now()->year + 1)],
            'part' => ['required', 'string', 'max:160'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:50'],
            'photo' => ['nullable', 'file', self::PHOTO_MIMES, 'max:8192'],
            'message' => ['nullable', 'string', 'max:1500'],
        ], ['vin.regex' => 'A VIN is 17 letters and numbers (never I, O or Q). It is on your registration and at the bottom of the windscreen.'], ['vin' => 'VIN', 'vehicle_make' => 'make', 'vehicle_model' => 'model']);

        LeadIntake::create(LeadType::Parts, $this->contactFields($data) + [
            'message' => $data['message'] ?? null,
            'details' => [
                'vin' => $data['vin'],
                'vehicle_make' => $data['vehicle_make'],
                'vehicle_model' => $data['vehicle_model'],
                'vehicle_year' => $data['vehicle_year'] ?? null,
                'part' => $data['part'],
                'quantity' => (int) ($data['quantity'] ?? 1),
            ],
        ], ['part' => array_filter([$request->file('photo')])], $request);

        return to_route('thanks');
    }

    // ---------------------------------------------------------------- trade-in

    public function tradeIn(): View
    {
        return view('pages.forms.trade-in', ['interests' => $this->interestOptions()]);
    }

    public function storeTradeIn(Request $request): RedirectResponse
    {
        if (LeadIntake::isBot($request)) {
            return to_route('thanks');
        }
        $data = $request->validate($this->contactRules() + [
            'vehicle_make' => ['required', 'string', 'max:40'],
            'vehicle_model' => ['required', 'string', 'max:60'],
            'vehicle_year' => ['required', 'integer', 'min:1970', 'max:'.(now()->year + 1)],
            'mileage' => ['required', 'integer', 'min:0', 'max:1500000'],
            'transmission' => ['nullable', Rule::in(['automatic', 'manual'])],
            'condition' => ['required', Rule::in(['excellent', 'good', 'fair', 'needs_work'])],
            'registration' => ['nullable', 'string', 'max:12'],
            'owed' => ['nullable', 'boolean'],
            'interest' => ['nullable', 'string', 'max:60'],
            'photos' => ['nullable', 'array', 'max:6'],
            'photos.*' => ['file', self::PHOTO_MIMES, 'max:8192'],
            'message' => ['nullable', 'string', 'max:1500'],
        ], ['photos.max' => 'Up to 6 photos, please.'], ['vehicle_make' => 'make', 'vehicle_model' => 'model', 'vehicle_year' => 'year']);

        [$vehicleId, $modelKey] = $this->parseInterest(input_string($data['interest'] ?? '')) ?? [null, null];
        LeadIntake::create(LeadType::TradeIn, $this->contactFields($data) + [
            'vehicle_id' => $vehicleId,
            'model_key' => $modelKey,
            'message' => $data['message'] ?? null,
            'details' => [
                'vehicle_make' => $data['vehicle_make'],
                'vehicle_model' => $data['vehicle_model'],
                'vehicle_year' => (int) $data['vehicle_year'],
                'mileage' => (int) $data['mileage'],
                'transmission' => $data['transmission'] ?? null,
                'condition' => $data['condition'],
                'registration' => isset($data['registration']) ? strtoupper($data['registration']) : null,
                'owed' => (bool) ($data['owed'] ?? false),
            ],
        ], ['trade_in' => array_values($request->file('photos', []))], $request);

        return to_route('thanks');
    }

    // ---------------------------------------------------------------- finance

    public function finance(Request $request): View
    {
        return view('pages.forms.finance', [
            'interests' => $this->interestOptions(),
            'selected' => input_string($request->query('interest')) ?: null,
        ]);
    }

    public function storeFinance(Request $request): RedirectResponse
    {
        if (LeadIntake::isBot($request)) {
            return to_route('thanks');
        }
        $validator = validator($request->all(), $this->contactRules() + [
            'residency' => ['required', Rule::in(array_keys((array) config('hadeed.finance.residency')))],
            'employment' => ['required', Rule::in(array_keys((array) config('hadeed.finance.employment')))],
            'employer' => ['nullable', 'required_if:employment,employed', 'string', 'max:120'],
            'job_title' => ['nullable', 'string', 'max:120'],
            'years_employed' => ['required', Rule::in(array_keys((array) config('hadeed.finance.years_employed')))],
            'income_band' => ['required', Rule::in(array_keys((array) config('hadeed.finance.income_bands')))],
            'deposit' => ['required', 'integer', 'min:0', 'max:5000000'],
            'term' => ['nullable', Rule::in(array_map('strval', (array) config('hadeed.finance.terms')))],
            'interest' => ['required', 'string', 'max:60'],
            'id_document' => ['nullable', 'file', self::DOC_MIMES, 'max:8192'],
            'payslips' => ['nullable', 'array', 'max:3'],
            'payslips.*' => ['file', self::DOC_MIMES, 'max:8192'],
            'consent' => ['accepted'],
            'message' => ['nullable', 'string', 'max:1500'],
        ], [
            'consent.accepted' => 'We need your consent to share these details with Finance & Development Co.',
            'employer.required_if' => 'Tell us who you work for.',
            'interest.required' => 'Choose a vehicle, or "Not sure yet".',
        ], ['income_band' => 'monthly income', 'years_employed' => 'time in this job']);

        // Belt and braces: this form never asks for bank details, and refuses them if a client sends them.
        $validator->after(function (Validator $v) use ($request) {
            foreach (['account_number', 'bank_account', 'routing_number', 'card_number', 'pin'] as $field) {
                if ($request->filled($field)) {
                    $v->errors()->add('message', 'Please do not send bank or card numbers. FDC will never ask for them online.');
                }
            }
        });
        $data = $validator->validate();

        $interest = input_string($data['interest']);
        [$vehicleId, $modelKey] = $this->parseInterest($interest) ?? [null, null];
        LeadIntake::create(LeadType::Finance, $this->contactFields($data) + [
            'vehicle_id' => $vehicleId,
            'model_key' => $modelKey,
            'consented_at' => now(),
            'message' => $data['message'] ?? null,
            'details' => [
                'residency' => $data['residency'],
                'employment' => $data['employment'],
                'employer' => $data['employer'] ?? null,
                'job_title' => $data['job_title'] ?? null,
                'years_employed' => $data['years_employed'],
                'income_band' => $data['income_band'],
                'deposit' => (int) $data['deposit'],
                'term' => isset($data['term']) ? (int) $data['term'] : null,
                'undecided' => $interest === 'undecided',
            ],
        ], [
            'id' => array_filter([$request->file('id_document')]),
            'payslip' => array_values($request->file('payslips', [])),
        ], $request);

        return to_route('thanks');
    }

    // ---------------------------------------------------------------- enquiry

    public function storeEnquiry(Request $request): RedirectResponse
    {
        if (LeadIntake::isBot($request)) {
            return to_route('thanks');
        }
        $data = $request->validate($this->contactRules() + [
            'topic' => ['required', Rule::in(['sales', 'callback', 'service', 'other'])],
            'interest' => ['nullable', 'string', 'max:60'],
            'message' => ['required', 'string', 'max:3000'],
        ]);
        [$vehicleId, $modelKey] = $this->parseInterest(input_string($data['interest'] ?? '')) ?? [null, null];
        LeadIntake::create(LeadType::Enquiry, $this->contactFields($data) + [
            'vehicle_id' => $vehicleId,
            'model_key' => $modelKey,
            'message' => $data['message'],
            'details' => ['topic' => $data['topic']],
        ], [], $request);

        return to_route('thanks');
    }

    public function thanks(Request $request): View|RedirectResponse
    {
        $last = $request->session()->get('last_lead');

        return is_array($last) ? view('pages.thanks', ['reference' => $last['reference'], 'type' => LeadType::tryFrom($last['type']) ?? LeadType::Enquiry]) : to_route('home');
    }

    // ---------------------------------------------------------------- shared

    /** @return array<string, list<mixed>> */
    private function contactRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+()\-. ]{7,}$/'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'contact_pref' => ['nullable', Rule::in(['phone', 'whatsapp', 'email'])],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function contactFields(array $data): array
    {
        return [
            'name' => trim((string) $data['name']),
            'phone' => trim((string) $data['phone']),
            'email' => strtolower(trim((string) $data['email'])),
            'contact_pref' => $data['contact_pref'] ?? 'phone',
        ];
    }

    /**
     * "v:12" is a stock unit, "m:jimny" a line-up model, "undecided" neither.
     *
     * @return array{0: int|null, 1: string|null}|null
     */
    private function parseInterest(string $value): ?array
    {
        if ($value === 'undecided') {
            return [null, null];
        }
        if (str_starts_with($value, 'v:') && ctype_digit(substr($value, 2))) {
            $id = Vehicle::listed()->whereKey((int) substr($value, 2))->value('id');

            return $id ? [(int) $id, null] : null;
        }
        if (str_starts_with($value, 'm:') && array_key_exists(substr($value, 2), Lineup::options())) {
            return [null, substr($value, 2)];
        }

        return null;
    }

    /**
     * The grouped "which car?" options shared by the test-drive, trade-in and finance forms,
     * in the "m:key" / "v:id" / "undecided" format parseInterest() reads back.
     *
     * @return array<string, array<string, string>>
     */
    private function interestOptions(string $undecided = 'Not sure yet'): array
    {
        return [
            'New models' => collect(Lineup::options())->mapWithKeys(fn (string $label, string $key) => ['m:'.$key => 'New '.$label])->all(),
            'In stock now' => Vehicle::available()->orderBy('condition')->orderBy('make')->orderBy('model')->get(['id', 'year', 'make', 'model', 'trim', 'condition'])
                ->mapWithKeys(fn (Vehicle $v) => ['v:'.$v->id => $v->title.($v->condition->value === 'used' ? ' (pre-owned)' : ' (in stock)')])->all(),
            'Other' => ['undecided' => $undecided],
        ];
    }

    /** @return array<string, list<string>> date => bookable times with a place left */
    private function slotGrid(): array
    {
        $days = Booking::days();
        if ($days === []) {
            return [];
        }
        // One query for the whole window, counted per "date time".
        $taken = Lead::where('type', LeadType::TestDrive)->where('status', '!=', LeadStatus::Lost)
            ->whereBetween('appointment_date', [$days[0]->toDateString(), end($days)->toDateString()])
            ->get(['appointment_date', 'appointment_time'])
            ->countBy(fn (Lead $l) => $l->appointment_date?->toDateString().' '.$l->appointment_time);
        $perSlot = (int) config('hadeed.booking.test_drives_per_slot', 2);

        $grid = [];
        foreach ($days as $day) {
            $date = $day->toDateString();
            $grid[$date] = array_values(array_filter(Booking::testDriveTimes($day), fn (string $t) => (int) ($taken[$date.' '.$t] ?? 0) < $perSlot));
        }

        return $grid;
    }
}
