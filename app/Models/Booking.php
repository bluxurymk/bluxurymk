<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'service_id', 'stylist_id', 'customer_name', 'customer_phone', 'customer_email', 'note',
    'starts_at', 'ends_at', 'status', 'deposit_amount', 'deposit_due_at', 'deposit_paid_at',
    'manage_token', 'google_event_id', 'consent_at', 'received_sent_at', 'deposit_reminder_sent_at',
    'reminder_sent_at', 'cancelled_at', 'cancel_reason',
])]
class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => BookingStatus::class,
            'starts_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime',
            'deposit_amount' => 'integer',
            'deposit_due_at' => 'immutable_datetime',
            'deposit_paid_at' => 'immutable_datetime',
            'consent_at' => 'immutable_datetime',
            'received_sent_at' => 'immutable_datetime',
            'deposit_reminder_sent_at' => 'immutable_datetime',
            'reminder_sent_at' => 'immutable_datetime',
            'cancelled_at' => 'immutable_datetime',
        ];
    }

    /**
     * @return BelongsTo<Service, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * @return BelongsTo<Stylist, $this>
     */
    public function stylist(): BelongsTo
    {
        return $this->belongsTo(Stylist::class);
    }

    /**
     * Bookings that occupy their time slot.
     */
    #[Scope]
    protected function blocking(Builder $query): void
    {
        $query->whereIn('status', BookingStatus::blocking());
    }

    /**
     * Whether the customer may still cancel it from the manage page.
     */
    public function isCancellableByCustomer(): bool
    {
        return $this->status->blocksSlot() && $this->starts_at->isFuture();
    }

    public function cancel(?string $reason = null): void
    {
        $this->update([
            'status' => BookingStatus::Cancelled,
            'cancelled_at' => now(),
            'cancel_reason' => $reason,
        ]);
    }
}
