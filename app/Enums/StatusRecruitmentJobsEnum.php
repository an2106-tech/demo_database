<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum StatusRecruitmentJobsEnum: string implements HasIcon, HasColor, HasLabel
{
    case DRAFT     = 'draft';
    case PUBLISHED = 'published';
    case CLOSED    = 'closed';
    case ARCHIVED  = 'archived';
    case EXPIRED   = 'expired';

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::DRAFT     => 'heroicon-o-pencil',
            self::PUBLISHED => 'heroicon-o-megaphone',
            self::CLOSED    => 'heroicon-o-lock-closed',
            self::ARCHIVED  => 'heroicon-o-archive-box',
            self::EXPIRED   => 'heroicon-o-clock',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::DRAFT     => 'gray',
            self::PUBLISHED => 'success',
            self::CLOSED    => 'warning',
            self::ARCHIVED  => 'danger',
            self::EXPIRED   => 'danger',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::DRAFT     => 'Nháp',
            self::PUBLISHED => 'Đang đăng',
            self::CLOSED    => 'Đã đóng',
            self::ARCHIVED  => 'Lưu trữ',
            self::EXPIRED   => 'Hết hạn',
        };
    }
}
