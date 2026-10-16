<?php

namespace App\Models;

use Database\Factories\BlockedSlotFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A period nobody can book (holiday, day off). A null stylist blocks the whole salon.
 */
#[Fillable(['stylist_id', 'starts_at', 'ends_at', 'reason'])]
class BlockedSlot extends Model
{
    /** @use HasFactory<BlockedSlotFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime',
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
