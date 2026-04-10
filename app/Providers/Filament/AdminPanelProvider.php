<?php

namespace App\Providers\Filament;

use App\Filament\Resources\Applications\ApplicationResource;
use App\Filament\Resources\Branches\BranchResource;
use App\Filament\Resources\Candidates\CandidateResource;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Departments\DepartmentResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\RecruitmentJobs\RecruitmentJobResource;
use App\Filament\Resources\Roles\RoleResource;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Resources\Workplaces\WorkplaceResource;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Illuminate\Support\HtmlString;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public static bool $registerNavigation = false;
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->resources([
                ApplicationResource::class,
                BranchResource::class,
                CandidateResource::class,
                DepartmentResource::class,
                RecruitmentJobResource::class,
                RoleResource::class,
                UserResource::class,
                WorkplaceResource::class,
                CategoryResource::class,
                PostResource::class,
            ])
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
                \App\Filament\Widgets\InterviewCalendar::class,
            ])
            ->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook(
                'panels::body.end',
                fn (): HtmlString => new HtmlString(<<<'HTML'
                <script>
                (function () {
                    function attachCalendarTooltips() {
                        document.querySelectorAll(".fc-timegrid-event:not([data-fc-tip])").forEach(function (el) {
                            var time  = el.querySelector(".fc-event-time")?.textContent?.trim() ?? "";
                            var title = el.querySelector(".fc-event-title")?.textContent?.trim() ?? "";
                            if (title) {
                                el.setAttribute("title", time ? time + "\n" + title : title);
                                el.setAttribute("data-fc-tip", "1");
                                el.style.cursor = "default";
                            }
                        });
                    }
                    var observer = new MutationObserver(attachCalendarTooltips);
                    document.addEventListener("DOMContentLoaded", function () {
                        observer.observe(document.body, { childList: true, subtree: true });
                        attachCalendarTooltips();
                    });
                })();
                </script>
                HTML)
            );
    }
}
