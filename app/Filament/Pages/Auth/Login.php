<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Actions\Action;

class Login extends BaseLogin
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.auth.login';

    protected function getFormActions(): array
    {
        return [
            Action::make('loginWithGoogle')
                ->label('Login dengan Google')
                ->url(url('/auth/google/redirect'))
                ->icon('heroicon-o-user-circle')
                ->extraAttributes([
                    'class' => 'w-full flex justify-center', 
                ]),
        ];
    }

    public function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        return $form
            ->schema([]);
    }
}
