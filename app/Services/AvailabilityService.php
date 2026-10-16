<?php

namespace App\Services;

use App\Models\BlockedSlot;
use App\Models\Booking;
use App\Models\Service;
use App\Models\Stylist;
use App\Models\WorkingHour;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

/**
 * Calculates bookable time slots from working hours, blocked periods,
 * existing bookings and the salon's booking rules.
 */
class AvailabilityService
{
    /**
     * Active stylists who perform the given service.
     *
     * @return EloquentCollection<int, Stylist>
     */
    public function stylistsFor(Service $service): EloquentCollection
    {
        return $service->stylists()->active()->orderBy('stylists.id')->get();
    }

    public function firstBookableDate(): CarbonImmutable
    {
        return $this->earliestStart()->startOfDay();
    }

    public function lastBookableDate(): CarbonImmutable
    {
        return now()->toImmutable()->startOfDay()->addDays(config('salon.booking.max_days_ahead'));
    }

    public function isBookableDate(CarbonImmutable $date): bool
    {
        return $date->between($this->firstBookableDate(), $this->lastBookableDate());
    }

    /**
     * Dates in the range that have at least one free slot.
     *
     * @return Collection<int, CarbonImmutable>
     */
    public function availableDates(Service $service, ?Stylist $stylist, CarbonImmutable $from, CarbonImmutable $to): Collection
    {
        $from = $from->startOfDay()->max($this->firstBookableDate());
        $to = $to->startOfDay()->min($this->lastBookableDate());

        if ($from->greaterThan($to)) {
            return collect();
        }

        return collect($this->slotsByDate($service, $this->candidates($service, $stylist), $from, $to))
            ->filter(fn (array $slotsByStylist): bool => collect($slotsByStylist)->flatten()->isNotEmpty())
            ->keys()
            ->map(fn (string $date): CarbonImmutable => CarbonImmutable::parse($date))
            ->values();
    }

    /**
     * Free start times on a date, keyed by "H:i". With no stylist, a time is
     * free when at least one stylist who performs the service is free.
     *
     * @return Collection<string, CarbonImmutable>
     */
    public function availableSlots(Service $service, CarbonImmutable $date, ?Stylist $stylist = null): Collection
    {
        $date = $date->startOfDay();

        if (! $this->isBookableDate($date)) {
            return collect();
        }

        $slotsByStylist = $this->slotsByDate($service, $this->candidates($service, $stylist), $date, $date)[$date->toDateString()];

        return collect($slotsByStylist)
            ->flatten()
            ->unique(fn (CarbonImmutable $slot): int => $slot->getTimestamp())
            ->sort()
            ->mapWithKeys(fn (CarbonImmutable $slot): array => [$slot->format('H:i') => $slot]);
    }

    /**
     * The stylist who can take the booking at the given time: the requested
     * stylist if free, otherwise (when none was requested) the first free one.
     */
    public function freeStylistAt(Service $service, CarbonImmutable $startsAt, ?Stylist $stylist = null): ?Stylist
    {
        $candidates = $this->candidates($service, $stylist);
        $date = $startsAt->startOfDay();

        if ($candidates->isEmpty() || ! $this->isBookableDate($date)) {
            return null;
        }

        $slotsByStylist = $this->slotsByDate($service, $candidates, $date, $date)[$date->toDateString()];

        return $candidates->first(fn (Stylist $candidate): bool => collect($slotsByStylist[$candidate->id])
            ->contains(fn (CarbonImmutable $slot): bool => $slot->equalTo($startsAt)));
    }

    /**
     * @return EloquentCollection<int, Stylist>
     */
    private function candidates(Service $service, ?Stylist $stylist): EloquentCollection
    {
        $stylists = $this->stylistsFor($service);

        return $stylist === null ? $stylists : $stylists->where('id', $stylist->id)->values();
    }

    private function earliestStart(): CarbonImmutable
    {
        return now()->toImmutable()->addMinutes(config('salon.booking.min_notice_minutes'));
    }

    /**
     * Free start times per date and stylist, loading everything needed for the range in a few queries.
     *
     * @param  EloquentCollection<int, Stylist>  $stylists
     * @return array<string, array<int, list<CarbonImmutable>>>
     */
    private function slotsByDate(Service $service, EloquentCollection $stylists, CarbonImmutable $from, CarbonImmutable $to): array
    {
        $rangeStart = $from->startOfDay();
        $rangeEnd = $to->endOfDay();
        $stylistIds = $stylists->modelKeys();

        $hours = WorkingHour::query()
            ->whereNull('stylist_id')
            ->orWhereIn('stylist_id', $stylistIds)
            ->orderBy('opens_at')
            ->get();

        $blocks = BlockedSlot::query()
            ->where('starts_at', '<', $rangeEnd)
            ->where('ends_at', '>', $rangeStart)
            ->where(fn ($query) => $query->whereNull('stylist_id')->orWhereIn('stylist_id', $stylistIds))
            ->get();

        $bookings = Booking::query()
            ->blocking()
            ->whereIn('stylist_id', $stylistIds)
            ->where('starts_at', '<', $rangeEnd->addDay())
            ->where('ends_at', '>', $rangeStart->subDay())
            ->get(['stylist_id', 'starts_at', 'ends_at']);

        $result = [];

        for ($date = $rangeStart; $date->lessThanOrEqualTo($to); $date = $date->addDay()) {
            $result[$date->toDateString()] = [];

            foreach ($stylists as $stylist) {
                $result[$date->toDateString()][$stylist->id] = $this->stylistSlots(
                    $service,
                    $this->intervalsFor($hours, $stylist, $date),
                    $blocks->filter(fn (BlockedSlot $block): bool => $block->stylist_id === null || $block->stylist_id === $stylist->id),
                    $bookings->where('stylist_id', $stylist->id),
                );
            }
        }

        return $result;
    }

    /**
     * Working intervals for a stylist on a date. Stylist-specific hours
     * replace the salon-wide hours entirely when the stylist has any.
     *
     * @param  EloquentCollection<int, WorkingHour>  $hours
     * @return list<array{0: CarbonImmutable, 1: CarbonImmutable}>
     */
    private function intervalsFor(EloquentCollection $hours, Stylist $stylist, CarbonImmutable $date): array
    {
        $ownHours = $hours->where('stylist_id', $stylist->id);
        $applicable = $ownHours->isNotEmpty() ? $ownHours : $hours->whereNull('stylist_id');

        return $applicable
            ->where('day_of_week', $date->dayOfWeek)
            ->map(fn (WorkingHour $hour): array => [
                $date->setTimeFromTimeString($hour->opens_at),
                $date->setTimeFromTimeString($hour->closes_at),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  list<array{0: CarbonImmutable, 1: CarbonImmutable}>  $intervals
     * @param  Collection<int, BlockedSlot>  $blocks
     * @param  Collection<int, Booking>  $bookings
     * @return list<CarbonImmutable>
     */
    private function stylistSlots(Service $service, array $intervals, Collection $blocks, Collection $bookings): array
    {
        $step = config('salon.booking.slot_interval_minutes');
        $buffer = config('salon.booking.buffer_minutes');
        $earliestStart = $this->earliestStart();
        $slots = [];

        foreach ($intervals as [$opensAt, $closesAt]) {
            for ($start = $opensAt; $start->addMinutes($service->duration_min)->lessThanOrEqualTo($closesAt); $start = $start->addMinutes($step)) {
                $end = $start->addMinutes($service->duration_min);

                if ($start->lessThan($earliestStart)) {
                    continue;
                }

                $isBlocked = $blocks->contains(fn (BlockedSlot $block): bool => $block->starts_at->lessThan($end) && $block->ends_at->greaterThan($start));

                $clashesWithBooking = $bookings->contains(fn (Booking $booking): bool => $booking->starts_at->lessThan($end->addMinutes($buffer))
                    && $booking->ends_at->addMinutes($buffer)->greaterThan($start));

                if (! $isBlocked && ! $clashesWithBooking) {
                    $slots[] = $start;
                }
            }
        }

        return $slots;
    }
}
