<?php

namespace App\Models;

use App\Enums\Condition;
use App\Enums\Transmission;
use App\Enums\VehicleStatus;
use Database\Factories\VehicleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $slug
 * @property string|null $stock_no
 * @property Condition $condition
 * @property VehicleStatus $status
 * @property string $make
 * @property string $model
 * @property string|null $model_key
 * @property string|null $trim
 * @property int $year
 * @property string $body_type
 * @property Transmission $transmission
 * @property string|null $fuel
 * @property string|null $engine
 * @property string|null $drive
 * @property int|null $mileage_km
 * @property string|null $colour
 * @property int|null $seats
 * @property int|null $price
 * @property string|null $description
 * @property list<string>|null $features
 * @property bool $featured
 * @property bool $published
 * @property bool $is_demo
 * @property Carbon|null $sold_at
 * @property Carbon $created_at
 * @property-read string $title
 * @property-read Collection<int, VehiclePhoto> $photos
 * @property-read VehiclePhoto|null $coverPhoto
 */
class Vehicle extends Model
{
    /** @use HasFactory<VehicleFactory> */
    use HasFactory;

    protected $fillable = [
        'slug', 'stock_no', 'condition', 'status', 'make', 'model', 'model_key', 'trim', 'year', 'body_type', 'transmission',
        'fuel', 'engine', 'drive', 'mileage_km', 'colour', 'seats', 'price', 'description', 'features', 'featured', 'published', 'is_demo', 'sold_at',
    ];

    #[\Override]
    protected function casts(): array
    {
        return [
            'condition' => Condition::class,
            'status' => VehicleStatus::class,
            'transmission' => Transmission::class,
            'features' => 'array',
            'featured' => 'boolean',
            'published' => 'boolean',
            'is_demo' => 'boolean',
            'sold_at' => 'datetime',
            'year' => 'integer',
            'price' => 'integer',
            'mileage_km' => 'integer',
        ];
    }

    #[\Override]
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** @return HasMany<VehiclePhoto, $this> */
    public function photos(): HasMany
    {
        return $this->hasMany(VehiclePhoto::class)->orderBy('sort')->orderBy('id');
    }

    /** @return HasOne<VehiclePhoto, $this> */
    public function coverPhoto(): HasOne
    {
        return $this->hasOne(VehiclePhoto::class)->ofMany(['sort' => 'min', 'id' => 'min']);
    }

    public function getTitleAttribute(): string
    {
        return trim("{$this->year} {$this->make} {$this->model} ".($this->trim ?? ''));
    }

    public function priceLabel(): string
    {
        return $this->price ? money($this->price) : 'Call for price';
    }

    /**
     * Shown on the public site: published and not sold more than a week ago.
     *
     * @param  Builder<self>  $query
     */
    public function scopeListed(Builder $query): void
    {
        $query->where('published', true)
            ->where(fn (Builder $q) => $q->where('status', '!=', VehicleStatus::Sold)->orWhere('sold_at', '>=', now()->subDays(7)));
    }

    /** @param Builder<self> $query */
    public function scopeAvailable(Builder $query): void
    {
        $query->where('published', true)->where('status', VehicleStatus::Available);
    }

    /**
     * Inventory filters. Every value is checked against a whitelist, so the same
     * array works from a JS-free GET form or a crafted URL.
     *
     * @param  Builder<self>  $query
     * @param  array<string, mixed>  $f
     */
    public function scopeFilter(Builder $query, array $f): void
    {
        $query
            ->when($f['make'] ?? null, fn (Builder $q, $v) => $q->where('make', $v))
            ->when($f['body'] ?? null, fn (Builder $q, $v) => $q->where('body_type', $v))
            ->when($f['transmission'] ?? null, fn (Builder $q, $v) => $q->where('transmission', $v))
            ->when($f['year_min'] ?? null, fn (Builder $q, $v) => $q->where('year', '>=', $v))
            ->when($f['year_max'] ?? null, fn (Builder $q, $v) => $q->where('year', '<=', $v))
            ->when($f['price_max'] ?? null, fn (Builder $q, $v) => $q->whereNotNull('price')->where('price', '<=', $v));
    }

    /** Cover photo URL; the small copy by default, for cards and lists. */
    public function photoUrl(bool $large = false): ?string
    {
        $photo = $this->relationLoaded('photos') ? $this->photos->first() : $this->coverPhoto;

        return $photo ? ($large ? $photo->url() : $photo->thumbUrl()) : null;
    }
}
