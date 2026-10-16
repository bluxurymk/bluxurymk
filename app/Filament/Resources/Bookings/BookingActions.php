<?php

namespace App\Filament\Resources\Bookings;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

/**
 * Status changes staff can make on a booking, shared by the table and the edit page.
 */
class BookingActions
{
    /**
     * @return list<Action>
     */
    public static function all(): array
    {
        return [
            self::confirmDeposit(),
            self::markCompleted(),
            self::markNoShow(),
            self::cancel(),
        ];
    }

    public static function confirmDeposit(): Action
    {
        return Action::make('confirmDeposit')
            ->label('Капар примен')
            ->icon(Heroicon::OutlinedBanknotes)
            ->color('success')
            ->visible(fn (Booking $record): bool => $record->status === BookingStatus::PendingDeposit)
            ->requiresConfirmation()
            ->modalHeading('Капарот е примен?')
            ->modalDescription(fn (Booking $record): string => "{$record->customer_name} го плати капарот од {$record->deposit_amount} ден. Резервацијата ќе биде потврдена.")
            ->action(function (Booking $record): void {
                $record->confirmDeposit();

                Notification::make()->title('Резервацијата е потврдена')->success()->send();
            });
    }

    public static function cancel(): Action
    {
        return Action::make('cancel')
            ->label('Откажи')
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->visible(fn (Booking $record): bool => $record->status->blocksSlot())
            ->requiresConfirmation()
            ->modalHeading('Откажи ја резервацијата?')
            ->modalDescription('Терминот ќе се ослободи за други клиенти.')
            ->schema([
                TextInput::make('reason')
                    ->label('Причина (опционално)')
                    ->maxLength(150),
            ])
            ->action(function (Booking $record, array $data): void {
                $record->cancel($data['reason'] ?: 'salon');

                Notification::make()->title('Резервацијата е откажана')->success()->send();
            });
    }

    public static function markCompleted(): Action
    {
        return Action::make('markCompleted')
            ->label('Завршена')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('info')
            ->visible(fn (Booking $record): bool => $record->status === BookingStatus::Confirmed && $record->hasStarted())
            ->action(fn (Booking $record) => $record->markCompleted());
    }

    public static function markNoShow(): Action
    {
        return Action::make('markNoShow')
            ->label('Не се појави')
            ->icon(Heroicon::OutlinedUserMinus)
            ->color('warning')
            ->visible(fn (Booking $record): bool => $record->status->blocksSlot() && $record->hasStarted())
            ->requiresConfirmation()
            ->action(fn (Booking $record) => $record->markNoShow());
    }
}
