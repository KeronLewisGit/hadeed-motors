<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Customer uploads. Always on the private "local" disk; finance documents
 * (ID, pay slips) can only be opened by the finance role.
 *
 * @property int $id
 * @property int $lead_id
 * @property string $kind
 * @property string $path
 * @property string $original_name
 * @property string|null $mime
 * @property int $size
 * @property-read Lead $lead
 */
class LeadFile extends Model
{
    public const array FINANCE_KINDS = ['id', 'payslip'];

    protected $fillable = ['lead_id', 'kind', 'path', 'original_name', 'mime', 'size'];

    /** @return BelongsTo<Lead, $this> */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function isFinanceDocument(): bool
    {
        return in_array($this->kind, self::FINANCE_KINDS, true);
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime, 'image/');
    }

    public function label(): string
    {
        return match ($this->kind) {
            'id' => 'Photo ID',
            'payslip' => 'Pay slip',
            'part' => 'Part photo',
            'trade_in' => 'Trade-in photo',
            default => 'File',
        };
    }
}
