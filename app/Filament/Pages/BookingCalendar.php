<?php

namespace App\Filament\Pages;

use App\Enums\BookingStatus;
use App\Models\Booking;
use BackedEnum;
use Carbon\CarbonImmutable;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use UnitEnum;

/**
 * Week-at-a-glance agenda of active and finished bookings.
 */
class BookingCalendar extends Page
{
    protected string $view = 'filament.pages.booking-calendar';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|UnitEnum|null $navigationGroup = 'Резервации';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Календар';

    protected static ?string $title = 'Календар';

    protected static ?string $slug = 'kalendar';

    /**
     * Monday of the shown week, as Y-m-d.
     */
    #[Url]
    public string $week = '';

    public function mount(): void
    {
        $this->week = $this->weekStart()->toDateString();
    }

    public function previousWeek(): void
    {
        $this->week = $this->weekStart()->subWeek()->toDateString();
    }

    public function nextWeek(): void
    {
        $this->week = $this->weekStart()->addWeek()->toDateString();
    }

    public function thisWeek(): void
    {
        $this->week = today()->toImmutable()->startOfWeek()->toDateString();
    }

    public function weekStart(): CarbonImmutable
    {
        $date = preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->week)
            ? CarbonImmutable::createFromFormat('!Y-m-d', $this->week)
            : null;

        return ($date ?: today()->toImmutable())->startOfWeek();
    }

    /**
     * The seven days of the week with their bookings, cancelled ones left out.
     *
     * @return Collection<int, array{date: CarbonImmutable, bookings: Collection<int, Booking>}>
     */
    #[Computed]
    public function days(): Collection
    {
        $start = $this->weekStart();

        $bookings = Booking::query()
            ->with(['service', 'stylist'])
            ->where('status', '!=', BookingStatus::Cancelled)
            ->whereBetween('starts_at', [$start, $start->endOfWeek()])
            ->orderBy('starts_at')
            ->get()
            ->groupBy(fn (Booking $booking): string => $booking->starts_at->toDateString());

        return collect(range(0, 6))->map(fn (int $offset): array => [
            'date' => $start->addDays($offset),
            'bookings' => $bookings->get($start->addDays($offset)->toDateString(), collect()),
        ]);
    }
}
