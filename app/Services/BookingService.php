<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Events\BookingWasPlaced;
use App\Exceptions\SlotUnavailableException;
use App\Models\Booking;
use App\Models\Service;
use App\Models\Stylist;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingService
{
    public function __construct(private AvailabilityService $availability) {}

    /**
     * Create a booking awaiting its deposit. The slot is re-checked inside a
     * transaction while the candidate stylists' rows are locked, so two
     * customers cannot take the same slot at the same time.
     *
     * @param  array{customer_name: string, customer_phone: string, customer_email: string, note?: string|null}  $customer
     *
     * @throws SlotUnavailableException
     */
    public function create(Service $service, ?Stylist $stylist, CarbonImmutable $startsAt, array $customer): Booking
    {
        return DB::transaction(function () use ($service, $stylist, $startsAt, $customer): Booking {
            $candidateIds = $stylist === null
                ? $this->availability->stylistsFor($service)->modelKeys()
                : [$stylist->id];

            Stylist::query()->whereKey($candidateIds)->orderBy('id')->lockForUpdate()->get();

            $assignedStylist = $this->availability->freeStylistAt($service, $startsAt, $stylist);

            if ($assignedStylist === null) {
                throw new SlotUnavailableException;
            }

            $booking = Booking::create([
                'service_id' => $service->id,
                'stylist_id' => $assignedStylist->id,
                'customer_name' => $customer['customer_name'],
                'customer_phone' => $customer['customer_phone'],
                'customer_email' => $customer['customer_email'],
                'note' => $customer['note'] ?? null,
                ...$this->newBookingAttributes($service, $startsAt, BookingStatus::PendingDeposit),
            ]);

            BookingWasPlaced::dispatch($booking, true);

            return $booking;
        });
    }

    /**
     * System-managed attributes of a new booking: end time, deposit terms, manage token and consent time.
     *
     * @return array<string, mixed>
     */
    public function newBookingAttributes(Service $service, CarbonImmutable $startsAt, BookingStatus $status): array
    {
        $now = now()->toImmutable();

        return [
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->addMinutes($service->duration_min),
            'status' => $status,
            'deposit_amount' => config('salon.booking.deposit_amount'),
            'deposit_due_at' => $now->addHours(config('salon.booking.deposit_hours'))->min($startsAt),
            'deposit_paid_at' => $status === BookingStatus::Confirmed ? $now : null,
            'manage_token' => Str::random(48),
            'consent_at' => $now,
        ];
    }

    /**
     * Whether the stylist already has an active booking overlapping the period.
     * Used for bookings entered by staff, who may book outside working hours.
     */
    public function hasConflict(int $stylistId, CarbonImmutable $startsAt, CarbonImmutable $endsAt, ?int $ignoreBookingId = null): bool
    {
        return Booking::query()
            ->blocking()
            ->where('stylist_id', $stylistId)
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt)
            ->when($ignoreBookingId, fn ($query) => $query->whereKeyNot($ignoreBookingId))
            ->exists();
    }
}
