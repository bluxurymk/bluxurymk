<?php

namespace App\Filament\Resources\BlockedSlots;

use App\Filament\Resources\BlockedSlots\Pages\ManageBlockedSlots;
use App\Models\BlockedSlot;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class BlockedSlotResource extends Resource
{
    protected static ?string $model = BlockedSlot::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNoSymbol;

    protected static string|UnitEnum|null $navigationGroup = 'Салон';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'неработен период';

    protected static ?string $pluralModelLabel = 'неработни периоди';

    protected static ?string $navigationLabel = 'Празници и слободни денови';

    protected static ?string $recordTitleAttribute = 'reason';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('stylist_id')
                    ->label('За')
                    ->relationship('stylist', 'name')
                    ->placeholder('Цел салон')
                    ->columnSpanFull(),
                DateTimePicker::make('starts_at')
                    ->label('Од')
                    ->seconds(false)
                    ->required(),
                DateTimePicker::make('ends_at')
                    ->label('До')
                    ->seconds(false)
                    ->required()
                    ->after('starts_at'),
                TextInput::make('reason')
                    ->label('Причина')
                    ->placeholder('на пр. Нова година, одмор')
                    ->maxLength(150)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('starts_at')
            ->columns([
                TextColumn::make('starts_at')
                    ->label('Од')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->label('До')
                    ->dateTime('d.m.Y H:i'),
                TextColumn::make('stylist.name')
                    ->label('За')
                    ->placeholder('Цел салон'),
                TextColumn::make('reason')
                    ->label('Причина')
                    ->placeholder('—'),
            ])
            ->filters([
                Filter::make('upcoming')
                    ->label('Само идни')
                    ->query(fn (Builder $query): Builder => $query->where('ends_at', '>=', now()))
                    ->default(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageBlockedSlots::route('/'),
        ];
    }
}
