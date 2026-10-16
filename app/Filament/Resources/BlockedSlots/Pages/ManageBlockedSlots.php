<?php

namespace App\Filament\Resources\BlockedSlots\Pages;

use App\Filament\Resources\BlockedSlots\BlockedSlotResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageBlockedSlots extends ManageRecords
{
    protected static string $resource = BlockedSlotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
