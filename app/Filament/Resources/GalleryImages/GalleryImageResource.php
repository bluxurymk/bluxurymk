<?php

namespace App\Filament\Resources\GalleryImages;

use App\Filament\Resources\GalleryImages\Pages\ManageGalleryImages;
use App\Models\GalleryImage;
use App\Services\ImageOptimizer;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
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

class GalleryImageResource extends Resource
{
    protected static ?string $model = GalleryImage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|UnitEnum|null $navigationGroup = 'Салон';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'фотографија';

    protected static ?string $pluralModelLabel = 'галерија';

    protected static ?string $recordTitleAttribute = 'alt';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('path')
                    ->label('Фотографија')
                    ->helperText('Автоматски се намалува и зачувува во WebP формат.')
                    ->image()
                    ->disk('public')
                    ->maxSize(15360)
                    ->required()
                    ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file): string => app(ImageOptimizer::class)->storeAsWebp($file, 'gallery'))
                    ->columnSpanFull(),
                TextInput::make('alt')
                    ->label('Опис на фотографијата')
                    ->helperText('Кратко, за слепи и слабовидни посетители и за Google (на пр. Балајаж на долга коса).')
                    ->required()
                    ->maxLength(150)
                    ->columnSpanFull(),
                TextInput::make('category')
                    ->label('Категорија')
                    ->maxLength(50),
                Toggle::make('is_visible')
                    ->label('Видлива на сајтот')
                    ->default(true)
                    ->inline(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                ImageColumn::make('path')
                    ->label('')
                    ->disk('public')
                    ->imageHeight(64),
                TextColumn::make('alt')
                    ->label('Опис')
                    ->searchable(),
                TextColumn::make('category')
                    ->label('Категорија')
                    ->placeholder('—'),
                ToggleColumn::make('is_visible')
                    ->label('Видлива'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageGalleryImages::route('/'),
        ];
    }
}
