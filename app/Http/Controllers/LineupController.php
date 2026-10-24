<?php

namespace App\Http\Controllers;

use App\Enums\Condition;
use App\Models\Vehicle;
use App\Support\Lineup;
use Illuminate\View\View;

/** New vehicles: the line-up from config, with any new stock units the dealership has entered. */
class LineupController extends Controller
{
    public function index(): View
    {
        return view('pages.lineup.index', ['brand' => null, 'models' => Lineup::all()]);
    }

    public function brand(string $brand): View
    {
        abort_unless(in_array($brand, Lineup::brands(), true), 404);

        return view('pages.lineup.index', ['brand' => $brand, 'models' => Lineup::all($brand)]);
    }

    public function show(string $brand, string $model): View
    {
        $m = Lineup::find($brand, $model);
        abort_unless($m !== null, 404);

        return view('pages.lineup.show', [
            'key' => $model,
            'brandKey' => $brand,
            'm' => $m,
            'stock' => Vehicle::available()->with('coverPhoto')->where('condition', Condition::New)->where('model_key', $model)->get(),
            'others' => collect(Lineup::all($brand))->except($model)->take(3),
        ]);
    }
}
