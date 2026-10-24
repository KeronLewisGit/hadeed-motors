<?php

namespace App\Models;

use App\Enums\FinanceStatus;
use App\Enums\LeadStatus;
use App\Enums\LeadType;
use Database\Factories\LeadFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $reference
 * @property LeadType $type
 * @property LeadStatus $status
 * @property string $name
 * @property string $phone
 * @property string|null $email
 * @property string $contact_pref
 * @property int|null $vehicle_id
 * @property string|null $model_key
 * @property int|null $assigned_to
 * @property Carbon|null $appointment_date
 * @property string|null $appointment_time
 * @property array<string, mixed>|null $details
 * @property string|null $message
 * @property FinanceStatus|null $finance_status
 * @property Carbon|null $consented_at
 * @property string|null $source_url
 * @property Carbon|null $first_contacted_at
 * @property Carbon|null $status_changed_at
 * @property Carbon $created_at
 * @property-read Vehicle|null $vehicle
 * @property-read User|null $assignee
 * @property-read Collection<int, LeadNote> $notes
 * @property-read Collection<int, LeadFile> $files
 */
class Lead extends Model
{
    /** @use HasFactory<LeadFactory> */
    use HasFactory;

    /** A new lead nobody has called within this many hours is overdue. */
    public const int RESPONSE_HOURS = 2;

    protected $fillable = [
        'type', 'status', 'name', 'phone', 'email', 'contact_pref', 'vehicle_id', 'model_key', 'assigned_to',
        'appointment_date', 'appointment_time', 'details', 'message', 'finance_status', 'consented_at', 'source_url',
        'first_contacted_at', 'status_changed_at',
    ];

    #[\Override]
    protected function casts(): array
    {
        return [
            'type' => LeadType::class,
            'status' => LeadStatus::class,
            'finance_status' => FinanceStatus::class,
            'details' => 'array',
            'appointment_date' => 'date',
            'consented_at' => 'datetime',
            'first_contacted_at' => 'datetime',
            'status_changed_at' => 'datetime',
        ];
    }

    #[\Override]
    protected static function booted(): void
    {
        static::creating(function (self $lead): void {
            $lead->reference ??= 'pending-'.bin2hex(random_bytes(4));
            $lead->status ??= LeadStatus::New;
        });
        static::created(function (self $lead): void {
            $lead->forceFill([
                'reference' => sprintf('%s-%s-%05d', $lead->type->prefix(), $lead->created_at->format('y'), $lead->id),
            ])->saveQuietly();
        });
        static::saving(function (self $lead): void {
            $lead->phone_digits = preg_replace('/\D+/', '', (string) $lead->phone) ?: null;
        });
    }

    /** @return BelongsTo<Vehicle, $this> */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /** @return BelongsTo<User, $this> */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /** @return HasMany<LeadNote, $this> */
    public function notes(): HasMany
    {
        return $this->hasMany(LeadNote::class)->latest('id');
    }

    /** @return HasMany<LeadFile, $this> */
    public function files(): HasMany
    {
        return $this->hasMany(LeadFile::class);
    }

    /** What the customer was looking at: a stock unit, a line-up model, or nothing. */
    public function subject(): ?string
    {
        if ($this->vehicle) {
            return $this->vehicle->title;
        }
        if ($this->model_key && ($m = config('hadeed.models.'.$this->model_key))) {
            return config('hadeed.brands.'.$m['brand'].'.name').' '.$m['name'];
        }
        $d = $this->details ?? [];
        if (! empty($d['vehicle_make']) || ! empty($d['vehicle_model'])) {
            return trim(($d['vehicle_year'] ?? '').' '.($d['vehicle_make'] ?? '').' '.($d['vehicle_model'] ?? ''));
        }

        return null;
    }

    public function detail(string $key): mixed
    {
        return ($this->details ?? [])[$key] ?? null;
    }

    public function isOverdue(): bool
    {
        return $this->status === LeadStatus::New && $this->created_at->lt(now()->subHours(self::RESPONSE_HOURS));
    }

    public function moveTo(LeadStatus $status): bool
    {
        if ($this->status === $status) {
            return false;
        }
        $this->status = $status;
        $this->status_changed_at = now();
        if ($status !== LeadStatus::New) {
            $this->first_contacted_at ??= now();
        }

        return true;
    }

    /**
     * Leads this user's role works on.
     *
     * @param  Builder<self>  $query
     */
    public function scopeVisibleTo(Builder $query, User $user): void
    {
        $query->whereIn('type', $user->leadTypeValues());
    }

    /**
     * New and nobody has been in touch for longer than RESPONSE_HOURS.
     *
     * @param  Builder<self>  $query
     */
    public function scopeOverdue(Builder $query): void
    {
        $query->where('status', LeadStatus::New)->where('created_at', '<', now()->subHours(self::RESPONSE_HOURS));
    }

    /**
     * Service bookings that hold a place in the workshop on a day.
     *
     * @param  Builder<self>  $query
     */
    public function scopeHoldingServiceSlot(Builder $query): void
    {
        $query->where('type', LeadType::Service)->where('status', '!=', LeadStatus::Lost)->whereNotNull('appointment_date');
    }

    /**
     * @param  Builder<self>  $query
     * @param  array<string, mixed>  $f
     */
    public function scopeFilter(Builder $query, array $f): void
    {
        $query
            ->when($f['type'] ?? null, fn (Builder $q, $v) => $q->where('type', $v))
            ->when($f['status'] ?? null, fn (Builder $q, $v) => $v === 'open'
                ? $q->whereNotIn('status', [LeadStatus::Sold, LeadStatus::Lost])
                : $q->where('status', $v))
            ->when($f['assigned'] ?? null, fn (Builder $q, $v) => $v === 'none' ? $q->whereNull('assigned_to') : $q->where('assigned_to', $v))
            ->when($f['overdue'] ?? null, fn (Builder $q) => $q->overdue())
            ->when($f['q'] ?? null, function (Builder $q, $term) {
                $like = '%'.str_replace(['%', '_'], ['\%', '\_'], (string) $term).'%';
                $digits = preg_replace('/\D+/', '', (string) $term);
                $q->where(function (Builder $w) use ($like, $digits) {
                    $w->where('name', 'like', $like)->orWhere('reference', 'like', $like)->orWhere('email', 'like', $like);
                    if (strlen((string) $digits) >= 3) {
                        $w->orWhere('phone_digits', 'like', '%'.$digits.'%');
                    }
                });
            });
    }
}
