<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseLogin
{
    protected static string $view = 'filament.pages.auth.login';

    public function getHeading(): string | Htmlable
    {
        return '';
    }

    public function getSubheading(): string | Htmlable | null
    {
        return null;
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('📧 Email')
            ->email()
            ->required()
            ->autocomplete()
            ->autofocus()
            ->placeholder('admin@winpawa.com')
            ->extraInputAttributes([
                'class' => 'gaming-input',
                'style' => 'font-size: 0.95rem;'
            ])
            ->prefixIcon('heroicon-o-envelope')
            ->prefixIconColor('primary');
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('🔒 Mot de passe')
            ->password()
            ->required()
            ->placeholder('Entrez votre mot de passe')
            ->extraInputAttributes([
                'class' => 'gaming-input',
                'style' => 'font-size: 0.95rem;'
            ])
            ->prefixIcon('heroicon-o-lock-closed')
            ->prefixIconColor('primary')
            ->revealable();
    }

    protected function getRememberFormComponent(): Component
    {
        return parent::getRememberFormComponent()
            ->label('Se souvenir de moi');
    }
}
