<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum StatusApplicationEnum: string implements HasIcon, HasColor, HasLabel
{
    case APPLIED = 'new';
    case IN_REVIEW = 'in_review';
    case INTERVIEW_SCHEDULED = 'interview_scheduled';
    case OFFERED = 'offered';
    case REJECTED = 'rejected';

    public function getIcon(): string|BackedEnum|Htmlable|null
    {

        return match ($this) {
            self::APPLIED => 'heroicon-o-document',
            self::IN_REVIEW => 'heroicon-o-eye',
            self::INTERVIEW_SCHEDULED => 'heroicon-o-calendar',
            self::OFFERED => 'heroicon-o-hand-raise',
            self::REJECTED => 'heroicon-o-x-circle',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::APPLIED => 'primary',
            self::IN_REVIEW => 'warning',
            self::INTERVIEW_SCHEDULED => 'info',
            self::OFFERED => 'success',
            self::REJECTED => 'danger',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::APPLIED => 'Đã ứng tuyển',
            self::IN_REVIEW => 'Đang xem xét',
            self::INTERVIEW_SCHEDULED => 'Đã lên lịch phỏng vấn',
            self::OFFERED => 'Đã nhận offer',
            self::REJECTED => 'Đã từ chối',
        };
    }
}
