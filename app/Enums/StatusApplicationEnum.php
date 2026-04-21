<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum StatusApplicationEnum: string implements HasIcon, HasColor, HasLabel
{
    case CV_REVIEWING = 'new';
    case SCREENING = 'screening';
    case INTERVIEW_SCHEDULED = 'interview_scheduled';
    case INTERVIEWING = 'interview';
    case OFFERED = 'offer';
    case HIRED = 'hired';
    case REJECTED = 'rejected';

    // Backward-compatible aliases for older workflow code paths.
    public const NEW = self::CV_REVIEWING;
    public const INTERVIEW = self::INTERVIEWING;
    public const OFFER = self::OFFERED;

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::CV_REVIEWING => 'heroicon-o-eye',
            self::SCREENING => 'heroicon-o-phone',
            self::INTERVIEW_SCHEDULED => 'heroicon-o-calendar',
            self::INTERVIEWING => 'heroicon-o-user-group',
            self::OFFERED => 'heroicon-o-hand-raised',
            self::HIRED => 'heroicon-o-check-badge',
            self::REJECTED => 'heroicon-o-x-circle',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::CV_REVIEWING => 'gray',
            self::SCREENING => 'info',
            self::INTERVIEW_SCHEDULED => 'warning',
            self::INTERVIEWING => 'primary',
            self::OFFERED => 'success',
            self::HIRED => 'success',
            self::REJECTED => 'danger',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::CV_REVIEWING => 'Sàng lọc CV',
            self::SCREENING => 'Sơ tuyển',
            self::INTERVIEW_SCHEDULED => 'Hẹn phỏng vấn',
            self::INTERVIEWING => 'Đang phỏng vấn',
            self::OFFERED => 'Đã gửi Offer',
            self::HIRED => 'Đã tuyển',
            self::REJECTED => 'Từ chối',
        };
    }
}
