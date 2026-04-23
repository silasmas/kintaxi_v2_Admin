<?php

namespace App\Providers\Filament;

use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Devonab\FilamentEasyFooter\EasyFooterPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
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
            ->login(\App\Filament\Pages\Auth\CustomLogin::class)
            ->profile(\App\Filament\Pages\Auth\EditProfile::class)
            ->sidebarCollapsibleOnDesktop()
            ->brandName('KinTaxi')
            ->brandLogo(asset('assets/img/logo-text.png'))
            ->brandLogoHeight('2rem')
            ->favicon(asset('assets/img/favicon/favicon-32x32.png'))
            ->colors([
                'primary' => Color::hex('#171717'),
                'gray' => Color::Zinc,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
                EasyFooterPlugin::make()
                    ->footerEnabled()
                    ->withFooterPosition('footer')
                    ->withSentence('KinTaxi Admin - Plateforme de supervision')
                    ->withLoadTime('Page chargée en')
                    ->withBorder()
                    ->withLogo(
                        asset('assets/img/logo-text.png'),
                        url('/admin'),
                        'Propulsé par',
                        22,
                    )
                    ->withLinks([
                        ['title' => 'Support', 'url' => 'mailto:support@kintaxi.com'],
                        ['title' => 'Documentation', 'url' => 'https://docs.usesmileid.com/'],
                        ['title' => 'Statut système', 'url' => '/up'],
                    ]),
            ])
            ->renderHook(
                PanelsRenderHook::TOPBAR_END,
                fn (): string => view('filament.partials.shepherd-tour')->render(),
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
                \App\Filament\Pages\ForbiddenPage::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
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
            ]);
    }
}
