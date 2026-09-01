<?php

namespace App\Providers\Filament;

use App\Filament\Resources\Applications\ApplicationResource;
use App\Filament\Resources\Branches\BranchResource;
use App\Filament\Resources\Candidates\CandidateResource;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Departments\DepartmentResource;
use App\Filament\Resources\OfferResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\RecruitmentJobs\RecruitmentJobResource;
use App\Filament\Resources\Roles\RoleResource;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Resources\Workplaces\WorkplaceResource;
use App\Filament\Widgets\RecruitmentDistributionChart;
use App\Filament\Widgets\RecruitmentPipelineChart;
use App\Filament\Widgets\RecruitmentRoleOverviewStats;
use App\Filament\Widgets\RecruitmentWorkload;
use App\Filament\Widgets\UpcomingInterviews;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
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
            ->navigationGroups([
                'Vận hành tuyển dụng',
                'Quản lý tuyển dụng',
                'Cơ cấu tổ chức',
                'Quản trị hệ thống',
                'Nội dung',
            ])
            ->resources([
                ApplicationResource::class,
                BranchResource::class,
                CandidateResource::class,
                DepartmentResource::class,
                OfferResource::class,
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
                RecruitmentRoleOverviewStats::class,
                RecruitmentWorkload::class,
                UpcomingInterviews::class,
                RecruitmentPipelineChart::class,
                RecruitmentDistributionChart::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
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
                PanelsRenderHook::USER_MENU_BEFORE,
                fn (): HtmlString => Auth::check()
                    ? new HtmlString(Blade::render('@livewire(\App\Livewire\Admin\NotificationsBell::class)'))
                    : new HtmlString('')
            );
    }
}
