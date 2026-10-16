<?php

namespace App\Filament\Resources\Stylists;

use App\Filament\Resources\Stylists\Pages\ManageStylists;
use App\Models\Stylist;
use App\Services\ImageOptimizer;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use UnitEnum;

class StylistResource extends Resource
{
    protected static ?string $model = Stylist::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Салон';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'стилист';

    protected static ?string $pluralModelLabel = 'стилисти';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Име')
                    ->required()
                    ->maxLength(100),
                Toggle::make('is_active')
                    ->label('Активен (може да се резервира)')
                    ->default(true)
                    ->inline(false),
                FileUpload::make('photo')
                    ->label('Фотографија')
                    ->image()
                    ->disk('public')
                    ->maxSize(10240)
                    ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file): string => app(ImageOptimizer::class)->storeAsWebp($file, 'stylists', 800))
                    ->columnSpanFull(),
                Select::make('services')
                    ->label('Услуги')
                    ->relationship('services', 'name')
                    ->multiple()
                    ->preload()
                    ->columnSpanFull(),
                Textarea::make('bio')
                    ->label('Кратко за стилистот')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                ImageColumn::make('photo')
                    ->label('')
                    ->disk('public')
                    ->circular(),
                TextColumn::make('name')
                    ->label('Име')
                    ->searchable(),
                TextColumn::make('services.name')
                    ->label('Услуги')
                    ->badge(),
                ToggleColumn::make('is_active')
                    ->label('Активен'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->hidden(fn (Stylist $record): bool => $record->bookings()->exists()),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageStylists::route('/'),
        ];
    }
}
