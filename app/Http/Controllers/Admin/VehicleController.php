<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Condition;
use App\Enums\Transmission;
use App\Enums\VehicleStatus;
use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehiclePhoto;
use App\Support\Lineup;
use App\Support\PhotoResizer;
use App\Support\Slug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Stock management. Photos go to the "uploads" disk (public_path('uploads')/vehicles). */
class VehicleController extends Controller
{
    public const int MAX_PHOTOS = 24;

    public function index(Request $request): View
    {
        $status = VehicleStatus::tryFrom(input_string($request->query('status')))?->value;
        $condition = Condition::tryFrom(input_string($request->query('condition')))?->value;
        $q = input_string($request->query('q'));

        return view('admin.vehicles.index', [
            'vehicles' => Vehicle::with('coverPhoto')->withCount('photos')
                ->when($status, fn ($w) => $w->where('status', $status))
                ->when($condition, fn ($w) => $w->where('condition', $condition))
                ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x->where('make', 'like', "%{$q}%")->orWhere('model', 'like', "%{$q}%")->orWhere('stock_no', 'like', "%{$q}%")))
                ->orderByRaw("case status when 'available' then 0 when 'reserved' then 1 else 2 end")->latest('id')
                ->paginate(30)->withQueryString(),
            'filters' => compact('status', 'condition', 'q'),
            'counts' => Vehicle::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function create(): View
    {
        return view('admin.vehicles.form', ['vehicle' => new Vehicle([
            'condition' => Condition::Used, 'status' => VehicleStatus::Available, 'transmission' => Transmission::Automatic,
            'published' => true, 'year' => (int) now()->year,
        ])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = Slug::unique(Vehicle::class, "{$data['year']} {$data['make']} {$data['model']}", 'vehicle');
        $vehicle = Vehicle::create($data);
        $this->storePhotos($request, $vehicle);

        return to_route('admin.vehicles.edit', $vehicle)->with('status', "{$vehicle->title} added.");
    }

    public function edit(Vehicle $vehicle): View
    {
        return view('admin.vehicles.form', ['vehicle' => $vehicle->load('photos')]);
    }

    public function update(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $data = $this->validated($request, $vehicle);
        if ($data['status'] === VehicleStatus::Sold->value && $vehicle->status !== VehicleStatus::Sold) {
            $data['sold_at'] = now();
        } elseif ($data['status'] !== VehicleStatus::Sold->value) {
            $data['sold_at'] = null;
        }
        $vehicle->update($data);
        $this->storePhotos($request, $vehicle);

        return back()->with('status', "{$vehicle->title} saved.");
    }

    /** Quick status change from the list (available / reserved / sold). */
    public function status(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::enum(VehicleStatus::class)]]);
        $status = VehicleStatus::from($data['status']);
        $vehicle->update(['status' => $status, 'sold_at' => $status === VehicleStatus::Sold ? ($vehicle->sold_at ?? now()) : null]);

        return back()->with('status', "{$vehicle->title}: {$status->label()}.");
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        foreach ($vehicle->photos as $photo) {
            Storage::disk('uploads')->delete(array_filter([$photo->path, $photo->thumb_path]));
        }
        $vehicle->delete();

        return to_route('admin.vehicles.index')->with('status', 'Vehicle deleted.');
    }

    public function destroyPhoto(Vehicle $vehicle, VehiclePhoto $photo): RedirectResponse
    {
        abort_unless($photo->vehicle_id === $vehicle->id, 404);
        Storage::disk('uploads')->delete(array_filter([$photo->path, $photo->thumb_path]));
        $photo->delete();

        return back()->with('status', 'Photo removed.')->withFragment('photos');
    }

    /** Make this photo the cover (first in the gallery). */
    public function coverPhoto(Vehicle $vehicle, VehiclePhoto $photo): RedirectResponse
    {
        abort_unless($photo->vehicle_id === $vehicle->id, 404);
        foreach ($vehicle->photos as $i => $p) {
            $p->update(['sort' => $p->is($photo) ? 0 : $i + 1]);
        }

        return back()->with('status', 'Cover photo set.')->withFragment('photos');
    }

    private function storePhotos(Request $request, Vehicle $vehicle): void
    {
        $sort = (int) $vehicle->photos()->max('sort');
        foreach ($request->file('photos', []) as $file) {
            $vehicle->photos()->create(PhotoResizer::store($file, 'vehicles/'.$vehicle->id) + ['sort' => ++$sort]);
        }
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?Vehicle $vehicle = null): array
    {
        $existing = $vehicle ? $vehicle->photos()->count() : 0;
        $data = $request->validate([
            'condition' => ['required', Rule::enum(Condition::class)],
            'status' => ['required', Rule::enum(VehicleStatus::class)],
            'stock_no' => ['nullable', 'string', 'max:30'],
            'make' => ['required', 'string', 'max:40'],
            'model' => ['required', 'string', 'max:60'],
            'model_key' => ['nullable', Rule::in(array_keys(Lineup::options()))],
            'trim' => ['nullable', 'string', 'max:60'],
            'year' => ['required', 'integer', 'min:1970', 'max:'.(now()->year + 1)],
            'body_type' => ['required', Rule::in((array) config('hadeed.body_types'))],
            'transmission' => ['required', Rule::enum(Transmission::class)],
            'fuel' => ['nullable', Rule::in(['Petrol', 'Diesel', 'Hybrid', 'Electric'])],
            'engine' => ['nullable', 'string', 'max:40'],
            'drive' => ['nullable', Rule::in(['2WD', '4WD', 'AWD'])],
            'mileage_km' => ['nullable', 'required_if:condition,used', 'integer', 'min:0', 'max:1500000'],
            'colour' => ['nullable', 'string', 'max:40'],
            'seats' => ['nullable', 'integer', 'min:1', 'max:30'],
            'price' => ['nullable', 'integer', 'min:1000', 'max:5000000'],
            'description' => ['nullable', 'string', 'max:4000'],
            'features_text' => ['nullable', 'string', 'max:3000'],
            'photos' => ['nullable', 'array', 'max:'.max(0, self::MAX_PHOTOS - $existing)],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:8192', 'dimensions:min_width=800'],
        ], [
            'photos.*.dimensions' => 'Use photos at least 800 pixels wide so they stay sharp in the gallery.',
            'photos.max' => 'A vehicle can have up to '.self::MAX_PHOTOS.' photos.',
            'mileage_km.required_if' => 'Pre-owned vehicles need the odometer reading.',
        ]);

        $data['features'] = lines_to_array($data['features_text'] ?? '');
        unset($data['features_text'], $data['photos']);

        return $data + ['featured' => $request->boolean('featured'), 'published' => $request->boolean('published')];
    }
}
