<?php

namespace App\Http\Controllers;

use App\Enums\Condition;
use App\Enums\Transmission;
use App\Models\Setting;
use App\Models\Vehicle;
use App\Support\LoanEstimator;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Pre-owned stock. Filters are a plain GET form, so they work without JavaScript. */
class InventoryController extends Controller
{
    private const array SORTS = ['newest' => ['created_at', 'desc'], 'price_asc' => ['price', 'asc'], 'price_desc' => ['price', 'desc'], 'year_desc' => ['year', 'desc'], 'km_asc' => ['mileage_km', 'asc']];

    public const array PRICE_STEPS = [30000, 45000, 60000, 80000, 100000, 150000];

    public function index(Request $request): View
    {
        $base = Vehicle::listed()->where('condition', Condition::Used);
        $filters = $this->filters($request, (clone $base)->distinct()->pluck('make')->all(), (clone $base)->distinct()->pluck('body_type')->all());
        $sortKey = array_key_exists(input_string($request->query('sort')), self::SORTS) ? input_string($request->query('sort')) : 'newest';
        [$col, $dir] = self::SORTS[$sortKey];

        $vehicles = (clone $base)->with('coverPhoto')->filter($filters)
            ->orderByRaw("case when status = 'available' then 0 when status = 'reserved' then 1 else 2 end")
            ->when($col === 'price', fn ($q) => $q->orderByRaw('price is null'))
            ->orderBy($col, $dir)->orderByDesc('id')
            ->paginate(12)->withQueryString();

        return view('pages.inventory.index', [
            'vehicles' => $vehicles,
            'filters' => $filters,
            'sort' => $sortKey,
            'makes' => (clone $base)->selectRaw('make, count(*) as total')->groupBy('make')->orderBy('make')->pluck('total', 'make'),
            'bodies' => (clone $base)->selectRaw('body_type, count(*) as total')->groupBy('body_type')->orderBy('body_type')->pluck('total', 'body_type'),
            'years' => (clone $base)->distinct()->orderByDesc('year')->pluck('year'),
            'total' => (clone $base)->count(),
        ]);
    }

    public function show(Vehicle $vehicle): View
    {
        abort_unless($vehicle->published && Vehicle::listed()->whereKey($vehicle->id)->exists(), 404);
        $vehicle->load('photos');
        $rate = Setting::financeRate();

        return view('pages.inventory.show', [
            'vehicle' => $vehicle,
            'rate' => $rate,
            'estimate' => $vehicle->price ? LoanEstimator::estimate($vehicle->price, (int) round($vehicle->price * 0.25, -2), 60, $rate) : null,
            'similar' => Vehicle::available()->with('coverPhoto')->where('condition', $vehicle->condition)->whereKeyNot($vehicle->id)
                ->where(fn ($q) => $q->where('body_type', $vehicle->body_type)->orWhere('make', $vehicle->make))->limit(3)->get(),
        ]);
    }

    /**
     * @param  list<string>  $makes
     * @param  list<string>  $bodies
     * @return array<string, string|int|null>
     */
    private function filters(Request $request, array $makes, array $bodies): array
    {
        $int = fn (string $k) => ctype_digit(input_string($request->query($k))) ? (int) input_string($request->query($k)) : null;
        $make = input_string($request->query('make'));
        $body = input_string($request->query('body'));

        return [
            'make' => in_array($make, $makes, true) ? $make : null,
            'body' => in_array($body, $bodies, true) ? $body : null,
            'transmission' => Transmission::tryFrom(input_string($request->query('transmission')))?->value,
            'year_min' => ($y = $int('year_min')) && $y >= 1970 && $y <= now()->year + 1 ? $y : null,
            'year_max' => ($y = $int('year_max')) && $y >= 1970 && $y <= now()->year + 1 ? $y : null,
            'price_max' => ($p = $int('price_max')) && $p > 0 ? $p : null,
        ];
    }
}
