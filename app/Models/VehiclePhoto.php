<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $vehicle_id
 * @property string $path
 * @property string|null $thumb_path
 * @property int|null $width
 * @property int|null $height
 * @property int $sort
 * @property string|null $credit
 */
class VehiclePhoto extends Model
{
    protected $fillable = ['vehicle_id', 'path', 'thumb_path', 'width', 'height', 'sort', 'credit'];

    /** @return BelongsTo<Vehicle, $this> */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function url(): string
    {
        return Storage::disk('uploads')->url($this->path);
    }

    public function thumbUrl(): string
    {
        return Storage::disk('uploads')->url($this->thumb_path ?? $this->path);
    }
}
