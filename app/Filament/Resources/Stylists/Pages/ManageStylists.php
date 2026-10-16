<?php

namespace App\Filament\Resources\Stylists\Pages;

use App\Filament\Resources\Stylists\StylistResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageStylists extends ManageRecords
{
    protected static string $resource = StylistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
