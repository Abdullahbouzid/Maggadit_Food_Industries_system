<?php

namespace App\Filament\Resources\UOMResource\Pages;

use App\Filament\Resources\UOMResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUOM extends CreateRecord
{
    protected static string $resource = UOMResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}