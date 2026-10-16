<?php

namespace App\Filament\Resources\Services\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class ServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('name')
                    ->label('Име')
                    ->description(fn ($record): ?string => $record->subtitle)
                    ->searchable(),
                TextColumn::make('duration_min')
                    ->label('Времетраење')
                    ->suffix(' мин.')
                    ->sortable(),
                TextColumn::make('price')
                    ->label('Цена')
                    ->suffix(' ден.')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('stylists_count')
                    ->label('Стилисти')
                    ->counts('stylists'),
                ToggleColumn::make('is_active')
                    ->label('Активна'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
