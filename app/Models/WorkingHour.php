<?php

namespace App\Models;

use Database\Factories\WorkingHourFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A working interval on a weekday. `day_of_week` follows Carbon: 0 = Sunday … 6 = Saturday.
 * `opens_at` / `closes_at` are "HH:MM:SS" strings. A null stylist means salon-wide hours.
 */
#[Fillable(['stylist_id', 'day_of_week', 'opens_at', 'closes_at'])]
class WorkingHour extends Model
{
    /** @use HasFactory<WorkingHourFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Stylist, $this>
     */
    public function stylist(): BelongsTo
    {
        return $this->belongsTo(Stylist::class);
    }
}
