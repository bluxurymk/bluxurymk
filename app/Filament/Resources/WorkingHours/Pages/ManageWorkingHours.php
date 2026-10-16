<?php

namespace App\Filament\Resources\WorkingHours\Pages;

use App\Filament\Resources\WorkingHours\WorkingHourResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageWorkingHours extends ManageRecords
{
    protected static string $resource = WorkingHourResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
