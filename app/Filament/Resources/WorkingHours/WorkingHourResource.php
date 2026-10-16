<?php

namespace App\Filament\Resources\WorkingHours;

use App\Filament\Resources\WorkingHours\Pages\ManageWorkingHours;
use App\Models\WorkingHour;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class WorkingHourResource extends Resource
{
    /**
     * Carbon day numbers in Monday-first order.
     *
     * @var array<int, string>
     */
    public const DAYS = [
        1 => 'Понеделник',
        2 => 'Вторник',
        3 => 'Среда',
        4 => 'Четврток',
        5 => 'Петок',
        6 => 'Сабота',
        0 => 'Недела',
    ];

    protected static ?string $model = WorkingHour::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static string|UnitEnum|null $navigationGroup = 'Салон';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'работно време';

    protected static ?string $pluralModelLabel = 'работно време';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('stylist_id')
                    ->label('За')
                    ->relationship('stylist', 'name')
                    ->placeholder('Цел салон')
                    ->helperText('Ако стилистот има свое работно време, тоа целосно го заменува работното време на салонот.')
                    ->columnSpanFull(),
                Select::make('day_of_week')
                    ->label('Ден')
                    ->options(self::DAYS)
                    ->required()
                    ->columnSpanFull(),
                TimePicker::make('opens_at')
                    ->label('Од')
                    ->seconds(false)
                    ->required(),
                TimePicker::make('closes_at')
                    ->label('До')
                    ->seconds(false)
                    ->required()
                    ->after('opens_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('day_of_week')
            ->columns([
                TextColumn::make('stylist.name')
                    ->label('За')
                    ->placeholder('Цел салон'),
                TextColumn::make('day_of_week')
                    ->label('Ден')
                    ->formatStateUsing(fn (int $state): string => self::DAYS[$state])
                    ->sortable(),
                TextColumn::make('opens_at')
                    ->label('Од')
                    ->time('H:i'),
                TextColumn::make('closes_at')
                    ->label('До')
                    ->time('H:i'),
            ])
            ->filters([
                SelectFilter::make('stylist')
                    ->label('Стилист')
                    ->relationship('stylist', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageWorkingHours::route('/'),
        ];
    }
}
