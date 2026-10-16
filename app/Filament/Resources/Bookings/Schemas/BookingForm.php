<?php

namespace App\Filament\Resources\Bookings\Schemas;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Service;
use App\Models\Stylist;
use App\Services\BookingService;
use Carbon\CarbonImmutable;
use Closure;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class BookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Термин')
                    ->columns(2)
                    ->schema([
                        Select::make('service_id')
                            ->label('Услуга')
                            ->relationship('service', 'name')
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('stylist_id', null)),
                        Select::make('stylist_id')
                            ->label('Стилист')
                            ->options(fn (Get $get): array => self::stylistOptions($get('service_id')))
                            ->required(),
                        DateTimePicker::make('starts_at')
                            ->label('Почеток')
                            ->seconds(false)
                            ->minutesStep(5)
                            ->required()
                            ->rule(fn (Get $get, ?Booking $record): Closure => self::noOverlapRule($get, $record)),
                        Select::make('status')
                            ->label('Статус')
                            ->options([
                                BookingStatus::PendingDeposit->value => BookingStatus::PendingDeposit->label(),
                                BookingStatus::Confirmed->value => 'Потврдена (капарот е платен)',
                            ])
                            ->default(BookingStatus::PendingDeposit->value)
                            ->required()
                            ->visibleOn('create'),
                    ]),
                Section::make('Клиент')
                    ->columns(2)
                    ->schema([
                        TextInput::make('customer_name')
                            ->label('Име и презиме')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('customer_phone')
                            ->label('Телефон')
                            ->tel()
                            ->required()
                            ->maxLength(30),
                        TextInput::make('customer_email')
                            ->label('Е-пошта')
                            ->email()
                            ->maxLength(150),
                        Textarea::make('note')
                            ->label('Забелешка')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * Stylists who perform the chosen service (all active stylists until one is chosen).
     *
     * @return array<int, string>
     */
    private static function stylistOptions(mixed $serviceId): array
    {
        $query = filled($serviceId)
            ? Service::find($serviceId)?->stylists()->getQuery()
            : Stylist::query();

        return $query?->where('stylists.is_active', true)->orderBy('stylists.name')->pluck('stylists.name', 'stylists.id')->all() ?? [];
    }

    private static function noOverlapRule(Get $get, ?Booking $record): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($get, $record): void {
            $service = Service::find($get('service_id'));
            $stylistId = $get('stylist_id');

            if ($service === null || blank($stylistId) || blank($value)) {
                return;
            }

            $startsAt = CarbonImmutable::parse($value);

            if (app(BookingService::class)->hasConflict((int) $stylistId, $startsAt, $startsAt->addMinutes($service->duration_min), $record?->id)) {
                $fail('Стилистот веќе има резервација во ова време.');
            }
        };
    }
}
