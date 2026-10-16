<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Enums\BookingStatus;
use App\Filament\Resources\Bookings\BookingResource;
use App\Models\Service;
use App\Services\BookingService;
use Carbon\CarbonImmutable;
use Filament\Resources\Pages\CreateRecord;

/**
 * Bookings entered by staff, e.g. taken over the phone.
 */
class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $status = $data['status'] instanceof BookingStatus ? $data['status'] : BookingStatus::from($data['status']);

        return [
            ...$data,
            ...app(BookingService::class)->newBookingAttributes(
                Service::findOrFail($data['service_id']),
                CarbonImmutable::parse($data['starts_at'])->startOfMinute(),
                $status,
            ),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
