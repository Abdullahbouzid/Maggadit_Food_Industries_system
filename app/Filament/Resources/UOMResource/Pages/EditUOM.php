<?php

namespace App\Filament\Resources\UOMResource\Pages;

use App\Filament\Resources\UOMResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUOM extends EditRecord
{
    protected static string $resource = UOMResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('حذف'),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}