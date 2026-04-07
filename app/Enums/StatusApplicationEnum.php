<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum StatusApplicationEnum: string implements HasIcon, HasColor, HasLabel
{
    case NEW = 'new';
    case SCREENING = 'screening';
    case INTERVIEW = 'interview';
    case OFFER = 'offer';
    case HIRED = 'hired';
    case REJECTED = 'rejected';

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::NEW => 'heroicon-o-document-plus',
            self::SCREENING => 'heroicon-o-eye',
            self::INTERVIEW => 'heroicon-o-calendar',
            self::OFFER => 'heroicon-o-hand-raised',
            self::HIRED => 'heroicon-o-check-badge',
            self::REJECTED => 'heroicon-o-x-circle',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::NEW => 'gray',
            self::SCREENING => 'warning',
            self::INTERVIEW => 'info',
            self::OFFER => 'primary',
            self::HIRED => 'success',
            self::REJECTED => 'danger',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::NEW => 'Mới',
            self::SCREENING => 'Sàng lọc',
            self::INTERVIEW => 'Phỏng vấn',
            self::OFFER => 'Offer',
            self::HIRED => 'Đã tuyển',
            self::REJECTED => 'Từ chối',
        };
    }
}