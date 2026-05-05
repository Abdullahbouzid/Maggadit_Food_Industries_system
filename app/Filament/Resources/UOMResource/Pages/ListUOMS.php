<?php

namespace App\Filament\Resources\UOMResource\Pages;

use App\Filament\Resources\UOMResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUOMS extends ListRecords
{
    protected static string $resource = UOMResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('إضافة وحدة قياس جديدة')
                ->icon('heroicon-o-plus'),
        ];
    }
}