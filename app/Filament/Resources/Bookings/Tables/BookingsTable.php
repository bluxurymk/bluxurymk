<?php

namespace App\Filament\Resources\Bookings\Tables;

use App\Enums\BookingStatus;
use App\Filament\Resources\Bookings\BookingActions;
use App\Models\Booking;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('starts_at')
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['service', 'stylist']))
            ->columns([
                TextColumn::make('starts_at')
                    ->label('Термин')
                    ->dateTime('D d.m.Y, H:i')
                    ->sortable(),
                TextColumn::make('customer_name')
                    ->label('Клиент')
                    ->description(fn (Booking $record): string => $record->customer_phone)
                    ->searchable(['customer_name', 'customer_phone', 'customer_email']),
                TextColumn::make('service.name')
                    ->label('Услуга'),
                TextColumn::make('stylist.name')
                    ->label('Стилист')
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge(),
                TextColumn::make('deposit_due_at')
                    ->label('Капар до')
                    ->dateTime('d.m. H:i')
                    ->color(fn (Booking $record): ?string => $record->deposit_due_at?->isPast() ? 'danger' : null)
                    ->state(fn (Booking $record) => $record->status === BookingStatus::PendingDeposit ? $record->deposit_due_at : null)
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(BookingStatus::class)
                    ->multiple()
                    ->default([BookingStatus::PendingDeposit->value, BookingStatus::Confirmed->value]),
                SelectFilter::make('stylist')
                    ->label('Стилист')
                    ->relationship('stylist', 'name'),
                Filter::make('upcoming')
                    ->label('Само идни термини')
                    ->query(fn (Builder $query): Builder => $query->where('starts_at', '>=', today()))
                    ->default(),
            ])
            ->recordActions([
                BookingActions::confirmDeposit(),
                ActionGroup::make([
                    EditAction::make(),
                    BookingActions::markCompleted(),
                    BookingActions::markNoShow(),
                    BookingActions::cancel(),
                ]),
            ]);
    }
}
