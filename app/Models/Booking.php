<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Events\BookingWasCancelled;
use App\Events\DepositWasConfirmed;
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
    public const CANCELLED_BY_CUSTOMER = 'customer';

    public const CANCELLED_BY_SALON = 'salon';

    public const CANCELLED_DEPOSIT_EXPIRED = 'deposit_expired';

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

    /**
     * Whether the appointment time has started, so it can be closed as completed or no-show.
     */
    public function hasStarted(): bool
    {
        return $this->starts_at->isPast();
    }

    /**
     * Mark the deposit as paid. Only a booking still waiting for it can be confirmed.
     */
    public function confirmDeposit(): bool
    {
        $confirmed = $this->transitionFrom([BookingStatus::PendingDeposit], [
            'status' => BookingStatus::Confirmed->value,
            'deposit_paid_at' => now(),
        ]);

        if ($confirmed) {
            DepositWasConfirmed::dispatch($this);
        }

        return $confirmed;
    }

    /**
     * Cancel an active booking. The reason is one of the CANCELLED_* constants or staff's own text.
     */
    public function cancel(string $reason): bool
    {
        $cancelled = $this->transitionFrom(BookingStatus::blocking(), [
            'status' => BookingStatus::Cancelled->value,
            'cancelled_at' => now(),
            'cancel_reason' => $reason,
        ]);

        if ($cancelled) {
            BookingWasCancelled::dispatch($this);
        }

        return $cancelled;
    }

    /**
     * Cancel the booking if its deposit deadline has passed while it still
     * waits for the deposit. A deposit confirmed in the meantime wins.
     */
    public function expireUnpaidDeposit(): bool
    {
        if ($this->deposit_due_at === null || $this->deposit_due_at->isFuture()) {
            return false;
        }

        $expired = $this->transitionFrom([BookingStatus::PendingDeposit], [
            'status' => BookingStatus::Cancelled->value,
            'cancelled_at' => now(),
            'cancel_reason' => self::CANCELLED_DEPOSIT_EXPIRED,
        ]);

        if ($expired) {
            BookingWasCancelled::dispatch($this);
        }

        return $expired;
    }

    public function markCompleted(): void
    {
        $this->update(['status' => BookingStatus::Completed]);
    }

    public function markNoShow(): void
    {
        $this->update(['status' => BookingStatus::NoShow]);
    }

    /**
     * Change the status atomically, but only while it is still one of the given
     * statuses, so the expiry command and staff cannot both act on the same booking.
     *
     * @param  list<BookingStatus>  $fromStatuses
     * @param  array<string, mixed>  $attributes
     */
    private function transitionFrom(array $fromStatuses, array $attributes): bool
    {
        $updated = static::query()
            ->whereKey($this->getKey())
            ->whereIn('status', $fromStatuses)
            ->update($attributes);

        if ($updated === 0) {
            return false;
        }

        $this->refresh();

        return true;
    }
}
