<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\InterviewCalendar;
use App\Filament\Widgets\InterviewScheduleAgenda;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;

class InterviewSchedule extends Page
{
    protected Width|string|null $maxContentWidth = Width::Full;

    protected static ?string $slug = 'interview-schedule';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Lịch phỏng vấn';

    protected static string|\UnitEnum|null $navigationGroup = 'Vận hành tuyển dụng';

    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function getTitle(): string|Htmlable
    {
        return 'Lịch phỏng vấn';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Theo dõi lịch theo ngày hoặc tuần và mở hồ sơ ứng tuyển ngay từ từng lịch hẹn.';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            InterviewScheduleAgenda::class,
            InterviewCalendar::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }
}
