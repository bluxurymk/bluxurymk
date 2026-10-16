<?php

namespace App\Filament\Resources\Services\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ServiceForm
{
    /**
     * Icons available in the public site's icon component.
     *
     * @var array<string, string>
     */
    public const ICONS = [
        'scissors' => 'Ножици',
        'droplet' => 'Капка',
        'bowl' => 'Чинија за боја',
        'waves' => 'Бранови',
        'sparkle' => 'Ѕвезда',
        'lotus' => 'Лотос',
        'leaf' => 'Лист',
        'heart' => 'Срце',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Опис')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Име')
                            ->required()
                            ->maxLength(100)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state, string $operation): void {
                                if ($operation === 'create' && blank($get('slug'))) {
                                    $set('slug', Str::slug(Str::transliterate((string) $state)));
                                }
                            }),
                        TextInput::make('slug')
                            ->label('URL (латиница)')
                            ->helperText('Дел од адресата, на пр. /uslugi/keratin')
                            ->required()
                            ->alphaDash()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),
                        TextInput::make('subtitle')
                            ->label('Поднаслов')
                            ->maxLength(150),
                        Select::make('icon')
                            ->label('Икона')
                            ->options(self::ICONS)
                            ->required()
                            ->default('scissors'),
                        Textarea::make('description')
                            ->label('Опис')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
                Section::make('Резервација')
                    ->columns(2)
                    ->schema([
                        TextInput::make('duration_min')
                            ->label('Времетраење')
                            ->suffix('мин.')
                            ->required()
                            ->integer()
                            ->minValue(5)
                            ->maxValue(600),
                        TextInput::make('price')
                            ->label('Цена')
                            ->suffix('ден.')
                            ->integer()
                            ->minValue(0),
                        Select::make('stylists')
                            ->label('Стилисти')
                            ->helperText('Само избраните стилисти може да се резервираат за оваа услуга.')
                            ->relationship('stylists', 'name')
                            ->multiple()
                            ->preload()
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('Активна (видлива на сајтот)')
                            ->default(true),
                    ]),
            ]);
    }
}
