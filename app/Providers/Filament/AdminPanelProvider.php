<?php

namespace App\Providers\Filament;

use App\Models\GameModule;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Filament\Support\Enums\MaxWidth;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('WINPAWA')
            ->brandLogo(asset('images/logo.png'))
            ->brandLogoHeight('3.5rem')
            ->favicon(asset('images/logo.png'))
            ->colors([
                'primary' => Color::Amber,
                'danger' => Color::Red,
                'gray' => Color::Zinc,
                'info' => Color::Blue,
                'success' => Color::Green,
                'warning' => Color::Amber,
            ])
            ->font('Outfit')
            ->darkMode(true)  // Force dark mode only
            ->darkModeBrandLogo(asset('images/logo.png'))  // Use same logo for dark mode
            ->maxContentWidth(MaxWidth::Full)  // Full width for modern dashboard
            ->sidebarCollapsibleOnDesktop()  // Collapsible sidebar for more space
            ->databaseNotifications()
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->navigationItems($this->getModuleNavigationItems())
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => Blade::render('@include(\'filament.custom-head\')')
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => Blade::render('@include(\'filament.custom-scripts\')')
            );
    }

    protected function getModuleNavigationItems(): array
    {
        $items = [];

        // Récupérer tous les modules actifs (sauf Jeux Casino qui a son propre Resource)
        $modules = GameModule::where('is_active', true)
            ->where('slug', '!=', 'jeux-casino')
            ->ordered()
            ->get();

        foreach ($modules as $module) {
            if ($module->is_locked) {
                // Module verrouillé : afficher avec cadenas et badge
                $items[] = NavigationItem::make($module->name . ' 🔒')
                    ->icon($module->icon)
                    ->group('Gestion des Jeux')
                    ->sort($module->sort_order + 10)
                    ->url('#')
                    ->badge('Verrouillé');
            } else {
                // Module déverrouillé : créer un lien vers la page du module
                $items[] = NavigationItem::make($module->name)
                    ->icon($module->icon)
                    ->group('Gestion des Jeux')
                    ->sort($module->sort_order + 10)
                    ->url('/admin/module-games?module=' . $module->slug)
                    ->badge($module->games()->count() ?: null);
            }
        }

        return $items;
    }
}
