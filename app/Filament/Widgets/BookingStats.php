<?php

namespace App\Filament\Widgets;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BookingStats extends StatsOverviewWidget
{
    protected static ?int $sort = -1;

    protected function getStats(): array
    {
        $activeBookings = Booking::query()->blocking();

        return [
            Stat::make('Термини денес', (clone $activeBookings)->whereDate('starts_at', today())->count()),
            Stat::make('Чекаат капар', Booking::where('status', BookingStatus::PendingDeposit)->count())
                ->description('Потврдете ги во Резервации → Листа')
                ->color('warning'),
            Stat::make('Термини оваа недела', (clone $activeBookings)->whereBetween('starts_at', [today()->startOfWeek(), today()->endOfWeek()])->count()),
        ];
    }
}
