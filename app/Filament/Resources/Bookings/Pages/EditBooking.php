<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Filament\Resources\Bookings\BookingActions;
use App\Filament\Resources\Bookings\BookingResource;
use App\Models\Service;
use Carbon\CarbonImmutable;
use Filament\Resources\Pages\EditRecord;

class EditBooking extends EditRecord
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return BookingActions::all();
    }

    /**
     * Keep the end time in step with a changed start time or service.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $startsAt = CarbonImmutable::parse($data['starts_at'])->startOfMinute();

        return [
            ...$data,
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->addMinutes(Service::findOrFail($data['service_id'])->duration_min),
        ];
    }
}
