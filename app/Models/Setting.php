<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Small key/value store for things the dealership changes without a deploy:
 * the indicative finance rate (blank until FDC supplies one) and daily service capacity.
 *
 * @property string $key
 * @property string|null $value
 */
class Setting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    /** @var array<string, string|null> */
    private static array $cache = [];

    public static function get(string $key, ?string $default = null): ?string
    {
        if (! array_key_exists($key, self::$cache)) {
            self::$cache[$key] = self::query()->whereKey($key)->value('value');
        }
        $value = self::$cache[$key];

        return $value === null || $value === '' ? $default : $value;
    }

    public static function put(string $key, ?string $value): void
    {
        self::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        self::$cache[$key] = $value;
    }

    public static function flush(): void
    {
        self::$cache = [];
    }

    /** Annual percentage, or null while no rate has been published (the estimator stays hidden). */
    public static function financeRate(): ?float
    {
        $rate = self::get('finance_indicative_rate');

        return is_numeric($rate) && (float) $rate > 0 ? (float) $rate : null;
    }

    public static function serviceCapacity(): int
    {
        return max(1, (int) self::get('service_daily_capacity', (string) config('hadeed.booking.service_daily_capacity', 8)));
    }
}
