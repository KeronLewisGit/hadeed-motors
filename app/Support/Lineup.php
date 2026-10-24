<?php

namespace App\Support;

/** The new-vehicle line-up from config/hadeed.php. */
final class Lineup
{
    /** @return array<string, array<string, mixed>> keyed by model key */
    public static function all(?string $brand = null): array
    {
        $models = (array) config('hadeed.models');

        return $brand ? array_filter($models, fn (array $m) => $m['brand'] === $brand) : $models;
    }

    /**
     * One model by key. Looked up in the array, never as a dotted config path, so a
     * key like "jimny.trims" from a URL can't reach inside a model.
     *
     * @return array<string, mixed>|null
     */
    public static function get(string $key): ?array
    {
        $models = (array) config('hadeed.models');

        return array_key_exists($key, $models) && is_array($models[$key]) ? $models[$key] : null;
    }

    /** @return array<string, mixed>|null */
    public static function find(string $brand, string $key): ?array
    {
        $m = self::get($key);

        return $m !== null && $m['brand'] === $brand ? $m : null;
    }

    public static function brandOf(string $key): ?string
    {
        return self::get($key)['brand'] ?? null;
    }

    public static function label(string $key): string
    {
        $m = self::get($key);

        return $m !== null ? config('hadeed.brands.'.$m['brand'].'.name').' '.$m['name'] : $key;
    }

    /** @return array<string, string> model key => "Suzuki Jimny", grouped order by brand */
    public static function options(): array
    {
        $out = [];
        foreach (array_keys((array) config('hadeed.brands')) as $brand) {
            foreach (self::all($brand) as $key => $m) {
                $out[$key] = self::label($key);
            }
        }

        return $out;
    }

    /** @return list<string> */
    public static function brands(): array
    {
        return array_keys((array) config('hadeed.brands'));
    }
}
