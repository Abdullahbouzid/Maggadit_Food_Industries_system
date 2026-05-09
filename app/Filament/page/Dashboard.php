<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Actions\Action;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class Dashboard extends BaseDashboard
{
    protected function getHeaderActions(): array
    {
        return [
            Action::make('switchLanguage')
                ->label(App::getLocale() == 'ar' ? 'English' : 'العربية')
                ->icon('heroicon-o-language')
                ->action(function () {
                    $newLocale = App::getLocale() == 'ar' ? 'en' : 'ar';
                    Session::put('locale', $newLocale);
                    App::setLocale($newLocale);
                    redirect()->refresh();
                }),
        ];
    }
}